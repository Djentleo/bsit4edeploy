<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-white leading-tight">
                {{ __('Dashboard') }}
            </h2>
            @auth
            @if(auth()->user()->role === 'admin')
            <div class="ml-4">
                @livewire('admin-notification-bell')
            </div>
            @endif
            @endauth
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Summary Cards with Counter-Up Animation -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-blue-100 dark:bg-blue-900 rounded-lg shadow p-6 flex flex-col items-center">
                    <span x-data="{ count: 0, target: {{ $summaryData['total_cases'] ?? 0 }} }"
                        x-init="let i = setInterval(() => { if(count < target) count++; else clearInterval(i); }, 10)"
                        class="text-3xl font-bold text-blue-900 dark:text-white" x-text="count"></span>
                    <span class="mt-2 text-blue-700 dark:text-blue-200">Total Cases</span>
                </div>
                <div class="bg-yellow-100 dark:bg-yellow-900 rounded-lg shadow p-6 flex flex-col items-center">
                    <span x-data="{ count: 0, target: {{ $summaryData['current_issues'] ?? 0 }} }"
                        x-init="let i = setInterval(() => { if(count < target) count++; else clearInterval(i); }, 30)"
                        class="text-3xl font-bold text-yellow-900 dark:text-white" x-text="count"></span>
                    <span class="mt-2 text-yellow-700 dark:text-yellow-200">Current Issues</span>
                </div>
                <div class="bg-green-100 dark:bg-green-900 rounded-lg shadow p-6 flex flex-col items-center">
                    <span x-data="{ count: 0, target: {{ $summaryData['completed_issues'] ?? 0 }} }"
                        x-init="let i = setInterval(() => { if(count < target) count++; else clearInterval(i); }, 10)"
                        class="text-3xl font-bold text-green-900 dark:text-white" x-text="count"></span>
                    <span class="mt-2 text-green-700 dark:text-green-200">Completed Issues</span>
                </div>
                <div class="bg-indigo-100 dark:bg-indigo-900 rounded-lg shadow p-6 flex flex-col items-center">
                    <span x-data="{ count: 0, target: {{ $summaryData['responders'] ?? 0 }} }"
                        x-init="let i = setInterval(() => { if(count < target) count++; else clearInterval(i); }, 80)"
                        class="text-3xl font-bold text-indigo-900 dark:text-white" x-text="count"></span>
                    <span class="mt-2 text-indigo-700 dark:text-indigo-200">Responders</span>
                </div>
            </div>

            <!-- Dashboard Charts Section - 2x2 Grid Layout -->
            <div class="mt-8">
                <!-- Global Filters for All Charts -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-3">
                    <h1 class="text-lg font-semibold text-gray-700 dark:text-white">Dashboard Charts</h1>
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Granularity:</span>
                        <div class="relative">
                            <select id="groupingSelect"
                                class="appearance-none inline-flex items-center px-3 py-1.5 pr-8 text-sm font-medium text-slate-700 bg-slate-50 border border-slate-300 rounded-lg hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 cursor-pointer">
                                <option value="day">Day</option>
                                <option value="week">Week</option>
                                <option value="month" selected>Month</option>
                                <option value="year">Year</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"></div>
                        </div>
                        <div class="relative">
                            <select id="yearFilterSelect"
                                class="appearance-none inline-flex items-center px-3 py-1.5 pr-8 text-sm font-medium text-slate-700 bg-slate-50 border border-slate-300 rounded-lg hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200 cursor-pointer">
                                <option value="all" selected>All Years</option>
                                <option value="2025">2025</option>
                                <option value="2024">2024</option>
                                <option value="2023">2023</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"></div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-6">
                    <!-- Top Left: Incidents Over Time -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-3">
                            <div class="flex items-center gap-6">
                                <h3 class="text-sm font-bold text-gray-700 dark:text-white">Incidents Over Time</h3>
                            </div>
                            <button id="toggleChartType"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-indigo-600 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 transition-all duration-200 shadow-sm hover:shadow">
                                <i class="fas fa-chart-line"></i> Line/Bar
                            </button>
                        </div>
                        <div style="height: 300px;">
                            <canvas id="incidentOverTimeChart"></canvas>
                        </div>
                    </div>

                    <!-- Bottom Left: By Type -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-white mb-4 text-center">By Type</h3>
                        <div style="height: 300px;">
                            <canvas id="incidentTypeChart"></canvas>
                        </div>
                    </div>

                    <!-- Top Right: By Status -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-white mb-4 text-center">By Status</h3>
                        <div style="height: 300px;">
                            <canvas id="incidentStatusChart"></canvas>
                        </div>
                    </div>

                    <!-- Bottom Right: By Severity -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-white mb-4 text-center">By Severity
                        </h3>
                        <div style="height: 300px;">
                            <canvas id="incidentSeverityChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                .blinking-dot {
                    animation: blink 1.2s infinite;
                }

                @keyframes blink {

                    0%,
                    100% {
                        opacity: 1;
                    }

                    50% {
                        opacity: 0.2;
                    }
                }
            </style>
            <!-- Recent Incidents (Wireframe Style) -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mt-8">
                <h3 class="text-2xl font-bold text-gray-700 dark:text-white mb-4 border-b pb-2">Recent Incidents</h3>
                <div class="space-y-4">
                    @forelse ($incidents ?? [] as $incident)
                    <div
                        class="flex items-center justify-between bg-gray-50 dark:bg-gray-700 rounded-lg px-4 py-3 shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="h-4 w-4 rounded-full inline-block blinking-dot"
                                style="background-color: {{ ($incident['status'] ?? '') === 'ongoing' ? '#FACC15' : (($incident['status'] ?? '') === 'resolved' ? '#4ADE80' : '#F87171') }};"></span>
                            <div>
                                <div class="font-semibold text-gray-700 dark:text-white flex items-center gap-2">
                                    {{ ucfirst($incident['type'] ?? $incident['event'] ?? '') }}
                                    <span
                                        class="ml-2 px-2 py-0.5 rounded text-xs font-semibold {{ $incident['display_source'] === 'CCTV' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-200' : 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200' }}">
                                        {{ $incident['display_source'] ?? '' }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-200">{{ !empty($incident['timestamp'])
                                    ?
                                    \Carbon\Carbon::parse($incident['timestamp'])->format('Y-m-d h:i A') : '' }}</div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full mb-1"
                                style="background-color: {{ ($incident['status'] ?? '') === 'ongoing' ? '#FEF3C7' : (($incident['status'] ?? '') === 'resolved' ? '#D1FAE5' : '#FEE2E2') }}; color: {{ ($incident['status'] ?? '') === 'ongoing' ? '#B45309' : (($incident['status'] ?? '') === 'resolved' ? '#065F46' : '#991B1B') }};">{{
                                ucfirst($incident['status'] ?? '') }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-200">
                                @if(($incident['display_source'] ?? '') === 'CCTV')
                                {{ $incident['user_name'] ?? $incident['reporter_name'] ?? '' }}
                                @else
                                {{ $incident['reporter_name'] ?? '' }}
                                @endif
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-gray-400 dark:text-gray-200 py-8">No recent incidents found.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


<!-- Chart.js CDN (UMD build for direct <script> usage) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.5.0/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js failed to load');
        return;
    }

