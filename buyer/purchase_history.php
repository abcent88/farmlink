<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole('buyer');

$buyerId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Buyer Statistics
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM orders
    WHERE buyer_id = ?
");
$stmt->execute([$buyerId]);
$totalOrders = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM orders
    WHERE buyer_id = ?
    AND status = 'completed'
");
$stmt->execute([$buyerId]);
$completedOrders = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM orders
    WHERE buyer_id = ?
    AND status IN ('pending','farmer_approved','accepted')
");
$stmt->execute([$buyerId]);
$pendingOrders = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT
        SUM(o.quantity * p.price)
    FROM orders o
    JOIN products p
        ON o.product_id = p.id
    WHERE o.buyer_id = ?
    AND o.status = 'completed'
");
$stmt->execute([$buyerId]);
$totalSpent = $stmt->fetchColumn() ?: 0;

/*
|--------------------------------------------------------------------------
| Order History
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        o.id,
        o.quantity,
        o.status,
        o.created_at,
        p.product_name,
        p.price,
        u.fullname AS farmer_name
    FROM orders o
    JOIN products p
        ON o.product_id = p.id
    JOIN users u
        ON p.farmer_id = u.id
    WHERE o.buyer_id = ?
    ORDER BY o.id DESC
");
$stmt->execute([$buyerId]);

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">

<h2>Purchase History</h2>

<div class="row mb-4">

    <div class="col-md-3">
        <div class="card shadow p-3 text-center">
            <h4><?= $totalOrders ?></h4>
            <p>Total Orders</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow p-3 text-center">
            <h4><?= $completedOrders ?></h4>
            <p>Completed</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow p-3 text-center">
            <h4><?= $pendingOrders ?></h4>
            <p>Pending</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow p-3 text-center">
            <h4>₦<?= number_format($totalSpent,2) ?></h4>
            <p>Total Spent</p>
        </div>
    </div>

</div>

<div class="card shadow p-3">

<h4>Order History</h4>

<table class="table table-bordered">

<thead>
<tr>
    <th>ID</th>
    <th>Product</th>
    <th>Farmer</th>
    <th>Quantity</th>
    <th>Value</th>
    <th>Status</th>
    <th>Date</th>
</tr>
</thead>

<tbody>

<?php foreach($orders as $order): ?>

<tr>

<td><?= $order['id'] ?></td>

<td><?= htmlspecialchars($order['product_name']) ?></td>

<td><?= htmlspecialchars($order['farmer_name']) ?></td>

<td><?= $order['quantity'] ?></td>

<td>
₦<?= number_format($order['quantity'] * $order['price'],2) ?>
</td>

<td>
<?= htmlspecialchars($order['status']) ?>
</td>

<td><?= $order['created_at'] ?></td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

<?php include '../includes/footer.php'; ?>