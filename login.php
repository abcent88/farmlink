<?php

require_once 'includes/auth.php';

require_once 'config/database.php';

require_once 'includes/logger.php';
require_once 'includes/csrf.php';


$message='';


if(
$_SERVER['REQUEST_METHOD']==='POST'
){
    verify_csrf();

$email=
trim(
$_POST['email']
);


$password=
$_POST['password'];


$stmt=
$pdo->prepare(

"

SELECT *

FROM users

WHERE email=?

LIMIT 1

"

);


$stmt->execute([
$email
]);


$user=
$stmt->fetch();


if($user){

if(
$user['status']!=='active'
){

$message=
'Account awaiting approval.';


}elseif(
password_verify(
$password,
$user['password']
)
){

session_regenerate_id(
true
);


$_SESSION['user_id']=
$user['id'];

$_SESSION['fullname']=
$user['fullname'];

$_SESSION['role']=
$user['role'];


/*
Log activity BEFORE redirect
*/

logActivity(

$pdo,

$user['id'],

'Login',

'User signed into FarmLink'

);


$redirect='index.php';


switch(
$user['role']
){

case 'super_admin':

$redirect=
'admin/dashboard.php';

break;


case 'lga_admin':

$redirect=
'admin/lga_admin/lga_admin_dashboard.php';

break;


case 'farmer':

$redirect=
'farmer/dashboard.php';

break;


case 'buyer':

$redirect=
'buyer/dashboard.php';

break;


case 'trucker':

$redirect=
'trucker/dashboard.php';

break;


case 'investor':

$redirect=
'investor/dashboard.php';

break;

}


header(
"Location: $redirect"
);

exit;


}else{

$message=
'Invalid password';

}


}else{

$message=
'User not found';

}

}

?>

<?php include 'includes/header.php'; ?>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-5">

<div class="card shadow">

<div class="card-header bg-success text-white">

<h3>

Login

</h3>

</div>

<div class="card-body">

<?php if($message): ?>

<div class="alert alert-warning">

<?= htmlspecialchars(
$message
) ?>

</div>

<?php endif; ?>

<form method="POST">
<?= csrfField(); ?>
<div class="mb-3">

<label>

Email

</label>

<input
type="email"
name="email"
class="form-control"
required>

</div>

<div class="mb-3">

<label>

Password

</label>

<input
type="password"
name="password"
class="form-control"
required>

</div>

<button
type="submit"
class="btn btn-success w-100">

Login

</button>

<div class="mt-3">

<a href="forgot_password.php">

Forgot Password?

</a>

</div>

</form>

</div>

</div>

</div>

</div>

</div>

<?php include 'includes/footer.php'; ?>
