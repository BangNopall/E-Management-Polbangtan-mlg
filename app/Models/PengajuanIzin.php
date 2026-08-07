<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajuanIzin extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'waktu_berangkat' => 'datetime',
        'waktu_kembali' => 'datetime',
        'diajukan_at' => 'datetime',
        'disetujui_at' => 'datetime',
        'keluar_at' => 'datetime',
        'kembali_at' => 'datetime',
        'tiba_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function jenisIzin()
    {
        return $this->belongsTo(JenisIzin::class, 'jenis_izin_id');
    }

    public function ukm()
    {
        return $this->belongsTo(Ukm::class, 'ukm_id');
    }

    public function pelanggaran()
    {
        return $this->belongsTo(Pelanggaran::class, 'pelanggaran_id');
    }

    public function approvals()
    {
        return $this->hasMany(IzinApproval::class, 'pengajuan_izin_id')->orderBy('urutan', 'asc');
    }

    public function scopeAktifPada($query, $waktu)
    {
        return $query->whereIn('status', ['disetujui', 'berjalan'])
            ->where('waktu_berangkat', '<=', $waktu)
            ->where('waktu_kembali', '>=', $waktu);
    }

    public function scopeBelumKembali($query)
    {
        return $query->where('status', 'berjalan')
            ->whereNull('kembali_at');
    }
}
