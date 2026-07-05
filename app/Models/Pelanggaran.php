<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pelanggaran extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function accepted()
    {
        return $this->belongsTo(User::class, 'accepted_id');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriPelanggaran::class, 'kategori_id');
    }
}
