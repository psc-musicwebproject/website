<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    public function events(Request $request)
    {
        $bookings = Booking::with(['room', 'user'])
            ->where('approval_status', 'approved')
            ->get();

        $events = $bookings->map(function ($booking) {
            $user = Auth::user();
            // Check if admin
            $isAdmin = Auth::guard('admin')->check();

            // Determine viewing permissions
            $canViewDetails = $isAdmin || ($user && $booking->isOwnerOrAttendee($user));

            // Prepare Owner Name
            $ownerName = $booking->user->name . ' ' . $booking->user->surname; // Assuming User has name/surname
            if (!$canViewDetails) {
                // Redact Name: Be... Li...
                $nameParts = explode(' ', $ownerName);
                $redactedParts = array_map(function ($part) {
                    return mb_substr($part, 0, 2) . 'xxx';
                }, $nameParts);
                $ownerName = implode(' ', $redactedParts);
            }

            return [
                'id' => $booking->booking_id, // User requested booking_id
                'title' => \App\Models\Room::getRoomNameByID($booking->room_id) . ' - ' . $booking->booking_name,
                'start' => $booking->booked_from,
                'end' => $booking->booked_to,
                'extendedProps' => [
                    'can_view_detail' => $canViewDetails,
                    'owner_name' => $ownerName,
                    'room_name' => \App\Models\Room::getRoomNameByID($booking->room_id) ?? 'Unknown Room',
                    'booking_name' => $booking->booking_name,
                    'start_formatted' => \Carbon\Carbon::parse($booking->booked_from)->format('d/m/Y H:i'),
                    'end_formatted' => \Carbon\Carbon::parse($booking->booked_to)->format('d/m/Y H:i'),
                    // Dynamic Detail URL
                    'detail_url' => $isAdmin
                        ? route('admin.booking.detail', ['id' => $booking->booking_id])
                        : route('dash.booking.history.detail', ['id' => $booking->booking_id]),
                    // Only send attendees if authorized? User said "attendee list won't show"
                    'attendees' => $canViewDetails ? $booking->parseAttendeeforDisplay() : [],
                ]
            ];
        });

        return response()->json($events);
    }
}
