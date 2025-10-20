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
                                <input type="text" name="status"
                                    value="{{ isset($incident['status']) ? ucfirst($incident['status']) : 'N/A' }}"
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
                                class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Description</label>
                            <textarea rows="3" name="description"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white"
                                readonly>N/A</textarea>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Evidence &
                                Attachments</label>
                            @if(!empty($incident['screenshot_path']))
                            <div x-data="{ showModal: false }">
                                <img src="{{ $incident['screenshot_path'] }}" alt="Screenshot"
                                    style="width: 100%; max-width: 100%; height: 400px; object-fit: cover; cursor: pointer;"
                                    class="rounded shadow border mb-2 transition-transform hover:scale-105"
                                    @click="showModal = true" />
                                <p class="text-sm text-gray-500 dark:text-gray-200">Click to view full size</p>
                                <!-- Modal -->
                                <div x-show="showModal" x-transition
                                    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70"
                                    @click.self="showModal = false">
                                    <img src="{{ $incident['screenshot_path'] }}" alt="Screenshot Full Size"
                                        class="object-contain rounded-lg shadow-2xl"
                                        style="max-height: 90vh; max-width: 95vw;">
                                </div>
                            </div>
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
                                class="bg-gray-100 dark:bg-gray-900 rounded-lg flex flex-col items-center justify-center border-2 border-dashed border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer py-4">
                                @if(!empty($incident['proofImageUrl']) || !empty($incident['proof_image_url']))
                                <div x-data="{ showModal: false }">
                                    <img src="{{ $incident['proofImageUrl'] ?? $incident['proof_image_url'] }}"
                                        alt="Photo Evidence"
                                        style="width: 100%; max-width: 100%; height: 400px; object-fit: cover; cursor: pointer;"
                                        class="rounded shadow border mb-2 transition-transform hover:scale-105"
                                        @click="showModal = true" />
                                    <p class="text-sm text-gray-500 dark:text-gray-200">Click to view full size</p>
                                    <!-- Modal -->
                                    <!-- Modal -->
                                    <div x-show="showModal" x-transition
                                        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70"
                                        @click.self="showModal = false">
                                        <img src="{{ $incident['proof_image_url'] ?? $incident['proofImageUrl'] }}"
                                            alt="Photo Evidence Full Size" class="object-contain rounded-lg shadow-2xl"
                                            style="max-height: 90vh; max-width: 95vw;">
                                    </div>
                                </div>
                                @else
                                <div class="text-center">
                                    <i class="fas fa-camera"></i>
                                    <p class="text-sm text-gray-500 dark:text-gray-200">Photo Evidence</p>
                                    <span class="text-xs text-gray-400 dark:text-gray-200">No photo provided.</span>
                                </div>
                                @endif
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

    <style>
        /* Custom Map Style Switcher Control */
        .mapboxgl-ctrl-style-switcher {
            background-color: #fff;
            border-radius: 4px;
            box-shadow: 0 0 0 2px rgba(0, 0, 0, .1);
        }

        .mapboxgl-ctrl-style-switcher button {
            width: 29px;
            height: 29px;
            display: block;
            padding: 0;
            outline: none;
            border: 0;
            box-sizing: border-box;
            background-color: transparent;
            /* Ensure icon inherits a visible color */
            color: #111827;
            /* gray-900 */
            cursor: pointer;
            position: relative;
        }

        .mapboxgl-ctrl-style-switcher button:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .mapboxgl-ctrl-style-switcher button svg {
            width: 20px;
            height: 20px;
            display: block;
            margin: auto;
        }

        .mapboxgl-ctrl-style-switcher .style-dropdown {
            position: absolute;
            top: 0;
            right: 35px;
            background: white;
            border-radius: 4px;
            box-shadow: 0 0 0 2px rgba(0, 0, 0, .1);
            display: none;
            min-width: 180px;
            z-index: 1;
        }

        .mapboxgl-ctrl-style-switcher .style-dropdown.active {
            display: block;
        }

        .mapboxgl-ctrl-style-switcher .style-option {
            padding: 8px 12px;
            cursor: pointer;
            font-size: 13px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            border-bottom: 1px solid #f0f0f0;
            white-space: nowrap;
        }

        .mapboxgl-ctrl-style-switcher .style-option:last-child {
            border-bottom: none;
        }

        .mapboxgl-ctrl-style-switcher .style-option:hover {
            background-color: #f0f0f0;
        }

        .mapboxgl-ctrl-style-switcher .style-option.active {
            background-color: #e8f4f8;
            font-weight: 600;
        }

        /* Dark mode support (Tailwind uses the `.dark` class on html/body) */
        .dark .mapboxgl-ctrl-style-switcher {
            background-color: #111827;
            /* gray-900 */
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.08);
        }

        .dark .mapboxgl-ctrl-style-switcher button {
            color: #e5e7eb;
            /* gray-200 for the icon */
        }

        .dark .mapboxgl-ctrl-style-switcher button:hover {
            background-color: rgba(255, 255, 255, 0.06);
        }

        .dark .mapboxgl-ctrl-style-switcher .style-dropdown {
            background: #111827;
            /* gray-900 */
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.08);
        }

        .dark .mapboxgl-ctrl-style-switcher .style-option {
            color: #e5e7eb;
            /* gray-200 */
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .dark .mapboxgl-ctrl-style-switcher .style-option:hover {
            background-color: rgba(255, 255, 255, 0.06);
        }

        .dark .mapboxgl-ctrl-style-switcher .style-option.active {
            background-color: rgba(59, 130, 246, 0.18);
            /* primary tint */
            color: #ffffff;
        }
    </style>

    <script>
        // Mapbox integration for incident location (with geocoding if needed)
        mapboxgl.accessToken = @json(config('services.mapbox.token'));

        // Custom Style Switcher Control
        class StyleSwitcherControl {
            constructor(styles) {
                this._styles = styles;
                this._currentStyle = styles[0].url;
            }

            onAdd(map) {
                this._map = map;
                this._container = document.createElement('div');
                this._container.className = 'mapboxgl-ctrl mapboxgl-ctrl-group mapboxgl-ctrl-style-switcher';

                // Create button
                const button = document.createElement('button');
                button.type = 'button';
                button.innerHTML = '<svg viewBox="0 0 20 20" fill="currentColor"><path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/></svg>';
                button.title = 'Change map style';

                // Create dropdown
                const dropdown = document.createElement('div');
                dropdown.className = 'style-dropdown';

                this._styles.forEach(style => {
                    const option = document.createElement('div');
                    option.className = 'style-option';
                    option.textContent = style.name;
                    option.dataset.styleUrl = style.url;
                    
                    if (style.url === this._currentStyle) {
                        option.classList.add('active');
                    }

                    option.addEventListener('click', () => {
                        // Update active state
                        dropdown.querySelectorAll('.style-option').forEach(opt => opt.classList.remove('active'));
                        option.classList.add('active');
                        
                        // Change map style
                        this._currentStyle = style.url;
                        this._map.setStyle(style.url);
                        
                        // Close dropdown
                        dropdown.classList.remove('active');
                    });

                    dropdown.appendChild(option);
                });

                // Toggle dropdown on button click
                button.addEventListener('click', (e) => {
                    e.stopPropagation();
                    dropdown.classList.toggle('active');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', () => {
                    dropdown.classList.remove('active');
                });

                this._container.appendChild(button);
                this._container.appendChild(dropdown);

                return this._container;
            }

            onRemove() {
                this._container.parentNode.removeChild(this._container);
                this._map = undefined;
            }
        }

        // Mobile Incident Map
        if (!@json(isset($incident['camera_name']))) {
            const mapStyles = [
                { name: 'Streets', url: 'mapbox://styles/mapbox/streets-v12' },
                { name: 'Satellite Streets', url: 'mapbox://styles/mapbox/satellite-streets-v12' },
                { name: 'Navigation Day', url: 'mapbox://styles/mapbox/navigation-day-v1' },
                { name: 'Navigation Night', url: 'mapbox://styles/mapbox/navigation-night-v1' }
            ];

            const map = new mapboxgl.Map({
                container: 'map',
                style: mapStyles[0].url,
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
            
            // Add custom style switcher control
            map.addControl(new StyleSwitcherControl(mapStyles), 'top-right');
            
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
            const cctvMapStyles = [
                { name: 'Streets', url: 'mapbox://styles/mapbox/streets-v12' },
                { name: 'Satellite Streets', url: 'mapbox://styles/mapbox/satellite-streets-v12' },
                { name: 'Navigation Day', url: 'mapbox://styles/mapbox/navigation-day-v1' },
                { name: 'Navigation Night', url: 'mapbox://styles/mapbox/navigation-night-v1' }
            ];

            const cctvMap = new mapboxgl.Map({
                container: 'cctv-map',
                style: cctvMapStyles[0].url,
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
            
            // Add custom style switcher control for CCTV map
            cctvMap.addControl(new StyleSwitcherControl(cctvMapStyles), 'top-right');
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
        // All incident details are now rendered from backend PHP variables. No Firebase JS SDK is used.
    </script>
</x-app-layout>