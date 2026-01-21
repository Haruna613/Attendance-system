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

Route::middleware('auth','verified')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.register');

    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');

    Route::post('/attendance/rest-start', [AttendanceController::class, 'restStart'])->name('attendance.rest.start');

    Route::post('/attendance/rest-end', [AttendanceController::class, 'restEnd'])->name('attendance.rest.end');

    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');

    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');

    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'edit'])->name('attendance.detail');

    Route::patch('/attendance/update/{id}', [AttendanceController::class, 'update'])->name('attendance.update');

    Route::get('/attendance/detail/{idOrDate}', [AttendanceController::class, 'edit'])->name('attendance.detail');

    Route::post('/attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');

    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'correctionList'])->name('attendance.correction.list');
});