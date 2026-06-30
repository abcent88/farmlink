<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';

requireRole('buyer');

$productId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT *
    FROM products
    WHERE id = ?
");

$stmt->execute([$productId]);

$product = $stmt->fetch();

if (!$product) {
    die("Product not found");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $quantity = (float) $_POST['quantity'];

    if ($quantity > $product['quantity']) {

        echo "<script>
                alert('Requested quantity exceeds available stock');
                history.back();
              </script>";
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO orders
        (
            buyer_id,
            product_id,
            quantity
        )
        VALUES (?, ?, ?)
    ");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $productId,
        $quantity
    ]);

    echo "<script>
            alert('Order Request Sent');
            window.location='marketplace.php';
          </script>";
    exit;
}
   

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">

```
<h2>Request Product</h2>

<div class="card p-4">

    <h4>
        <?= htmlspecialchars($product['product_name']) ?>
    </h4>

    <p>
        Price:
        ₦<?= number_format($product['price'],2) ?>
    </p>

    <form method="POST">

        <label class="form-label">
            Quantity Needed
        </label>

        <input
    
    type="number"
    step="0.01"
    min="1"
    max="<?= $product['quantity'] ?>"
    name="quantity"
    class="form-control"
    required>
        <br>
        <p>
    Available Stock:
    <strong><?= $product['quantity'] ?></strong>
</p>

        <button
            class="btn btn-success">
            Submit Request
        </button>

    </form>

</div>
```

</div>

<?php include '../includes/footer.php'; ?>
