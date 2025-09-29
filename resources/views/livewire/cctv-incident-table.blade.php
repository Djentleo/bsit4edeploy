<!-- Firebase compat SDKs for browser -->
<script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-database-compat.js"></script>

<div class="max-w-full" x-data="cctvIncidents()" x-init="init()">
    <!-- Switch Source Toggle -->
    <div class="flex justify-end mb-6">
    <div class="relative inline-flex rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            <button type="button" onclick="window.location.href='{{ route('incidents.mobile') }}'"
                class="relative px-4 py-2.5 text-sm font-medium focus:outline-none transition-all duration-200 {{ request()->is('mobile-incident-table') ? 'bg-blue-600 text-white' : 'text-gray-600 dark:text-gray-200 hover:text-white hover:bg-[#1C3A5B] dark:hover:bg-gray-900' }} rounded-l-lg"
                {{ request()->is('mobile-incident-table') ? 'disabled' : '' }}>
                <span class="flex items-center gap-2">
                    <i class="fas fa-mobile-alt"></i>
                    Mobile
                </span>
            </button>
            <button type="button" onclick="window.location.href='{{ route('incidents.cctv') }}'"
                class="relative px-4 py-2.5 text-sm font-medium focus:outline-none transition-all duration-200 {{ request()->is('cctv-incident-table') ? 'bg-blue-600 text-white' : 'text-gray-600 dark:text-gray-200 hover:text-white hover:bg-[#1C3A5B] dark:hover:bg-gray-900' }} rounded-r-lg"
                {{ request()->is('cctv-incident-table') ? 'disabled' : '' }}>
                <span class="flex items-center gap-2">
                    <i class="fas fa-video"></i>
                    CCTV
                </span>
            </button>
        </div>
    </div>
    <!-- Search Row -->

    <div class="flex flex-row gap-4 mb-6">
        <div class="relative flex-grow">
            <input type="search" x-model.debounce.150ms="search" @keydown.enter.prevent autocomplete="off"
                class="w-full pl-10 pr-4 py-2.5 border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-sm text-gray-600 dark:text-white"
                placeholder="Search CCTV incidents...">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>
        <div class="relative w-48">
            <select x-model="filter"
                class="w-full px-4 py-2.5 appearance-none border border-gray-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-sm text-gray-600 dark:text-white">
                <option value="">All Events</option>
                <option value="Accident">Accident</option>
                <option value="Fire">Fire</option>
                <!-- Add more event types here if needed -->
            </select>
            <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                <svg class="h-5 w-5 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="w-full overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#1C3A5B] dark:bg-gray-900">
                    <tr class="text-left">
                        <th class="px-6 py-3 text-xs font-medium text-white uppercase tracking-wider">CAMERA NAME</th>
                        <th class="px-6 py-3 text-xs font-medium text-white uppercase tracking-wider">EVENT</th>
                        <th class="px-6 py-3 text-xs font-medium text-white uppercase tracking-wider">SCREENSHOT</th>
                        <th class="px-6 py-3 text-xs font-medium text-white uppercase tracking-wider">VIDEO</th>
                        <th class="px-6 py-3 text-xs font-medium text-white uppercase tracking-wider">TIMESTAMP</th>
                        <th class="px-6 py-3 text-xs font-medium text-white uppercase tracking-wider">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    <template x-for="incident in paginatedIncidents" :key="incident.firebase_id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                            <td class="px-6 py-4 text-gray-900 dark:text-white font-medium" x-text="incident.camera_name"></td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-300" x-text="incident.event"></td>
                            <td class="px-6 py-4">
                                <template x-if="incident.screenshot_path">
                                    <img :src="incident.screenshot_path" alt="Screenshot"
                                        class="h-16 w-auto rounded shadow border border-gray-200 dark:border-gray-600" />
                                </template>
                                <template x-if="!incident.screenshot_path">
                                    <span class="text-gray-400 dark:text-gray-500 text-sm">No image</span>
                                </template>
                            </td>
                            <td class="px-6 py-4">
                                <template x-if="incident.camera_url">
                                    <a :href="incident.camera_url" target="_blank" 
                                       class="inline-flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 underline text-sm">
                                        <i class="fas fa-play-circle mr-1"></i>
                                        View Video
                                    </a>
                                </template>
                                <template x-if="!incident.camera_url">
                                    <span class="text-gray-400 dark:text-gray-500 text-sm">No video</span>
                                </template>
                            </td>
                            <td class="px-6 py-4 text-gray-500 dark:text-gray-400 text-sm" x-text="incident.timestamp_formatted || incident.timestamp">
                            </td>
                            <td class="px-6 py-4">
                                <a :href="'/dispatch?incident_id=' + encodeURIComponent(incident.firebase_id)"
                                    class="inline-flex items-center px-3 py-2 rounded-md bg-blue-600 dark:bg-blue-700 text-white text-sm font-medium hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors duration-150">
                                    <i class="fas fa-eye mr-1"></i>
                                    View
                                </a>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="paginatedIncidents.length === 0">
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-900">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-video text-gray-400 dark:text-gray-500 text-2xl mb-2"></i>
                                <p class="text-sm">No CCTV incidents found.</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination and Results -->
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Showing <span x-text="filteredIncidents.length"></span> results
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        Page <span x-text="page + 1"></span> of <span x-text="totalPages"></span>
                    </span>
                    <button type="button" 
                        class="px-3 py-2 rounded-md bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-150"
                        :disabled="page === 0" @click="page = Math.max(0, page - 1)">
                        <i class="fas fa-chevron-left"></i>
                        Prev
                    </button>
                    <button type="button" 
                        class="px-3 py-2 rounded-md bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-150"
                        :disabled="page >= totalPages - 1"
                        @click="page = Math.min(totalPages - 1, page + 1)">
                        Next
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function cctvIncidents(initial) {
            return {
                incidents: [],
                init() {
                    this.setupFirebase();
                },
                setupFirebase() {
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
                    const incidentsRef = db.ref('incidents');
                    incidentsRef.on('value', (snapshot) => {
                        const data = snapshot.val() || {};
                        this.incidents = Object.entries(data).map(([firebase_id, incident]) => ({ firebase_id, ...incident }));
                    });
                },
                search: '',
                filter: '',
                page: 0,
                pageSize: 10,
                get filteredIncidents() {
                    const s = (this.search || '').toLowerCase().trim();
                    const f = (this.filter || '').toLowerCase().trim();
                    const sortByTimeDesc = arr => arr.slice().sort((a, b) => (b.timestamp || '').localeCompare(a.timestamp || ''));
                    let filtered = this.incidents;
                    if (s !== '') {
                        filtered = filtered.filter(i => {
                            const hay = Object.values(i).join(' ').toLowerCase();
                            return hay.indexOf(s) !== -1;
                        });
                    }
                    if (f !== '') {
                        filtered = filtered.filter(i => ((i.event || '').toLowerCase() === f));
                    }
                    return sortByTimeDesc(filtered);
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