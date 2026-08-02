<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../includes/csrf.php';

requireRole('super_admin');

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
SELECT *
FROM product_categories
WHERE id=?
LIMIT 1
");

$stmt->execute([$id]);

$category = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$category){

die("Category not found.");

}

$errors=[];

if($_SERVER['REQUEST_METHOD']=="POST"){

verify_csrf();

$category_name = trim($_POST['category_name']);
$description   = trim($_POST['description']);
$status        = $_POST['status'];

if($category_name==""){

$errors[]="Category name is required.";

}

$check=$pdo->prepare("

SELECT id

FROM product_categories

WHERE category_name=?

AND id<>?

");

$check->execute([
$category_name,
$id
]);

if($check->fetch()){

$errors[]="Category already exists.";

}

if(empty($errors)){

$update=$pdo->prepare("

UPDATE product_categories

SET

category_name=?,

description=?,

status=?

WHERE id=?

");

$update->execute([

$category_name,

$description,

$status,

$id

]);

header("Location: categories.php?success=updated");

exit;

}

}

include '../includes/header.php';
include '../includes/navbar.php';

?>

<div class="container mt-5">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h3>

Edit Category

</h3>

</div>

<div class="card-body">

<?php if($errors): ?>

<div class="alert alert-danger">

<ul>

<?php foreach($errors as $error): ?>

<li><?= htmlspecialchars($error) ?></li>

<?php endforeach; ?>

</ul>

</div>

<?php endif; ?>

<form method="POST">

<?= csrfField(); ?>

<div class="mb-3">

<label>

Category Name

</label>

<input

type="text"

name="category_name"

class="form-control"

value="<?= htmlspecialchars($category['category_name']) ?>"

required>

</div>

<div class="mb-3">

<label>

Description

</label>

<textarea

name="description"

rows="4"

class="form-control"><?= htmlspecialchars($category['description']) ?></textarea>

</div>

<div class="mb-3">

<label>

Status

</label>

<select

name="status"

class="form-select">

<option

value="active"

<?= $category['status']=="active" ? "selected":"" ?>>

Active

</option>

<option

value="inactive"

<?= $category['status']=="inactive" ? "selected":"" ?>>

Inactive

</option>

</select>

</div>

<button

class="btn btn-primary">

Save Changes

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