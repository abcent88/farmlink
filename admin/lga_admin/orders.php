<?php

require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/roles.php';

requireRole('lga_admin');

$adminId = $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Get Admin LGA
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT lga
FROM users
WHERE id=?
");

$stmt->execute([$adminId]);

$lga = $stmt->fetchColumn();



/*
|--------------------------------------------------------------------------
| Deliveries Awaiting LGA Approval
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("

SELECT

d.id             AS delivery_id,
d.status         AS delivery_status,
d.created_at,

o.id             AS order_id,
o.quantity,
o.total_amount,

p.product_name,
p.unit,

buyer.fullname   AS buyer_name,
buyer.phone      AS buyer_phone,

farmer.fullname  AS farmer_name,
farmer.phone     AS farmer_phone

FROM deliveries d

JOIN orders o
ON d.order_id=o.id

JOIN products p
ON o.product_id=p.id

JOIN users buyer
ON buyer.id=o.buyer_id

JOIN users farmer
ON farmer.id=o.farmer_id

WHERE

farmer.lga=?

AND d.status='assigned'

ORDER BY d.id DESC

");

$stmt->execute([$lga]);

$deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);


include '../../includes/header.php';
include '../../includes/navbar.php';

?>

<div class="container mt-5">

<div class="d-flex justify-content-between align-items-center mb-4">

<h2>

Orders Awaiting LGA Approval

</h2>

<span class="badge bg-success fs-6">

<?= count($deliveries) ?> Pending

</span>

</div>

<?php if(empty($deliveries)): ?>

<div class="alert alert-info">

No deliveries are waiting for approval.

</div>

<?php else: ?>

<div class="table-responsive">

<table class="table table-bordered table-hover align-middle">

<thead class="table-success">

<tr>

<th>Delivery</th>

<th>Order</th>

<th>Product</th>

<th>Buyer</th>

<th>Farmer</th>

<th>Quantity</th>

<th>Total</th>

<th>Status</th>

<th>Action</th>

</tr>

</thead>

<tbody>

<?php foreach($deliveries as $delivery): ?>

<tr>

<td>

#<?= $delivery['delivery_id'] ?>

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

<?= htmlspecialchars($delivery['buyer_name']) ?>

<br>

<small>

<?= htmlspecialchars($delivery['buyer_phone']) ?>

</small>

</td>

<td>

<?= htmlspecialchars($delivery['farmer_name']) ?>

<br>

<small>

<?= htmlspecialchars($delivery['farmer_phone']) ?>

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

<span class="badge bg-warning text-dark">

Awaiting Approval

</span>

</td>

<td>

<a

href="approve_delivery.php?id=<?= $delivery['delivery_id'] ?>"

class="btn btn-success btn-sm">

Approve

</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

<?php endif; ?>

</div>

<?php include '../../includes/footer.php'; ?>