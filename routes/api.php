<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SoapProxyController;

Route::prefix('soap')->group(function () {
    Route::get('wsdl', [SoapProxyController::class, 'wsdl']);
    Route::post('call', [SoapProxyController::class, 'call']);
    Route::post('{action}', [SoapProxyController::class, 'callAction']);
});

// Include generated SPG SOAP routes, if present (inherits API middleware)
if (file_exists(base_path('routes/spg.php'))) {
    require base_path('routes/spg.php');
}


