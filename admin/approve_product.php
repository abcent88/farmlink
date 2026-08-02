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
    u.lga,
    fp.verification_status
FROM products p
JOIN users u
    ON p.farmer_id = u.id
LEFT JOIN farmer_profiles fp
    ON fp.user_id = u.id
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
| Already Approved?
|--------------------------------------------------------------------------
*/

if ($product['status'] !== 'pending') {
    header('Location: products.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Farmer Verified?
|--------------------------------------------------------------------------
*/

if ($product['verification_status'] !== 'verified') {
    die('Farmer must be verified before approving products.');
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
| Approve Product
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
UPDATE products
SET
    status='approved',
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

    'Product Approved',

    'Your product "' .
    $product['product_name'] .
    '" has been approved and is now visible in the marketplace.'

);

header('Location: products.php?success=approved');

exit;