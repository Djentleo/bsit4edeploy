<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;

class DashboardController extends Controller
{
    public function index(FirebaseService $firebaseService)
    {
        $summaryData = $firebaseService->getSummaryData();
        $incidents = $firebaseService->getIncidents();
        return view('dashboard', compact('summaryData', 'incidents'));
    }
}
