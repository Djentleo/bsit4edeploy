<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Livewire\Responders\IncidentDetails;
use App\Http\Controllers\AdminCreationController;
use App\Http\Controllers\AdminRecoveryController;
use App\Services\FirebaseService;
use App\Models\Incident;

Route::get('/', function () {
    return redirect()->route('login');
});

// Responder RBAC routes
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->prefix('responder')->name('responder.')->group(function () {
    Route::get('/incidents', function () {
        if (Auth::user() && Auth::user()->role !== 'responder') {
            return redirect()->route('dashboard');
        }
        return view('responders.incidents');
    })->name('incidents');
    Route::get('/history', function () {
        if (Auth::user() && Auth::user()->role !== 'responder') {
            return redirect()->route('dashboard');
        }
        return view('responders.history');
    })->name('history');

    // Responder incident details page (Livewire 3)
    Route::get('/incidents/{dispatchId}', IncidentDetails::class)->name('incident-details');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('users', function () {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard');
        }
        return view('users.index');
    })->name('users.index');
    // Incident Tables Dropdown
    Route::get('incidents/mobile', function () {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard');
        }
        return view('incidents.mobile');
    })->name('incidents.mobile');
    Route::get('incidents/cctv', function () {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard');
        }
        return view('incidents.cctv');
    })->name('incidents.cctv');
    Route::get('incident-logs', [\App\Http\Controllers\IncidentLogsController::class, 'index'])->name('incident.logs');
    Route::get('incident-logs', function () {
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('dashboard');
        }
        return app(\App\Http\Controllers\IncidentLogsController::class)->index(app(\App\Services\FirebaseService::class));
    })->name('incident.logs');

    Route::get('/incident-report/generate', [\App\Http\Controllers\IncidentReportController::class, 'generate'])->name('incident-report.generate');
});



Route::get('/dispatch', function (\Illuminate\Http\Request $request) {
    $incidentId = $request->query('incident_id');
    $incident = $incidentId ? Incident::where('firebase_id', $incidentId)->first() : null;
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

// Admin creation page (only if no admin exists) - accessible to guests only with rate limiting
Route::middleware(['guest', 'throttle:10,1'])->group(function () {
    Route::get('/admin-create', [AdminCreationController::class, 'showForm'])->name('admin.create.form');
    Route::post('/admin-create', [AdminCreationController::class, 'create'])->name('admin.create');
});

// Admin recovery page (accessible with recovery key)
Route::middleware(['guest', 'throttle:10,1'])->group(function () {
    Route::get('/admin-recover', [AdminRecoveryController::class, 'showForm'])->name('admin.recover.form');
    Route::post('/admin-recover', [AdminRecoveryController::class, 'recover'])->name('admin.recover');
});
