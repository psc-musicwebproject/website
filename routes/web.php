<?php

// PSC-MusicWeb's Route
// 2025 - Toonshouin! , ArmGameXD

use App\Http\Controllers\BookingController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AppSettingController;
use App\Http\Controllers\UserController;
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
    $guard = request()->query('guard', 'web');
    Auth::guard($guard)->logout();

    Session::invalidate();
    Session::regenerateToken();

    return redirect('/auth/login');
})->name('auth.web.logout');

// Line Authentication Routes (public - for initial login)
Route::get('/auth/line/callback', [App\Http\Controllers\LineIntegrationController::class, 'GetCallbackFromLine'])->name('auth.line.callback');

// LINE Binding Routes (for both web and admin users)
Route::middleware(['auth:web,admin'])->group(function () {
    Route::get('/auth/line/bindingPage', function () {
        // Prefer explicit guard query parameter when provided (preserved during redirects)
        $requestedGuard = request()->query('guard');

        if ($requestedGuard === 'admin') {
            $defaultRedirect = route('admin.dash');
        } else {
            // Fallback to checking the current authenticated guard
            $defaultRedirect = Auth::guard('admin')->check() ? route('admin.dash') : route('dash');
        }

        $skipUrl = $defaultRedirect;
        $title = 'ผูกบัญชี LINE';
        return view('auth.bindline', compact('title', 'skipUrl'));
    })->name('auth.line.bind');

    Route::post('auth/bind/line', [App\Http\Controllers\LineIntegrationController::class, 'BindLineAccount'])
        ->name('auth.bind.line');

    // Forced Password Reset Routes
    Route::get('/auth/new-password', [App\Http\Controllers\NewPasswordController::class, 'create'])
        ->name('auth.web.newpass.form');

    Route::post('/auth/new-password', [App\Http\Controllers\NewPasswordController::class, 'store'])
        ->name('auth.web.newpass');
});

// Group: authenticated user dashboard routes
Route::middleware('auth')->group(function () {
    Route::view('/dash', 'dash.main', ['title' => 'Dashboard'])->name('dash');

    Route::get('/dash/booking', function () {
        $rooms = App\Models\Room::getAvailableRooms();

        return view('dash.booking.main', [
            'title' => 'จองห้อง',
            'rooms' => $rooms
        ]);
    })->name('dash.booking');
    Route::post('/dash/booking/submit', [BookingController::class, 'saveBooking'])->defaults('redirectRoute', 'dash.booking')->name('dash.booking.submit');
    Route::post('/dash/booking/find-user', [BookingController::class, 'findUser'])->name('dash.booking.find_user');
    Route::get('/dash/booking/history', function () {
        // Use auth() helper to get the current guard's user
        $Bookings = App\Models\Booking::getCurrentUserBookings(auth()->id());
        return view('dash.booking.summary', [
            'title' => 'ประวัติการจอง',
            'bookings' => $Bookings
        ]);
    })->name('dash.booking.history');
    Route::get('/dash/booking/history/{id}', function ($id) {
        $bookingDetails = App\Models\Booking::getBookingByID($id);
        return view('dash.booking.details', [
            'title' => 'รายละเอียดการจอง',
            'booking' => $bookingDetails
        ]);
    })->name('dash.booking.history.detail');

    Route::get('/dash/club/register', function () {
        // Use auth() helper to get the current guard's user
        $user = auth()->user();
        $clubMembership = $user->clubMembership;

        return view('dash.club.register', [
            'title' => 'สมัครสมาชิก',
            'clubMembership' => $clubMembership
        ]);
    })->name('dash.club.register');

    Route::post('/dash/club/register', App\Http\Controllers\ClubRegisterController::class)
        ->name('dash.club.register.submit');

    Route::get('/dash/calendar/events', [\App\Http\Controllers\Api\CalendarController::class, 'events'])->name('dash.calendar.events');
});

// Shared route for photo access (User + Admin)
Route::get('/dash/club/photo/{member_id}', [\App\Http\Controllers\ClubPhotoController::class, 'show'])
    ->middleware('auth:web,admin')
    ->name('dash.club.photo');

