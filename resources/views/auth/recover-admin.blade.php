<x-guest-layout>
    <x-authentication-card class="w-full sm:max-w-2xl">
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-4 text-sm text-gray-600 text-center">
            Admin Recovery: Enter the recovery key and new admin details.
        </div>

        @if(session('status'))
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ session('status') }}
        </div>
        @endif

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('admin.recover') }}">
            @csrf

            <!-- Recovery Key - Full Width -->
            <div class="mb-4">
                <x-label for="recovery_key" value="Recovery Key" />
                <x-input id="recovery_key" class="block mt-1 w-full" type="text" name="recovery_key" required
                    autofocus />
            </div>

            <!-- Two Column Layout for Form Fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <!-- Left Column -->
                <div class="space-y-4">
                    <div>
                        <x-label for="username" value="Username" />
                        <x-input id="username" class="block mt-1 w-full" type="text" name="username"
                            :value="old('username')" required autocomplete="username" />
                    </div>

                    <div>
                        <x-label for="name" value="Name" />
                        <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')"
                            required />
                    </div>

                    <div>
                        <x-label for="email" value="Email" />
                        <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                            required autocomplete="username" />
                    </div>

                    <div>
                        <x-label for="mobile" value="Mobile" />
                        <x-input id="mobile" class="block mt-1 w-full" type="text" name="mobile"
                            :value="old('mobile')" />
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <div>
                        <x-label for="assigned_area" value="Area" />
                        <x-input id="assigned_area" class="block mt-1 w-full" type="text" name="assigned_area"
                            :value="old('assigned_area')" />
                    </div>

                    <div>
                        <x-label for="status" value="Status" />
                        <select id="status" name="status"
                            class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div>
                        <x-label for="password" value="Password" />
                        <x-input id="password" class="block mt-1 w-full" type="password" name="password" required
                            autocomplete="new-password" />
                    </div>

                    <div>
                        <x-label for="password_confirmation" value="Confirm Password" />
                        <x-input id="password_confirmation" class="block mt-1 w-full" type="password"
                            name="password_confirmation" required autocomplete="new-password" />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-button>
                    Recover Admin Account
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>