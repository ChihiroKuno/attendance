<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;


Route::get('/', function () {
    return view('welcome');
});

// 会員登録
Route::get('/register', [RegisterController::class, 'show'])->name('register.show');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

// ログイン
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login.show');
Route::post('/login', [LoginController::class, 'login'])->name('login.perform');
Route::view('/login', 'auth.login')->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// 勤怠（認証必須）
Route::middleware(['auth'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/start', [AttendanceController::class, 'workStart'])->name('attendance.start');
    Route::post('/attendance/break-in', [AttendanceController::class, 'breakStart'])->name('attendance.breakIn');
    Route::post('/attendance/break-out', [AttendanceController::class, 'breakEnd'])->name('attendance.breakOut');
    Route::post('/attendance/end', [AttendanceController::class, 'workEnd'])->name('attendance.end');

    // 🔹 勤怠詳細
    Route::get('/attendance/detail/{date}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    Route::put('/attendance/detail/{date}', [AttendanceController::class, 'update'])->name('attendance.update');

    // 🔹 勤怠一覧
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');

    // 一般ユーザー用：申請一覧
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'list'])
        ->name('request.list');
});