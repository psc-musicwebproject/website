<x-dash.layout>
    <div class="row">
        <div class="col-12 col-sm-6 col-md-3">
            <x-dash.widgets.AllBooking />
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <x-dash.widgets.ApproveBooking />
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <x-dash.widgets.WaitingBooking />
        </div>
        <div class="col-12 col-sm-6 col-md-3">
            <x-dash.widgets.RejectedBooking />
        </div>
    </div>

    <x-dash.widgets.calendar />

    <!-- Booking Detail Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookingModalLabel">รายละเอียดการจอง</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>ห้อง:</strong> <span id="modalRoom"></span></p>
                    <p><strong>หัวข้อ:</strong> <span id="modalTitle"></span></p>
                    <p><strong>ผู้จอง:</strong> <span id="modalOwner"></span></p>
                    <p><strong>เวลา:</strong> <span id="modalTime"></span></p>
                    <div id="modalAttendeesSection" style="display: none;">
                        <p><strong>ผู้เข้าร่วม:</strong></p>
                        <ul id="modalAttendeesList"></ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" id="modalDetailBtn" class="btn btn-primary" style="display: none;">ดูรายละเอียดเพิ่มเติม</a>
                </div>
            </div>
        </div>
    </div>
</x-dash.layout>


