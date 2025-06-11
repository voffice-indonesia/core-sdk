<div class="relative">
    @if ($isAuthenticated)
        <!-- User Menu -->
        <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-700">Welcome, {{ $userName }}</span>

            <div class="relative">
                <button wire:click="toggleUserMenu"
                    class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <div class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center">
                        <span class="text-white text-sm font-medium">
                            {{ substr($userName, 0, 1) }}
                        </span>
                    </div>
                    <svg class="ml-2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                @if ($showUserMenu)
                    <div
                        class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-sm text-gray-900 font-medium">{{ $userName }}</p>
                            <p class="text-sm text-gray-500">{{ $userEmail }}</p>
                        </div>

                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Profile
                        </a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            Settings
                        </a>

                        <div class="border-t border-gray-100">
                            <button wire:click="logout"
                                class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                Sign out
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
        <!-- Login Button -->
        <a href="{{ route('vauth.redirect') }}"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013 3v1"></path>
            </svg>
            Sign In
        </a>
    @endif
</div>

@script
    <script>
        // Close user menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('[wire\\:click="toggleUserMenu"]') &&
                !event.target.closest('.absolute.right-0')) {
                @this.showUserMenu = false;
            }
        });
    </script>
@endscript
