<?php

function requireRole($role)
{
    if (!isset($_SESSION['role'])) {
        die("Access Denied");
    }

    if ($_SESSION['role'] !== $role) {
        die("Access Denied");
    }
}
