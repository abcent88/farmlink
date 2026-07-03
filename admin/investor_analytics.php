<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';

requireRole('super_admin');


/*
Total Investors
*/

$totalInvestors=
$pdo
->query("

SELECT COUNT(*)

FROM investors

")
->fetchColumn();



/*
Total Capital
*/

$totalCapital=
$pdo
->query("

SELECT
COALESCE(
SUM(invested_amount),
0
)

FROM investors

")
->fetchColumn();



/*
Total ROI Paid
*/

$totalROI=
$pdo
->query("

SELECT
COALESCE(
SUM(payout),
0
)

FROM investor_earnings

")
->fetchColumn();



/*
Active Investors
*/

$active=
$pdo
->query("

SELECT COUNT(*)

FROM investors

WHERE status='active'

")
->fetchColumn();



/*
Locked
*/

$locked=
$pdo
->query("

SELECT COUNT(*)

FROM investors

WHERE withdrawal_date>CURDATE()

")
->fetchColumn();



/*
Average Capital
*/

$average=
$pdo
->query("

SELECT
COALESCE(
AVG(invested_amount),
0
)

FROM investors

")
->fetchColumn();



include '../includes/header.php';
include '../includes/navbar.php';

?>

<div class="container mt-4">

<h2>

Investor Analytics

</h2>


<div class="row g-4">


<div class="col-md-4">

<div class="card shadow">

<div class="card-body">

<h6>Total Investors</h6>

<h2>

<?= $totalInvestors ?>

</h2>

</div>

</div>

</div>



<div class="col-md-4">

<div class="card shadow">

<div class="card-body">

<h6>Total Capital</h6>

<h2>

₦<?= number_format(
$totalCapital,
2
) ?>

</h2>

</div>

</div>

</div>



<div class="col-md-4">

<div class="card shadow">

<div class="card-body">

<h6>Total ROI Paid</h6>

<h2>

₦<?= number_format(
$totalROI,
2
) ?>

</h2>

</div>

</div>

</div>



<div class="col-md-4">

<div class="card shadow">

<div class="card-body">

<h6>Active Investors</h6>

<h2>

<?= $active ?>

</h2>

</div>

</div>

</div>



<div class="col-md-4">

<div class="card shadow">

<div class="card-body">

<h6>Locked Investors</h6>

<h2>

<?= $locked ?>

</h2>

</div>

</div>

</div>



<div class="col-md-4">

<div class="card shadow">

<div class="card-body">

<h6>Average Capital</h6>

<h2>

₦<?= number_format(
$average,
2
) ?>

</h2>

</div>

</div>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>