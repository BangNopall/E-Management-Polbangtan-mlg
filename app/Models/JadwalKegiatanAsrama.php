<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class jadwalKegiatanAsrama extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function blokRuangan()
    {
        return $this->belongsTo(blokRuangan::class , 'blok_id');
    }

    public function presensiApel()
    {
        return $this->hasMany(PresensiApel::class);
    }

    public function presensiSenam()
    {
        return $this->hasMany(PresensiSenam::class);
    }

    public function presensiUpacara()
    {
        return $this->hasMany(PresensiUpacara::class);
    }
}
