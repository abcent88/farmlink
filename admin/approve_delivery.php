<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../includes/csrf.php';

requireRole(['super_admin','lga_admin']);

verify_csrf();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: deliveries.php');
    exit;

}

$deliveryId = (int)($_POST['delivery_id'] ?? 0);

if ($deliveryId <= 0) {

    header('Location: deliveries.php');
    exit;

}

/*
|--------------------------------------------------------------------------
| Verify Delivery Exists
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT
id,
status
FROM deliveries
WHERE id=?
");

$stmt->execute([$deliveryId]);

$delivery = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$delivery) {

    header('Location: deliveries.php');
    exit;

}

/*
|--------------------------------------------------------------------------
| Already Approved?
|--------------------------------------------------------------------------
*/

if ($delivery['status'] != 'assigned') {

    header('Location: deliveries.php');
    exit;

}

/*
|--------------------------------------------------------------------------
| Approve Delivery
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
UPDATE deliveries
SET status='approved'
WHERE id=?
");

$stmt->execute([$deliveryId]);

header("Location: deliveries.php?success=approved");

exit;