<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Sign in to your account
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                You will be redirected to our secure authentication server
            </p>
        </div>

        @if ($errorMessage)
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ $errorMessage }}</span>
            </div>
        @endif

        <div class="mt-8 space-y-6">
            <button wire:click="redirectToOAuth" wire:loading.attr="disabled"
                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="redirectToOAuth">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1">
                        </path>
                    </svg>
                    Sign in with OAuth
                </span>

                <span wire:loading wire:target="redirectToOAuth" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Redirecting...
                </span>
            </button>

            <div class="text-center">
                <p class="text-xs text-gray-500">
                    By signing in, you agree to our
                    <a href="#" class="text-indigo-600 hover:text-indigo-500">Terms of Service</a>
                    and
                    <a href="#" class="text-indigo-600 hover:text-indigo-500">Privacy Policy</a>
                </p>
            </div>
        </div>
    </div>
</div>
