<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
// Route::get('/payment/verify',function(Request $request){
//  dd($request->all());
// });
Route::get('/payment/verify', function (Request $request) {
       $response=Http::post('http://localhost:8000/api/payment/verify', [
        'token' => $request->query('trackId'),
        'status' => $request->query('status'),
        'success' => $request->query('success')
    ]);
     return redirect()->away(env('APP_FRONT_URL').'/payment/bankback?token='.$request->query('trackId'));
});
