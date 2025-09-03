<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileUploadController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('users', function () {
        return view('users.index');
    })->name('users.index');
    // Incident Tables Dropdown
    Route::get('incidents/mobile', function () {
        return view('incidents.mobile');
    })->name('incidents.mobile');
    Route::get('incidents/cctv', function () {
        return view('incidents.cctv');
    })->name('incidents.cctv');
    Route::get('incident-logs', function () {
        return view('incident_logs.index'); // Updated to reflect the renamed file
    })->name('incident.logs');
});
use App\Services\FirebaseService;

Route::get('/dispatch', function (\Illuminate\Http\Request $request, FirebaseService $firebaseService) {
    $incidentId = $request->query('incident_id');
    $incident = $incidentId ? $firebaseService->getIncidentById($incidentId) : null;
    return view('dispatch.index', compact('incidentId', 'incident'));
});
Route::get('/test-firebase', function (FirebaseService $firebaseService) {
    $incidents = $firebaseService->getIncidents();
    return response()->json($incidents);
});

Route::get('/test-summary', function (FirebaseService $firebaseService) {
    $summaryData = $firebaseService->getSummaryData();
    return response()->json($summaryData);
});

Route::post('/upload', [FileUploadController::class, 'upload'])->name('file.upload');
