<?php

require_once '../../includes/auth.php';
require_once '../../includes/roles.php';
require_once '../../config/database.php';

requireRole('lga_admin');

$userId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Get Admin LGA
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT lga
FROM users
WHERE id=?
LIMIT 1
");

$stmt->execute([$userId]);

$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    die("Unauthorized.");
}

$lga = $admin['lga'];

/*
|--------------------------------------------------------------------------
| Load Rejected Farmers
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT

u.id,
u.fullname,
u.phone,
u.email,
u.lga,
u.town,

fp.farm_name,
fp.farm_type,
fp.verified_at,
fp.rejection_reason,

verifier.fullname AS verified_by

FROM farmer_profiles fp

INNER JOIN users u
ON fp.user_id=u.id

LEFT JOIN users verifier
ON verifier.id=fp.verified_by

WHERE

fp.verification_status='rejected'

AND u.lga=?

ORDER BY fp.verified_at DESC
");

$stmt->execute([$lga]);

$farmers = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../includes/header.php';
include '../../includes/navbar.php';

?>

<div class="container py-4">

<h2 class="mb-4">

Rejected Farmers

</h2>
<?php

$totalRejected = count($farmers);

?>

<div class="row mb-4">

    <div class="col-md-4">

        <div class="card border-danger shadow-sm">

            <div class="card-body text-center">

                <h2 class="text-danger">

                    <?= $totalRejected ?>

                </h2>

                <h6 class="mb-0">

                    Rejected Farmers

                </h6>

            </div>

        </div>

    </div>

    <div class="col-md-4">

        <div class="card border-secondary shadow-sm">

            <div class="card-body text-center">

                <h2>

                    <?= htmlspecialchars($lga) ?>

                </h2>

                <h6 class="mb-0">

                    Current LGA

                </h6>

            </div>

        </div>

    </div>

    <div class="col-md-4">

        <div class="card border-warning shadow-sm">

            <div class="card-body text-center">

                <h2>

                    <?= date('d M Y') ?>

                </h2>

                <h6 class="mb-0">

                    Today's Date

                </h6>

            </div>

        </div>

    </div>

</div>

<?php if(isset($_GET['success'])): ?>

<div class="alert alert-success alert-dismissible fade show">

<?= htmlspecialchars($_GET['success']) ?>

<button
class="btn-close"
data-bs-dismiss="alert">
</button>

</div>

<?php endif; ?>

<div class="card shadow">

<div class="card-body">
    <?php if(empty($farmers)): ?>

<div class="alert alert-info">

No rejected farmers found.

</div>

<?php else: ?>

<div class="table-responsive">

<table class="table table-hover align-middle">

<thead class="table-danger">

<tr>

<th>Farmer</th>

<th>Farm</th>

<th>Phone</th>

<th>Town</th>

<th>Rejected On</th>

<th>Rejected By</th>

<th>Reason</th>

<th width="120">Action</th>

</tr>

</thead>

<tbody>

<?php foreach($farmers as $farmer): ?>

<tr>

<td>

<strong>

<?= htmlspecialchars($farmer['fullname']) ?>

</strong>

<br>

<small class="text-muted">

<?= htmlspecialchars($farmer['email']) ?>

</small>

</td>

<td>

<?= htmlspecialchars($farmer['farm_name'] ?: 'N/A') ?>

<br>

<small class="text-muted">

<?= htmlspecialchars($farmer['farm_type'] ?: '-') ?>

</small>

</td>

<td>

<?= htmlspecialchars($farmer['phone']) ?>

</td>

<td>

<?= htmlspecialchars($farmer['town']) ?>

</td>

<td>

<?= $farmer['verified_at']
    ? date('d M Y', strtotime($farmer['verified_at']))
    : '-' ?>

</td>

<td>

<?= htmlspecialchars($farmer['verified_by'] ?: '-') ?>

</td>

<td>

<?php

$reason = trim($farmer['rejection_reason']);

if(strlen($reason) > 50){

    echo htmlspecialchars(substr($reason,0,50));

    echo '...';

}else{

    echo htmlspecialchars($reason ?: '-');

}

?>

</td>

<td>

<a

href="review_farmer.php?id=<?= $farmer['id'] ?>"

class="btn btn-primary btn-sm">

View

</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php endif; ?>
</div>
</div>

</div>

<?php include '../../includes/footer.php'; ?>