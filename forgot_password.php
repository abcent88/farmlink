<?php

require_once 'config/database.php';

require_once 'includes/mailer.php';
require_once 'config/app.php';


$message='';


if(
$_SERVER['REQUEST_METHOD']==='POST'
){

$email=
trim(
$_POST['email']
);


$stmt=
$pdo->prepare(

"

SELECT id

FROM users

WHERE email=?

LIMIT 1

"

);


$stmt->execute([

$email

]);


if(
$stmt->fetch()
){

$token=
bin2hex(
random_bytes(32)
);


$expiry=
date(

'Y-m-d H:i:s',

time()+3600

);


$stmt=
$pdo->prepare(

"

INSERT INTO password_resets
(

email,

token,

expires_at

)

VALUES
(

?,

?,

?

)

"

);


$stmt->execute([

$email,

$token,

$expiry

]);


$link=

APP_URL

."/reset_password.php?token="

.$token;



$body=

"

<h2>

FarmLink Password Reset

</h2>

<p>

We received a request to reset your password.

</p>

<p>

Click below:

</p>

<p>

<a href='$link'>

Reset Password

</a>

</p>

<p>

This link expires in 1 hour.

</p>

";


sendMail(

$email,

'FarmLink Password Reset',

$body

);


}


$message=

"If the email exists, password reset instructions have been sent.";

}


include 'includes/header.php';

include 'includes/navbar.php';

?>

<div class="container mt-5">

<div class="card shadow">

<div class="card-body">

<h3>

Forgot Password

</h3>

<?php if($message): ?>

<div class="alert alert-success">

<?= htmlspecialchars($message) ?>

</div>

<?php endif; ?>

<form method="POST">

<label>

Email

</label>

<input
type="email"
name="email"
class="form-control"
required>

<br>

<button
class="btn btn-success">

Send Reset Link

</button>

</form>

</div>

</div>

</div>

<?php include 'includes/footer.php'; ?>
