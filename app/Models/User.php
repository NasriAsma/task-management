<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


//Imports de packages d'activity_log
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Contracts\Activity;

class User extends Authenticatable
{
    use LogsActivity;

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

    // 1. Configuration des champs à surveiller (Old / New / Type d'action)
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email']) // Les champs à surveiller
            ->logOnlyDirty() // IMPORTANT : Permet de générer les tableaux "attributes" (New) et "old" (Old)
            ->dontSubmitEmptyLogs(); // N'enregistre rien s'il n'y a pas de changement
    }

    // 2. Injection des données supplémentaires (IP, User contextuel)
    // Cette méthode est appelée juste avant que l'activité ne soit sauvegardée en base.
    public function tapActivity(Activity $activity, string $eventName)
    {
        // Ajout de l'adresse IP dans les propriétés personnalisées
        $activity->properties = $activity->properties->merge([
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'), // Bonus : le navigateur utilisé
        ]);
        
        // Note : Le "User", le "Temps", et le "Type d'action" sont gérés automatiquement
        // par le package, voir les explications ci-dessous.
    }


    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }
    public function tasksAssigned()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function tasksCreated()
    {
        return $this->hasMany(Task::class, 'created_by');
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