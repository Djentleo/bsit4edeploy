<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentTimeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_id',
        'user_id',
        'action',
        'details',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
