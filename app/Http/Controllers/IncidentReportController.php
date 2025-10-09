<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;
use App\Models\Incident;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IncidentReportController extends Controller
{
    public function generate(Request $request)
    {
        $source = $request->input('source', 'mobile');
        $period = $request->input('period', 'day');
        $date = $request->input('date', now()->toDateString());
        $typeFilter = $request->input('typeFilter', '');
        $statusFilter = $request->input('statusFilter', '');
        $page = $request->input('page', 1);
        $perPage = $request->input('perPage', 10);

        // Build query
        $query = Incident::query()->where('source', $source);
        if ($typeFilter) $query->where('type', $typeFilter);
        if ($statusFilter) $query->where('status', $statusFilter);

        // Date filtering anchored to selected date
        $anchor = Carbon::parse($date);
        switch ($period) {
            case 'day':
                $query->whereDate('timestamp', $anchor->toDateString());
                break;
            case 'week':
                $query->whereBetween('timestamp', [
                    $anchor->copy()->startOfWeek(),
                    $anchor->copy()->endOfWeek(),
                ]);
                break;
            case 'month':
                $query->whereYear('timestamp', $anchor->year)
                    ->whereMonth('timestamp', $anchor->month);
                break;
            case 'year':
                $query->whereYear('timestamp', $anchor->year);
                break;
        }

        // For report/PDF, fetch all filtered incidents (no pagination)
        $incidents = $query->orderBy('timestamp', 'desc')->get();

        // Chart data
        // Build base filtered query for charts (clone of $query without pagination)
        $baseForCharts = (clone $query);
        $severityData = [];
        if ($source === 'mobile') {
            $severityData = (clone $baseForCharts)
                ->selectRaw('severity, count(*) as count')
                ->groupBy('severity')
                ->pluck('count', 'severity')
                ->toArray();
        }
        $statusData = (clone $baseForCharts)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Type distribution
        $typeData = (clone $baseForCharts)
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        // Time series data depending on period
        $timeSeries = ['labels' => [], 'data' => []];
        if ($period === 'day') {
            $buckets = (clone $baseForCharts)
                ->selectRaw('HOUR(timestamp) as bucket, COUNT(*) as count')
                ->groupBy('bucket')
                ->pluck('count', 'bucket')
                ->toArray();
            for ($h = 0; $h < 24; $h++) {
                $timeSeries['labels'][] = sprintf('%02d:00', $h);
                $timeSeries['data'][] = (int)($buckets[$h] ?? 0);
            }
        } elseif ($period === 'week') {
            $start = $anchor->copy()->startOfWeek();
            $end = $anchor->copy()->endOfWeek();
            $buckets = (clone $baseForCharts)
                ->selectRaw('DATE(timestamp) as bucket, COUNT(*) as count')
                ->groupBy('bucket')
                ->pluck('count', 'bucket')
                ->toArray();
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $label = $d->format('Y-m-d');
                $timeSeries['labels'][] = $label;
                $timeSeries['data'][] = (int)($buckets[$label] ?? 0);
            }
        } elseif ($period === 'month') {
            $start = $anchor->copy()->startOfMonth();
            $end = $anchor->copy()->endOfMonth();
            $buckets = (clone $baseForCharts)
                ->selectRaw('DATE(timestamp) as bucket, COUNT(*) as count')
                ->groupBy('bucket')
                ->pluck('count', 'bucket')
                ->toArray();
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $label = $d->format('Y-m-d');
                $timeSeries['labels'][] = $label;
                $timeSeries['data'][] = (int)($buckets[$label] ?? 0);
            }
        } else { // year
            $buckets = (clone $baseForCharts)
                ->selectRaw('MONTH(timestamp) as bucket, COUNT(*) as count')
                ->groupBy('bucket')
                ->pluck('count', 'bucket')
                ->toArray();
            for ($m = 1; $m <= 12; $m++) {
                $timeSeries['labels'][] = Carbon::create($anchor->year, $m, 1)->format('Y-m');
                $timeSeries['data'][] = (int)($buckets[$m] ?? 0);
            }
        }

        // Render Blade view to HTML
        $html = View::make('incidents.report', [
            'incidents' => $incidents,
            'source' => $source,
            'period' => $period,
            'date' => $date,
            'severityData' => $severityData,
            'statusData' => $statusData,
            'typeData' => $typeData,
            'timeSeries' => $timeSeries,
        ])->render();

        // Generate PDF using Browsershot
        $pdf = Browsershot::html($html)
            ->setOption('args', ['--no-sandbox'])
            ->format('A4')
            ->margins(10, 10, 10, 10)
            ->waitUntilNetworkIdle()
            ->setDelay(1200)
            ->pdf();

        return response($pdf)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="incident-report-' . $source . '-' . $period . '-' . now()->format('Ymd_His') . '.pdf"');
    }
}
