<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'event',
        'user_id',
        'values',
        'url',
        'ip_address',
        'user_agent',
        'description',
        'created_at',
    ];

    protected $casts = [
        'values' => 'array',
        'created_at' => 'datetime',
    ];

    // Relation : Un audit appartient à un utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
