<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';

requireRole('super_admin');

$status = $_GET['status'] ?? '';

$sql = "
    SELECT
        o.id,
        o.quantity,
        o.status,
        o.created_at,

        b.fullname AS buyer_name,

        p.product_name,
        p.price,

        f.fullname AS farmer_name

    FROM orders o

    JOIN users b
        ON o.buyer_id = b.id

    JOIN products p
        ON o.product_id = p.id

    JOIN users f
        ON p.farmer_id = f.id
";

$params = [];

if (!empty($status)) {

    $sql .= " WHERE o.status = ? ";

    $params[] = $status;
}

$sql .= " ORDER BY o.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$orders = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">

    <h1>Manage Orders</h1>

    <div class="mb-4">

        <a href="orders.php"
           class="btn btn-secondary btn-sm">
            All
        </a>

        <a href="orders.php?status=pending"
           class="btn btn-warning btn-sm">
            Pending
        </a>

        <a href="orders.php?status=accepted"
           class="btn btn-info btn-sm">
            Accepted
        </a>

        <a href="orders.php?status=completed"
           class="btn btn-success btn-sm">
            Completed
        </a>

        <a href="orders.php?status=rejected"
           class="btn btn-danger btn-sm">
            Rejected
        </a>

    </div>

    <table class="table table-bordered table-striped">

        <thead>

            <tr>

                <th>ID</th>
                <th>Buyer</th>
                <th>Farmer</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Total Value</th>
                <th>Platform Fee (5%)</th>
                <th>Status</th>
                <th>Date</th>

            </tr>

        </thead>

        <tbody>

        <?php foreach($orders as $order): ?>

            <?php

            $totalValue =
                $order['quantity'] * $order['price'];

            $fee =
                $totalValue * 0.05;

            ?>

            <tr>

                <td><?= $order['id'] ?></td>

                <td>
                    <?= htmlspecialchars($order['buyer_name']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($order['farmer_name']) ?>
                </td>

                <td>
                    <?= htmlspecialchars($order['product_name']) ?>
                </td>

                <td>
                    <?= $order['quantity'] ?>
                </td>

                <td>
                    ₦<?= number_format($totalValue, 2) ?>
                </td>

                <td>
                    ₦<?= number_format($fee, 2) ?>
                </td>

                <td>

                    <?php if($order['status'] == 'pending'): ?>

                        <span class="badge bg-warning text-dark">
                            Pending
                        </span>

                    <?php elseif($order['status'] == 'accepted'): ?>

                        <span class="badge bg-info">
                            Accepted
                        </span>

                    <?php elseif($order['status'] == 'rejected'): ?>

                        <span class="badge bg-danger">
                            Rejected
                        </span>

                    <?php elseif($order['status'] == 'completed'): ?>

                        <span class="badge bg-success">
                            Completed
                        </span>

                    <?php endif; ?>

                </td>

                <td>
                    <?= $order['created_at'] ?>
                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

</div>

<?php include '../includes/footer.php'; ?>