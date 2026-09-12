<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UkmJadwal extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected static function booted()
    {
        static::deleting(function ($jadwal) {
            if ($jadwal->isForceDeleting()) {
                $jadwal->presensis()->forceDelete();
            } else {
                $jadwal->presensis()->delete();
            }
        });
    }

    public function ukm()
    {
        return $this->belongsTo(Ukm::class);
    }

    public function presensis()
    {
        return $this->hasMany(UkmPresensi::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
