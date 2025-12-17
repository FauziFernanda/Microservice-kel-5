<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Auth' }}</title>
    @vite('resources/css/app.css')
</head>

<body class="min-h-screen bg-gradient-to-br from-black via-gray-900 to-black flex items-center justify-center">


    <div class="w-full max-w-md p-8 rounded-2xl bg-gray-900/80 backdrop-blur shadow-2xl border border-gray-700">
        {{ $slot }}
    </div>


</body>

</html>
