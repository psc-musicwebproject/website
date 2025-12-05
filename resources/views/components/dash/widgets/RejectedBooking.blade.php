<div class="info-box">
    <span class="info-box-icon text-bg-danger shadow-sm">
        <i class="bi bi-x-lg"></i>
    </span>
    <div class="info-box-content">
        <span class="info-box-text">การจองที่ถูกปฎิเสธ</span>
        <span class="info-box-number">{{ App\Models\Booking::countCurrentUserBookings(Auth::id(),'rejected') }}</span>
    </div>
</div>