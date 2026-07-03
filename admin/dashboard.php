
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
/*
Analytics
*/

$totalInvestors=
$pdo->query(

"

SELECT COUNT(*)

FROM users

WHERE role='investor'

"

)->fetchColumn();

$pendingWithdrawals=
$pdo->query(

"

SELECT COUNT(*)

FROM investor_withdrawals

WHERE status='pending'

"

)->fetchColumn();

$totalProducts=
$pdo->query(

"

SELECT COUNT(*)

FROM products

"

)->fetchColumn();

$approvedProducts=
$pdo->query(

"

SELECT COUNT(*)

FROM products

WHERE status='approved'

"

)->fetchColumn();

$investedCapital=
$pdo->query(

"

SELECT
COALESCE(
SUM(invested_amount),
0
)

FROM investors

"

)->fetchColumn();

$monthlyOrders=
$pdo->query(

"

SELECT
COUNT(*)

FROM orders

WHERE
MONTH(created_at)=MONTH(NOW())

AND

YEAR(created_at)=YEAR(NOW())

"

)->fetchColumn();

$todayUsers=
$pdo->query(

"

SELECT
COUNT(*)

FROM users

WHERE
DATE(created_at)=CURDATE()

"

)->fetchColumn();


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
<a
href="generate_investor_earnings.php"
class="btn btn-success">

Generate Investor Earnings

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
href="roi_settings.php"
class="btn btn-primary">


ROI Settings

</a>

</div>

</div>

</div>
<br>

<div class="row g-4">

<div class="col-md-3">

<div class="card shadow p-4">

<h6>

Investors

</h6>

<h2>

🏦

<?= $totalInvestors ?>

</h2>

</div>

</div>

<div class="col-md-3">

<div class="card shadow p-4">

<h6>

Pending Withdrawals

</h6>

<h2>

💵

<?= $pendingWithdrawals ?>

</h2>

</div>

</div>

<div class="col-md-3">

<div class="card shadow p-4">

<h6>

Products

</h6>

<h2>

🌽

<?= $totalProducts ?>

</h2>

<small>

Approved:

<?= $approvedProducts ?>

</small>

</div>

</div>

<div class="col-md-3">

<div class="card shadow p-4">

<h6>

Investor Capital

</h6>

<h2>

₦<?= number_format(
$investedCapital,
2
) ?>

</h2>

</div>

</div>

</div>

<br>

<div class="row g-4">

<div class="col-md-6">

<div class="card shadow p-4">

<h5>

This Month Orders

</h5>

<h1>

🛒

<?= $monthlyOrders ?>

</h1>

</div>

</div>

<div class="col-md-6">

<div class="card shadow p-4">

<h5>

New Users Today

</h5>

<h1>

🔥

<?= $todayUsers ?>

</h1>

</div>

</div>

</div>
<div class="col-md-3">

<a
href="activity_logs.php"
class="nav-link-card">

<div class="card admin-card">

<div class="admin-icon">

🧾

</div>

Logs

</div>

</a>

</div>


<?php include '../includes/footer.php'; ?>

