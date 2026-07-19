<?php

if (!isset($pdo)) {
    require_once __DIR__ . '/../config/database.php';
}

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');


/*
|--------------------------------------------------------------------------
| Homepage Statistics
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        SUM(role='farmer')  AS farmers,
        SUM(role='buyer')   AS buyers,
        SUM(role='trucker') AS truckers
    FROM users
    WHERE status='active'
");

$stats = $stmt->fetch(PDO::FETCH_ASSOC);

$farmerCount  = (int) ($stats['farmers'] ?? 0);
$buyerCount   = (int) ($stats['buyers'] ?? 0);
$truckerCount = (int) ($stats['truckers'] ?? 0);

/*
|--------------------------------------------------------------------------
| Product Count
|--------------------------------------------------------------------------
*/

$productCount = (int) $pdo->query("
    SELECT COUNT(*)
    FROM products
    WHERE status='approved'
")->fetchColumn();

/*
|--------------------------------------------------------------------------
| Delivery Count
|--------------------------------------------------------------------------
*/

$deliveryCount = (int) $pdo->query("
    SELECT COUNT(*)
    FROM deliveries
    WHERE status='completed'
")->fetchColumn();

/*
|--------------------------------------------------------------------------
| Featured / Latest Products
|--------------------------------------------------------------------------
*/

$sql = "
SELECT
    p.*,
    u.fullname AS farmer_name,
    u.lga,
    u.town
FROM products p
INNER JOIN users u
    ON u.id = p.farmer_id
WHERE
    p.status='approved'
";

$params = [];

if ($search !== '') {

    $sql .= "
    AND (
        p.product_name LIKE ?
        OR p.category LIKE ?
        OR p.description LIKE ?
    )
    ";

    $term = "%{$search}%";

    $params = [
        $term,
        $term,
        $term
    ];
}
if ($category !== '') {

    $sql .= "
        AND p.category = ?
    ";

    $params[] = $category;

}

$sql .= "
ORDER BY p.created_at DESC
LIMIT 8
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Featured Products
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
SELECT
    p.*,
    u.fullname AS farmer_name
FROM products p
INNER JOIN users u
    ON u.id = p.farmer_id
WHERE p.status = 'approved'
ORDER BY p.created_at DESC
LIMIT 4
");

$featuredProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Product Categories
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
SELECT DISTINCT category
FROM products
WHERE
    status='approved'
    AND category IS NOT NULL
    AND category <> ''
ORDER BY category
");

$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

/*
|--------------------------------------------------------------------------
| Featured Farmers
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
SELECT
    id,
    fullname,
    lga,
    town,
    created_at
FROM users
WHERE
    role='farmer'
    AND status='active'
ORDER BY created_at DESC
LIMIT 6
");

$farmers = $stmt->fetchAll(PDO::FETCH_ASSOC);