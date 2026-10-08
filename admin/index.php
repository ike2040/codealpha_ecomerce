<?php
/**
 * IKE PRODUCTS LOUNGE - Studio Admin Dashboard (admin/index.php)
 * Real-time financial metrics, studio low-stock warnings, and acquisition logs
 */

$page_title = 'Studio Operations Dashboard';
require_once __DIR__ . '/includes/admin_header.php';

// Calculate KPIs
$total_revenue = (float)$db->query("SELECT SUM(total_amount) FROM orders WHERE status != 'Cancelled'")->fetchColumn();
$total_orders  = (int)$db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_products= (int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_customers = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();

// Fetch low stock statues (<= 5 units)
$low_stock_stmt = $db->query("
    SELECT p.*, c.name AS category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.stock_quantity <= 5 
    ORDER BY p.stock_quantity ASC 
    LIMIT 6
");
$low_stock_products = $low_stock_stmt->fetchAll();

// Fetch latest orders
$recent_orders_stmt = $db->query("
    SELECT o.*, COUNT(oi.id) as item_count 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    GROUP BY o.id 
    ORDER BY o.id DESC 
    LIMIT 5
");
$recent_orders = $recent_orders_stmt->fetchAll();
?>

<!-- Top Bar -->
<div class="admin-topbar">
    <div>
        <h1 style="font-size: 1.85rem; margin-bottom: 0.25rem;">Studio Operations &amp; Intelligence</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Catalog performance, inventory health, and recent acquisitions.</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?= BASE_URL ?>/admin/product-add.php" class="btn btn-primary btn-pill btn-sm">
            ➕ Register New Statue
        </a>
        <a href="<?= BASE_URL ?>/admin/orders.php" class="btn btn-secondary btn-pill btn-sm">
            📋 Acquisition Ledger
        </a>
    </div>
</div>

<!-- 1. KPI Grid -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-icon" style="background: #ecfdf5; color: #059669;">💎</div>
        <div>
            <div class="kpi-val"><?= format_price($total_revenue) ?></div>
            <div class="kpi-label">Settled Revenue</div>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon" style="background: #eff6ff; color: #2563eb;">📦</div>
        <div>
            <div class="kpi-val"><?= number_format($total_orders) ?></div>
            <div class="kpi-label">Client Acquisitions</div>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon" style="background: #fdf4ff; color: #c026d3;">🏛️</div>
        <div>
            <div class="kpi-val"><?= number_format($total_products) ?></div>
            <div class="kpi-label">Masterline Catalog</div>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-icon" style="background: #fffbeb; color: #d97706;">👥</div>
        <div>
            <div class="kpi-val"><?= number_format($total_customers) ?></div>
            <div class="kpi-label">Registered Collectors</div>
        </div>
    </div>
</div>

<!-- 2. Main Dashboard Split -->
<div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 2rem; align-items: start;">
    
    <!-- Left: Recent Orders -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="padding: 1.25rem 1.75rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1.2rem; margin: 0;">Recent Acquisitions</h3>
            <a href="<?= BASE_URL ?>/admin/orders.php" class="btn btn-secondary btn-sm btn-pill" style="font-size: 0.8rem;">All Orders &rarr;</a>
        </div>

        <?php if (empty($recent_orders)): ?>
            <div style="padding: 3rem 1.5rem; text-align: center; color: var(--text-muted);">
                No client acquisitions registered yet.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Collector</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Fulfillment</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $ord): ?>
                            <tr>
                                <td><strong style="color: var(--primary);">#IPL-<?= str_pad($ord['id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                                <td>
                                    <div style="font-weight: 700; color: var(--dark);"><?= sanitize($ord['shipping_name']) ?></div>
                                    <span style="font-size: 0.8rem; color: var(--text-muted);"><?= sanitize($ord['shipping_email']) ?></span>
                                </td>
                                <td style="font-size: 0.85rem; color: var(--text-muted);"><?= date('M j, Y', strtotime($ord['created_at'])) ?></td>
                                <td style="font-weight: 800; color: var(--dark);"><?= format_price($ord['total_amount']) ?></td>
                                <td>
                                    <span class="badge <?= get_status_class($ord['status']) ?>">
                                        <?= sanitize($ord['status']) ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <a href="<?= BASE_URL ?>/admin/order-details.php?id=<?= $ord['id'] ?>" class="btn btn-secondary btn-sm btn-pill" style="font-size: 0.8rem;">
                                        Manage &rarr;
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right: Low Stock Warnings -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="padding: 1.25rem 1.75rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: #fff8f8;">
            <h3 style="font-size: 1.2rem; margin: 0; color: #991b1b; display: flex; align-items: center; gap: 0.5rem;">
                ⚠️ Allocation Limits
            </h3>
            <span class="badge badge-danger"><?= count($low_stock_products) ?> Critical</span>
        </div>

        <?php if (empty($low_stock_products)): ?>
            <div style="padding: 3rem 1.5rem; text-align: center; color: var(--success); font-weight: 600;">
                ✓ All masterline editions have adequate inventory reserves.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Statue</th>
                            <th>Stock</th>
                            <th style="text-align: right;">Allocation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($low_stock_products as $lp): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <img src="<?= get_product_image($lp['image']) ?>" alt="" style="width: 38px; height: 38px; object-fit: contain; border-radius: 4px; background: #f8fafc;">
                                        <div>
                                            <a href="<?= BASE_URL ?>/admin/product-edit.php?id=<?= $lp['id'] ?>" style="font-weight: 700; color: var(--dark); font-size: 0.875rem;">
                                                <?= truncate(sanitize($lp['name']), 24) ?>
                                            </a>
                                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?= sanitize($lp['category_name']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($lp['stock_quantity'] <= 0): ?>
                                        <span class="badge badge-danger">Sold Out</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning"><?= $lp['stock_quantity'] ?> left</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <a href="<?= BASE_URL ?>/admin/product-edit.php?id=<?= $lp['id'] ?>" class="btn btn-secondary btn-sm btn-pill" style="font-size: 0.775rem;">
                                        Adjust
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
