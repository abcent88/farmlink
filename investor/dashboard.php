<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';

requireRole('investor');

$userId=$_SESSION['user_id'];


/*
Load Investor Profile
*/

$stmt=$pdo->prepare("

SELECT

u.fullname,
u.email,
u.phone,
u.status,

i.id AS investor_id,
i.ownership_percent,
i.invested_amount,
i.created_at,
i.lock_period_months,
i.withdrawal_date

FROM users u

JOIN investors i
ON u.id=i.user_id

WHERE u.id=?

LIMIT 1

");

$stmt->execute([
$userId
]);

$profile=
$stmt->fetch();

if(!$profile){

appFail(
"Profile not found."
);

}

$investorId=
$profile['investor_id'];


/*
Total ROI
*/

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

$totalROI=
$stmt->fetchColumn();


/*
Today's ROI
*/

$stmt=$pdo->prepare("

SELECT

COALESCE(
SUM(payout),
0
)

FROM investor_earnings

WHERE investor_id=?
AND earning_date=CURDATE()

");

$stmt->execute([
$investorId
]);

$todayROI=
$stmt->fetchColumn();


/*
Recent Earnings
*/

$stmt=$pdo->prepare("

SELECT *

FROM investor_earnings

WHERE investor_id=?

ORDER BY earning_date DESC

LIMIT 5

");

$stmt->execute([
$investorId
]);

$history=
$stmt->fetchAll();


/*
Portfolio
*/

$portfolio=
$profile['invested_amount']
+
$totalROI;


/*
Quarter Countdown
*/

$month=date('n');

if($month<=3){

$nextDate=
date('Y').'-03-31';

}elseif($month<=6){

$nextDate=
date('Y').'-06-30';

}elseif($month<=9){

$nextDate=
date('Y').'-09-30';

}else{

$nextDate=
date('Y').'-12-31';

}

$days=
max(
0,
floor(
(
strtotime($nextDate)
-
time()
)
/
86400
)
);


/*
Withdrawal
*/

$withdrawLocked=

strtotime(
$profile['withdrawal_date']
)>time();

include '../includes/header.php';
include '../includes/navbar.php';

?>

<div class="container mt-4">

<h2>

Investor Dashboard

</h2>

<p>

Welcome
<strong>

<?= htmlspecialchars(
$_SESSION['fullname']
) ?>

</strong>

</p>



<div class="row">

<div class="col-md-3">

<div class="card shadow">

<div class="card-body">

<h6>

Seed Capital

</h6>

<h3>

₦<?= number_format(
$profile['invested_amount'],
2
) ?>

</h3>

</div>

</div>

</div>


<div class="col-md-3">

<div class="card shadow">

<div class="card-body">

<h6>

Total ROI

</h6>

<h3>

₦<?= number_format(
$totalROI,
2
) ?>

</h3>

</div>

</div>

</div>



<div class="col-md-3">

<div class="card shadow">

<div class="card-body">

<h6>

Today

</h6>

<h3>

₦<?= number_format(
$todayROI,
2
) ?>

</h3>

</div>

</div>

</div>



<div class="col-md-3">

<div class="card shadow">

<div class="card-body">

<h6>

Portfolio Value

</h6>

<h3>

₦<?= number_format(
$portfolio,
2
) ?>

</h3>

</div>

</div>

</div>

</div>



<div class="row mt-4">

<div class="col-md-6">

<div class="card shadow">

<div class="card-body">

<h5>

Investment Details

</h5>

<hr>

<p>

Ownership

<strong>

<?= number_format(
$profile['ownership_percent'],
2
) ?>%

</strong>

</p>

<p>

Investor Since

<strong>

<?= date(
'd M Y',
strtotime(
$profile['created_at']
)
) ?>

</strong>

</p>

<p>

Lock Period

<strong>

<?= $profile['lock_period_months'] ?>

Months

</strong>

</p>

<p>

Withdrawal Date

<strong>

<?= $profile['withdrawal_date'] ?>

</strong>

</p>

<?php if($withdrawLocked): ?>

<span class="badge bg-warning">

Locked

</span>

<?php else: ?>

<span class="badge bg-success">

Eligible

</span>

<?php endif; ?>

</div>

</div>

</div>



<div class="col-md-6">

<div class="card shadow">

<div class="card-body">

<h5>

Next ROI

</h5>

<hr>

<h1>

<?= $days ?>

Days

</h1>

<p>

Next Quarterly ROI

</p>

<p>

<?= $nextDate ?>

</p>

</div>

</div>

</div>

</div>



<div class="card shadow mt-4">

<div class="card-body">

<h4>

Recent ROI History

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



<div class="card shadow mt-4">

<div class="card-body">

<h4>

ROI Performance

</h4>

<canvas
id="roiChart">
</canvas>

</div>

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

label:'ROI',

data:values,

borderWidth:3

}]

}

}

);

</script>

<?php include '../includes/footer.php'; ?>