<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Attendance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DailyAttendance extends Command
{
    protected $signature = 'daily-attendance';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Logika untuk membuat absensi
        $attendance = new Attendance();
        $attendance->title = ' Absensi Harian';
        $attendance->date = Carbon::now()->format('Y-m-d');
        $attendance->save();
        
        // $this->info('Absensi harian telah berhasil dibuat.');
        Log::info('Absensi harian berhasil dibuat pada ' . now());
    }
}
