<?php

namespace App\Services\Izin;

use App\Models\IzinWorkflowStep;
use App\Models\JadwalPetugas;
use App\Models\Pejabat;
use App\Models\PengajuanIzin;
use App\Models\UkmMember;
use App\Models\User;
use Illuminate\Support\Collection;

class ApproverResolver
{
    /**
     * Resolve candidates for a workflow step given the request context.
     *
     * @param IzinWorkflowStep $step
     * @param PengajuanIzin|array $context Array or PengajuanIzin model containing user, ukm_id, etc.
     * @return array Matrix with 'candidates' (Collection of Users) and 'is_fallback' (bool)
     */
    public function resolve(IzinWorkflowStep $step, PengajuanIzin|array $context): array
    {
        $student = $context instanceof PengajuanIzin ? $context->user : ($context['user'] ?? null);
        $ukmId = $context instanceof PengajuanIzin ? $context->ukm_id : ($context['ukm_id'] ?? null);

        $date = null;
        if ($context instanceof PengajuanIzin && $context->waktu_berangkat) {
            $date = $context->waktu_berangkat->format('Y-m-d');
        } elseif (is_array($context) && !empty($context['waktu_berangkat'])) {
            try {
                $date = \Carbon\Carbon::parse($context['waktu_berangkat'])->format('Y-m-d');
            } catch (\Throwable $e) {
                $date = null;
            }
        }

        // 1. Run primary resolver strategy
        $candidates = $this->resolveStrategy(
            $step->resolver,
            $step->jabatan,
            $step->lingkup ?? 'global',
            $step->lingkup_id,
            $ukmId,
            $date,
            $student
        );

        if ($candidates->isNotEmpty()) {
            return [
                'candidates' => $candidates,
                'is_fallback' => false,
                'resolver_used' => $step->resolver,
            ];
        }

        // 2. If primary returns empty, execute fallback resolver if present
        if ($step->fallback_resolver) {
            $fallbackCandidates = $this->resolveStrategy(
                $step->fallback_resolver,
                $step->fallback_jabatan,
                $step->lingkup ?? 'global',
                $step->lingkup_id,
                $ukmId,
                $date,
                $student
            );

            if ($fallbackCandidates->isNotEmpty()) {
                return [
                    'candidates' => $fallbackCandidates,
                    'is_fallback' => true,
                    'resolver_used' => $step->fallback_resolver,
                ];
            }
        }

        return [
            'candidates' => collect(),
            'is_fallback' => false,
            'resolver_used' => $step->resolver,
        ];
    }

    /**
     * Execute specific resolver strategy.
     */
    public function resolveStrategy(
        string $resolver,
        ?array $jabatan,
        ?string $lingkup,
        ?int $lingkupId,
        ?int $ukmId,
        ?string $date,
        ?User $student
    ): Collection {
        $lingkup = $lingkup ?? 'global';
        return match ($resolver) {
            'pejabat' => $this->resolvePejabat($jabatan, $lingkup, $lingkupId, $student),
            'dosen_pa' => $this->resolveDosenPa($student),
            'pembina_ukm' => $this->resolvePembinaUkm($ukmId),
            'petugas_jaga' => $this->resolvePetugasJaga($date),
            default => collect(),
        };
    }

    /**
     * Resolve pejabat candidates based on jabatan list and scope.
     */
    private function resolvePejabat(?array $jabatans, string $lingkup, ?int $lingkupId, ?User $student): Collection
    {
        if (empty($jabatans)) {
            return collect();
        }

        $effectiveLingkupId = $lingkupId;
        if ($lingkup === 'prodi' && !$effectiveLingkupId && $student) {
            $effectiveLingkupId = $student->prodi_id;
        } elseif ($lingkup === 'blok' && !$effectiveLingkupId && $student) {
            $effectiveLingkupId = $student->blok_ruangan_id;
        }

        $pejabats = Pejabat::whereIn('jabatan', $jabatans)
            ->where('is_active', true)
            ->where(function ($q) use ($lingkup, $effectiveLingkupId) {
                $q->where('lingkup', 'global');
                if ($lingkup !== 'global' && $effectiveLingkupId) {
                    $q->orWhere(function ($q2) use ($lingkup, $effectiveLingkupId) {
                        $q2->where('lingkup', $lingkup)->where('lingkup_id', $effectiveLingkupId);
                    });
                }
            })
            ->with('user')
            ->get();

        return $pejabats->pluck('user')->filter()->values();
    }

    /**
     * Resolve Dosen PA candidate from student class relation, or fallback to Operator role users if null.
     */
    private function resolveDosenPa(?User $student): Collection
    {
        if ($student) {
            $student->loadMissing('dosenPa');
            if ($student->dosenPa) {
                return collect([$student->dosenPa]);
            }

            if ($student->kelas_id) {
                $student->loadMissing('kelas.dosenPa');
                $dosenPaKelas = optional($student->kelas)->dosenPa;

                if ($dosenPaKelas) {
                    return collect([$dosenPaKelas]);
                }
            }
        }

        // Fallback: Jika mahasiswa tidak memiliki dosen PA sama sekali,
        // fallback diarahkan ke seluruh akun dengan role Dosen PA, BUKAN Operator.
        return User::where('role_id', User::DOSEN_PA_ROLE_ID)->get();
    }

    /**
     * Resolve active Pembina UKM candidates for given UKM ID.
     */
    private function resolvePembinaUkm(?int $ukmId): Collection
    {
        if (!$ukmId) {
            return collect();
        }

        $pembinas = UkmMember::where('ukm_id', $ukmId)
            ->where('peran', 'pembina')
            ->where('status', 'aktif')
            ->with('user')
            ->get();

        return $pembinas->pluck('user')->filter()->values();
    }

    /**
     * Resolve duty officers (petugas_jaga) for a specific date, or fallback to Pelatih & Operator users if no duty officer scheduled.
     */
    private function resolvePetugasJaga(?string $date): Collection
    {
        if ($date) {
            $jadwal = JadwalPetugas::where('date', $date)
                ->with(['petugas1', 'petugas2'])
                ->first();

            if ($jadwal) {
                $officers = collect([$jadwal->petugas1, $jadwal->petugas2])
                    ->filter()
                    ->values();

                if ($officers->isNotEmpty()) {
                    return $officers;
                }
            }
        }

        // Fallback: Jika jadwal petugas piket pada tanggal keberangkatan belum dibuat oleh admin,
        // resolve otomatis murni ke akun staf ber-role Pelatih (tanpa melibatkan Operator).
        return User::where('role_id', User::PELATIH_ROLE_ID)->get();
    }
}
