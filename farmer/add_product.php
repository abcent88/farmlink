<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole('farmer');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_name = trim($_POST['product_name']);
    $category     = trim($_POST['category']);
    $quantity     = $_POST['quantity'];
    $unit         = trim($_POST['unit']);
    $price        = $_POST['price'];
    $description  = trim($_POST['description']);

    $imageName = null;

    if (!empty($_FILES['image']['name'])) {

        $allowed = [
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if (
            !in_array(
                $_FILES['image']['type'],
                $allowed
            )
        ) {
            die('Invalid file type');
        }

        $imageName =
            time() . '_' .
            basename($_FILES['image']['name']);

        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            '../uploads/products/' . $imageName
        );
    }

    $stmt = $pdo->prepare("
        INSERT INTO products
        (
            farmer_id,
            product_name,
            category,
            quantity,
            unit,
            price,
            description,
            image
        )
        VALUES
        (
            ?, ?, ?, ?, ?, ?, ?, ?
        )
    ");

    $stmt->execute([
        $_SESSION['user_id'],
        $product_name,
        $category,
        $quantity,
        $unit,
        $price,
        $description,
        $imageName
    ]);

    $message =
        'Product submitted successfully and awaiting approval.';
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">

    <div class="row justify-content-center">

        <div class="col-md-8">

            <div class="card shadow">

                <div class="card-header bg-success text-white">

                    <h3>Add Product</h3>

                </div>

                <div class="card-body">

                    <?php if(!empty($message)): ?>

                        <div class="alert alert-success">
                            <?= htmlspecialchars($message) ?>
                        </div>

                    <?php endif; ?>

                    <form method="POST"
                          enctype="multipart/form-data">

                        <div class="mb-3">

                            <label>Product Name</label>

                            <input
                                type="text"
                                name="product_name"
                                class="form-control"
                                required>

                        </div>

                        <div class="mb-3">

                            <label>Category</label>

                            <input
                                type="text"
                                name="category"
                                class="form-control"
                                required>

                        </div>

                        <div class="mb-3">

                            <label>Quantity</label>

                            <input
                                type="number"
                                step="0.01"
                                name="quantity"
                                class="form-control"
                                required>

                        </div>

                        <div class="mb-3">

                            <label>Unit</label>

                            <select
                                name="unit"
                                class="form-control">

                                <option>Kg</option>
                                <option>Ton</option>
                                <option>Bag</option>
                                <option>Crate</option>

                            </select>

                        </div>

                        <div class="mb-3">

                            <label>Price (₦)</label>

                            <input
                                type="number"
                                step="0.01"
                                name="price"
                                class="form-control"
                                required>

                        </div>

                        <div class="mb-3">

                            <label>Description</label>

                            <textarea
                                name="description"
                                class="form-control"
                                rows="4"></textarea>

                        </div>

                        <div class="mb-3">

                            <label>Product Image</label>

                            <input
                                type="file"
                                name="image"
                                accept="image/*"
                                class="form-control">

                        </div>

                        <button
                            type="submit"
                            class="btn btn-success">

                            Save Product

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>