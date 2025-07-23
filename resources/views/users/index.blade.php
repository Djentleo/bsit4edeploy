<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('User Management') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-2xl font-bold text-gray-700">Users</h3>
                    <button @click="addUserModal = true" class="bg-blue-700 hover:bg-blue-800 text-white font-semibold px-5 py-2 rounded flex items-center gap-2">
                        <span>ADD USER</span>
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
                <div>
                    <table id="myTable" class="w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-xs font-semibold text-gray-600 uppercase tracking-wider">#</th>
                                <th class="px-4 py-2 text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                                <th class="px-4 py-2 text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
                                <th class="px-4 py-2 text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                                <th class="px-4 py-2 text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-2 text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($users as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 text-center">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-2">{{ $user->name }}</td>
                                    <td class="px-4 py-2">{{ $user->role ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $user->email }}</td>
                                    <td class="px-4 py-2">
                                        <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full
                                            {{ $user->status === 'active' ? 'bg-gray-200 text-gray-700' : 'bg-gray-100 text-gray-400' }}">
                                            {{ ucfirst($user->status ?? 'Active') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 flex items-center gap-2">
                                        <button class="text-blue-600 hover:text-blue-900" title="Edit"><i class="fa-solid fa-pen-to-square"></i></button>
                                        <button class="text-gray-500 hover:text-red-600" title="Deactivate"><i class="fa-solid fa-user-slash"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Modal Placeholder -->
    <div x-data="{ addUserModal: false }">
        <div x-show="addUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-auto p-6 relative">
                <button @click="addUserModal = false" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
                <h2 class="text-lg font-semibold mb-4">Add New User</h2>
                <!-- Add user form fields here -->
                <div class="text-gray-500">Form coming soon...</div>
            </div>
        </div>
    </div>
    
</x-app-layout>

    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('#myTable').DataTable();
        });
    </script>