<?php
/**
 * IKE PRODUCTS LOUNGE - Acquisition Ledger (admin/orders.php)
 * Lists client acquisitions with fulfillment status filters and search
 */

$page_title = 'Acquisition Ledger';
require_once __DIR__ . '/includes/admin_header.php';

$status_filter = trim($_GET['status'] ?? '');
$search        = trim($_GET['search'] ?? '');

$where = ["1=1"];
$params = [];

if ($status_filter !== '') {
    $where[] = "o.status = ?";
    $params[] = $status_filter;
}

if ($search !== '') {
    $where[] = "(o.shipping_name LIKE ? OR o.shipping_email LIKE ? OR o.id = ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = (int)$search;
}

$sql = "
    SELECT o.*, COUNT(oi.id) AS item_count 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE " . implode(' AND ', $where) . " 
    GROUP BY o.id 
    ORDER BY o.id DESC
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$status_counts = [];
$count_rows = $db->query("SELECT status, COUNT(*) as cnt FROM orders GROUP BY status")->fetchAll();
foreach ($count_rows as $row) {
    $status_counts[$row['status']] = $row['cnt'];
}
$all_count = (int)$db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
?>

<!-- Top Bar -->
<div class="admin-topbar">
    <div>
        <h1 style="font-size: 1.85rem; margin-bottom: 0.25rem;">Acquisition Ledger &amp; Transit Log</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Review client acquisitions, examine numbered certificates, and manage courier fulfillment stages.</p>
    </div>
</div>

<!-- Status Filter Tabs -->
<div style="display: flex; gap: 0.6rem; flex-wrap: wrap; margin-bottom: 1.75rem;">
    <a href="<?= BASE_URL ?>/admin/orders.php" class="btn btn-sm btn-pill <?= $status_filter === '' ? 'btn-primary' : 'btn-secondary' ?>">
        All Acquisitions (<?= $all_count ?>)
    </a>
    <?php 
    $statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
    foreach ($statuses as $st): 
        $cnt = $status_counts[$st] ?? 0;
    ?>
        <a href="<?= BASE_URL ?>/admin/orders.php?status=<?= urlencode($st) ?>" class="btn btn-sm btn-pill <?= $status_filter === $st ? 'btn-primary' : 'btn-secondary' ?>">
            <?= $st ?> (<?= $cnt ?>)
        </a>
    <?php endforeach; ?>
</div>

<!-- Search Bar -->
<div class="card" style="margin-bottom: 1.5rem; padding: 1rem 1.5rem;">
    <form method="GET" action="<?= BASE_URL ?>/admin/orders.php" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
        <?php if ($status_filter): ?>
            <input type="hidden" name="status" value="<?= sanitize($status_filter) ?>">
        <?php endif; ?>
        <div style="flex: 1; min-width: 250px;">
            <input type="text" name="search" class="form-control" placeholder="Search by collector name, email, or reference #..." value="<?= sanitize($search) ?>" style="border-radius: var(--radius-pill);">
        </div>
        <button type="submit" class="btn btn-secondary btn-sm btn-pill">Search</button>
        <?php if ($search !== '' || $status_filter !== ''): ?>
            <a href="<?= BASE_URL ?>/admin/orders.php" class="btn btn-sm btn-pill" style="color: var(--text-muted);">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Orders Table -->
<div class="card" style="padding: 0; overflow: hidden;">
    <?php if (empty($orders)): ?>
        <div style="text-align: center; padding: 4rem 1.5rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
            <h3>No Acquisitions Found</h3>
            <p style="color: var(--text-muted);">No acquisitions matched your active status filter or search term.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Collector</th>
                        <th>Date Placed</th>
                        <th>Allocations</th>
                        <th>Total Settlement</th>
                        <th>Payment Method</th>
                        <th>Fulfillment</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $ord): ?>
                        <tr>
                            <td>
                                <strong style="color: var(--primary);">#IPL-<?= str_pad($ord['id'], 5, '0', STR_PAD_LEFT) ?></strong>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--dark);"><?= sanitize($ord['shipping_name']) ?></div>
                                <span style="font-size: 0.8rem; color: var(--text-muted);"><?= sanitize($ord['shipping_email']) ?></span>
                            </td>
                            <td style="font-size: 0.85rem; color: var(--text-muted);">
                                <?= date('M j, Y, g:i A', strtotime($ord['created_at'])) ?>
                            </td>
                            <td><strong><?= (int)$ord['item_count'] ?></strong> piece(s)</td>
                            <td style="font-weight: 800; color: var(--dark);">
                                <?= format_price($ord['total_amount']) ?>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem;"><?= sanitize($ord['payment_method']) ?></span>
                            </td>
                            <td>
                                <span class="badge <?= get_status_class($ord['status']) ?>">
                                    ● <?= sanitize($ord['status']) ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="<?= BASE_URL ?>/admin/order-details.php?id=<?= $ord['id'] ?>" class="btn btn-secondary btn-sm btn-pill">
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

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
