<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole('trucker');

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT order_id
    FROM deliveries
    WHERE id = ?
");

$stmt->execute([$id]);

$delivery = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$delivery) {
    die('Delivery not found');
}

$orderId = $delivery['order_id'];

$stmt = $pdo->prepare("
    SELECT
        o.quantity AS ordered_qty,
        o.product_id
    FROM orders o
    WHERE o.id = ?
");

$stmt->execute([$orderId]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die('Order not found');
}

/*
|--------------------------------------------------------------------------
| Complete Delivery
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    UPDATE deliveries
    SET status = 'completed'
    WHERE id = ?
");

$stmt->execute([$id]);

/*
|--------------------------------------------------------------------------
| Complete Order
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    UPDATE orders
    SET status = 'completed'
    WHERE id = ?
");

$stmt->execute([$orderId]);

/*
|--------------------------------------------------------------------------
| Reduce Product Stock
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    UPDATE products
    SET
        quantity = GREATEST(0, quantity - ?),
        status = CASE
            WHEN quantity - ? <= 0 THEN 'rejected'
            ELSE status
        END
    WHERE id = ?
");

$stmt->execute([
    $order['ordered_qty'],
    $order['ordered_qty'],
    $order['product_id']
]);
header("Location: my_deliveries.php");
exit;
?>