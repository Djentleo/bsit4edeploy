<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_id',
        'type',
        'location',
        'reporter_name',
        'department',
        'status',
        'timestamp',
        'incident_description',
        'responder_id',
        'resolved_at',
        'remarks',
    ];

    public function responder()
    {
        return $this->belongsTo(User::class, 'responder_id');
    }
}
