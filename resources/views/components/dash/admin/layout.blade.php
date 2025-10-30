<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config('app.name', "PSC-MusicWebProject")}} - {{ $title ?? 'Dashboard' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-terriary">
    <div class="app-wrapper">
        <x-dash.navbar />
        <x-dash.admin.sidebar />
        <main class="app-main">
            <div class="app-content-header">
                <h3>{{ $title ?? 'Dashboard' }}</h3>
            </div>
            <div class="app-content">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>