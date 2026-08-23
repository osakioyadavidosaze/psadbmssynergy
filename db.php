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
   if ($count === 0) {
    $seed = $pdo->prepare('INSERT INTO menu_items (name, description, category, price, emoji) VALUES (?, ?, ?, ?, ?)');

    $items = [
        ['Golden Harvest Bowl', 'Roasted sweet potato, citrus grains, greens and tahini crunch.', 'Bowls', 12.50, '🥗'],
        ['Smoky Garden Burger', 'Charred plant-based patty, tomato jam, crisp lettuce and herb aioli.', 'Mains', 14.00, '🍔'],
        ['Citrus Spark', 'Fresh orange, lime, ginger and a bright splash of sparkling water.', 'Drinks', 5.50, '🍊'],
        ['Chocolate Cloud', 'Silky dark chocolate mousse with sea salt and toasted cacao nibs.', 'Desserts', 7.00, '🍫'],
        ['Herbal Harmony', 'A soothing blend of chamomile, mint and lavender.', 'Drinks', 4.50, '🍵'],
        ['Spiced Lentil Soup', 'Hearty lentils with a touch of cumin and coriander.', 'Starters', 6.00, '🥣'],
        ['Berry Bliss Parfait', 'Layers of fresh berries, yogurt and granola.', 'Desserts', 6.50, '🍓'],
        ['Mediterranean Flatbread', 'Topped with olives, feta, tomatoes and fresh herbs.', 'Mains', 13.00, '🥖'],
        ['Tropical Smoothie', 'A refreshing blend of mango, pineapple and coconut water.', 'Drinks', 5.00, '🥭'],
        ['Quinoa & Kale Salad', 'Protein-packed quinoa with kale, cranberries and a lemon vinaigrette.', 'Salads', 11.00, '🥗'],
    ];

    foreach ($items as $item) {
        $seed->execute($item);
    }
} [
        'Golden Harvest Bowl',
        'Roasted sweet potato, citrus grains, greens and tahini crunch.',
        'Bowls',
        12.50,
        '🥗',
        'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80'
    ],
    [
        'Smoky Garden Burger',
        'Charred plant-based patty, tomato jam, crisp lettuce and herb aioli.',
        'Mains',
        14.00,
        '🍔',
        'https://images.unsplash.com/photo-1571091718767-18b5b1457add?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80'
    ],
    [
        'Citrus Spark',
        'Fresh orange, lime, ginger and a bright splash of sparkling water.',
        'Drinks',
        5.50,
        '🍊',
        'https://images.unsplash.com/photo-1622597467836-f3285f2131b8?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80'
    ],
    [
        'Chocolate Cloud',
        'Silky dark chocolate mousse with sea salt and toasted cacao nibs.',
        'Desserts',
        7.00,
        '🍫',
        'https://images.unsplash.com/photo-1541783245831-57d6fb0926d3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80'
    ],
    [
        'Herbal Harmony',
        'A soothing blend of chamomile, mint and lavender.',
        'Drinks',
        4.50,
        '🍵',
        'https://images.unsplash.com/photo-1576092768241-dec231879fc3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80'
    ],
    [
        'Spiced Lentil Soup',
        'Hearty lentils with a touch of cumin and coriander.',
        'Starters',
        60.00,
        '🥣',
        'https://images.unsplash.com/photo-1547592166-23ac45744acd?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80'
    ],
    [
        'Berry Bliss Parfait',
        'Layers of fresh berries, yogurt and granola.',
        'Desserts',
        12.0000,
        '🍓',
        'https://images.unsplash.com/photo-1488477181946-6428a0291777?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80'
    ],
    [
        'Mediterranean Flatbread',
        'Topped with olives, feta, tomatoes and fresh herbs.',
        'Mains',
        13.0000,
        '🥖',
        'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80'
    ],
    [
        'Tropical Smoothie',
        'A refreshing blend of mango, pineapple and coconut water.',
        'Drinks',
        5.0000,
        '🥭',
        'https://images.unsplash.com/photo-1505252585461-04db1eb84625?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80'
    ],
    [
        'Quinoa & Kale Salad',
        'Protein-packed quinoa with kale, cranberries and a lemon vinaigrette.',
        'Salads',
        131.00,
        '🥗',
        'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80'
    ],
];
   foreach ($items as $item) {
    $seed->execute($item);
}
