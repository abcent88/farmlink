<?php

require_once '../../includes/auth.php';
require_once '../../includes/roles.php';
require_once '../../config/database.php';

requireRole('lga_admin');

$lga = $_SESSION['lga'];

$search = trim($_GET['search'] ?? '');

$sql = "
SELECT
    u.id,
    u.fullname,
    u.phone,
    u.town,
    fp.farm_name,
    fp.verified_at,
    verifier.fullname AS verified_by
FROM farmer_profiles fp
INNER JOIN users u
    ON fp.user_id = u.id
LEFT JOIN users verifier
    ON fp.verified_by = verifier.id
WHERE
    fp.verification_status = 'verified'
    AND u.lga = ?
";

$params = [$lga];

if ($search !== '') {

    $sql .= "
    AND
    (
        u.fullname LIKE ?
        OR fp.farm_name LIKE ?
        OR u.phone LIKE ?
    )
    ";

    $like = "%{$search}%";

    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= "
ORDER BY
fp.verified_at DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$farmers = $stmt->fetchAll();

include '../../includes/header.php';
include '../../includes/navbar.php';

?>

<div class="container py-4">

<h2 class="mb-4">

Verified Farmers

</h2>

<form method="GET" class="row mb-4">

<div class="col-md-6">

<input
type="text"
name="search"
class="form-control"
placeholder="Search farmer..."
value="<?= htmlspecialchars($search) ?>">

</div>

<div class="col-md-2">

<button class="btn btn-success w-100">

Search

</button>

</div>

</form>

<div class="card shadow-sm">

<div class="card-body">

<?php if(empty($farmers)): ?>

<div class="alert alert-info">

No verified farmers found.

</div>

<?php else: ?>

<table class="table table-hover align-middle">

<thead class="table-success">

<tr>

<th>Farmer</th>

<th>Farm</th>

<th>Phone</th>

<th>Town</th>

<th>Verified On</th>

<th>Verified By</th>

<th></th>

</tr>

</thead>

<tbody>

<?php foreach($farmers as $farmer): ?>

<tr>

<td>

<?= htmlspecialchars($farmer['fullname']) ?>

</td>

<td>

<?= htmlspecialchars($farmer['farm_name']) ?>

</td>

<td>

<?= htmlspecialchars($farmer['phone']) ?>

</td>

<td>

<?= htmlspecialchars($farmer['town']) ?>

</td>

<td>

<?= date('d M Y', strtotime($farmer['verified_at'])) ?>

</td>

<td>

<?= htmlspecialchars($farmer['verified_by'] ?? '-') ?>

</td>

<td>

<a
href="review_farmer.php?id=<?= $farmer['id'] ?>"
class="btn btn-outline-success btn-sm">

View

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