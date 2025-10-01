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
}
