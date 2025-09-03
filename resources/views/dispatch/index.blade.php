<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-gray-900 tracking-tight">Incident Dispatch</h2>
            @if(isset($incidentId) && $incidentId)
                <span class="ml-4 px-3 py-1 rounded bg-blue-100 text-blue-800 text-sm font-semibold">Incident ID: {{ $incidentId }}</span>
            @elseif(!isset($incidentId) || !$incidentId)
                <script>window.onload = function() { alert('No incident ID provided.'); };</script>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column -->
                <div class="lg:col-span-2 space-y-4">
                    <!-- Incident Details Section -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Incident Details</h3>
                        
                        <!-- Incident fields in grid layout -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Incident Type</label>
                                <input type="text" name="incident_type" value="{{ $incident['type'] ?? 'N/A' }}" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Priority Level</label>
                                <input type="text" name="priority_level" value="{{ $incident['priority'] ?? 'N/A' }}" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-red-50 text-red-700" readonly>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Reporter Name</label>
                                <input type="text" name="reporter_name" value="{{ $incident['reporter_name'] ?? 'N/A' }}" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Contact Number</label>
                                <input type="text" name="contact_number" value="{{ $incident['contact_number'] ?? 'N/A' }}" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50" readonly>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date & Time</label>
                                <input type="text" name="date_time" value="{{ $incident['timestamp'] ?? 'N/A' }}" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <input type="text" name="status" value="{{ $incident['status'] ?? 'N/A' }}" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-yellow-50 text-yellow-700" readonly>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea rows="3" name="description" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50" readonly>{{ $incident['description'] ?? 'N/A' }}</textarea>
                        </div>
                    </div>

                    <!-- Location & Map Section -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Incident Location</h3>
                        
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <input type="text" name="address" value="{{ $incident['location'] ?? 'N/A' }}" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50" readonly>
                        </div>
                        
                        <!-- Map display -->
                        @if(!empty($incident['latitude']) && !empty($incident['longitude']))
                            <iframe
                                width="100%"
                                height="250"
                                frameborder="0"
                                style="border:0"
                                src="https://www.google.com/maps?q={{ $incident['latitude'] }},{{ $incident['longitude'] }}&hl=es;z=14&output=embed"
                                allowfullscreen>
                            </iframe>
                        @else
                            <div class="bg-gray-100 rounded-lg h-64 flex items-center justify-center mb-4 border-2 border-dashed border-gray-300">
                                <div class="text-center text-gray-500">No map data available.</div>
                            </div>
                        @endif
                    </div>

                    <!-- Evidence & Attachments Section -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Evidence & Attachments</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-100 rounded-lg h-32 flex items-center justify-center border-2 border-dashed border-gray-300 hover:bg-gray-50 cursor-pointer">
                                <div class="text-center">
                                    <svg class="w-8 h-8 mx-auto text-gray-400 mb-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                                    </svg>
                                    <p class="text-sm text-gray-500">Photo Evidence</p>
                                    <p class="text-xs text-gray-400">2 files uploaded</p>
                                </div>
                            </div>
                            <div class="bg-gray-100 rounded-lg h-32 flex items-center justify-center border-2 border-dashed border-gray-300 hover:bg-gray-50 cursor-pointer">
                                <div class="text-center">
                                    <svg class="w-8 h-8 mx-auto text-gray-400 mb-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                                    </svg>
                                    <p class="text-sm text-gray-500">Video Evidence</p>
                                    <p class="text-xs text-gray-400">1 file uploaded</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <!-- Responder Assignment -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Assign Responders</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Primary Responder</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-900 focus:ring-2 focus:ring-blue-500">
                                    <option>Select Police Unit</option>
                                    <option>QCPD Station 10</option>
                                    <option>QCPD Station 11</option>
                                    <option>MMDA Unit Alpha-7</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Medical Response</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-900 focus:ring-2 focus:ring-blue-500">
                                    <option>Select Medical Unit</option>
                                    <option>QC General Hospital Ambulance</option>
                                    <option>Red Cross Emergency Unit</option>
                                    <option>Private Ambulance Service</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Traffic Management</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-900 focus:ring-2 focus:ring-blue-500">
                                    <option>Select Traffic Unit</option>
                                    <option>MMDA Traffic Enforcer</option>
                                    <option>QCPD Traffic Division</option>
                                    <option>Barangay Traffic Aide</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Dispatch Summary -->
                    <div class="bg-blue-50 rounded-lg h-40 flex items-center justify-center border border-blue-200">
                        <div class="text-center">
                            <div class="text-blue-600 mb-2">
                                <svg class="w-8 h-8 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-blue-800">Dispatch Summary</h3>
                            <p class="text-sm text-blue-600 mt-1">Ready to dispatch to assigned units</p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        <button class="w-full bg-green-600 text-white py-3 px-4 rounded-lg flex items-center justify-between hover:bg-green-700 transition-colors group">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Approve & Dispatch
                            </span>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <button class="w-full bg-orange-600 text-white py-3 px-4 rounded-lg flex items-center justify-between hover:bg-orange-700 transition-colors group">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                Request More Info
                            </span>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>

                        <button class="w-full bg-red-600 text-white py-3 px-4 rounded-lg flex items-center justify-between hover:bg-red-700 transition-colors group">
                            <span class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                                Reject Incident
                            </span>
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Activity Timeline -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3">Activity Timeline</h3>
                        <div class="space-y-3">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900 font-medium">Incident Reported</p>
                                    <p class="text-xs text-gray-500">Aug 21, 2025 - 2:30 PM</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-2 h-2 bg-yellow-500 rounded-full mt-2"></div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-900 font-medium">Under Review</p>
                                    <p class="text-xs text-gray-500">Aug 21, 2025 - 2:32 PM</p>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-2 h-2 bg-gray-300 rounded-full mt-2"></div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-500">Awaiting Dispatch</p>
                                    <p class="text-xs text-gray-400">Pending approval</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Fetch incident details from Firebase
        const urlParams = new URLSearchParams(window.location.search);
        const incidentId = urlParams.get('id');

        if (incidentId) {
            const database = firebase.database();
            const incidentRef = database.ref(`incidents/${incidentId}`);

            incidentRef.once('value').then((snapshot) => {
                const incident = snapshot.val();

                if (incident) {
                    document.querySelector('[name="incident_type"]').value = incident.type || 'N/A';
                    document.querySelector('[name="priority_level"]').value = incident.severity || 'N/A';
                    document.querySelector('[name="reporter_name"]').value = incident.reporter_name || 'N/A';
                    document.querySelector('[name="contact_number"]').value = incident.contact_number || 'N/A';
                    document.querySelector('[name="date_time"]').value = incident.timestamp || 'N/A';
                    document.querySelector('[name="status"]').value = incident.status || 'N/A';
                    document.querySelector('[name="description"]').value = incident.incident_description || 'N/A';
                    document.querySelector('[name="address"]').value = incident.location || 'N/A';
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
