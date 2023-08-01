<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReserveController;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/check-otp', [AuthController::class, 'checkOtp']);
Route::post('/auth/resend-otp', [AuthController::class, 'resendOtp']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
});


Route::apiResource('doctors',DoctorController::class);
Route::get('doctor/verifySite',[DoctorController::class,'verifySite']);
Route::apiResource('offices',OfficeController::class);
Route::apiResource('reserves',ReserveController::class);
Route::post('reservetask',[ReserveController::class,'task']);
Route::get('refreshReserve',[ReserveController::class,'refreshReserves']);
Route::get('trackReserve',[ReserveController::class,'trackReserve']);
Route::get('resetReserves',[ReserveController::class,'resetReserves']);
Route::get('smstracking',[ReserveController::class,'smsTracking']);

Route::get('payment/send',[PaymentController::class,'send']);