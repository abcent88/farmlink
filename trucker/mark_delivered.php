<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../includes/notify.php';

requireRole('trucker');

$deliveryId = (int)($_GET['id'] ?? 0);

if ($deliveryId <= 0) {
    die('Invalid delivery.');
}

/*
|--------------------------------------------------------------------------
| Load Delivery
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT

    d.id,
    d.status,
    d.order_id,

    o.buyer_id,
    o.farmer_id,

    p.product_name

FROM deliveries d

JOIN orders o
    ON o.id = d.order_id

JOIN products p
    ON p.id = o.product_id

WHERE
    d.id = ?
AND
    d.trucker_id = ?

LIMIT 1
");

$stmt->execute([
    $deliveryId,
    $_SESSION['user_id']
]);

$delivery = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$delivery) {
    die('Delivery not found.');
}

if ($delivery['status'] !== 'in_transit') {
    die('Only deliveries in transit can be marked as delivered.');
}

try {

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Mark Delivery as Delivered
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE deliveries
        SET status='delivered'
        WHERE id=?
    ");

    $stmt->execute([$deliveryId]);
    /*
|--------------------------------------------------------------------------
| Update Order Status
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    UPDATE orders
    SET status='delivered'
    WHERE id=?
");

$stmt->execute([
    $delivery['order_id']
]);

    /*
    |--------------------------------------------------------------------------
    | Notify Buyer
    |--------------------------------------------------------------------------
    */

    notify(
        $pdo,
        $delivery['buyer_id'],
        'Delivery Arrived',
        'Your order for "' .
        $delivery['product_name'] .
        '" has arrived. Please confirm receipt.'
    );

    /*
    |--------------------------------------------------------------------------
    | Notify Farmer
    |--------------------------------------------------------------------------
    */

    notify(
        $pdo,
        $delivery['farmer_id'],
        'Delivery Arrived',
        'The trucker has successfully delivered "' .
        $delivery['product_name'] .
        '" to the buyer.'
    );

    /*
    |--------------------------------------------------------------------------
    | Notify Trucker
    |--------------------------------------------------------------------------
    */

    notify(
        $pdo,
        $_SESSION['user_id'],
        'Delivery Marked as Delivered',
        'You have successfully marked "' .
        $delivery['product_name'] .
        '" as delivered.'
    );

    $pdo->commit();

    header("Location: my_deliveries.php?delivered=1");
    exit;

} catch (Exception $e) {

    $pdo->rollBack();

    die($e->getMessage());

}