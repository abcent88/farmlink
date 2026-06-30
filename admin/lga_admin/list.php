<?php

require_once '../../includes/auth.php';
require_once '../../includes/roles.php';
require_once '../../config/database.php';

requireRole('super_admin');

$stmt = $pdo->prepare("
    SELECT
        id,
        fullname,
        email,
        phone,
        lga,
        town,
        commission_rate,
        status,
        created_at
    FROM users
    WHERE role = 'lga_admin'
    ORDER BY id DESC
");

$stmt->execute();

$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../includes/header.php';
include '../../includes/navbar.php';

?>

<div class="container mt-5">


<div class="d-flex justify-content-between mb-3">

    <h2>LGA Administrators</h2>

    <a href="create.php"
       class="btn btn-success">
       Create LGA Admin
    </a>

</div>

<div class="card shadow">

    <div class="card-body">

        <?php if(count($admins) > 0): ?>

        <table class="table table-bordered table-striped">

            <thead>

            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>LGA</th>
                <th>Town</th>
                <th>Commission</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>

            </thead>

            <tbody>

            <?php foreach($admins as $admin): ?>

            <tr>

                <td><?= $admin['id'] ?></td>

                <td>
                    <?= htmlspecialchars($admin['fullname']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($admin['email']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($admin['phone']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($admin['lga']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($admin['town']) ?>
                </td>

                <td>
                    <?= number_format($admin['commission_rate'],2) ?>%
                </td>

                <td>

                    <?php if($admin['status'] == 'active'): ?>

                        <span class="badge bg-success">
                            Active
                        </span>

                    <?php else: ?>

                        <span class="badge bg-danger">
                            Inactive
                        </span>

                    <?php endif; ?>

                </td>

                <td>
                    <?= date(
                        'd M Y',
                        strtotime($admin['created_at'])
                    ) ?>
                </td>

                <td>

                    <a
href="edit.php?id=<?= $admin['id'] ?>"
class="btn btn-sm btn-primary">
Edit
</a>

<a
href="../block_user.php?id=<?= $admin['id'] ?>"
class="btn btn-sm btn-warning">

<?= $admin['status']=='active'
? 'Deactivate'
: 'Activate' ?>

</a>
                </td>

            </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

        <?php else: ?>

            <div class="alert alert-info">
                No LGA Admins found.
            </div>

        <?php endif; ?>

    </div>

</div>



<?php include '../../includes/footer.php'; ?>
