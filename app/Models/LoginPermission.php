<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginPermission extends Model
{
    use HasFactory;

    protected $table = 'login_permissions'; // Nama tabel di database

    protected $fillable = [
        'user_id',
        'is_login',
        'is_logout',
        'desc_logout',
        'expiry_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
