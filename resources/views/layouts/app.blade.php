<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>

<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        @yield('content')
    </div>

    @livewireScripts
</body>

</html>
