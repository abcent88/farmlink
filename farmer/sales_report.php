<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole('farmer');

$farmerId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Total Revenue
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        SUM(o.quantity * p.price) AS revenue
    FROM orders o
    JOIN products p
        ON o.product_id = p.id
    WHERE p.farmer_id = ?
    AND o.status = 'completed'
");

$stmt->execute([$farmerId]);

$totalRevenue = $stmt->fetchColumn() ?: 0;

/*
|--------------------------------------------------------------------------
| Completed Orders
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM orders o
    JOIN products p
        ON o.product_id = p.id
    WHERE p.farmer_id = ?
    AND o.status = 'completed'
");

$stmt->execute([$farmerId]);

$totalOrders = $stmt->fetchColumn();
/*
|--------------------------------------------------------------------------
| Current Stock Value
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        SUM(quantity * price)
    FROM products
    WHERE farmer_id = ?
");

$stmt->execute([$farmerId]);

$stockValue = $stmt->fetchColumn() ?: 0;

/*
|--------------------------------------------------------------------------
| Top Selling Product
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        p.product_name,
        SUM(o.quantity) AS total_sold
    FROM orders o
    JOIN products p
        ON o.product_id = p.id
    WHERE p.farmer_id = ?
    AND o.status = 'completed'
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 1
");

$stmt->execute([$farmerId]);

$topProduct = $stmt->fetch(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Products Sold
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        p.product_name,
        SUM(o.quantity) AS qty_sold,
        SUM(o.quantity * p.price) AS revenue
    FROM orders o
    JOIN products p
        ON o.product_id = p.id
    WHERE p.farmer_id = ?
    AND o.status = 'completed'
    GROUP BY p.id
    ORDER BY revenue DESC
");

$stmt->execute([$farmerId]);

$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">

<h2>Sales Report</h2>

<div class="row mb-4">

    <div class="col-md-3">
        <div class="card shadow p-3 text-center">
            <h4>₦<?= number_format($totalRevenue,2) ?></h4>
            <p>Total Revenue</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow p-3 text-center">
            <h4><?= $totalOrders ?></h4>
            <p>Completed Orders</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow p-3 text-center">
            <h4>₦<?= number_format($stockValue,2) ?></h4>
            <p>Current Stock Value</p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow p-3 text-center">

            <h6>
                <?= htmlspecialchars(
                    $topProduct['product_name'] ?? 'N/A'
                ) ?>
            </h6>

            <small>
                <?= number_format(
                    $topProduct['total_sold'] ?? 0,
                    2
                ) ?>
                sold
            </small>

        </div>
    </div>

</div>

<div class="card shadow p-3">

<h4>Products Sold</h4>

<table class="table table-bordered">

<thead>
<tr>
    <th>Product</th>
    <th>Quantity Sold</th>
    <th>Revenue</th>
</tr>
</thead>

<tbody>

<?php foreach($sales as $row): ?>

<tr>

<td><?= htmlspecialchars($row['product_name']) ?></td>

<td><?= number_format($row['qty_sold'],2) ?></td>

<td>₦<?= number_format($row['revenue'],2) ?></td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

<?php include '../includes/footer.php'; ?>