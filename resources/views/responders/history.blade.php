<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl dark:text-white text-gray-900 tracking-tight">Incident History</h2>
    </x-slot>

    <div class="container mx-auto py-8">
        @livewire('responders.responder-history')
    </div>
</x-app-layout>
