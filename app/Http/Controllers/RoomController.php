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

    public function editRoom (Request $request, $room_uuid) {
        if ($request->has('name')) {
            Room::edit($room_uuid, 'name', $request->input('name'));
        }
        return redirect()->route('admin.roomsetting')->with('success', 'แก้ไขห้องเรียบร้อยแล้ว');
    }

    public function deleteRoom ($room_uuid) {
        Room::del($room_uuid);
        return redirect()->route('admin.roomsetting')->with('success', 'ลบห้องเรียบร้อยแล้ว');
    }

    public function disableRoom ($room_uuid) {
        Room::disable($room_uuid);
        return redirect()->route('admin.roomsetting')->with('success', 'ปิดใช้งานห้องเรียบร้อยแล้ว');
    }

    public function enableRoom ($room_uuid) {
        Room::enable($room_uuid);
        return redirect()->route('admin.roomsetting')->with('success', 'เปิดใช้งานห้องเรียบร้อยแล้ว');
    }
}
