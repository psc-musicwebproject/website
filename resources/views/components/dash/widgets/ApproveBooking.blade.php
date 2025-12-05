<div class="info-box">
    <span class="info-box-icon text-bg-success shadow-sm">
        <i class="bi bi-check-lg"></i>
    </span>
    <div class="info-box-content">
        <span class="info-box-text">การจองที่อนุมัติ</span>
        <span class="info-box-number">{{ App\Models\Booking::countCurrentUserBookings(Auth::id(),'approved') }}</span>
    </div>
</div>