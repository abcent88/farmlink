<?php

require_once '../includes/auth.php';

require_once '../includes/roles.php';

require_once '../config/database.php';

require_once '../includes/mailer.php';
require_once '../includes/notify.php';



requireRole('super_admin');


$message='';


if(
$_SERVER['REQUEST_METHOD']==='POST'
){

$withdrawalId=
intval(
$_POST['withdrawal_id']
);


$action=
trim(
$_POST['action']
);


if(
in_array(
$action,
[
'approved',
'rejected'
]
)
){

$stmt=
$pdo->prepare(

"

UPDATE investor_withdrawals

SET status=?

WHERE id=?

"

);
notify(

$pdo,

$investorUserId,

'Withdrawal Approved',

'Your withdrawal request has been approved and payment is being processed.'

);


$stmt->execute([

$action,

$withdrawalId

]);


$stmt=
$pdo->prepare(

"

SELECT

u.email,
u.fullname,

w.amount,
w.status

FROM investor_withdrawals w

JOIN investors i
ON w.investor_id=i.id

JOIN users u
ON i.user_id=u.id

WHERE w.id=?

LIMIT 1

"

);


$stmt->execute([

$withdrawalId

]);


$user=
$stmt->fetch();


if($user){

$subject=
$action==='approved'

?

'FarmLink Withdrawal Approved'

:

'FarmLink Withdrawal Rejected';


$body=

"

<h2>

Withdrawal Update

</h2>

<p>

Hello {$user['fullname']},

</p>

<p>

Your withdrawal request has been

<b>

".strtoupper($action)."

</b>

</p>

<p>

Amount:

<b>

₦".number_format(
$user['amount'],
2
)."

</b>

</p>

";



if(
$action==='approved'
){

$body.=

"

<p>

Funds will be processed shortly.

</p>

";

}else{

$body.=

"

<p>

Please contact support if needed.

</p>

";

}



sendMail(

$user['email'],

$subject,

$body

);

}


$message=

"Withdrawal updated and email sent.";

}

}



$stmt=
$pdo->query(

"

SELECT

w.id,

u.fullname,

u.email,

w.amount,

w.status,

w.requested_at

FROM investor_withdrawals w

JOIN investors i
ON w.investor_id=i.id

JOIN users u
ON i.user_id=u.id

ORDER BY w.id DESC

"

);


$withdrawals=
$stmt->fetchAll();


include '../includes/header.php';

include '../includes/navbar.php';

?>

<div class="container mt-5">

<h2>

Investor Withdrawals

</h2>

<?php if($message): ?>

<div class="alert alert-success">

<?= htmlspecialchars($message) ?>

</div>

<?php endif; ?>

<table class="table table-bordered">

<tr>

<th>ID</th>

<th>Investor</th>

<th>Email</th>

<th>Amount</th>

<th>Status</th>

<th>Date</th>

<th>Action</th>

</tr>

<?php foreach($withdrawals as $w): ?>

<tr>

<td>

<?= $w['id'] ?>

</td>

<td>

<?= htmlspecialchars(
$w['fullname']
) ?>

</td>

<td>

<?= htmlspecialchars(
$w['email']
) ?>

</td>

<td>

₦<?= number_format(
$w['amount'],
2
) ?>

</td>

<td>

<?= strtoupper(
$w['status']
) ?>

</td>

<td>

<?= $w['requested_at'] ?>

</td>

<td>

<?php if(
$w['status']==='pending'
): ?>

<form
method="POST">

<input
type="hidden"
name="withdrawal_id"
value="<?= $w['id'] ?>">

<button
name="action"
value="approved"
class="btn btn-success btn-sm">

Approve

</button>

<button
name="action"
value="rejected"
class="btn btn-danger btn-sm">

Reject

</button>

</form>

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

</table>

</div>

<?php include '../includes/footer.php'; ?>
