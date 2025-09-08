<nav x-data="{ open: false, collapsed: false }" :class="collapsed ? 'w-16' : 'w-64'"
    class="h-screen bg-blue-900 border-r border-gray-200 fixed top-0 left-0 z-30 flex flex-col transition-all duration-300">

    <!-- Toggle Button -->
    <div class="absolute -right-3 top-6 z-40">
        <button @click="collapsed = !collapsed; $dispatch('sidebar-toggle', { collapsed: collapsed })"
            class="bg-blue-900 text-white rounded-full p-1 border-2 border-white hover:bg-blue-800 transition-colors">
            <svg :class="collapsed ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-300" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
    </div>

    <!-- Logo -->
    <div class="h-20 flex items-center justify-center">
        <a
            href="{{ Auth::user()->role === 'admin' ? route('dashboard') : (Auth::user()->role === 'responder' ? route('responder.incidents') : '#') }}">
            <x-application-mark :class="collapsed ? 'h-6 w-auto' : 'h-9 w-auto'"
                class="block transition-all duration-300" />
        </a>
    </div>
    <!-- Navigation Links -->
    <div class="flex-1 flex flex-col py-6">
        <div class="flex flex-col space-y-2 px-6" x-show="!collapsed" x-transition>
            @if (Auth::user()->role === 'admin')
            <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-nav-link>
            <x-nav-link href="{{ route('users.index') }}" :active="request()->routeIs('users.*')">
                {{ __('User Management') }}
            </x-nav-link>
            <div x-data="{ open: false }">
                <button @click="open = !open"
                    class="w-full flex items-center justify-between px-1 py-2 text-sm font-medium rounded-md text-gray-300 hover:text-white hover:bg-blue-800 transition-colors focus:outline-none">
                    <span>{{ __('Incident Tables') }}</span>
                    <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-300" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="open" x-transition class="pl-4 mt-1">
                    <x-nav-link href="{{ route('incidents.mobile') }}" :active="request()->routeIs('incidents.mobile')">
                        {{ __('Mobile Incident Table') }}
                    </x-nav-link>
                    <x-nav-link href="{{ route('incidents.cctv') }}" :active="request()->routeIs('incidents.cctv')">
                        {{ __('CCTV Incident Table') }}
                    </x-nav-link>
                </div>
            </div>
            <x-nav-link href="{{ route('incident.logs') }}" :active="request()->routeIs('incident.logs')">
                {{ __('Incident Logs') }}
            </x-nav-link>
            @elseif (Auth::user()->role === 'responder')
            <x-nav-link href="{{ route('responder.incidents') }}" :active="request()->routeIs('responder.incidents')">
                {{ __('Incident Table') }}
            </x-nav-link>
            <x-nav-link href="{{ route('responder.history') }}" :active="request()->routeIs('responder.history')">
                {{ __('Incident History') }}
            </x-nav-link>
            @endif
        </div>

        <!-- Collapsed Navigation Icons -->
        <div class="flex flex-col space-y-4 px-4" x-show="collapsed" x-transition>
            @if (Auth::user()->role === 'admin')
            <a href="{{ route('dashboard') }}"
                class="flex items-center justify-center p-2 rounded-md {{ request()->routeIs('dashboard') ? 'bg-blue-800 text-white' : 'text-gray-300 hover:text-white hover:bg-blue-800' }} transition-colors"
                title="Dashboard">
                <i class="fa-solid fa-house"></i>
            </a>
            <a href="{{ route('users.index') }}"
                class="flex items-center justify-center p-2 rounded-md {{ request()->routeIs('users.*') ? 'bg-blue-800 text-white' : 'text-gray-300 hover:text-white hover:bg-blue-800' }} transition-colors"
                title="User Management">
                <i class="fa-solid fa-users"></i>
            </a>
            <a href="{{ route('incidents.mobile') }}"
                class="flex items-center justify-center p-2 rounded-md {{ request()->routeIs('incidents.mobile') ? 'bg-blue-800 text-white' : 'text-gray-300 hover:text-white hover:bg-blue-800' }} transition-colors"
                title="Mobile Incidents">
                <i class="fa-solid fa-table"></i>
            </a>
            <a href="{{ route('incidents.cctv') }}"
                class="flex items-center justify-center p-2 rounded-md {{ request()->routeIs('incidents.cctv') ? 'bg-blue-800 text-white' : 'text-gray-300 hover:text-white hover:bg-blue-800' }} transition-colors"
                title="CCTV Incidents">
                <i class="fa-solid fa-table"></i>
            </a>
            <a href="{{ route('incident.logs') }}"
                class="flex items-center justify-center p-2 rounded-md {{ request()->routeIs('incident.logs') ? 'bg-blue-800 text-white' : 'text-gray-300 hover:text-white hover:bg-blue-800' }} transition-colors"
                title="Incident Logs">
                <i class="fa-solid fa-file-alt"></i>
            </a>
            @elseif (Auth::user()->role === 'responder')
            <a href="{{ route('responder.incidents') }}"
                class="flex items-center justify-center p-2 rounded-md {{ request()->routeIs('responder.incidents') ? 'bg-blue-800 text-white' : 'text-gray-300 hover:text-white hover:bg-blue-800' }} transition-colors"
                title="Incident Table">
                <i class="fa-solid fa-table"></i>
            </a>
            <a href="{{ route('responder.history') }}"
                class="flex items-center justify-center p-2 rounded-md {{ request()->routeIs('responder.history') ? 'bg-blue-800 text-white' : 'text-gray-300 hover:text-white hover:bg-blue-800' }} transition-colors"
                title="Incident History">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </a>
            @endif
        </div>
        <!-- Teams Dropdown -->
        @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
        <div class="mt-8 px-6" x-show="!collapsed" x-transition>
            <x-dropdown align="right" width="60">
                <x-slot name="trigger">
                    <span class="inline-flex rounded-md w-full">
                        <button type="button"
                            class="w-full inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                            {{ Auth::user()->currentTeam->name }}
                            <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                            </svg>
                        </button>
                    </span>
                </x-slot>
                <x-slot name="content">
                    <div class="w-60">
                        <!-- Team Management -->
                        <div class="block px-4 py-2 text-xs text-gray-400">
                            {{ __('Manage Team') }}
                        </div>
                        <!-- Team Settings -->
                        <x-dropdown-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}">
                            {{ __('Team Settings') }}
                        </x-dropdown-link>
                        @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                        <x-dropdown-link href="{{ route('teams.create') }}">
                            {{ __('Create New Team') }}
                        </x-dropdown-link>
                        @endcan
                        <!-- Team Switcher -->
                        @if (Auth::user()->allTeams()->count() > 1)
                        <div class="border-t border-gray-200"></div>
                        <div class="block px-4 py-2 text-xs text-gray-400">
                            {{ __('Switch Teams') }}
                        </div>
                        @foreach (Auth::user()->allTeams() as $team)
                        <x-switchable-team :team="$team" />
                        @endforeach
                        @endif
                    </div>
                </x-slot>
            </x-dropdown>
        </div>
        @endif
    </div>
    <!-- Settings Dropdown at bottom -->
    <div class="px-6 py-4  mt-auto" x-data="{ dropdownOpen: false }" class="relative" x-show="!collapsed" x-transition>
        <div class="relative">
            <button @click="dropdownOpen = !dropdownOpen"
                class="w-full inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                <img class="size-8 rounded-full object-cover mr-2" src="{{ Auth::user()->profile_photo_url }}"
                    alt="{{ Auth::user()->name }}" />
                @endif
                {{ Auth::user()->name }}
                <span class="flex-1"></span>
                <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            <!-- Dropdown Menu - Opens Upward -->
            <div x-show="dropdownOpen" x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95" @click.away="dropdownOpen = false"
                class="absolute bottom-full mb-2 right-0 w-48 bg-white rounded-md shadow-lg py-1 z-50 ring-1 ring-black ring-opacity-5">

                <!-- Account Management -->
                <div class="block px-4 py-2 text-xs text-gray-400">
                    {{ __('Manage Account') }}
                </div>
                <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    {{ __('Profile') }}
                </a>
                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                <a href="{{ route('api-tokens.index') }}"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    {{ __('API Tokens') }}
                </a>
                @endif
                <div class="border-t border-gray-200"></div>
                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Collapsed Settings Button -->
    <div class="px-4 py-4 border-t border-gray-100 mt-auto" x-show="collapsed" x-transition
        x-data="{ dropdownOpen: false }" class="relative">
        <button @click="dropdownOpen = !dropdownOpen"
            class="w-full flex items-center justify-center p-2 rounded-md text-gray-300 hover:text-white hover:bg-blue-800 transition-colors"
            title="{{ Auth::user()->name }}">
            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
            <img class="size-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}"
                alt="{{ Auth::user()->name }}" />
            @else
            <i class="fa-solid fa-user"></i>
            @endif
        </button>

        <!-- Collapsed Dropdown Menu -->
        <div x-show="dropdownOpen" x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95" @click.away="dropdownOpen = false"
            class="fixed bottom-1 left-20 w-48 bg-white rounded-md shadow-lg py-1 z-50 ring-1 ring-black ring-opacity-5">

            <!-- Account Management -->
            <div class="block px-4 py-2 text-xs text-gray-400">
                {{ __('Manage Account') }}
            </div>
            <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                {{ __('Profile') }}
            </a>
            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
            <a href="{{ route('api-tokens.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                {{ __('API Tokens') }}
            </a>
            @endif
            <div class="border-t border-gray-200"></div>
            <!-- Authentication -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </div>
</nav>