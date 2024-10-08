<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JadwalPetugas extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    
     public function petugas1()
     {
         return $this->belongsTo(User::class, 'petugas1_id');
     }
 
     public function petugas2()
     {
         return $this->belongsTo(User::class, 'petugas2_id');
     }
}
