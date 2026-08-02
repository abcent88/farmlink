<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../includes/csrf.php';

requireRole('super_admin');

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {

    header("Location: categories.php");
    exit;

}

/*
|--------------------------------------------------------------------------
| Load Category
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("

SELECT

id,
category_name

FROM product_categories

WHERE id=?

LIMIT 1

");

$stmt->execute([$id]);

$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {

    header("Location: categories.php");
    exit;

}

/*
|--------------------------------------------------------------------------
| Check Usage
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("

SELECT COUNT(*) 

FROM products

WHERE category=?

");

$stmt->execute([

$category['category_name']

]);

$totalProducts = $stmt->fetchColumn();

if ($totalProducts > 0) {

    header("Location: categories.php?error=inuse");
    exit;

}

/*
|--------------------------------------------------------------------------
| Delete Category
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("

DELETE

FROM product_categories

WHERE id=?

");

$stmt->execute([$id]);

header("Location: categories.php?success=deleted");

exit;