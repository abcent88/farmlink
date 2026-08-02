<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../includes/notify.php';

requireRole('buyer');

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
SELECT

d.id,
d.status,

o.id AS order_id,
o.buyer_id,
o.farmer_id,

d.trucker_id,

p.product_name

FROM deliveries d

JOIN orders o
ON d.order_id=o.id

JOIN products p
ON o.product_id=p.id

WHERE

d.id=?

AND o.buyer_id=?

LIMIT 1
");

$stmt->execute([
    $id,
    $_SESSION['user_id']
]);

$delivery = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$delivery){

    die('Delivery not found.');

}

if($delivery['status']!='delivered'){

    die('Delivery has not been marked delivered.');

}

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

$stmt->execute([$id]);

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

/*
|--------------------------------------------------------------------------
| Notify Farmer
|--------------------------------------------------------------------------
*/

notify(

    $pdo,

    $delivery['farmer_id'],

    'Order Completed',

    'The buyer has confirmed delivery of "' .
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

    $delivery['trucker_id'],

    'Delivery Confirmed',

    'The buyer has confirmed successful delivery of "' .
    $delivery['product_name'] .
    '".'

);

header("Location: orders.php?completed=1");

exit;