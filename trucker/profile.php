<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole(['trucker']);

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT
        fullname,
        email,
        phone,
        lga,
        town,
        truck_type,
        truck_capacity,
        load_capacity,
        capacity_unit,
        status,
        created_at
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$userId]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-4">

<div class="card border-0 shadow-sm">

<div class="card-header bg-success text-white">

<h4 class="mb-0">

My Profile

</h4>

</div>

<div class="card-body">
    <div class="row">

    <div class="col-md-6 mb-3">
        <strong>Full Name</strong>
        <div class="form-control bg-light">
            <?= htmlspecialchars($user['fullname']) ?>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <strong>Email Address</strong>
        <div class="form-control bg-light">
            <?= htmlspecialchars($user['email']) ?>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <strong>Phone Number</strong>
        <div class="form-control bg-light">
            <?= htmlspecialchars($user['phone']) ?>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <strong>Account Status</strong>
        <div class="form-control bg-light">
            <?php
            if ($user['status'] === 'active') {
                echo '<span class="badge bg-success">Active</span>';
            } elseif ($user['status'] === 'blocked') {
                echo '<span class="badge bg-danger">Blocked</span>';
            } else {
                echo '<span class="badge bg-warning text-dark">Pending</span>';
            }
            ?>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <strong>LGA</strong>
        <div class="form-control bg-light">
            <?= htmlspecialchars($user['lga'] ?: '-') ?>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <strong>Town</strong>
        <div class="form-control bg-light">
            <?= htmlspecialchars($user['town'] ?: '-') ?>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <strong>Truck Type</strong>
        <div class="form-control bg-light">
            <?= htmlspecialchars($user['truck_type'] ?: '-') ?>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <strong>Truck Capacity</strong>
        <div class="form-control bg-light">
            <?= htmlspecialchars($user['truck_capacity'] ?: '-') ?>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <strong>Load Capacity</strong>
        <div class="form-control bg-light">
            <?= htmlspecialchars($user['load_capacity'] ?: '-') ?>
            <?= htmlspecialchars($user['capacity_unit'] ?: '') ?>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <strong>Member Since</strong>
        <div class="form-control bg-light">
            <?= date('F d, Y', strtotime($user['created_at'])) ?>
        </div>
    </div>

</div>
<hr class="my-4">

<div class="row">

    <div class="col-md-6 mb-3">

        <a href="/projects/farmlink/edit_profile.php"
           class="btn btn-success w-100">

            <i class="bi bi-pencil-square me-2"></i>

            Edit Profile

        </a>

    </div>

    <div class="col-md-6 mb-3">

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

<?php include '../includes/footer.php'; ?>