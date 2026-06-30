<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/csrf.php';
require_once '../includes/audit.php';

verify_csrf();

$id = (int)$_POST['id'];

$stmt = $pdo->prepare("
    UPDATE users
    SET status='active'
    WHERE id=?
");

$stmt->execute([$id]);

logAction(
    $pdo,
    $_SESSION['user_id'],
    'Approved User',
    $id
);

header("Location: users.php");
exit();