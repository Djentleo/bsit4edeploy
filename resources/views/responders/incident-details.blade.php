<div class="max-w-4xl mx-auto p-6">
    <h2 class="text-2xl font-bold mb-6">Incident Details</h2>
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <div class="font-semibold">Reporter Name:</div>
                <div>{{ $incident['reporter_name'] ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="font-semibold">Type:</div>
                <div>{{ $incident['type'] ?? $incident['event'] ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="font-semibold">Status:</div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                        @if($status === 'resolved') bg-green-100 text-green-800
                        @elseif($status === 'en_route') bg-yellow-100 text-yellow-800
                        @elseif($status === 'closed') bg-gray-100 text-gray-800
                        @else bg-blue-100 text-blue-800
                        @endif">
                        {{ $statusOptions[$status] ?? ucfirst($status) }}
                    </span>
                    <select wire:change="updateStatus($event.target.value)" class="border rounded px-2 py-1 text-sm">
                        @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($status===$value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <div class="font-semibold">Source:</div>
                <div>{{ $incident['source'] ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="font-semibold">Location:</div>
                <div>{{ $incident['location'] ?? $incident['camera_name'] ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="font-semibold">Date & Time:</div>
                <div>{{ $incident['datetime'] ?? $incident['date_time'] ?? $incident['timestamp'] ?? 'N/A' }}</div>
            </div>
        </div>
        <div class="mb-4">
            <div class="font-semibold">Description:</div>
            <div>{{ $incident['incident_description'] ?? $incident['screenshot'] ?? 'N/A' }}</div>
        </div>
    </div>

    <!-- Notes Section -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6 mb-6">
        <div class="font-semibold mb-2">Internal Notes</div>
        <div class="space-y-2 max-h-32 overflow-y-auto mb-2">
            @forelse($incidentNotes as $note)
            <div class="bg-gray-100 rounded p-2 text-sm">
                <div class="font-semibold">{{ $note['user_name'] }}</div>
                <div>{{ $note['note'] }}</div>
                <div class="text-xs text-gray-500">{{ $note['created_at'] }}</div>
            </div>
            @empty
            <div class="text-gray-400">No notes yet.</div>
            @endforelse
        </div>
        <form wire:submit.prevent="addNote" class="flex gap-2 mt-2">
            <input type="text" wire:model.defer="newNote" class="border rounded px-2 py-1 flex-1"
                placeholder="Add a note...">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">Add</button>
        </form>
    </div>

    <!-- Timeline Section -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6">
        <div class="font-semibold mb-2">Timeline</div>
        <div class="space-y-2 max-h-32 overflow-y-auto">
            @forelse($timeline as $item)
            <div class="bg-gray-50 rounded p-2 text-sm">
                <div><span class="font-semibold">{{ $item['user_name'] }}</span> {{ $item['action'] }}: {{
                    $item['details'] }}</div>
                <div class="text-xs text-gray-500">{{ $item['created_at'] }}</div>
            </div>
            @empty
            <div class="text-gray-400">No timeline entries yet.</div>
            @endforelse
        </div>
    </div>

    <!-- Location & Map Section (Mobile only: no camera_name) -->
    @if(!isset($incident['camera_name']))
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6 mt-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Incident Location</h3>
        <div class="mb-3">
            <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Address</label>
            <input type="text" value="{{ $incident['location'] ?? 'N/A' }}"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white"
                readonly>
        </div>
        <div id="map" style="width: 100%; height: 400px; border-radius: 0.5rem; overflow: hidden;"></div>
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
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-6 mt-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Camera Location</h3>
        <div class="mb-3">
            <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Camera Name</label>
            <input type="text" value="{{ $incident['camera_name'] ?? 'N/A' }}"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white"
                readonly>
        </div>
        <div id="cctv-map" style="width: 100%; height: 400px; border-radius: 0.5rem; overflow: hidden;"></div>
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