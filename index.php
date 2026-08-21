<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$category = trim((string) ($_GET['category'] ?? ''));
$query = 'SELECT * FROM menu_items WHERE is_available = 1';
$params = [];
if ($category !== '') {
    $query .= ' AND category = :category';
    $params['category'] = $category;
}
$query .= ' ORDER BY category, name';
$stmt = db()->prepare($query);
$stmt->execute($params);
$items = $stmt->fetchAll();
$categories = db()->query('SELECT DISTINCT category FROM menu_items WHERE is_available = 1 ORDER BY category')->fetchAll(PDO::FETCH_COLUMN);
$flash = pull_flash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(APP_NAME) ?> | Good food, better together</title>
    <link rel="stylesheet" href="<?= e(asset('style.css')) ?>"><link rel="stylesheet" href="<?= e(asset('features.css')) ?>"><script src="<?= e(asset('mobile-menu.js')) ?>" defer></script>
</head>
<body>
    <header class="site-header">
        <a class="brand" href="index.php"><span class="brand-mark">S</span><span>synergy<span class="brand-accent">food</span></span></a>
        <nav><a href="#menu">Menu</a><a href="#story">Our table</a><?php if (is_logged_in()): ?><span class="user-greeting">Hi, <?= e(current_user()['name']) ?></span><a class="button button-small" href="<?= current_user()['role'] === 'admin' ? 'admin.php' : '#menu' ?>"><?= current_user()['role'] === 'admin' ? 'Admin panel' : 'Your table' ?></a><a href="logout.php">Sign out</a><?php else: ?><a href="login.php">Customer sign in</a><a class="button button-small" href="login.php?role=admin">Admin</a><?php endif; ?></nav>
    </header>

    <main>
        <section class="hero">
            <div class="hero-copy">
                <p class="eyebrow">Fresh thinking. Shared plates.</p>
                <h1>Food that brings your whole table into the conversation.</h1>
                <p class="hero-text">Synergy Food is a bright, generous kitchen built around ingredients that work better together.</p>
                <a class="button" href="#menu">Explore the menu <span aria-hidden="true">↓</span></a>
            </div>
            <div class="hero-art" aria-label="Illustration of a colorful shared meal" role="img"><span>🥬</span><span>🍋</span><span>🌶️</span><strong>made<br>to share</strong></div>
        </section>

        <section class="menu-section" id="menu">
            <div class="section-heading"><div><p class="eyebrow">The current edit</p><h2>On the table</h2></div><div class="filters"><a class="filter <?= $category === '' ? 'active' : '' ?>" href="index.php">All</a><?php foreach ($categories as $itemCategory): ?><a class="filter <?= $category === $itemCategory ? 'active' : '' ?>" href="?category=<?= urlencode($itemCategory) ?>"><?= e($itemCategory) ?></a><?php endforeach; ?></div></div>
            <div class="menu-grid">
                <?php foreach ($items as $item): ?>
                    <article class="menu-card"><div class="food-image" <?= $item['image_url'] ? 'style="background-image:url(\'' . e($item['image_url']) . '\')"' : '' ?>><span class="food-icon"><?= e($item['emoji']) ?></span></div><div class="card-top"><span class="category-label"><?= e($item['category']) ?></span><span class="price">₦<?= number_format((float) $item['price'], 2) ?></span></div><h3><?= e($item['name']) ?></h3><p><?= e($item['description']) ?></p></article>
                <?php endforeach; ?>
            </div>
            <?php if (!$items): ?><p class="empty-state">Nothing is available in this category right now.</p><?php endif; ?>
        </section>

        <section class="story" id="story"><p class="eyebrow">Our table</p><h2>Many flavors.<br><em>One good feeling.</em></h2><p>We believe the best meals leave room for everyone. Our menu changes with the season, but the invitation stays the same: pull up a chair.</p></section>
    </main>
    <footer><span>© <?= date('Y') ?> Synergy Food</span><span>Made for hungry minds.</span></footer>
</body>
</html>
