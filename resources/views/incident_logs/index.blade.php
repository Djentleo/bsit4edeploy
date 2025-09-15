<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Incident Logs
        </h2>
    </x-slot>

    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold mb-4"></h1>
        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Resolved Incidents</h3>
            @if(count($resolvedIncidents) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead style="background-color: #1E3A8A;" class="border-b border-gray-200">
                            <tr class="text-left">
                                <th class="px-3 py-4 text-xs font-semibold text-white uppercase tracking-wider">ID</th>
                                <th class="px-3 py-4 text-xs font-semibold text-white uppercase tracking-wider">TYPE</th>
                                <th class="px-3 py-4 text-xs font-semibold text-white uppercase tracking-wider hidden sm:table-cell">LOCATION</th>
                                <th class="px-3 py-4 text-xs font-semibold text-white uppercase tracking-wider hidden md:table-cell">REPORTER</th>
                                <th class="px-3 py-4 text-xs font-semibold text-white uppercase tracking-wider">STATUS</th>
                                <th class="px-3 py-4 text-xs font-semibold text-white uppercase tracking-wider hidden lg:table-cell">DEPARTMENT</th>
                                <!-- Actions column removed -->
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-gray-200 bg-white">
                            @foreach($resolvedIncidents as $incident)
                                <tr class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-3 py-4 text-gray-900 font-medium text-sm">{{ $incident['incident_id'] ?? '-' }}</td>
                                    <td class="px-3 py-4">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold
                                            @if(($incident['type'] ?? '') === 'fire') bg-red-100 text-red-700
                                            @elseif(($incident['type'] ?? '') === 'vehicle_crash' || ($incident['type'] ?? '') === 'vehicular_accident') bg-orange-100 text-orange-700
                                            @elseif(($incident['type'] ?? '') === 'medical_emergency') bg-emerald-100 text-emerald-700
                                            @elseif(($incident['type'] ?? '') === 'disturbance') bg-blue-100 text-blue-700
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $incident['type'] ?? '-')) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 text-gray-600 text-sm hidden sm:table-cell">{{ $incident['location'] ?? '-' }}</td>
                                    <td class="px-3 py-4 text-gray-600 text-sm hidden md:table-cell">{{ $incident['reporter_name'] ?? '-' }}</td>
                                    <td class="px-3 py-4">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold
                                            @if(($incident['status'] ?? '') === 'new') bg-green-100 text-green-700
                                            @elseif(($incident['status'] ?? '') === 'dispatched') bg-yellow-100 text-yellow-700
                                            @elseif(($incident['status'] ?? '') === 'resolved') bg-gray-100 text-gray-700
                                            @endif">
                                            {{ ucfirst($incident['status'] ?? '-') }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 text-gray-600 font-medium text-sm hidden lg:table-cell">{{ $incident['department'] ?? '-' }}</td>
                                    <!-- Actions button removed -->
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-600">No resolved incidents found.</p>
            @endif
        </div>
    </div>
</x-app-layout>