<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            @if ($processing)
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    Processing Authentication
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Please wait while we verify your credentials...
                </p>
            @elseif($success)
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    Authentication Successful!
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    You will be redirected to your dashboard shortly...
                </p>

                <div class="mt-4">
                    <a href="{{ $redirectUrl }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Continue to Dashboard
                        <svg class="ml-2 -mr-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5-5 5M6 12h12"></path>
                        </svg>
                    </a>
                </div>
            @elseif($error)
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </div>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    Authentication Failed
                </h2>
                <p class="mt-2 text-sm text-red-600">
                    {{ $error }}
                </p>

                <div class="mt-4">
                    <a href="{{ route('vauth.redirect') }}"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Try Again
                        <svg class="ml-2 -mr-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@if ($success && $redirectUrl)
    <script>
        document.addEventListener('livewire:init', function() {
            Livewire.on('redirect-after-delay', (event) => {
                setTimeout(() => {
                    window.location.href = event.url;
                }, event.delay);
            });
        });
    </script>
@endif
