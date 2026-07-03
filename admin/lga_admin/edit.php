<?php

require_once '../../includes/auth.php';
require_once '../../includes/roles.php';
require_once '../../config/database.php';

requireRole('super_admin');

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT *
    FROM users
    WHERE id = ?
    AND role = 'lga_admin'
");

$stmt->execute([$id]);

$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    appFail(
"Profile not found."
);
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $lga = trim($_POST['lga']);
    $town = trim($_POST['town']);
    $commission_rate = $_POST['commission_rate'];
    $status = $_POST['status'];

    $update = $pdo->prepare("
        UPDATE users
        SET
            fullname = ?,
            email = ?,
            phone = ?,
            lga = ?,
            town = ?,
            commission_rate = ?,
            status = ?
        WHERE id = ?
    ");

    $update->execute([
        $fullname,
        $email,
        $phone,
        $lga,
        $town,
        $commission_rate,
        $status,
        $id
    ]);

    $message = "LGA Admin updated successfully.";

    $stmt->execute([$id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
}

include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container mt-5">

    <h2>Edit LGA Admin</h2>

    <?php if($message): ?>
        <div class="alert alert-success">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-body">

            <form method="POST">

                <div class="mb-3">
                    <label>Full Name</label>
                    <input
                        type="text"
                        name="fullname"
                        class="form-control"
                        value="<?= htmlspecialchars($admin['fullname']) ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label>Email</label>
                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="<?= htmlspecialchars($admin['email']) ?>"
                        required>
                </div>

                <div class="mb-3">
                    <label>Phone</label>
                    <input
                        type="text"
                        name="phone"
                        class="form-control"
                        value="<?= htmlspecialchars($admin['phone']) ?>">
                </div>

                <div class="mb-3">
                    <label>LGA</label>
                    <input
                        type="text"
                        name="lga"
                        class="form-control"
                        value="<?= htmlspecialchars($admin['lga']) ?>">
                </div>

                <div class="mb-3">
                    <label>Town</label>
                    <input
                        type="text"
                        name="town"
                        class="form-control"
                        value="<?= htmlspecialchars($admin['town']) ?>">
                </div>

                <div class="mb-3">
                    <label>Commission Rate (%)</label>
                    <input
                        type="number"
                        step="0.01"
                        name="commission_rate"
                        class="form-control"
                        value="<?= $admin['commission_rate'] ?>">
                </div>

                <div class="mb-3">
                    <label>Status</label>

                    <select
                        name="status"
                        class="form-control">

                        <option value="active"
                            <?= $admin['status']=='active' ? 'selected' : '' ?>>
                            Active
                        </option>

                        <option value="inactive"
                            <?= $admin['status']=='inactive' ? 'selected' : '' ?>>
                            Inactive
                        </option>

                    </select>
                </div>

                <button
                    type="submit"
                    class="btn btn-primary">
                    Save Changes
                </button>

                <a
                    href="list.php"
                    class="btn btn-secondary">
                    Back
                </a>

            </form>

        </div>
    </div>

</div>

<?php include '../../includes/footer.php'; ?>