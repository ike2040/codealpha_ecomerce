<?php
/**
 * IKE PRODUCTS LOUNGE - Studio Admin Header & Navigation
 * Enforces executive admin credentials and renders studio navigation
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/functions.php';

require_admin();

$db = getDB();

$low_stock_count = (int)$db->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= 5")->fetchColumn();
$pending_orders_count = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn();

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? sanitize($page_title) . ' | IKE PRODUCTS LOUNGE Admin' : 'IKE PRODUCTS LOUNGE - Studio Admin' ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/images/logo-icon.svg">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>

<div class="admin-layout">
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-logo" style="padding: 1.25rem 1.25rem 1rem;">
            <a href="<?= BASE_URL ?>/admin/index.php" style="display: flex; align-items: center; text-decoration: none; gap: 0.75rem;">
                <img src="<?= BASE_URL ?>/assets/images/logo-icon.svg" alt="IKE PRODUCTS LOUNGE" style="height: 38px; width: 38px; flex-shrink: 0; display: block; border-radius: 8px;">
                <div style="display: flex; flex-direction: column; line-height: 1.1;">
                    <span style="font-size: 1.05rem; font-weight: 900; letter-spacing: -0.01em; color: #ffffff; font-family: var(--font);">IKE PRODUCTS</span>
                    <span style="font-size: 0.65rem; font-weight: 800; letter-spacing: 2.5px; color: #FF5436; text-transform: uppercase;">STUDIO ADMIN</span>
                </div>
            </a>
        </div>

        <ul class="admin-nav">
            <li>
                <a href="<?= BASE_URL ?>/admin/index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>">
                    <span>📊</span> Studio Overview
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/admin/products.php" class="<?= in_array($current_page, ['products.php', 'product-add.php', 'product-edit.php']) ? 'active' : '' ?>">
                    <span>📦</span> Statues &amp; Editions
                    <?php if ($low_stock_count > 0): ?>
                        <span class="badge badge-warning" style="margin-left: auto; font-size: 0.7rem;"><?= $low_stock_count ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/admin/categories.php" class="<?= $current_page === 'categories.php' ? 'active' : '' ?>">
                    <span>🏷️</span> Universe Galleries
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/admin/orders.php" class="<?= in_array($current_page, ['orders.php', 'order-details.php']) ? 'active' : '' ?>">
                    <span>📋</span> Acquisitions
                    <?php if ($pending_orders_count > 0): ?>
                        <span class="badge badge-danger" style="margin-left: auto; font-size: 0.7rem;"><?= $pending_orders_count ?></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>

        <div class="admin-sidebar-footer" style="margin-top: auto; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1);">
            <div style="font-size: 0.85rem; margin-bottom: 0.75rem; color: #94a3b8; line-height: 1.4;">
                <div style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.06em; color: var(--accent); font-weight: 800; margin-bottom: 0.2rem;">Store Founder &amp; Director</div>
                <strong style="color: white; font-size: 0.95rem;"><?= sanitize($_SESSION['user_name'] ?? 'Isaac Ofori') ?></strong>
                <div style="font-size: 0.75rem; color: #cbd5e1; margin-top: 3px;">📞 +233594844398</div>
                <div style="font-size: 0.72rem; color: #94a3b8; word-break: break-all;">📧 isaac0594844398@gmail.com</div>
            </div>
            <a href="<?= BASE_URL ?>/index.php" class="btn btn-secondary btn-sm btn-block btn-pill" style="margin-bottom: 0.5rem; justify-content: flex-start;">
                🏪 Flagship Store
            </a>
            <a href="<?= BASE_URL ?>/logout.php" class="btn btn-danger btn-sm btn-block btn-pill" style="justify-content: flex-start;">
                🚪 Sign Out
            </a>
        </div>
    </aside>

    <main class="admin-main">
