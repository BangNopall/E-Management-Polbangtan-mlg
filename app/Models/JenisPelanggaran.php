<?php

namespace App\Models;

use App\Models\KategoriPelanggaran;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JenisPelanggaran extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function KategoriPelanggaran()
    {
        return $this->belongsTo(KategoriPelanggaran::class);
    }
}
