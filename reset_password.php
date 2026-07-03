<?php

require_once 'config/database.php';

$message='';

$token='';


if($_SERVER['REQUEST_METHOD']==='GET'){

$token=
$_GET['token']
?? '';

}


if($_SERVER['REQUEST_METHOD']==='POST'){

$token=
$_POST['token']
?? '';

$password=
trim(
$_POST['password']
);


$stmt=
$pdo->prepare("

SELECT
email

FROM password_resets

WHERE token=?

AND used=0

LIMIT 1

");

$stmt->execute([
$token
]);

$row=
$stmt->fetch();


if(!$row){

$message=
"Invalid reset link";

}else{

$stmt=
$pdo->prepare("

SELECT
expires_at

FROM password_resets

WHERE token=?

");

$stmt->execute([
$token
]);

$reset=
$stmt->fetch();


if(
strtotime(
$reset['expires_at']
)
<
time()
){

$message=
"Password reset link generated. Open below.";

$resetLink=
$link;

}else{


$newPassword=
password_hash(

$password,

PASSWORD_DEFAULT

);


$stmt=
$pdo->prepare("

UPDATE users

SET password=?

WHERE email=?

");

$stmt->execute([

$newPassword,

$row['email']

]);


$stmt=
$pdo->prepare("

UPDATE password_resets

SET used=1

WHERE token=?

");

$stmt->execute([
$token
]);


$message=
"Password updated successfully";

}

}

}


include 'includes/header.php';
include 'includes/navbar.php';

?>

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-5">

<div class="card shadow">

<div class="card-body">

<h3>

Reset Password

</h3>

<?php if(!empty($message)): ?>

<div class="alert alert-info">

<?= $message ?>

<br><br>

<?php if(!empty($resetLink)): ?>

<a
class="btn btn-success"

href="<?= $resetLink ?>">

Open Reset Page

</a>

<?php endif; ?>

</div>

<?php endif; ?>

<form method="POST">

<input
type="hidden"
name="token"
value="<?= htmlspecialchars($token) ?>">

<label>

New Password

</label>

<input
type="password"
name="password"
class="form-control"
required>

<br>

<button
class="btn btn-success">

Update Password

</button>

</form>

</div>

</div>

</div>

</div>

</div>

<?php include 'includes/footer.php'; ?>
