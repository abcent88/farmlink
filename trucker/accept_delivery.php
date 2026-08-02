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
| Load Approved Delivery
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
    ON o.id=d.order_id

JOIN products p
    ON p.id=o.product_id

WHERE
    d.id=?
AND
    d.status='approved'

LIMIT 1
");

$stmt->execute([$deliveryId]);

$delivery = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$delivery) {
    die('Delivery not found or already taken.');
}

/*
|--------------------------------------------------------------------------
| Accept Delivery
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
UPDATE deliveries
SET

    trucker_id=?,
    status='accepted'

WHERE id=?
");

$stmt->execute([

    $_SESSION['user_id'],
    $deliveryId

]);
/*
|--------------------------------------------------------------------------
| Keep Order Active
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    UPDATE orders
    SET status='accepted'
    WHERE id=?
");

$stmt->execute([
    $delivery['order_id']
]);

/*
|--------------------------------------------------------------------------
| Notify Farmer
|--------------------------------------------------------------------------
*/

notify(

    $pdo,

    $delivery['farmer_id'],

    'Delivery Accepted',

    'A trucker has accepted delivery for "' .

    $delivery['product_name'] .

    '".'

);

/*
|--------------------------------------------------------------------------
| Notify Buyer
|--------------------------------------------------------------------------
*/

notify(

    $pdo,

    $delivery['buyer_id'],

    'Delivery Accepted',

    'A trucker has accepted delivery of your order "' .

    $delivery['product_name'] .

    '".'

);

/*
|--------------------------------------------------------------------------
| Notify Trucker
|--------------------------------------------------------------------------
*/

notify(

    $pdo,

    $_SESSION['user_id'],

    'Delivery Assigned',

    'You have successfully accepted delivery of "' .

    $delivery['product_name'] .

    '".'

);

header("Location: my_deliveries.php?accepted=1");

exit;