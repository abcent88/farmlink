<?php

require_once '../../includes/auth.php';
require_once '../../includes/roles.php';
require_once '../../config/database.php';
require_once '../../includes/csrf.php';

requireRole('lga_admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: farmer_verifications.php");
    exit;
}

verify_csrf();

$userId  = (int)($_POST['user_id'] ?? 0);
$action  = $_POST['action'] ?? '';
$reason  = trim($_POST['reason'] ?? '');

$adminId = $_SESSION['user_id'];
$lga = NULL;

if (!$userId || !in_array($action, ['approve', 'reject'])) {

    header("Location: farmer_verifications.php?error=invalid");
    exit;

}

/*
|--------------------------------------------------------------------------
| Ensure farmer belongs to this LGA
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
SELECT
u.id
FROM users u
INNER JOIN farmer_profiles fp
ON fp.user_id=u.id
WHERE
u.id = 2
AND u.lga = NULL
LIMIT 1
");

$stmt->execute([
    $userId,
    $lga
]);

if (!$stmt->fetch()) {

    header("Location: farmer_verifications.php?error=permission");
    exit;

}

try {

    $pdo->beginTransaction();

    if ($action === 'approve') {

        $stmt = $pdo->prepare("
        UPDATE farmer_profiles
        SET

        verification_status='verified',

        verified_by=?,

        verified_at=NOW(),

        rejection_reason=NULL

        WHERE user_id=?
        ");

        $stmt->execute([

            $adminId,

            $userId

        ]);

        $title = "Farm Verification Approved";

        $message = "Congratulations! Your farm profile has been verified by the LGA Administrator. You can now enjoy all verified farmer benefits.";

    } else {

        if ($reason == '') {

            throw new Exception("Please provide a rejection reason.");

        }

        $stmt = $pdo->prepare("
        UPDATE farmer_profiles
        SET

        verification_status='rejected',

        verified_by=?,

        verified_at=NOW(),

        rejection_reason=?

        WHERE user_id=?
        ");

        $stmt->execute([

            $adminId,

            $reason,

            $userId

        ]);

        $title = "Farm Verification Rejected";

        $message = "Your verification request has been rejected.\n\nReason:\n".$reason;

    }

    /*
    |--------------------------------------------------------------------------
    | Notification
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
    INSERT INTO notifications
    (
    user_id,
    title,
    message
    )
    VALUES
    (
    ?,
    ?,
    ?
    )
    ");

    $stmt->execute([

        $userId,

        $title,

        $message

    ]);

    $pdo->commit();

    header("Location: farmer_verifications.php?success=1");
    exit;

} catch (Exception $e) {

    $pdo->rollBack();

    header("Location: farmer_verifications.php?error=failed");
    exit;

}