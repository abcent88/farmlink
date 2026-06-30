
<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';

requireRole('farmer');

$farmerId = $_SESSION['user_id'];

$totalProducts =
$pdo->prepare("
SELECT COUNT(*) total
FROM products
WHERE farmer_id=?
");

$totalProducts->execute([
$farmerId
]);

$totalProducts =
$totalProducts
->fetch()['total'];

$orderSummary =
$pdo->prepare("

SELECT
o.status,
COUNT(*) total,
SUM(
o.quantity*p.price
) revenue

FROM orders o

JOIN products p
ON o.product_id=p.id

WHERE p.farmer_id=?

GROUP BY o.status

");

$orderSummary
->execute([
$farmerId
]);

$orders =
$orderSummary
->fetchAll(
PDO::FETCH_ASSOC
);

$totalRevenue=0;

foreach(
$orders
as
$o
){

if(
in_array(
$o['status'],
[
'accepted',
'completed'
]
)
){

$totalRevenue+=
$o['revenue'];

}

}

$recent =
$pdo->prepare("

SELECT
o.id,
o.quantity,
o.status,
p.product_name

FROM orders o

JOIN products p
ON o.product_id=p.id

WHERE p.farmer_id=?

ORDER BY o.id DESC

LIMIT 5

");

$recent->execute([
$farmerId
]);

$recentOrders =
$recent->fetchAll(
PDO::FETCH_ASSOC
);

include '../includes/header.php';
include '../includes/navbar.php';

?>

<style>

.dashboard-card{

border:none;

border-radius:22px;

padding:22px;

text-align:center;

transition:.3s;

height:180px;

}

.dashboard-card:hover{

transform:
translateY(-6px);

}

.dashboard-icon{

font-size:42px;

margin-bottom:12px;

}

.dashboard-link{

text-decoration:none;

color:inherit;

}

.dark-mode .card{

background:#1f2937;

color:white;

}

</style>

<div class="container mt-4">

<h2>

🌾 Farmer Dashboard

</h2>

<p>

Welcome
<?= htmlspecialchars($_SESSION['fullname']) ?>

</p>

<!-- MAIN ICON GRID -->

<div class="row g-4">

<div class="col-md-3">

<a
href="add_product.php"
class="dashboard-link">

<div class="card dashboard-card">

<div class="dashboard-icon">

🌽

</div>

<h5>

Add Product

</h5>

</div>

</a>

</div>

<div class="col-md-3">

<a
href="products.php"
class="dashboard-link">

<div class="card dashboard-card">

<div class="dashboard-icon">

📦

</div>

<h5>

Products

</h5>

<p>

<?= $totalProducts ?>

Items

</p>

</div>

</a>

</div>

<div class="col-md-3">

<a
href="orders.php"
class="dashboard-link">

<div
class="
card
dashboard-card
bg-success
text-white
">

<div class="dashboard-icon">

🛒

</div>

<h5>

Orders

</h5>

<p>

Manage

</p>

</div>

</a>

</div>

<div class="col-md-3">

<a
href="earnings.php"
class="dashboard-link">

<div
class="
card
dashboard-card
bg-dark
text-white
">

<div class="dashboard-icon">

💰

</div>

<h5>

Revenue

</h5>

<p>

₦<?= number_format(
$totalRevenue,
2
) ?>

</p>

</div>

</a>

</div>

</div>

<br>

<!-- QUICK ACTIONS -->

<div class="row g-4">

<div class="col-md-3">

<a
href="sales_report.php"
class="dashboard-link">

<div class="card dashboard-card">

<div class="dashboard-icon">

📈

</div>

Sales Report

</div>

</a>

</div>

<div class="col-md-3">

<a
href="orders.php?status=pending"
class="dashboard-link">

<div class="card dashboard-card">

<div class="dashboard-icon">

⏳

</div>

Pending Orders

</div>

</a>

</div>

<div class="col-md-3">

<a
href="orders.php?status=accepted"
class="dashboard-link">

<div class="card dashboard-card">

<div class="dashboard-icon">

✅

</div>

Approved

</div>

</a>

</div>

<div class="col-md-3">

<a
href="../logout.php"
class="dashboard-link">

<div
class="
card
dashboard-card
bg-danger
text-white
">

<div class="dashboard-icon">

🚪

</div>

Logout

</div>

</a>

</div>

</div>

<br>

<!-- RECENT -->

<div class="card shadow">

<div class="card-body">

<h4>

Recent Orders

</h4>

<table
class="
table
table-striped
">

<tr>

<th>

ID

</th>

<th>

Product

</th>

<th>

Qty

</th>

<th>

Status

</th>

</tr>

<?php foreach(
$recentOrders
as
$order
): ?>

<tr>

<td>

#<?= $order['id'] ?>

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

<?= ucfirst(
$order['status']
) ?>

</td>

</tr>

<?php endforeach; ?>

</table>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>
```
