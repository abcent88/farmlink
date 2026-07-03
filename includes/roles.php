<?php

function requireRole($role)
{
    if (
        !isset($_SESSION['role']) ||
        $_SESSION['role'] !== $role
    ) {

        header("Location: /projects/farmlink/login.php");

        exit;
    }
}

?>
