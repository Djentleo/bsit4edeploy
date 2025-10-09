@php
// Chart.js CDN
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Incident Report</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>
    <style>
        body {
            background: #fff;
            color: #111827;
            font-family: 'Figtree', Arial, sans-serif;
        }

        h1,
        h2,
        .title {
            font-family: 'Figtree', Arial, sans-serif;
        }

        .container {
            max-width: 900px;
            padding: 0 12px;
        }

        .grid {
            display: grid;
            gap: 10px;
        }

        .grid-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        @media (max-width: 700px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px;
            box-sizing: border-box;
        }

        .chart-h {
            height: 200px !important;
            max-width: 100% !important;
        }

        .title {
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 6px;
            color: #111827;
        }

        table {
            font-size: 12px;
        }

        th,
        td {
            padding: 7px 8px;
        }
    </style>
</head>

<body>
    <div class="container mx-auto py-8">
        <!-- Header Banner -->
        <div class="flex items-center mb-6 border-b pb-3">
            <img src="https://scontent.fmnl4-7.fna.fbcdn.net/v/t39.30808-6/559425642_122149487702748041_1971237142326372742_n.jpg?_nc_cat=104&ccb=1-7&_nc_sid=127cfc&_nc_ohc=1ZlCB5OyoS4Q7kNvwF3H_QL&_nc_oc=AdkCJjuo1W8e21jqN1HXoHdVIMqzuvEfSC3_dHAtpEuEJ4M6p8AYDNGgbo8l6qOply4&_nc_zt=23&_nc_ht=scontent.fmnl4-7.fna&_nc_gid=SLrst2XipX565mZFZLZTXA&oh=00_AfejguN_A1MYKrJao5c-71ZS_a9EXP71Bc1vLeuwJIh9cw&oe=68ED9CCD"
                alt="Barangay Baritan Logo" class="mr-4" style="width:60px; height:60px; object-fit:contain;" />
            <div>
                <div class="font-bold text-lg">Barangay Baritan, Malabon City</div>
                <div class="font-semibold text-base">AI-Based Incident Report and Management System</div>
                <div class="text-xs text-gray-500 mt-1">Report Generated: {{ $date }}</div>
            </div>
        </div>

        <h1 class="text-2xl font-bold mb-4 text-left">
            {{ ucfirst($source) }} Incident Report
            @if($period === 'year' && !empty($allYears))
            (All Years)
            @else
            ({{ ucfirst($period) }})
            @endif
        </h1>

        <div class="grid grid-2 mb-8">
            <div class="card">
                <div class="title">Incidents Over Time</div>
                <canvas id="timeChart" class="chart-h"></canvas>
            </div>
            <div class="card">
                <div class="title">Incidents by Type</div>
                <canvas id="typeChart" class="chart-h"></canvas>
            </div>
            <div class="card">
                <div class="title">Incidents by Status</div>
                <canvas id="statusChart" class="chart-h"></canvas>
            </div>
            @if($source === 'mobile')
            <div class="card">
                <div class="title">Incidents by Severity</div>
                <canvas id="severityChart" class="chart-h"></canvas>
            </div>
            @endif
        </div>

        <h2 class="text-xl font-semibold mb-2">Incident Table</h2>
        <table class="min-w-full bg-white border border-gray-300 mb-8">
            <thead>
                <tr>
                    <th class="px-4 py-2 border">ID</th>
                    <th class="px-4 py-2 border">Reporter Name</th>
                    <th class="px-4 py-2 border">Type</th>
                    <th class="px-4 py-2 border">Location</th>
                    <th class="px-4 py-2 border">Status</th>
                    <th class="px-4 py-2 border">Timestamp</th>
                    @if($source === 'mobile')
                    <th class="px-4 py-2 border">Severity</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($incidents as $incident)
                <tr>
                    <td class="px-4 py-2 border">{{ $incident->id }}</td>
                    <td class="px-4 py-2 border">{{ $incident->reporter_name }}</td>
                    <td class="px-4 py-2 border">{{ $incident->type }}</td>
                    <td class="px-4 py-2 border">{{ $incident->location }}</td>
                    <td class="px-4 py-2 border">{{ $incident->status }}</td>
                    <td class="px-4 py-2 border">{{ $incident->timestamp }}</td>
                    @if($source === 'mobile')
                    <td class="px-4 py-2 border">{{ $incident->severity }}</td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script>
        // Prepare chart data from controller
    const timeSeries = @json($timeSeries);
    const typeData = @json($typeData);
    const statusData = @json($statusData);
    @if($source === 'mobile')
    const severityData = @json($severityData);
    @endif

    // Register datalabels plugin
    Chart.register(window.ChartDataLabels);

    // Time Chart (line for most, bar for all years)
    let timeChartType = 'line';
    if ('{{ $period }}' === 'year' && {{ !empty($allYears) ? 'true' : 'false' }}) {
        timeChartType = 'bar';
    }
    new Chart(document.getElementById('timeChart'), {
        type: timeChartType,
        data: {
            labels: timeSeries.labels,
            datasets: [{
                label: 'Incidents',
                data: timeSeries.data,
                backgroundColor: '#6366F1',
                borderColor: '#6366F1',
                fill: timeChartType === 'line' ? false : true,
                tension: 0.3,
                pointRadius: 2,
            }]
        },
        options: {
            maintainAspectRatio: false,
            layout: { padding: 8 },
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { autoSkip: true, maxTicksLimit: 7, font: { size: 10 } }, grid: { color: '#eef2f7' } },
                y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { color: '#eef2f7' } }
            }
        }
    });

    // Type Chart (always)
    new Chart(document.getElementById('typeChart'), {
        type: 'pie',
        data: {
            labels: Object.keys(typeData),
            datasets: [{ data: Object.values(typeData), backgroundColor: ['#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#f472b6', '#f97316', '#14b8a6', '#a78bfa'] }]
        },
        options: {
            maintainAspectRatio: false,
            layout: { padding: 8 },
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } },
                datalabels: {
                    color: '#222',
                    font: { weight: 'bold', size: 10 },
                    formatter: (value, ctx) => {
                        let sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                        let pct = sum ? (value / sum * 100) : 0;
                        return pct.toFixed(1) + '%';
                    }
                }
            }
        }
    });

        // Status Chart
        new Chart(document.getElementById('statusChart'), {
            type: 'pie',
            data: {
                labels: Object.keys(statusData),
                datasets: [{
                    data: Object.values(statusData),
                    backgroundColor: ['#3b82f6', '#fbbf24', '#10b981', '#ef4444'],
                }]
            },
            options: {
                maintainAspectRatio: false,
                layout: { padding: 8 },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } },
                    datalabels: {
                        color: '#222',
                        font: { weight: 'bold', size: 10 },
                        formatter: (value, ctx) => {
                            let sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            let pct = sum ? (value / sum * 100) : 0;
                            return pct.toFixed(1) + '%';
                        }
                    }
                },
                title: { display: false }
            }
        });

        @if($source === 'mobile')
        // Severity Chart
        new Chart(document.getElementById('severityChart'), {
            type: 'pie',
            data: { labels: Object.keys(severityData), datasets: [{ data: Object.values(severityData), backgroundColor: ['#ef4444', '#f59e42', '#fbbf24', '#10b981'] }] },
            options: {
                maintainAspectRatio: false,
                layout: { padding: 8 },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } },
                    datalabels: {
                        color: '#222',
                        font: { weight: 'bold', size: 10 },
                        formatter: (value, ctx) => {
                            let sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            let pct = sum ? (value / sum * 100) : 0;
                            return pct.toFixed(1) + '%';
                        }
                    }
                }
            }
        });
        @endif
    </script>
</body>

</html>