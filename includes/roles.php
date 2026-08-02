<?php

/*
|--------------------------------------------------------------------------
| Role Authorization Helper
|--------------------------------------------------------------------------
|
| Supports:
|
| requireRole('super_admin');
|
| requireRole(['super_admin','lga_admin']);
|
*/

function requireRole($roles)
{
    if (!isset($_SESSION['role'])) {

        header("Location: /projects/farmlink/login.php");
        exit;
    }

    // Allow multiple roles
    if (is_array($roles)) {

        if (!in_array($_SESSION['role'], $roles, true)) {

            header("Location: /projects/farmlink/login.php");
            exit;
        }

        return;
    }

    // Single role
    if ($_SESSION['role'] !== $roles) {

        header("Location: /projects/farmlink/login.php");
        exit;
    }
}