<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'firebase_id',
        'type',
        'location',
        'reporter_name',
        'reporter_id',
        'department',
        'status',
        'timestamp',
        'source',
        'incident_description',
        'priority',
        'severity',
    ];

    protected $dates = [
        'timestamp',
        'created_at',
        'updated_at',
    ];

    public function dispatches()
    {
        return $this->hasMany(Dispatch::class, 'incident_id', 'firebase_id');
    }

    // Get all responders (users) assigned to this incident via Dispatch
    public function responders()
    {
        return $this->hasManyThrough(
            User::class, // The related model
            Dispatch::class, // The intermediate model
            'incident_id', // Foreign key on Dispatch table...
            'id', // Foreign key on User table...
            'firebase_id', // Local key on Incident table...
            'responder_id' // Local key on Dispatch table...
        );
    }
}
