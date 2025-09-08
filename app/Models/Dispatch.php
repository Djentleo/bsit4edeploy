<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_id',
        'responder_id',
        'status',
    ];

    public function responder()
    {
        return $this->belongsTo(User::class, 'responder_id');
    }
}
