<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GiftCardController;

// Requisito 2: Endpoint de resgate
Route::post('/redeem', [GiftCardController::class, 'redeem']);

// Requisito 5: Mock do emissor para testar o recebimento do webhook
Route::post('/webhook/issuer-platform', function (\Illuminate\Http\Request $request) {
    return response()->json(['status' => 'success'], 200);
});