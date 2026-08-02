<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole(['super_admin','lga_admin']);

$role   = $_SESSION['role'];
$userId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Logged In Admin
|--------------------------------------------------------------------------
*/

$admin = [];

if($role == 'lga_admin'){

    $stmt = $pdo->prepare("
        SELECT lga
        FROM users
        WHERE id=?
    ");

    $stmt->execute([$userId]);

    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

}

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

$lgaWhere = '';
$params = [];

if ($role === 'lga_admin') {
    $lgaWhere = " WHERE u.lga = ? ";
    $params[] = $admin['lga'];
}

/* Total Farmers */

$sql = "
SELECT COUNT(*)
FROM users
WHERE role='farmer'
";

$stmt = $pdo->prepare($sql);

$stmt->execute();

$totalFarmers = $stmt->fetchColumn();

/* Total Buyers */

$sql = "
SELECT COUNT(*)
FROM users
WHERE role='buyer'
";

$stmt = $pdo->prepare($sql);

$stmt->execute();

$totalBuyers = $stmt->fetchColumn();

/* Total Truckers */

$sql = "
SELECT COUNT(*)
FROM users
WHERE role='trucker'
";

$stmt = $pdo->prepare($sql);

$stmt->execute();

$totalTruckers = $stmt->fetchColumn();

/* Total Products */

$params = [];

$sql = "
SELECT COUNT(*)
FROM products p
JOIN users u
ON p.farmer_id=u.id
";

if ($role === 'lga_admin') {
    $sql .= " WHERE u.lga=?";
    $params[] = $admin['lga'];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$totalProducts = $stmt->fetchColumn();

/* Total Orders */

$params = [];

$sql = "
SELECT COUNT(*)
FROM orders o
JOIN users u
ON o.farmer_id=u.id
";

if ($role === 'lga_admin') {
    $sql .= " WHERE u.lga=?";
    $params[] = $admin['lga'];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$totalOrders = $stmt->fetchColumn();

/* Total Deliveries */

$params = [];

$sql = "
SELECT COUNT(*)
FROM deliveries d
JOIN orders o
ON d.order_id=o.id
JOIN users u
ON o.farmer_id=u.id
";

if ($role === 'lga_admin') {
    $sql .= " WHERE u.lga=?";
    $params[] = $admin['lga'];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$totalDeliveries = $stmt->fetchColumn();

/* Total Revenue */

$params = [];

$sql = "
SELECT COALESCE(SUM(o.total_amount),0)
FROM orders o
JOIN users u
ON o.farmer_id=u.id
WHERE o.status='completed'
";

if ($role === 'lga_admin') {
    $sql .= " AND u.lga=?";
    $params[] = $admin['lga'];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$totalRevenue = $stmt->fetchColumn();

/* Delivery Status */

$params = [];

$sql = "
SELECT
SUM(d.status='assigned') AS assigned,
SUM(d.status='approved') AS approved,
SUM(d.status='accepted') AS accepted,
SUM(d.status='in_transit') AS in_transit,
SUM(d.status='delivered') AS delivered,
SUM(d.status='completed') AS completed
FROM deliveries d
JOIN orders o
ON d.order_id=o.id
JOIN users u
ON o.farmer_id=u.id
";

if ($role === 'lga_admin') {
    $sql .= " WHERE u.lga=?";
    $params[] = $admin['lga'];
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$statusStats = $stmt->fetch(PDO::FETCH_ASSOC);

include '../includes/header.php';
include '../includes/navbar.php';
?>
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Reports Dashboard</h2>
            <p class="text-muted mb-0">
                FarmLink Platform Summary
            </p>
        </div>
    </div>

    <div class="row g-3">

        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h2 class="text-success fw-bold">
                        <?= number_format($totalFarmers) ?>
                    </h2>
                    <small class="text-muted">Farmers</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h2 class="text-primary fw-bold">
                        <?= number_format($totalBuyers) ?>
                    </h2>
                    <small class="text-muted">Buyers</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h2 class="text-warning fw-bold">
                        <?= number_format($totalTruckers) ?>
                    </h2>
                    <small class="text-muted">Truckers</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h2 class="text-success fw-bold">
                        <?= number_format($totalProducts) ?>
                    </h2>
                    <small class="text-muted">Products</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h2 class="text-info fw-bold">
                        <?= number_format($totalOrders) ?>
                    </h2>
                    <small class="text-muted">Orders</small>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h2 class="text-secondary fw-bold">
                        <?= number_format($totalDeliveries) ?>
                    </h2>
                    <small class="text-muted">Deliveries</small>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0 bg-success text-white">
                <div class="card-body text-center">
                    <h2 class="fw-bold">
                        ₦<?= number_format($totalRevenue,2) ?>
                    </h2>
                    <small>Total Revenue</small>
                </div>
            </div>
        </div>

    </div>

    <div class="card shadow-sm mt-4">

        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                Delivery Status Summary
            </h5>
        </div>

        <div class="card-body p-0">

            <table class="table table-bordered table-hover mb-0">

                <thead class="table-success">
                    <tr>
                        <th>Status</th>
                        <th width="150">Total</th>
                    </tr>
                </thead>

                <tbody>

                    <tr>
                        <td>Assigned</td>
                        <td><?= $statusStats['assigned'] ?? 0 ?></td>
                    </tr>

                    <tr>
                        <td>Approved</td>
                        <td><?= $statusStats['approved'] ?? 0 ?></td>
                    </tr>

                    <tr>
                        <td>Accepted</td>
                        <td><?= $statusStats['accepted'] ?? 0 ?></td>
                    </tr>

                    <tr>
                        <td>In Transit</td>
                        <td><?= $statusStats['in_transit'] ?? 0 ?></td>
                    </tr>

                    <tr>
                        <td>Delivered</td>
                        <td><?= $statusStats['delivered'] ?? 0 ?></td>
                    </tr>

                    <tr>
                        <td>Completed</td>
                        <td><?= $statusStats['completed'] ?? 0 ?></td>
                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>