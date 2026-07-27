# Motor de Comissão

## Visão Geral
Motor único de cálculo de comissões para vendas e pagamentos.

## Regras
1. Comissão inicial na primeira venda
2. Comissão de recorrência em pagamentos subsequentes
3. Comissão de gestor quando vendedor tem gestor
4. Trava de fim de mês (não gera comissão após fim do mês do vencimento)
5. Antecipação de parcelado (comissão cheia no primeiro pagamento)

## Uso
```php
$resultado = CommissionService::gerarParaPagamento($pagamento);
```

## Testes
```bash
php artisan test --filter=CommissionServiceTest
php artisan test --filter=CommissionCalculatorTest
```
