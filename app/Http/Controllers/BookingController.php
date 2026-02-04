<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Notifications\User\Booking\NewBooking;
use App\Notifications\Admin\Booking\NewBookingNotice;
use App\Notifications\User\Booking\UserInvitedNotify;
use Illuminate\Support\Facades\Notification;

class BookingController extends Controller
{
    public function saveBooking(Request $request)
    {
        $isAdmin = $request->route('isAdmin', false);
        $redirectRoute = $request->route('redirectRoute', 'dash.booking');

        try {
            $request->validate([
                'room_id' => ['required', 'string'],
                'name' => ['required', 'string', 'max:255'],
                'date' => ['required', 'date'],
                'time_from' => ['required'],
                'time_to' => ['required'],
            ]);

            $booking = new Booking();
            $booking->booking_name = $request->input('name');
            $booking->room_id = $request->input('room_id');
            $booking->booking_time = now();
            $booking->user_id = Auth::id();

            if ($isAdmin) {
                if (Auth::user()->type != 'admin') {
                    throw new \Exception('สิทธิ์ไม่เพียงพอในการดำเนินการนี้');
                } else {
                    $booking->approval_status = 'approved';
                    $booking->approval_person_id = Auth::id();
                    $booking->approval_time = now();
                    $booking->approval_comment = 'จองโดยผู้ดูแลระบบ, ไม่จำเป็นต้องรออนุมัติ';
                    $booking->booking_status = 'approved';
                }
            }

            /** Booking from / to consist of date, time_from, time_to
             * three part to be combined into datetime format */
            $booked_date = $request->input('date');
            $time_from = $request->input('time_from');
            $time_to = $request->input('time_to');
            $booking->booked_from = date('Y-m-d H:i:s', strtotime("$booked_date $time_from"));
            $booking->booked_to = date('Y-m-d H:i:s', strtotime("$booked_date $time_to"));
            $attendees = $request->input('attendees');
            if (is_string($attendees)) {
                $decoded = json_decode($attendees, true);
                // Check if decode was successful
                if (json_last_error() === JSON_ERROR_NONE) {
                    $attendees = $decoded;
                }
            }
            $booking->attendees = empty($attendees) ? null : $attendees;

            // Handle "Book on Behalf" for Admins
            if (Auth::user()->type === 'admin' && $request->has('book_owner_id')) {
                 $ownerId = $request->input('book_owner_id');
                 if (\App\Models\User::where('id', $ownerId)->exists()) {
                     $booking->user_id = $ownerId;
                 } else {
                     $booking->user_id = Auth::id(); // Fallback
                 }
            } else {
                $booking->user_id = Auth::id();
            }

            $booking->save();

            if ($booking->user) {
                $booking->user->notify(new NewBooking($booking, Auth::user()));
                if ($isAdmin && $booking->attendees) {
                    $InternalList = Booking::fetchInternalAttendeeList($booking);
                    foreach ($InternalList as $user) {
                        $user->notify(new UserInvitedNotify($booking));
                    }
                    $GuestList = Booking::fetchGuestAttendeeList($booking);
                    foreach ($GuestList as $guest) {
                        // Send invitation email to guest with their name
                        Notification::route('mail', $guest['email'])
                            ->notify(new UserInvitedNotify($booking, $guest['name']));
                    }
                }
            }

            if ($booking->user && ($booking->user->type != "admin" || Auth::user()->type != 'admin') ) {
                $admin = \App\Models\User::where('type', 'admin')->get();
                if ($admin->isNotEmpty()) {
                    Notification::send($admin, new NewBookingNotice($booking));
                }
            }

            return redirect()->route($redirectRoute)->with('success', 'บันทึกการจองเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->route($redirectRoute)->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function approveBooking(Request $request, $bookingId)
    {
        try {
            $booking = Booking::where('booking_id', $bookingId)->first();
            $wasWaiting = $booking && $booking->booking_status === 'waiting';

            Booking::approveBooking($request, $bookingId);
            $booking = Booking::where('booking_id', $bookingId)->first();

            if ($booking->booking_status === 'denied') {
                $booking->user->notify(new \App\Notifications\User\Booking\DeniedUserNotify($booking));
            } elseif ($booking->booking_status === 'approved') {
                $booking->user->notify(new \App\Notifications\User\Booking\ApprovedUserNotify($booking));

                if ($wasWaiting && $booking && $booking->attendees) {
                    $InternalList = Booking::fetchInternalAttendeeList($booking);
                    foreach ($InternalList as $user) {
                        $user->notify(new UserInvitedNotify($booking));
                    }
                    $GuestList = Booking::fetchGuestAttendeeList($booking);
                    foreach ($GuestList as $guest) {
                        // Send invitation email to guest with their name
                        Notification::route('mail', $guest['email'])
                            ->notify(new UserInvitedNotify($booking, $guest['name']));
                    }
                }
            }
            return redirect()->route('admin.booking')->with('success', 'อัปเดตสถานะการจองเรียบร้อยแล้ว');
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('admin.booking')->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function deleteBooking($bookingId)
    {
        try {
            $deleted = Booking::delBooking($bookingId);
            if ($deleted) {
                return redirect()->route('admin.booking')->with('success', 'ลบการจองเรียบร้อยแล้ว');
            } else {
                return redirect()->route('admin.booking')->with('error', 'ไม่พบการจองที่ต้องการลบ');
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.booking')->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function findUser(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json(['error' => 'Query is required'], 400);
        }

        $searchQuery = $query;
        $user = \App\Models\User::where('is_active', true)
            ->where(function ($q) use ($searchQuery) {
                $q->where('student_id', $searchQuery)
                  ->orWhere('email', $searchQuery);
            })
            ->first();

        if ($user) {
            return response()->json([
                'found' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'surname' => $user->surname,
                    'student_id' => $user->student_id,
                    'email' => $user->email,
                    'type' => $user->type,
                    'role_label' => $user->role_label
                ]
            ]);
        }

        return response()->json(['found' => false], 404);
    }
}
