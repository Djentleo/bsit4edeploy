<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl dark:text-white text-gray-900 tracking-tight">Assigned Incidents</h2>
    </x-slot>

    <div class="container mx-auto py-8">
        @livewire('responder-dashboard')
    </div>
</x-app-layout>