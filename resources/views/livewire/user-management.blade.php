<div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-6">
            <button wire:click="openModal"
                class="bg-[#1C3A5B] hover:bg-[#1C3A5B]/80 text-white font-semibold px-5 py-2 rounded flex items-center gap-2">
                <span>ADD USER</span>
                <i class="fa-solid fa-plus"></i>
            </button>
        </div>

        <!-- Search Bar -->
        <div class="flex flex-col md:flex-row gap-4 mb-6 items-center">
            <div class="relative flex-grow w-full md:w-auto">
                <input type="search" wire:model.live="search" autocomplete="off"
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-sm text-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Search users...">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
            <div>
                <select wire:model.live="roleFilter"
                    class="pl-3 pr-8 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-sm text-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="responder">Responder</option>
                    <option value="cctv">CCTV</option>
                </select>
            </div>
            <div>
                <button wire:click="clearFilters"
                    class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200 text-sm">
                    Clear Filters
                </button>
            </div>
        </div>

        @if($users->count() > 0)
        <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow">
            <table class="w-full table-auto">
                <thead style="background-color: #1C3A5B;" class="border-b border-gray-200 dark:border-gray-700">
                    <tr class="text-center">
                        <th class="px-4 py-3 text-xs font-medium text-white uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-xs font-medium text-white uppercase tracking-wider">Name</th>
                        <th class="px-4 py-3 text-xs font-medium text-white uppercase tracking-wider">Username</th>
                        <th class="px-4 py-3 text-xs font-medium text-white uppercase tracking-wider">Role</th>
                        <th class="px-4 py-3 text-xs font-medium text-white uppercase tracking-wider">Email</th>
                        <th
                            class="px-4 py-3 text-xs font-medium text-white uppercase tracking-wider hidden sm:table-cell">
                            Mobile</th>
                        <th
                            class="px-4 py-3 text-xs font-medium text-white uppercase tracking-wider hidden lg:table-cell">
                            Area</th>
                        <th class="px-4 py-3 text-xs font-medium text-white uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-xs font-medium text-white uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @foreach ($users as $user)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-2 py-2 text-center text-xs dark:text-white">{{ $loop->iteration }}</td>
                        <td class="px-2 py-2 text-center text-xs truncate max-w-20 dark:text-white"
                            title="{{ $user->name }}">{{ $user->name }}</td>
                        <td class="px-2 py-2 text-center text-xs truncate max-w-20 dark:text-white"
                            title="{{ $user->username }}">{{ $user->username
                            }}</td>
                        <td class="px-2 py-2 text-center text-xs truncate max-w-16 dark:text-white"
                            title="{{ $user->role ?? '-' }}">
                            @if($user->role)
                            <span
                                class="inline-block px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : 
                                       ($user->role === 'responder' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 
                                       ($user->role === 'cctv' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200')) }}">
                                {{ ucfirst($user->role) }}
                            </span>
                            @else
                            <span class="text-gray-400 dark:text-gray-200">-</span>
                            @endif
                        </td>
                        <td class="px-2 py-2 text-center text-xs truncate max-w-32 dark:text-white"
                            title="{{ $user->email }}">{{ $user->email }}
                        </td>
                        <td class="px-2 py-2 text-center text-xs truncate max-w-20 dark:text-white"
                            title="{{ $user->mobile ?? '-' }}">{{
                            $user->mobile ?? '-' }}</td>
                        <td class="px-2 py-2 text-center text-xs truncate max-w-16 dark:text-white"
                            title="{{ $user->assigned_area ?? '-' }}">{{
                            $user->assigned_area ?? '-' }}</td>
                        <td class="px-2 py-2 text-center">
                            <span
                                class="inline-block px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $user->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                {{ ucfirst($user->status ?? 'Active') }}
                            </span>
                        </td>
                        <td class="px-2 py-2 flex items-center justify-center gap-2">
                            <button
                                class="text-blue-600 dark:text-blue-300 hover:text-blue-900 dark:hover:text-blue-400 p-1 rounded hover:bg-blue-50 dark:hover:bg-blue-900 transition-colors"
                                title="Edit" wire:click="editUser({{ $user->id }})">
                                <i class="fa-solid fa-pen-to-square text-xs"></i>
                            </button>
                            <button
                                class="text-amber-600 dark:text-amber-300 hover:text-amber-900 dark:hover:text-amber-400 p-1 rounded hover:bg-amber-50 dark:hover:bg-amber-900 transition-colors"
                                title="{{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}"
                                wire:click="toggleStatus({{ $user->id }})">
                                <i
                                    class="fa-solid fa-user-{{ $user->status === 'active' ? 'slash' : 'plus' }} text-xs"></i>
                            </button>
                            <button x-data="{}" @click.prevent="Swal.fire({
                                        title: 'Are you sure?',
                                        text: 'This will delete the user and all their records!',
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonColor: '#d33',
                                        cancelButtonColor: '#3085d6',
                                        confirmButtonText: 'Yes, delete it!'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            $wire.deleteUser({{ $user->id }});
                                        }
                                    })"
                                class="text-red-600 dark:text-red-300 hover:text-red-900 dark:hover:text-red-400 p-1 rounded hover:bg-red-50 dark:hover:bg-red-900 transition-colors"
                                title="Delete">
                                <i class="fa-solid fa-trash text-xs"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-8 text-center">
            <div class="flex flex-col items-center">
                <svg class="w-16 h-16 text-gray-400 dark:text-gray-200 mb-4" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                    </path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No users found</h3>
                <p class="text-gray-500 dark:text-gray-200 mb-4">
                    @if($search || $roleFilter !== 'all')
                    No users match your current search criteria. Try adjusting your filters.
                    @else
                    Get started by adding your first user to the system.
                    @endif
                </p>
                @if($search || $roleFilter !== 'all')
                <button wire:click="clearFilters"
                    class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 dark:hover:bg-blue-900 transition-colors duration-200">
                    Clear Filters
                </button>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40"
        style="display: @if($addUserModal) flex @else none @endif;">
        <div
            class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-lg mx-auto p-8 relative border border-blue-200 dark:border-blue-700">
            <button wire:click="closeModal"
                class="absolute top-3 right-3 text-gray-400 dark:text-gray-200 hover:text-gray-700 dark:hover:text-white transition-colors">
                <i class="fa-solid fa-xmark text-2xl"></i>
            </button>
            <h2 class="text-2xl font-bold mb-6 text-blue-800 dark:text-blue-200">{{ $editMode ? 'Edit User' : 'Add New
                User' }}</h2>
            <form wire:submit.prevent="{{ $editMode ? 'updateUser' : 'saveUser' }}"
                class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2">
                <div>
                    <label class="block text-gray-700 dark:text-white text-sm font-semibold mb-1">Full Name</label>
                    <input type="text" wire:model.defer="name"
                        class="border border-gray-300 dark:border-gray-700 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-blue-400 focus:outline-none text-gray-700 dark:text-white bg-white dark:bg-gray-900">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-gray-700 dark:text-white text-sm font-semibold mb-1">Username</label>
                    <input type="text" wire:model.defer="username"
                        class="border border-gray-300 dark:border-gray-700 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-blue-400 focus:outline-none text-gray-700 dark:text-white bg-white dark:bg-gray-900">
                    @error('username') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-gray-700 dark:text-white text-sm font-semibold mb-1">Email Address</label>
                    <input type="email" wire:model.defer="email"
                        class="border border-gray-300 dark:border-gray-700 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-blue-400 focus:outline-none text-gray-700 dark:text-white bg-white dark:bg-gray-900">
                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-gray-700 dark:text-white text-sm font-semibold mb-1">Password
                        (Auto-generated)</label>
                    <input type="text" wire:model.defer="password" readonly
                        class="border border-gray-300 dark:border-gray-700 rounded-lg w-full py-2 px-3 bg-gray-100 dark:bg-gray-800 focus:ring-2 focus:ring-blue-400 focus:outline-none text-gray-700 dark:text-white cursor-not-allowed">
                    @if(!$editMode)
                    @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    @endif
                </div>
                <div>
                    <label class="block text-gray-700 dark:text-white text-sm font-semibold mb-1">Mobile Number</label>
                    <input type="text" wire:model.defer="mobile"
                        class="border border-gray-300 dark:border-gray-700 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-blue-400 focus:outline-none text-gray-700 dark:text-white bg-white dark:bg-gray-900">
                    @error('mobile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div x-data="{ role: @entangle('role') }">
                    <label class="block text-gray-700 dark:text-white text-sm font-semibold mb-1">Assigned Role</label>
                    <select x-model="role" wire:model="role"
                        class="border border-gray-300 dark:border-gray-700 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-blue-400 focus:outline-none text-gray-700 dark:text-white bg-white dark:bg-gray-900">
                        <option value="">Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="responder">Responder</option>
                        <option value="cctv">CCTV</option>
                    </select>
                    @error('role') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div x-data="{ role: @entangle('role') }" x-show="role === 'responder'" class="md:col-span-2 -mt-2"
                    x-cloak>
                    <label class="block text-gray-700 dark:text-white text-sm font-semibold mb-1">Responder Type</label>
                    <select wire:model.defer="responder_type"
                        class="border border-gray-300 dark:border-gray-700 rounded-lg w-full py-0 px-3 focus:ring-2 focus:ring-blue-400 focus:outline-none text-gray-700 dark:text-white bg-white dark:bg-gray-900">
                        <option value="">Select Responder Type</option>
                        <option value="police">Police / Peace & Order</option>
                        <option value="fire">Fire Department</option>
                        <option value="medical">Medical / Health Services</option>
                        <option value="tanod">Barangay Tanod / Community Responder</option>
                    </select>
                    @error('responder_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 dark:text-white text-sm font-semibold mb-1">Assigned Area
                        (Department)</label>
                    <input type="text" wire:model.defer="assigned_area"
                        class="border border-gray-300 dark:border-gray-700 rounded-lg w-full py-2 px-3 focus:ring-2 focus:ring-blue-400 focus:outline-none text-gray-700 dark:text-white bg-white dark:bg-gray-900"
                        placeholder="e.g. fire, med, enforcement">
                    @error('assigned_area') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-2 flex justify-end gap-2 pt-2 relative">
                    <button type="button" wire:click="closeModal"
                        class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-800 text-gray-800 dark:text-white font-semibold py-2 px-5 rounded-lg transition-colors">Cancel</button>
                    <button type="submit"
                        class="bg-blue-700 hover:bg-blue-800 dark:bg-blue-900 dark:hover:bg-blue-800 text-white font-semibold py-2 px-5 rounded-lg transition-colors relative"
                        style="min-width: 110px;">
                        <span wire:loading.remove wire:target="{{ $editMode ? 'updateUser' : 'saveUser' }}">{{ $editMode
                            ? 'Update User' : 'Add User' }}</span>
                        <span wire:loading wire:target="{{ $editMode ? 'updateUser' : 'saveUser' }}"
                            class="flex items-center justify-center absolute inset-0 left-0 right-0 top-0 bottom-0 bg-blue-700 dark:bg-blue-900 bg-opacity-80 rounded-lg">
                            <svg class="animate-spin h-5 w-5 text-white ml-12 mt-2" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                </path>
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
                    icon: 'success'
                    , title: 'User Added'
                    , text: 'The new user has been added successfully!'
                    , timer: 2000
                    , showConfirmButton: false
                });
            });
            window.Livewire.on('user-updated', () => {
                Swal.fire({
                    icon: 'success'
                    , title: 'User Updated'
                    , text: 'User information has been updated!'
                    , timer: 2000
                    , showConfirmButton: false
                });
            });
            window.Livewire.on('user-status-toggled', () => {
                Swal.fire({
                    icon: 'success'
                    , title: 'Status Changed'
                    , text: 'User status has been updated!'
                    , timer: 1500
                    , showConfirmButton: false
                });
            });
            window.Livewire.on('user-deleted', () => {
                Swal.fire({
                    icon: 'success'
                    , title: 'User Deleted'
                    , text: 'User account has been deleted!'
                    , timer: 1500
                    , showConfirmButton: false
                });
            });
            window.Livewire.on('confirm-delete', (id) => {
                Swal.fire({
                    title: 'Are you sure?'
                    , text: 'This will delete the user and all their records!'
                    , icon: 'warning'
                    , showCancelButton: true
                    , confirmButtonColor: '#d33'
                    , cancelButtonColor: '#3085d6'
                    , confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.emit('deleteUser', id);
                    }
                });
            });
        });

    </script>
</div>