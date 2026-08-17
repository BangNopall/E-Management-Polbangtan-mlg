<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlokRuangan extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function jadwalKegiatanAsrama()
    {
        return $this->hasMany(JadwalKegiatanAsrama::class , 'blok_id');
    }
}
