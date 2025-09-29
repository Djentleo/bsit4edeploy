<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl dark:text-white text-gray-800 leading-tight">
            Incident Logs
        </h2>
    </x-slot>

    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold mb-4"></h1>
        <livewire:incident-logs-table />
    </div>
</x-app-layout>