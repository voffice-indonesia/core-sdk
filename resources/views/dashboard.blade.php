<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>

<body class="bg-gray-50">
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-gray-900">
                        {{ config('app.name', 'Laravel') }}
                    </h1>
                </div>

                <div class="flex items-center">
                    <livewire:core-auth-status />
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="border-4 border-dashed border-gray-200 rounded-lg h-96 p-8">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">
                        Welcome to your Dashboard!
                    </h2>
                    <p class="text-gray-600 mb-8">
                        You have successfully authenticated using the Core SDK.
                    </p>

                    @auth(config('core.guard_name', 'core'))
                        <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">
                                        Authentication Status: Active
                                    </h3>
                                    <div class="mt-2 text-sm text-green-700">
                                        <p>User: {{ auth()->guard(config('core.guard_name', 'core'))->user()->getName() }}
                                        </p>
                                        <p>Email: {{ auth()->guard(config('core.guard_name', 'core'))->user()->getEmail() }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endauth

                    <p class="text-sm text-gray-500">
                        This is a sample dashboard page. Customize it according to your needs.
                    </p>
                </div>
            </div>
        </div>
    </main>

    @livewireScripts
</body>

</html>
