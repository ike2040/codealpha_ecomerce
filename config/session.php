<?php
/**
 * Session Management
 * Handles session start, flash messages, and authentication guards
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Set a flash message (shown once on next page load)
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type'    => $type,       // 'success', 'error', 'warning', 'info'
        'message' => $message
    ];
}

/**
 * Get and clear the flash message
 */
function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if logged-in user is admin
 */
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Redirect to login if user is not logged in
 */
function require_login() {
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to access that page.');
        $baseUrl = defined('BASE_URL') ? BASE_URL : '/codealpha_ecommerce';
        header('Location: ' . $baseUrl . '/login.php');
        exit;
    }
}

/**
 * Redirect to homepage if user is not an admin
 */
function require_admin() {
    if (!is_admin()) {
        set_flash('error', 'Access denied. Admin privileges required.');
        $baseUrl = defined('BASE_URL') ? BASE_URL : '/codealpha_ecommerce';
        header('Location: ' . $baseUrl . '/index.php');
        exit;
    }
}
