<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;

class BookingController extends Controller
{
    public function saveBooking(Request $request)
    {
        $isAdmin = $request->route('isAdmin', false);
        $redirectRoute = $request->route('redirectRoute', 'dash.booking');
        
        try {
            $request->validate([
                'room_id' => ['required','string'],
                'name' => ['required','string','max:255'],
                'date' => ['required','date'],
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
            $booking->attendees = empty($attendees) ? null : $attendees;
            $booking->save();

            return redirect()->route($redirectRoute)->with('success', 'บันทึกการจองเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->route($redirectRoute)->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }


    public function approveBooking(Request $request, $bookingId)
    {
        try {
            Booking::approveBooking($request, $bookingId);
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
}
