<!-- Firebase compat SDKs for browser -->
<script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-database-compat.js"></script>

<div class="max-w-full" x-data="mobileIncidents()" x-init="init()">
    <!-- Switch Source Toggle -->
    <div class="flex justify-end mb-4">
        <div class="inline-flex rounded-md shadow-sm border border-gray-200 bg-white">
            <button type="button" onclick="window.location.href='{{ route('incidents.mobile') }}'"
                class="px-4 py-2 text-sm font-medium focus:outline-none transition-all {{ request()->is('mobile-incident-table') ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }} rounded-l-md"
                {{ request()->is('mobile-incident-table') ? 'disabled' : '' }}
                >📱 Mobile</button>
            <button type="button" onclick="window.location.href='{{ route('incidents.cctv') }}'"
                class="px-4 py-2 text-sm font-medium focus:outline-none transition-all {{ request()->is('cctv-incident-table') ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }} rounded-r-md"
                {{ request()->is('cctv-incident-table') ? 'disabled' : '' }}
                >🎥 CCTV</button>
        </div>
    </div>
    <!-- Search & Filter Row -->
    <div class="flex flex-row gap-4 mb-6">
        <div class="relative flex-grow">
            <input type="search" x-model.debounce.150ms="search" @keydown.enter.prevent autocomplete="off"
                class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg bg-white text-sm text-gray-600"
                placeholder="Search incidents...">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>
        <div class="relative w-48">
            <select x-model="filter"
                class="w-full px-4 py-2.5 appearance-none border border-gray-200 rounded-lg bg-white text-sm text-gray-600">
                <option value="">All Types</option>
                <option value="vehicle_crash">Vehicular accident</option>
                <option value="fire">Fire</option>
                <option value="medical_emergency">Medical emergency</option>
                <option value="disturbance">Disturbance</option>
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="bg-white rounded-lg px-6 py-4">

        <div class="w-full">
            <table class="w-full">
                <thead>
                    <tr class="text-left">
                        <th class="pb-4 text-xs font-medium text-gray-500">ID</th>
                        <th class="pb-4 text-xs font-medium text-gray-500">TYPE</th>
                        <th class="pb-4 text-xs font-medium text-gray-500">LOCATION</th>
                        <th class="pb-4 text-xs font-medium text-gray-500">REPORTER</th>
                        <th class="pb-4 text-xs font-medium text-gray-500">STATUS</th>
                        <th class="pb-4 text-xs font-medium text-gray-500">DEPARTMENT</th>
                        <th class="pb-4 text-xs font-medium text-gray-500">TIMESTAMP</th>
                        <th class="pb-4 text-xs font-medium text-gray-500">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    <template x-for="incident in paginatedIncidents" :key="incident.incident_id">
                        <tr>
                            <td class="py-3 text-gray-900" x-text="incident.incident_id"></td>
                            <td class="py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium"
                                    :class="{
                                        'text-red-600': incident.type === 'fire',
                                        'text-orange-600': incident.type === 'vehicle_crash' || incident.type === 'vehicular_accident',
                                        'text-emerald-600': incident.type === 'medical_emergency',
                                        'text-blue-600': incident.type === 'disturbance'
                                    }"
                                    x-text="(incident.type || '').replace('_', ' ').replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase())"></span>
                            </td>
                            <td class="py-3 text-gray-600" x-text="incident.location"></td>
                            <td class="py-3 text-gray-600" x-text="incident.reporter_name"></td>
                            <td class="py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium"
                                    :class="{
                                        'text-emerald-600': incident.status === 'new',
                                        'text-orange-600': incident.status === 'dispatched',
                                        'text-gray-600': incident.status === 'resolved'
                                    }" x-text="(incident.status || '').replace(/\b\w/g, c => c.toUpperCase())"></span>
                            </td>
                            <td class="py-3 text-gray-600"
                                x-text="(incident.department || '').replace(/\b\w/g, c => c.toUpperCase())"></td>
                            <td class="py-3 text-gray-500" x-text="incident.timestamp_formatted || incident.timestamp">
                            </td>
                            <td class="py-3">
                                <a :href="'/dispatch?incident_id=' + encodeURIComponent(incident.incident_id)"
                                    class="inline-flex items-center px-3 py-1 rounded bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition">
                                    View
                                </a>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="paginatedIncidents.length === 0">
                        <td colspan="7" class="py-4 text-center text-gray-500">No incidents found.</td>
                    </tr>
                </tbody>
            </table>
            <!-- Pagination Controls -->
            <div class="flex items-center justify-between mt-4">
                <div class="text-sm text-gray-500">
                    Page <span x-text="page + 1"></span> of <span x-text="totalPages"></span>
                </div>
                <div class="space-x-2">
                    <button type="button" class="px-3 py-1 rounded bg-gray-200 text-gray-600 text-sm"
                        :disabled="page === 0" @click="page = Math.max(0, page - 1)">Prev</button>
                    <button type="button" class="px-3 py-1 rounded bg-gray-200 text-gray-600 text-sm"
                        :disabled="page >= totalPages - 1"
                        @click="page = Math.min(totalPages - 1, page + 1)">Next</button>
                </div>
            </div>
        </div>

        <!-- Results Count -->
        <div class="mt-6 text-sm text-gray-500">
            Showing <span x-text="filteredIncidents.length"></span> results
        </div>
    </div>

    <script>
        function mobileIncidents() {
            return {
                incidents: [],
                init() {
                    this.setupFirebase();
                },
                setupFirebase() {
                    // TODO: Replace with your Firebase config
                    const firebaseConfig = {
                        apiKey: "AIzaSyB3XyotQMmmegvcpehChurRa_t1CL3V2yU",
                        authDomain: "incident-report--database.firebaseapp.com",
                        databaseURL: "https://incident-report--database-default-rtdb.asia-southeast1.firebasedatabase.app",
                        projectId: "incident-report--database",
                        storageBucket: "incident-report--database.firebasestorage.app",
                        messagingSenderId: "79154499994",
                        appId: "1:79154499994:web:bfcf3600bb2ad0c58fea23",
                        measurementId: "G-SF2623RC2F"
                    };
                    if (!window.firebase) {
                        setTimeout(() => this.setupFirebase(), 500);
                        return;
                    }
                    if (!window.firebase.apps || window.firebase.apps.length === 0) {
                        window.firebase.initializeApp(firebaseConfig);
                    }
                    const db = window.firebase.database();
                    const incidentsRef = db.ref('mobile_incidents');
                    incidentsRef.on('value', (snapshot) => {
                        const data = snapshot.val() || {};
                        this.incidents = Object.values(data);
                    });
                },
                search: '',
                filter: '',
                page: 0,
                pageSize: 10,
                get filteredIncidents() {
                    const s = (this.search || '').toLowerCase().trim();
                    const sortByIdDesc = arr => arr.slice().sort((a, b) => Number(b.incident_id) - Number(a.incident_id));
                    if (s !== '') {
                        return sortByIdDesc(this.incidents.filter(i => {
                            const hay = Object.values(i).join(' ').toLowerCase();
                            return hay.indexOf(s) !== -1;
                        }));
                    }
                    if (this.filter && this.filter !== '') {
                        return sortByIdDesc(this.incidents.filter(i => ((i.type || '').toLowerCase() === (this.filter || '').toLowerCase())));
                    }
                    return sortByIdDesc(this.incidents);
                },
                get totalPages() {
                    return Math.max(1, Math.ceil(this.filteredIncidents.length / this.pageSize));
                },
                get paginatedIncidents() {
                    const start = this.page * this.pageSize;
                    return this.filteredIncidents.slice(start, start + this.pageSize);
                }
            }
        }
    </script>
</div>