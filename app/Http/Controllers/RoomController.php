<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;

class RoomController extends Controller
{
    public function addRoom (Request $request) {
        Room::add($request->input('name'));
        return redirect()->route('admin.roomsetting')->with('success', 'เพิ่มห้องเรียบร้อยแล้ว');
    }

    public function editRoom (Request $request, $room_id) {
        if ($request->has('name')) {
            Room::edit($room_id, 'name', $request->input('name'));
        }
        return redirect()->route('admin.roomsetting')->with('success', 'แก้ไขห้องเรียบร้อยแล้ว');
    }

    public function deleteRoom ($room_id) {
        Room::del($room_id);
        return redirect()->route('admin.roomsetting')->with('success', 'ลบห้องเรียบร้อยแล้ว');
    }

    public function disableRoom ($room_id) {
        Room::disable($room_id);
        return redirect()->route('admin.roomsetting')->with('success', 'ปิดใช้งานห้องเรียบร้อยแล้ว');
    }

    public function enableRoom ($room_id) {
        Room::enable($room_id);
        return redirect()->route('admin.roomsetting')->with('success', 'เปิดใช้งานห้องเรียบร้อยแล้ว');
    }
}
