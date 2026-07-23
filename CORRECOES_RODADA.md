# Correções desta rodada (verificado: PHP lint 527 OK, tsc ✅, build ✅)

## 1. Bug "The string did not match the expected pattern" (gestor na equipe)
Causa: no Safari/WebKit, chamar `res.json()` num corpo vazio ou não-JSON lança
essa mensagem críptica, mascarando o erro real.
- **`src/lib/api.ts`**: blindado. Agora lê o corpo como texto e parseia com
  segurança; nunca mais estoura essa mensagem. Erros de validação (422) do
  Laravel são formatados em texto legível para o toast.
- **`EquipeController` (store/update)**: a unicidade do gestor passou a considerar
  apenas equipes **ATIVAS** (equipes inativas não travam mais a atribuição), com
  mensagem clara: "Este gestor já é responsável por outra equipe ativa."

## 2. Status do cliente (regra nova)
Alinhado em `ClienteStatusService` (via Asaas e no fallback local):
- **Ativo** — pagamento em dia.
- **Pendente** — pagamento atrasado até 7 dias (ou aguardando 1º pagamento).
- **Churn** — atrasado há **mais de 7 dias**.
(Antes o corte era 30 dias e havia o estado extra "inadimplente".)

## 3. Limpeza de código morto
Removidas 2 pastas de rota **duplicadas e sem nenhum link de entrada**:
- `src/app/(menu)/analises-e-contabil/`
- `src/app/(menu)/pessoas-e-empresas/`
(As outras duplicadas — `contabilidade`, `cadastros`, `gestao-financeira` —
**ainda são referenciadas** por telas vivas, então NÃO foram removidas.)

## Sobre "reestruturar tudo em árvore"
Importante: os dois projetos **já são árvores**.
- No **Next.js (App Router)** a estrutura de pastas em `src/app/**` **é** a árvore
  de URLs — `(menu)/dashboard`, `(menu)/gestao-comercial/vendedores`, etc. Mover
  esses arquivos muda as URLs e quebra os links. Por isso a limpeza correta é
  **remover o que está morto** (feito), não reorganizar às cegas.
- No **Laravel** a estrutura já segue o padrão MVC (`Controllers/`, `Models/`,
  `Services/`). Renomear namespaces em massa quebraria o autoload.

Recomendação: seguir removendo dead-code de forma incremental e verificada (posso
listar arquivo por arquivo os candidatos), em vez de um "move" global arriscado.

## Garantia de estabilidade
Fiz checagem de sintaxe em todos os 527 arquivos PHP (0 erros) e build de produção
do Next.js (passou). Como não consigo executar o Laravel/banco neste ambiente, a
validação final de runtime (subir, logar, criar equipe com gestor, ver status)
deve ser feita por você em staging antes de produção.
