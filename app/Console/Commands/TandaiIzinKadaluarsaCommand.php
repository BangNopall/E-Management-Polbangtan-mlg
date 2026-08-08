<?php

namespace App\Console\Commands;

use App\Models\PengajuanIzin;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TandaiIzinKadaluarsaCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'izin:tandai-kadaluarsa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tandai pengajuan izin yang belum selesai diproses namun waktu keberangkatan telah terlewat >2 jam sebagai kadaluarsa (Idempoten).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();
        $cutoff = $now->copy()->subHours(2);

        // Idempoten: Hanya menyasar status 'diajukan' atau 'menunggu' yang terlewat >2 jam
        $affected = PengajuanIzin::whereIn('status', ['diajukan', 'menunggu'])
            ->where('waktu_berangkat', '<', $cutoff)
            ->update([
                'status' => 'kadaluarsa',
                'langkah_aktif' => null,
                'updated_at' => $now,
            ]);

        $this->info("Berhasil menandai {$affected} pengajuan izin sebagai kadaluarsa.");

        return Command::SUCCESS;
    }
}
