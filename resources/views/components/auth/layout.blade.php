<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config('app.name', "PSC-MusicWebProject")}}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page bg-body-secondary">
    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <img src="{{ asset("/assets/image/logo.png") }}" alt="Logo" class="mx-auto mb-1 d-block" style="max-height: 3.5rem">
                <p class="mb-0 text-center fs-6 fw-bold">{{ config('app.name', "PSC-MusicWeb Project") }}</p>
            </div>
            <div class="card-body login-card-body">
                {{ $slot }}
            </div>
            <div class="card-footer text-center">
                <small class="text-muted">{{now()->year}} - Toonshouin! , ArmGameXD</small>
                <small class="text-muted"> | v{{ config('app.version', '1.0.0') }}</small>
            </div>
        </div>
    </div>
    
</body>
</html>