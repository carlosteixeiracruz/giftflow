🎁 GiftFlow - API de Resgate de Gift Cards

Este projeto é um desafio técnico para uma API de resgate de Gift Cards, focada em performance, uso de filas para Webhooks, segurança com assinaturas digitais e arquitetura baseada em eventos.
🛠️ Decisões Técnicas & Diferenciais

    Segurança HMAC SHA256: Implementação de assinatura digital no Header (X-GiftFlow-Signature) para garantir a integridade e autenticidade dos Webhooks enviados.

    Idempotência de Resgate: Garantia de que um mesmo código não seja processado mais de uma vez para o mesmo usuário, evitando gastos duplicados.

    Queueing (Filas): Webhooks processados em background (driver database) para resposta instantânea ao usuário.

    Persistência em JSON: Simulação de integração com sistemas legados através de parsing e escrita em arquivos JSON estruturados.

    Dockerizado (Sail): Ambiente isolado e reprodutível via containers.

🚀 Como Instalar e Rodar

    Subir os Containers:
    Bash

./vendor/bin/sail up -d

Configurar o Ambiente:
Bash

./vendor/bin/sail composer install
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate

Permissões Críticas (Docker Desktop):
Bash

docker exec -u root -it giftflow-laravel.test-1 chmod -R 777 storage database

Popular Dados (Seed):
Bash

    ./vendor/bin/sail artisan giftflow:seed

📡 Testando a API
1. Resgate de Gift Card

    Endpoint: POST http://localhost:8888/api/redeem

    Body JSON:

JSON

{
    "code": "GFLOW-TEST-0001",
    "user": {
        "email": "antonio@favedev.com"
    }
}

2. Validação do Webhook (Simulação de Emissor)

O sistema possui um Mock Endpoint integrado que valida a assinatura dos Webhooks recebidos.

    Rota de Escuta: /api/webhook/issuer-platform

    Validação: O endpoint verifica se o HMAC enviado no header confere com a GIFTFLOW_WEBHOOK_SECRET.

Para processar a fila e ver a validação acontecendo no log:
Bash

# Terminal 1: Rodar o Worker
./vendor/bin/sail artisan queue:work

# Terminal 2: Ver o Log de Sucesso
tail -f storage/logs/laravel.log

📂 Estrutura de Arquivos

    storage/app/giftcards.json: Banco de dados de códigos disponíveis.

    storage/app/redemptions.json: Histórico de resgates para controle de idempotência.

Desenvolvido por Antonio (FaveDev)