<?php

namespace App\Console\Commands;

use App\Models\JenisPelanggaran;
use App\Models\Pelanggaran;
use App\Models\PengajuanIzin;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PeriksaKeterlambatanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'izin:periksa-keterlambatan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa mahasiswa berizin yang terlewat tenggat waktu kembali tanpa scan masuk, ubah status ke terlambat dan buat Pelanggaran otomatis berstatus submitted (Idempoten).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();

        // Cari pengajuan izin berstatus 'berjalan' yang waktu kembalinya sudah lewat dan belum ada kembali_at
        $terlambatIzins = PengajuanIzin::where('status', 'berjalan')
            ->where('waktu_kembali', '<', $now)
            ->whereNull('kembali_at')
            ->get();

        $count = 0;

        foreach ($terlambatIzins as $izin) {
            $izin->update(['status' => 'terlambat']);

            // Idempoten: Cek apakah Pelanggaran untuk mahasiswa ini pada tanggal ini sudah dibuat
            $jenisTerlambat = JenisPelanggaran::where('jenis_pelanggaran', 'like', '%lambat kembali%')->first();
            if (!$jenisTerlambat) {
                $jenisTerlambat = JenisPelanggaran::firstOrCreate(
                    ['jenis_pelanggaran' => 'Terlambat kembali dari izin resmi'],
                    ['kategori_id' => 1, 'poin' => 2, 'sub_kategori' => 'Ringan']
                );
            }

            $alreadyExists = Pelanggaran::where('user_id', $izin->user_id)
                ->where('jenis_pelanggaran_id', $jenisTerlambat->id)
                ->where('date', $now->toDateString())
                ->exists();

            if (!$alreadyExists) {
                $pelanggaran = Pelanggaran::create([
                    'user_id' => $izin->user_id,
                    'jenis_pelanggaran_id' => $jenisTerlambat->id,
                    'date' => $now->toDateString(),
                    'time' => $now->toTimeString(),
                    'statusPelanggaran' => 'submitted',
                ]);

                $izin->update(['pelanggaran_id' => $pelanggaran->id]);
            }

            $count++;
        }

        $this->info("Berhasil memproses {$count} pengajuan izin terlambat.");

        return Command::SUCCESS;
    }
}
