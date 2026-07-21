<?php

function validateRegistration(
    string $fullname,
    string $email,
    string $phone,
    string $password
): array {

    $errors = [];

    if (strlen(trim($fullname)) < 3) {
        $errors[] = "Full name must be at least 3 characters.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $errors[] = "Please enter a valid phone number.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must contain uppercase, lowercase, a number and a special character.";
    }

    if (
    !preg_match('/[A-Z]/', $password) ||
    !preg_match('/[a-z]/', $password) ||
    !preg_match('/[0-9]/', $password) ||
    !preg_match('/[^A-Za-z0-9]/', $password)
){
        $errors[] =
            "Password must contain uppercase, lowercase and a number.";
    }

    return $errors;
}