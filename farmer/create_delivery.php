<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole('farmer');

$orderId = (int)($_GET['order_id'] ?? 0);

if (!$orderId) {
    appError(
$e->getMessage()
);

appFail();
}

/*
|--------------------------------------------------------------------------
| Fetch Order
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT
    o.id,
    o.quantity,
    o.status,
    p.product_name
FROM orders o
JOIN products p
    ON p.id = o.product_id
WHERE o.id = ?
");

$stmt->execute([$orderId]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    appError(
$e->getMessage()
);

appFail();
}

if ($order['status'] !== 'accepted') {
   appError(
$e->getMessage()
);

appFail('Order must be approved by LGA before assigning truck.');
}

/*
|--------------------------------------------------------------------------
| Prevent Duplicate Delivery
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT id
FROM deliveries
WHERE order_id = ?
");

$stmt->execute([$orderId]);

if ($stmt->fetch()) {

    include '../includes/header.php';

    echo "
    <div class='container mt-5'>
        <div class='alert alert-info'>
            Delivery already assigned.
        </div>

        <a href='orders.php'
           class='btn btn-primary'>
           Back
        </a>
    </div>
    ";

    include '../includes/footer.php';

    exit;
}

/*
|--------------------------------------------------------------------------
| Fetch Available Truckers
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT
    id,
    fullname,
    truck_type,
    truck_capacity
FROM users
WHERE role='trucker'
AND status='active'
AND truck_capacity IS NOT NULL
AND truck_capacity >= ?
ORDER BY truck_capacity ASC
");

$stmt->execute([
    $order['quantity']
]);

$truckers = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Create Delivery
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $truckerId = $_POST['trucker_id'];

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
            ?,
            'open'
        )
    ");

    $stmt->execute([
        $orderId,
        $truckerId
    ]);

    header("Location: orders.php");

    exit;
}

include '../includes/header.php';
include '../includes/navbar.php';

?>

<div class="container mt-5">

<div class="card shadow">

<div class="card-body">

<h2>Assign Delivery</h2>

<hr>

<p>
<b>Order:</b>
#<?= $order['id'] ?>
</p>

<p>
<b>Product:</b>
<?= htmlspecialchars($order['product_name']) ?>
</p>

<p>
<b>Quantity:</b>
<?= $order['quantity'] ?> Tonnes
</p>

<?php if(count($truckers)>0): ?>

<form method="POST">

<label>
Choose Trucker
</label>

<select
name="trucker_id"
class="form-control"
required>

<option value="">
Select Trucker
</option>

<?php foreach($truckers as $trucker): ?>

<option
value="<?= $trucker['id'] ?>">

<?= htmlspecialchars($trucker['fullname']) ?>

—

<?= htmlspecialchars($trucker['truck_type']) ?>

—

<?= $trucker['truck_capacity'] ?>

 Tonnes

</option>

<?php endforeach; ?>

</select>

<br>

<button
class="btn btn-success">

Assign Delivery

</button>

</form>

<?php else: ?>

<div class="alert alert-warning">

No truck available for this quantity.

</div>

<?php endif; ?>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>