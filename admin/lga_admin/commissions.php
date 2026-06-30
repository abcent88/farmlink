<?php

require_once '../../includes/auth.php';
require_once '../../includes/roles.php';
require_once '../../config/database.php';

requireRole('lga_admin');

$stmt = $pdo->prepare("
    SELECT
        ac.id,
        ac.amount,
        ac.created_at,
        o.id AS order_id,
        p.product_name
    FROM admin_commissions ac
    JOIN orders o
        ON ac.order_id = o.id
    JOIN products p
        ON o.product_id = p.id
    WHERE ac.admin_id = ?
    ORDER BY ac.id DESC
");

$stmt->execute([
    $_SESSION['user_id']
]);

$commissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalStmt = $pdo->prepare("
    SELECT SUM(amount)
    FROM admin_commissions
    WHERE admin_id = ?
");

$totalStmt->execute([
    $_SESSION['user_id']
]);

$totalCommission = $totalStmt->fetchColumn();
$totalCommission = $totalCommission ?: 0;

include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container mt-5">

    <h2>My Commissions</h2>

    <div class="alert alert-success">
        <strong>Total Earned:</strong>
        ₦<?= number_format($totalCommission, 2) ?>
    </div>

    <table class="table table-bordered table-striped">

        <thead>
            <tr>
                <th>ID</th>
                <th>Order ID</th>
                <th>Product</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
        </thead>

        <tbody>

        <?php foreach($commissions as $commission): ?>

            <tr>

                <td><?= $commission['id'] ?></td>

                <td><?= $commission['order_id'] ?></td>

                <td>
                    <?= htmlspecialchars($commission['product_name']) ?>
                </td>

                <td>
                    ₦<?= number_format($commission['amount'], 2) ?>
                </td>

                <td>
                    <?= $commission['created_at'] ?>
                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

</div>

<?php include '../../includes/footer.php'; ?>