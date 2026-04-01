# Integração com Checkout

## Configuração

Adicione ao `.env`:

```env
CHECKOUT_API_URL=http://localhost:8001
CHECKOUT_API_KEY=ck_live_sua_chave_aqui
CHECKOUT_WEBHOOK_SECRET=seu_webhook_secret_aqui
CHECKOUT_TIMEOUT=30
```

## Rota de Webhook

Adicione ao `routes/web.php`:

```php
// Checkout webhook (recebe eventos do serviço de Checkout)
Route::post('/webhook/checkout', [\App\Http\Controllers\Integration\CheckoutWebhookController::class, 'handle'])
    ->name('webhook.checkout');
```

## Uso no Controller de Vendas

```php
use App\Services\Checkout\CheckoutClient;

// Criar cobrança via Checkout
$client = new CheckoutClient();
$result = $client->createTransaction([
    'external_id' => $venda->id,
    'amount' => $venda->valor_total,
    'payment_method' => 'credit_card',
    'customer' => [
        'name' => $cliente->nome,
        'email' => $cliente->email,
        'document' => $cliente->cpf_cnpj,
    ],
    'metadata' => [
        'venda_id' => $venda->id,
        'vendedor_id' => $venda->vendedor_id,
    ],
]);
```

## Fluxo

1. BasileiaVendas cria transação via `CheckoutClient::createTransaction()`
2. Checkout processa e envia para o gateway (Asaas)
3. Gateway responde → Checkout atualiza status
4. Checkout envia webhook para BasileiaVendas em `/webhook/checkout`
5. `CheckoutWebhookController` atualiza venda/pagamento

## Eventos Suportados

- `payment.approved` → Atualiza venda para "paga", gera comissão
- `payment.refused` → Atualiza venda para "recusada"
- `payment.pending` → Atualiza venda para "pendente"
- `payment.overdue` → Atualiza venda para "vencida"
- `payment.refunded` → Atualiza venda para "estornada"
- `boleto.generated` → Salva URL/Barcode do boleto
- `pix.generated` → Salva QRCode do PIX
