<?php

namespace App\Console\Commands;

use App\Models\IzinApproval;
use App\Models\Notifikasi;
use App\Services\NotifikasiService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class IngatkanApproverCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'izin:ingatkan-approver';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim notifikasi pengingat in-app kepada approver yang memiliki pending persetujuan >12 jam (Idempoten).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();
        $cutoff = $now->copy()->subHours(12);

        $pendingApprovals = IzinApproval::with(['pengajuan'])
            ->where('status', 'menunggu')
            ->where('dibuka_at', '<', $cutoff)
            ->get();

        $notifikasiService = app(NotifikasiService::class);
        $count = 0;

        foreach ($pendingApprovals as $app) {
            if (!$app->approver_user_id) {
                continue;
            }

            $judul = 'Pengingat Persetujuan Perizinan';
            $pesan = "Pengajuan izin {$app->pengajuan->nama_snapshot} (Langkah {$app->urutan}) telah menunggu persetujuan Anda selama lebih dari 12 jam.";
            $link = route('admin.izin.persetujuan.review', $app->pengajuan_izin_id);

            // Idempoten: Cek apakah notifikasi pengingat yang sama sudah dikirimkan dalam 12 jam terakhir
            $alreadyNotified = Notifikasi::where('user_id', $app->approver_user_id)
                ->where('judul', $judul)
                ->where('link', $link)
                ->where('created_at', '>=', $cutoff)
                ->exists();

            if (!$alreadyNotified) {
                $notifikasiService->kirim($app->approver_user_id, $judul, $pesan, $link);
                $count++;
            }
        }

        $this->info("Berhasil mengirim {$count} pengingat persetujuan kepada approver.");

        return Command::SUCCESS;
    }
}
