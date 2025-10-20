<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;

class BookingController extends Controller
{
    public function index()
    {
        $rooms = Room::where('room_status', 'active')->get();
        
        return view('dash.booking', [
            'title' => 'จองห้อง',
            'rooms' => $rooms
        ]);
    }
}
