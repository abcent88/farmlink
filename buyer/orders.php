<?php
require_once '../includes/auth.php';
require_once '../includes/roles.php';
requireRole('buyer');
require_once '../config/database.php';

$buyerId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT 
        o.*,
        p.product_name,
        p.price,
        u.fullname AS farmer_name
    FROM orders o
    JOIN products p ON o.product_id = p.id
    JOIN users u ON p.farmer_id = u.id
    WHERE o.buyer_id = ?
    ORDER BY o.id DESC
");
$stmt->execute([$buyerId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">

<h2>My Orders</h2>

<table class="table table-bordered table-striped">
    <thead class="table-light">
        <tr>
            <th>Order ID</th>
            <th>Product</th>
            <th>Farmer</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Platform Fee (5%)</th>
            <th>Total Cost</th>
            <th>Status</th>
            <th>Order Date</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($orders as $order): 
            $orderValue = $order['quantity'] * $order['price'];
            $fee = $orderValue * 0.05;
            $totalCost = $orderValue + $fee;
        ?>
        <tr>
            <td><?= $order['id'] ?></td>
            <td><?= htmlspecialchars($order['product_name']) ?></td>
            <td><?= htmlspecialchars($order['farmer_name']) ?></td>
            <td><?= $order['quantity'] ?> <?= htmlspecialchars($order['unit'] ?? '') ?></td>
            <td>₦<?= number_format($orderValue, 2) ?></td>
            <td>₦<?= number_format($fee, 2) ?></td>
            <td>₦<?= number_format($totalCost, 2) ?></td>
            <td>
                <?php if($order['status'] == 'pending'): ?>
                    <span class="badge bg-warning text-dark">Pending</span>
                <?php elseif($order['status'] == 'accepted'): ?>
                    <span class="badge bg-primary">Accepted</span>
                <?php elseif($order['status'] == 'rejected'): ?>
                    <span class="badge bg-danger">Rejected</span>
                <?php elseif($order['status'] == 'completed'): ?>
                    <span class="badge bg-success">Completed</span>
                <?php endif; ?>
            </td>
            <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</div>

<?php include '../includes/footer.php'; ?>