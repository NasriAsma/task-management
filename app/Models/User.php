<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'code_2FA',
        'code_2FA_expiry',
        'is_2fa_enabled'
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }
    public function tasksAssigned()
    {
        return $this->hasMany(Tasks::class, 'assigned_to');
    }

    public function tasksCreated()
    {
        return $this->hasMany(Tasks::class, 'created_by');
    }

    public function hasRole($roleName)
    {
        return 
        $this->roles()->where('name', $roleName)->exists(); 
    }


    public function hasPermission($permissionName)
    {
        foreach ($this->roles as $role) {
            if ($role->permissions()->where('name', $permissionName)->exists()) {
                return true;
            }
        }
        return false;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token'
      
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'code_2FA_expiry' => 'datetime',

    ];


  
}
