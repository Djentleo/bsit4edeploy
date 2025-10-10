<div class="max-w-5xl mx-auto p-6 space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold mb-2">Incident Details</h2>
                <p class="text-blue-100">ID: {{ $incident['firebase_id'] ?? $incident['id'] ?? 'N/A' }}</p>
            </div>
            <div class="text-right">
                <div class="text-sm text-blue-100 mb-1">Last Updated</div>
                <div class="text-lg font-semibold">{{ now()->format('M d, Y H:i') }}</div>
            </div>
        </div>
    </div>

    <!-- Main Details Card -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="bg-gray-50 dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Incident Information</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div class="space-y-1">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Reporter Name</div>
                    <div class="text-gray-900 dark:text-white font-medium">{{ $incident['reporter_name'] ?? 'N/A' }}</div>
                </div>
                <div class="space-y-1">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Type</div>
                    <div class="flex items-center">
                        @php
                            $typeVal = $incident['type'] ?? $incident['event'] ?? 'N/A';
                            $typeClasses = match($typeVal) {
                                'fire' => 'bg-red-100 text-red-800 border-red-200',
                                'vehicle_crash', 'vehicular_accident' => 'bg-orange-100 text-orange-800 border-orange-200',
                                'medical_emergency' => 'bg-green-100 text-green-800 border-green-200',
                                'disturbance' => 'bg-blue-100 text-blue-800 border-blue-200',
                                default => 'bg-gray-100 text-gray-800 border-gray-200',
                            };
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-sm font-medium border {{ $typeClasses }}">
                            {{ ucfirst(str_replace('_', ' ', $typeVal)) }}
                        </span>
                    </div>
                </div>
                <div class="space-y-1">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</div>
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium border
                            @if($status === 'resolved') bg-green-100 text-green-800 border-green-200
                            @elseif($status === 'en_route') bg-yellow-100 text-yellow-800 border-yellow-200
                            @elseif($status === 'closed') bg-gray-100 text-gray-800 border-gray-200
                            @else bg-blue-100 text-blue-800 border-blue-200
                            @endif">
                            {{ $statusOptions[$status] ?? ucfirst($status) }}
                        </span>
                    </div>
                </div>
                <div class="space-y-1">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Source</div>
                    <div class="text-gray-900 dark:text-white font-medium">{{ $incident['source'] ?? 'N/A' }}</div>
                </div>
                <div class="space-y-1">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Location</div>
                    <div class="text-gray-900 dark:text-white font-medium">{{ $incident['location'] ?? $incident['camera_name'] ?? 'N/A' }}</div>
                </div>
                <div class="space-y-1">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Date & Time</div>
                    <div class="text-gray-900 dark:text-white font-medium">{{ $incident['datetime'] ?? $incident['date_time'] ?? $incident['timestamp'] ?? 'N/A' }}</div>
                </div>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Description</div>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 text-gray-900 dark:text-white">
                    {{ $incident['incident_description'] ?? $incident['screenshot'] ?? 'No description available.' }}
                </div>
            </div>
            
            <!-- Status Messages -->
            @if (session()->has('status'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-green-800">{{ session('status') }}</p>
                    </div>
                </div>
            @endif

            @error('status')
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-red-800">{{ $message }}</p>
                    </div>
                </div>
            @enderror

            <!-- Update Status Button -->
            <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700 mt-6">
                <div class="flex items-center gap-3">
                    <select wire:model="selectedStatus" class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <button wire:click="updateStatus" 
                        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Update Status
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notes Section -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="bg-gray-50 dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Internal Notes</h3>
            </div>
        </div>
        <div class="p-6">
            <div class="space-y-3 max-h-40 overflow-y-auto mb-4">
                @forelse($incidentNotes as $note)
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 rounded-lg p-4 border-l-4 border-blue-500">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                    {{ substr($note['user_name'], 0, 1) }}
                                </div>
                                <div class="font-semibold text-gray-900 dark:text-white">{{ $note['user_name'] }}</div>
                            </div>
                            <div class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ $note['note'] }}</div>
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 ml-4">{{ $note['created_at'] }}</div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                    </svg>
                    <div class="text-gray-500 dark:text-gray-400">No notes yet.</div>
                </div>
                @endforelse
            </div>
            <form wire:submit.prevent="addNote" class="flex gap-3">
                <input type="text" wire:model.defer="newNote" 
                    class="flex-1 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Add a note...">
                <button type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-medium transition-colors duration-200 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Add Note
                </button>
            </form>
        </div>
    </div>

    <!-- Timeline Section -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="bg-gray-50 dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Timeline</h3>
            </div>
        </div>
        <div class="p-6">
            <div class="space-y-4 max-h-40 overflow-y-auto">
                @forelse($timeline as $item)
                <div class="relative flex items-start gap-4">
                    <div class="flex-shrink-0 w-3 h-3 bg-blue-500 rounded-full mt-2"></div>
                    <div class="flex-1 bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $item['user_name'] }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $item['created_at'] }}</span>
                        </div>
                        <div class="text-gray-700 dark:text-gray-300">
                            <span class="font-medium">{{ $item['action'] }}:</span> {{ $item['details'] }}
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-gray-500 dark:text-gray-400">No timeline entries yet.</div>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Location & Map Section (Mobile only: no camera_name) -->
    @if(!isset($incident['camera_name']))
    <!-- Evidence & Attachments Section (Mobile only) -->
    @if(!empty($incident['proof_image_url'] ?? $incident['proofImageUrl']))
    <div x-data="{ showModal: false }" class="bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-yellow-500 to-orange-600 px-6 py-4">
            <div class="flex items-center gap-2 text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                <h3 class="text-xl font-semibold">Evidence & Attachments</h3>
            </div>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Photo Evidence</label>
                <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-3 flex justify-center">
                    <img
                        src="{{ $incident['proof_image_url'] ?? $incident['proofImageUrl'] }}"
                        alt="Photo Evidence"
                        class="cursor-pointer object-cover rounded-lg shadow-lg"
                        style="width: 100%; max-width: 600px; height: 400px;"
                        @click="showModal = true"
                    >
                </div>
                <!-- Modal -->
                <div x-show="showModal" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70" @click.self="showModal = false">
                    <img
                        src="{{ $incident['proof_image_url'] ?? $incident['proofImageUrl'] }}"
                        alt="Photo Evidence Full Size"
                        class="object-contain rounded-lg shadow-2xl"
                        style="max-height: 90vh; max-width: 95vw;"
                    >
                </div>
            </div>
        </div>
    </div>
    @endif
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-4">
            <div class="flex items-center gap-2 text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <h3 class="text-xl font-semibold">Incident Location</h3>
            </div>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Address</label>
                <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        </svg>
                        <span class="text-gray-900 dark:text-white font-medium">{{ $incident['location'] ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-2">
                <div id="map" style="width: 100%; height: 400px; border-radius: 0.5rem; overflow: hidden;"></div>
            </div>
        </div>
    </div>
        <!-- Mapbox CSS & JS -->
        <link href="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css" rel="stylesheet" />
        <script src="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>
        <script src="https://unpkg.com/@mapbox/mapbox-sdk/umd/mapbox-sdk.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (window.mapboxgl && document.getElementById('map')) {
                    mapboxgl.accessToken = 'pk.eyJ1IjoiZGplbnRsZW8iLCJhIjoiY21mNnoxMDgzMGt3NjJyb20zY3dqdnRjdSJ9.OKI8RAGo7e9eRRXejMLfOA';
                    const map = new mapboxgl.Map({
                        container: 'map',
                        style: 'mapbox://styles/mapbox/streets-v11',
                        center: [120.9532, 14.6562], // Default center (Malabon)
                        zoom: 13,
                        pitch: 45,
                        bearing: -17.6
                    });
                    map.addControl(new mapboxgl.NavigationControl());
                    map.addControl(new mapboxgl.FullscreenControl());
                    map.addControl(new mapboxgl.GeolocateControl({
                        positionOptions: { enableHighAccuracy: true },
                        trackUserLocation: true,
                        showUserHeading: true
                    }));
                    map.on('load', function () {
                        const layers = map.getStyle().layers;
                        let labelLayerId;
                        for (let i = 0; i < layers.length; i++) {
                            if (layers[i].type === 'symbol' && layers[i].layout['text-field']) {
                                labelLayerId = layers[i].id;
                                break;
                            }
                        }
                        map.addLayer({
                            'id': '3d-buildings',
                            'source': 'composite',
                            'source-layer': 'building',
                            'filter': ['==', 'extrude', 'true'],
                            'type': 'fill-extrusion',
                            'minzoom': 15,
                            'paint': {
                                'fill-extrusion-color': '#aaa',
                                'fill-extrusion-height': ["get", "height"],
                                'fill-extrusion-base': ["get", "min_height"],
                                'fill-extrusion-opacity': 0.6
                            }
                        }, labelLayerId);
                    });
                    // Dynamic marker/geocode logic
                    const address = @json($incident['location'] ?? '');
                    const lat = @json($incident['latitude'] ?? null);
                    const lng = @json($incident['longitude'] ?? null);
                    if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
                        map.setCenter([lng, lat]);
                        new mapboxgl.Marker({ color: 'red' })
                            .setLngLat([lng, lat])
                            .addTo(map);
                    } else if (address) {
                        const mapboxClient = mapboxSdk({ accessToken: mapboxgl.accessToken });
                        mapboxClient.geocoding
                            .forwardGeocode({
                                query: address,
                                limit: 1
                            })
                            .send()
                            .then(function(response) {
                                if (
                                    response &&
                                    response.body &&
                                    response.body.features &&
                                    response.body.features.length
                                ) {
                                    const feature = response.body.features[0];
                                    map.setCenter(feature.center);
                                    new mapboxgl.Marker({ color: 'red' })
                                        .setLngLat(feature.center)
                                        .addTo(map);
                                }
                            });
                    }
                }
            });
        </script>
    </div>
    @endif

    <!-- Camera Location & Map Section (CCTV only) -->
    @if(isset($incident['camera_name']))
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="bg-gradient-to-r from-purple-500 to-indigo-600 px-6 py-4">
            <div class="flex items-center gap-2 text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                <h3 class="text-xl font-semibold">Camera Location</h3>
            </div>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Camera Name</label>
                <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <span class="text-gray-900 dark:text-white font-medium">{{ $incident['camera_name'] ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-2">
                <div id="cctv-map" style="width: 100%; height: 400px; border-radius: 0.5rem; overflow: hidden;"></div>
            </div>
        </div>
    </div>
        <!-- Mapbox CSS & JS -->
        <link href="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css" rel="stylesheet" />
        <script src="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>
        <script src="https://unpkg.com/@mapbox/mapbox-sdk/umd/mapbox-sdk.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (window.mapboxgl && document.getElementById('cctv-map')) {
                    mapboxgl.accessToken = 'pk.eyJ1IjoiZGplbnRsZW8iLCJhIjoiY21mNnoxMDgzMGt3NjJyb20zY3dqdnRjdSJ9.OKI8RAGo7e9eRRXejMLfOA';
                    const cctvMap = new mapboxgl.Map({
                        container: 'cctv-map',
                        style: 'mapbox://styles/mapbox/streets-v11',
                        center: [120.9532, 14.6562],
                        zoom: 13,
                        pitch: 45,
                        bearing: -17.6
                    });
                    cctvMap.addControl(new mapboxgl.NavigationControl());
                    cctvMap.addControl(new mapboxgl.FullscreenControl());
                    cctvMap.addControl(new mapboxgl.GeolocateControl({
                        positionOptions: { enableHighAccuracy: true },
                        trackUserLocation: true,
                        showUserHeading: true
                    }));
                    cctvMap.on('load', function () {
                        const layers = cctvMap.getStyle().layers;
                        let labelLayerId;
                        for (let i = 0; i < layers.length; i++) {
                            if (layers[i].type === 'symbol' && layers[i].layout['text-field']) {
                                labelLayerId = layers[i].id;
                                break;
                            }
                        }
                        cctvMap.addLayer({
                            'id': '3d-buildings',
                            'source': 'composite',
                            'source-layer': 'building',
                            'filter': ['==', 'extrude', 'true'],
                            'type': 'fill-extrusion',
                            'minzoom': 15,
                            'paint': {
                                'fill-extrusion-color': '#aaa',
                                'fill-extrusion-height': ["get", "height"],
                                'fill-extrusion-base': ["get", "min_height"],
                                'fill-extrusion-opacity': 0.6
                            }
                        }, labelLayerId);
                    });
                    // Dynamic marker/geocode logic
                    const cameraName = @json($incident['camera_name'] ?? '');
                    const lat = @json($incident['latitude'] ?? null);
                    const lng = @json($incident['longitude'] ?? null);
                    if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
                        cctvMap.setCenter([lng, lat]);
                        new mapboxgl.Marker({ color: 'red' })
                            .setLngLat([lng, lat])
                            .addTo(cctvMap);
                    } else if (cameraName) {
                        const mapboxClient = mapboxSdk({ accessToken: mapboxgl.accessToken });
                        mapboxClient.geocoding
                            .forwardGeocode({
                                query: cameraName,
                                limit: 1
                            })
                            .send()
                            .then(function(response) {
                                if (
                                    response &&
                                    response.body &&
                                    response.body.features &&
                                    response.body.features.length
                                ) {
                                    const feature = response.body.features[0];
                                    cctvMap.setCenter(feature.center);
                                    new mapboxgl.Marker({ color: 'red' })
                                        .setLngLat(feature.center)
                                        .addTo(cctvMap);
                                }
                            });
                    }
                }
            });
        </script>
    </div>
    @endif
</div>