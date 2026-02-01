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
                @yield('content')
            </div>
        </main>

        @auth
            @if (auth()->user()->type === 'admin')
                <!-- Toast Container -->
                <div id="toastContainer" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 99999;">
                    <!-- Toasts will be dynamically inserted here -->
                </div>
            @endif
        @endauth
    </div>

    @auth
        @if (auth()->user()->type === 'admin')
            <script type="module">
                // Listen for booking notifications on the admin's private channel
                window.Echo.private('App.Models.User.{{ auth()->id() }}')
                    .notification((notification) => {
                        console.log('Notification received:', notification);

                        // Display notification using Bootstrap toast
                        if (notification.message) {
                            const container = document.getElementById('toastContainer');

                            // Create a new toast element
                            const toastEl = document.createElement('div');
                            toastEl.className = 'toast';
                            toastEl.setAttribute('role', 'alert');
                            toastEl.setAttribute('aria-live', 'assertive');
                            toastEl.setAttribute('aria-atomic', 'true');

                            toastEl.innerHTML = `
                        <div class="toast-header bg-primary text-white">
                            <i class="bi bi-bell-fill me-2"></i>
                            <strong class="me-auto">การแจ้งเตือน</strong>
                            <small class="notification-time">ตอนนี้</small>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">
                            ${notification.message}
                        </div>
                    `;

                            // Append to container
                            container.appendChild(toastEl);

                            // Show the toast using Bootstrap
                            const toast = new bootstrap.Toast(toastEl, {
                                autohide: true,
                                delay: 10000
                            });
                            toast.show();

                            // Remove from DOM after it's hidden
                            toastEl.addEventListener('hidden.bs.toast', () => {
                                toastEl.remove();
                            });
                        }
                    });
            </script>
        @endif
    @endauth
    @stack('scripts')
</body>

</html>
