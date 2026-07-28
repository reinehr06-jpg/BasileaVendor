# Contatos de Suporte e Escala On-Call — Basiléia Vendor OS

## Equipe Técnica

| Função | Nome | E-mail | Telefone / WhatsApp |
|--------|------|--------|---------------------|
| **CTO / Responsável Técnico** | Vinícius Reinehr | viniciusreinehr@basileia.com | (XX) XXXXX-XXXX |
| **DevOps / Infraestrutura** | [A definir] | devops@basileia.com | (XX) XXXXX-XXXX |
| **Suporte N1 (Operações)** | [A definir] | suporte@basileia.com | (XX) XXXXX-XXXX |

> ⚠️ **Preencha os dados reais acima antes de ir para produção!**

---

## Escala de On-Call (Plantão)

| Dia da Semana | Responsável | Horário |
|---------------|-------------|---------|
| Segunda a Sexta | Vinícius Reinehr | 08:00 – 18:00 |
| Segunda a Sexta (Noite) | [A definir] | 18:00 – 08:00 |
| Sábado e Domingo | [A definir] | 24h |

---

## Procedimento de Emergência (SEV-1)

1. **Detectou o problema?** Consulte o [Runbook](./RUNBOOK.md) para diagnóstico rápido.
2. **Não conseguiu resolver em 15 minutos?** Escalone para o responsável técnico (CTO).
3. **Sistema completamente fora do ar?**
   - Execute `docker-compose down && docker-compose up -d` no servidor.
   - Se o banco corrompeu, execute `bash scripts/restore-database.sh`.
   - Após restauração, notifique a equipe no grupo do WhatsApp.
4. **Após resolução:** Preencha o template de Post-Mortem do [Runbook](./RUNBOOK.md).

---

## Contatos de Terceiros (Fornecedores)

| Serviço | Fornecedor | Contato / Painel |
|---------|------------|------------------|
| **Gateway de Pagamentos** | Asaas | https://www.asaas.com → Painel do Cliente |
| **Monitoramento de Erros** | Sentry | https://sentry.io → Projeto `vendor-os` |
| **Hospedagem / VPS** | [A definir] | [URL do Painel] |
| **Domínio / DNS** | [A definir] | [URL do Painel] |
| **E-mail Transacional** | [A definir] | [URL do Painel] |

---

## Grupo de Comunicação de Incidentes

- **WhatsApp:** [Link do grupo — a definir]
- **Slack/Discord:** [Canal — a definir]

> Em caso de incidente SEV-1 ou SEV-2, a comunicação deve ser feita **imediatamente** no grupo acima, mesmo fora do horário comercial.
