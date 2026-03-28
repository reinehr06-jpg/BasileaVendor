# ==========================================
# GUIA DEFINITIVO - BASILEIA VENDAS
# ==========================================

## 🔗 URLs DE ACESSO

### Produção (Render) - JÁ FUNCIONANDO!
https://basileiavendas.onrender.com

### Localhost + Ngrok (para testes)
Veja instrucciones abajo

---

## 💻 COMO RODAR LOCALHOST

### PASSO 1: Abrir o projeto
Abra o terminal e digite:

```bash
cd /Users/viniciusreinehr/.gemini/antigravity/scratch/BasileiaVendas/basileia-app
```

### PASSO 2: Iniciar o servidor Laravel
No terminal, digite:

```bash
php artisan serve --port=8000
```

Deixe esse terminal aberto!

### PASSO 3: Ngrok (para ter URL pública)
Abra OUTRO terminal e digite:

```bash
ngrok http 8000
```

**A URL que aparecer ali é a sua URLpública!**

---

## 🚀 ATUALIZAR PRODUÇÃO (RENDER)

### Sempre que fizer alterações no código:

```bash
cd /Users/viniciusreinehr/.gemini/antigravity/scratch/BasileiaVendas/basileia-app
git add .
git commit -m "atualização"
git push origin main
```

O Render vai atualizar automaticamente!

---

## 📋 RESUMO

| O que você quer | O que fazer |
|-----------------|-------------|
| Acessar sistema agora | https://basileiavendas.onrender.com |
| Testar local | `php artisan serve` + `ngrok http 8000` |
| Atualizar produção | `git add . && git commit -m "x" && git push` |

---

## ⚠️ QUE NUNCA FAZER

- ❌ NÃO edite arquivos no Render manualmente
- ❌ NÃOmexa no banco via phpMyAdmin sem backup
- ❌ NÃO desliga o ngrok se quiser URL pública funcionando