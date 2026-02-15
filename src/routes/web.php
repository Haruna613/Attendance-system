<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/admin/login', function () {
    return view('auth.admin-login');
})->name('admin.login');

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/attendance/list', [AttendanceController::class, 'adminDailyIndex'])
        ->name('admin.attendance.all');

    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'correctionList'])->name('attendance.correction.list');

    Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AttendanceController::class, 'approveForm'])->name('attendance.correction.approve.form');

    Route::post('/stamp_correction_request/approve/{id}', [AttendanceController::class, 'approve'])->name('admin.attendance.approve');

    Route::get('/admin/staff/list',[AttendanceController::class,'staffList'])->name('staff.list');

    Route::get('/admin/attendance/staff/{id}',[AttendanceController::class,'attendanceList'])->name('admin.attendance.list');

    Route::get('/admin/attendance/{id}', [AttendanceController::class, 'showAttendanceDetail'])->name('admin.attendance.detail');

    Route::get('/admin/attendance/staff/{id}/csv', [AttendanceController::class, 'exportCsv'])->name('admin.attendance.csv');
});

Route::middleware('auth','verified')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.register');

    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');

    Route::post('/attendance/rest-start', [AttendanceController::class, 'restStart'])->name('attendance.rest.start');

    Route::post('/attendance/rest-end', [AttendanceController::class, 'restEnd'])->name('attendance.rest.end');

    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');

    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');

    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'edit'])->name('attendance.detail');

    Route::patch('/attendance/update/{id}', [AttendanceController::class, 'update'])->name('attendance.update');

    Route::post('/attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');
});