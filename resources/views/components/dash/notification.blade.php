@props(['channel'])

@auth
    <!-- Toast Container -->
    <div id="toastContainer" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 99999;">
        <!-- Toasts will be dynamically inserted here -->
    </div>

    <script type="module">
        // Listen for notifications on the private channel
        window.Echo.private('{{ $channel }}')
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
@endauth
