<?php

require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/roles.php';
require_once '../../includes/notify.php';

requireRole('lga_admin');

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
        d.order_id,
        d.status,

        o.buyer_id,
        o.farmer_id,

        p.product_name

    FROM deliveries d

    INNER JOIN orders o
        ON o.id = d.order_id

    INNER JOIN products p
        ON p.id = o.product_id

    WHERE d.id = ?

    LIMIT 1
");

$stmt->execute([$deliveryId]);

$delivery = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$delivery) {
    die('Delivery not found.');
}

if ($delivery['status'] !== 'assigned') {
    die('This delivery has already been processed.');
}

try {

    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | Approve Delivery
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE deliveries
        SET status = 'accepted'
        WHERE id = ?
    ");

    $stmt->execute([$deliveryId]);

    /*
    |--------------------------------------------------------------------------
    | Synchronize Order Status
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE orders
        SET status = 'accepted'
        WHERE id = ?
    ");

    $stmt->execute([$delivery['order_id']]);

    /*
    |--------------------------------------------------------------------------
    | Notify Farmer
    |--------------------------------------------------------------------------
    */

    notify(
        $pdo,
        $delivery['farmer_id'],
        'Delivery Approved',
        'Your order "' .
        $delivery['product_name'] .
        '" has been approved by the LGA Administrator.'
    );

    /*
    |--------------------------------------------------------------------------
    | Notify Buyer
    |--------------------------------------------------------------------------
    */

    notify(
        $pdo,
        $delivery['buyer_id'],
        'Delivery Approved',
        'Your order "' .
        $delivery['product_name'] .
        '" has been approved and is now available for truckers.'
    );

    /*
    |--------------------------------------------------------------------------
    | Notify All Active Truckers
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->query("
        SELECT id
        FROM users
        WHERE role = 'trucker'
        AND status = 'active'
    ");

    $truckers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($truckers as $trucker) {

        notify(
            $pdo,
            $trucker['id'],
            'New Delivery Available',
            'A new delivery for "' .
            $delivery['product_name'] .
            '" is now available for acceptance.'
        );

    }

    $pdo->commit();

    header("Location: orders.php?approved=1");
    exit;

} catch (Exception $e) {

    $pdo->rollBack();

    die($e->getMessage());

}