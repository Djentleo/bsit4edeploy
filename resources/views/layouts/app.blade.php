<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="themeSwitcher()" x-init="init()"
    :class="{ 'dark': isDark }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('baritan-logo.png') }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css?family=Figtree:300,regular,500,600,700,800,900,300italic,italic,500italic,600italic,700italic,800italic,900italic"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles
</head>

<body class="font-sans antialiased bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
    <x-banner />

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900" x-data="{ sidebarCollapsed: false }"
        @sidebar-toggle.window="sidebarCollapsed = $event.detail.collapsed">
        @livewire('navigation-menu')

        <div :class="sidebarCollapsed ? 'ml-16' : 'ml-64'" class="transition-all duration-300">
            <!-- Page Heading -->
            @if (isset($header))
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('modals')

    @livewireScripts

    {{-- Theme Switcher moved to navigation-menu sidebar --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                isDark: false,
                init() {
                    const saved = localStorage.getItem('theme');
                    if (saved === 'dark') this.isDark = true;
                    else if (saved === 'light') this.isDark = false;
                    else this.isDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                    this.apply();
                },
                toggle() {
                    this.isDark = !this.isDark;
                    localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
                    this.apply();
                },
                apply() {
                    const root = document.documentElement;
                    if (this.isDark) root.classList.add('dark');
                    else root.classList.remove('dark');
                    window.dispatchEvent(new CustomEvent('theme-changed', { detail: { isDark: this.isDark } }));
                }
            });
            Alpine.store('theme').init();
        });
    </script>

    @auth
    @if(auth()->user()->role === 'admin')
    <!-- Firebase compat SDKs (global) -->
    <script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/9.6.1/firebase-database-compat.js"></script>

    <!-- Global New Incident Alert Indicator (single toast, non-stacking) -->
    <div x-data="incidentAlerts()" x-init="init()" class="fixed bottom-4 right-4 z-50 pointer-events-none">

        <audio x-ref="notifSound" src="/sounds/new-notification-3-398649.mp3"></audio>
        <div x-show="visible" x-transition.opacity x-transition.duration.200ms
            class="pointer-events-auto w-80 rounded-lg shadow-lg overflow-hidden border"
            :class="current.source === 'mobile' ? 'bg-white border-blue-200' : 'bg-white border-amber-200'">
            <div class="p-4 flex items-start gap-3">
                <div class="shrink-0">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full"
                        :class="current.source === 'mobile' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700'">
                        <!-- Bell Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900">New Incident Detected</p>
                    <p class="mt-0.5 text-xs text-gray-600">
                        <span class="font-medium" x-text="current.source === 'mobile' ? 'Mobile' : 'CCTV'"></span>
                        <span class="mx-1">•</span>
                        <span x-text="current.when"></span>
                    </p>
                </div>
                <button type="button" class="text-gray-400 hover:text-gray-600" @click="dismiss()">
                    <span class="sr-only">Close</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
                <button type="button" class="ml-2 text-gray-400 hover:text-gray-600" @click="toggleMute()">
                    <span class="sr-only" x-text="muted ? 'Unmute' : 'Mute'"></span>
                    <svg x-show="!muted" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <svg x-show="muted" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5v14l11-7-11-7z" />
                        <line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <script>
        function incidentAlerts() {
            return {
                visible: false,
                current: { source: 'mobile', when: '' },
                dismissTimeout: null,
                muted: false,
                mobileBaseline: new Set(),
                cctvBaseline: new Set(),
                mobileReady: false,
                cctvReady: false,
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

                    const ensureFirebase = () => {
                        if (!window.firebase) return false;
                        if (!window.firebase.apps || window.firebase.apps.length === 0) {
                            try { window.firebase.initializeApp(firebaseConfig); } catch (e) {}
                        }
                        return true;
                    };

                    if (!ensureFirebase()) {
                        // Retry until SDKs are ready
                        setTimeout(() => this.setupFirebase(), 300);
                        return;
                    }

                    const db = window.firebase.database();

                    // Build baselines to avoid toasts for existing records
                    db.ref('mobile_incidents').once('value').then(snap => {
                        const data = snap.val() || {};
                        Object.keys(data).forEach(k => this.mobileBaseline.add(k));
                        this.mobileReady = true;
                        // Listen for new children
                        db.ref('mobile_incidents').on('child_added', (child) => {
                            const key = child.key;
                            if (this.mobileReady && !this.mobileBaseline.has(key)) {
                                this.pushToast('mobile');
                            }
                        });
                    });

                    db.ref('incidents').once('value').then(snap => {
                        const data = snap.val() || {};
                        Object.keys(data).forEach(k => this.cctvBaseline.add(k));
                        this.cctvReady = true;
                        // Listen for new children
                        db.ref('incidents').on('child_added', (child) => {
                            const key = child.key;
                            if (this.cctvReady && !this.cctvBaseline.has(key)) {
                                this.pushToast('cctv');
                            }
                        });
                    });
                },
                pushToast(source) {
                    const when = new Date().toLocaleTimeString();
                    this.current = { source, when };
                    this.visible = true;
                    if (!this.muted) {
                        this.$refs.notifSound.currentTime = 0;
                        this.$refs.notifSound.play();
                    }
                    if (this.dismissTimeout) clearTimeout(this.dismissTimeout);
                    this.dismissTimeout = setTimeout(() => { this.visible = false; }, 5000);
                },
                dismiss() {
                    this.visible = false;
                    if (this.dismissTimeout) clearTimeout(this.dismissTimeout);
                    this.dismissTimeout = null;
                },
                toggleMute() {
                    this.muted = !this.muted;
                },
            };
        }
    </script>
    @endif
    @endauth
</body>

</html>