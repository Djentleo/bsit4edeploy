<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseService;

class IncidentLogsController extends Controller
{
    public function index(FirebaseService $firebaseService)
    {
        // Fetch resolved incidents from Firebase
        $resolved = $firebaseService->getResolvedIncidents();
        return view('incident_logs.index', [
            'resolvedIncidents' => $resolved,
        ]);
    }
}
