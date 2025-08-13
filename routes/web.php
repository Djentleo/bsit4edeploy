<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
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
});
