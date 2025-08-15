<div class="overflow-hidden">
    @if($incidents && count($incidents) > 0)
        <!-- Desktop Table View -->
        <div class="hidden lg:block">
            <table class="w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">
                            ID
                        </th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                            Type & Severity
                        </th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Location & Reporter
                        </th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-28">
                            Source
                        </th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                            Status & Dept
                        </th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-28">
                            Date
                        </th>
                        <th scope="col" class="relative px-3 py-3 w-20">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($incidents as $key => $incident)
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <!-- ID Column -->
                            <td class="px-3 py-4">
                                <div class="text-xs font-mono text-gray-900 bg-gray-100 px-2 py-1 rounded truncate">
                                    {{ Str::limit($key, 6) }}
                                </div>
                            </td>
                            
                            <!-- Type & Severity Column -->
                            <td class="px-3 py-4">
                                <div class="space-y-1">
                                    @php
                                        $typeColors = [
                                            'fire' => 'bg-red-100 text-red-800',
                                            'medical_emergency' => 'bg-purple-100 text-purple-800',
                                            'vehicular_accident' => 'bg-yellow-100 text-yellow-800',
                                            'test_type' => 'bg-gray-100 text-gray-800'
                                        ];
                                        $typeColor = $typeColors[$incident['type'] ?? 'test_type'] ?? 'bg-gray-100 text-gray-800';
                                        
                                        $severityColors = [
                                            'high' => 'bg-red-100 text-red-800',
                                            'medium' => 'bg-yellow-100 text-yellow-800',
                                            'low' => 'bg-green-100 text-green-800',
                                            'test_severity' => 'bg-gray-100 text-gray-800'
                                        ];
                                        $severityColor = $severityColors[$incident['severity'] ?? 'test_severity'] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $typeColor }}">
                                        {{ ucfirst(str_replace(['_', 'vehicular'], [' ', 'vehicle'], $incident['type'] ?? 'N/A')) }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $severityColor }}">
                                        @if(($incident['severity'] ?? '') === 'high')
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                        @endif
                                        {{ ucfirst($incident['severity'] ?? 'N/A') }}
                                    </span>
                                </div>
                            </td>
                            
                            <!-- Location & Reporter Column -->
                            <td class="px-3 py-4">
                                <div class="space-y-2">
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 text-gray-400 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <div class="text-xs text-gray-900 truncate">{{ $incident['location'] ?? 'N/A' }}</div>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="h-5 w-5 rounded-full bg-gray-300 flex items-center justify-center mr-2 flex-shrink-0">
                                            <span class="text-xs font-medium text-gray-700">
                                                {{ strtoupper(substr($incident['reporter_name'] ?? 'N', 0, 1)) }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-900 truncate">
                                            {{ $incident['reporter_name'] ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Source Column -->
                            <td class="px-3 py-4">
                                @php
                                    $sourceIcons = [
                                        'mobile' => '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a1 1 0 001-1V4a1 1 0 00-1-1H8a1 1 0 00-1 1v16a1 1 0 001 1z"></path></svg>',
                                        'cctv_ai' => '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>',
                                        'test_source' => '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>'
                                    ];
                                    $sourceIcon = $sourceIcons[$incident['source'] ?? 'test_source'] ?? $sourceIcons['test_source'];
                                @endphp
                                <div class="flex items-center text-xs text-gray-900">
                                    <div class="text-gray-400 mr-1">{!! $sourceIcon !!}</div>
                                    <span class="truncate">{{ ucfirst(str_replace('_', ' ', $incident['source'] ?? 'N/A')) }}</span>
                                </div>
                            </td>
                            
                            <!-- Status & Department Column -->
                            <td class="px-3 py-4">
                                <div class="space-y-1">
                                    @php
                                        $statusColors = [
                                            'new' => 'bg-blue-100 text-blue-800',
                                            'dispatched' => 'bg-yellow-100 text-yellow-800',
                                            'resolved' => 'bg-green-100 text-green-800',
                                            'test_status' => 'bg-gray-100 text-gray-800'
                                        ];
                                        $statusColor = $statusColors[$incident['status'] ?? 'test_status'] ?? 'bg-gray-100 text-gray-800';
                                        
                                        $deptColors = [
                                            'police' => 'bg-blue-100 text-blue-800',
                                            'fire' => 'bg-red-100 text-red-800',
                                            'health' => 'bg-green-100 text-green-800',
                                            'test_department' => 'bg-gray-100 text-gray-800'
                                        ];
                                        $deptColor = $deptColors[$incident['department'] ?? 'test_department'] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColor }}">
                                        @if(($incident['status'] ?? '') === 'new')
                                            <div class="w-1.5 h-1.5 bg-current rounded-full mr-1"></div>
                                        @elseif(($incident['status'] ?? '') === 'dispatched')
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                        @elseif(($incident['status'] ?? '') === 'resolved')
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        @endif
                                        {{ ucfirst($incident['status'] ?? 'N/A') }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $deptColor }}">
                                        {{ ucfirst($incident['department'] ?? 'N/A') }}
                                    </span>
                                </div>
                            </td>
                            
                            <!-- Date Column -->
                            <td class="px-3 py-4 text-xs text-gray-500">
                                @php
                                    $timestamp = $incident['timestamp'] ?? '';
                                    if($timestamp) {
                                        try {
                                            $date = \Carbon\Carbon::parse($timestamp);
                                            $formattedDate = $date->format('M j');
                                            $formattedTime = $date->format('g:i A');
                                        } catch (Exception $e) {
                                            $formattedDate = 'Invalid';
                                            $formattedTime = 'Date';
                                        }
                                    } else {
                                        $formattedDate = 'N/A';
                                        $formattedTime = '';
                                    }
                                @endphp
                                <div class="space-y-1">
                                    <div class="text-gray-900 font-medium">{{ $formattedDate }}</div>
                                    @if($formattedTime)
                                        <div class="text-gray-500">{{ $formattedTime }}</div>
                                    @endif
                                </div>
                            </td>
                            
                            <!-- Actions Column -->
                            <td class="px-3 py-4 text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-1">
                                    <button class="text-indigo-600 hover:text-indigo-900 p-1 rounded transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    <button class="text-gray-600 hover:text-gray-900 p-1 rounded transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Mobile/Tablet Card View -->
        <div class="lg:hidden space-y-4">
            @foreach($incidents as $key => $incident)
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow duration-200">
                    <!-- Header Row -->
                    <div class="flex items-center justify-between mb-3">
                        <div class="text-sm font-mono text-gray-900 bg-gray-100 px-2 py-1 rounded">
                            {{ Str::limit($key, 8) }}
                        </div>
                        <div class="flex items-center space-x-1">
                            <button class="text-indigo-600 hover:text-indigo-900 p-1 rounded transition-colors duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                            <button class="text-gray-600 hover:text-gray-900 p-1 rounded transition-colors duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Type and Severity -->
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        @php
                            $typeColors = [
                                'fire' => 'bg-red-100 text-red-800',
                                'medical_emergency' => 'bg-purple-100 text-purple-800',
                                'vehicular_accident' => 'bg-yellow-100 text-yellow-800',
                                'test_type' => 'bg-gray-100 text-gray-800'
                            ];
                            $typeColor = $typeColors[$incident['type'] ?? 'test_type'] ?? 'bg-gray-100 text-gray-800';
                            
                            $severityColors = [
                                'high' => 'bg-red-100 text-red-800',
                                'medium' => 'bg-yellow-100 text-yellow-800',
                                'low' => 'bg-green-100 text-green-800',
                                'test_severity' => 'bg-gray-100 text-gray-800'
                            ];
                            $severityColor = $severityColors[$incident['severity'] ?? 'test_severity'] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeColor }}">
                            {{ ucfirst(str_replace(['_', 'vehicular'], [' ', 'vehicle'], $incident['type'] ?? 'N/A')) }}
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $severityColor }}">
                            @if(($incident['severity'] ?? '') === 'high')
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            @endif
                            {{ ucfirst($incident['severity'] ?? 'N/A') }}
                        </span>
                    </div>
                    
                    <!-- Location and Reporter -->
                    <div class="space-y-2 mb-3">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <div class="text-sm text-gray-900">{{ $incident['location'] ?? 'N/A' }}</div>
                        </div>
                        <div class="flex items-center">
                            <div class="h-6 w-6 rounded-full bg-gray-300 flex items-center justify-center mr-2">
                                <span class="text-xs font-medium text-gray-700">
                                    {{ strtoupper(substr($incident['reporter_name'] ?? 'N', 0, 1)) }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-900">
                                {{ $incident['reporter_name'] ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status, Department, Source, Date -->
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        @php
                            $statusColors = [
                                'new' => 'bg-blue-100 text-blue-800',
                                'dispatched' => 'bg-yellow-100 text-yellow-800',
                                'resolved' => 'bg-green-100 text-green-800',
                                'test_status' => 'bg-gray-100 text-gray-800'
                            ];
                            $statusColor = $statusColors[$incident['status'] ?? 'test_status'] ?? 'bg-gray-100 text-gray-800';
                            
                            $deptColors = [
                                'police' => 'bg-blue-100 text-blue-800',
                                'fire' => 'bg-red-100 text-red-800',
                                'health' => 'bg-green-100 text-green-800',
                                'test_department' => 'bg-gray-100 text-gray-800'
                            ];
                            $deptColor = $deptColors[$incident['department'] ?? 'test_department'] ?? 'bg-gray-100 text-gray-800';
                            
                            $timestamp = $incident['timestamp'] ?? '';
                            if($timestamp) {
                                try {
                                    $date = \Carbon\Carbon::parse($timestamp);
                                    $formattedDate = $date->format('M j, Y g:i A');
                                } catch (Exception $e) {
                                    $formattedDate = 'Invalid Date';
                                }
                            } else {
                                $formattedDate = 'N/A';
                            }
                        @endphp
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $statusColor }}">
                            @if(($incident['status'] ?? '') === 'new')
                                <div class="w-1.5 h-1.5 bg-current rounded-full mr-1"></div>
                            @elseif(($incident['status'] ?? '') === 'dispatched')
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            @elseif(($incident['status'] ?? '') === 'resolved')
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            @endif
                            {{ ucfirst($incident['status'] ?? 'N/A') }}
                        </span>
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $deptColor }}">
                            {{ ucfirst($incident['department'] ?? 'N/A') }}
                        </span>
                        <span class="text-gray-500">{{ ucfirst(str_replace('_', ' ', $incident['source'] ?? 'N/A')) }}</span>
                        <span class="text-gray-500">{{ $formattedDate }}</span>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        <div class="bg-white px-6 py-3 border-t border-gray-200 flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
                <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Previous
                </button>
                <button class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Next
                </button>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium">1</span> to <span class="font-medium">{{ count($incidents) }}</span> of <span class="font-medium">{{ count($incidents) }}</span> results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <button class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Previous</span>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <button class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                            1
                        </button>
                        <button class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Next</span>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No incidents</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by reporting a new incident.</p>
            <div class="mt-6">
                <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    New Incident
                </button>
            </div>
        </div>
    @endif
</div>