// Theme-aware helpers
function isDarkMode() {
    return document.documentElement.classList.contains('dark');
}
function chartColors() {
    const dark = isDarkMode();
    return {
        grid: dark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)',
        tick: dark ? '#d1d5db' : '#6b7280',
        tooltipBg: dark ? 'rgba(17,24,39,0.9)' : 'rgba(0,0,0,0.8)',
        tooltipText: '#ffffff',
        primary: '#3b82f6',
        status: ['#f59e0b', '#3b82f6', '#10b981'],
        severity: ['#10b981', '#facc15', '#f97316', '#dc2626']
    };
}

let chartType = 'line';
const overTimeCanvas = document.getElementById('incidentOverTimeChart');
if (!overTimeCanvas) {
    console.error('incidentOverTimeChart canvas not found');
    return;
}
const ctxOverTime = overTimeCanvas.getContext('2d');

// Show loading state
let colors = chartColors();
let overTimeChart = new Chart(ctxOverTime, {
    type: chartType,
    data: {
        labels: [],
        datasets: [{
            label: 'Incidents',
            data: [],
            borderColor: colors.primary,
            backgroundColor: 'rgba(59,130,246,0.1)',
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointBackgroundColor: colors.primary,
            pointBorderColor: '#ffffff',
            pointBorderWidth: 2,
            pointHoverRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        scales: {
            x: { grid: { display: false }, ticks: { color: colors.tick, font: { size: 12 } } },
            y: { beginAtZero: true, grid: { color: colors.grid }, ticks: { color: colors.tick, font: { size: 12 } } }
        },
        plugins: { legend: { display: false }, tooltip: { backgroundColor: colors.tooltipBg, titleColor: colors.tooltipText, bodyColor: colors.tooltipText, cornerRadius: 6 } }
    }
});



function fetchIncidentsOverTime(group = 'month', filterYear = '') {
    let url = `/api/incidents-over-time?group=${group}`;
    if (filterYear && filterYear !== 'all') {
        url += `&filterYear=${filterYear}`;
    }
    fetch(url)
        .then(res => res.json())
        .then(json => {
            if (json.labels && json.data) {
                overTimeChart.data.labels = json.labels;
                overTimeChart.data.datasets[0].data = json.data;
                overTimeChart.update();
            } else {
                console.error('API returned invalid data', json);
            }
        })
        .catch(err => {
            console.error('Failed to fetch incidents over time', err);
        });
}


// Initial fetch (default: month, all years)
const groupingSelect = document.getElementById('groupingSelect');
const yearSelect = document.getElementById('yearFilterSelect');
let currentGroup = groupingSelect ? groupingSelect.value : 'month';
let currentYear = yearSelect ? yearSelect.value : 'all';
fetchIncidentsOverTime(currentGroup, currentYear);

// Listen for grouping and year changes
function reloadAllCharts() {
    fetchIncidentsOverTime(currentGroup, currentYear);
    fetchIncidentStatusChart(currentYear);
    fetchIncidentTypeChart(currentYear);
    fetchIncidentSeverityChart(currentYear);
}
if (groupingSelect) {
    groupingSelect.addEventListener('change', function(e) {
        currentGroup = e.target.value;
        reloadAllCharts();
    });
}
if (yearSelect) {
    yearSelect.addEventListener('change', function(e) {
        currentYear = e.target.value;
        reloadAllCharts();
    });
}

// Auto-refresh all charts every 5 seconds
setInterval(() => {
    reloadAllCharts();
}, 5000);

document.getElementById('toggleChartType').onclick = function() {
    chartType = chartType === 'line' ? 'bar' : 'line';
    // Save current data and labels
    const currentLabels = overTimeChart.data.labels;
    const currentData = overTimeChart.data.datasets[0].data;
    overTimeChart.destroy();
    overTimeChart = new Chart(ctxOverTime, {
        type: chartType,
        data: {
            labels: currentLabels,
            datasets: [{
                label: 'Incidents',
                data: currentData,
                borderColor: '#3b82f6',
                backgroundColor: chartType === 'bar' ? 'rgba(59,130,246,0.8)' : 'rgba(59,130,246,0.1)',
                fill: chartType === 'line',
                tension: 0.4,
                pointRadius: chartType === 'line' ? 4 : 0,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            scales: {
                x: { 
                    grid: { display: false },
                    ticks: { color: '#6b7280', font: { size: 12 } }
                },
                y: { 
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.08)' },
                    ticks: { color: '#6b7280', font: { size: 12 } }
                }
            },
            plugins: { 
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    cornerRadius: 6
                }
            }
        }
    });
};

// Status Pie Chart (Dynamic)
const statusCanvas = document.getElementById('incidentStatusChart');
const ctxStatus = statusCanvas ? statusCanvas.getContext('2d') : null;
let statusChart;
function fetchIncidentStatusChart(filterYear = '') {
    if (!ctxStatus) return;
    let url = '/api/incident-status-counts';
    if (filterYear && filterYear !== 'all') {
        url += `?filterYear=${filterYear}`;
    }
    fetch(url)
        .then(res => res.json())
        .then(json => {
            const labels = json.labels || [];
            const data = json.data || [];
            // Color palette for statuses: new, dispatched, resolved
            const bgColors = chartColors().status;
            if (statusChart) {
                statusChart.data.labels = labels;
                statusChart.data.datasets[0].data = data;
                statusChart.data.datasets[0].backgroundColor = bgColors.slice(0, labels.length);
                statusChart.update();
            } else {
                statusChart = new Chart(ctxStatus, {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: bgColors.slice(0, labels.length),
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    fontSize: 10,
                                    padding: 8,
                                    usePointStyle: true
                                }
                            }
                        }
                    }
                });
            }
        })
        .catch(err => {
            console.error('Failed to fetch incident status chart', err);
        });
}

