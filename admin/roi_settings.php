<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';

requireRole('super_admin');

$message='';

if($_SERVER['REQUEST_METHOD']==='POST'){

$roi=
floatval(
$_POST['annual_roi']
);

$stmt=
$pdo->prepare("

UPDATE settings

SET setting_value=?

WHERE setting_key='annual_roi'

");

$stmt->execute([

$roi

]);

$message=
"ROI Updated";

}


$stmt=
$pdo->query("

SELECT setting_value

FROM settings

WHERE setting_key='annual_roi'

LIMIT 1

");

$current=
$stmt->fetchColumn();

include '../includes/header.php';
include '../includes/navbar.php';

?>

<div class="container mt-4">

<h2>

ROI Settings

</h2>

<?php if($message): ?>

<div class="alert alert-success">

<?= $message ?>

</div>

<?php endif; ?>

<div class="card shadow">

<div class="card-body">

<form method="POST">

<label>

Annual ROI (%)

</label>

<input
type="number"
step="0.01"
name="annual_roi"
class="form-control"
value="<?= $current ?>"
required>

<br>

<button
class="btn btn-success">

Save

</button>

</form>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>