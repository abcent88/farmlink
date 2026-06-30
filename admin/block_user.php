<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';

requireRole('super_admin');

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: lga_admin/list.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Toggle Active / Blocked
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
UPDATE users
SET status =
CASE
    WHEN status='active'
    THEN 'blocked'
    ELSE 'active'
END
WHERE id = ?
");

$stmt->execute([$id]);

header("Location: lga_admin/list.php");
exit;