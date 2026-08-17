<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class blokRuangan extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function jadwalKegiatanAsrama()
    {
        return $this->hasMany(jadwalKegiatanAsrama::class , 'blok_id');
    }
}
