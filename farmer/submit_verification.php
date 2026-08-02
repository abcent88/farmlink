<?php

require_once '../includes/auth.php';
require_once '../includes/roles.php';
require_once '../config/database.php';
require_once '../includes/csrf.php';

requireRole('farmer');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: profile.php");
    exit;
}

verify_csrf();

$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
UPDATE farmer_profiles
SET
verification_status='pending',
submitted_at=NOW()
WHERE user_id=?
");

$stmt->execute([$userId]);

header("Location: profile.php?submitted=1");
exit;