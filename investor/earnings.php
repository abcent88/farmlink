<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';

requireRole('investor');

$userId=$_SESSION['user_id'];

$stmt=$pdo->prepare("
SELECT id
FROM investors
WHERE user_id=?
LIMIT 1
");

$stmt->execute([
$userId
]);

$investor=
$stmt->fetch();

if(!$investor){

appFail(
"Profile not found."
);
}

$investorId=
$investor['id'];

$stmt=$pdo->prepare("
SELECT *
FROM investor_earnings
WHERE investor_id=?
ORDER BY earning_date DESC
");

$stmt->execute([
$investorId
]);

$earnings=
$stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';

?>

<div class="container mt-4">

<h2>

💵 Earnings History

</h2>

<div class="card shadow">

<div class="card-body">

<table class="table table-striped">

<thead>

<tr>

<th>Date</th>

<th>Revenue</th>

<th>Share</th>

<th>Payout</th>

</tr>

</thead>

<tbody>

<?php if(count($earnings)): ?>

<?php foreach($earnings as $e): ?>

<tr>

<td>

<?= $e['earning_date'] ?>

</td>

<td>

₦<?= number_format(
$e['revenue'],
2
) ?>

</td>

<td>

<?= $e['share_percent'] ?>%

</td>

<td>

<strong>

₦<?= number_format(
$e['payout'],
2
) ?>

</strong>

</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>

<td colspan="4">

No ROI available

</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>