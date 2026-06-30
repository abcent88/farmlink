<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';

requireRole('super_admin');

$lga = $_GET['lga'] ?? '';

$sql = "
SELECT
    u.id,
    u.fullname,
    u.lga,
    u.town,
    u.commission_rate,
    COUNT(ac.id) AS total_orders,
    COALESCE(SUM(ac.amount),0) AS total_commission
FROM users u
LEFT JOIN admin_commissions ac
    ON u.id = ac.admin_id
WHERE u.role='lga_admin'
";

$params = [];

if($lga != ''){

    $sql .= " AND u.lga = ? ";
    $params[] = $lga;
}

$sql .= "
GROUP BY u.id
ORDER BY total_commission DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

$lgas = $pdo->query("
    SELECT DISTINCT lga
    FROM users
    WHERE role='lga_admin'
    ORDER BY lga
")->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">

<h2>Commission Reports</h2>

<form method="GET" class="row mb-4">

<div class="col-md-4">

<select
    name="lga"
    class="form-control">

<option value="">
All LGAs
</option>

<?php foreach($lgas as $item): ?>

<option
value="<?= htmlspecialchars($item['lga']) ?>"
<?= $lga == $item['lga'] ? 'selected' : '' ?>>

<?= htmlspecialchars($item['lga']) ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div class="col-md-2">

<button class="btn btn-primary">
Filter
</button>

</div>

</form>

<table class="table table-bordered">

<thead>

<tr>
<th>Admin</th>
<th>LGA</th>
<th>Town</th>
<th>Rate</th>
<th>Orders</th>
<th>Total Commission</th>
</tr>

</thead>

<tbody>

<?php foreach($admins as $admin): ?>

<tr>

<td>
<?= htmlspecialchars($admin['fullname']) ?>
</td>

<td>
<?= htmlspecialchars($admin['lga']) ?>
</td>

<td>
<?= htmlspecialchars($admin['town']) ?>
</td>

<td>
<?= $admin['commission_rate'] ?>%
</td>

<td>
<?= $admin['total_orders'] ?>
</td>

<td>
₦<?= number_format($admin['total_commission'],2) ?>
</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php include '../includes/footer.php'; ?>