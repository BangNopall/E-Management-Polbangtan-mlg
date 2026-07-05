<?php

namespace App\Models;

use App\Models\JenisPelanggaran;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KategoriPelanggaran extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function JenisPelanggaran()
    {
        return $this->belongsTo(JenisPelanggaran::class);
    }
}
