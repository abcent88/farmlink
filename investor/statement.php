<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';

requireRole('investor');

$userId=$_SESSION['user_id'];


/*
Investor
*/

$stmt=$pdo->prepare("

SELECT

u.fullname,
u.email,

i.id,
i.invested_amount,
i.ownership_percent

FROM users u

JOIN investors i
ON u.id=i.user_id

WHERE u.id=?

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


/*
ROI
*/

$stmt=$pdo->prepare("

SELECT *

FROM investor_earnings

WHERE investor_id=?

ORDER BY earning_date DESC

");

$stmt->execute([
$investorId
]);

$rows=
$stmt->fetchAll();


$stmt=$pdo->prepare("

SELECT
COALESCE(
SUM(payout),
0
)

FROM investor_earnings

WHERE investor_id=?

");

$stmt->execute([
$investorId
]);

$total=
$stmt->fetchColumn();

$portfolio=
$investor['invested_amount']
+
$total;

include '../includes/header.php';
include '../includes/navbar.php';

?>

<div class="container mt-4">

<div class="card shadow">

<div class="card-body">

<h2>

Investment Statement

</h2>

<hr>

<p>

Name:
<strong>

<?= $investor['fullname'] ?>

</strong>

</p>

<p>

Email:
<?= $investor['email'] ?>

</p>

<p>

Capital:
₦<?= number_format(
$investor['invested_amount'],
2
) ?>

</p>

<p>

Total ROI:
₦<?= number_format(
$total,
2
) ?>

</p>

<p>

Portfolio:
₦<?= number_format(
$portfolio,
2
) ?>

</p>

<hr>

<table class="table">

<tr>

<th>Date</th>
<th>Capital</th>
<th>ROI</th>
<th>Payout</th>

</tr>

<?php foreach($rows as $r): ?>

<tr>

<td>

<?= $r['earning_date'] ?>

</td>

<td>

₦<?= number_format(
$r['revenue'],
2
) ?>

</td>

<td>

<?= $r['share_percent'] ?>%

</td>

<td>

₦<?= number_format(
$r['payout'],
2
) ?>

</td>

</tr>

<?php endforeach; ?>

</table>

<button
onclick="window.print()"
class="btn btn-success">

Download Statement

</button>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>