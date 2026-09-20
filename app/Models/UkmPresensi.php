<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UkmPresensi extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function jadwal()
    {
        return $this->belongsTo(UkmJadwal::class, 'ukm_jadwal_id');
    }

    public function jadwalWithTrashed()
    {
        return $this->belongsTo(UkmJadwal::class, 'ukm_jadwal_id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
