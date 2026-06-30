<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole('trucker');

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    UPDATE deliveries
    SET
        trucker_id = ?,
        status = 'accepted'
    WHERE id = ?
");

$stmt->execute([
    $_SESSION['user_id'],
    $id
]);

header("Location: my_deliveries.php");
exit;
?>