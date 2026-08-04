<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UkmPresensi extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function jadwal()
    {
        return $this->belongsTo(UkmJadwal::class, 'ukm_jadwal_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
