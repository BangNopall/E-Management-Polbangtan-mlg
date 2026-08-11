<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ukm extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function members()
    {
        return $this->hasMany(UkmMember::class);
    }

    public function jadwals()
    {
        return $this->hasMany(UkmJadwal::class);
    }

    // anggota aktif saja (dipakai saat fan-out presensi, Milestone C)
    public function anggotaAktif()
    {
        return $this->hasMany(UkmMember::class)
            ->where('peran', 'anggota')
            ->where('status', 'aktif');
    }

    public function pelatih()
    {
        return $this->hasMany(UkmMember::class)->where('peran', 'pelatih');
    }
}
