<?php

require_once '../includes/auth.php';

require_once '../includes/roles.php';

require_once '../config/database.php';

require_once '../includes/mailer.php';
require_once '../includes/notify.php';
require_once '../includes/csrf.php';



requireRole('super_admin');


$message='';


if(
$_SERVER['REQUEST_METHOD']==='POST'
){
verify_csrf();
try{

$pdo->beginTransaction();


$fullname=
trim($_POST['fullname']);

$email=
trim($_POST['email']);

$phone=
trim($_POST['phone']);


$passwordPlain=
$_POST['password'];


$password=
password_hash(
$passwordPlain,
PASSWORD_DEFAULT
);


$ownership=
floatval(
$_POST['ownership_percent']
);


$capital=
floatval(
$_POST['invested_amount']
);



$stmt=
$pdo->prepare(

"

INSERT INTO users
(

fullname,
email,
phone,
password,
role,
status

)

VALUES
(

?,
?,
?,
?,
'investor',
'active'

)

"

);


$stmt->execute([

$fullname,

$email,

$phone,

$password

]);


$userId=
$pdo->lastInsertId();



$stmt=
$pdo->prepare(

"

INSERT INTO investors
(

user_id,

ownership_percent,

invested_amount,

status,

lock_period_months,

withdrawal_date

)

VALUES
(

?,

?,

?,

'active',

12,

DATE_ADD(
NOW(),
INTERVAL 12 MONTH
)

)

"

);


$stmt->execute([

$userId,

$ownership,

$capital

]);


$pdo->commit();
notify(

$pdo,

$userId,

'Investor Account Created',

'Your investor account has been created successfully.'

);




$emailBody=

"

<h2>

Welcome to FarmLink

</h2>

<p>

Hello {$fullname},

</p>

<p>

Your investor account has been created.

</p>

<p>

Email:
<b>{$email}</b>

</p>

<p>

Password:
<b>{$passwordPlain}</b>

</p>

<p>

Ownership:
<b>{$ownership}%</b>

</p>

<p>

Seed Capital:
<b>₦".number_format($capital,2)."</b>

</p>

<p>

You can now login.

</p>

";


sendMail(

$email,

'FarmLink Investor Account Created',

$emailBody

);



$message=
"Investor created successfully and email sent";


}catch(
Exception $e
){

if(
$pdo->inTransaction()
){

$pdo->rollBack();

}


$message=
$e->getMessage();

}

}



$investors=
$pdo->query(

"

SELECT

u.fullname,
u.email,

i.ownership_percent,

i.invested_amount

FROM users u

JOIN investors i
ON u.id=i.user_id

WHERE u.role='investor'

ORDER BY u.id DESC

"

)->fetchAll();


include '../includes/header.php';

include '../includes/navbar.php';

?>

<div class="container mt-5">

<h2>

Create Investor

</h2>

<?php if($message): ?>

<div class="alert alert-info">

<?= htmlspecialchars($message) ?>

</div>

<?php endif; ?>

<form method="POST">
    <?= csrfField(); ?>

<div class="mb-3">

<label>Name</label>

<input
name="fullname"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Email</label>

<input
type="email"
name="email"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Phone</label>

<input
name="phone"
class="form-control">

</div>

<div class="mb-3">

<label>Password</label>

<input
type="password"
name="password"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Ownership (%)</label>

<input
type="number"
step="0.01"
name="ownership_percent"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Seed Capital (₦)</label>

<input
type="number"
step="0.01"
name="invested_amount"
class="form-control"
required>

</div>

<button
class="btn btn-success">

Create Investor

</button>

</form>

<hr>

<h3>

Existing Investors

</h3>

<table class="table">

<tr>

<th>Name</th>

<th>Email</th>

<th>Ownership</th>

<th>Seed Capital</th>

</tr>

<?php foreach($investors as $i): ?>

<tr>

<td>

<?= htmlspecialchars($i['fullname']) ?>

</td>

<td>

<?= htmlspecialchars($i['email']) ?>

</td>

<td>

<?= $i['ownership_percent'] ?>%

</td>

<td>

₦<?= number_format($i['invested_amount'],2) ?>

</td>

</tr>

<?php endforeach; ?>

</table>

</div>

<?php include '../includes/footer.php'; ?>
