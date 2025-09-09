<link href="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css" rel="stylesheet" />
<script src="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>
<script src="https://unpkg.com/@mapbox/mapbox-sdk/umd/mapbox-sdk.min.js"></script>
<div x-data="{ open: @entangle('showModal'), incident: @entangle('selectedIncident'), notes: @entangle('incidentNotes'), status: @entangle('incidentStatus') }"
    x-effect="if(open && incident) { Alpine.store('incidentAddress', incident.location || incident.camera_name || ''); Alpine.store('modalMapLoaded', false); }"
    x-init="
        let modalMapInstance = null;
        function initMap(incidentData) {
            setTimeout(() => {
                if (modalMapInstance) {
                    try { modalMapInstance.remove(); } catch(e) {}
                    modalMapInstance = null;
                }
                mapboxgl.accessToken = 'pk.eyJ1IjoiZGplbnRsZW8iLCJhIjoiY21mNnoxMDgzMGt3NjJyb20zY3dqdnRjdSJ9.OKI8RAGo7e9eRRXejMLfOA';
                modalMapInstance = new mapboxgl.Map({
                    container: 'modal-map',
                    style: 'mapbox://styles/mapbox/streets-v11',
                    center: [120.9532, 14.6562],
                    zoom: 13
                });
                setTimeout(() => { modalMapInstance.resize(); }, 300);
                const address = incidentData.location || incidentData.camera_name || '';
                if (address) {
                    const mapboxClient = mapboxSdk({ accessToken: mapboxgl.accessToken });
                    mapboxClient.geocoding.forwardGeocode({ query: address, limit: 1 }).send()
                        .then(function(response) {
                            if (response?.body?.features?.length) {
                                const feature = response.body.features[0];
                                modalMapInstance.setCenter(feature.center);
                                new mapboxgl.Marker({ color: 'red' })
                                    .setLngLat(feature.center)
                                    .addTo(modalMapInstance);
                            }
                        });
                }
            }, 200);
        }
        $watch('open', value => {
            if (value && incident) {
                // Modal opened → always re-init map
                initMap(incident);
            } else if (!value && modalMapInstance) {
                // Modal closed → destroy map
                try { modalMapInstance.remove(); } catch(e) {}
                modalMapInstance = null;
            }
        });
        $watch('incident', value => {
            if (open && value) {
                // Incident changed while modal open → re-init map
                initMap(value);
            }
        });
    " x-show="open" style="display: none;"
    class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-40">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden relative">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2L3 7v11a1 1 0 001 1h3v-6h6v6h3a1 1 0 001-1V7l-7-5z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold">Incident Details</h3>
                    <p class="text-blue-100 text-sm" x-text="incident && (incident.type || incident.event || 'N/A')">
                    </p>
                </div>
            </div>
            <button @click="open = false"
                class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                </svg>
            </button>
        </div>

        <!-- Content -->
        <div class="flex h-[calc(90vh-80px)]">
            <!-- Left Panel: Details & Actions -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                <!-- Incident Info Card -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Status</label>
                            <div class="mt-1">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                    x-text="incident && (status || incident.status || 'N/A')"></span>
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Priority</label>
                            <div class="mt-1">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    High
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-1 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Location</label>
                            <p class="mt-1 text-sm text-gray-900"
                                x-text="incident && (incident.location || incident.camera_name || 'N/A')"></p>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Date &
                                Time</label>
                            <p class="mt-1 text-sm text-gray-900"
                                x-text="incident && (incident.datetime || incident.date_time || incident.timestamp || 'N/A')">
                            </p>
                        </div>
                        <div>
                            <label
                                class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Description</label>
                            <p class="mt-1 text-sm text-gray-900"
                                x-text="incident && (incident.description || incident.incident_description || incident.screenshot || 'N/A')">
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Status Update Card -->
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Update Status
                    </h4>
                    <form @submit.prevent="$wire.updateStatus()" class="flex gap-3">
                        <select x-model="$wire.incidentStatus"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <template x-if="$wire.statusOptions">
                                <template x-for="(label, value) in $wire.statusOptions" :key="value">
                                    <option :value="value" x-text="label"></option>
                                </template>
                            </template>
                        </select>
                        <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                            Update
                        </button>
                    </form>
                </div>

                <!-- Internal Notes Card -->
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                            <path fill-rule="evenodd"
                                d="M4 5a2 2 0 012-2v1a1 1 0 001 1h6a1 1 0 001-1V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3z" />
                        </svg>
                        Internal Notes
                    </h4>
                    <div class="space-y-3 max-h-40 overflow-y-auto mb-4">
                        <template x-if="notes && notes.length">
                            <template x-for="note in notes" :key="note.id">
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-medium text-gray-900" x-text="note.user_name"></span>
                                        <span class="text-xs text-gray-500" x-text="note.created_at"></span>
                                    </div>
                                    <p class="text-sm text-gray-700" x-text="note.note"></p>
                                </div>
                            </template>
                        </template>
                        <template x-if="!notes || notes.length === 0">
                            <div class="text-center py-4">
                                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.959 8.959 0 01-4.906-1.459L3 21l2.541-5.094A8.959 8.959 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z" />
                                </svg>
                                <p class="text-sm text-gray-500 mt-2">No notes yet</p>
                            </div>
                        </template>
                    </div>
                    <form @submit.prevent="$wire.addNote()" class="flex gap-2">
                        <input type="text" x-model="$wire.newNote"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Add a note...">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                            Add
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Panel: Map & Evidence -->
            <div class="w-96 bg-gray-50 border-l border-gray-200 flex flex-col">
                <!-- Map Section -->
                <div class="p-4 flex-1">
                    <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" />
                        </svg>
                        Location Map
                    </h4>
                    <div id="modal-map" class="w-full h-64 rounded-lg border border-gray-300 shadow-sm"></div>
                </div>

                <!-- Evidence Section -->
                <div class="p-4 border-t border-gray-200">
                    <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" />
                        </svg>
                        Evidence & Attachments
                    </h4>
                    <div class="space-y-2">
                        <template x-if="incident && incident.photo_evidence">
                            <div class="bg-white rounded-lg border border-gray-200 p-2">
                                <img :src="incident.photo_evidence" alt="Photo Evidence"
                                    class="w-full h-20 object-cover rounded" />
                            </div>
                        </template>
                        <template x-if="incident && incident.video_evidence">
                            <div class="bg-white rounded-lg border border-gray-200 p-3">
                                <a :href="incident.video_evidence" target="_blank"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M2 6a2 2 0 012-2h6l2 2h6a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z" />
                                    </svg>
                                    View Video Evidence
                                </a>
                            </div>
                        </template>
                        <template x-if="incident && !incident.photo_evidence && !incident.video_evidence">
                            <div class="text-center py-6">
                                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-sm text-gray-500 mt-2">No evidence uploaded</p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Mapbox modal map logic is now handled in Alpine x-init above -->
</div>