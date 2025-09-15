<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SoapProxyController;

Route::prefix('spg')->group(function () {
    Route::post('GetAccountVerification', [SoapProxyController::class, 'callOperation'])->defaults('action', 'GetAccountVerification');
    Route::post('GetUpdateAfterCashReceived', [SoapProxyController::class, 'callOperation'])->defaults('action', 'GetUpdateAfterCashReceived');
    Route::post('DailyStTransaction', [SoapProxyController::class, 'callOperation'])->defaults('action', 'DailyStTransaction');
    Route::post('TransactionDetails', [SoapProxyController::class, 'callOperation'])->defaults('action', 'TransactionDetails');
    Route::post('TransactionVerification', [SoapProxyController::class, 'callOperation'])->defaults('action', 'TransactionVerification');
    Route::post('TransactionVerificationWithRefNo', [SoapProxyController::class, 'callOperation'])->defaults('action', 'TransactionVerificationWithRefNo');
    Route::post('DailySpTransaction', [SoapProxyController::class, 'callOperation'])->defaults('action', 'DailySpTransaction');
    Route::post('RequestInfo', [SoapProxyController::class, 'callOperation'])->defaults('action', 'RequestInfo');
    Route::post('GetSessionKey', [SoapProxyController::class, 'callOperation'])->defaults('action', 'GetSessionKey');
});
