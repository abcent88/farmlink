<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';

requireRole('investor');

$userId=$_SESSION['user_id'];

$total=$pdo->prepare("
SELECT
COALESCE(
SUM(payout),
0
)
FROM investor_earnings
WHERE investor_id=?
");

$total->execute([
$userId
]);

$totalEarned=
$total->fetchColumn();

$today=$pdo->prepare("
SELECT
COALESCE(
SUM(payout),
0
)
FROM investor_earnings
WHERE investor_id=?
AND earning_date=CURDATE()
");

$today->execute([
$userId
]);

$todayEarned=
$today->fetchColumn();

$list=$pdo->prepare("
SELECT *
FROM investor_earnings
WHERE investor_id=?
ORDER BY id DESC
LIMIT 10
");

$list->execute([
$userId
]);

$records=
$list->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';

?>

<div class="container mt-5">

<h1>

Investor Dashboard

</h1>

<p>

Welcome
<?= htmlspecialchars($_SESSION['fullname']) ?>

</p>

<div class="row">

<div class="col-md-6">

<div class="card shadow p-4">

<h2>

₦<?= number_format($totalEarned,2) ?>

</h2>

<p>

Total Earnings

</p>

</div>

</div>

<div class="col-md-6">

<div class="card shadow p-4">

<h2>

₦<?= number_format($todayEarned,2) ?>

</h2>

<p>

Today's Earnings

</p>

</div>

</div>

</div>

<div class="card shadow mt-4">

<div class="card-body">

<h3>

Recent Earnings

</h3>

<table class="table">

<tr>

<th>Date</th>

<th>Revenue</th>

<th>%</th>

<th>Payout</th>

</tr>

<?php foreach($records as $r): ?>

<tr>

<td>

<?= $r['earning_date'] ?>

</td>

<td>

₦<?= number_format($r['revenue'],2) ?>

</td>

<td>

<?= $r['share_percent'] ?>%

</td>

<td>

₦<?= number_format($r['payout'],2) ?>

</td>

</tr>

<?php endforeach; ?>

</table>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>
