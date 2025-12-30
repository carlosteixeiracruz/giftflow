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
        $url = 'http://localhost:8888/api/webhook/issuer-platform';
        $secret = env('GIFTFLOW_WEBHOOK_SECRET', 'sua_secret_aqui');

        $payload = json_encode($this->data);
        $signature = hash_hmac('sha256', $payload, $secret);

        Http::withHeaders([
            'X-GiftFlow-Signature' => $signature,
            'Content-Type' => 'application/json',
        ])->post($url, $this->data);
    }
}
