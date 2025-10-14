<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\LoginController;

Route::get('/', function () {
    return redirect('/dash');
});

Route::get('/', function () {
    return redirect('/dash');
});

Route::get('/auth/login', function (){
    return view('auth.login');
})->name('login');

Route::post('/auth/web/login', LoginController::class,)->middleware('throttle:5,1')->name('auth.web.login');

Route::get('/dash', function (){
    return view('dash.main', ['title' => 'Dashboard']);
})->middleware('auth')->name('dashboard');

Route::get('/dash/booking', function (){
    return view('dash.booking', ['title' => 'จองห้อง']);
})->middleware('auth')->name('dash.booking');

Route::get('/dash/booking/history', function (){
    return view('dash.bookhistory');
})->middleware('auth')->name('dash.booking.history');

Route::get('/dash/club/register', function (){
    $user = Auth::user();
    $clubMembership = $user->clubMembership;
    
    return view('dash.clubregis', [
        'title' => 'สมัครสมาชิก',
        'clubMembership' => $clubMembership
    ]);
})->middleware('auth')->name('dash.club.register');

Route::post('/dash/club/register', App\Http\Controllers\ClubRegisterController::class)
    ->middleware('auth')
    ->name('dash.club.register.submit');

Route::post('/auth/web/logout', function (){
    Auth::guard('web')->logout();

    Session::invalidate();
    Session::regenerateToken();

    return redirect('/auth/login');
})->name('auth.web.logout');