// Initial fetch (default: all years)
fetchIncidentStatusChart(currentYear);

// Type Doughnut Chart (Dynamic)
const typeCanvas = document.getElementById('incidentTypeChart');
const ctxType = typeCanvas ? typeCanvas.getContext('2d') : null;
let typeChart;
function fetchIncidentTypeChart(filterYear = '') {
    if (!ctxType) return;
    let url = '/api/incident-type-counts';
    if (filterYear && filterYear !== 'all') {
        url += `?filterYear=${filterYear}`;
    }
    fetch(url)
        .then(res => res.json())
        .then(json => {
            const labels = json.labels || [];
            const data = json.data || [];
            // Generate color palette
            const palette = [
                '#ef4444', '#64748b', '#3b82f6', '#8b5cf6', '#f59e0b',
                '#10b981', '#facc15', '#f97316', '#dc2626', '#6366f1', '#eab308', '#0ea5e9'
            ];
            const paletteColors = labels.map((_, i) => palette[i % palette.length]);
            if (typeChart) {
                typeChart.data.labels = labels;
                typeChart.data.datasets[0].data = data;
                typeChart.data.datasets[0].backgroundColor = paletteColors;
                typeChart.update();
            } else {
                typeChart = new Chart(ctxType, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: paletteColors,
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '50%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    fontSize: 10,
                                    padding: 6,
                                    usePointStyle: true
                                }
                            }
                        }
                    }
                });
            }
        })
        .catch(err => {
            console.error('Failed to fetch incident type chart', err);
        });
}

