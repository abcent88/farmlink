<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../includes/csrf.php';
require_once '../includes/lgas.php';

requireRole(['super_admin', 'lga_admin']);

$role   = $_SESSION['role'];
$userId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Logged In Admin
|--------------------------------------------------------------------------
*/

$admin = [];

if ($role === 'lga_admin') {

    $stmt = $pdo->prepare("
        SELECT lga
        FROM users
        WHERE id = ?
    ");

    $stmt->execute([$userId]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
}

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

$search       = trim($_GET['search'] ?? '');
$verification = trim($_GET['verification'] ?? '');
$farmType     = trim($_GET['farm_type'] ?? '');
$lga          = trim($_GET['lga'] ?? '');

$params = [];
$where  = [];

/*
|--------------------------------------------------------------------------
| Main Query
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

u.id,

u.fullname,

u.email,

u.phone,

u.lga,

u.town,

u.status,

u.created_at,

fp.farm_name,

fp.farm_type,

fp.farm_size,

fp.farm_size_unit,

fp.years_experience,

fp.farm_address,

fp.about,

fp.profile_photo,

fp.cover_photo,

fp.facebook,

fp.instagram,

fp.twitter,

fp.website,

fp.verification_status,

fp.verified_at,

fp.rejection_reason,

COUNT(p.id) AS total_products,

SUM(
CASE
WHEN p.status='approved'
THEN 1
ELSE 0
END
) AS approved_products,

SUM(
CASE
WHEN p.status='pending'
THEN 1
ELSE 0
END
) AS pending_products,

SUM(
CASE
WHEN p.status='rejected'
THEN 1
ELSE 0
END
) AS rejected_products

FROM users u

LEFT JOIN farmer_profiles fp
ON fp.user_id=u.id

LEFT JOIN products p
ON p.farmer_id=u.id

WHERE u.role='farmer'

";

/*
|--------------------------------------------------------------------------
| LGA Restriction
|--------------------------------------------------------------------------
*/

if ($role === 'lga_admin') {

    $where[] = "u.lga=?";

    $params[] = $admin['lga'];
}

/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

if ($search !== '') {

    $where[] = "(
        u.fullname LIKE ?
        OR u.email LIKE ?
        OR u.phone LIKE ?
        OR fp.farm_name LIKE ?
    )";

    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

/*
|--------------------------------------------------------------------------
| Verification Filter
|--------------------------------------------------------------------------
*/

if ($verification !== '') {

    $where[] = "fp.verification_status=?";

    $params[] = $verification;
}

/*
|--------------------------------------------------------------------------
| Farm Type Filter
|--------------------------------------------------------------------------
*/

if ($farmType !== '') {

    $where[] = "fp.farm_type=?";

    $params[] = $farmType;
}

/*
|--------------------------------------------------------------------------
| LGA Filter
|--------------------------------------------------------------------------
*/

if ($role === 'super_admin' && $lga !== '') {

    $where[] = "u.lga=?";

    $params[] = $lga;
}

if (!empty($where)) {

    $sql .= " AND " . implode(" AND ", $where);
}

$sql .= "

GROUP BY u.id

ORDER BY u.id DESC

";

$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$farmers = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

$statsSql = "

SELECT

COUNT(*) AS total_farmers,

SUM(
CASE
WHEN fp.verification_status='verified'
THEN 1
ELSE 0
END
) AS verified_farmers,

SUM(
CASE
WHEN fp.verification_status='pending'
THEN 1
ELSE 0
END
) AS pending_farmers,

SUM(
CASE
WHEN fp.verification_status='rejected'
THEN 1
ELSE 0
END
) AS rejected_farmers

FROM users u

LEFT JOIN farmer_profiles fp
ON fp.user_id=u.id

WHERE u.role='farmer'

";

$statsParams = [];

if ($role === 'lga_admin') {

    $statsSql .= " AND u.lga=?";

    $statsParams[] = $admin['lga'];
}

$statsStmt = $pdo->prepare($statsSql);

$statsStmt->execute($statsParams);

$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

$totalFarmers    = $stats['total_farmers'] ?? 0;
$verifiedFarmers = $stats['verified_farmers'] ?? 0;
$pendingFarmers  = $stats['pending_farmers'] ?? 0;
$rejectedFarmers = $stats['rejected_farmers'] ?? 0;

/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

include '../includes/header.php';
include '../includes/navbar.php';

?>
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="fw-bold mb-1">
                Farmer Management
            </h2>

            <p class="text-muted mb-0">
                Manage farmer accounts, verification and farm information.
            </p>

        </div>

    </div>

    <!-- Dashboard Cards -->

    <div class="row g-3 mb-4">

        <div class="col-lg-3 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body text-center">

                    <h2 class="fw-bold text-success">
                        <?= number_format($totalFarmers) ?>
                    </h2>

                    <div class="text-muted">
                        Total Farmers
                    </div>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body text-center">

                    <h2 class="fw-bold text-primary">
                        <?= number_format($verifiedFarmers) ?>
                    </h2>

                    <div class="text-muted">
                        Verified
                    </div>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body text-center">

                    <h2 class="fw-bold text-warning">
                        <?= number_format($pendingFarmers) ?>
                    </h2>

                    <div class="text-muted">
                        Pending Verification
                    </div>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body text-center">

                    <h2 class="fw-bold text-danger">
                        <?= number_format($rejectedFarmers) ?>
                    </h2>

                    <div class="text-muted">
                        Rejected
                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- Search -->

    <form method="GET" class="card shadow-sm mb-4">

        <div class="card-body">

            <div class="row g-3">

                <div class="col-lg-3">

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search farmer..."
                        value="<?= htmlspecialchars($search) ?>">

                </div>

                <div class="col-lg-2">

                    <select
                        name="verification"
                        class="form-select">

                        <option value="">
                            Verification
                        </option>

                        <option value="verified"
                            <?= $verification=='verified'?'selected':'' ?>>
                            Verified
                        </option>

                        <option value="pending"
                            <?= $verification=='pending'?'selected':'' ?>>
                            Pending
                        </option>

                        <option value="rejected"
                            <?= $verification=='rejected'?'selected':'' ?>>
                            Rejected
                        </option>

                    </select>

                </div>

                <div class="col-lg-2">

                    <input
                        type="text"
                        name="farm_type"
                        class="form-control"
                        placeholder="Farm Type"
                        value="<?= htmlspecialchars($farmType) ?>">

                </div>

                <?php if($role=='super_admin'): ?>

                <div class="col-lg-2">

    <select
        name="lga"
        class="form-select">

        <option value="">
            All LGAs
        </option>

        <?php foreach ($lgas as $row): ?>

            <option
                value="<?= htmlspecialchars($row['lga']) ?>"
                <?= ($lga === $row['lga']) ? 'selected' : '' ?>>

                <?= htmlspecialchars($row['lga']) ?>

            </option>

        <?php endforeach; ?>

    </select>

</div>

                <?php endif; ?>

                <div class="col-lg-2">

                    <button
                        class="btn btn-success w-100">

                        Search

                    </button>

                </div>

                <div class="col-lg-1">

                    <a
                        href="farmers.php"
                        class="btn btn-outline-secondary w-100">

                        Reset

                    </a>

                </div>

            </div>

        </div>

    </form>
    <div class="card shadow-sm">

    <div class="card-header bg-success text-white">

        <h5 class="mb-0">
            Registered Farmers
        </h5>

    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover table-bordered align-middle mb-0">

                <thead class="table-success">

                <tr>

                    <th>ID</th>

                    <th>Photo</th>

                    <th>Farmer</th>

                    <th>Farm</th>

                    <th>Products</th>

                    <th>LGA</th>

                    <th>Phone</th>

                    <th>Verification</th>

                    <th>Joined</th>

                    <th width="120">
                        Action
                    </th>

                </tr>

                </thead>

                <tbody>

                <?php if(empty($farmers)): ?>

                    <tr>

                        <td colspan="10" class="text-center text-muted py-5">

                            No farmers found.

                        </td>

                    </tr>

                <?php endif; ?>

                <?php foreach($farmers as $farmer):

                    $profilePhoto = !empty($farmer['profile_photo'])
                        ? '../uploads/profile/' . $farmer['profile_photo']
                        : '../assets/images/default-avatar.png';

                    $verificationBadge = match($farmer['verification_status']){

                        'verified' =>
                            '<span class="badge bg-success">Verified</span>',

                        'rejected' =>
                            '<span class="badge bg-danger">Rejected</span>',

                        default =>
                            '<span class="badge bg-warning text-dark">Pending</span>'

                    };

                ?>

                <tr>

                    <td>

                        <?= $farmer['id'] ?>

                    </td>

                    <td>

                        <img

                            src="<?= htmlspecialchars($profilePhoto) ?>"

                            class="rounded-circle border"

                            style="width:60px;height:60px;object-fit:cover;"

                            loading="lazy"

                            alt="<?= htmlspecialchars($farmer['fullname']) ?>">

                    </td>

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

                        <strong>

                            <?= htmlspecialchars($farmer['farm_name'] ?: 'Not Provided') ?>

                        </strong>

                        <br>

                        <small class="text-muted">

                            <?= htmlspecialchars($farmer['farm_type'] ?: '-') ?>

                        </small>

                    </td>

                    <td>

                        <span class="badge bg-primary">

                            <?= $farmer['total_products'] ?>

                        </span>

                        <br>

                        <small class="text-success">

                            Approved:
                            <?= $farmer['approved_products'] ?>

                        </small>

                        <br>

                        <small class="text-warning">

                            Pending:
                            <?= $farmer['pending_products'] ?>

                        </small>

                    </td>

                    <td>

                        <?= htmlspecialchars($farmer['lga']) ?>

                    </td>

                    <td>

                        <?= htmlspecialchars($farmer['phone']) ?>

                    </td>

                    <td>

                        <?= $verificationBadge ?>

                    </td>

                    <td>

                        <?= date('d M Y', strtotime($farmer['created_at'])) ?>

                    </td>

                    <td>

                        <button

                            class="btn btn-success btn-sm"

                            data-bs-toggle="modal"

                            data-bs-target="#farmerModal<?= $farmer['id'] ?>">

                            View

                        </button>

                    </td>

                </tr>


                <?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</div>

<!-- ===========================
     FARMER MODALS
============================ -->

<?php foreach($farmers as $farmer):

$profilePhoto = !empty($farmer['profile_photo'])
    ? '../uploads/profile/'.$farmer['profile_photo']
    : '../assets/images/default-avatar.png';

$verificationBadge = match($farmer['verification_status']){

    'verified' =>
        '<span class="badge bg-success">Verified</span>',

    'rejected' =>
        '<span class="badge bg-danger">Rejected</span>',

    default =>
        '<span class="badge bg-warning text-dark">Pending</span>'

};

?>

<div
class="modal fade"
id="farmerModal<?= $farmer['id'] ?>"
tabindex="-1"
aria-hidden="true">

<div class="modal-dialog modal-xl modal-dialog-scrollable">

<div class="modal-content">

<div class="modal-header bg-success text-white">

<h4 class="modal-title">

Farmer Profile

</h4>

<button
type="button"
class="btn-close btn-close-white"
data-bs-dismiss="modal">
</button>

</div>

<div class="modal-body">

<div class="row">

<div class="col-lg-4 text-center">

<img
src="<?= htmlspecialchars($profilePhoto) ?>"
class="img-fluid rounded shadow mb-3"
style="max-height:300px;object-fit:cover;"
alt="<?= htmlspecialchars($farmer['fullname']) ?>">

<h4 class="fw-bold">

<?= htmlspecialchars($farmer['fullname']) ?>

</h4>

<p class="text-muted">

<?= htmlspecialchars($farmer['farm_name'] ?: 'No Farm Name') ?>

</p>

<?= $verificationBadge ?>

</div>

<div class="col-lg-8">

<h5 class="border-bottom pb-2">Personal Information</h5>

<div class="row">

<div class="col-md-6 mb-3">
<strong>Email</strong><br>
<?= htmlspecialchars($farmer['email']) ?>
</div>

<div class="col-md-6 mb-3">
<strong>Phone</strong><br>
<?= htmlspecialchars($farmer['phone']) ?>
</div>

<select name="lga" class="form-control" required>

<option value="">Select LGA</option>

<?php foreach ($lgas as $row): ?>

<option
    value="<?= htmlspecialchars($row['lga']) ?>"
    <?= (($currentLga ?? '') == $row['lga']) ? 'selected' : '' ?>>

    <?= htmlspecialchars($row['lga']) ?>

</option>

<?php endforeach; ?>

</select>

<div class="col-md-6 mb-3">
<strong>Town</strong><br>
<?= htmlspecialchars($farmer['town']) ?>
</div>

</div>

<hr>

<h5 class="border-bottom pb-2">Farm Information</h5>

<div class="row">

<div class="col-md-6 mb-3">
<strong>Farm Name</strong><br>
<?= htmlspecialchars($farmer['farm_name'] ?: 'Not Provided') ?>
</div>

<div class="col-md-6 mb-3">
<strong>Farm Type</strong><br>
<?= htmlspecialchars($farmer['farm_type'] ?: 'Not Provided') ?>
</div>

<div class="col-md-6 mb-3">
<strong>Farm Size</strong><br>

<?php if($farmer['farm_size']): ?>

<?= number_format($farmer['farm_size'],2) ?>

<?= htmlspecialchars($farmer['farm_size_unit']) ?>

<?php else: ?>

Not Provided

<?php endif; ?>

</div>

<div class="col-md-6 mb-3">

<strong>Experience</strong><br>

<?= (int)$farmer['years_experience'] ?> Years

</div>

<div class="col-12">

<strong>Farm Address</strong><br>

<?= nl2br(htmlspecialchars($farmer['farm_address'] ?: 'Not Provided')) ?>

</div>

</div>

<hr>

<h5 class="border-bottom pb-2">About Farmer</h5>

<p>

<?= nl2br(htmlspecialchars($farmer['about'] ?: 'No information provided.')) ?>

</p>

</div>

</div>

</div>

<div class="modal-footer">

<button
type="button"
class="btn btn-secondary"
data-bs-dismiss="modal">

Close

</button>

</div>

</div>

</div>

</div>

<?php endforeach; ?>

<?php include '../includes/footer.php'; ?>