# Rodar tudo local (banco + backend + frontend) na porta 8007

> Deixei tudo pré-configurado. **Não consegui executar aqui** (meu ambiente não
> tem Docker/PHP/Postgres e a porta não seria acessível do seu navegador), então
> rode estes comandos na SUA máquina. Precisa ter Docker Desktop instalado.

## Passo a passo (IMPORTANTE: se já tentou subir antes, comece pelo reset)

Na raiz do projeto (onde está o `docker-compose.yml`):

```bash
# 0. RESET — apaga o banco antigo (que ficou com senha errada da 1ª tentativa)
docker compose down -v

# 1. Subir banco + backend + frontend (o backend já migra e cria o master no boot)
docker compose up -d --build
```

Aguarde ~40 segundos (o backend espera o banco, migra e cria o usuário).
Depois abra: **http://localhost:8007**

Se em ~1 min ainda der erro, veja o log do backend e me mande a mensagem:
```bash
docker compose logs backend --tail=80
```

- Frontend: http://localhost:8007
- Backend/API: proxied automaticamente em http://localhost:8007/api
- Banco Postgres: dentro do compose (serviço `postgres`)

## Login (ambiente local)

Criado pelo seeder `CreateAdminUserSeeder`:
- **E-mail:** `basileia.vendas@basileia.com`
- **Senha:** `B4s1131@V3nd4s!2026#Xk9$mP2@nQ7&wZ5!pL8%rT4^vN6*bH0`

(Troque depois. Em produção, gere uma APP_KEY nova e credenciais próprias.)

## O que já configurei para isso funcionar
- `docker-compose.yml`: frontend exposto na **8007**, API apontando para `/api`.
- `.env` (raiz, **gitignored**): APP_KEY gerada, `APP_ENV=local`, `APP_DEBUG=true`, senha de banco de dev.
- `backend/entrypoint.sh`: agora **espera o banco e roda `migrate --force`** no boot.
- `nginx.conf` do backend já escuta na 8000 e o front faz proxy `/api → backend:8000`.

## Comandos úteis
```bash
docker compose logs -f backend      # ver logs do Laravel
docker compose logs -f frontend     # ver logs do Next
docker compose exec backend php artisan migrate:fresh --seed   # zerar e recriar
docker compose down                 # parar tudo
```

## Se algo falhar
- **500 no /api**: veja `docker compose logs -f backend`. Geralmente é migration/seed
  faltando — rode o passo 2.
- **Página em branco / erro de fonte**: já corrigido (não depende mais do Google Fonts).
- **Porta 8007 ocupada**: troque `8007:3000` no `docker-compose.yml`.

> Importante: eu validei sintaxe (PHP 526 arquivos OK) e o build de produção do
> Next passou, mas **não rodei o Laravel em runtime**. O teste de fumaça final
> (subir, logar, criar equipe com gestor, ver status do cliente) é com você.
