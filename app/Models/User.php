<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Role;
use App\Models\Kelas;
use App\Models\prodi;
use App\Models\Presence;
use App\Models\JenisKelas;
use App\Models\LevelKelas;
use App\Models\JadwalPetugas;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const ADMIN_ROLE_ID = 1;
    const OPERATOR_ROLE_ID = 2;
    const USER_ROLE_ID = 3;
    const PELATIH_ROLE_ID = 4;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'position_id',
        'nim',
        'blok_kamar',
        'no_kamar',
        'asal_daerah',
        'password',
        'phone',
        'role_id',
        'image',
        'status',
        'point',
        'kelas_id',
        'blok_ruangan_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'id');
    }
    public function Nokelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'kelas');
    }

    public function LoginPermission()
    {
        return $this->hasOne(LoginPermission::class, 'user_id');
    }

    public function levelKelas()
    {
        return $this->belongsTo(LevelKelas::class, 'level_kelas_id');
    }

    public function petugas1()
     {
         return $this->belongsTo(JadwalPetugas::class, 'petugas1_id');
     }
 
     public function petugas2()
     {
         return $this->belongsTo(JadwalPetugas::class, 'petugas2_id');
     }

    public function presenses()
    {
        return $this->hasMany(Presence::class, 'user_id', 'id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
    public function roleId()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function blok()
    {
        return $this->belongsTo(blokRuangan::class, 'blok_ruangan_id');
    }
    public function prodi()
    {
        return $this->belongsTo(prodi::class, 'prodi_id');
    }

    public function scopeOnlyEmployees($query)
    {
        return $query->where('role_id', self::USER_ROLE_ID);
    }

    public function isAdmin()
    {
        return $this->role_id === self::ADMIN_ROLE_ID;
    }

    public function isOperator()
    {
        return $this->role_id === self::OPERATOR_ROLE_ID;
    }

    public function isUser()
    {
        return $this->role_id === self::USER_ROLE_ID;
    }

    public function isPelatih()
    {
        return $this->role_id === self::PELATIH_ROLE_ID;
    }
}
