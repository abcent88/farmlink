<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../includes/csrf.php';
require_once '../includes/notify.php';

requireRole(['super_admin','lga_admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: products.php');
    exit;
}

verify_csrf();

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    header('Location: products.php');
    exit;
}

$userId = $_SESSION['user_id'];
$role   = $_SESSION['role'];

/*
|--------------------------------------------------------------------------
| Load Product
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT
    p.id,
    p.status,
    p.product_name,
    p.farmer_id,
    u.lga
FROM products p
JOIN users u
    ON p.farmer_id = u.id
WHERE p.id=?
LIMIT 1
");

$stmt->execute([$id]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die('Product not found.');
}

/*
|--------------------------------------------------------------------------
| Already Processed?
|--------------------------------------------------------------------------
*/

if ($product['status'] !== 'pending') {
    header('Location: products.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| LGA Restriction
|--------------------------------------------------------------------------
*/

if ($role === 'lga_admin') {

    $stmt = $pdo->prepare("
    SELECT lga
    FROM users
    WHERE id=?
    ");

    $stmt->execute([$userId]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin['lga'] !== $product['lga']) {
        die('Unauthorized.');
    }
}

/*
|--------------------------------------------------------------------------
| Reject Product
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
UPDATE products
SET
    status='rejected',
    approved_by=?
WHERE id=?
");

$stmt->execute([
    $userId,
    $id
]);

/*
|--------------------------------------------------------------------------
| Notify Farmer
|--------------------------------------------------------------------------
*/

notify(

    $pdo,

    $product['farmer_id'],

    'Product Rejected',

    'Your product "' .
    $product['product_name'] .
    '" was rejected. Please review the product information and submit it again.'

);

header('Location: products.php?success=rejected');

exit;