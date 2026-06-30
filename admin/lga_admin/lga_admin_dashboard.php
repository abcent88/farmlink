<?php

require_once '../../includes/auth.php';
require_once '../../includes/roles.php';
require_once '../../config/database.php';

requireRole('lga_admin');

$adminId=$_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Admin Location
|--------------------------------------------------------------------------
*/

$stmt=$pdo->prepare("
SELECT lga,town
FROM users
WHERE id=?
");

$stmt->execute([$adminId]);

$admin=$stmt->fetch(PDO::FETCH_ASSOC);

$lga=$admin['lga']??'';
$town=$admin['town']??'';

/*
|--------------------------------------------------------------------------
| Stats
|--------------------------------------------------------------------------
*/

$farmers=$pdo->prepare("
SELECT COUNT(*)
FROM users
WHERE role='farmer'
AND lga=?
");

$farmers->execute([$lga]);

$totalFarmers=
$farmers->fetchColumn();

$products=$pdo->prepare("
SELECT COUNT(*)
FROM products p
JOIN users u
ON p.farmer_id=u.id
WHERE u.lga=?
");

$products->execute([$lga]);

$totalProducts=
$products->fetchColumn();

$orders=$pdo->prepare("
SELECT COUNT(*)
FROM orders o
JOIN products p
ON o.product_id=p.id
JOIN users u
ON p.farmer_id=u.id
WHERE u.lga=?
AND o.status
IN(
'accepted',
'completed'
)
");

$orders->execute([$lga]);

$totalOrders=
$orders->fetchColumn();

$commission=
$pdo->prepare("
SELECT SUM(amount)
FROM admin_commissions
WHERE admin_id=?
");

$commission->execute([
$adminId
]);

$totalCommission=
$commission->fetchColumn();

$totalCommission=
$totalCommission?:0;

/*
|--------------------------------------------------------------------------
| Recent Orders
|--------------------------------------------------------------------------
*/

$recent=
$pdo->prepare("
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

$recent->execute([
$lga
]);

$recentOrders=
$recent->fetchAll();

include '../../includes/header.php';
include '../../includes/navbar.php';

?>

<style>

.quick-grid{

display:grid;

grid-template-columns:
repeat(
auto-fit,
minmax(
150px,
1fr
));

gap:15px;

}

.quick-card{

padding:20px;

border-radius:20px;

text-align:center;

background:
var(--nav-card);

box-shadow:
0 3px 12px
rgba(
0,
0,
0,
0.12
);

text-decoration:none;

color:
var(--nav-text);

}

.quick-card:hover{

transform:
translateY(-4px);

}

.quick-card span{

font-size:34px;

display:block;

margin-bottom:10px;

}

</style>

<div class="container mt-4">

<h2>

🗺️ LGA Dashboard

</h2>

<p>

Welcome

<?= htmlspecialchars(
$_SESSION['fullname']
) ?>

</p>

<p>

LGA:

<b>

<?= htmlspecialchars(
$lga
) ?>

</b>

</p>

<div class="row text-center mb-4">

<div class="col-md-3">

<div class="card shadow p-3">

<h2>

👨‍🌾

</h2>

<h3>

<?= $totalFarmers ?>

</h3>

<p>

Farmers

</p>

</div>

</div>

<div class="col-md-3">

<div class="card shadow p-3">

<h2>

🌾

</h2>

<h3>

<?= $totalProducts ?>

</h3>

<p>

Products

</p>

</div>

</div>

<div class="col-md-3">

<div class="card shadow p-3">

<h2>

📦

</h2>

<h3>

<?= $totalOrders ?>

</h3>

<p>

Orders

</p>

</div>

</div>

<div class="col-md-3">

<div
class="card shadow p-3 bg-success text-white">

<h2>

💰

</h2>

<h4>

₦<?= number_format(
$totalCommission,
2
) ?>

</h4>

<p>

Commission

</p>

</div>

</div>

</div>

<h4>

Quick Actions

</h4>

<div class="quick-grid mb-5">

<a
class="quick-card"
href="orders.php">

<span>

📦

</span>

Review Orders

</a>

<a
class="quick-card"
href="commissions.php">

<span>

💵

</span>

Commissions

</a>

<a
class="quick-card"
href="../users.php">

<span>

👥

</span>

Users

</a>

</div>

<div class="card shadow p-3">

<h4>

Recent Orders

</h4>

<table
class="table">

<thead>

<tr>

<th>ID</th>

<th>Product</th>

<th>Qty</th>

<th>Status</th>

</tr>

</thead>

<tbody>

<?php foreach(
$recentOrders
as
$order
): ?>

<tr>

<td>

<?= $order['id'] ?>

</td>

<td>

<?= htmlspecialchars(
$order['product_name']
) ?>

</td>

<td>

<?= $order['quantity'] ?>

</td>

<td>

<span
class="badge bg-secondary">

<?= ucfirst(
$order['status']
) ?>

</span>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

<?php include '../../includes/footer.php'; ?>
