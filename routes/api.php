<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


use App\Http\Controllers\Api\DashboardAnalyticsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Incidents over time analytics
Route::get('/incidents-over-time', [DashboardAnalyticsController::class, 'incidentsOverTime']);
// Incident type counts analytics
Route::get('/incident-type-counts', [DashboardAnalyticsController::class, 'incidentTypeCounts']);
// Incident status counts analytics
Route::get('/incident-status-counts', [DashboardAnalyticsController::class, 'incidentStatusCounts']);
// Incident severity counts analytics
Route::get('/incident-severity-counts', [DashboardAnalyticsController::class, 'incidentSeverityCounts']);
