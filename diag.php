<?php
$pdo = new PDO('sqlite:' . __DIR__ . '/data/synergy.sqlite');
$images = [
    'Golden Harvest Bowl' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80',
    'Smoky Garden Burger' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80',
    'Citrus Spark' => 'https://images.unsplash.com/photo-1613478223719-2ab802602423?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80',
    'Chocolate Cloud' => 'https://images.unsplash.com/photo-1551024506-0bccd828d307?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80',
];
$stmt = $pdo->prepare('UPDATE menu_items SET image_url = ? WHERE name = ? AND (image_url IS NULL OR image_url = \'\')');
foreach ($images as $name => $url) {
    $stmt->execute([$url, $name]);
}
echo "--- After update ---" . PHP_EOL;
foreach ($pdo->query('SELECT id, name, image_url FROM menu_items') as $r) {
    echo $r['id'] . ' | ' . $r['name'] . ' | img=[' . $r['image_url'] . ']' . PHP_EOL;
}

