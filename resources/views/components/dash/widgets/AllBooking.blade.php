<div class="info-box">
    <span class="info-box-icon text-bg-primary shadow-sm">
        <i class="bi bi-calendar3"></i>
    </span>
    <div class="info-box-content">
        <span class="info-box-text">จำนวนการจอง</span>
        <span class="info-box-number">{{ App\Models\Booking::countCurrentUserBookings(Auth::id()) }}</span>
    </div>
</div>