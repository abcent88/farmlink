<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole('buyer');

$stmt = $pdo->prepare("
SELECT
p.*,
u.fullname AS farmer_name
FROM products p
JOIN users u
ON p.farmer_id = u.id
WHERE p.status = 'approved'
AND p.quantity > 0
ORDER BY p.id DESC
");

$stmt->execute();

$products = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">

    <h2>Marketplace</h2>

    <div class="row">

    <?php foreach($products as $product): ?>

        <div class="col-md-4 mb-4">

            <div class="card product-card shadow">

                <?php if(!empty($product['image'])): ?>

                    <img
                    src="/projects/farmlink/uploads/products/<?= htmlspecialchars($product['image']) ?>"
                    class="card-img-top"
                    style="height:220px;object-fit:cover;">

                <?php endif; ?>

                <div class="card-body">

                    <h5>
                        <?= htmlspecialchars($product['product_name']) ?>
                    </h5>

                    <p>
                        <strong>Farmer:</strong>
                        <?= htmlspecialchars($product['farmer_name']) ?>
                    </p>

                    <p>
                        <strong>Category:</strong>
                        <?= htmlspecialchars($product['category']) ?>
                    </p>

                    <p>
                        <strong>Quantity:</strong>
                        <?= htmlspecialchars($product['quantity']) ?>
                        <?= htmlspecialchars($product['unit']) ?>
                    </p>
                    <?php if($product['quantity'] <= 10): ?>

```
<p class="text-danger">
    <strong>Low Stock!</strong>
</p>
```

<?php endif; ?>


                    <p>
                        <strong>Price:</strong>
                        ₦<?= number_format($product['price'],2) ?>
                    </p>

                    <p>
                        <?= htmlspecialchars($product['description']) ?>
                    </p>
                    <a
    href="request_order.php?id=<?= $product['id'] ?>"
    class="btn btn-success">
    Request Product
</a>

                </div>

            </div>

        </div>

    <?php endforeach; ?>

    </div>

</div>

<?php include '../includes/footer.php'; ?>