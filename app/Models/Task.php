<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Contracts\Activity;
use App\Models\User;

class Task extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'status',
        'deadline',
        'priority',
        'assigned_to',
        'created_by',
    ];



     // 1. Configure les champs suivis + le comportement d'enregistrement
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'description', 'status', 'deadline', 'priority', 'assigned_to', 'created_by']) // Champs surveillés
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

        Log::channel(config('logging.default'))->info('Activity log', [
            'event' => $eventName,
            'subject_type' => $activity->subject_type,
            'subject_id' => $activity->subject_id,
            'causer_id' => $activity->causer_id,
            'properties' => $activity->properties,
        ]);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

