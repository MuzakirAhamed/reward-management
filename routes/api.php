<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VoucherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::apiResource('vouchers', App\Http\Controllers\Api\VoucherController::class);
    Route::get('users', [App\Http\Controllers\Api\UserController::class, 'index']);
    Route::post('issue-voucher', [App\Http\Controllers\Api\UserController::class, 'issueVoucher']);
    Route::get('voucher-user/{voucherId}', [App\Http\Controllers\Api\UserController::class, 'getVoucherUsers']);
    Route::get('active-vouchers', [App\Http\Controllers\Api\VoucherController::class, 'getActiveVouchers']);
    Route::get('/dashboard', [VoucherController::class, 'dashboard']);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
Route::get('/me', [AuthController::class, 'me']);
