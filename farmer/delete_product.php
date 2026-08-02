<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../includes/csrf.php';
require_once '../includes/error_handler.php';

requireRole('farmer');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: products.php');
    exit;

}

verify_csrf();

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {

    appFail('Invalid product.');

}

/*
|--------------------------------------------------------------------------
| Load Product
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT
    id,
    image,
    status
FROM products
WHERE id=?
AND farmer_id=?
LIMIT 1
");

$stmt->execute([

    $id,

    $_SESSION['user_id']

]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {

    appFail('Product not found.');

}

/*
|--------------------------------------------------------------------------
| Prevent deleting approved products
|--------------------------------------------------------------------------
*/

if ($product['status'] === 'approved') {

    appFail('Approved products cannot be deleted.');

}

/*
|--------------------------------------------------------------------------
| Delete Image
|--------------------------------------------------------------------------
*/

if (!empty($product['image'])) {

    $file = '../uploads/products/' . $product['image'];

    if (file_exists($file)) {

        unlink($file);

    }

}

/*
|--------------------------------------------------------------------------
| Delete Product
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
DELETE
FROM products
WHERE id=?
");

$stmt->execute([$id]);

header('Location: products.php?success=deleted');

exit;