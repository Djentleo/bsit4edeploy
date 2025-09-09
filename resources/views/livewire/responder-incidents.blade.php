<div>
    <h2 class="text-xl font-bold mb-4">Assigned Incidents</h2>
    @if(count($incidents) === 0)
    <div class="text-gray-500">No incidents assigned to you.</div>
    @else
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded shadow">
            <thead class="bg-blue-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700">Type</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700">Status</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700">Location</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700">Date & Time</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700">Description</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($incidents as $key => $incident)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $incident['type'] ?? $incident['event'] ?? 'N/A' }}</td>
                    <td class="px-4 py-2">{{ $incident['status'] ?? 'N/A' }}</td>
                    <td class="px-4 py-2">{{ $incident['location'] ?? $incident['camera_name'] ?? 'N/A' }}</td>
                    <td class="px-4 py-2">{{ $incident['datetime'] ?? $incident['date_time'] ?? $incident['timestamp']
                        ?? 'N/A' }}</td>
                    <td class="px-4 py-2">{{ $incident['incident_description'] ?? $incident['screenshot'] ?? 'N/A' }}
                    </td>
                    <td class="px-4 py-2">
                        <button class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1 rounded"
                            wire:click="showIncident({{ $key }})">View</button>
                    </td>
                </tr>
                @endforeach
                @include('livewire.responder-incident-modal')
            </tbody>
        </table>
    </div>
    @endif
</div>