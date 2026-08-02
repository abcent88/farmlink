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
| Get Delivery
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT
id,
status,
order_id
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
| Only Delivered Orders Can Be Completed
|--------------------------------------------------------------------------
*/

if ($delivery['status'] != 'delivered') {

    header('Location: deliveries.php');
    exit;

}

/*
|--------------------------------------------------------------------------
| Complete Delivery
|--------------------------------------------------------------------------
*/

$pdo->beginTransaction();

try{

    /*
    Update delivery
    */

    $stmt = $pdo->prepare("
    UPDATE deliveries
    SET status='completed'
    WHERE id=?
    ");

    $stmt->execute([$deliveryId]);

    /*
    Update order
    */

    $stmt = $pdo->prepare("
    UPDATE orders
    SET status='completed'
    WHERE id=?
    ");

    $stmt->execute([$delivery['order_id']]);

    $pdo->commit();

}catch(Exception $e){

    $pdo->rollBack();

    die($e->getMessage());

}

header("Location: deliveries.php?success=completed");

exit;