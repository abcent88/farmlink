<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';

requireRole('super_admin');

/*
|--------------------------------------------------------------------------
| Total Platform Commissions
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT SUM(amount)
    FROM admin_commissions
");

$totalCommissions = $stmt->fetchColumn() ?: 0;

/*
|--------------------------------------------------------------------------
| Total Completed Orders
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT COUNT(*)
    FROM orders
    WHERE status='completed'
");

$totalOrders = $stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| Total Marketplace Revenue
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        SUM(o.quantity * p.price)
    FROM orders o
    JOIN products p
        ON o.product_id = p.id
    WHERE o.status='completed'
");

$totalRevenue = $stmt->fetchColumn() ?: 0;

/*
|--------------------------------------------------------------------------
| Total Products Sold
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT SUM(quantity)
    FROM orders
    WHERE status='completed'
");

$totalProductsSold = $stmt->fetchColumn() ?: 0;

/*
|--------------------------------------------------------------------------
| Top LGA Admins
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        u.fullname,
        SUM(ac.amount) AS total_commission
    FROM admin_commissions ac
    JOIN users u
        ON ac.admin_id = u.id
    GROUP BY ac.admin_id
    ORDER BY total_commission DESC
    LIMIT 5
");

$topAdmins = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Top Farmers
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        u.fullname,
        SUM(o.quantity * p.price) AS sales
    FROM orders o
    JOIN products p
        ON o.product_id = p.id
    JOIN users u
        ON p.farmer_id = u.id
    WHERE o.status='completed'
    GROUP BY u.id
    ORDER BY sales DESC
    LIMIT 5
");

$topFarmers = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">

<h2>Financial Dashboard</h2>

<div class="row mb-4">

<div class="col-md-3">
<div class="card shadow p-3 text-center">
<h4>₦<?= number_format($totalRevenue,2) ?></h4>
<p>Total Revenue</p>
</div>
</div>

<div class="col-md-3">
<div class="card shadow p-3 text-center">
<h4>₦<?= number_format($totalCommissions,2) ?></h4>
<p>Total Commissions</p>
</div>
</div>

<div class="col-md-3">
<div class="card shadow p-3 text-center">
<h4><?= number_format($totalProductsSold,2) ?></h4>
<p>Products Sold</p>
</div>
</div>

<div class="col-md-3">
<div class="card shadow p-3 text-center">
<h4><?= $totalOrders ?></h4>
<p>Completed Orders</p>
</div>
</div>

</div>

<div class="row">

<div class="col-md-6">

<div class="card shadow p-3">

<h4>Top LGA Admins</h4>

<table class="table table-bordered">

<tr>
<th>Name</th>
<th>Commission</th>
</tr>

<?php foreach($topAdmins as $admin): ?>

<tr>
<td><?= htmlspecialchars($admin['fullname']) ?></td>
<td>₦<?= number_format($admin['total_commission'],2) ?></td>
</tr>

<?php endforeach; ?>

</table>

</div>

</div>

<div class="col-md-6">

<div class="card shadow p-3">

<h4>Top Farmers</h4>

<table class="table table-bordered">

<tr>
<th>Farmer</th>
<th>Sales</th>
</tr>

<?php foreach($topFarmers as $farmer): ?>

<tr>
<td><?= htmlspecialchars($farmer['fullname']) ?></td>
<td>₦<?= number_format($farmer['sales'],2) ?></td>
</tr>

<?php endforeach; ?>

</table>

</div>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>