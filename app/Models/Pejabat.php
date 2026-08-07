<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pejabat extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'mulai_menjabat' => 'date',
        'selesai_menjabat' => 'date',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeUntuk($query, string $jabatan, string $lingkup = 'global', ?int $lingkupId = null)
    {
        return $query->where('jabatan', $jabatan)
            ->where('lingkup', $lingkup)
            ->when($lingkupId, fn ($q) => $q->where('lingkup_id', $lingkupId))
            ->active();
    }
}
