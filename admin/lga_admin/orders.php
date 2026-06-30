<?php

require_once '../../includes/auth.php';
require_once '../../includes/roles.php';
require_once '../../config/database.php';

requireRole('lga_admin');

$adminId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT lga
    FROM users
    WHERE id = ?
");
$stmt->execute([$adminId]);

$lga = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT
        o.id,
        o.quantity,
        o.status,
        p.product_name,
        p.price,
        b.fullname AS buyer_name,
        f.fullname AS farmer_name
    FROM orders o
    JOIN products p
        ON o.product_id = p.id
    JOIN users f
        ON p.farmer_id = f.id
    JOIN users b
        ON o.buyer_id = b.id
    WHERE f.lga = ?
    AND o.status = 'farmer_approved'
    ORDER BY o.id DESC
");

$stmt->execute([$lga]);

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../includes/header.php';
include '../../includes/navbar.php';
?>

<div class="container mt-5">

<h2>Orders Awaiting Approval</h2>

<table class="table table-bordered">

<thead>
<tr>
    <th>ID</th>
    <th>Buyer</th>
    <th>Farmer</th>
    <th>Product</th>
    <th>Quantity</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php foreach($orders as $order): ?>

<tr>

<td><?= $order['id'] ?></td>

<td><?= htmlspecialchars($order['buyer_name']) ?></td>

<td><?= htmlspecialchars($order['farmer_name']) ?></td>

<td><?= htmlspecialchars($order['product_name']) ?></td>

<td><?= $order['quantity'] ?></td>

<td>

<a
href="../approve_order.php?order_id=<?= $order['id'] ?>"
class="btn btn-success btn-sm">
Approve
</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php include '../../includes/footer.php'; ?>