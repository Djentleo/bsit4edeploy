<div>
    <h2 class="text-xl font-bold mb-4">Assigned Incidents</h2>
    @if(count($incidents) === 0)
        <div class="text-gray-500">No incidents assigned to you.</div>
    @else
        <div class="space-y-4">
            @foreach($incidents as $incident)
                <div class="p-4 border rounded bg-white shadow">
                    <div><strong>Type:</strong> 
                        {{ $incident['type'] ?? $incident['event'] ?? 'N/A' }}
                    </div>
                    <div><strong>Status:</strong> 
                        {{ $incident['status'] ?? 'N/A' }}
                    </div>
                    <div><strong>Location:</strong> 
                        {{ $incident['location'] ?? $incident['camera_name'] ?? 'N/A' }}
                    </div>
                    <div><strong>Date & Time:</strong> 
                        {{ $incident['datetime'] ?? $incident['date_time'] ?? $incident['timestamp'] ?? 'N/A' }}
                    </div>
                    <div><strong>Description:</strong> 
                        {{ $incident['description'] ?? $incident['screenshot'] ?? 'N/A' }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
