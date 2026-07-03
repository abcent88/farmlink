<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../includes/notify.php';


requireRole('super_admin');

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("
    UPDATE products
    SET status='approved'
    WHERE id=?
");
notify(

$pdo,

$farmerUserId,

'Product Approved',

'Your product is now visible in the marketplace.'

);

$stmt->execute([$id]);

header('Location: products.php');
exit;