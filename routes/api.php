<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\NextPayController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Panel\AuthController as PanelAuthController;
use App\Http\Controllers\Panel\OfficePanelController;
use App\Http\Controllers\Panel\ReservePanelController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReserveController;
use App\Http\Controllers\TransactionController;
use App\Models\Doctor;
use App\Models\Transaction;
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
    Route::apiResource('reserves', ReserveController::class);
    Route::post('payment/send', [PaymentController::class, 'send']);
});

Route::get('/transaction', [TransactionController::class, 'show']);

Route::apiResource('doctors', DoctorController::class);
Route::get('doctor/verifySite', [DoctorController::class, 'verifySite']);
Route::apiResource('offices', OfficeController::class);
Route::post('reservetask', [ReserveController::class, 'task']);
Route::get('refreshReserve', [ReserveController::class, 'refreshReserves']);
Route::get('trackReserve', [ReserveController::class, 'trackReserve']);
Route::get('resetReserves', [ReserveController::class, 'resetReserves']);
Route::get('smstracking', [ReserveController::class, 'smsTracking']);

Route::post('/payment/verify', [PaymentController::class, 'verify']);
Route::post('/reserve/delete', [ReserveController::class, 'destroy']);


Route::post('/reserve/smstest', [ReserveController::class, 'smstest']);


/// OFFICE ROUTES


Route::post('/panel/auth/login', [PanelAuthController::class, 'login']);
Route::post('/panel/auth/register', [PanelAuthController::class, 'register']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/panel/getDailyReserves/{office}', [OfficePanelController::class, 'getDailyReserves']);
    Route::get('/panel/getmonthlyReserves/{month}', [ReservePanelController::class, 'monthlyReserves']);
    Route::get('/panel/getOffices', [OfficePanelController::class, 'getOffices']);
    Route::get('/panel/reserve/delete/{reserve}', [OfficePanelController::class, 'deleteReserve']);
    Route::get('/panel/reserve/accept/{reserve}', [OfficePanelController::class, 'acceptReserve']);
    Route::post('/panel/reserve/store', [ReservePanelController::class, 'store']);
    Route::get('/panel/reserve/backToWait/{reserve}', [OfficePanelController::class, 'backToWaitReserve']);
    Route::get('/panel/reserves/{office}/{time}', [OfficePanelController::class, 'getReserves']);
    Route::get('/panel/auth/me', [PanelAuthController::class, 'me']);
    Route::post('/panel/auth/checkCellphone', [PanelAuthController::class, 'checkCellphone']);
});
Route::get('/panel/jalalidate', [PanelAuthController::class, 'parsianDate']);
Route::get('/panel/exportreport', [ReservePanelController::class, 'monthlyReserves']);
