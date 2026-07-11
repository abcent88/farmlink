<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';

requireRole('super_admin');

/*
|--------------------------------------------------------------------------
| User Statistics
|--------------------------------------------------------------------------
*/

$totalUsers = (int)$pdo->query("
    SELECT COUNT(*)
    FROM users
")->fetchColumn();

$totalFarmers = (int)$pdo->query("
    SELECT COUNT(*)
    FROM users
    WHERE role='farmer'
")->fetchColumn();

$totalBuyers = (int)$pdo->query("
    SELECT COUNT(*)
    FROM users
    WHERE role='buyer'
")->fetchColumn();

$pendingUsers = (int)$pdo->query("
    SELECT COUNT(*)
    FROM users
    WHERE status='pending'
")->fetchColumn();

/*
|--------------------------------------------------------------------------
| Product Statistics
|--------------------------------------------------------------------------
*/

$productStats = [
    'pending'  => 0,
    'approved' => 0,
    'rejected' => 0
];

$stmt = $pdo->query("
    SELECT status, COUNT(*) AS total
    FROM products
    GROUP BY status
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $productStats[$row['status']] = (int)$row['total'];
}

$totalProducts = (int)$pdo->query("
    SELECT COUNT(*)
    FROM products
")->fetchColumn();

$approvedProducts = (int)$pdo->query("
    SELECT COUNT(*)
    FROM products
    WHERE status='approved'
")->fetchColumn();

/*
|--------------------------------------------------------------------------
| Order Statistics
|--------------------------------------------------------------------------
*/

$orderStats = [
    'pending'   => 0,
    'accepted'  => 0,
    'completed' => 0,
    'rejected'  => 0
];

$stmt = $pdo->query("
    SELECT status, COUNT(*) AS total
    FROM orders
    GROUP BY status
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $orderStats[$row['status']] = (int)$row['total'];
}

$monthlyOrders = (int)$pdo->query("
    SELECT COUNT(*)
    FROM orders
    WHERE MONTH(created_at)=MONTH(CURDATE())
      AND YEAR(created_at)=YEAR(CURDATE())
")->fetchColumn();

/*
|--------------------------------------------------------------------------
| Platform Revenue
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        COALESCE(SUM(o.quantity * p.price * 0.05),0)
    FROM orders o
    INNER JOIN products p
        ON p.id = o.product_id
    WHERE o.status IN ('accepted','completed')
");

$platformRevenue = (float)$stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| Admin Commission
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(amount),0)
    FROM admin_commissions
    WHERE admin_id=?
");

$stmt->execute([
    $_SESSION['user_id']
]);

$totalCommission = (float)$stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| Investor Statistics
|--------------------------------------------------------------------------
*/

$totalInvestors = (int)$pdo->query("
    SELECT COUNT(*)
    FROM users
    WHERE role='investor'
")->fetchColumn();

$pendingWithdrawals = (int)$pdo->query("
    SELECT COUNT(*)
    FROM investor_withdrawals
    WHERE status='pending'
")->fetchColumn();

$investedCapital = (float)$pdo->query("
    SELECT COALESCE(SUM(invested_amount),0)
    FROM investors
")->fetchColumn();

/*
|--------------------------------------------------------------------------
| Today's Statistics
|--------------------------------------------------------------------------
*/

$todayUsers = (int)$pdo->query("
    SELECT COUNT(*)
    FROM users
    WHERE DATE(created_at)=CURDATE()
")->fetchColumn();

include '../includes/header.php';
include '../includes/navbar.php';
?>
<style>

.admin-card{
    border:none;
    border-radius:20px;
    padding:25px;
    min-height:190px;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    text-align:center;
    transition:.3s ease;
    box-shadow:0 .5rem 1rem rgba(0,0,0,.08);
}

.admin-card:hover{
    transform:translateY(-6px);
    box-shadow:0 .8rem 1.5rem rgba(0,0,0,.15);
}

.admin-icon{
    font-size:48px;
    margin-bottom:15px;
}

.admin-card h3,
.admin-card h4{
    margin-bottom:10px;
    font-weight:700;
}

.admin-card p{
    margin:0;
    font-size:15px;
}

.nav-link-card{
    text-decoration:none;
    color:inherit;
}

.nav-link-card:hover{
    color:inherit;
    text-decoration:none;
}

.dark-mode .admin-card{
    background:#1f2937;
    color:#fff;
}

</style>

<div class="container mt-4">

<h2 class="mb-1">
🛠 Super Admin Dashboard
</h2>

<p class="text-muted">
Welcome,
<strong><?= htmlspecialchars($_SESSION['fullname']) ?></strong>
</p>

<!-- ===========================
MAIN DASHBOARD CARDS
=========================== -->

<div class="row g-4">

<!-- USERS -->

<div class="col-lg-3 col-md-6">

<a href="users.php" class="nav-link-card">

<div class="admin-card bg-primary text-white">

<div class="admin-icon">

👥

</div>

<h3>

<?= number_format($totalUsers) ?>

</h3>

<p>

Users

</p>

</div>

</a>

</div>

<!-- PRODUCTS -->

<div class="col-lg-3 col-md-6">

<a href="products.php" class="nav-link-card">

<div class="admin-card bg-success text-white">

<div class="admin-icon">

🌽

</div>

<h3>

<?= number_format($productStats['approved']) ?>

</h3>

<p>

Approved Products

</p>

</div>

</a>

</div>

<!-- ORDERS -->

<div class="col-lg-3 col-md-6">

<a href="orders.php" class="nav-link-card">

<div class="admin-card bg-warning text-dark">

<div class="admin-icon">

🛒

</div>

<h3>

<?= number_format($orderStats['completed']) ?>

</h3>

<p>

Completed Orders

</p>

</div>

</a>

</div>

<!-- REVENUE -->

<div class="col-lg-3 col-md-6">

<a href="revenue.php" class="nav-link-card">

<div class="admin-card bg-dark text-white">

<div class="admin-icon">

💰

</div>

<h4>

₦<?= number_format($platformRevenue,2) ?>

</h4>

<p>

Platform Revenue

</p>

</div>

</a>

</div>

</div>

<br>
<!-- =======================================
ADMIN TOOLS
======================================= -->

<div class="row g-4 mt-2">

    <!-- Pending Users -->

    <div class="col-lg-3 col-md-6">

        <a href="pending_users.php" class="nav-link-card">

            <div class="admin-card bg-warning text-dark">

                <div class="admin-icon">
                    ⏳
                </div>

                <h3>
                    <?= number_format($pendingUsers) ?>
                </h3>

                <p>
                    Pending Users
                </p>

            </div>

        </a>

    </div>

    <!-- Commission -->

    <div class="col-lg-3 col-md-6">

        <a href="commissions.php" class="nav-link-card">

            <div class="admin-card bg-info text-white">

                <div class="admin-icon">
                    📈
                </div>

                <h4>

                    ₦<?= number_format($totalCommission,2) ?>

                </h4>

                <p>

                    Total Commission

                </p>

            </div>

        </a>

    </div>

    <!-- Financial Dashboard -->

    <div class="col-lg-3 col-md-6">

        <a href="financial_dashboard.php" class="nav-link-card">

            <div class="admin-card bg-primary text-white">

                <div class="admin-icon">
                    🏦
                </div>

                <h5>

                    Financial Dashboard

                </h5>

                <small>

                    View Reports

                </small>

            </div>

        </a>

    </div>

    <!-- LGA Admins -->

    <div class="col-lg-3 col-md-6">

        <a href="lga_admin/list.php" class="nav-link-card">

            <div class="admin-card bg-success text-white">

                <div class="admin-icon">
                    📍
                </div>

                <h5>

                    LGA Admins

                </h5>

                <small>

                    Manage LGAs

                </small>

            </div>

        </a>

    </div>

</div>


<!-- =======================================
SECOND ROW
======================================= -->

<div class="row g-4 mt-2">

    <!-- Generate Investor Earnings -->

    <div class="col-lg-4">

        <div class="admin-card bg-light">

            <div class="admin-icon">
                💸
            </div>

            <h5>

                Investor Earnings

            </h5>

            <a href="generate_investor_earnings.php"
               class="btn btn-success mt-3">

                Generate Earnings

            </a>

        </div>

    </div>

    <!-- ROI -->

    <div class="col-lg-4">

        <div class="admin-card bg-light">

            <div class="admin-icon">
                📊
            </div>

            <h5>

                ROI Settings

            </h5>

            <a href="roi_settings.php"
               class="btn btn-primary mt-3">

                Manage ROI

            </a>

        </div>

    </div>

    <!-- Activity Logs -->

    <div class="col-lg-4">

        <a href="activity_logs.php"
           class="nav-link-card">

            <div class="admin-card bg-secondary text-white">

                <div class="admin-icon">
                    🧾
                </div>

                <h5>

                    Activity Logs

                </h5>

                <small>

                    System Monitoring

                </small>

            </div>

        </a>

    </div>

</div>

<br>
<!-- ==========================================
ANALYTICS
========================================== -->

<h3 class="mt-5 mb-4">
📊 Platform Analytics
</h3>

<div class="row g-4">

    <!-- Farmers -->

    <div class="col-lg-3 col-md-6">

        <div class="admin-card bg-success text-white">

            <div class="admin-icon">
                👨‍🌾
            </div>

            <h3><?= number_format($totalFarmers) ?></h3>

            <p>Registered Farmers</p>

        </div>

    </div>

    <!-- Buyers -->

    <div class="col-lg-3 col-md-6">

        <div class="admin-card bg-primary text-white">

            <div class="admin-icon">
                🛒
            </div>

            <h3><?= number_format($totalBuyers) ?></h3>

            <p>Registered Buyers</p>

        </div>

    </div>

    <!-- Investors -->

    <div class="col-lg-3 col-md-6">

        <div class="admin-card bg-info text-white">

            <div class="admin-icon">
                🏦
            </div>

            <h3><?= number_format($totalInvestors) ?></h3>

            <p>Registered Investors</p>

        </div>

    </div>

    <!-- Pending Withdrawals -->

    <div class="col-lg-3 col-md-6">

        <div class="admin-card bg-warning text-dark">

            <div class="admin-icon">
                💵
            </div>

            <h3><?= number_format($pendingWithdrawals) ?></h3>

            <p>Pending Withdrawals</p>

        </div>

    </div>

</div>

<div class="row g-4 mt-2">

    <!-- Products -->

    <div class="col-lg-3 col-md-6">

        <div class="admin-card">

            <div class="admin-icon">
                🌽
            </div>

            <h3><?= number_format($totalProducts) ?></h3>

            <p>Total Products</p>

            <small class="text-success">
                Approved: <?= number_format($approvedProducts) ?>
            </small>

        </div>

    </div>

    <!-- Capital -->

    <div class="col-lg-3 col-md-6">

        <div class="admin-card bg-dark text-white">

            <div class="admin-icon">
                💰
            </div>

            <h4>

                ₦<?= number_format($investedCapital,2) ?>

            </h4>

            <p>Total Capital</p>

        </div>

    </div>

    <!-- Orders -->

    <div class="col-lg-3 col-md-6">

        <div class="admin-card bg-secondary text-white">

            <div class="admin-icon">
                📦
            </div>

            <h3><?= number_format($monthlyOrders) ?></h3>

            <p>This Month Orders</p>

        </div>

    </div>

    <!-- New Users -->

    <div class="col-lg-3 col-md-6">

        <div class="admin-card bg-danger text-white">

            <div class="admin-icon">
                🔥
            </div>

            <h3><?= number_format($todayUsers) ?></h3>

            <p>Today's New Users</p>

        </div>

    </div>

</div>

<br>
<!-- ==========================================
QUICK ACTIONS
========================================== -->

<div class="card shadow-lg border-0 mt-5">

    <div class="card-header bg-dark text-white">

        <h3 class="mb-0">
            ⚡ Quick Actions
        </h3>

    </div>

    <div class="card-body">

        <div class="row g-3">

            <div class="col-lg-3 col-md-4 col-sm-6">

                <a href="users.php" class="btn btn-success w-100 py-3">
                    👥<br>
                    Manage Users
                </a>

            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">

                <a href="pending_users.php" class="btn btn-warning w-100 py-3">
                    ⏳<br>
                    Pending Users
                </a>

            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">

                <a href="products.php" class="btn btn-primary w-100 py-3">
                    🌽<br>
                    Products
                </a>

            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">

                <a href="orders.php" class="btn btn-info w-100 py-3">
                    📦<br>
                    Orders
                </a>

            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">

                <a href="investors.php" class="btn btn-secondary w-100 py-3">
                    🏦<br>
                    Investors
                </a>

            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">

                <a href="withdrawals.php" class="btn btn-danger w-100 py-3">
                    💵<br>
                    Withdrawals
                </a>

            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">

                <a href="commissions.php" class="btn btn-dark w-100 py-3">
                    📈<br>
                    Commissions
                </a>

            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">

                <a href="financial_dashboard.php" class="btn btn-primary w-100 py-3">
                    💳<br>
                    Financial Dashboard
                </a>

            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">

                <a href="generate_investor_earnings.php" class="btn btn-success w-100 py-3">
                    💸<br>
                    Generate Earnings
                </a>

            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">

                <a href="roi_settings.php" class="btn btn-info w-100 py-3">
                    📊<br>
                    ROI Settings
                </a>

            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">

                <a href="activity_logs.php" class="btn btn-secondary w-100 py-3">
                    🧾<br>
                    Activity Logs
                </a>

            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">

                <a href="lga_admin/list.php" class="btn btn-success w-100 py-3">
                    📍<br>
                    LGA Admins
                </a>

            </div>

        </div>

    </div>

</div>

<br><br>

<?php include '../includes/footer.php'; ?>