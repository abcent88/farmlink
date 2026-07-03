<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';
require_once '../includes/logger.php';
require_once '../includes/notify.php';


requireRole('investor');

$userId=$_SESSION['user_id'];

$stmt=$pdo->prepare("
SELECT
id,
invested_amount,
withdrawal_date
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


/*
Available ROI
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


$stmt=$pdo->prepare("
SELECT
COALESCE(
SUM(amount),
0
)

FROM investor_withdrawals

WHERE investor_id=?
AND status='approved'
");

$stmt->execute([
$investorId
]);

$used=
$stmt->fetchColumn();

$available=
$totalROI-
$used;

if(
$available<0
){

$available=0;

}

$message='';


if($_SERVER['REQUEST_METHOD']==='POST'){

$bank=
trim(
$_POST['bank_name']
);

$accountName=
trim(
$_POST['account_name']
);

$accountNumber=
trim(
$_POST['account_number']
);

$amount=
floatval(
$_POST['amount']
);


if(
empty($bank)
||
empty($accountName)
||
empty($accountNumber)
){

$message=
"All bank details are required";

}elseif(
!preg_match(
'/^[0-9]{10}$/',
$accountNumber
)
){

$message=
"Account number must be 10 digits";

}elseif(
$amount<=0
){

$message=
"Invalid amount";

}elseif(
$amount>$available
){

$message=
"Insufficient ROI balance";

}else{

$stmt=
$pdo->prepare("
INSERT INTO investor_withdrawals
(

investor_id,
amount,
bank_name,
account_name,
account_number

)

VALUES
(
?,
?,
?,
?,
?
)
");

$stmt->execute([

$investorId,
$amount,
$bank,
$accountName,
$accountNumber

]);
notify(

$pdo,

$userId,

'Withdrawal Submitted',

'Your withdrawal request of ₦'.number_format($amount,2).' has been submitted.'

);


logActivity(

$pdo,

$userId,

'Withdrawal Request',

'Requested ₦'.$amount

);


$message=
"Withdrawal request submitted";

}

}



/*
History
*/

$stmt=
$pdo->prepare("
SELECT *

FROM investor_withdrawals

WHERE investor_id=?

ORDER BY id DESC
");

$stmt->execute([
$investorId
]);

$requests=
$stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';

?>

<div class="container mt-4">

<h2>

Withdrawal

</h2>


<div class="card shadow">

<div class="card-body">

<h5>

Available ROI Balance

</h5>

<h2>

₦<?= number_format(
$available,
2
) ?>

</h2>

</div>

</div>



<div class="card shadow mt-4">

<div class="card-body">

<?php if($message): ?>

<div class="alert alert-info">

<?= $message ?>

</div>

<?php endif; ?>

<form method="POST">

<div class="mb-3">

<label>

Bank Name

</label>

<input
name="bank_name"
class="form-control"
required>

</div>


<div class="mb-3">

<label>

Account Name

</label>

<input
name="account_name"
class="form-control"
required>

</div>


<div class="mb-3">

<label>

Account Number

</label>

<input
name="account_number"
maxlength="10"
class="form-control"
required>

</div>


<div class="mb-3">

<label>

Amount

</label>

<input
type="number"
step="0.01"
name="amount"
class="form-control"
required>

</div>


<button
class="btn btn-success">

Request Withdrawal

</button>

</form>

</div>

</div>




<div class="card mt-4 shadow">

<div class="card-body">

<h5>

History

</h5>

<table class="table">

<tr>

<th>Amount</th>

<th>Bank</th>

<th>Account</th>

<th>Status</th>

<th>Date</th>

</tr>

<?php foreach($requests as $r): ?>

<tr>

<td>

₦<?= number_format(
$r['amount'],
2
) ?>

</td>

<td>

<?= htmlspecialchars(
$r['bank_name']
) ?>

</td>

<td>

<?= htmlspecialchars(
$r['account_number']
) ?>

</td>

<td>

<?= strtoupper(
$r['status']
) ?>

</td>

<td>

<?= $r['requested_at'] ?>

</td>

</tr>

<?php endforeach; ?>

</table>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>