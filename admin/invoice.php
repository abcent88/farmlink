<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole('super_admin');

$orderId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT
        o.id,
        o.quantity,
        o.status,
        o.created_at,

        b.fullname AS buyer_name,
        f.fullname AS farmer_name,

        p.product_name,
        p.price

    FROM orders o

    JOIN users b
        ON o.buyer_id = b.id

    JOIN products p
        ON o.product_id = p.id

    JOIN users f
        ON p.farmer_id = f.id

    WHERE o.id = ?
");

$stmt->execute([$orderId]);

$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    appError(
$e->getMessage()
);

appFail();
}

$totalValue = $order['quantity'] * $order['price'];
$platformFee = $totalValue * 0.05;

include '../includes/header.php';
?>

<div class="container mt-5">

```
<div class="card p-4 shadow">

    <h2>Order Invoice #<?= $order['id'] ?></h2>

    <hr>

    <p><strong>Buyer:</strong>
    <?= htmlspecialchars($order['buyer_name']) ?></p>

    <p><strong>Farmer:</strong>
    <?= htmlspecialchars($order['farmer_name']) ?></p>

    <p><strong>Product:</strong>
    <?= htmlspecialchars($order['product_name']) ?></p>

    <p><strong>Quantity:</strong>
    <?= $order['quantity'] ?></p>

    <p><strong>Unit Price:</strong>
    ₦<?= number_format($order['price'],2) ?></p>

    <p><strong>Total Value:</strong>
    ₦<?= number_format($totalValue,2) ?></p>

    <p><strong>Platform Fee:</strong>
    ₦<?= number_format($platformFee,2) ?></p>

    <p><strong>Status:</strong>
    <?= htmlspecialchars($order['status']) ?></p>

    <p><strong>Date:</strong>
    <?= $order['created_at'] ?></p>

    <button onclick="window.print()"
            class="btn btn-primary">
        Print Invoice
    </button>

</div>
```

</div>

<?php include '../includes/footer.php'; ?>
