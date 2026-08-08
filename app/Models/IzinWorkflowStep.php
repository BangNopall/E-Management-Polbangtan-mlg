<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IzinWorkflowStep extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'jabatan' => 'array',
        'fallback_jabatan' => 'array',
        'is_active' => 'boolean',
    ];

    public function jenisIzin()
    {
        return $this->belongsTo(JenisIzin::class, 'jenis_izin_id');
    }
}
