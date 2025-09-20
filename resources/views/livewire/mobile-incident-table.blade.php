<!-- Firebase compat SDKs for browser -->
<script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-database-compat.js"></script>

<div class="max-w-full" x-data="mobileIncidents()" x-init="init()">
    <!-- Switch Source Toggle -->
    <div class="flex justify-end mb-6">
        <div class="relative inline-flex rounded-lg shadow-sm border border-gray-200 bg-white">
            <button type="button" onclick="window.location.href='{{ route('incidents.mobile') }}'"
                class="relative px-4 py-2.5 text-sm font-medium focus:outline-none transition-all duration-200 {{ request()->is('mobile-incident-table') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }} rounded-l-lg"
                {{ request()->is('mobile-incident-table') ? 'disabled' : '' }}>
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M7 2a2 2 0 00-2 2v12a2 2 0 002 2h6a2 2 0 002-2V4a2 2 0 00-2-2H7zM6 4a1 1 0 011-1h6a1 1 0 011 1v10H6V4z"
                            clip-rule="evenodd" />
                    </svg>
                    Mobile
                </span>
            </button>
            <button type="button" onclick="window.location.href='{{ route('incidents.cctv') }}'"
                class="relative px-4 py-2.5 text-sm font-medium focus:outline-none transition-all duration-200 {{ request()->is('cctv-incident-table') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:text-blue-600 hover:bg-blue-50' }} rounded-r-lg"
                {{ request()->is('cctv-incident-table') ? 'disabled' : '' }}>
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z" />
                    </svg>
                    CCTV
                </span>
            </button>
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
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="min-w-full">
            <table class="w-full table-auto">
                <thead style="background-color: #1E3A8A;" class="border-b border-gray-200">
                    <tr class="text-left">
                        <th class="px-3 py-4 text-xs font-semibold text-white uppercase tracking-wider">ID</th>
                        <th class="px-3 py-4 text-xs font-semibold text-white uppercase tracking-wider">TYPE</th>
                        <th class="px-3 py-4 text-xs font-semibold text-white uppercase tracking-wider">SEVERITY</th>
                        <th
                            class="px-3 py-4 text-xs font-semibold text-white uppercase tracking-wider hidden sm:table-cell">
                            LOCATION</th>
                        <th
                            class="px-3 py-4 text-xs font-semibold text-white uppercase tracking-wider hidden md:table-cell">
                            REPORTER</th>
                        <th class="px-3 py-4 text-xs font-semibold text-white uppercase tracking-wider">STATUS</th>
                        <th
                            class="px-3 py-4 text-xs font-semibold text-white uppercase tracking-wider hidden lg:table-cell">
                            DEPARTMENT</th>
                        <th class="px-3 py-4 text-xs font-semibold text-white uppercase tracking-wider">TIMESTAMP</th>
                        <th class="px-3 py-4 text-xs font-semibold text-white uppercase tracking-wider">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-200 bg-white">
                    <template x-for="incident in paginatedIncidents" :key="incident.incident_id">
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-3 py-4 text-gray-900 font-medium text-xs" x-text="incident.incident_id"></td>
                            <td class="px-3 py-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold"
                                    :class="{
                                        'bg-red-100 text-red-700': incident.type === 'fire',
                                        'bg-orange-100 text-orange-700': incident.type === 'vehicle_crash' || incident.type === 'vehicular_accident',
                                        'bg-emerald-100 text-emerald-700': incident.type === 'medical_emergency',
                                        'bg-blue-100 text-blue-700': incident.type === 'disturbance'
                                    }"
                                    x-text="(incident.type || '').replace('_', ' ').replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase())"></span>
                            </td>
                            <td class="px-3 py-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold"
                                    :class="{
                                        'bg-red-200 text-red-800': incident.severity === 'critical',
                                        'bg-orange-200 text-orange-800': incident.severity === 'high',
                                        'bg-yellow-100 text-yellow-800': incident.severity === 'medium',
                                        'bg-green-100 text-green-800': incident.severity === 'low'
                                    }"
                                    x-text="(incident.severity || '').charAt(0).toUpperCase() + (incident.severity || '').slice(1)"></span>
                            </td>
                            <td class="px-3 py-4 text-gray-600 text-xs hidden sm:table-cell" x-text="incident.location">
                            </td>
                            <td class="px-3 py-4 text-gray-600 text-xs hidden md:table-cell"
                                x-text="incident.reporter_name"></td>
                            <td class="px-3 py-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold"
                                    :class="{
                                        'bg-green-100 text-green-700': incident.status === 'new',
                                        'bg-yellow-100 text-yellow-700': incident.status === 'dispatched',
                                        'bg-gray-100 text-gray-700': incident.status === 'resolved'
                                    }" x-text="(incident.status || '').replace(/\b\w/g, c => c.toUpperCase())"></span>
                            </td>
                            <td class="px-3 py-4 text-gray-600 font-medium text-xs hidden lg:table-cell"
                                x-text="(incident.department || '').replace(/\b\w/g, c => c.toUpperCase())"></td>
                            <td
                                class="px-3 py-4 text-gray-500 text-xs max-w-[100px] md:max-w-[180px] truncate whitespace-normal break-words">
                                <div
                                    x-text="(incident.timestamp ? new Date(incident.timestamp).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '-')">
                                </div>
                                <div class="text-gray-400 text-xs"
                                    x-text="(incident.timestamp ? new Date(incident.timestamp).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false }) : '')">
                                </div>
                            </td>
                            <td class="px-3 py-4">
                                <a :href="'/dispatch?incident_id=' + encodeURIComponent(incident.incident_id)"
                                    class="inline-flex items-center px-3 py-1.5 rounded-md bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200 shadow-sm">
                                    View
                                </a>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="paginatedIncidents.length === 0">
                        <td colspan="8" class="px-3 py-8 text-center text-gray-500 bg-gray-50">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-sm font-medium">No incidents found</p>
                                <p class="text-xs text-gray-400 mt-1">Try adjusting your search or filter criteria</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <!-- Pagination Controls -->
            <div class="flex items-center justify-between px-3 py-4 bg-gray-50 border-t border-gray-200">
                <div class="text-sm text-gray-600">
                    Page <span class="font-medium" x-text="page + 1"></span> of <span class="font-medium"
                        x-text="totalPages"></span>
                </div>
                <div class="flex space-x-2">
                    <button type="button"
                        class="px-3 py-2 rounded-md bg-white border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
                        :disabled="page === 0" @click="page = Math.max(0, page - 1)">Previous</button>
                    <button type="button"
                        class="px-3 py-2 rounded-md bg-white border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
                        :disabled="page >= totalPages - 1"
                        @click="page = Math.min(totalPages - 1, page + 1)">Next</button>
                </div>
            </div>
        </div>

        <!-- Results Count -->
        <div class="px-3 py-3 bg-gray-50 border-t border-gray-200 text-sm text-gray-600">
            Showing <span class="font-medium" x-text="filteredIncidents.length"></span> results
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
                    // Severity mapping: higher value = higher severity
                    const severityMap = { critical: 4, high: 3, medium: 2, low: 1 };
                    const sortFn = (a, b) => {
                        const aSeverity = (a.severity || '').toLowerCase();
                        const bSeverity = (b.severity || '').toLowerCase();
                        const aScore = severityMap[aSeverity] || 0;
                        const bScore = severityMap[bSeverity] || 0;
                        if (aScore === bScore) {
                            // If same severity, sort by timestamp (earliest first)
                            const aTime = a.timestamp || '';
                            const bTime = b.timestamp || '';
                            return aTime.localeCompare(bTime);
                        }
                        // Higher severity first
                        return bScore - aScore;
                    };
                    let arr = this.incidents.slice();
                    if (s !== '') {
                        arr = arr.filter(i => {
                            const hay = Object.values(i).join(' ').toLowerCase();
                            return hay.indexOf(s) !== -1;
                        });
                    } else if (this.filter && this.filter !== '') {
                        arr = arr.filter(i => ((i.type || '').toLowerCase() === (this.filter || '').toLowerCase()));
                    }
                    return arr.sort(sortFn);
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