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
                    <span x-data="{ count: 0, target: 123 }" x-init="let i = setInterval(() => { if(count < target) count++; else clearInterval(i); }, 10)" class="text-3xl font-bold text-blue-900" x-text="count"></span>
                    <span class="mt-2 text-blue-700">Total Cases Tite</span>
                </div>
                <div class="bg-yellow-100 rounded-lg shadow p-6 flex flex-col items-center">
                    <span x-data="{ count: 0, target: 12 }" x-init="let i = setInterval(() => { if(count < target) count++; else clearInterval(i); }, 30)" class="text-3xl font-bold text-yellow-900" x-text="count"></span>
                    <span class="mt-2 text-yellow-700">Current Issues</span>
                </div>
                <div class="bg-green-100 rounded-lg shadow p-6 flex flex-col items-center">
                    <span x-data="{ count: 0, target: 111 }" x-init="let i = setInterval(() => { if(count < target) count++; else clearInterval(i); }, 10)" class="text-3xl font-bold text-green-900" x-text="count"></span>
                    <span class="mt-2 text-green-700">Completed Issues</span>
                </div>
                <div class="bg-indigo-100 rounded-lg shadow p-6 flex flex-col items-center">
                    <span x-data="{ count: 0, target: 8 }" x-init="let i = setInterval(() => { if(count < target) count++; else clearInterval(i); }, 80)" class="text-3xl font-bold text-indigo-900" x-text="count"></span>
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
                    <!-- Incident Card 1 -->
                    <div class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3 shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="h-4 w-4 rounded-full inline-block blinking-dot bg-yellow-400"></span>
                            <div>
                                <div class="font-semibold text-gray-700">Fire</div>
                                <div class="text-xs text-gray-500">2025-07-23 10:15 AM</div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="inline-block px-3 py-1 text-xs font-semibold bg-yellow-200 text-yellow-800 rounded-full mb-1">Ongoing</span>
                            <span class="text-xs text-gray-500">Juan Dela Cruz</span>
                        </div>
                    </div>
                    <!-- Incident Card 2 -->
                    <div class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3 shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="h-4 w-4 rounded-full inline-block bg-green-400"></span>
                            <div>
                                <div class="font-semibold text-gray-700">Flood</div>
                                <div class="text-xs text-gray-500">2025-07-22 08:30 PM</div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="inline-block px-3 py-1 text-xs font-semibold bg-green-200 text-green-800 rounded-full mb-1">Resolved</span>
                            <span class="text-xs text-gray-500">Maria Santos</span>
                        </div>
                    </div>
                    <!-- Incident Card 3 -->
                    <div class="flex items-center justify-between bg-gray-50 rounded-lg px-4 py-3 shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="h-4 w-4 rounded-full inline-block blinking-dot bg-red-400"></span>
                            <div>
                                <div class="font-semibold text-gray-700">Medical</div>
                                <div class="text-xs text-gray-500">2025-07-22 07:10 PM</div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="inline-block px-3 py-1 text-xs font-semibold bg-red-200 text-red-800 rounded-full mb-1">Pending</span>
                            <span class="text-xs text-gray-500">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
