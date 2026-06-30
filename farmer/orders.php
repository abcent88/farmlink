<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole('farmer');

$stmt = $pdo->prepare("
    SELECT
        o.*,
        p.product_name,
        u.fullname AS buyer_name
    FROM orders o
    JOIN products p
        ON o.product_id = p.id
    JOIN users u
        ON o.buyer_id = u.id
    WHERE p.farmer_id = ?
    ORDER BY o.id DESC
");

$stmt->execute([
    $_SESSION['user_id']
]);

$orders = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">

```
<h2>Order Requests</h2>

<table class="table table-bordered">

    <thead>
        <tr>
            <th>ID</th>
            <th>Buyer</th>
            <th>Product</th>
            <th>Quantity</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>

    <?php foreach($orders as $order): ?>

        <tr>

            <td><?= $order['id'] ?></td>

            <td>
                <?= htmlspecialchars($order['buyer_name']) ?>
            </td>

            <td>
                <?= htmlspecialchars($order['product_name']) ?>
            </td>

            <td>
                <?= $order['quantity'] ?>
            </td>

            <td>
                <?php if($order['status'] == 'pending'): ?>

    <span class="badge bg-warning text-dark">
        Pending
    </span>

<<?php elseif($order['status'] == 'farmer_approved'): ?>

    <span class="badge bg-info">
        Awaiting LGA Approval
    </span>

<?php elseif($order['status'] == 'accepted'): ?>

    <span class="badge bg-primary">
        Approved By LGA
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

    <?php if($order['status'] == 'pending'): ?>

        <a
            href="accept_order.php?id=<?= $order['id'] ?>"
            class="btn btn-success btn-sm">
            Accept
        </a>

        <a
            href="reject_order.php?id=<?= $order['id'] ?>"
            class="btn btn-danger btn-sm">
            Reject
        </a>

    <?php elseif($order['status'] == 'farmer_approved'): ?>

    <button
        class="btn btn-secondary btn-sm"
        disabled>
        Awaiting LGA Approval
    </button>

<?php elseif($order['status'] == 'accepted'): ?>

    <a
        href="create_delivery.php?order_id=<?= $order['id'] ?>"
        class="btn btn-primary btn-sm">
        Assign Truck
    </a>

<?php endif; ?>

</td>
        </tr>

    <?php endforeach; ?>

    </tbody>

</table>
```

</div>

<?php include '../includes/footer.php'; ?>
