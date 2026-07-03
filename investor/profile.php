<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';

requireRole('investor');

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
SELECT
u.fullname,
u.email,
u.phone,
u.status,

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

$profile =
$stmt->fetch();

if(!$profile){

dappFail(
"Profile not found."
);
}

/*
Next Quarterly ROI
*/

$month=date('n');

if($month<=3){

$next="31 March";

}elseif($month<=6){

$next="30 June";

}elseif($month<=9){

$next="30 September";

}else{

$next="31 December";

}

/*
Withdrawal Status
*/

$locked=
strtotime(
$profile['withdrawal_date']
)>time();

include '../includes/header.php';
include '../includes/navbar.php';

?>

<div class="container mt-4">

<h2>

👤 Investor Profile

</h2>

<div class="row">

<!-- PERSONAL -->

<div class="col-md-6">

<div class="card shadow">

<div class="card-body">

<h4>

Personal Information

</h4>

<hr>

<p>

<strong>Name:</strong>

<?= htmlspecialchars(
$profile['fullname']
) ?>

</p>

<p>

<strong>Email:</strong>

<?= htmlspecialchars(
$profile['email']
) ?>

</p>

<p>

<strong>Phone:</strong>

<?= htmlspecialchars(
$profile['phone']
) ?>

</p>

<p>

<strong>Status:</strong>

<?= strtoupper(
$profile['status']
) ?>

</p>

</div>

</div>

</div>



<!-- INVESTMENT -->

<div class="col-md-6">

<div class="card shadow">

<div class="card-body">

<h4>

Investment Information

</h4>

<hr>

<p>

<strong>Ownership:</strong>

<?= number_format(
$profile['ownership_percent'],
2
) ?>%

</p>

<p>

<strong>Invested Amount:</strong>

₦<?= number_format(
$profile['invested_amount'],
2
) ?>

</p>

<p>

<strong>Next ROI Date:</strong>

<?= $next ?>

</p>

<p>

<strong>Investor Since:</strong>

<?= date(
"d M Y",
strtotime(
$profile['created_at']
)
) ?>

</p>

<p>

<strong>Lock Period:</strong>

<?= $profile['lock_period_months'] ?>

Months

</p>

<p>

<strong>Withdrawal Eligible:</strong>

<?= date(
"d M Y",
strtotime(
$profile['withdrawal_date']
)
) ?>

</p>

<p>

<strong>Withdrawal Status:</strong>

<?php if($locked): ?>

<span class="badge bg-warning">

LOCKED

</span>

<?php else: ?>

<span class="badge bg-success">

ELIGIBLE

</span>

<?php endif; ?>

</p>

</div>

</div>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>