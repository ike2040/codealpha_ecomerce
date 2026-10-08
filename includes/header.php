<?php
/**
 * Header Include
 * Loaded at the top of every public page
 * Sets the HTML document structure, metadata, and stylesheet links
 */

// Load config files if not already loaded
if (!function_exists('getDB')) {
    require_once __DIR__ . '/../config/database.php';
}
if (!function_exists('is_logged_in')) {
    require_once __DIR__ . '/../config/session.php';
}
if (!function_exists('sanitize')) {
    require_once __DIR__ . '/../config/functions.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? sanitize($page_title) . ' | IKE PRODUCTS LOUNGE' : 'IKE PRODUCTS LOUNGE - Exclusive Collectibles' ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/images/logo-icon.svg">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
