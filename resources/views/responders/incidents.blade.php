<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-2xl dark:text-white text-gray-900 tracking-tight">Assigned Incidents</h2>
            @auth
            @if(auth()->user()->role === 'responder')
            <div class="ml-4">
                @livewire('responder-notification-bell')
            </div>
            @endif
            @endauth
        </div>
    </x-slot>

    <div class="container mx-auto py-8">
        @livewire('responder-dashboard')
    </div>
</x-app-layout>