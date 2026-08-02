<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../includes/notify.php';

requireRole('farmer');

$orderId = (int)($_GET['id'] ?? 0);

if ($orderId <= 0) {
    die('Invalid order.');
}

/*
|--------------------------------------------------------------------------
| Load Order
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        o.*,
        p.product_name,
        p.quantity AS stock
    FROM orders o
    INNER JOIN products p
        ON p.id = o.product_id
    WHERE
        o.id = ?
    AND
        o.farmer_id = ?
    LIMIT 1
");

$stmt->execute([
    $orderId,
    $_SESSION['user_id']
]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die('Order not found.');
}

/*
|--------------------------------------------------------------------------
| Prevent Duplicate Processing
|--------------------------------------------------------------------------
*/

if ($order['status'] !== 'pending') {
    die('This order has already been processed.');
}

/*
|--------------------------------------------------------------------------
| Check Available Stock
|--------------------------------------------------------------------------
*/

if ($order['quantity'] > $order['stock']) {
    die('Not enough stock available.');
}

try {

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Farmer Approves Order
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE orders
        SET status='farmer_approved'
        WHERE id=?
    ");

    $stmt->execute([$orderId]);

    /*
    |--------------------------------------------------------------------------
    | Reduce Product Stock
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE products
        SET quantity = quantity - ?
        WHERE id = ?
    ");

    $stmt->execute([
        $order['quantity'],
        $order['product_id']
    ]);

    /*
    |--------------------------------------------------------------------------
    | Create Delivery Record
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO deliveries
        (
            order_id,
            trucker_id,
            status
        )
        VALUES
        (
            ?,
            NULL,
            'assigned'
        )
    ");

    $stmt->execute([$orderId]);

    /*
    |--------------------------------------------------------------------------
    | Notify Buyer
    |--------------------------------------------------------------------------
    */

    notify(
        $pdo,
        $order['buyer_id'],
        'Order Approved',
        'Your order for "' .
        $order['product_name'] .
        '" has been approved by the farmer and is awaiting LGA approval.'
    );

    /*
    |--------------------------------------------------------------------------
    | Notify LGA Admin(s)
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT u.id
        FROM users u
        INNER JOIN users f
            ON f.id = ?
        WHERE
            u.role='lga_admin'
        AND
            u.status='active'
        AND
            u.lga=f.lga
    ");

    $stmt->execute([
        $order['farmer_id']
    ]);

    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($admins as $admin) {

        notify(
            $pdo,
            $admin['id'],
            'Order Awaiting Approval',
            'A new order for "' .
            $order['product_name'] .
            '" requires your approval.'
        );

    }

    $pdo->commit();

    header("Location: orders.php?success=accepted");
    exit;

} catch (Exception $e) {

    $pdo->rollBack();
    die($e->getMessage());

}