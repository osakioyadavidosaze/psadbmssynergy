<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

require_login('admin');

$pdo = db();
$errors = [];
$editing = null;
$categories = ['Bowls', 'Mains', 'Sides', 'Drinks', 'Desserts'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM menu_items WHERE id = ?');
        $stmt->execute([(int) ($_POST['id'] ?? 0)]);
        flash('Menu item removed.');
        redirect('admin.php');
    }

    $id = (int) ($_POST['id'] ?? 0);
    $name = trim((string) ($_POST['name'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $category = trim((string) ($_POST['category'] ?? ''));
    $price = (float) ($_POST['price'] ?? 0);
    $emoji = trim((string) ($_POST['emoji'] ?? '🍽️')) ?: '🍽️';
    $imageUrl = trim((string) ($_POST['image_url'] ?? ''));
    $isAvailable = isset($_POST['is_available']) ? 1 : 0;

    if ($name === '') $errors[] = 'Name is required.';
    if ($description === '') $errors[] = 'Description is required.';
    if ($category === '') $errors[] = 'Category is required.';
    if ($price < 0) $errors[] = 'Price cannot be negative.';
    if ($imageUrl !== '' && !filter_var($imageUrl, FILTER_VALIDATE_URL)) $errors[] = 'Image URL must be a valid web address.';

    if (!$errors) {
        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE menu_items SET name = ?, description = ?, category = ?, price = ?, emoji = ?, image_url = ?, is_available = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $stmt->execute([$name, $description, $category, $price, $emoji, $imageUrl, $isAvailable, $id]);
            flash('Menu item updated.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO menu_items (name, description, category, price, emoji, image_url, is_available) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $description, $category, $price, $emoji, $imageUrl, $isAvailable]);
            flash('Menu item added.');
        }
        redirect('admin.php');
    }

    $editing = compact('id', 'name', 'description', 'category', 'price', 'emoji', 'imageUrl', 'isAvailable');
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM menu_items WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editing = $stmt->fetch() ?: null;
}

$items = $pdo->query('SELECT * FROM menu_items ORDER BY created_at DESC, id DESC')->fetchAll();
$flash = pull_flash();
?>
<!doctype html>
<html lang="en">
    <head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Manage menu | <?= e(APP_NAME) ?></title><link rel="stylesheet" href="<?= e(asset('style.css')) ?>"><link rel="stylesheet" href="<?= e(asset('features.css')) ?>"><script src="<?= e(asset('mobile-menu.js')) ?>" defer></script></head>
<body class="admin-body">
    <header class="site-header"><a class="brand" href="index.php"><span class="brand-mark">S</span><span>synergy<span class="brand-accent">food</span></span></a><div class="header-actions"><span class="user-greeting">Hi, <?= e(current_user()['name']) ?></span><a class="text-link" href="logout.php">Sign out</a><a class="text-link" href="index.php">View public menu →</a></div></header>
    <main class="admin-main"><div class="admin-intro"><div><p class="eyebrow">Kitchen control</p><h1>Manage your menu</h1></div><p>Add, edit, hide or remove items. Changes appear on the public menu instantly.</p></div>
        <?php if ($flash): ?><div class="notice <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
        <?php if ($errors): ?><div class="notice error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>
        <div class="admin-layout"><section class="form-panel"><h2><?= $editing ? 'Edit item' : 'New item' ?></h2><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= e($editing['id'] ?? 0) ?>"><input type="hidden" name="action" value="save"><label>Name<input name="name" required value="<?= e($editing['name'] ?? '') ?>"></label><label>Description<textarea name="description" rows="4" required><?= e($editing['description'] ?? '') ?></textarea></label><div class="form-row"><label>Category<select name="category" required><option value="">Choose one</option><?php foreach ($categories as $option): ?><option <?= ($editing['category'] ?? '') === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select></label><label>Price<input type="number" min="0" step="0.01" name="price" required value="<?= e($editing['price'] ?? '') ?>"></label></div><div class="form-row"><label>Icon / emoji<input name="emoji" maxlength="8" value="<?= e($editing['emoji'] ?? '🍽️') ?>"></label><label>Food image URL<input type="url" name="image_url" placeholder="https://..." value="<?= e($editing['image_url'] ?? $editing['imageUrl'] ?? '') ?>"></label></div><label class="checkbox"><input type="checkbox" name="is_available" <?= !isset($editing['is_available']) || (int) $editing['is_available'] === 1 || (int) ($editing['isAvailable'] ?? 0) === 1 ? 'checked' : '' ?>> Available on public menu</label><button class="button" type="submit"><?= $editing ? 'Save changes' : 'Add menu item' ?></button><?php if ($editing): ?><a class="cancel-link" href="admin.php">Cancel edit</a><?php endif; ?></form></section>
        <section class="table-panel"><div class="panel-heading"><h2>All items</h2><span><?= count($items) ?> total</span></div><div class="table-wrap"><table><thead><tr><th>Item</th><th>Category</th><th>Price</th><th>Status</th><th></th></tr></thead><tbody><?php foreach ($items as $item): ?><tr><td><span class="table-emoji"><?= e($item['emoji']) ?></span><?= e($item['name']) ?></td><td><?= e($item['category']) ?></td><td>₦<?= number_format((float) $item['price'], 2) ?></td><td><span class="status <?= $item['is_available'] ? 'available' : 'hidden' ?>"><?= $item['is_available'] ? 'Live' : 'Hidden' ?></span></td><td class="actions"><a href="?edit=<?= (int) $item['id'] ?>">Edit</a><form method="post" onsubmit="return confirm('Remove this menu item?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $item['id'] ?>"><button type="submit" class="delete-button">Delete</button></form></td></tr><?php endforeach; ?></tbody></table></div></section></div>
    </main>
</body>
</html>
