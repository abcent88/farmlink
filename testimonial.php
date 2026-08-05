<?php

require_once 'includes/auth.php';
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

$message = "";
$error = "";

/* Check if user already submitted */

$stmt = $pdo->prepare("
SELECT *
FROM testimonials
WHERE user_id=?
LIMIT 1
");

$stmt->execute([$userId]);

$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $rating = (int)$_POST['rating'];
    $review = trim($_POST['message']);

    if ($rating < 1 || $rating > 5) {

        $error = "Rating must be between 1 and 5.";

    } elseif ($review == "") {

        $error = "Please write your testimonial.";

    } else {

        if ($existing) {

            $stmt = $pdo->prepare("
            UPDATE testimonials
            SET
                rating=?,
                message=?,
                status='pending'
            WHERE user_id=?
            ");

            $stmt->execute([
                $rating,
                $review,
                $userId
            ]);

            $message = "Your testimonial has been updated and is awaiting approval.";

        } else {

            $stmt = $pdo->prepare("
            INSERT INTO testimonials
            (
                user_id,
                rating,
                message
            )
            VALUES
            (?,?,?)
            ");

            $stmt->execute([
                $userId,
                $rating,
                $review
            ]);

            $message = "Thank you for your testimonial.";

        }
    }
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container py-5">

<div class="row justify-content-center">

<div class="col-lg-8">

<div class="card shadow">

<div class="card-header bg-success text-white">

<h3 class="mb-0">
Share Your Experience
</h3>

</div>

<div class="card-body">

<?php if($message): ?>

<div class="alert alert-success">

<?= htmlspecialchars($message) ?>

</div>

<?php endif; ?>

<?php if($error): ?>

<div class="alert alert-danger">

<?= htmlspecialchars($error) ?>

</div>

<?php endif; ?>

<form method="post">

<div class="mb-3">

<label class="form-label">
Your Rating
</label>

<div class="rating">

<?php for($i=5;$i>=1;$i--): ?>

<input
type="radio"
name="rating"
value="<?= $i ?>"
id="star<?= $i ?>"
<?= (($existing['rating'] ?? 0) == $i) ? 'checked' : '' ?>>

<label for="star<?= $i ?>">
★
</label>

<?php endfor; ?>

</div>

</div>

<div class="mb-3">

<label class="form-label">

Your Testimonial

</label>

<textarea
name="message"
rows="6"
class="form-control"
required><?= htmlspecialchars($existing['message'] ?? '') ?></textarea>

</div>

<button
class="btn btn-success">

Submit Testimonial

</button>

</form>

</div>

</div>

</div>

</div>

</div>

<?php include 'includes/footer.php'; ?>