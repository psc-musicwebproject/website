<x-dash.admin.layout>
    <table class="table table-bordered table-striped">
        <tbody>
            @foreach($booking as $detail)
            <tr>
                <th scope="col">รหัสการจอง</th>
                <td>{{ $detail->booking_id }}</td>
            </tr>
            <tr>
                <th scope="col">ชื่อการจอง</th>
                <td>{{ $detail->booking_name }}</td>
            </tr>
            <tr>
                <th scope="col">เวลาการจอง</th>
                <td>
                    {{ \Carbon\Carbon::parse($detail->booked_from)->format('d/m/Y H:i') }} - 
                    {{ \Carbon\Carbon::parse($detail->booked_to)->format('d/m/Y H:i') }}
                </td>
            </tr>
            <tr>
                <th scope="col">ห้องที่จอง</th>
                <td>{{ App\Models\Room::getRoomNameByID($detail->room_id) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($detail->user->type != 'admin')
    <form action="{{ route('admin.booking.approve', $detail->booking_id) }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-header">
                    @if ($detail->approval_status == 'pending')
                        <span>อนุมัติ / ไม่อนุมัติการจอง</span>
                    @else
                        <span>อัปเดตสถานะการจอง</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="approval_comment" class="form-label">เหตุผล (ถ้ามี)</label>
                        <input type="text" class="form-control" placeholder="สาเหตุการตัดสิน" name="approval_comment">
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-end">
                        <button type="submit" name="action" value="approve" class="btn btn-success me-2">
                            <i class="bi bi-file-earmark-check-fill"></i>
                            <span>อนุมัติ</span>
                        </button>
                        <button type="submit" name="action" value="reject" class="btn btn-danger">
                            <i class="bi bi-file-earmark-excel-fill"></i>
                            <span>ไม่อนุมัติ</span>
                        </button>
                    </div>
                </div>
            </div>
    </form>
    @endif
</x-dash.admin.layout>