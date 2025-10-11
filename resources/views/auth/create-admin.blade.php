<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            Create Admin Account
        </div>

        @if(session('status'))
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ session('status') }}
        </div>
        @endif

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('admin.create') }}">
            @csrf

            <div class="block mb-4">
                <x-label for="username" value="Username" />
                <x-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')"
                    required autocomplete="username" />
            </div>

            <div class="block mb-4">
                <x-label for="mobile" value="Mobile" />
                <x-input id="mobile" class="block mt-1 w-full" type="text" name="mobile" :value="old('mobile')" />
            </div>

            <div class="block mb-4">
                <x-label for="assigned_area" value="Area" />
                <x-input id="assigned_area" class="block mt-1 w-full" type="text" name="assigned_area"
                    :value="old('assigned_area')" />
            </div>

            <div class="block mb-4">
                <x-label for="status" value="Status" />
                <select id="status" name="status" class="block mt-1 w-full">
                    <option value="active" selected>Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="block mb-4">
                <x-label for="name" value="{{ __('Name') }}" />
                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required
                    autofocus />
            </div>

            <div class="block mb-4">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                    autocomplete="username" />
            </div>

            <div class="block mb-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required
                    autocomplete="new-password" />
            </div>

            <div class="block mb-4">
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password"
                    name="password_confirmation" required autocomplete="new-password" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-button>
                    Create Admin Account
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>