<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole('buyer');

$userId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Fetch Buyer Information
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        fullname,
        email,
        phone,
        role,
        status,
        lga,
        town,
        created_at,
        last_login
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$userId]);

$buyer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$buyer) {
    die('Buyer account not found.');
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-4">

    <div class="row">

        <div class="col-12">

            <h2 class="fw-bold mb-1">
                My Profile
            </h2>

            <p class="text-muted mb-4">
                View and manage your account information.
            </p>

        </div>

    </div>
    <div class="row g-4">

    <!-- Profile Card -->
    <div class="col-lg-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body text-center">

                <div class="mb-3">

                    <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center"
                         style="width:100px;height:100px;font-size:42px;font-weight:bold;">

                        <?= strtoupper(substr($buyer['fullname'], 0, 1)) ?>

                    </div>

                </div>

                <h4 class="fw-bold mb-1">
                    <?= htmlspecialchars($buyer['fullname']) ?>
                </h4>

                <p class="text-muted mb-3">
                    Buyer Account
                </p>

                <?php
                switch ($buyer['status']) {

                    case 'active':
                        $badge = 'success';
                        break;

                    case 'blocked':
                        $badge = 'danger';
                        break;

                    default:
                        $badge = 'warning text-dark';
                }
                ?>

                <span class="badge bg-<?= $badge ?> px-3 py-2">

                    <?= ucfirst($buyer['status']) ?>

                </span>

            </div>

        </div>

    </div>

    <!-- Personal Information -->
    <div class="col-lg-8">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-success text-white">

                <h5 class="mb-0">

                    Personal Information

                </h5>

            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-6 mb-4">

                        <strong>Full Name</strong>

                        <div class="text-muted">

                            <?= htmlspecialchars($buyer['fullname']) ?>

                        </div>

                    </div>

                    <div class="col-md-6 mb-4">

                        <strong>Email Address</strong>

                        <div class="text-muted">

                            <?= htmlspecialchars($buyer['email']) ?>

                        </div>

                    </div>

                    <div class="col-md-6 mb-4">

                        <strong>Phone Number</strong>

                        <div class="text-muted">

                            <?= htmlspecialchars($buyer['phone']) ?>

                        </div>

                    </div>

                    <div class="col-md-6 mb-4">

                        <strong>Role</strong>

                        <div class="text-muted">

                            Buyer

                        </div>

                    </div>

                    <div class="col-md-6 mb-4">

                        <strong>Local Government Area</strong>

                        <div class="text-muted">

                            <?= htmlspecialchars($buyer['lga'] ?: 'Not Available') ?>

                        </div>

                    </div>

                    <div class="col-md-6 mb-4">

                        <strong>Town</strong>

                        <div class="text-muted">

                            <?= htmlspecialchars($buyer['town'] ?: 'Not Available') ?>

                        </div>

                    </div>

                    <div class="col-md-6 mb-4">

                        <strong>Member Since</strong>

                        <div class="text-muted">

                            <?= date('F d, Y', strtotime($buyer['created_at'])) ?>

                        </div>

                    </div>

                    <div class="col-md-6 mb-4">

                        <strong>Last Login</strong>

                        <div class="text-muted">

                            <?= !empty($buyer['last_login'])
                                ? date('F d, Y h:i A', strtotime($buyer['last_login']))
                                : 'Never Logged In'; ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
<!-- Account Actions -->
<div class="row mt-4">

    <div class="col-12">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-success text-white">

                <h5 class="mb-0">

                    Account Actions

                </h5>

            </div>

            <div class="card-body">

                <div class="row g-3">

                    <div class="col-md-6">

                        <a href="/projects/farmlink/edit_profile.php"
                           class="btn btn-success w-100">

                            <i class="bi bi-person-gear me-2"></i>

                            Edit Profile

                        </a>

                    </div>

                    <div class="col-md-6">

                        <a href="/projects/farmlink/change_password.php"
                           class="btn btn-outline-primary w-100">

                            <i class="bi bi-key me-2"></i>

                            Change Password

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

</div>

<?php include '../includes/footer.php'; ?>