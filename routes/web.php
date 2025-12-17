<?php

// PSC-MusicWeb's Route
// 2025 - Toonshouin! , ArmGameXD

use App\Http\Controllers\BookingController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AppSettingController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

// Default Route
Route::get('/', function () {
    return redirect('/dash');
});

// Auth - with optional guard parameter
Route::get('/auth/login', function () {
    return view('auth.login');
})->name('login');

// Login is an invokable controller (LoginController::__invoke)
Route::post('/auth/web/login', LoginController::class)
    ->middleware('throttle:5,1')
    ->name('auth.web.login');

// Logout (POST)
Route::post('/auth/web/logout', function () {
    Auth::guard('web')->logout();

    Session::invalidate();
    Session::regenerateToken();

    return redirect('/auth/login');
})->name('auth.web.logout');

// Line Authentication Routes (public - for initial login)
Route::get('/auth/line/redirect', [App\Http\Controllers\LineIntegrationController::class, 'AuthenticateViaLine'])->name('auth.line.redirect');
Route::get('/auth/line/callback', [App\Http\Controllers\LineIntegrationController::class, 'GetCallbackFromLine'])->name('auth.line.callback');

// Group: authenticated user dashboard routes
Route::middleware('auth')->group(function () {
    Route::view('/dash', 'dash.main', ['title' => 'Dashboard'])->name('dash');

    // LINE Binding Routes (for web users)
    Route::get('/auth/line/bindingPage', function () {
        return view('auth.bindline', ['title' => 'ผูกบัญชี LINE']);
    })->name('auth.line.bind');
    Route::post('auth/bind/line', [App\Http\Controllers\LineIntegrationController::class, 'BindLineAccount'])->name('auth.bind.line');

    Route::get('/dash/booking', function() {
        $rooms = App\Models\Room::getAvailableRooms();
        
        return view('dash.booking.main', [
            'title' => 'จองห้อง',
            'rooms' => $rooms
        ]);
    })->name('dash.booking');
    Route::post('/dash/booking/submit', [BookingController::class, 'saveBooking'])->defaults('redirectRoute', 'dash.booking')->name('dash.booking.submit');
    Route::get('/dash/booking/history', function() {
        $Bookings = App\Models\Booking::getCurrentUserBookings(Auth::id());
        return view('dash.booking.summary', [
            'title' => 'ประวัติการจอง',
            'bookings' => $Bookings
        ]);
    })->name('dash.booking.history');
    Route::get('/dash/booking/history/{id}', function($id) {
        $bookingDetails = App\Models\Booking::getBookingByID($id);
        return view('dash.booking.details', [
            'title' => 'รายละเอียดการจอง',
            'booking' => $bookingDetails
        ]);
    })->name('dash.booking.history.detail');

    Route::get('/dash/club/register', function () {
        $user = Auth::user();
        $clubMembership = $user->clubMembership;

        return view('dash.clubregis', [
            'title' => 'สมัครสมาชิก',
            'clubMembership' => $clubMembership
        ]);
    })->name('dash.club.register');

    Route::post('/dash/club/register', App\Http\Controllers\ClubRegisterController::class)
        ->name('dash.club.register.submit');
});

// Admin-only routes
Route::middleware('auth:admin')->group(function () {
    Route::view('/admin', 'admin.main', ['title' => 'Dashboard'])->name('admin.dash');
    
    // LINE Binding Routes (for admin users) - override the web routes
    Route::get('/auth/line/bindingPage', function () {
        return view('auth.bindline', ['title' => 'ผูกบัญชี LINE']);
    });
    Route::post('auth/bind/line', [App\Http\Controllers\LineIntegrationController::class, 'BindLineAccount']);
    
    Route::get('/admin/manage/app', function () {
        return view('admin.appsetting', [
            'title' => 'ตั้งค่าระบบ',
        ]);
    })->name('admin.appsetting');
    Route::post('/admin/manage/app/update', AppSettingController::class)->name('admin.appsetting.update');
    Route::get('/admin/club/approve', function() {
        return view('admin.club.approve.main', [
            'title' => 'อนุมัติสมาชิกชมรม',
            'clubApprovals' => App\Models\ClubMember::getPendingApprovals()
        ]);
    })->name('admin.club.approve');
    Route::get('/admin/club/approve/{id}', function($id) {
        return view('admin.club.approve.detail', [
            'title' => 'อนุมัติสมาชิกชมรม',
            'clubMember' => App\Models\ClubMember::getApplicationByID($id)
        ]);
    })->name('admin.club.approve.detail');

    Route::post('/admin/club/approve/{id}', [App\Http\Controllers\ClubApprovalController::class, 'update'])->name('admin.club.approve.update');

    Route::get('/admin/manage/room', function() {
        return view('admin.roomsetting', [
            'title' => 'ตั้งค่าห้อง',
        ]);
    })->name('admin.roomsetting');
    Route::post('/admin/manage/room/add', [App\Http\Controllers\RoomController::class, 'addRoom'])->name('admin.room.add');
    Route::post('/admin/manage/room/edit/{room_id}', [App\Http\Controllers\RoomController::class, 'editRoom'])->name('admin.room.edit');
    Route::post('/admin/manage/room/delete/{room_id}', [App\Http\Controllers\RoomController::class, 'deleteRoom'])->name('admin.room.delete');
    Route::post('/admin/manage/room/disable/{room_id}', [App\Http\Controllers\RoomController::class, 'disableRoom'])->name('admin.room.disable');
    Route::post('/admin/manage/room/enable/{room_id}', [App\Http\Controllers\RoomController::class, 'enableRoom'])->name('admin.room.enable');

    Route::get('/admin/booking' , function() {
        return view('admin.booking.main', [
            'title' => 'การจองห้องทั้งหมด',
            'bookings' => App\Models\Booking::getAllBookings(),
            'rooms' => App\Models\Room::getAllRooms()
        ]);
    })->name('admin.booking');
    Route::post('/admin/booking/submit', [BookingController::class, 'saveBooking'])->defaults('isAdmin', true)->defaults('redirectRoute', 'admin.booking')->name('admin.booking.submit');
    Route::get('/admin/booking/{id}', function($id) {
        return view('admin.booking.detail', [
            'title' => 'รายละเอียดการจอง',
            'booking' => App\Models\Booking::getBookingByID($id)
        ]);
    })->name('admin.booking.detail');
    Route::post('/admin/booking/approve/{id}', [App\Http\Controllers\BookingController::class, 'approveBooking'])->name('admin.booking.approve');
    Route::post('/admin/booking/delete/{id}', [App\Http\Controllers\BookingController::class, 'deleteBooking'])->name('admin.booking.delete');
});