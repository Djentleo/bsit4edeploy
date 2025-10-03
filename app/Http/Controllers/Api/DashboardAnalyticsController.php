<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardAnalyticsController extends Controller
{
    // GET /api/incident-severity-counts
    public function incidentSeverityCounts(Request $request)
    {
        $filterYear = $request->query('filterYear'); // optional, e.g. '2025'
        $allowedSeverities = ['critical', 'high', 'medium', 'low'];
        $severityCounts = array_fill_keys($allowedSeverities, 0);
        $query = \App\Models\Incident::query();
        if ($filterYear && $filterYear !== 'all') {
            $query->whereYear('timestamp', $filterYear);
        }
        $results = $query->select('severity', DB::raw('COUNT(*) as count'))
            ->whereIn('severity', $allowedSeverities)
            ->groupBy('severity')
            ->get();
        foreach ($results as $row) {
            $severity = strtolower($row->severity);
            if (isset($severityCounts[$severity])) {
                $severityCounts[$severity] = $row->count;
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
    public function incidentStatusCounts(Request $request)
    {
        $filterYear = $request->query('filterYear'); // optional, e.g. '2025'
        $allowedStatuses = ['new', 'dispatched', 'resolved'];
        $statusCounts = array_fill_keys($allowedStatuses, 0);

        $query = \App\Models\Incident::query();
        if ($filterYear && $filterYear !== 'all') {
            $query->whereYear('timestamp', $filterYear);
        }
        // Count new and dispatched
        $results = $query->select('status', DB::raw('COUNT(*) as count'))
            ->whereIn('status', ['new', 'dispatched'])
            ->groupBy('status')
            ->get();
        foreach ($results as $row) {
            $status = $row->status;
            if (isset($statusCounts[$status])) {
                $statusCounts[$status] = $row->count;
            }
        }
        // Count resolved (status = resolved)
        $resolvedQuery = \App\Models\Incident::query();
        if ($filterYear && $filterYear !== 'all') {
            $resolvedQuery->whereYear('timestamp', $filterYear);
        }
        $resolvedCount = $resolvedQuery->where('status', 'resolved')->count();
        $statusCounts['resolved'] = $resolvedCount;

        $labels = array_map('ucfirst', array_keys($statusCounts));
        $data = array_values($statusCounts);
        return response()->json([
            'labels' => $labels,
            'data' => $data
        ]);
    }

    // GET /api/incidents-over-time
    public function incidentsOverTime(Request $request)
    {
        $group = $request->query('group', 'month'); // 'day', 'week', 'month', or 'year'
        $filterYear = $request->query('filterYear'); // optional, e.g. '2025'
        $query = \App\Models\Incident::query();
        if ($filterYear && $filterYear !== 'all') {
            $query->whereYear('timestamp', $filterYear);
        }
        // Choose SQL date format based on group
        switch ($group) {
            case 'day':
                $dateFormat = '%Y-%m-%d';
                break;
            case 'week':
                $dateFormat = '%x-W%v';
                break;
            case 'month':
                $dateFormat = '%Y-%m';
                break;
            case 'year':
                $dateFormat = '%Y';
                break;
            default:
                $dateFormat = '%Y-%m';
        }
        $results = $query
            ->selectRaw('DATE_FORMAT(timestamp, ?) as period, COUNT(*) as count', [$dateFormat])
            ->groupBy('period')
            ->orderBy('period')
            ->get();
        $labels = $results->pluck('period');
        $data = $results->pluck('count');
        return response()->json([
            'labels' => $labels,
            'data' => $data,
            'group' => $group
        ]);
    }

    // GET /api/incident-type-counts
    public function incidentTypeCounts(Request $request)
    {
        $filterYear = $request->query('filterYear'); // optional, e.g. '2025'
        $query = \App\Models\Incident::query();
        if ($filterYear && $filterYear !== 'all') {
            $query->whereYear('timestamp', $filterYear);
        }
        $results = $query->select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->orderBy('type')
            ->get();

        // Map similar types to a canonical label
        $typeMap = [
            'vehicular accident' => ['vehicular accident', 'vehicular_accident', 'vehicle crash', 'vehicle_crash', 'accident'],
            // Add more mappings as needed
        ];

        $grouped = [];
        foreach ($results as $row) {
            $raw = strtolower(str_replace('_', ' ', $row->type));
            $label = null;
            foreach ($typeMap as $canonical => $aliases) {
                if (in_array($raw, $aliases)) {
                    $label = $canonical;
                    break;
                }
            }
            if (!$label) {
                $label = ucwords($raw);
            } else {
                $label = ucwords($label);
            }
            if (!isset($grouped[$label])) {
                $grouped[$label] = 0;
            }
            $grouped[$label] += $row->count;
        }
        $labels = array_keys($grouped);
        $data = array_values($grouped);
        return response()->json([
            'labels' => $labels,
            'data' => $data
        ]);
    }
}
