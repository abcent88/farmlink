
<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';

requireRole('super_admin');

$totalUsers =
$pdo->query(
"SELECT COUNT(*) FROM users"
)->fetchColumn();

$totalFarmers =
$pdo->query(
"SELECT COUNT(*) FROM users WHERE role='farmer'"
)->fetchColumn();

$totalBuyers =
$pdo->query(
"SELECT COUNT(*) FROM users WHERE role='buyer'"
)->fetchColumn();

$pendingUsers =
$pdo->query(
"SELECT COUNT(*) FROM users WHERE status='pending'"
)->fetchColumn();

$productStats =
[
'pending'=>0,
'approved'=>0,
'rejected'=>0
];

$stmt=
$pdo->query("
SELECT status,
COUNT(*) total
FROM products
GROUP BY status
");

while(
$row=
$stmt->fetch()
){

$productStats[
$row['status']
]=$row['total'];

}

$orderStats=
[
'pending'=>0,
'accepted'=>0,
'completed'=>0,
'rejected'=>0
];

$stmt=
$pdo->query("
SELECT
status,
COUNT(*) total
FROM orders
GROUP BY status
");

while(
$row=
$stmt->fetch()
){

$orderStats[
$row['status']
]=$row['total'];

}

$platformRevenue=
$pdo->query("

SELECT
SUM(
o.quantity*
p.price*
0.05
)

FROM orders o

JOIN products p
ON o.product_id=p.id

WHERE
o.status
IN(
'accepted',
'completed'
)

")->fetchColumn();

$platformRevenue=
$platformRevenue
?:0;

$totalCommission=
$pdo->prepare("

SELECT
SUM(amount)

FROM admin_commissions

WHERE admin_id=?

");

$totalCommission
->execute([
$_SESSION['user_id']
]);

$totalCommission=
$totalCommission
->fetchColumn()
?:0;

include '../includes/header.php';
include '../includes/navbar.php';

?>

<style>

.admin-card{

border:none;

border-radius:24px;

padding:25px;

text-align:center;

height:190px;

transition:.3s;

}

.admin-card:hover{

transform:
translateY(-5px);

}

.admin-icon{

font-size:46px;

margin-bottom:10px;

}

.nav-link-card{

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

🛠 Super Admin Dashboard

</h2>

<p>

Welcome

<?= htmlspecialchars(
$_SESSION['fullname']
) ?>

</p>

<div class="row g-4">

<div class="col-md-3">

<a
href="users.php"
class="nav-link-card">

<div class="card admin-card">

<div class="admin-icon">

👥

</div>

<h4>

<?= $totalUsers ?>

</h4>

Users

</div>

</a>

</div>

<div class="col-md-3">

<a
href="products.php"
class="nav-link-card">

<div class="card admin-card">

<div class="admin-icon">

🌽

</div>

<h4>

<?= $productStats['approved'] ?>

</h4>

Products

</div>

</a>

</div>

<div class="col-md-3">

<a
href="orders.php"
class="nav-link-card">

<div
class="
card
admin-card
bg-success
text-white
">

<div class="admin-icon">

🛒

</div>

<h4>

<?= $orderStats['completed'] ?>

</h4>

Orders

</div>

</a>

</div>

<div class="col-md-3">

<a
href="revenue.php"
class="nav-link-card">

<div
class="
card
admin-card
bg-dark
text-white
">

<div class="admin-icon">

💰

</div>

<h4>

₦<?= number_format(
$platformRevenue,
2
) ?>

</h4>

Revenue

</div>

</a>

</div>

</div>

<br>

<div class="row g-4">

<div class="col-md-3">

<a
href="users.php"
class="nav-link-card">

<div class="card admin-card">

<div class="admin-icon">

⏳

</div>

<h4>

<?= $pendingUsers ?>

</h4>

Pending Users

</div>

</a>

</div>

<div class="col-md-3">

<a
href="commissions.php"
class="nav-link-card">

<div
class="
card
admin-card
bg-info
text-white
">

<div class="admin-icon">

📈

</div>

<h4>

₦<?= number_format(
$totalCommission,
2
) ?>

</h4>

Commission

</div>

</a>

</div>

<div class="col-md-3">

<a
href="financial_dashboard.php"
class="nav-link-card">

<div class="card admin-card">

<div class="admin-icon">

🏦

</div>

Financial

</div>

</a>

</div>

<div class="col-md-3">

<a
href="lga_admin/list.php"
class="nav-link-card">

<div class="card admin-card">

<div class="admin-icon">

📍

</div>

LGA Admins

</div>

</a>

</div>

</div>

<br>

<div class="row g-4">

<div class="col-md-4">

<div class="card shadow p-4">

<h5>

Farmers

</h5>

<h2>

<?= $totalFarmers ?>

</h2>

</div>

</div>

<div class="col-md-4">

<div class="card shadow p-4">

<h5>

Buyers

</h5>

<h2>

<?= $totalBuyers ?>

</h2>

</div>

</div>

<div class="col-md-4">

<a
href="../logout.php"
class="nav-link-card">

<div
class="
card
shadow
p-4
bg-danger
text-white
">

<h5>

🚪 Logout

</h5>

</div>

</a>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>

