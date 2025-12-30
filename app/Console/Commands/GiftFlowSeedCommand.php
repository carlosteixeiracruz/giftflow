<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GiftFlowSeedCommand extends Command
{
    protected $signature = 'giftflow:seed';

    public function handle()
    {
        // Caminho absoluto dentro do container
        $path = '/var/www/html/storage/app/giftcards.json';
        
        // Garante que a pasta app exista dentro de storage
        if (!is_dir('/var/www/html/storage/app')) {
            mkdir('/var/www/html/storage/app', 0777, true);
        }

        $codes = [
            ['code' => 'GFLOW-TEST-0001', 'status' => 'available', 'product_id' => 'prod_abc', 'creator_id' => 'creator_123'],
            ['code' => 'GFLOW-TEST-0002', 'status' => 'available', 'product_id' => 'prod_xyz', 'creator_id' => 'creator_123'],
            ['code' => 'GFLOW-USED-0003', 'status' => 'redeemed', 'product_id' => 'prod_abc', 'creator_id' => 'creator_456'],
        ];

        if (file_put_contents($path, json_encode($codes, JSON_PRETTY_PRINT))) {
            $this->info("Arquivo JSON criado com sucesso em: $path");
        } else {
            $this->error("Erro ao escrever no arquivo. Verifique permissões.");
        }
    }
}