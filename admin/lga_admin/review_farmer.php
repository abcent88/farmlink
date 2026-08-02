<?php

require_once '../../includes/auth.php';
require_once '../../includes/roles.php';
require_once '../../config/database.php';
require_once '../../includes/csrf.php';

requireRole('lga_admin');

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
SELECT

u.id,
u.fullname,
u.email,
u.phone,
u.lga,
u.town,

fp.*

FROM users u

INNER JOIN farmer_profiles fp

ON u.id=fp.user_id

WHERE u.id=?

LIMIT 1
");

$stmt->execute([$id]);

$farmer = $stmt->fetch();

if (!$farmer) {

    die("Farmer not found.");

}

include '../../includes/header.php';
include '../../includes/navbar.php';

?>

<div class="container py-4">

<h2 class="mb-4">

Review Farmer

</h2>

<div class="row">

<div class="col-md-4">

<div class="card shadow-sm">

<div class="card-body text-center">

<div
class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mx-auto mb-3"
style="width:140px;height:140px;font-size:60px;">

<?= strtoupper(substr($farmer['fullname'],0,1)); ?>

</div>

<h4>

<?= htmlspecialchars($farmer['fullname']) ?>

</h4>

<p>

<?= htmlspecialchars($farmer['email']) ?>

</p>

<p>

<?= htmlspecialchars($farmer['phone']) ?>

</p>

</div>

</div>

</div>

<div class="col-md-8">

<div class="card shadow-sm">

<div class="card-header bg-success text-white">

Farm Information

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>
<th width="35%">Farm Name</th>
<td><?= htmlspecialchars($farmer['farm_name']) ?></td>
</tr>

<tr>
<th>Farm Type</th>
<td><?= htmlspecialchars($farmer['farm_type']) ?></td>
</tr>

<tr>
<th>Farm Size</th>
<td>

<?= htmlspecialchars($farmer['farm_size']) ?>

<?= htmlspecialchars($farmer['farm_size_unit']) ?>

</td>
</tr>

<tr>
<th>Experience</th>
<td><?= htmlspecialchars($farmer['years_experience']) ?></td>
</tr>

<tr>
<th>LGA</th>
<td><?= htmlspecialchars($farmer['lga']) ?></td>
</tr>

<tr>
<th>Town</th>
<td><?= htmlspecialchars($farmer['town']) ?></td>
</tr>

<tr>
<th>Address</th>
<td><?= htmlspecialchars($farmer['farm_address']) ?></td>
</tr>

<tr>
<th>About Farm</th>
<td><?= nl2br(htmlspecialchars($farmer['about'])) ?></td>
</tr>

<tr>
<th>Status</th>

<td>

<span class="badge bg-warning">

Pending Verification

</span>

</td>

</tr>

</table>

</div>

</div>

<br>

<div class="d-flex gap-3">

<form
method="POST"
action="verify_farmer.php">

<?= csrfField(); ?>

<input
type="hidden"
name="user_id"
value="<?= $farmer['user_id'] ?>">

<input
type="hidden"
name="action"
value="approve">

<button
class="btn btn-success">

Approve Farmer

</button>

</form>

<button
class="btn btn-danger"
data-bs-toggle="modal"
data-bs-target="#rejectModal">

Reject Farmer

</button>

</div>

</div>

</div>

</div>
<div
class="modal fade"
id="rejectModal"
tabindex="-1">

<div class="modal-dialog">

<div class="modal-content">

<form
method="POST"
action="verify_farmer.php">

<?= csrfField(); ?>

<input
type="hidden"
name="user_id"
value="<?= $farmer['user_id'] ?>">

<input
type="hidden"
name="action"
value="reject">

<div class="modal-header">

<h5 class="modal-title">

Reject Farmer

</h5>

<button
type="button"
class="btn-close"
data-bs-dismiss="modal"></button>

</div>

<div class="modal-body">

<label class="form-label">

Reason for rejection

</label>

<textarea
name="reason"
class="form-control"
rows="5"
required></textarea>

</div>

<div class="modal-footer">

<button
type="button"
class="btn btn-secondary"
data-bs-dismiss="modal">

Cancel

</button>

<button
type="submit"
class="btn btn-danger">

Reject Farmer

</button>

</div>

</form>

</div>

</div>

</div>

<?php include '../../includes/footer.php'; ?>