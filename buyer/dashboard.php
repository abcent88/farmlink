<?php
require_once '../includes/auth.php';
require_once '../includes/roles.php';
requireRole('buyer');
require_once '../config/database.php';

$buyerId=$_SESSION['user_id'];

$orderSummary=$pdo->prepare("
SELECT
o.status,
COUNT(*) total,
SUM(o.quantity*p.price) total_amount
FROM orders o
JOIN products p
ON o.product_id=p.id
WHERE o.buyer_id=?
GROUP BY o.status
");

$orderSummary->execute([$buyerId]);

$orders=$orderSummary->fetchAll(PDO::FETCH_ASSOC);

$totalFee=0;
$totalSpent=0;

foreach($orders as $o){

$totalFee+=($o['total_amount']*0.05);

$totalSpent+=$o['total_amount'];

}

$totalOrders=array_sum(
array_column(
$orders,
'total'
)
);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<style>

.dashboard-card{

border:none;

border-radius:20px;

padding:25px;

text-align:center;

transition:.3s;

}

.dashboard-card:hover{

transform:translateY(-5px);

}

.icon-box{

font-size:38px;

margin-bottom:10px;

}

.quick-grid{

display:grid;

grid-template-columns:
repeat(
auto-fit,
minmax(
140px,
1fr
)
);

gap:20px;

}

.quick-item{

text-align:center;

padding:20px;

border-radius:20px;

text-decoration:none;

background:
var(--bs-body-bg);

box-shadow:
0 2px 12px
rgba(
0,
0,
0,
0.08
);

}

.quick-item:hover{

transform:scale(1.05);

}

.quick-item span{

display:block;

font-size:42px;

}

.bottom-nav-space{

height:100px;

}

</style>

<div class="container mt-4">

<h2>

👋 Welcome

<?= htmlspecialchars(
$_SESSION['fullname']
) ?>

</h2>

<div class="row mt-4">

<div class="col-md-4">

<div class="dashboard-card shadow">

<div class="icon-box">
🛒
</div>

<h3>

<?= $totalOrders ?>

</h3>

<p>

Total Orders

</p>

</div>

</div>

<div class="col-md-4">

<div class="dashboard-card shadow">

<div class="icon-box">
💰
</div>

<h3>

₦<?= number_format(
$totalSpent,
2
) ?>

</h3>

<p>

Total Spent

</p>

</div>

</div>

<div class="col-md-4">

<div class="dashboard-card shadow">

<div class="icon-box">
🏦
</div>

<h3>

₦<?= number_format(
$totalFee,
2
) ?>

</h3>

<p>

Platform Fees

</p>

</div>

</div>

</div>

<div class="mt-5">

<h4>

Quick Actions

</h4>

<div class="quick-grid">

<a
class="quick-item"
href="marketplace.php">

<span>
🛍️
</span>

Marketplace

</a>

<a
class="quick-item"
href="orders.php">

<span>
📦
</span>

Orders

</a>

<a
class="quick-item"
href="purchase_history.php">

<span>
📄
</span>

History

</a>

<a
class="quick-item"
href="../logout.php">

<span>
🚪
</span>

Logout

</a>

</div>

</div>

<div class="mt-5">

<h4>

Order Status

</h4>

<div class="row">

<?php foreach($orders as $order): ?>

<div class="col-md-3">

<div class="dashboard-card shadow">

<h2>

<?= $order['total'] ?>

</h2>

<p>

<?= ucfirst(
$order['status']
) ?>

</p>

</div>

</div>

<?php endforeach; ?>

</div>

</div>

</div>

<div class="bottom-nav-space"></div>

<?php include '../includes/footer.php'; ?>
