<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-900 tracking-tight">Responder</h2>
    </x-slot>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
            <div class="h-16 flex items-center px-6 font-bold text-xl text-blue-800 border-b border-gray-100">
                Responder
            </div>
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="{{ route('responder.incidents') }}"
                    class="block px-4 py-2 rounded hover:bg-blue-50 font-medium text-gray-700">
                    <i class="fa-solid fa-table-list mr-2"></i> Incident Table
                </a>
                <a href="{{ route('responder.history') }}"
                    class="block px-4 py-2 rounded hover:bg-blue-50 font-medium text-gray-700">
                    <i class="fa-solid fa-clock-rotate-left mr-2"></i> Incident History
                </a>
            </nav>
            <div class="px-4 pb-6">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold">Log
                        Out</button>
                </form>
            </div>
        </aside>
        <!-- Main Content -->
        <main class="flex-1 bg-gray-50 p-8">
            {{ $slot }}
        </main>
    </div>
</x-app-layout>