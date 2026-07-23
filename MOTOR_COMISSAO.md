# Motor único de comissão — o que mudou e como validar

## Regras implementadas (as suas 5 + trava de tempo)

1. **Inicial** — primeira venda paga → `% inicial` do vendedor.
2. **Recorrência** — pagamentos seguintes → `% recorrência` (menor).
3. **Gestor** — recebe uma `%` (do cadastro do gestor) quando o vendedor tem gestor acima dele.
4. **Gestor em venda direta** (sem gestor acima) → recebe só a parte de vendedor; nenhuma comissão de gestor.
5. **Anual/parcelado no cartão** (`parcelas > 1`) → comissão **antecipada** 100% na 1ª parcela; demais parcelas não geram nada. Valor antecipado = `% inicial × 1 parcela + % recorrência × (parcelas − 1)`.
   - Ex.: 10×R$100, inicial 10% + recorrência 5% → **R$55** de uma vez.
6. **Trava do fim do mês** — a recorrência de um ciclo só é gerada se o pagamento foi confirmado **até o último dia do mês do vencimento** (28/29/30/31). Pagou depois → não gera aquele ciclo.

## Arquivos

- `app/Services/Commission/CommissionCalculator.php` — cálculo puro (sem banco), testável.
- `app/Services/Commission/CommissionService.php` — motor único: resolve percentuais, aplica idempotência (**trava por `pagamento_id`**) e grava.
- `app/Services/CommissionEngineService.php` — agora só delega ao motor único (webhook).
- `app/Services/PagamentoService.php` — `gerarComissoes()` delega ao motor único (checkout/sync). Lógica antiga preservada como `gerarComissoesLegado()` (não é mais chamada).
- `app/Console/Commands/ProcessarComissoesCommand.php` — **motor noturno** (`comissoes:processar`).
- `app/Models/Pagamento.php` — relação `comissoes()`.
- `routes/console.php` — agenda `comissoes:processar` às 03:10 (após o sync das 03:00).
- `supervisor.conf` e `render.yaml` — passam a rodar o **scheduler** e o **queue worker** (antes não rodavam → nada agendado executava).

## Como funciona agora

- **Tempo real:** webhook do Asaas → `asaas:process-events` → motor único.
- **Rede de segurança (madrugada):** o sync das 03:00 confirma os pagamentos no banco; às 03:10 o `comissoes:processar` varre pagamentos confirmados sem comissão e gera. Como é idempotente, nunca duplica.

## Como VALIDAR antes de produção (importante)

Não consegui executar PHP no meu ambiente, então a matemática foi verificada por espelho independente (11 cenários, todos OK). Faça a validação final no seu ambiente:

1. **Testes unitários da regra:**
   ```bash
   cd backend
   php artisan test --filter=CommissionCalculatorTest
   ```
   Devem passar 12 testes cobrindo as 5 regras + trava de fim de mês.

2. **Simulação sem gravar (dry-run)** num banco de staging/cópia:
   ```bash
   php artisan comissoes:processar --dry-run
   ```

3. **Rodar de verdade em staging** e conferir alguns clientes conhecidos:
   ```bash
   php artisan comissoes:processar
   ```

4. Só então subir para produção.

## Clientes antigos do Asaas (retroativo mês a mês)

Ao importar/sincronizar e editar o cliente antigo (painel Master → Clientes Asaas),
você informa **primeiro pagamento** e **último pagamento confirmado**. Ao confirmar
(ou ao salvar com um vendedor atribuído), o sistema gera o **histórico retroativo
completo**, mês a mês:

- 1º mês → comissão **inicial**; meses seguintes → **recorrência**, até o último
  pagamento informado.
- Parcelado no cartão → uma comissão **antecipada** (inicial + recorrência das
  demais parcelas), igual à regra 5.
- Funciona **mesmo para cliente inativo/CHURN/cancelado** — o histórico é gerado
  independentemente do status.
- Idempotente: chaveado por (vendedor, cliente, venda, competência); reimportar
  não duplica.

Isso alimenta seu histórico de vendas e o ticket médio. O motor noturno **ignora**
as vendas legadas (`origem = asaas_legado`) para não contar em dobro.

Arquivo: `AsaasClienteSyncController::gerarComissoesHistoricas()` — reescrito para
usar o mesmo `CommissionCalculator` do motor único (corrige a fórmula antiga de
parcelado e remove o bug dos 5% de gestor).

## ⚠️ Pontos que você precisa confirmar

- **Regras de valor fixo por plano (`CommissionRule`)**: o motor novo é 100% percentual (conforme suas regras). O caminho antigo de valor fixo por plano **não é mais usado**. Se algum plano dependia de valor fixo, me avise para reincorporar.
- **Comissões históricas**: o motor não recalcula o passado — ele só gera para pagamentos **sem** comissão. Registros antigos ficam como estão.
- **Vínculo de equipe**: a comissão de gestor depende de o vendedor ter `gestor_id` preenchido. Confirme que anexar o vendedor à equipe grava esse campo.
