<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole('trucker');

/*
|--------------------------------------------------------------------------
| Deliveries Approved By LGA
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT
    d.id,
    d.order_id,
    d.status,
    d.created_at,

    o.quantity,
    o.total_amount,

    p.product_name,
    p.unit,

    buyer.fullname  AS buyer_name,
    buyer.phone     AS buyer_phone,

    farmer.fullname AS farmer_name,
    farmer.phone    AS farmer_phone

FROM deliveries d

JOIN orders o
    ON o.id=d.order_id

JOIN products p
    ON p.id=o.product_id

JOIN users buyer
    ON buyer.id=o.buyer_id

JOIN users farmer
    ON farmer.id=o.farmer_id

WHERE d.status='approved'

ORDER BY d.created_at DESC
");

$stmt->execute();

$deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">

<div class="d-flex justify-content-between align-items-center mb-4">

<h2>

Available Deliveries

</h2>

<span class="badge bg-success fs-6">

<?= count($deliveries) ?> Available

</span>

</div>

<?php if(empty($deliveries)): ?>

<div class="alert alert-info">

No deliveries are available at the moment.

</div>

<?php else: ?>

<div class="table-responsive">

<table class="table table-bordered table-hover align-middle">

<thead class="table-success">

<tr>

<th>Delivery</th>

<th>Order</th>

<th>Product</th>

<th>Farmer</th>

<th>Buyer</th>

<th>Quantity</th>

<th>Total</th>

<th>Action</th>

</tr>

</thead>

<tbody>

<?php foreach($deliveries as $delivery): ?>

<tr>

<td>

#<?= $delivery['id'] ?>

</td>

<td>

#<?= $delivery['order_id'] ?>

</td>

<td>

<strong>

<?= htmlspecialchars($delivery['product_name']) ?>

</strong>

</td>

<td>

<?= htmlspecialchars($delivery['farmer_name']) ?>

<br>

<small>

<?= htmlspecialchars($delivery['farmer_phone']) ?>

</small>

</td>

<td>

<?= htmlspecialchars($delivery['buyer_name']) ?>

<br>

<small>

<?= htmlspecialchars($delivery['buyer_phone']) ?>

</small>

</td>

<td>

<?= number_format($delivery['quantity']) ?>

<?= htmlspecialchars($delivery['unit']) ?>

</td>

<td>

₦<?= number_format($delivery['total_amount'],2) ?>

</td>

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

</div>

<?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>