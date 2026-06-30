<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole('farmer');

$farmerId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Lifetime Earnings
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        SUM(o.quantity * p.price)
    FROM orders o
    JOIN products p
        ON o.product_id = p.id
    WHERE p.farmer_id = ?
    AND o.status = 'completed'
");

$stmt->execute([$farmerId]);

$totalEarnings = $stmt->fetchColumn() ?: 0;

/*
|--------------------------------------------------------------------------
| This Month Earnings
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        SUM(o.quantity * p.price)
    FROM orders o
    JOIN products p
        ON o.product_id = p.id
    WHERE p.farmer_id = ?
    AND o.status = 'completed'
    AND MONTH(o.created_at) = MONTH(CURRENT_DATE())
    AND YEAR(o.created_at) = YEAR(CURRENT_DATE())
");

$stmt->execute([$farmerId]);

$monthlyEarnings = $stmt->fetchColumn() ?: 0;

/*
|--------------------------------------------------------------------------
| Completed Orders
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM orders o
    JOIN products p
        ON o.product_id = p.id
    WHERE p.farmer_id = ?
    AND o.status='completed'
");

$stmt->execute([$farmerId]);

$totalOrders = $stmt->fetchColumn();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">

<h2>My Earnings</h2>

<div class="row">

<div class="col-md-4">
<div class="card shadow p-3 text-center">
<h3>₦<?= number_format($totalEarnings,2) ?></h3>
<p>Total Earnings</p>
</div>
</div>

<div class="col-md-4">
<div class="card shadow p-3 text-center">
<h3>₦<?= number_format($monthlyEarnings,2) ?></h3>
<p>This Month</p>
</div>
</div>

<div class="col-md-4">
<div class="card shadow p-3 text-center">
<h3><?= $totalOrders ?></h3>
<p>Completed Orders</p>
</div>
</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>