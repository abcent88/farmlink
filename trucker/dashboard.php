<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole('trucker');

$userId=$_SESSION['user_id'];

$totalDeliveries=$pdo->prepare("
SELECT COUNT(*)
FROM deliveries
WHERE trucker_id=?
");

$totalDeliveries->execute([
$userId
]);

$totalDeliveries=
$totalDeliveries->fetchColumn();

$acceptedDeliveries=$pdo->prepare("
SELECT COUNT(*)
FROM deliveries
WHERE trucker_id=?
AND status='accepted'
");

$acceptedDeliveries->execute([
$userId
]);

$acceptedDeliveries=
$acceptedDeliveries->fetchColumn();

$completedDeliveries=$pdo->prepare("
SELECT COUNT(*)
FROM deliveries
WHERE trucker_id=?
AND status='completed'
");

$completedDeliveries->execute([
$userId
]);

$completedDeliveries=
$completedDeliveries->fetchColumn();

$openDeliveries=
$pdo
->query("
SELECT COUNT(*)
FROM deliveries
WHERE status='open'
")
->fetchColumn();

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

background:
var(--bs-body-bg);

}

.dashboard-card:hover{

transform:translateY(-5px);

}

.icon{

font-size:40px;

margin-bottom:10px;

}

.quick-grid{

display:grid;

grid-template-columns:
repeat(
auto-fit,
minmax(
160px,
1fr
)
);

gap:20px;

}

.quick-link{

text-decoration:none;

padding:25px;

border-radius:20px;

box-shadow:
0 2px 10px
rgba(
0,
0,
0,
0.08
);

text-align:center;

color:inherit;

}

.quick-link:hover{

transform:scale(1.05);

}

.quick-link span{

display:block;

font-size:44px;

margin-bottom:10px;

}

</style>

<div class="container mt-4">

<h2>

🚚 Trucker Dashboard

</h2>

<p>

Welcome

<?= htmlspecialchars(
$_SESSION['fullname']
) ?>

</p>

<div class="row g-4">

<div class="col-md-3">

<div class="dashboard-card shadow">

<div class="icon">

📦

</div>

<h2>

<?= $totalDeliveries ?>

</h2>

<p>

Total Deliveries

</p>

</div>

</div>

<div class="col-md-3">

<div class="dashboard-card shadow">

<div class="icon">

🟢

</div>

<h2>

<?= $openDeliveries ?>

</h2>

<p>

Open Deliveries

</p>

</div>

</div>

<div class="col-md-3">

<div class="dashboard-card shadow">

<div class="icon">

🚛

</div>

<h2>

<?= $acceptedDeliveries ?>

</h2>

<p>

Accepted

</p>

</div>

</div>

<div class="col-md-3">

<div class="dashboard-card shadow">

<div class="icon">

✅

</div>

<h2>

<?= $completedDeliveries ?>

</h2>

<p>

Completed

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
href="deliveries.php"
class="quick-link">

<span>

📬

</span>

Available

</a>

<a
href="my_deliveries.php"
class="quick-link">

<span>

🚚

</span>

My Deliveries

</a>

<a
href="../logout.php"
class="quick-link">

<span>

🚪

</span>

Logout

</a>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>
