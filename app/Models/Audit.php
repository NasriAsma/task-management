<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    use HasFactory;

    protected $fillable = [
        'event',
        'user_id',
        'values',
        'url',
        'ip_address',
        'user_agent',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
