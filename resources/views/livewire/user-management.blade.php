<div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-2xl font-bold text-gray-700">Users</h3>
            <button wire:click="openModal" class="bg-blue-700 hover:bg-blue-800 text-white font-semibold px-5 py-2 rounded flex items-center gap-2">
                <span>ADD USER</span>
                <i class="fa-solid fa-plus"></i>
            </button>
        </div>
        <div class="w-full">
            <table class="w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-2 text-xs font-semibold text-gray-600 uppercase tracking-wider w-8">#</th>
                        <th class="px-2 py-2 text-xs font-semibold text-gray-600 uppercase tracking-wider w-20">Name</th>
                        <th class="px-2 py-2 text-xs font-semibold text-gray-600 uppercase tracking-wider w-16">Role</th>
                        <th class="px-2 py-2 text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">Email</th>
                        <th class="px-2 py-2 text-xs font-semibold text-gray-600 uppercase tracking-wider w-20">Mobile</th>
                        <th class="px-2 py-2 text-xs font-semibold text-gray-600 uppercase tracking-wider w-20">Position</th>
                        <th class="px-2 py-2 text-xs font-semibold text-gray-600 uppercase tracking-wider w-16">Area</th>
                        <th class="px-2 py-2 text-xs font-semibold text-gray-600 uppercase tracking-wider w-16">Status</th>
                        <th class="px-2 py-2 text-xs font-semibold text-gray-600 uppercase tracking-wider w-16">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach ($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-2 py-2 text-center text-xs">{{ $loop->iteration }}</td>
                            <td class="px-2 py-2 text-xs truncate max-w-20" title="{{ $user->name }}">{{ $user->name }}</td>
                            <td class="px-2 py-2 text-xs truncate max-w-16" title="{{ $user->role ?? '-' }}">{{ $user->role ?? '-' }}</td>
                            <td class="px-2 py-2 text-xs truncate max-w-32" title="{{ $user->email }}">{{ $user->email }}</td>
                            <td class="px-2 py-2 text-xs truncate max-w-20" title="{{ $user->mobile ?? '-' }}">{{ $user->mobile ?? '-' }}</td>
                            <td class="px-2 py-2 text-xs truncate max-w-20" title="{{ $user->position ?? '-' }}">{{ $user->position ?? '-' }}</td>
                            <td class="px-2 py-2 text-xs truncate max-w-16" title="{{ $user->assigned_area ?? '-' }}">{{ $user->assigned_area ?? '-' }}</td>
                            <td class="px-2 py-2">
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $user->status === 'active' ? 'bg-gray-200 text-gray-700' : 'bg-gray-100 text-gray-400' }}">
                                    {{ ucfirst($user->status ?? 'Active') }}
                                </span>
                            </td>
                            <td class="px-2 py-2 flex items-center gap-1">
                                <button class="text-blue-600 hover:text-blue-900" title="Edit" wire:click="editUser({{ $user->id }})"><i class="fa-solid fa-pen-to-square text-xs"></i></button>
                                <button class="text-gray-500 hover:text-red-600" title="{{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}" wire:click="toggleStatus({{ $user->id }})">
                                    <i class="fa-solid fa-user-slash text-xs"></i>
                                </button>
                                <button class="text-red-600 hover:text-red-900" title="Delete" wire:click="confirmDeleteUser({{ $user->id }})"><i class="fa-solid fa-trash text-xs"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" style="display: @if($addUserModal) flex @else none @endif;">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-auto p-8 relative border border-blue-200">
            <button wire:click="closeModal" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 transition-colors">
                <i class="fa-solid fa-xmark text-2xl"></i>
            </button>
            <h2 class="text-2xl font-bold mb-6 text-blue-800">{{ $editMode ? 'Edit User' : 'Add New User' }}</h2>
            <form wire:submit.prevent="{{ $editMode ? 'updateUser' : 'saveUser' }}" class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-1">Full Name</label>
                    <input type="text" wire:model.defer="name" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-blue-400 focus:outline-none text-gray-700">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-1">Email Address</label>
                    <input type="email" wire:model.defer="email" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-blue-400 focus:outline-none text-gray-700">
                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-1">Password (Auto-generated)</label>
                    <input type="text" wire:model.defer="password" readonly class="border border-gray-300 rounded-lg w-full py-2 px-3 bg-gray-100 focus:ring-2 focus:ring-blue-400 focus:outline-none text-gray-700 cursor-not-allowed">
                    @if(!$editMode)
                        @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    @endif
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-1">Mobile Number</label>
                    <input type="text" wire:model.defer="mobile" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-blue-400 focus:outline-none text-gray-700">
                    @error('mobile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-1">Assigned Role</label>
                    <select wire:model.defer="role" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-blue-400 focus:outline-none text-gray-700">
                        <option value="">Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="responder">Responder</option>
                    </select>
                    @error('role') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-semibold mb-1">Barangay Position</label>
                    <input type="text" wire:model.defer="position" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-blue-400 focus:outline-none text-gray-700">
                    @error('position') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 text-sm font-semibold mb-1">Assigned Area (Department)</label>
                    <input type="text" wire:model.defer="assigned_area" class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-blue-400 focus:outline-none text-gray-700" placeholder="e.g. fire, med, enforcement">
                    @error('assigned_area') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-2 flex justify-end gap-2 pt-2 relative">
                    <button type="button" wire:click="closeModal" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-5 rounded-lg transition-colors">Cancel</button>
                    <button type="submit" class="bg-blue-700 hover:bg-blue-800 text-white font-semibold py-2 px-5 rounded-lg transition-colors relative" style="min-width: 110px;">
                        <span wire:loading.remove wire:target="{{ $editMode ? 'updateUser' : 'saveUser' }}">{{ $editMode ? 'Update User' : 'Add User' }}</span>
                        <span wire:loading wire:target="{{ $editMode ? 'updateUser' : 'saveUser' }}" class="flex items-center justify-center absolute inset-0 left-0 right-0 top-0 bottom-0 bg-blue-700 bg-opacity-80 rounded-lg">
                            <svg class="animate-spin h-5 w-5 text-white ml-12 mt-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('livewire:init', () => {
        window.Livewire.on('user-added', () => {
            Swal.fire({
                icon: 'success',
                title: 'User Added',
                text: 'The new user has been added successfully!',
                timer: 2000,
                showConfirmButton: false
            });
        });
        window.Livewire.on('user-updated', () => {
            Swal.fire({
                icon: 'success',
                title: 'User Updated',
                text: 'User information has been updated!',
                timer: 2000,
                showConfirmButton: false
            });
        });
        window.Livewire.on('user-status-toggled', () => {
            Swal.fire({
                icon: 'success',
                title: 'Status Changed',
                text: 'User status has been updated!',
                timer: 1500,
                showConfirmButton: false
            });
        });
        window.Livewire.on('user-deleted', () => {
            Swal.fire({
                icon: 'success',
                title: 'User Deleted',
                text: 'User account has been deleted!',
                timer: 1500,
                showConfirmButton: false
            });
        });
        window.Livewire.on('confirm-delete', (id) => {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will delete the user and all their records!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.emit('deleteUser', id);
                }
            });
        });
    });
</script>
</div>
