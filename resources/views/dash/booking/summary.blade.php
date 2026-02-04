@extends('layouts.dash')

@section('content')
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ชื่อการจอง</th>
                <th>เวลา</th>
                <th>สถานะ</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $booking)
                <tr>
                    <td>{{ $booking->booking_name }}</td>
                    <td>{{ $booking->booking_time }}</td>
                    <td>{{ App\Models\Booking::bookingStatusToText($booking->approval_status) }}</td>
                    <td>
                        <a href="{{ route('dash.booking.history.detail', ['id' => $booking->booking_id]) }}" class="btn btn-primary btn-sm">
                            ดูรายละเอียด
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
