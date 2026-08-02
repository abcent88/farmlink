<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../includes/csrf.php';

requireRole('super_admin');

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verify_csrf();

    $category_name = trim($_POST['category_name']);
    $description   = trim($_POST['description']);
    $status        = $_POST['status'];

    if ($category_name === '') {
        $errors[] = "Category name is required.";
    }

    $check = $pdo->prepare("
        SELECT id
        FROM product_categories
        WHERE category_name = ?
    ");

    $check->execute([$category_name]);

    if ($check->fetch()) {
        $errors[] = "Category already exists.";
    }

    if (empty($errors)) {

        $stmt = $pdo->prepare("
            INSERT INTO product_categories
            (
                category_name,
                description,
                status
            )
            VALUES
            (
                ?, ?, ?
            )
        ");

        $stmt->execute([
            $category_name,
            $description,
            $status
        ]);

        header("Location: categories.php?success=created");
        exit;
    }
}

include '../includes/header.php';
include '../includes/navbar.php';

?>

<div class="container mt-5">

<div class="card shadow">

<div class="card-header bg-success text-white">

<h3>

Add Product Category

</h3>

</div>

<div class="card-body">

<?php if(!empty($errors)): ?>

<div class="alert alert-danger">

<ul class="mb-0">

<?php foreach($errors as $error): ?>

<li><?= htmlspecialchars($error) ?></li>

<?php endforeach; ?>

</ul>

</div>

<?php endif; ?>

<form method="POST">

<?= csrfField(); ?>

<div class="mb-3">

<label class="form-label">

Category Name

</label>

<input
type="text"
name="category_name"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">

Description

</label>

<textarea
name="description"
class="form-control"
rows="4"></textarea>

</div>

<div class="mb-3">

<label class="form-label">

Status

</label>

<select
name="status"
class="form-select">

<option value="active">

Active

</option>

<option value="inactive">

Inactive

</option>

</select>

</div>

<button
class="btn btn-success">

Save Category

</button>

<a
href="categories.php"
class="btn btn-secondary">

Cancel

</a>

</form>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>