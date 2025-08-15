<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Summary Cards with Counter-Up Animation -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-blue-100 rounded-lg shadow p-6 flex flex-col items-center">
                    <span x-data="{ count: 0, target: {{ $summaryData['total_cases'] }} }" x-init="let i = setInterval(() => { if(count < target) count++; else clearInterval(i); }, 10)" class="text-3xl font-bold text-blue-900" x-text="count"></span>
                    <span class="mt-2 text-blue-700">Total Cases</span>
                </div>
                <div class="bg-yellow-100 rounded-lg shadow p-6 flex flex-col items-center">
                    <span x-data="{ count: 0, target: {{ $summaryData['current_issues'] }} }" x-init="let i = setInterval(() => { if(count < target) count++; else clearInterval(i); }, 30)" class="text-3xl font-bold text-yellow-900" x-text="count"></span>
                    <span class="mt-2 text-yellow-700">Current Issues</span>
                </div>
                <div class="bg-green-100 rounded-lg shadow p-6 flex flex-col items-center">
                    <span x-data="{ count: 0, target: {{ $summaryData['completed_issues'] }} }" x-init="let i = setInterval(() => { if(count < target) count++; else clearInterval(i); }, 10)" class="text-3xl font-bold text-green-900" x-text="count"></span>
                    <span class="mt-2 text-green-700">Completed Issues</span>
                </div>
                <div class="bg-indigo-100 rounded-lg shadow p-6 flex flex-col items-center">
                    <span x-data="{ count: 0, target: {{ $summaryData['responders'] }} }" x-init="let i = setInterval(() => { if(count < target) count++; else clearInterval(i); }, 80)" class="text-3xl font-bold text-indigo-900" x-text="count"></span>
                    <span class="mt-2 text-indigo-700">Responders</span>
                </div>
            </div>

            <style>
                .blinking-dot {
                    animation: blink 1.2s infinite;
                }
                @keyframes blink {
                    0%, 100% { opacity: 1; }
                    50% { opacity: 0.2; }
                }
            </style>
            <!-- Recent Incidents (Wireframe Style) -->
            <div class="bg-white rounded-lg shadow p-6 mt-8">
                <h3 class="text-2xl font-bold text-gray-700 mb-4 border-b pb-2">Recent Incidents</h3>
                <div class="space-y-4">
                    @foreach ($incidents as $incident)
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3 shadow-sm">
                            <div class="flex items-center gap-3">
                                <span class="h-4 w-4 rounded-full inline-block blinking-dot" style="background-color: {{ $incident['status'] === 'ongoing' ? '#FACC15' : ($incident['status'] === 'resolved' ? '#4ADE80' : '#F87171') }};"></span>
                                <div>
                                    <div class="font-semibold text-gray-700">{{ ucfirst($incident['type']) }}</div>
                                    <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($incident['timestamp'])->format('Y-m-d h:i A') }}</div>
                                </div>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full mb-1" style="background-color: {{ $incident['status'] === 'ongoing' ? '#FEF3C7' : ($incident['status'] === 'resolved' ? '#D1FAE5' : '#FEE2E2') }}; color: {{ $incident['status'] === 'ongoing' ? '#B45309' : ($incident['status'] === 'resolved' ? '#065F46' : '#991B1B') }};">{{ ucfirst($incident['status']) }}</span>
                                <span class="text-xs text-gray-500">{{ $incident['reporter_name'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
