<?php

require_once '../../includes/auth.php';
require_once '../../includes/roles.php';
require_once '../../config/database.php';

requireRole('lga_admin');

$lga = $_SESSION['lga'];

$stmt = $pdo->prepare("
SELECT
    fp.id,
    fp.user_id,
    fp.farm_name,
    fp.submitted_at,
    u.fullname,
    u.phone,
    u.lga,
    u.town
FROM farmer_profiles fp
INNER JOIN users u
ON fp.user_id=u.id
WHERE
u.lga=?
AND fp.verification_status='pending'
ORDER BY fp.submitted_at ASC
");

$stmt->execute([$lga]);

$farmers = $stmt->fetchAll();

include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container py-4">

<h2 class="mb-4">

Pending Farmer Verifications

</h2>

<div class="card shadow">

<div class="card-body">

<?php if(empty($farmers)): ?>

<div class="alert alert-info">

No farmers are awaiting verification.

</div>

<?php else: ?>

<table class="table table-hover">

<thead class="table-success">

<tr>

<th>Farmer</th>

<th>Farm</th>

<th>Phone</th>

<th>Town</th>

<th>Submitted</th>

<th></th>

</tr>

</thead>

<tbody>

<?php foreach($farmers as $farmer): ?>

<tr>

<td><?= htmlspecialchars($farmer['fullname']) ?></td>

<td><?= htmlspecialchars($farmer['farm_name']) ?></td>

<td><?= htmlspecialchars($farmer['phone']) ?></td>

<td><?= htmlspecialchars($farmer['town']) ?></td>

<td><?= htmlspecialchars($farmer['submitted_at']) ?></td>

<td>

<a
class="btn btn-success btn-sm"
href="review_farmer.php?id=<?= $farmer['user_id'] ?>">

Review

</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<?php endif; ?>

</div>

</div>

</div>

<?php include '../../includes/footer.php'; ?>