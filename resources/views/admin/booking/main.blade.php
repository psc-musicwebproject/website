<x-dash.admin.layout>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @elseif (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    <table class="table table-bordered">
        <thead>
            <tr>
                <th scope="col">รหัสการจอง</th>
                <th scope="col">ชื่อการจอง</th>
                <th scope="col">เวลาการจอง</th>
                <th scope="col">สถานะ</th>
                <th scope="col">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addBooking">เพิ่มการจองห้อง</button>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $booking)
            <tr>
                <td>{{ $booking->booking_id }}</td>
                <td>{{ $booking->booking_name }}</td>
                <td>
                    {{ \Carbon\Carbon::parse($booking->booked_from)->format('d/m/Y H:i') }} -
                    {{ \Carbon\Carbon::parse($booking->booked_to)->format('d/m/Y H:i') }}
                </td>
                <td>{{ App\Models\Booking::bookingStatusToText($booking->approval_status) }}</td>
                <td>
                    <a href="{{ route('admin.booking.detail', ['id' => $booking->booking_id]) }}" class="btn btn-primary btn-sm">ดูรายละเอียด</a>
                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteBooking-{{ $booking->booking_id }}">ลบ</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="modal fade" id="addBooking" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">เพิ่มการจองห้อง</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action=" {{ route('admin.booking.submit') }} ">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="room_id" class="form-label">ห้องที่ต้องการจอง</label>
                                    <select class="form-select" id="room_id" name="room_id" required @if(count($rooms)==0) disabled @endif>
                                        @foreach($rooms as $room)
                                        <option value="{{ $room->room_id }}">{{ $room->room_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">ระยะเวลาการจอง</label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label small">วันที่</label>
                                            <input type="date" class="form-control" id="date" name="date" placeholder="วันที่" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">เวลาเริ่ม</label>
                                            <input type="time" class="form-control" id="time_from" name="time_from" placeholder="เวลาเริ่ม" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">เวลาสิ้นสุด</label>
                                            <input type="time" class="form-control" id="time_to" name="time_to" placeholder="เวลาสิ้นสุด" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label">ชื่อการจอง</label>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="หัวข้อการจอง / ใช้ห้อง" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                        <button type="submit" class="btn btn-primary">บันทึกการจอง</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- For Delete Booking Modal -->
    @foreach($bookings as $booking)
    <div class="modal fade" id="deleteBooking-{{ $booking->booking_id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">ลบการจองห้อง</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.booking.delete', ['id' => $booking->booking_id]) }}">
                    @csrf
                    <div class="modal-body">
                        <p>คุณแน่ใจหรือไม่ว่าต้องการลบการจองห้องนี้?</p>
                        <p>ชื่อการจอง: {{ $booking->booking_name }}</p>
                        <p>โดย: {{ $booking->user->name }} {{ $booking->user->surname }} ({{ $booking->user->student_id }})</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-danger">ลบการจอง</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</x-dash.admin.layout>