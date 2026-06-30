<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole('super_admin');

$stmt = $pdo->query("
SELECT
    p.*,
    u.fullname
FROM products p
JOIN users u
ON p.farmer_id = u.id
ORDER BY p.id DESC
");

$products = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">

<h2>Manage Products</h2>

<table class="table table-bordered">

<thead>
<tr>
    <th>ID</th>
    <th>Farmer</th>
    <th>Product</th>
    <th>Price</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

<?php foreach($products as $product): ?>

<tr>

<td><?= $product['id'] ?></td>

<td><?= htmlspecialchars($product['fullname']) ?></td>

<td><?= htmlspecialchars($product['product_name']) ?></td>

<td>₦<?= number_format($product['price'],2) ?></td>

<td><?= htmlspecialchars($product['status']) ?></td>

<td>

<a href="approve_product.php?id=<?= $product['id'] ?>"
   class="btn btn-success btn-sm">
   Approve
</a>

<a href="reject_product.php?id=<?= $product['id'] ?>"
   class="btn btn-danger btn-sm">
   Reject
</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php include '../includes/footer.php'; ?>