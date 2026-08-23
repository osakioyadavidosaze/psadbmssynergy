<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $directory = dirname(DB_PATH);
    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');

    $pdo->exec(<<<'SQL'
        CREATE TABLE IF NOT EXISTS menu_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT NOT NULL,
            category TEXT NOT NULL,
            price REAL NOT NULL CHECK(price >= 0),
            emoji TEXT NOT NULL DEFAULT '🍽️',
            image_url TEXT NOT NULL DEFAULT '',
            is_available INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    SQL);

    $columns = $pdo->query('PRAGMA table_info(menu_items)')->fetchAll();
    $columnNames = array_column($columns, 'name');
    if (!in_array('image_url', $columnNames, true)) {
        $pdo->exec("ALTER TABLE menu_items ADD COLUMN image_url TEXT NOT NULL DEFAULT ''");
    }

    $pdo->exec(<<<'SQL'
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'customer' CHECK(role IN ('customer', 'admin')),
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    SQL);

    $adminExists = $pdo->prepare('SELECT COUNT(*) FROM users WHERE role = ?');
    $adminExists->execute(['admin']);
    if ((int) $adminExists->fetchColumn() === 0) {
        $seedAdmin = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $seedAdmin->execute(['Kitchen Admin', 'osazedavid969@gmail.com', password_hash('Osaze@01', PASSWORD_DEFAULT), 'admin']);
    }

    $count = (int) $pdo->query('SELECT COUNT(*) FROM menu_items')->fetchColumn();
    
// 1. Make sure the image column exists
try {
    $pdo->exec("ALTER TABLE menu_items ADD COLUMN image TEXT");
} catch (PDOException $e) {
    // ignore if it already exists
}

// 2. Clear old data so we can re-seed with images
$pdo->exec("DELETE FROM menu_items");

// 3. Insert the items with images
$seed = $pdo->prepare("INSERT INTO menu_items (name, description, category, price, emoji, image) VALUES (?, ?, ?, ?, ?, ?)");

$items = [
    [
        'Golden Harvest Bowl',
        'Roasted sweet potato, citrus grains, greens and tahini crunch.',
        'Bowls',
        124.50,
        '🥗',
        'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=800&q=80'
    ],
    [
        'Smoky Garden Burger',
        'Charred plant-based patty, tomato jam, crisp lettuce and herb aioli.',
        'Mains',
        142.00,
        '🍔',
        'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&q=80'
    ],
    [
        'Citrus Spark',
        'Fresh orange, lime, ginger and a bright splash of sparkling water.',
        'Drinks',
        5.5000,
        '🍊',
        'https://images.unsplash.com/photo-1622597467836-f3285f2131b8?w=800&q=80'
    ],
    [
        'Chocolate Cloud',
        'Silky dark chocolate mousse with sea salt and toasted cacao nibs.',
        'Desserts',
        700.00,
        '🍫',
        'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=800&q=80'
    ],
];

foreach ($items as $item) {
    $seed->execute($item);
}

return $pdo;
}
