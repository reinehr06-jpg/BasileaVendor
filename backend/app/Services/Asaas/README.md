# Integração Asaas

## Visão Geral
Services especializados para integração com API do Asaas.

## Services
- `CustomerService`: Gestão de clientes
- `PaymentService`: Gestão de pagamentos
- `SubscriptionService`: Gestão de assinaturas
- `SplitService`: Gestão de splits

## Uso
```php
$asaas = new AsaasService();
$customer = $asaas->createCustomer('Nome', '12345678900');
$payment = $asaas->createPayment($customerId, 100.00, '2026-12-31', 'PIX', 'Descrição');
```

## Testes
```bash
php artisan test --filter=AsaasServiceTest
```
