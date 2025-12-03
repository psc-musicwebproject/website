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

// Group: authenticated user dashboard routes
Route::middleware('auth')->group(function () {
    Route::view('/dash', 'dash.main', ['title' => 'Dashboard'])->name('dash');

    Route::get('/dash/booking', [BookingController::class, 'index'])->name('dash.booking');

    Route::view('/dash/booking/history', 'dash.bookhistory')->name('dash.booking.history');

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
    Route::get('/admin/appsetting', function () {
        return view('admin.appsetting', [
            'title' => 'ตั้งค่าระบบ',
            'AppSetting' => App\Models\AppSetting::first()
        ]);
    })->name('admin.appsetting');
    Route::post('/admin/manage/appsetting/update', AppSettingController::class)->name('admin.appsetting.update');
});
