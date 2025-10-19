<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incident;

class ResponderIncidentController extends Controller
{
    public function show($id)
    {
        $incident = Incident::findOrFail($id);
        // You may want to check if the logged-in user is assigned to this incident
        return view('responders.incident-show', compact('incident'));
    }
}
