<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole('trucker');

$stmt = $pdo->prepare("
    SELECT
        d.*,
        p.product_name
    FROM deliveries d
    JOIN orders o
        ON d.order_id = o.id
    JOIN products p
        ON o.product_id = p.id
    WHERE d.status = 'open'
    ORDER BY d.id DESC
");

$stmt->execute();

$deliveries = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">

```
<h2>Available Deliveries</h2>

<table class="table table-bordered">

    <thead>
        <tr>
            <th>ID</th>
            <th>Product</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>

    <?php foreach($deliveries as $delivery): ?>

        <tr>

            <td><?= $delivery['id'] ?></td>

            <td><?= htmlspecialchars($delivery['product_name']) ?></td>

            <td><?php if($delivery['status'] == 'open'): ?>

    <span class="badge bg-warning text-dark">
        Open
    </span>

<?php elseif($delivery['status'] == 'accepted'): ?>

    <span class="badge bg-info">
        Accepted
    </span>

<?php elseif($delivery['status'] == 'completed'): ?>

    <span class="badge bg-success">
        Completed
    </span>

<?php endif; ?></td>

            <td>

                <a
                    href="accept_delivery.php?id=<?= $delivery['id'] ?>"
                    class="btn btn-success btn-sm">
                    Accept Delivery
                </a>

            </td>

        </tr>

    <?php endforeach; ?>

    </tbody>

</table>
```

</div>

<?php include '../includes/footer.php'; ?>
