<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'name' => (string) $user['name'],
        'email' => (string) $user['email'],
        'role' => (string) $user['role'],
    ];
}

function logout_user(): void
{
    unset($_SESSION['user']);
    session_regenerate_id(true);
}

function require_login(?string $role = null): void
{
    $user = current_user();
    if (!$user || ($role !== null && $user['role'] !== $role)) {
        flash($role === 'admin' ? 'Please sign in as an administrator.' : 'Please sign in to continue.', 'error');
        redirect('login.php' . ($role === 'admin' ? '?role=admin' : ''));
    }
}

function find_user(string $email): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([strtolower(trim($email))]);
    $user = $stmt->fetch();
    return $user ?: null;
}
