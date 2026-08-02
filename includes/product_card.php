<?php
require_once __DIR__ . '/product_image.php';
?>

<div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4">

    <div class="card product-card shadow h-100 border-0">

        <div class="position-relative">

            <img
                src="<?= productImage($product['image']) ?>"
                class="card-img-top"
                style="height:220px;object-fit:cover;"
                alt="<?= htmlspecialchars($product['product_name']) ?>">

            <?php if (($product['farmer_status'] ?? '') === 'active'): ?>

                <span class="badge bg-success position-absolute top-0 start-0 m-2">
                    ✓ Verified
                </span>

            <?php endif; ?>

        </div>

        <div class="card-body d-flex flex-column">

            <h5 class="fw-bold">
                <?= htmlspecialchars($product['product_name']) ?>
            </h5>

            <h4 class="text-success mb-3">
                ₦<?= number_format($product['price'],2) ?>
            </h4>

            <p class="mb-1">
                👨‍🌾 <strong><?= htmlspecialchars($product['farmer_name']) ?></strong>
            </p>

            <p class="text-muted mb-2">
                📍
                <?= htmlspecialchars($product['lga'] ?? '') ?>
                <?php if (!empty($product['town'])): ?>
                    , <?= htmlspecialchars($product['town']) ?>
                <?php endif; ?>
            </p>

            <p class="mb-2">
                🌾 <?= htmlspecialchars($product['category']) ?>
            </p>

            <p class="mb-3">
                📦
                <?= htmlspecialchars($product['quantity']) ?>
                <?= htmlspecialchars($product['unit']) ?>
            </p>

            <div class="mt-auto d-grid gap-2">

                <a
                    href="/projects/farmlink/product.php?id=<?= $product['id'] ?>"
                    class="btn btn-success">

                    View Details

                </a>

                <a
                    href="/projects/farmlink/login.php"
                    class="btn btn-outline-success">

                    Contact Farmer

                </a>

            </div>

        </div>

    </div>

</div>