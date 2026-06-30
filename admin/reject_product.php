<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole('super_admin');

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("
    UPDATE products
    SET status='rejected'
    WHERE id=?
");

$stmt->execute([$id]);

header('Location: products.php');
exit;