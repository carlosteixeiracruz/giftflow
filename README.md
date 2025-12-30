# 🎁 GiftFlow - API de Resgate de Gift Cards

Este projeto é um desafio técnico para uma API de resgate de Gift Cards, focada em performance, uso de filas para Webhooks e arquitetura baseada em eventos.

## 🛠️ Decisões Técnicas & Diferenciais
- **Persistência em JSON**: Utilizado como banco de dados principal para os Gift Cards, simulando integração com sistemas legados ou arquivos de terceiros.
- **Queueing (Filas)**: Implementação de Webhooks em background utilizando o driver `database` (SQLite) para garantir que a resposta da API seja instantânea, sem esperar o retorno do servidor de destino.
- **Dockerizado**: Configurado via Laravel Sail para garantir que o ambiente rode identicamente em qualquer máquina.
- **Zend Certified Mindset**: Código limpo, tratamento de erros robusto e atenção a permissões de sistema de arquivos.

## 🚀 Como Instalar e Rodar

1. **Subir os Containers (Sail):**
   ```bash
   ./vendor/bin/sail up -d

    Configurar o Ambiente: Instale as dependências e gere a chave da aplicação:
    Bash

./vendor/bin/sail composer install
./vendor/bin/sail artisan key:generate

Permissões Críticas (Importante para Docker Desktop): Como o PHP precisa escrever no JSON, no SQLite e nos Logs dentro do container, rode:
Bash

docker exec -u root -it giftflow-laravel.test-1 chmod -R 777 storage database

Preparar a Fila (Migrations):
Bash

    ./vendor/bin/sail artisan migrate

📡 Testando a API
1. Guia de Testes da API (Postman)

Endpoint: POST http://localhost/api/gift-cards/redeem

Headers Obrigatórios:

    Accept: application/json

    Content-Type: application/json

Corpo da Requisição (Body JSON):
JSON

{
    "code": "GFLOW-TEST-0001",
    "user": {
        "email": "antonio@favedev.com"
    }
}

🟢 Respostas Esperadas:
Status Code	Cenário	Exemplo de Mensagem
200 OK	Sucesso no resgate	"message": "Resgate processado com sucesso!"
422 Unprocessable Entity	Dados inválidos (ex: e-mail vazio)	"message": "The user.email field is required."
404 Not Found	Código inexistente no JSON	"message": "Gift card não encontrado."
409 Conflict	Código já utilizado anteriormente	"message": "Este gift card já foi resgatado."
2. Processar o Webhook

O sistema irá enfileirar o envio do Webhook para garantir alta disponibilidade. Para disparar o envio do Job que está na fila e ver o resultado no terminal:
Bash

./vendor/bin/sail artisan queue:work --once

📂 Estrutura de Dados

Os Gift Cards estão localizados em storage/app/giftcards.json. O sistema realiza o parsing deste arquivo, valida se o código existe e se o status está como available antes de permitir o resgate e disparar os eventos de Webhook.

Desenvolvido por Antonio (FaveDev)