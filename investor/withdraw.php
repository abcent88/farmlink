<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';
require_once '../includes/csrf.php';

requireRole('investor');

$userId=$_SESSION['user_id'];

$stmt=$pdo->prepare("
SELECT *
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

$message='';

if($_SERVER['REQUEST_METHOD']=='POST'){
verify_csrf();
if(
strtotime(
$investor['withdrawal_date']
)>time()
){

$message=
"Withdrawal unavailable until ".
$investor['withdrawal_date'];

}else{

$amount=
floatval(
$_POST['amount']
);

$stmt=$pdo->prepare("
INSERT INTO investor_withdrawals
(
investor_id,
amount
)
VALUES
(
?,
?
)
");

$stmt->execute([

$investor['id'],
$amount

]);

$message=
"Withdrawal request submitted";

}

}

include '../includes/header.php';
include '../includes/navbar.php';

?>

<div class="container mt-4">

<h2>

🏦 Withdrawal Request

</h2>

<?php if($message): ?>

<div class="alert alert-info">

<?= $message ?>

</div>

<?php endif; ?>

<div class="card shadow">

<div class="card-body">

<form method="POST">
<?= csrfField(); ?>
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
class="btn btn-danger">

Submit Request

</button>

</form>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>