<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GiftCardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Requisito 2: Endpoint de resgate (POST /api/redeem)
Route::post('/redeem', [GiftCardController::class, 'redeem']);

/**
 * MOCK - Plataforma Emissora de Gift Cards (Requisitos 5 e 6)
 * Este endpoint simula o sistema externo que recebe a notificação de resgate.
 */
Route::post('/webhook/issuer-platform', function (Request $request) {
    // 1. Recuperar a assinatura enviada no Header
    $signature = $request->header('X-GiftFlow-Signature');

    // 2. Recuperar a Secret do .env (Usando a que definimos no seu arquivo)
    $secret = env('GIFTFLOW_WEBHOOK_SECRET', 'favedev_secret_2025');

    // 3. Re-calcular a assinatura com base no corpo bruto da requisição
    $computedSignature = hash_hmac('sha256', $request->getContent(), $secret);

    // 4. Validação da Assinatura (Requisito 5)
    if (!$signature || !hash_equals($computedSignature, (string)$signature)) {
        Log::warning('Tentativa de Webhook com assinatura inválida.', [
            'received' => $signature,
            'expected' => $computedSignature
        ]);
        return response()->json(['error' => 'Invalid signature'], 401);
    }

    // 5. Simulação de Idempotência (Requisito 6)
    $eventId = $request->input('event_id');
    $code = $request->input('data.code') ?? $request->input('code');

    Log::info('Webhook GiftFlow recebido e validado com sucesso!', [
        'event_id' => $eventId,
        'code' => $code
    ]);

    // Retorna 200 conforme exigido pelo desafio
    return response()->json([
        'status' => 'success',
        'message' => 'Webhook processed',
        'event_id' => $eventId
    ], 200);
});