// Initial fetch (default: all years)
fetchIncidentTypeChart(currentYear);

// Severity Doughnut Chart (Dynamic)
const severityCanvas = document.getElementById('incidentSeverityChart');
const ctxSeverity = severityCanvas ? severityCanvas.getContext('2d') : null;
let severityChart;
function fetchIncidentSeverityChart(filterYear = '') {
    if (!ctxSeverity) return;
    let url = '/api/incident-severity-counts';
    if (filterYear && filterYear !== 'all') {
        url += `?filterYear=${filterYear}`;
    }
    fetch(url)
        .then(res => res.json())
        .then(json => {
            // Always use these labels/colors for consistency
            const labels = ['Low', 'Medium', 'High', 'Critical'];
            const apiLabels = (json.labels || []).map(l => l.toLowerCase());
            const apiData = json.data || [];
            // Map API data to fixed label order
            const severityOrder = ['low', 'medium', 'high', 'critical'];
            const data = severityOrder.map(l => {
                const idx = apiLabels.indexOf(l);
                return idx !== -1 ? apiData[idx] : 0;
            });
            const bgColors = chartColors().severity;
            if (severityChart) {
                severityChart.data.labels = labels;
                severityChart.data.datasets[0].data = data;
                severityChart.data.datasets[0].backgroundColor = bgColors;
                severityChart.update();
            } else {
                severityChart = new Chart(ctxSeverity, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: bgColors,
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '50%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    fontSize: 10,
                                    padding: 6,
                                    usePointStyle: true
                                }
                            }
                        }
                    }
                });
            }
        })
        .catch(err => {
            console.error('Failed to fetch incident severity chart', err);
        });
}

// Initial fetch (default: all years)
fetchIncidentSeverityChart(currentYear);
// Update charts when theme changes
window.addEventListener('theme-changed', () => {
    colors = chartColors();
    if (overTimeChart) {
        overTimeChart.options.scales.x.ticks.color = colors.tick;
        overTimeChart.options.scales.y.ticks.color = colors.tick;
        overTimeChart.options.scales.y.grid.color = colors.grid;
        overTimeChart.options.plugins.tooltip.backgroundColor = colors.tooltipBg;
        overTimeChart.data.datasets[0].borderColor = colors.primary;
        overTimeChart.data.datasets[0].pointBackgroundColor = colors.primary;
        overTimeChart.update();
    }
    if (statusChart) {
        statusChart.data.datasets[0].backgroundColor = chartColors().status;
        statusChart.update();
    }
    if (severityChart) {
        severityChart.data.datasets[0].backgroundColor = chartColors().severity;
        severityChart.update();
    }
});
});
</script>