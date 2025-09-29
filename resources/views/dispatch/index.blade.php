<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-gray-900 dark:text-white tracking-tight">Incident Dispatch</h2>
            @if(isset($incidentId) && $incidentId)
            <span
                class="ml-4 px-3 py-1 rounded bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-sm font-semibold">Incident
                ID: {{
                $incidentId }}</span>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-4">
                    <!-- Incident Details Section -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Incident Details</h3>

                        <!-- Incident fields in grid layout -->
                        @if(isset($incident['camera_name']))
                        <!-- CCTV Incident UI: Only show relevant CCTV info, remove map, evidence, attachments -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Camera
                                    Name</label>
                                <input type="text" name="camera_name" value="{{ $incident['camera_name'] ?? 'N/A' }}"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white"
                                    readonly>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Event</label>
                                <input type="text" name="event" value="{{ $incident['event'] ?? 'N/A' }}"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white"
                                    readonly>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Status</label>
                                <input type="text" name="status" value="{{ $incident['status'] ?? 'N/A' }}"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-yellow-50 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-200"
                                    readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Date &
                                    Time</label>
                                <input type="text" name="date_time" value="{{ $incident['timestamp'] ?? 'N/A' }}"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white"
                                    readonly>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Screenshot</label>
                            @if(!empty($incident['screenshot_path']))
                            <img src="{{ $incident['screenshot_path'] }}" alt="Screenshot"
                                class="h-32 w-auto rounded shadow border" />
                            @else
                            <span class="text-gray-400 dark:text-gray-200">N/A</span>
                            @endif
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Video</label>
                            @if(!empty($incident['camera_url']))
                            <a href="{{ $incident['camera_url'] }}" target="_blank" class="text-blue-600 underline">View
                                Video</a>
                            @else
                            <span class="text-gray-400 dark:text-gray-200">N/A</span>
                            @endif
                        </div>
                        @else
                        <!-- Mobile Incident UI (default, now using mobileIncidents variable) -->
                        @php $mobile_incidents = $incident; @endphp
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Incident
                                    Type</label>
                                <input type="text" name="incident_type" value="{{ $incident['type'] ?? 'N/A' }}"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white"
                                    readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Priority
                                    Level</label>
                                <input type="text" name="priority_level" value="{{ $incident['priority'] ?? 'N/A' }}"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-red-50 dark:bg-red-900 text-red-700 dark:text-red-200"
                                    readonly>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Reporter
                                    Name</label>
                                <input type="text" name="reporter_name"
                                    value="{{ $incident['reporter_name'] ?? 'N/A' }}"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white"
                                    readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Contact
                                    Number</label>
                                <input type="text" name="contact_number"
                                    value="{{ $incident['contact_number'] ?? 'N/A' }}"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white"
                                    readonly>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Date &
                                    Time</label>
                                <input type="text" name="date_time" value="{{ $incident['timestamp'] ?? 'N/A' }}"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white"
                                    readonly>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Status</label>
                                <input type="text" name="status" value="{{ $incident['status'] ?? 'N/A' }}"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-yellow-50 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-200"
                                    readonly>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label
                                class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Description</label>
                            <textarea rows="3" name="description"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white"
                                readonly>{{ $incident['description'] ?? $incident['incident_description'] ?? 'N/A' }}</textarea>
                        </div>
                        @endif
                    </div>

                    {{-- Location & Map Section (Mobile only) --}}
                    @if(!isset($incident['camera_name']))
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Incident Location</h3>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Address</label>
                            <input type="text" name="address" value="{{ $incident['location'] ?? 'N/A' }}"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white"
                                readonly>
                        </div>
                        <div id="map" style="width: 100%; height: 400px; border-radius: 0.5rem; overflow: hidden;">
                        </div>
                        <!-- Mapbox CSS & JS -->
                        <link href="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css" rel="stylesheet" />
                        <script src="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>
                        <script src="https://unpkg.com/@mapbox/mapbox-sdk/umd/mapbox-sdk.min.js"></script>
                    </div>
                    @endif

                    {{-- Location & Map Section (CCTV only) --}}
                    @if(isset($incident['camera_name']))
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mt-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Camera Location</h3>
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Camera
                                Name</label>
                            <input type="text" name="camera_location" value="{{ $incident['camera_name'] ?? 'N/A' }}"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white"
                                readonly>
                        </div>
                        <div id="cctv-map" style="width: 100%; height: 400px; border-radius: 0.5rem; overflow: hidden;">
                        </div>
                        <!-- Mapbox CSS & JS -->
                        <link href="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css" rel="stylesheet" />
                        <script src="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>
                        <script src="https://unpkg.com/@mapbox/mapbox-sdk/umd/mapbox-sdk.min.js"></script>
                    </div>
                    @endif

                    {{-- Evidence & Attachments Section (Mobile only) --}}
                    @if(!isset($incident['camera_name']))
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Evidence & Attachments</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div
                                class="bg-gray-100 dark:bg-gray-900 rounded-lg h-56 flex items-center justify-center border-2 border-dashed border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer">
                                <div class="text-center">
                                    <i class="fas fa-camera"></i>
                                    <p class="text-sm text-gray-500 dark:text-gray-200">Photo Evidence</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Right Column (Livewire Responder Assignment) -->
                <div>
                    <livewire:incident-dispatch :incident-id="$incidentId" />
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mapbox integration for incident location (with geocoding if needed)
        mapboxgl.accessToken = 'pk.eyJ1IjoiZGplbnRsZW8iLCJhIjoiY21mNnoxMDgzMGt3NjJyb20zY3dqdnRjdSJ9.OKI8RAGo7e9eRRXejMLfOA';

        // Mobile Incident Map
        if (!@json(isset($incident['camera_name']))) {
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

        // CCTV Incident Map (camera_name as location)
        if (@json(isset($incident['camera_name']))) {
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
            const cameraName = @json($incident['camera_name'] ?? '');
            if (cameraName) {
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
        // Fetch incident details from Firebase
    const urlParams = new URLSearchParams(window.location.search);
    const incidentId = urlParams.get('incident_id');

    if (incidentId) {
            const database = firebase.database();
            // Try mobile_incidents first, fallback to incidents for CCTV
            let incidentRef = database.ref(`mobile_incidents/${incidentId}`);
            incidentRef.once('value').then((snapshot) => {
                let incident = snapshot.val();
                if (!incident) {
                    incidentRef = database.ref(`incidents/${incidentId}`);
                    return incidentRef.once('value').then(s => s.val());
                }
                return incident;
            }).then((incident) => {
                if (incident) {
                    if (incident.camera_name) {
                        // CCTV Incident
                        const cameraNameInput = document.querySelector('[name="camera_name"]');
                        if (cameraNameInput) cameraNameInput.value = incident.camera_name || 'N/A';
                        const eventInput = document.querySelector('[name="event"]');
                        if (eventInput) eventInput.value = incident.event || 'N/A';
                        const dateTimeInput = document.querySelector('[name="date_time"]');
                        if (dateTimeInput) dateTimeInput.value = incident.timestamp || 'N/A';
                        // Screenshot
                        const screenshotImg = document.querySelector('img[alt="Screenshot"]');
                        if (screenshotImg) screenshotImg.src = incident.screenshot_path || '';
                        // Video link
                        const videoLink = document.querySelector('a.text-blue-600');
                        if (videoLink) videoLink.href = incident.camera_url || '#';
                    } else {
                        // Mobile Incident (now using mobileIncidents variable)
                        document.querySelector('[name="incident_type"]').value = incident.type || 'N/A';
                        document.querySelector('[name="priority_level"]').value = incident.priority || 'N/A';
                        document.querySelector('[name="reporter_name"]').value = incident.reporter_name || 'N/A';
                        document.querySelector('[name="contact_number"]').value = incident.contact_number || 'N/A';
                        document.querySelector('[name="date_time"]').value = incident.timestamp || 'N/A';
                        document.querySelector('[name="status"]').value = incident.status || 'N/A';
                        // Use description, fallback to incident_description
                        document.querySelector('[name="description"]').value = incident.description || incident.incident_description || 'N/A';
                        document.querySelector('[name="address"]').value = incident.location || 'N/A';
                    }
                } else {
                    alert('Incident not found.');
                }
            }).catch((error) => {
                console.error('Error fetching incident:', error);
            });
        } else {
            alert('No incident ID provided.');
        }
    </script>
</x-app-layout>