<?php


namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Services\FirebaseService;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(FirebaseService $firebaseService)
    {
        // RBAC: Only allow admin, redirect responders
        if (Auth::user() && Auth::user()->role === 'responder') {
            return redirect()->route('responder.incidents');
        }

        $summaryData = $firebaseService->getSummaryData();
        // Add responder count from MySQL
        $summaryData['responders'] = User::where('role', 'responder')->count();

        // Get all incidents (mobile + cctv)
        $allIncidents = $firebaseService->getAllIncidents();
        // Sort by timestamp descending (most recent first)
        $sorted = collect($allIncidents)->sortByDesc(function ($i) {
            // Try to parse timestamp as string or fallback to 0
            return strtotime($i['timestamp'] ?? $i['created_at'] ?? '');
        })->values();
        // Get the 3 most recent
        $incidents = $sorted->take(3)->map(function ($i) {
            // Add a display_source for badge
            $i['display_source'] = $i['source'] === 'cctv' ? 'CCTV' : 'Mobile';
            return $i;
        })->all();

        return view('dashboard', compact('summaryData', 'incidents'));
    }
}
