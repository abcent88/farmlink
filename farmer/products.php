<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole('farmer');

$stmt = $pdo->prepare("
    SELECT *
    FROM products
    WHERE farmer_id = ?
    ORDER BY id DESC
");

$stmt->execute([
    $_SESSION['user_id']
]);

$products = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">

    <h2>My Products</h2>

    <a href="add_product.php"
       class="btn btn-success mb-3">
       Add Product
    </a>

    <table class="table table-bordered table-striped">

        <thead>

        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Product</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Status</th>
        </tr>

        </thead>

        <tbody>

        <?php foreach($products as $product): ?>

        <tr>

            <td><?= $product['id'] ?></td>

            <td>

                <?php if(!empty($product['image'])): ?>

                    <img
                        src="/projects/farmlink/uploads/products/<?= htmlspecialchars($product['image']) ?>"
                        width="80"
                        height="80"
                        style="object-fit:cover;">

                <?php else: ?>

                    No Image

                <?php endif; ?>

            </td>

            <td>
                <?= htmlspecialchars($product['product_name']) ?>
            </td>

            <td>
                <?= htmlspecialchars($product['category']) ?>
            </td>

            <td>
                <?= htmlspecialchars($product['quantity']) ?>
                <?= htmlspecialchars($product['unit']) ?>
            </td>

            <td>
                ₦<?= number_format($product['price'], 2) ?>
            </td>

            <td>

                <?php if($product['status'] === 'approved'): ?>

                    <span class="badge bg-success">
                        Approved
                    </span>

                <?php elseif($product['status'] === 'pending'): ?>

                    <span class="badge bg-warning text-dark">
                        Pending
                    </span>

                <?php else: ?>

                    <span class="badge bg-danger">
                        Rejected
                    </span>

                <?php endif; ?>

            </td>

        </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

</div>

<?php include '../includes/footer.php'; ?>