<?php

function logAction(PDO $pdo, $adminId, $action, $targetUser)
{
    $stmt = $pdo->prepare("
        INSERT INTO audit_logs
        (
            admin_id,
            action,
            target_user
        )
        VALUES
        (
            ?,
            ?,
            ?
        )
    ");

    $stmt->execute([
        $adminId,
        $action,
        $targetUser
    ]);
}