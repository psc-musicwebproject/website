<x-dash.layout>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th scope="col">รหัสการจอง</th>
                <th scope="col">ชื่อการจอง</th>
                <th scope="col">เวลาการจอง</th>
                <th scope="col">สถานะ</th>
                <th scope="col"></th>
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
                        <a href="{{ route('dash.booking.history.detail', ['id' => $booking->booking_id]) }}" class="btn btn-primary btn-sm">ดูรายละเอียด</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-dash.layout>