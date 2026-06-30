<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole('farmer');

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    UPDATE orders
    SET status = 'rejected'
    WHERE id = ?
");

$stmt->execute([$id]);

header("Location: orders.php");
exit;
?>