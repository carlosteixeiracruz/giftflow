<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected array $data) {}

    public function handle()
    {
        // 1. Buscamos a URL e a Secret diretamente do seu .env
        $url = env('GIFTFLOW_WEBHOOK_URL', 'http://localhost/api/webhook/issuer-platform');
        $secret = env('GIFTFLOW_WEBHOOK_SECRET', 'favedev_secret_2025');

        // 2. Preparamos o payload e geramos a assinatura HMAC SHA256 (Requisito 5 do desafio)
        $payload = json_encode($this->data);
        $signature = hash_hmac('sha256', $payload, $secret);

        // 3. Enviamos o Post com os headers obrigatórios
        Http::withHeaders([
            'X-GiftFlow-Signature' => $signature, // Assinatura para validação do mock
            'Accept'               => 'application/json',
            'Content-Type'         => 'application/json',
        ])->post($url, $this->data);
    }
}
