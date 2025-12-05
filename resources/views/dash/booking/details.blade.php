<x-dash.layout>
    @foreach($booking as $detail)
        <table class="table table-bordered table-striped">
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
            <tr>
                <th scope="col">สถานะ</th>
                <td>{{ App\Models\Booking::bookingStatusToText($detail->approval_status) }}</td>
            </tr>
            @if($detail->approval_status == 'approved' || $detail->approval_status == 'rejected')
            <tr>
                <th scope="col">ผู้อนุมัติ</th>
                <td>{{ $detail->approvalPerson->name }}  {{ $detail->approvalPerson->surname }}</td>
            </tr>
            <tr>
                <th scope="col">เวลาที่อนุมัติ</th>
                <td>{{ \Carbon\Carbon::parse($detail->approval_time)->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <th scope="col">สาเหตุการอนุมัติ</th>
                <td>{{ $detail->approval_comment }}</td>
            </tr>
            @endif
        </table>
    @endforeach
</x-dash.layout>