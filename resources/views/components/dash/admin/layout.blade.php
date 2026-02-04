@props(['title' => 'Dashboard'])

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $AppSetting::getSetting('name') ?? config('app.name', 'PSC-MusicWebProject') }} -
        {{ $title ?? 'Dashboard' }}</title>
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
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <div>{{ session('success') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @elseif(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <div>{{ session('error') }}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                {{ $slot }}
            </div>
        </main>

    </div>

    @auth
        @if (auth()->user()->type === 'admin')
            <x-dash.notification channel="admin.{{ auth()->id() }}" />
        @endif
    @endauth
</body>

</html>
