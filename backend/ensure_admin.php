<?php
// Script para garantir que o admin existe com a senha correta
// Executado pelo entrypoint via: php /usr/local/bin/ensure_admin.php

$host = $_ENV['DB_HOST'] ?? 'postgres';
$port = $_ENV['DB_PORT'] ?? '5432';
$db   = $_ENV['DB_DATABASE'] ?? 'basileia_vendas';
$user = $_ENV['DB_USERNAME'] ?? 'postgres';
$pass = $_ENV['DB_PASSWORD'] ?? 'secret';

$email    = 'basileia.vendas@basileia.com';
$password = 'B4s1131@V3nd4s!2026#Xk9$mP2@nQ7&wZ5!pL8%rT4^vN6*bH0';

echo "=== Garantindo admin ===\n";

try {
    $pdo = new PDO("pgsql:host={$host};port={$port};dbname={$db}", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar se a tabela users existe
    $check = $pdo->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name='users')");
    if (!$check->fetchColumn()) {
        echo "Tabela users não existe ainda. Pulando.\n";
        exit(0);
    }

    // Hash da senha
    $hashed = password_hash($password, PASSWORD_BCRYPT);

    // Verificar se o admin existe
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();

    if ($existing) {
        $update = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
        $update->execute([$hashed, $existing['id']]);
        echo "Admin ATUALIZADO: {$email} (ID: {$existing['id']})\n";
    } else {
        $insert = $pdo->prepare("INSERT INTO users (name, email, password, perfil, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $insert->execute(['Administrador Master', $email, $hashed, 'master']);
        echo "Admin CRIADO: {$email}\n";
    }

    // Verificar
    $stmt = $pdo->prepare("SELECT id, email, perfil FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    echo "Verificação: ID={$user['id']} email={$user['email']} perfil={$user['perfil']}\n";

    // Testar a senha
    $stmt = $pdo->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $hash = $stmt->fetchColumn();
    $valid = password_verify($password, $hash);
    echo "Senha válida: " . ($valid ? 'SIM ✓' : 'NÃO ✗') . "\n";

} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    exit(1);
}
