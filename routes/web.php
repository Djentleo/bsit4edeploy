<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
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
    // Placeholder Incident Tables route (Blade view expected at resources/views/incidents/index.blade.php)
    Route::get('incidents', function () {
        return view('incidents.index');
    })->name('incidents.index');
    // Placeholder Dispatch route (Blade view expected at resources/views/dispatch/index.blade.php)
    Route::get('dispatch', function () {
        return view('dispatch.index');
    })->name('dispatch.index');
    Route::get('incident-logs', function () {
        return view('incident_logs.index'); // Updated to reflect the renamed file
    })->name('incident.logs');
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
