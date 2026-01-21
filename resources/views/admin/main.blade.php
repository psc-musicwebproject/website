<x-dash.admin.layout>
    <div class="row">
        <div class="col-12">
            <div class="row">
                <div class="col-12 col-sm-6 col-md-6 mb-3">
                    <x-dash.admin.widgets.AllBooking />
                </div>
                <div class="col-12 col-sm-6 col-md-6 mb-3">
                    <x-dash.admin.widgets.WaitingBooking />
                </div>
            </div>
        </div>
        <div class="col-12 mt-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">การจองที่รออนุมัติ</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>ชื่อการจอง</th>
                                    <th>เวลาการจอง</th>
                                    <th>บุคคลที่จอง</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (App\Models\Booking::countAllBookings('waiting') != 0)
                                    @foreach (App\Models\Booking::getAllBookings('waiting', 5) as $booking)
                                    <tr>
                                        <td>{{ $booking->booking_name }}</td>
                                        <td class="text-nowrap">{{ $booking->booking_time }}</td>
                                        <td class="text-nowrap">{{ $booking->user->name }} {{ $booking->user->surname }} ({{ $booking->user->student_id }})</td>
                                        <td class="text-nowrap">
                                            <a href="{{ route('admin.booking.detail', ['id' => $booking->booking_id]) }}" class="btn btn-info btn-sm">
                                                ดูรายละเอียด
                                            </a>
                                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveBooking-{{ $booking->booking_id }}">
                                                อนุมัติ
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectBooking-{{ $booking->booking_id }}">
                                                ปฏิเสธ
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center">ไม่มีการจองที่รออนุมัติ</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 mt-4">
                <x-dash.widgets.calendar :eventRoute="route('admin.calendar.events')" />
        </div>
    </div>

    <!-- Modal for approval -->
    @foreach (App\Models\Booking::getAllBookings('waiting', 5) as $booking)
    <div class="modal fade" id="approveBooking-{{ $booking->booking_id }}" tabindex="-1" aria-labelledby="approveBookingModalLabel{{ $booking->booking_id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveBookingModalLabel{{ $booking->booking_id }}">อนุมัติการจอง: {{ $booking->booking_name }} [{{ $booking->user->name }} {{ $booking->user->surname }} ({{ $booking->user->student_id }})]</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.booking.approve', ['id' => $booking->booking_id]) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="approval_comment{{ $booking->booking_id }}" class="form-label">เหตุผล (ถ้ามี)</label>
                            <textarea class="form-control" id="approval_comment{{ $booking->booking_id }}" name="approval_comment" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">อนุมัติการจอง</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectBooking-{{ $booking->booking_id }}" tabindex="-1" aria-labelledby="rejectBookingModalLabel{{ $booking->booking_id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectBookingModalLabel{{ $booking->booking_id }}">ปฏิเสธการจอง: {{ $booking->booking_name }} [{{ $booking->user->name }} {{ $booking->user->surname }} ({{ $booking->user->student_id }})]</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.booking.delete', ['id' => $booking->booking_id]) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="rejection_comment{{ $booking->booking_id }}" class="form-label">เหตุผล (ถ้ามี)</label>
                            <textarea class="form-control" id="rejection_comment{{ $booking->booking_id }}" name="approval_comment" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-danger">ปฏิเสธการจอง</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

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
</x-dash.admin.layout>
