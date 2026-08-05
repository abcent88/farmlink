<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../includes/csrf.php';
require_once '../includes/lgas.php';

requireRole('super_admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
}

$search = trim($_GET['search'] ?? '');
$roleFilter   = trim($_GET['role'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$lgaFilter    = trim($_GET['lga'] ?? '');

$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

$where = [];
$params = [];

if ($search !== '') {

    $where[] = "(
        fullname LIKE ?
        OR email LIKE ?
        OR phone LIKE ?
        OR town LIKE ?
    )";

    for ($i=0;$i<4;$i++) {
        $params[]="%{$search}%";
    }
}

if ($roleFilter !== '') {

    $where[]="role=?";
    $params[]=$roleFilter;

}

if ($statusFilter !== '') {

    $where[]="status=?";
    $params[]=$statusFilter;

}

if ($lgaFilter !== '') {

    $where[]="lga=?";
    $params[]=$lgaFilter;

}

$whereSql="";

if($where){

    $whereSql="WHERE ".implode(" AND ",$where);

}

/*
|--------------------------------------------------------------------------
| Total Records
|--------------------------------------------------------------------------
*/

$countSql="
SELECT COUNT(*)
FROM users
{$whereSql}
";

$stmt = $pdo->prepare($countSql);
$stmt->execute($params);

$totalUsers = (int)$stmt->fetchColumn();

$totalPages = max(1, ceil($totalUsers / $perPage));

/*
|--------------------------------------------------------------------------
| Load Users
|--------------------------------------------------------------------------
*/

$sql="
SELECT
id,
fullname,
email,
phone,
role,
status,
lga,
town,
last_login,
created_at
FROM users
{$whereSql}
ORDER BY id DESC
LIMIT {$perPage}
OFFSET {$offset}
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-4">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h2 class="mb-1">
👥 User Management
</h2>
 <?php

$totalUsers=(int)$pdo->query("
SELECT COUNT(*)
FROM users
")->fetchColumn();

$totalFarmers=(int)$pdo->query("
SELECT COUNT(*)
FROM users
WHERE role='farmer'
")->fetchColumn();

$totalBuyers=(int)$pdo->query("
SELECT COUNT(*)
FROM users
WHERE role='buyer'
")->fetchColumn();

$pendingUsers=(int)$pdo->query("
SELECT COUNT(*)
FROM users
WHERE status='pending'
")->fetchColumn();

?>

<div class="row mb-4">

<div class="col-md-3">

<div class="card text-bg-primary shadow">

<div class="card-body text-center">

<h3><?= number_format($totalUsers) ?></h3>

<small>Total Users</small>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card text-bg-success shadow">

<div class="card-body text-center">

<h3><?= number_format($totalFarmers) ?></h3>

<small>Farmers</small>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card text-bg-info shadow">

<div class="card-body text-center">

<h3><?= number_format($totalBuyers) ?></h3>

<small>Buyers</small>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card text-bg-warning shadow">

<div class="card-body text-center">

<h3><?= number_format($pendingUsers) ?></h3>

<small>Pending</small>

</div>

</div>

</div>

</div>

<p class="text-muted mb-0">
Manage registered users
</p>

</div>

<div>

<span class="badge bg-success fs-6">

Total Users:
<?= number_format($totalUsers) ?>

</span>

</div>

</div>

<div class="card shadow-sm mb-4">

<div class="card-body">

<form method="GET" class="card shadow-sm mb-4">

<div class="card-body">

<div class="row g-3">

<div class="col-lg-4">

<input
type="text"
name="search"
class="form-control"
placeholder="Search name, email, phone, town..."
value="<?= htmlspecialchars($search) ?>">

</div>

<div class="col-lg-2">

<select
name="role"
class="form-select">

<option value="">All Roles</option>

<?php

$roles = [
'super_admin'=>'Super Admin',
'lga_admin'=>'LGA Admin',
'farmer'=>'Farmer',
'buyer'=>'Buyer',
'trucker'=>'Trucker',
'investor'=>'Investor'
];

foreach($roles as $key=>$value):

?>

<option
value="<?= $key ?>"
<?= $roleFilter==$key?'selected':'' ?>>

<?= $value ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-lg-2">

<select
name="status"
class="form-select">

<option value="">All Status</option>

<option
value="active"
<?= $statusFilter=='active'?'selected':'' ?>>

Active

</option>

<option
value="pending"
<?= $statusFilter=='pending'?'selected':'' ?>>

Pending

</option>

<option
value="blocked"
<?= $statusFilter=='blocked'?'selected':'' ?>>

Blocked

</option>

</select>

</div>

<div class="col-lg-2">

<select
name="lga"
class="form-select">

<option value="">All LGAs</option>

<?php foreach($lgas as $row): ?>

<option
value="<?= htmlspecialchars($row['lga']) ?>"
<?= $lgaFilter==$row['lga']?'selected':'' ?>>

<?= htmlspecialchars($row['lga']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-lg-1">

<button
class="btn btn-success w-100">

<i class="bi bi-search"></i>

</button>

</div>

<div class="col-lg-1">

<a
href="users.php"
class="btn btn-outline-secondary w-100">

Reset

</a>

</div>

</div>

</div>

</form>
</div>

</div>

<div class="card shadow-sm">

<div class="table-responsive">

<table class="table table-hover align-middle mb-0">

<thead class="table-success">

<tr>

<th>#</th>
<th>Name</th>
<th>Email</th>
<th>Phone</th>
<th>Role</th>
<th>LGA</th>
<th>Town</th>
<th>Status</th>
<th>Last Login</th>
<th>Joined</th>
<th width="260">Actions</th>

</tr>

</thead>

<tbody>

<?php if(empty($users)): ?>

<tr>

<td colspan="8" class="text-center py-4">

No users found.

</td>

</tr>

<?php endif; ?>

<?php foreach($users as $user): ?>

<tr>

<td>

<?= $user['id'] ?>

</td>

<td>

<strong>

<?= htmlspecialchars($user['fullname']) ?>

</strong>

</td>

<td>

<?= htmlspecialchars($user['email']) ?>

</td>

<td>

<?= htmlspecialchars($user['phone']) ?>

</td>

<td>

<?php

$roleColors = [

'super_admin'=>'danger',
'lga_admin'=>'secondary',
'farmer'=>'success',
'buyer'=>'primary',
'trucker'=>'warning',
'investor'=>'info'

];

$roleNames = [

'super_admin'=>'Super Admin',
'lga_admin'=>'LGA Admin',
'farmer'=>'Farmer',
'buyer'=>'Buyer',
'trucker'=>'Trucker',
'investor'=>'Investor'

];

?>

<span class="badge bg-<?= $roleColors[$user['role']] ?? 'dark' ?>">

<?= $roleNames[$user['role']] ?? ucfirst($user['role']) ?>

</span>

</td>

<td>

<?= htmlspecialchars($user['lga'] ?: '-') ?>

</td>

<td>

<?= htmlspecialchars($user['town'] ?: '-') ?>

</td>

<td>

<?php

$statusColors = [

'active'=>'success',
'pending'=>'warning',
'blocked'=>'danger'

];

?>

<span class="badge bg-<?= $statusColors[$user['status']] ?? 'secondary' ?>">

<?= ucfirst($user['status']) ?>

</span>

</td>

<td>

<?php

if(!empty($user['last_login'])){

echo date('d M Y H:i', strtotime($user['last_login']));

}else{

echo '<span class="text-muted">Never</span>';

}

?>

</td>

<td>

<?= date('d M Y', strtotime($user['created_at'])) ?>

</td>
<td>

<div class="d-flex flex-wrap gap-1">

<form method="POST" action="approve_user.php">

<?= csrfField(); ?>

<input type="hidden" name="id" value="<?= $user['id'] ?>">

<button class="btn btn-success btn-sm">

Approve

</button>

</form>

<form method="POST" action="block_user.php">

<?= csrfField(); ?>

<input type="hidden" name="id" value="<?= $user['id'] ?>">

<button class="btn btn-warning btn-sm">

Block

</button>

</form>

<form method="POST" action="delete_user.php">

<?= csrfField(); ?>

<input type="hidden" name="id" value="<?= $user['id'] ?>">

<button
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this user permanently?')">

Delete

</button>

</form>

<a
href="reset_password.php?id=<?= $user['id'] ?>"
class="btn btn-primary btn-sm">

Reset Password

</a>

</div>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

<div class="d-flex justify-content-between align-items-center mt-4">

<div>

Page

<strong><?= $page ?></strong>

of

<strong><?= $totalPages ?></strong>

</div>

<div>

<?php if($page>1): ?>

<a
class="btn btn-outline-secondary"
href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">

← Previous

</a>

<?php endif; ?>

<?php if($page<$totalPages): ?>

<a
class="btn btn-success"
href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">

Next →

</a>

<?php endif; ?>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>