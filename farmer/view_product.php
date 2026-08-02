<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../includes/csrf.php';

requireRole('farmer');

/*
|--------------------------------------------------------------------------
| Get Product ID
|--------------------------------------------------------------------------
*/

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    die('Invalid Product.');
}

/*
|--------------------------------------------------------------------------
| Load Product
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        p.*,
        u.fullname,
        u.lga,
        u.town,
        fp.farm_name
    FROM products p
    JOIN users u
        ON p.farmer_id = u.id
    LEFT JOIN farmer_profiles fp
        ON fp.user_id = u.id
    WHERE
        p.id = ?
        AND p.farmer_id = ?
    LIMIT 1
");

$stmt->execute([
    $id,
    $_SESSION['user_id']
]);

$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die('Product not found.');
}

$image = !empty($product['image'])
    ? '../uploads/products/' . $product['image']
    : '../assets/images/no-image.png';

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">

    <div class="row">

        <!-- Product Image -->
        <div class="col-lg-4 mb-4">

            <div class="card shadow">

                <div class="card-body text-center">

                    <img
                        src="<?= htmlspecialchars($image) ?>"
                        alt="<?= htmlspecialchars($product['product_name']) ?>"
                        class="img-fluid rounded"
                        style="max-height:300px;object-fit:cover;">

                    <h4 class="mt-3">
                        <?= htmlspecialchars($product['product_name']) ?>
                    </h4>

                    <p class="text-muted">
                        <?= htmlspecialchars($product['category']) ?>
                    </p>

                </div>

            </div>

        </div>

        <!-- Product Details -->
        <div class="col-lg-8">

            <div class="card shadow">

                <div class="card-header bg-success text-white">

                    <h4 class="mb-0">
                        Product Information
                    </h4>

                </div>

                <div class="card-body">

                    <table class="table table-bordered">

                        <tr>
                            <th width="35%">Farmer</th>
                            <td><?= htmlspecialchars($product['fullname']) ?></td>
                        </tr>

                        <tr>
                            <th>Farm</th>
                            <td><?= htmlspecialchars($product['farm_name'] ?: 'Not Provided') ?></td>
                        </tr>

                        <tr>
                            <th>LGA</th>
                            <td><?= htmlspecialchars($product['lga']) ?></td>
                        </tr>

                        <tr>
                            <th>Town</th>
                            <td><?= htmlspecialchars($product['town']) ?></td>
                        </tr>

                        <tr>
                            <th>Quantity</th>
                            <td>
                                <?= number_format($product['quantity']) ?>
                                <?= htmlspecialchars($product['unit']) ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Price</th>
                            <td>
                                ₦<?= number_format($product['price'],2) ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Description</th>
                            <td>
                                <?= nl2br(htmlspecialchars($product['description'])) ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Status</th>
                            <td>

                                <?php
                                switch ($product['status']) {

                                    case 'approved':
                                        echo '<span class="badge bg-success">Approved</span>';
                                        break;

                                    case 'rejected':
                                        echo '<span class="badge bg-danger">Rejected</span>';
                                        break;

                                    default:
                                        echo '<span class="badge bg-warning text-dark">Pending</span>';
                                }
                                ?>

                            </td>
                        </tr>

                        <?php if ($product['status'] == 'rejected'): ?>

                        <tr>

                            <th>Rejection Reason</th>

                            <td class="text-danger">

                                <?= nl2br(htmlspecialchars($product['rejection_reason'])) ?>

                            </td>

                        </tr>

                        <?php endif; ?>

                    </table>

                    <div class="mt-4">

                        <a
                            href="products.php"
                            class="btn btn-secondary">

                            Back

                        </a>

                        <?php if ($product['status'] != 'approved'): ?>

                            <a
                                href="edit_product.php?id=<?= $product['id'] ?>"
                                class="btn btn-warning">

                                Edit

                            </a>

                            <form
                                method="POST"
                                action="delete_product.php"
                                class="d-inline">

                                <?= csrfField(); ?>

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= $product['id'] ?>">

                                <button
                                    type="submit"
                                    class="btn btn-danger"
                                    onclick="return confirm('Delete this product?');">

                                    Delete

                                </button>

                            </form>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>