<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';

requireRole('super_admin');

$stats = $pdo->query("
    SELECT
        COUNT(o.id) AS total_orders,
        COALESCE(SUM(o.quantity * p.price),0) AS gross_sales,
        COALESCE(SUM(o.quantity * p.price * 0.05),0) AS platform_fees
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.status IN ('accepted','completed')
")->fetch();

$today = $pdo->query("
    SELECT
        COALESCE(SUM(o.quantity * p.price * 0.05),0) AS today_fee
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.status IN ('accepted','completed')
    AND DATE(o.created_at)=CURDATE()
")->fetch();

$topProducts = $pdo->query("
    SELECT
        p.product_name,
        SUM(o.quantity) AS qty_sold
    FROM orders o
    JOIN products p ON o.product_id=p.id
    WHERE o.status IN ('accepted','completed')
    GROUP BY p.id
    ORDER BY qty_sold DESC
    LIMIT 5
")->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">

    <h1>Revenue Dashboard</h1>

    <div class="row text-center mb-4">

        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body">
                    <h3><?= $stats['total_orders'] ?></h3>
                    <p>Successful Orders</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body">
                    <h3>₦<?= number_format($stats['gross_sales'],2) ?></h3>
                    <p>Total Sales</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow bg-success text-white">
                <div class="card-body">
                    <h3>₦<?= number_format($stats['platform_fees'],2) ?></h3>
                    <p>FarmLink Earnings (5%)</p>
                </div>
            </div>
        </div>

    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <h4>Today's Earnings</h4>
            <h2>
                ₦<?= number_format($today['today_fee'],2) ?>
            </h2>
        </div>
    </div>

    <div class="card shadow">

        <div class="card-body">

            <h4>Top Selling Products</h4>

            <table class="table">

                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity Sold</th>
                    </tr>
                </thead>

                <tbody>

                <?php foreach($topProducts as $product): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($product['product_name']) ?>
                        </td>

                        <td>
                            <?= $product['qty_sold'] ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>