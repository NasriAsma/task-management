<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Permission extends Model
{
    use HasFactory, LogsActivity;
    protected $fillable = ['name', 'description'];

  // 1. Configure les champs suivis + le comportement d'enregistrement
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description']) // Champs surveillés
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
        return $this->belongsToMany(Role::class, 'role_permission');
    }

       
    
  
}
