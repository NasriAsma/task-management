<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Log;


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

    // 1. Configure les champs suivis + le comportement d'enregistrement
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email']) // Champs surveillés
            ->logOnlyDirty() // Génère "attributes" (new) et "old" seulement si changement
            ->dontSubmitEmptyLogs(); // Ne log rien si aucun changement
    }

    // 2. Ajoute des infos contextuelles avant l'enregistrement en base
    public function tapActivity(Activity $activity, string $eventName)
    {
        // Ajoute IP + navigateur dans les propriétés personnalisées
        $activity->properties = $activity->properties->merge([
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'), // Bonus : le navigateur utilisé
        ]);



        // 3. Log dans le fichier de log aussi  
        Log::channel(config('logging.default'))->info('Activity log', [
            'event' => $eventName,
            'subject_type' => $activity->subject_type,
            'subject_id' => $activity->subject_id,
            'causer_id' => $activity->causer_id,
            'properties' => $activity->properties,
        ]);

        // Le user, la date et le type d'action (created/updated/deleted) sont gérés par le package
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