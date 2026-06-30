<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';

requireRole('lga_admin');

$orderId = $_GET['order_id'] ?? null;

if (!$orderId) {
    header("Location: orders.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Fetch Order Details
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        o.id,
        o.quantity,
        o.status,
        p.price,
        u.commission_rate
    FROM orders o
    JOIN products p
        ON o.product_id = p.id
    JOIN users u
        ON u.id = ?
    WHERE o.id = ?
");

$stmt->execute([
    $_SESSION['user_id'],
    $orderId
]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: orders.php?error=order_not_found");
    exit;
}

/*
|--------------------------------------------------------------------------
| Prevent duplicate commission
|--------------------------------------------------------------------------
*/

$check = $pdo->prepare("
    SELECT id
    FROM admin_commissions
    WHERE order_id = ?
");

$check->execute([$orderId]);

if ($check->fetch()) {
    header("Location: orders.php?already_processed=1");
    exit;
}

/*
|--------------------------------------------------------------------------
| Approve Order
|--------------------------------------------------------------------------
*/

$update = $pdo->prepare("
    UPDATE orders
    SET status='accepted'
    WHERE id = ?
");

$update->execute([$orderId]);

/*
|--------------------------------------------------------------------------
| Calculate Commission
|--------------------------------------------------------------------------
*/

$orderValue = $order['quantity'] * $order['price'];

$commission = $orderValue *
              ($order['commission_rate'] / 100);

/*
|--------------------------------------------------------------------------
| Save Commission
|--------------------------------------------------------------------------
*/

$insert = $pdo->prepare("
    INSERT INTO admin_commissions
    (
        admin_id,
        order_id,
        amount
    )
    VALUES
    (
        ?,
        ?,
        ?
    )
");

$insert->execute([
    $_SESSION['user_id'],
    $orderId,
    $commission
]);

header("Location: lga_admin/orders.php?approved=1");
exit;