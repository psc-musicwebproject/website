<div class="info-box">
    <span class="info-box-icon text-bg-warning shadow-sm">
        <i class="bi bi-files"></i>
    </span>
    <div class="info-box-content">
        <span class="info-box-text">การจองที่รออนุมัติ</span>
        <span class="info-box-number">{{ App\Models\Booking::countAllBookings('waiting') }}</span>
    </div>
</div>