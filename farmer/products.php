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
/*
|--------------------------------------------------------------------------
| Product Statistics
|--------------------------------------------------------------------------
*/

$totalProducts = count($products);

$approvedProducts = 0;
$pendingProducts = 0;
$rejectedProducts = 0;

foreach ($products as $item) {

    switch ($item['status']) {

        case 'approved':
            $approvedProducts++;
            break;

        case 'rejected':
            $rejectedProducts++;
            break;

        default:
            $pendingProducts++;
            break;

    }

}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">
    <?php if(isset($_GET['success'])): ?>

<div class="alert alert-success alert-dismissible fade show">

<?php

switch($_GET['success']){

    case 'added':
        echo "Product added successfully.";
        break;

    case 'updated':
        echo "Product updated successfully.";
        break;

    case 'deleted':
        echo "Product deleted successfully.";
        break;

}

?>

<button
type="button"
class="btn-close"
data-bs-dismiss="alert"></button>

</div>

<?php endif; ?>

    <h2>My Products</h2>
    <div class="row mb-4">

<div class="col-md-3">

<div class="card border-success shadow-sm">

<div class="card-body text-center">

<h3><?= $totalProducts ?></h3>

<p>Total Products</p>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card border-warning shadow-sm">

<div class="card-body text-center">

<h3><?= $pendingProducts ?></h3>

<p>Pending</p>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card border-success shadow-sm">

<div class="card-body text-center">

<h3><?= $approvedProducts ?></h3>

<p>Approved</p>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card border-danger shadow-sm">

<div class="card-body text-center">

<h3><?= $rejectedProducts ?></h3>

<p>Rejected</p>

</div>

</div>

</div>

</div>

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
            <th width="220">Actions</th>
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
            <td>

<a
href="view_product.php?id=<?= $product['id'] ?>"
class="btn btn-info btn-sm">

View

</a>

<?php if($product['status'] != 'approved'): ?>

<a
href="edit_product.php?id=<?= $product['id'] ?>"
class="btn btn-warning btn-sm">

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
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this product?');">

Delete

</button>

</form>

<?php endif; ?>

</td>

        </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

</div>

<?php include '../includes/footer.php'; ?>