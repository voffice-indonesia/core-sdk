<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel') }} - Authentication</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>

<body class="bg-gray-50">
    <livewire:core-auth-callback />

    @livewireScripts
</body>

</html>
