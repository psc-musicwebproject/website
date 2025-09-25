<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/login', function (){
    return view('auth.login');
});

Route::post('/auth/web/login', LoginController::class,)->name('auth.web.login');

Route::get('/dash', function (){
    return view('dash.main');
})->name('dashboard');

Route::post('/auth/web/logout', function (){
    Auth::guard('web')->logout();

    Session::invalidate();
    Session::regenerateToken();

    return redirect('/auth/login');
})->name('auth.web.logout');