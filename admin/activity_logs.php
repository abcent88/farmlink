<?php


require_once '../includes/auth.php';

require_once '../includes/roles.php';

require_once '../config/database.php';


requireRole('super_admin');


$stmt=
$pdo->query(

"

SELECT

a.*,

u.fullname

FROM activity_logs a

LEFT JOIN users u
ON a.user_id=u.id

ORDER BY a.id DESC

LIMIT 300

"

);


$logs=
$stmt->fetchAll();


include '../includes/header.php';

include '../includes/navbar.php';

?>

<div class="container mt-5">

<h2>

Activity Logs

</h2>

<table class="table table-bordered">

<tr>

<th>User</th>

<th>Action</th>

<th>Details</th>

<th>IP</th>

<th>Date</th>

</tr>

<?php foreach($logs as $l): ?>

<tr>

<td>

<?= htmlspecialchars(
$l['fullname']
?? 'SYSTEM'
) ?>

</td>

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

<?= $l['ip_address'] ?>

</td>

<td>

<?= $l['created_at'] ?>

</td>

</tr>

<?php endforeach; ?>

</table>

</div>

<?php include '../includes/footer.php'; ?>
