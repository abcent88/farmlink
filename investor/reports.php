<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';

requireRole('investor');

$userId=$_SESSION['user_id'];


/*
Load Investor
*/

$stmt=$pdo->prepare("

SELECT

i.id,
i.invested_amount,
i.ownership_percent

FROM investors i

WHERE i.user_id=?

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


/*
Summary
*/

$stmt=$pdo->prepare("

SELECT

COUNT(*) total_cycles,

COALESCE(
SUM(payout),
0
) total_roi,

COALESCE(
AVG(payout),
0
) avg_roi

FROM investor_earnings

WHERE investor_id=?

");

$stmt->execute([
$investorId
]);

$summary=
$stmt->fetch();


/*
History
*/

$stmt=$pdo->prepare("

SELECT

earning_date,
revenue,
share_percent,
payout

FROM investor_earnings

WHERE investor_id=?

ORDER BY earning_date DESC

");

$stmt->execute([
$investorId
]);

$history=
$stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';

?>

<div class="container mt-4">

<h2>

ROI Reports

</h2>

<div class="row">

<div class="col-md-4">

<div class="card shadow">

<div class="card-body">

<h3>

<?= $summary['total_cycles'] ?>

</h3>

<p>

Completed Quarters

</p>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card shadow">

<div class="card-body">

<h3>

₦<?= number_format(
$summary['total_roi'],
2
) ?>

</h3>

<p>

Total ROI

</p>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card shadow">

<div class="card-body">

<h3>

₦<?= number_format(
$summary['avg_roi'],
2
) ?>

</h3>

<p>

Average ROI

</p>

</div>

</div>

</div>

</div>


<div class="card mt-4 shadow">

<div class="card-body">

<h4>

ROI History

</h4>

<table class="table">

<tr>

<th>Date</th>
<th>Capital</th>
<th>ROI %</th>
<th>Payout</th>

</tr>

<?php foreach($history as $r): ?>

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

</div>

</div>

</div>
<div class="card mt-4 shadow">

<div class="card-body">

<h4>

ROI Performance

</h4>

<canvas
id="roiChart"
height="100">
</canvas>

</div>

</div>



<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const labels=[

<?php
foreach($history as $r){

echo "'".$r['earning_date']."',";

}
?>

];

const values=[

<?php
foreach($history as $r){

echo $r['payout'].",";

}
?>

];

new Chart(

document
.getElementById(
'roiChart'
),

{

type:'line',

data:{

labels:labels,

datasets:[{

label:

'Quarterly ROI',

data:values,

borderWidth:3,

tension:0.3

}]

},

options:{

responsive:true,

plugins:{

legend:{

display:true

}

}

}

}

);

</script>

<?php include '../includes/footer.php'; ?>