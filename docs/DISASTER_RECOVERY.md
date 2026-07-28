# Plano de Disaster Recovery - Basiléia Vendor OS

Este documento descreve os procedimentos passo a passo para recuperar o sistema em caso de falha catastrófica (corrupção de dados, perda do servidor, ataque ransomware, etc).

## 1. Cenário: Corrupção do Banco de Dados
**Sintoma:** O sistema está no ar, mas os dados estão corrompidos ou apagados acidentalmente.
**RTO (Recovery Time Objective):** 15 minutos.

### Procedimento de Restauração:
1. Conecte via SSH ao servidor de produção.
2. Navegue até a pasta do projeto: `cd /caminho/do/projeto/BasileaVendor`
3. Liste os backups disponíveis na pasta `backups/`: `ls -la backups/`
4. Identifique o backup `.sql.gz` mais recente que não esteja corrompido.
5. Execute o script de restauração:
   ```bash
   ./scripts/restore-database.sh backups/basileia_vendas_YYYY-MM-DD_HH-MM-SS.sql.gz
   ```
6. O script solicitará a confirmação escrevendo `SIM`. Isso destruirá os dados corrompidos e injetará o dump selecionado.
7. Verifique o painel administrativo para validar se os dados retornaram corretamente.

## 2. Cenário: Perda Total do Servidor
**Sintoma:** O servidor (VPS/EC2) inteiro foi apagado, caiu permanentemente ou foi comprometido.
**RTO (Recovery Time Objective):** 1 hora.

### Procedimento de Restauração:
1. Crie uma nova máquina (Ubuntu 22.04 LTS ou 24.04 LTS) na AWS/DigitalOcean.
2. Instale o Docker e o Docker Compose.
3. Clone o repositório do projeto do Github.
4. Transfira o arquivo de backup `.sql.gz` de um armazenamento externo (S3, Google Drive, ou sua máquina local) para a máquina nova.
5. Crie/Restaure os arquivos vitais: `.env.production` e os certificados em `backend/ssl/`.
6. Inicie os serviços em background:
   ```bash
   docker-compose up -d --build
   ```
7. Aguarde 30 segundos para o PostgreSQL inicializar completamente.
8. Execute o script de restauração:
   ```bash
   ./scripts/restore-database.sh caminho/do/seu/backup.sql.gz
   ```
9. Atualize o DNS do seu domínio (`vendor.basileia.global`) para apontar para o IP da nova máquina.

## 3. Práticas Essenciais de Manutenção
- **Armazenamento Externo:** O script de backup salva os dados na pasta `/backups/` dentro do próprio servidor. É fundamental que você tenha um script secundário, cronjob ou ferramenta (ex: AWS CLI) sincronizando essa pasta periodicamente com um bucket AWS S3 ou similar, para não perder os backups caso o disco inteiro do servidor queime.
- **Teste de Restore:** A cada 3 meses, pegue um backup do servidor de produção, puxe para o seu computador (local) e rode o `./scripts/restore-database.sh` para garantir que os arquivos gerados não estão corrompidos.
- **Chaves de API (APP_KEY e ASAAS):** Faça backup das chaves que ficam no `.env.production`. Se o servidor cair e você gerar um `APP_KEY` novo, todas as senhas dos usuários antigos do banco se tornarão inválidas e você precisará resetá-las! Guarde a sua `APP_KEY` a sete chaves.
