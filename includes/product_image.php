<?php

if (!function_exists('productImage')) {

    function productImage(?string $filename): string
    {
        $default = '/projects/farmlink/assets/images/no-image.png';

        if (empty($filename)) {
            return $default;
        }

        $filename = trim($filename);

        $fullPath = __DIR__ . '/../uploads/products/' . $filename;

        if (!is_file($fullPath)) {
            return $default;
        }

        return '/projects/farmlink/uploads/products/' . rawurlencode($filename);
    }
}