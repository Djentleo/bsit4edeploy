<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FirebaseService;
use Carbon\Carbon;

class DashboardAnalyticsController extends Controller
{
    // GET /api/incident-severity-counts
    public function incidentSeverityCounts(Request $request, FirebaseService $firebaseService)
    {
        $filterYear = $request->query('filterYear'); // optional, e.g. '2025'
        $allIncidents = $firebaseService->getAllIncidents(); // Only active incidents
        $allowedSeverities = ['critical', 'high', 'medium', 'low'];
        $severityCounts = array_fill_keys($allowedSeverities, 0);
        foreach ($allIncidents as $incident) {
            $ts = $incident['timestamp'] ?? $incident['created_at'] ?? null;
            if ($ts) {
                $carbon = Carbon::parse($ts);
                if ($filterYear && $carbon->format('Y') !== $filterYear) {
                    continue;
                }
            }
            $severity = strtolower($incident['severity'] ?? '');
            if ($severity && in_array($severity, $allowedSeverities)) {
                $severityCounts[$severity]++;
            }
        }
        $labels = array_map('ucfirst', array_keys($severityCounts));
        $data = array_values($severityCounts);
        return response()->json([
            'labels' => $labels,
            'data' => $data
        ]);
    }
    // GET /api/incident-status-counts
    public function incidentStatusCounts(Request $request, FirebaseService $firebaseService)
    {
        $filterYear = $request->query('filterYear'); // optional, e.g. '2025'
        $allIncidents = $firebaseService->getAllIncidents(); // active incidents only
        $resolvedIncidents = method_exists($firebaseService, 'getResolvedIncidents') ? $firebaseService->getResolvedIncidents() : [];
        $allowedStatuses = ['new', 'dispatched', 'resolved'];
        $statusCounts = array_fill_keys($allowedStatuses, 0);

        // Count active (non-resolved) statuses from active nodes only
        foreach ($allIncidents as $incident) {
            $ts = $incident['timestamp'] ?? $incident['created_at'] ?? null;
            if ($ts) {
                $carbon = Carbon::parse($ts);
                if ($filterYear && $carbon->format('Y') !== $filterYear) {
                    continue;
                }
            }
            $status = $incident['status'] ?? null;
            if ($status === 'new' || $status === 'dispatched') {
                $statusCounts[$status]++;
            }
            // Intentionally ignore 'resolved' here to avoid double counting
        }

        // Count resolved exclusively from the resolved_incidents node
        foreach ($resolvedIncidents as $incident) {
            // Prefer a dedicated resolved timestamp if available
            $ts = $incident['resolved_at'] ?? $incident['timestamp'] ?? $incident['created_at'] ?? null;
            if ($ts) {
                $carbon = Carbon::parse($ts);
                if ($filterYear && $carbon->format('Y') !== $filterYear) {
                    continue;
                }
            }
            $statusCounts['resolved']++;
        }
        $labels = array_map('ucfirst', array_keys($statusCounts));
        $data = array_values($statusCounts);
        return response()->json([
            'labels' => $labels,
            'data' => $data
        ]);
    }

    // GET /api/incidents-over-time
    public function incidentsOverTime(Request $request, FirebaseService $firebaseService)
    {
        $group = $request->query('group', 'month'); // 'day', 'week', 'month', or 'year'
        $filterYear = $request->query('filterYear'); // optional, e.g. '2025'
        $allIncidents = $firebaseService->getAllIncidents();
        $counts = [];
        foreach ($allIncidents as $incident) {
            $ts = $incident['timestamp'] ?? $incident['created_at'] ?? null;
            if ($ts) {
                $carbon = Carbon::parse($ts);
                // If filterYear is set, skip incidents not in that year
                if ($filterYear && $carbon->format('Y') !== $filterYear) {
                    continue;
                }
                if ($group === 'day') {
                    $key = $carbon->format('Y-m-d');
                } elseif ($group === 'week') {
                    $key = $carbon->format('o-\WW');
                } elseif ($group === 'month') {
                    $key = $carbon->format('Y-m');
                } elseif ($group === 'year') {
                    $key = $carbon->format('Y');
                } else {
                    $key = $carbon->format('Y-m'); // fallback
                }
                if (!isset($counts[$key])) $counts[$key] = 0;
                $counts[$key]++;
            }
        }
        ksort($counts);
        $labels = array_keys($counts);
        $data = array_values($counts);
        return response()->json([
            'labels' => $labels,
            'data' => $data,
            'group' => $group
        ]);
    }

    // GET /api/incident-type-counts
    public function incidentTypeCounts(Request $request, FirebaseService $firebaseService)
    {
        $filterYear = $request->query('filterYear'); // optional, e.g. '2025'
        $allIncidents = $firebaseService->getAllIncidents();
        $allowedTypes = ['vehicle_crash', 'fire', 'disturbance', 'medical_emergency'];
        $typeCounts = array_fill_keys($allowedTypes, 0);
        foreach ($allIncidents as $incident) {
            $ts = $incident['timestamp'] ?? $incident['created_at'] ?? null;
            if ($ts) {
                $carbon = Carbon::parse($ts);
                if ($filterYear && $carbon->format('Y') !== $filterYear) {
                    continue;
                }
            }
            $type = $incident['type'] ?? $incident['event'] ?? null;
            if ($type && in_array($type, $allowedTypes)) {
                $typeCounts[$type]++;
            }
        }
        $labels = array_keys($typeCounts);
        $data = array_values($typeCounts);
        return response()->json([
            'labels' => $labels,
            'data' => $data
        ]);
    }
}
