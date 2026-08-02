<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';
require_once '../includes/csrf.php';

requireRole('farmer');

$userId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Create Farmer Profile Automatically
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM farmer_profiles
    WHERE user_id = ?
");
$stmt->execute([$userId]);

if ($stmt->fetchColumn() == 0) {

    $stmt = $pdo->prepare("
        INSERT INTO farmer_profiles(user_id)
        VALUES(?)
    ");

    $stmt->execute([$userId]);
}

/*
|--------------------------------------------------------------------------
| Load User Information
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        fullname,
        email,
        phone,
        lga,
        town,
        status
    FROM users
    WHERE id=?
");

$stmt->execute([$userId]);

$user = $stmt->fetch();

/*
|--------------------------------------------------------------------------
| Load Farmer Profile
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM farmer_profiles
    WHERE user_id=?
");

$stmt->execute([$userId]);

$profile = $stmt->fetch();

$status = $profile['verification_status'] ?? 'pending';

function profileValue($value)
{
    return !empty($value)
        ? htmlspecialchars($value)
        : '<span class="text-muted">Not provided</span>';
}

include '../includes/header.php';
include '../includes/navbar.php';

?>

<div class="container py-4">

    <?php if (isset($_GET['submitted'])): ?>

        <div class="alert alert-success">

            Your verification request has been submitted successfully.
            An LGA Admin will review your profile shortly.

        </div>

    <?php endif; ?>

    <h2 class="mb-4">

        Farmer Profile

    </h2>

    <div class="row">

        <!-- LEFT COLUMN -->

        <div class="col-lg-4">

            <div class="card shadow-sm text-center">

                <div class="card-body">

                    <div
                        class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mx-auto mb-3"
                        style="width:150px;height:150px;font-size:60px;font-weight:bold;">

                        <?= strtoupper(substr($user['fullname'], 0, 1)); ?>

                    </div>

                    <h4>

                        <?= htmlspecialchars($user['fullname']); ?>

                    </h4>

                    <p class="text-muted mb-1">

                        <?= htmlspecialchars($user['email']); ?>

                    </p>

                    <p>

                        <?= htmlspecialchars($user['phone']); ?>

                    </p>

                    <?php if ($user['status'] == 'active'): ?>

                        <span class="badge bg-success">

                            Active Farmer

                        </span>

                    <?php else: ?>

                        <span class="badge bg-warning text-dark">

                            Pending Approval

                        </span>

                    <?php endif; ?>

                </div>

            </div>

        </div>

        <!-- RIGHT COLUMN -->

        <div class="col-lg-8">

            <div class="card shadow-sm">

                <div class="card-header bg-success text-white">

                    <h5 class="mb-0">

                        Farm Information

                    </h5>

                </div>

                <div class="card-body">

                    <table class="table table-bordered">

                        <tr>
                            <th width="35%">Farm Name</th>
                            <td><?= profileValue($profile['farm_name']); ?></td>
                        </tr>

                        <tr>
                            <th>Farm Type</th>
                            <td><?= profileValue($profile['farm_type']); ?></td>
                        </tr>

                        <tr>
                            <th>Farm Size</th>
                            <td>

                                <?= profileValue($profile['farm_size']); ?>

                                <?= htmlspecialchars($profile['farm_size_unit'] ?? ''); ?>

                            </td>
                        </tr>

                        <tr>
                            <th>Years of Experience</th>
                            <td><?= profileValue($profile['years_experience']); ?></td>
                        </tr>

                        <tr>
                            <th>LGA</th>
                            <td><?= profileValue($user['lga']); ?></td>
                        </tr>

                        <tr>
                            <th>Town</th>
                            <td><?= profileValue($user['town']); ?></td>
                        </tr>

                        <tr>
                            <th>Farm Address</th>
                            <td><?= profileValue($profile['farm_address']); ?></td>
                        </tr>

                        <tr>

                            <th>Verification</th>

                            <td>

                                <?php

                                if ($status == 'verified') {

                                    echo '<span class="badge bg-success">Verified Farmer ✓</span>';

                                } elseif ($status == 'rejected') {

                                    echo '<span class="badge bg-danger">Verification Rejected</span>';

                                } else {

                                    echo '<span class="badge bg-warning text-dark">Pending Verification</span>';

                                }

                                ?>

                            </td>

                        </tr>

                    </table>

                </div>

            </div>

            <br>

            <div class="card shadow-sm">

                <div class="card-header">

                    About Farm

                </div>

                <div class="card-body">

                    <?= profileValue($profile['about']); ?>

                </div>

            </div>

            <div class="d-flex flex-wrap gap-3 mt-4">

    <a
        href="/projects/farmlink/edit_profile.php"
        class="btn btn-success">

        <i class="bi bi-pencil-square"></i>
        Edit Profile

    </a>

    <a
        href="/projects/farmlink/change_password.php"
        class="btn btn-outline-dark">

        <i class="bi bi-key"></i>
        Change Password

    </a>

    <a
        href="products.php"
        class="btn btn-outline-success">

        <i class="bi bi-box-seam"></i>
        My Products

    </a>

    <a
        href="add_product.php"
        class="btn btn-primary">

        <i class="bi bi-plus-circle"></i>
        Add Product

    </a>

    <?php if ($status == 'verified'): ?>

        <button
            class="btn btn-success"
            disabled>

            <i class="bi bi-patch-check-fill"></i>
            Verified Farmer

        </button>

    <?php elseif ($status == 'pending'): ?>

        <button
            class="btn btn-warning"
            disabled>

            <i class="bi bi-hourglass-split"></i>
            Verification Pending

        </button>

    <?php else: ?>

        <form
            method="POST"
            action="submit_verification.php">

            <?= csrfField(); ?>

            <button
                type="submit"
                class="btn btn-warning">

                <i class="bi bi-send"></i>
                Submit for Verification

            </button>

        </form>

    <?php endif; ?>

</div>
        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>