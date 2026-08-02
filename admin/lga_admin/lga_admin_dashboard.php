<?php

require_once '../../includes/auth.php';
require_once '../../includes/roles.php';
require_once '../../config/database.php';

requireRole('lga_admin');

$adminId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Admin Information
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT lga, town
FROM users
WHERE id=?
");

$stmt->execute([$adminId]);

$admin = $stmt->fetch(PDO::FETCH_ASSOC);

$lga = $admin['lga'] ?? '';

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

// Farmers
$stmt = $pdo->prepare("
SELECT COUNT(*)
FROM users
WHERE role='farmer'
AND lga=?
");
$stmt->execute([$lga]);
$totalFarmers = $stmt->fetchColumn();

// Products
$stmt = $pdo->prepare("
SELECT COUNT(*)
FROM products p
JOIN users u
ON p.farmer_id=u.id
WHERE u.lga=?
");
$stmt->execute([$lga]);
$totalProducts = $stmt->fetchColumn();

// Orders
$stmt = $pdo->prepare("
SELECT COUNT(*)
FROM orders o
JOIN products p
ON o.product_id=p.id
JOIN users u
ON p.farmer_id=u.id
WHERE u.lga=?
");
$stmt->execute([$lga]);
$totalOrders = $stmt->fetchColumn();

// Commission
$stmt = $pdo->prepare("
SELECT COALESCE(SUM(amount),0)
FROM admin_commissions
WHERE admin_id=?
");
$stmt->execute([$adminId]);
$totalCommission = $stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| Verification Statistics
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT COUNT(*)
FROM farmer_profiles fp
JOIN users u
ON fp.user_id=u.id
WHERE fp.verification_status='pending'
AND u.lga=?
");
$stmt->execute([$lga]);
$pendingVerification = $stmt->fetchColumn();

$stmt = $pdo->prepare("
SELECT COUNT(*)
FROM farmer_profiles fp
JOIN users u
ON fp.user_id=u.id
WHERE fp.verification_status='verified'
AND u.lga=?
");
$stmt->execute([$lga]);
$verifiedFarmers = $stmt->fetchColumn();

$stmt = $pdo->prepare("
SELECT COUNT(*)
FROM farmer_profiles fp
JOIN users u
ON fp.user_id=u.id
WHERE fp.verification_status='rejected'
AND u.lga=?
");
$stmt->execute([$lga]);
$rejectedFarmers = $stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| Recent Orders
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
ON o.product_id=p.id
JOIN users u
ON p.farmer_id=u.id
WHERE u.lga=?
ORDER BY o.id DESC
LIMIT 5
");

$stmt->execute([$lga]);

$recentOrders = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| Recent Pending Verifications
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT
u.id,
u.fullname,
u.phone,
u.town,
fp.farm_name
FROM farmer_profiles fp
JOIN users u
ON fp.user_id=u.id
WHERE fp.verification_status='pending'
AND u.lga=?
ORDER BY fp.id DESC
LIMIT 5
");

$stmt->execute([$lga]);

$pendingFarmers = $stmt->fetchAll();

include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container py-4">

<h2 class="mb-3">

LGA Admin Dashboard

</h2>

<p>

Welcome,

<strong><?= htmlspecialchars($_SESSION['fullname']) ?></strong>

</p>

<p>

LGA:

<strong><?= htmlspecialchars($lga) ?></strong>

</p>

<div class="row g-3 mb-4">

<div class="col-md-3">

<div class="card shadow text-center">

<div class="card-body">

<h1>👨‍🌾</h1>

<h3><?= $totalFarmers ?></h3>

<p>Total Farmers</p>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card shadow text-center">

<div class="card-body">

<h1>🌾</h1>

<h3><?= $totalProducts ?></h3>

<p>Products</p>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card shadow text-center">

<div class="card-body">

<h1>📦</h1>

<h3><?= $totalOrders ?></h3>

<p>Orders</p>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card bg-success text-white shadow text-center">

<div class="card-body">

<h1>💰</h1>

<h4>₦<?= number_format($totalCommission,2) ?></h4>

<p>Commission</p>

</div>

</div>

</div>

</div>

<hr>

<h4 class="mb-3">

Farmer Verification

</h4>

<div class="row g-3 mb-4">

<div class="col-md-4">

<div class="card bg-warning shadow text-center">

<div class="card-body">

<h2><?= $pendingVerification ?></h2>

<p>Pending Verification</p>

<a href="farmer_verifications.php"
class="btn btn-dark btn-sm">

Review

</a>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card bg-success text-white shadow text-center">

<div class="card-body">

<h2><?= $verifiedFarmers ?></h2>

<p>Verified Farmers</p>

<a href="verified_farmers.php"
class="btn btn-light btn-sm">

View

</a>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card bg-danger text-white shadow text-center">

<div class="card-body">

<h2><?= $rejectedFarmers ?></h2>

<p>Rejected Farmers</p>

<a href="rejected_farmers.php"
class="btn btn-light btn-sm">

View

</a>

</div>

</div>

</div>

</div>

<hr>

<h4 class="mb-3">

Quick Actions

</h4>

<div class="d-flex flex-wrap gap-2 mb-5">

<a href="farmer_verifications.php" class="btn btn-warning">

Pending Verification

</a>

<a href="verified_farmers.php" class="btn btn-success">

Verified Farmers

</a>

<a href="rejected_farmers.php" class="btn btn-danger">

Rejected Farmers

</a>

<a href="orders.php" class="btn btn-primary">

Orders

</a>

<a href="commissions.php" class="btn btn-info">

Commissions

</a>

<a href="../users.php" class="btn btn-secondary">

Users

</a>

</div>

<div class="row">

<div class="col-lg-6">

<div class="card shadow mb-4">

<div class="card-header">

Recent Pending Verification

</div>

<div class="card-body">

<?php if(empty($pendingFarmers)): ?>

<div class="alert alert-success mb-0">

No pending verification requests.

</div>

<?php else: ?>

<table class="table table-sm">

<thead>

<tr>

<th>Farmer</th>

<th>Farm</th>

<th></th>

</tr>

</thead>

<tbody>

<?php foreach($pendingFarmers as $farmer): ?>

<tr>

<td><?= htmlspecialchars($farmer['fullname']) ?></td>

<td><?= htmlspecialchars($farmer['farm_name']) ?></td>

<td>

<a
href="review_farmer.php?id=<?= $farmer['id'] ?>"
class="btn btn-warning btn-sm">

Review

</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<?php endif; ?>

</div>

</div>

</div>

<div class="col-lg-6">

<div class="card shadow">

<div class="card-header">

Recent Orders

</div>

<div class="card-body">

<table class="table table-sm">

<thead>

<tr>

<th>ID</th>

<th>Product</th>

<th>Qty</th>

<th>Status</th>

</tr>

</thead>

<tbody>

<?php foreach($recentOrders as $order): ?>

<tr>

<td><?= $order['id'] ?></td>

<td><?= htmlspecialchars($order['product_name']) ?></td>

<td><?= $order['quantity'] ?></td>

<td>

<span class="badge bg-secondary">

<?= ucfirst($order['status']) ?>

</span>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

</div>

<?php include '../../includes/footer.php'; ?>