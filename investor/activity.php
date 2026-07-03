<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';

requireRole('investor');

$stmt=
$pdo->prepare("

SELECT *

FROM activity_logs

WHERE user_id=?

ORDER BY id DESC

");

$stmt->execute([

$_SESSION['user_id']

]);

$logs=
$stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';

?>

<div class="container mt-4">

<h2>

Activity Log

</h2>

<table class="table">

<tr>

<th>Action</th>

<th>Details</th>

<th>Date</th>

</tr>

<?php foreach($logs as $l): ?>

<tr>

<td>

<?= htmlspecialchars(
$l['action']
) ?>

</td>

<td>

<?= htmlspecialchars(
$l['details']
) ?>

</td>

<td>

<?= $l['created_at'] ?>

</td>

</tr>

<?php endforeach; ?>

</table>

</div>

<?php include '../includes/footer.php'; ?>