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
    <!-- Status Management Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mt-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Incident Status</h3>
        <form wire:submit.prevent="updateStatus" class="flex items-center gap-3">
            <select wire:model="status"
                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-900 focus:ring-2 focus:ring-blue-500">
                @foreach($statusOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors">Update</button>
        </form>
        <div class="mt-2 text-xs text-gray-500">Current status: <span class="font-semibold text-gray-800">{{
                ucfirst($status) }}</span></div>
    </div>

    <!-- Internal Notes Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mt-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Internal Notes</h3>
        <form wire:submit.prevent="addNote" class="mb-4">
            <textarea wire:model.defer="newNote" rows="2" placeholder="Add a note..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
            <div class="flex justify-end mt-2">
                <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                    Add Note
                </button>
            </div>
        </form>
        <div class="space-y-3 max-h-48 overflow-y-auto">
            @forelse($notes as $note)
            <div class="bg-gray-50 border border-gray-200 rounded p-2">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-semibold text-gray-800">{{ $note->user->name ?? 'Unknown' }}</span>
                    <span class="text-xs text-gray-500">{{ $note->created_at->diffForHumans() }}</span>
                </div>
                <div class="text-gray-700 text-sm">{{ $note->note }}</div>
            </div>
            @empty
            <div class="text-gray-400 text-sm">No notes yet.</div>
            @endforelse
        </div>
    </div>

    <!-- Timeline / Activity Log Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mt-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-3">Incident Timeline</h3>
        <div class="space-y-3 max-h-48 overflow-y-auto">
            @forelse($timeline as $entry)
            <div class="bg-gray-50 border border-gray-200 rounded p-2">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-semibold text-gray-800">{{ $entry->user->name ?? 'System' }}</span>
                    <span class="text-xs text-gray-500">{{ $entry->created_at->diffForHumans() }}</span>
                </div>
                <div class="text-gray-700 text-sm">
                    @if($entry->action === 'status_changed')
                    <span>Status changed: <span class="font-semibold">{{ ucfirst($entry->details) }}</span></span>
                    @elseif($entry->action === 'note_added')
                    <span>Note added</span>
                    @else
                    <span>{{ ucfirst($entry->action) }}</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-gray-400 text-sm">No timeline entries yet.</div>
            @endforelse
        </div>
    </div>
</div>