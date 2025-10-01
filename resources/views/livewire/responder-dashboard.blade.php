<div>
    <!-- Search & Filter Controls -->
    <div class="flex flex-col lg:flex-row gap-4 mb-6">
        <div class="relative flex-grow w-full md:w-auto">
            <input type="search" wire:model.live="search" autocomplete="off"
                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-sm text-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Search incidents...">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>
        <div class="flex gap-4">
            <div>
                <select wire:model="filterStatus"
                    class="pl-3 pr-8 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-sm text-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Status: All</option>
                    @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model="filterType"
                    class="pl-3 pr-8 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-sm text-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Type: All</option>
                    @php
                    $types = collect($incidents)->pluck('incident.type')->filter()->unique()->sort()->values();
                    @endphp
                    @foreach($types as $type)
                    <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if(count($incidents) === 0)
    <div class="text-gray-500">No incidents assigned to you.</div>
    @else
    <div class="overflow-x-auto bg-white dark:bg-gray-900 rounded-lg shadow">
        <table class="w-full table-auto">
            <thead style="background-color: #1C3A5B;" class="border-b border-gray-200 dark:border-gray-700">
                <tr class="text-center">
                    <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Reporter
                        Name</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Status
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Source
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Location
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Date &
                        Time</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($incidents as $item)
                <tr class="hover:bg-gray-50">
                    <!-- Reporter Name -->
                    <td class="px-6 py-4 text-sm font-medium text-gray-900 text-center">{{
                        $item['incident']['reporter_name'] ?? 'N/A' }}</td>
                    <!-- Type -->
                    <td class="px-6 py-4 text-sm font-medium text-gray-900 text-center">{{ $item['incident']['type'] ??
                        $item['incident']['event'] ?? 'N/A' }}</td>
                    <!-- Status -->
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                    @if($item['status'] === 'resolved') bg-green-100 text-green-800
                                    @elseif($item['status'] === 'en_route') bg-yellow-100 text-yellow-800
                                    @elseif($item['status'] === 'closed') bg-gray-100 text-gray-800
                                    @else bg-blue-100 text-blue-800
                                    @endif">
                            {{ $statusOptions[$item['status']] ?? ucfirst($item['status']) }}
                        </span>
                    </td>
                    <!-- Source -->
                    <td class="px-6 py-4 text-sm text-gray-900 text-center">{{ $item['incident']['source'] ?? 'N/A' }}
                    </td>
                    <!-- Location -->
                    <td class="px-6 py-4 text-sm text-gray-900 text-center">{{ $item['incident']['location'] ??
                        $item['incident']['camera_name'] ?? 'N/A' }}</td>
                    <!-- Date & Time -->
                    <td class="px-6 py-4 text-sm text-gray-900 text-center">{{ $item['incident']['datetime'] ??
                        $item['incident']['date_time'] ?? $item['incident']['timestamp'] ?? 'N/A' }}</td>
                    <!-- Actions -->
                    <td class="px-6 py-4 text-sm font-medium text-center">
                        <a href="{{ route('responder.incident-details', $item['dispatch_id']) }}"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        </table>
    </div>

    <!-- Pagination Controls -->
    <div class="flex justify-between items-center mt-6 pt-4 border-t border-gray-200">
        <div class="text-sm text-gray-600">
            Showing
            <span class="font-medium">{{ ($page - 1) * $perPage + 1 }}</span>
            to
            <span class="font-medium">{{ min($page * $perPage, $total) }}</span>
            of
            <span class="font-medium">{{ $total }}</span>
            incidents
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="prevPage" @if($page===1) disabled @endif
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">Prev</button>
            <span class="text-sm font-medium text-gray-700">Page {{ $page }}</span>
            <button wire:click="nextPage" @if($page * $perPage>= $total) disabled @endif class="px-4 py-2 text-sm
                font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50
                disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
        </div>
    </div>
    @endif

    <!-- Incident Modal removed: now using dedicated page -->
</div>