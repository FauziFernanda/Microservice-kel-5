<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <!-- Vite manifest not found — fall back to CDN styles so the welcome page still loads -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>body{background:#FDFDFC}</style>
    @endif
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
    <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6">
        <nav class="flex items-center justify-end gap-4">
            <a href="{{ url('/dashboard') }}" class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] border rounded-sm">Dashboard</a>
        </nav>
    </header>

    <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow">
        <main class="flex max-w-4xl w-full">
            <div class="w-full p-12 bg-white dark:bg-[#161615] dark:text-[#EDEDEC] shadow rounded-lg text-center">
                <h1 class="text-4xl font-bold mb-4">Welcome to Englicious. Let’s check out today’s news!</h1>
                <p class="text-lg text-gray-600 dark:text-gray-300 mb-4">Explore the latest articles and updates curated for you.</p>
                <a href="/news" class="btn btn-primary">Go to News Management</a>
            </div>
        </main>
    </div>
</body>
</html>