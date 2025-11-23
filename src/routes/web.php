<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminRequestController;
use App\Http\Controllers\AdminStampCorrectionRequestController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// ==========================================
// トップページ
// ==========================================
Route::get('/', function () {
    return view('welcome');
});

// ==========================================
// メール認証ルート（Laravel標準）
// ==========================================

// 認証メール送信完了画面
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// メール内リンクをクリック → 認証完了処理
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/attendance');
})->middleware(['auth', 'signed'])->name('verification.verify');

// 認証メール再送信
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '認証メールを再送しました。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// ==========================================
// 一般ユーザー向けルート
// ==========================================

// 会員登録
Route::get('/register', [RegisterController::class, 'show'])->name('register.show');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

// ログイン
// ログイン画面
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');

// ログイン実行
Route::post('/login', [LoginController::class, 'login'])->name('login.perform');

// ログアウト
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ==========================================
// 勤怠（認証 + メール認証済み 必須）
// ==========================================
Route::middleware(['auth', 'verified'])->group(function () {
    // 勤怠打刻
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/start', [AttendanceController::class, 'workStart'])->name('attendance.start');
    Route::post('/attendance/break-in', [AttendanceController::class, 'breakStart'])->name('attendance.breakIn');
    Route::post('/attendance/break-out', [AttendanceController::class, 'breakEnd'])->name('attendance.breakOut');
    Route::post('/attendance/end', [AttendanceController::class, 'workEnd'])->name('attendance.end');

    // 勤怠詳細
    Route::get('/attendance/detail/{date}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    Route::put('/attendance/detail/{date}', [AttendanceController::class, 'update'])->name('attendance.update');

    // 勤怠一覧
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');

    // 一般ユーザー用：申請一覧
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'list'])
        ->name('request.list');
});

// ==========================================
// 管理者向けルート
// ==========================================
Route::middleware('web')->prefix('admin')->group(function () {

    // 管理者ログイン
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])
        ->name('admin.login.show');

    Route::post('/login', [AdminLoginController::class, 'login'])
        ->name('admin.login.perform');

    // 管理者ログアウト
    Route::post('/logout', [AdminLoginController::class, 'logout'])
        ->name('admin.logout');

    // 🔒 管理者認証が必要
    Route::middleware('auth:admin')->group(function () {

        // 勤怠一覧
        Route::get('/attendance/list', [AdminAttendanceController::class, 'list'])
            ->name('admin.attendance.list');

        // スタッフ別勤怠一覧
        Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'staffList'])
            ->name('admin.attendance.staff');

        // 勤怠詳細
        Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])
            ->name('admin.attendance.detail');

        Route::put('/attendance/{id}', [AdminAttendanceController::class, 'update'])
            ->name('admin.attendance.update');

        // CSV出力
        Route::get('/attendance/export/{id}', [AdminAttendanceController::class, 'export'])
            ->name('admin.attendance.export');

        // スタッフ一覧
        Route::get('/staff/list', [AdminStaffController::class, 'index'])
            ->name('admin.staff.list');

        // 申請一覧
        Route::get('/stamp_correction_request/list', [AdminRequestController::class, 'list'])
            ->name('admin.request.list');

        // 管理者専用：申請詳細・承認
        Route::get('/stamp_correction_request/{id}', [AdminStampCorrectionRequestController::class, 'show'])
            ->name('stamp_correction_request.show');

        Route::post('/stamp_correction_request/approve/{id}', [AdminStampCorrectionRequestController::class, 'approve'])
            ->name('stamp_correction_request.approve');
    });
});