// Admin-only routes
Route::middleware('auth:admin')->group(function () {
    Route::view('/admin', 'admin.main', ['title' => 'Dashboard'])->name('admin.dash');

    Route::get('/admin/manage/app', function () {
        return view('admin.appsetting', [
            'title' => 'ตั้งค่าระบบ',
        ]);
    })->name('admin.appsetting');
    Route::post('/admin/manage/app/update', AppSettingController::class)->name('admin.appsetting.update');

    Route::get('/admin/manage/user', function () {
        $users = \App\Models\User::where('type', '!=', 'guest')->get();
        $userTypes = \App\Models\UserTypeMapping::where('db_type', '!=', 'guest')->get();
        return view('admin.usersetting', [
            'title' => 'ตั้งค่าผู้ใช้',
            'users' => $users,
            'userTypes' => $userTypes
        ]);
    })->name('admin.usersetting');

    Route::post('/admin/manage/user/store', [\App\Http\Controllers\UserManagerController::class, 'store'])->name('admin.user.store');
    Route::post('/admin/manage/user/import', [\App\Http\Controllers\UserManagerController::class, 'import'])->name('admin.user.import');
    Route::get('/admin/manage/user/template', [\App\Http\Controllers\UserManagerController::class, 'downloadTemplate'])->name('admin.user.template');
    Route::get('/admin/manage/user/download-credits/{id}', [\App\Http\Controllers\UserManagerController::class, 'downloadGeneratedCredentials'])->name('admin.user.download_credits');
    Route::post('/admin/manage/user/update/{id}', [UserController::class, 'update'])->name('admin.user.update');
    Route::post('/admin/manage/user/delete/{id}', [UserController::class, 'destroy'])->name('admin.user.delete');

    Route::get('/admin/club/approve', function () {
        return view('admin.club.approve.main', [
            'title' => 'อนุมัติสมาชิกชมรม',
            'clubApprovals' => App\Models\ClubMember::getPendingApprovals()
        ]);
    })->name('admin.club.approve');
    Route::get('/admin/club/approve/{id}', function ($id) {
        return view('admin.club.approve.detail', [
            'title' => 'อนุมัติสมาชิกชมรม',
            'clubMember' => App\Models\ClubMember::getApplicationByID($id)
        ]);
    })->name('admin.club.approve.detail');

    Route::post('/admin/club/approve/{id}', [App\Http\Controllers\ClubApprovalController::class, 'update'])->name('admin.club.approve.update');

    Route::get('/admin/manage/room', function () {
        return view('admin.roomsetting', [
            'title' => 'ตั้งค่าห้อง',
        ]);
    })->name('admin.roomsetting');
    Route::post('/admin/manage/room/add', [App\Http\Controllers\RoomController::class, 'addRoom'])->name('admin.room.add');
    Route::post('/admin/manage/room/edit/{room_id}', [App\Http\Controllers\RoomController::class, 'editRoom'])->name('admin.room.edit');
    Route::post('/admin/manage/room/delete/{room_id}', [App\Http\Controllers\RoomController::class, 'deleteRoom'])->name('admin.room.delete');
    Route::post('/admin/manage/room/disable/{room_id}', [App\Http\Controllers\RoomController::class, 'disableRoom'])->name('admin.room.disable');
    Route::post('/admin/manage/room/enable/{room_id}', [App\Http\Controllers\RoomController::class, 'enableRoom'])->name('admin.room.enable');

    Route::get('/admin/booking', function () {
        return view('admin.booking.main', [
            'title' => 'การจองห้องทั้งหมด',
            'bookings' => App\Models\Booking::getAllBookings(),
            'rooms' => App\Models\Room::getAllRooms()
        ]);
    })->name('admin.booking');
    Route::post('/admin/booking/submit', [BookingController::class, 'saveBooking'])->defaults('isAdmin', true)->defaults('redirectRoute', 'admin.booking')->name('admin.booking.submit');
    Route::get('/admin/booking/{id}', function ($id) {
        return view('admin.booking.detail', [
            'title' => 'รายละเอียดการจอง',
            'booking' => App\Models\Booking::getBookingByID($id)
        ]);
    })->name('admin.booking.detail');
    Route::post('/admin/booking/approve/{id}', [App\Http\Controllers\BookingController::class, 'approveBooking'])->name('admin.booking.approve');
    Route::post('/admin/booking/delete/{id}', [App\Http\Controllers\BookingController::class, 'deleteBooking'])->name('admin.booking.delete');
    Route::post('/admin/booking/find-user', [BookingController::class, 'findUser'])->name('admin.booking.find_user');

    Route::get('/admin/calendar/events', [\App\Http\Controllers\Api\CalendarController::class, 'events'])->name('admin.calendar.events');
});
