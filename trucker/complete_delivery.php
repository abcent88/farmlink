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

if ($delivery['status'] !== 'accepted' && $delivery['status'] !== 'in_transit') {
    die('This delivery cannot be completed.');
}

try {

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Complete Delivery
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE deliveries
        SET status='completed'
        WHERE id=?
    ");

    $stmt->execute([$deliveryId]);

    /*
    |--------------------------------------------------------------------------
    | Complete Order
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE orders
        SET status='completed'
        WHERE id=?
    ");

    $stmt->execute([
        $delivery['order_id']
    ]);
    $stmt->execute([$orderId]);

    /*
    |--------------------------------------------------------------------------
    | Notify Buyer
    |--------------------------------------------------------------------------
    */

    notify(

        $pdo,

        $delivery['buyer_id'],

        'Order Delivered',

        'Your order for "' .
        $delivery['product_name'] .
        '" has been delivered successfully.'

    );

    /*
    |--------------------------------------------------------------------------
    | Notify Farmer
    |--------------------------------------------------------------------------
    */

    notify(

        $pdo,

        $delivery['farmer_id'],

        'Delivery Completed',

        'Delivery of "' .
        $delivery['product_name'] .
        '" has been completed successfully.'

    );

    /*
    |--------------------------------------------------------------------------
    | Notify Trucker
    |--------------------------------------------------------------------------
    */

    notify(

        $pdo,

        $_SESSION['user_id'],

        'Delivery Completed',

        'You have successfully completed the delivery of "' .
        $delivery['product_name'] .
        '".'

    );

    $pdo->commit();

    header("Location: my_deliveries.php?completed=1");
    exit;

} catch (Exception $e) {

    $pdo->rollBack();

    die($e->getMessage());

}