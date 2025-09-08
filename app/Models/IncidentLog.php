<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_id',
        'responder_id',
        'resolved_at',
        'remarks',
    ];

    public function responder()
    {
        return $this->belongsTo(User::class, 'responder_id');
    }
}
