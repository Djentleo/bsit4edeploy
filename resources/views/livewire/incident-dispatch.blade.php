<div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Assign Responders</h3>
        <div class="space-y-3">
            <!-- Main Responder Dropdown -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Main Responder</label>
                <select wire:model="mainResponder"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-900 focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Responder</option>
                    @foreach($this->allResponders as $responder)
                    <option value="{{ $responder->id }}">{{ $responder->name }} ({{ $responder->responder_type ?? 'N/A'
                        }})</option>
                    @endforeach
                </select>
            </div>
            <!-- Additional Responders -->
            @foreach($additionalResponders as $index => $responderId)
            <div class="flex items-center space-x-2 mt-2">
                <select wire:model="additionalResponders.{{ $index }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-900 focus:ring-2 focus:ring-blue-500">
                    <option value="">Select Additional Responder</option>
                    @foreach($this->allResponders as $responder)
                    <option value="{{ $responder->id }}">{{ $responder->name }} ({{ $responder->responder_type ?? 'N/A'
                        }})</option>
                    @endforeach
                </select>
                <button type="button" wire:click="removeResponder({{ $index }})"
                    class="text-red-500 hover:text-red-700 px-2 py-1 rounded" title="Remove">&times;</button>
            </div>
            @endforeach
            <!-- Add Another Responder Button -->
            <button type="button" wire:click="addResponder"
                class="mt-3 w-full bg-gray-100 text-blue-700 py-2 px-4 rounded-lg font-medium border border-blue-200 hover:bg-blue-200 transition-colors">
                + Add Another Responder
            </button>
        </div>
    </div>
    <!-- Dispatch Button -->
    <button type="button" wire:click="dispatchIncident"
        class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-semibold text-lg hover:bg-blue-700 transition-colors mt-2">
        Dispatch
    </button>
    @if($successMessage)
    <div class="mt-3 p-2 bg-green-100 text-green-800 rounded">{{ $successMessage }}</div>
    @endif
    @if($errorMessage)
    <div class="mt-3 p-2 bg-red-100 text-red-800 rounded">{{ $errorMessage }}</div>
    @endif
</div>