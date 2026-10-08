<?php
/**
 * IKE PRODUCTS LOUNGE - Logout (logout.php)
 * Clears authentication session, retains cart, and redirects
 */

require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/functions.php';

// Unset authentication variables
unset($_SESSION['user_id']);
unset($_SESSION['user_name']);
unset($_SESSION['user_email']);
unset($_SESSION['user_role']);

set_flash('info', 'You have been logged out safely.');
redirect(BASE_URL . '/login.php');
