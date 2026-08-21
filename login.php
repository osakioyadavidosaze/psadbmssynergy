<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$requestedRole = ($_GET['role'] ?? '') === 'admin' ? 'admin' : 'customer';
$role = $_POST['role'] ?? $requestedRole;
$role = $role === 'admin' ? 'admin' : 'customer';
$errors = [];

if (is_logged_in()) {
    redirect(current_user()['role'] === 'admin' ? 'admin.php' : 'index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $mode = (string) ($_POST['mode'] ?? 'login');
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($mode === 'register' && $name === '') $errors[] = 'Name is required.';

    if (!$errors) {
        if ($mode === 'register') {
            if (find_user($email)) {
                $errors[] = 'An account with that email already exists.';
            } else {
                $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
                $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), 'customer']);
                $user = db()->lastInsertId();
                $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
                $stmt->execute([$user]);
                login_user($stmt->fetch());
                flash('Welcome to Synergy Food, ' . $name . '.');
                redirect('index.php');
            }
        } else {
            $user = find_user($email);
            if (!$user || !password_verify($password, $user['password_hash']) || ($user['role'] !== $role && $user['role'] !== 'admin')) {
                $errors[] = $role === 'admin' ? 'Those administrator details were not recognized.' : 'Those customer details were not recognized.';
            } else {
                login_user($user);
                redirect($user['role'] === 'admin' ? 'admin.php' : 'index.php');
            }
        }
    }
}

$flash = pull_flash();
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= $role === 'admin' ? 'Admin sign in' : 'Customer account' ?> | <?= e(APP_NAME) ?></title><link rel="stylesheet" href="<?= e(asset('style.css')) ?>"><link rel="stylesheet" href="<?= e(asset('features.css')) ?>"><script src="<?= e(asset('mobile-menu.js')) ?>" defer></script></head>
<body class="auth-body">
    <main class="auth-shell">
        <a class="brand" href="index.php"><span class="brand-mark">S</span><span>synergy<span class="brand-accent">food</span></span></a>
        <section class="auth-card">
            <div class="auth-heading"><p class="eyebrow"><?= $role === 'admin' ? 'Kitchen control' : 'Your table, saved' ?></p><h1><?= $role === 'admin' ? 'Welcome back.' : 'Pull up a chair.' ?></h1><p><?= $role === 'admin' ? 'Sign in to manage dishes, photos and availability.' : 'Sign in to keep your food favorites close.' ?></p></div>
            <div class="role-switch"><a class="<?= $role === 'customer' ? 'active' : '' ?>" href="login.php">Customer</a><a class="<?= $role === 'admin' ? 'active' : '' ?>" href="login.php?role=admin">Admin</a></div>
            <?php if ($flash): ?><div class="notice <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
            <?php if ($errors): ?><div class="notice error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
            <form method="post" class="auth-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="role" value="<?= e($role) ?>"><input type="hidden" name="mode" value="login">
                <label>Email<input type="email" name="email" required autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>"></label>
                <label>Password<input type="password" name="password" required minlength="6" autocomplete="current-password"></label>
                <button class="button" type="submit">Sign in <span aria-hidden="true">→</span></button>
            </form>
            <?php if ($role === 'customer'): ?><div class="auth-divider"><span>New here?</span></div><form method="post" class="auth-form register-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="role" value="customer"><input type="hidden" name="mode" value="register"><label>Name<input name="name" required autocomplete="name" value="<?= e($_POST['mode'] ?? '') === 'register' ? e($_POST['name'] ?? '') : '' ?>"></label><label>Email<input type="email" name="email" required autocomplete="email" value="<?= e($_POST['mode'] ?? '') === 'register' ? e($_POST['email'] ?? '') : '' ?>"></label><label>Password<input type="password" name="password" required minlength="6" autocomplete="new-password"></label><button class="button button-outline" type="submit">Create customer account</button></form><?php else: ?><p class="demo-hint">Admin login: <strong>osazedavid969@gmail.com</strong> / <strong>Osaze@01</strong></p><?php endif; ?>
        </section>
        <a class="text-link back-link" href="index.php">← Back to menu</a>
    </main>
</body>
</html>
