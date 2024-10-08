<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $protected = ['id'];

    public function Prodi()
    {
        return $this->belongsTo(prodi::class, 'prodi_id');
    }

    public function levelKelas()
    {
        return $this->belongsTo(LevelKelas::class, 'level_kelas_id');
    }
}
