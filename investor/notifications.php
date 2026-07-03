<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';

requireRole('investor');

$userId=
$_SESSION['user_id'];

$stmt=
$pdo->prepare("

SELECT *

FROM notifications

WHERE user_id=?

ORDER BY id DESC

");

$stmt->execute([

$userId

]);

$rows=
$stmt->fetchAll();

$pdo->prepare("

UPDATE notifications

SET status='read'

WHERE user_id=?

")->execute([

$userId

]);

include '../includes/header.php';
include '../includes/navbar.php';

?>

<div class="container mt-4">

<h2>

Notifications

</h2>

<?php foreach($rows as $n): ?>

<div class="card mb-3 shadow">

<div class="card-body">

<h5>

<?= htmlspecialchars(
$n['title']
) ?>

</h5>

<p>

<?= htmlspecialchars(
$n['message']
) ?>

</p>

<small>

<?= $n['created_at'] ?>

</small>

</div>

</div>

<?php endforeach; ?>

</div>

<?php include '../includes/footer.php'; ?>