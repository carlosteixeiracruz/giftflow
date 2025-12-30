<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Jobs\SendWebhookJob;

class GiftCardController extends Controller
{
    /**
     * Helper para obter o caminho do banco de Gift Cards
     */
    private function getStoragePath()
    {
        return storage_path('app/giftcards.json');
    }

    /**
     * Helper para obter o caminho do log de resgates (Idempotência)
     */
    private function getRedemptionsPath()
    {
        return storage_path('app/redemptions.json');
    }

    public function redeem(Request $request)
    {
        // 1. Validação
        $validated = $request->validate([
            'code' => 'required|string',
            'user.email' => 'required|email'
        ]);

        $email = $validated['user']['email'];
        $codeStr = $validated['code'];

        // 2. Verificar Idempotência
        if ($this->isAlreadyRedeemedByUser($codeStr, $email)) {
            return response()->json([
                'status' => 'redeemed',
                'message' => 'Resgate já processado anteriormente para este usuário.'
            ], 200);
        }

        // 3. Carregar Base de Dados
        $storageFile = $this->getStoragePath();
        $foundIndex = null;

        if (!file_exists($storageFile)) {
            return response()->json(['message' => 'Gift Card database not found.'], 500);
        }

        $cards = json_decode(file_get_contents($storageFile), true) ?? [];

        // 4. Buscar Código
        foreach ($cards as $index => $card) {
            if (($card['code'] ?? '') === $codeStr) {
                $foundIndex = $index;
                break;
            }
        }

        if ($foundIndex === null) {
            return response()->json(['message' => 'Code not found'], 404);
        }

        if (($cards[$foundIndex]['status'] ?? '') === 'redeemed') {
            return response()->json(['message' => 'Code already redeemed'], 409);
        }

        // 5. Preparar dados e Atualizar
        $product_id = $cards[$foundIndex]['product_id'] ?? 'unknown';
        $creator_id = $cards[$foundIndex]['creator_id'] ?? 'unknown';

        $cards[$foundIndex]['status'] = 'redeemed';
        file_put_contents($storageFile, json_encode($cards, JSON_PRETTY_PRINT));

        // 6. Registrar Idempotência e Webhook
        $this->registerRedemption($codeStr, $email);

        SendWebhookJob::dispatch([
            'event' => 'giftcard.redeemed',
            'code' => $codeStr,
            'email' => $email,
            'timestamp' => now()->toIso8601String()
        ]);

        return response()->json([
            'status' => 'redeemed',
            'code' => $codeStr,
            'creator_id' => $creator_id,
            'product_id' => $product_id
        ], 200);
    }

    private function isAlreadyRedeemedByUser($code, $email)
    {
        $path = $this->getRedemptionsPath();
        if (!file_exists($path)) return false;

        $redemptions = json_decode(file_get_contents($path), true) ?? [];
        foreach ($redemptions as $r) {
            if (($r['code'] ?? '') === $code && ($r['email'] ?? '') === $email) {
                return true;
            }
        }
        return false;
    }

    private function registerRedemption($code, $email)
    {
        $path = $this->getRedemptionsPath();
        $redemptions = file_exists($path) ? json_decode(file_get_contents($path), true) : [];

        $redemptions[] = [
            'code' => $code,
            'email' => $email,
            'date' => now()->toIso8601String()
        ];

        file_put_contents($path, json_encode($redemptions, JSON_PRETTY_PRINT));
    }
}
