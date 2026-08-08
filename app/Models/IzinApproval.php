<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IzinApproval extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'dibuka_at' => 'datetime',
        'acted_at' => 'datetime',
    ];

    public function pengajuan()
    {
        return $this->belongsTo(PengajuanIzin::class, 'pengajuan_izin_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }

    public function aktor()
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
