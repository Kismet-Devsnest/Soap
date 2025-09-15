<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SoapProxyController;

Route::get('/', function () {
    return view('welcome');
});

// SOAP API routes (loaded here to ensure availability)
Route::middleware('api')->prefix('api/soap')->group(function () {
    Route::get('wsdl', [SoapProxyController::class, 'wsdl']);
    Route::post('call', [SoapProxyController::class, 'call']);
    Route::post('{action}', [SoapProxyController::class, 'callOperation']);
});

// Include generated SPG SOAP routes (inherits API middleware or define own)
// Removed: SPG routes are loaded from routes/api.php
