<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisIzin extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'butuh_bermalam' => 'boolean',
        'butuh_ukm' => 'boolean',
        'butuh_konfirmasi_tiba' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function steps()
    {
        return $this->hasMany(IzinWorkflowStep::class, 'jenis_izin_id')->orderBy('urutan', 'asc');
    }

    public function pengajuan()
    {
        return $this->hasMany(PengajuanIzin::class, 'jenis_izin_id');
    }
}
