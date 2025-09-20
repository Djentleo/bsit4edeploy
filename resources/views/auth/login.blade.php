<x-guest-layout>
    <div class="h-screen flex overflow-hidden">
        <!-- Left Side - Login Form -->
        <div class="flex-1 flex flex-col justify-center py-4 px-4 sm:px-6 lg:flex-none lg:px-20 xl:px-24 bg-white shadow-lg">
            <div class="mx-auto w-full max-w-sm lg:w-96 max-h-full overflow-y-auto">
                <!-- Logo -->
                <div class="mb-4 flex-shrink-0">
                    <x-authentication-card-logo />
                </div>

                <!-- Login Form -->
                <div class="flex-shrink-0">
                    <h2 class="mt-2 text-3xl font-semibold text-gray-900 text-center">
                        Sign in to your account
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 text-center">
                        Barangay Staff Portal
                    </p>
                </div>

                <div class="mt-4 flex-shrink-0">
                    <x-validation-errors class="mb-3" />

                    @session('status')
                        <div class="mb-3 font-medium text-sm text-green-600">
                            {{ $value }}
                        </div>
                    @endsession

                    <form method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf

                        <div>
                            <x-label for="email" value="{{ __('Email') }}" />
                            <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                        </div>

                        <div>
                            <x-label for="password" value="{{ __('Password') }}" />
                            <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
                        </div>

                        <div class="flex items-center justify-between">
                            <label for="remember_me" class="flex items-center">
                                <x-checkbox id="remember_me" name="remember" />
                                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a class="text-sm text-indigo-600 hover:text-indigo-500" href="{{ route('password.request') }}">
                                    {{ __('Forgot your password?') }}
                                </a>
                            @endif
                        </div>

                        <div>
                            <x-button class="w-full flex justify-center">
                                {{ __('Log in') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Side - Video Background -->
        <div class="hidden lg:block relative w-0 flex-1 overflow-hidden rounded-l-2xl">
            <!-- Video Background -->
            <video autoplay muted loop class="absolute inset-0 w-full h-full object-cover">
                <source src="{{ asset('videos/background-video.mp4') }}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            
            <!-- Blue Overlay Filter -->
            <div class="absolute inset-0 bg-blue-600 bg-opacity-70 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-blue-900 bg-opacity-30"></div>
            
            <!-- Content Overlay -->
            <div class="relative z-10 flex items-center justify-center h-full px-12">
                <div class="text-center text-white w-full">
                    <h1 class="text-4xl font-bold mb-6 drop-shadow-lg">
                        AI-Based Incident Report & Management System
                    </h1>
                    <p class="text-xl mb-6 opacity-90 drop-shadow-md">
                        Barangay Baritan, Malabon City
                    </p>
                    <div class="mt-8">
                        <p class="text-2xl font-semibold mb-4 drop-shadow-md italic">
                            "Your Community. Your Safety. Our Priority."
                        </p>
                    </div>
                </div>
                <!-- Credits -->
                <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 w-full flex justify-center pointer-events-none select-none">
                    <span class="bg-blue-900 bg-opacity-80 px-4 py-1 rounded-full text-xs text-white shadow font-medium pointer-events-auto">
                        &copy; {{ date('Y') }} BSIT - 4E Group 3. All rights reserved.
                    </span>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
