<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Role;
use App\Models\Kelas;
use App\Models\Prodi;
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
    const PEMBINA_ROLE_ID = 5;
    const PELATIH_UKM_ROLE_ID = 6;
    const SECURITY_ROLE_ID = 7;
    const DOSEN_PA_ROLE_ID = 8;
    const PEJABAT_ROLE_ID = 9;

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
        'prodi_id',
        'dosen_pa_id',
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
        return $this->belongsTo(BlokRuangan::class, 'blok_ruangan_id');
    }
    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }

    public function dosenPa()
    {
        return $this->belongsTo(User::class, 'dosen_pa_id');
    }

    public function mahasiswaBimbingan()
    {
        return $this->hasMany(User::class, 'dosen_pa_id');
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

    public function isPembina()
    {
        return $this->role_id === self::PEMBINA_ROLE_ID;
    }

    public function isPelatihUkm()
    {
        return $this->role_id === self::PELATIH_UKM_ROLE_ID;
    }

    public function isSecurity()
    {
        return $this->role_id === self::SECURITY_ROLE_ID;
    }

    public function isDosenPa()
    {
        return $this->role_id === self::DOSEN_PA_ROLE_ID;
    }

    public function isPejabat()
    {
        return $this->role_id === self::PEJABAT_ROLE_ID;
    }

    public function ukmMemberships()
    {
        return $this->hasMany(UkmMember::class);
    }

    public function ukms()
    {
        return $this->belongsToMany(Ukm::class, 'ukm_members')
            ->withPivot('peran', 'status')
            ->withTimestamps();
    }
}
