<?php
/**
 * IKE PRODUCTS LOUNGE - Acquisition Details & Fulfillment (admin/order-details.php)
 * Inspect order receipt, recipient details, and update courier fulfillment stages
 */

$page_title = 'Acquisition Details';
require_once __DIR__ . '/includes/admin_header.php';

$order_id = (int)($_GET['id'] ?? 0);

if ($order_id <= 0) {
    set_flash('error', 'Invalid acquisition reference.');
    redirect(BASE_URL . '/admin/orders.php');
}

// Handle Order Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $new_status = trim($_POST['status'] ?? '');
    $valid_statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];

    if (in_array($new_status, $valid_statuses)) {
        $upd = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $upd->execute([$new_status, $order_id]);
        set_flash('success', '✓ Acquisition #IPL-' . str_pad($order_id, 5, '0', STR_PAD_LEFT) . ' fulfillment status set to "' . $new_status . '".');
    } else {
        set_flash('error', 'Invalid status selection.');
    }
    redirect(BASE_URL . '/admin/order-details.php?id=' . $order_id);
}

// Fetch order
$stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    set_flash('error', 'Acquisition record not found.');
    redirect(BASE_URL . '/admin/orders.php');
}

// Fetch order items
$item_stmt = $db->prepare("
    SELECT oi.*, p.name AS product_name, p.image AS product_image, c.name AS category_name 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE oi.order_id = ?
");
$item_stmt->execute([$order_id]);
$order_items = $item_stmt->fetchAll();
?>

<!-- Top Bar -->
<div class="admin-topbar">
    <div>
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.25rem;">
            <h1 style="font-size: 1.85rem; margin: 0;">Acquisition #IPL-<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></h1>
            <span class="badge <?= get_status_class($order['status']) ?>" style="font-size: 0.85rem; padding: 0.35rem 0.85rem;">
                ● <?= sanitize($order['status']) ?>
            </span>
        </div>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Logged on <?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?></p>
    </div>
    <a href="<?= BASE_URL ?>/admin/orders.php" class="btn btn-secondary btn-pill btn-sm">
        &larr; Back to Ledger
    </a>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start;">
    <!-- Left: Line Items -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="padding: 1.25rem 1.75rem; border-bottom: 1px solid var(--border);">
            <h3 style="font-size: 1.2rem; margin: 0;">Allocated Statues &amp; Editions</h3>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 70px;">Piece</th>
                        <th>Statue Title &amp; Universe</th>
                        <th>Unit Price</th>
                        <th>Allocated</th>
                        <th style="text-align: right;">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $calc_subtotal = 0;
                    foreach ($order_items as $item): 
                        $item_total = $item['price'] * $item['quantity'];
                        $calc_subtotal += $item_total;
                    ?>
                        <tr>
                            <td>
                                <img src="<?= get_product_image($item['product_image']) ?>" alt="" class="table-thumb" style="width: 48px; height: 48px;">
                            </td>
                            <td>
                                <div style="font-weight: 800; color: var(--dark); font-size: 1rem;"><?= sanitize($item['product_name'] ?? 'Edition #' . $item['product_id']) ?></div>
                                <span style="font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase;"><?= sanitize($item['category_name'] ?? 'Masterline') ?></span>
                            </td>
                            <td><?= format_price($item['price']) ?></td>
                            <td><strong><?= (int)$item['quantity'] ?></strong></td>
                            <td style="text-align: right; font-weight: 900; color: var(--dark); font-size: 1.05rem;"><?= format_price($item_total) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="padding: 1.5rem 1.75rem; background: #f8fafc; border-top: 1px solid var(--border); display: flex; justify-content: flex-end;">
            <div style="width: 260px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; font-size: 0.95rem;">
                    <span>Subtotal:</span>
                    <strong><?= format_price($calc_subtotal) ?></strong>
                </div>
                <?php if (!empty($order['discount_amount']) && (float)$order['discount_amount'] > 0): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; font-size: 0.95rem; color: var(--success); font-weight: 700;">
                        <span>Voucher Discount (<?= sanitize($order['coupon_code'] ?? 'PROMO') ?>):</span>
                        <span>-<?= format_price($order['discount_amount']) ?></span>
                    </div>
                <?php endif; ?>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem; font-size: 0.95rem;">
                    <span>Insured Transit:</span>
                    <span><?= ($order['total_amount'] - ($calc_subtotal - (float)($order['discount_amount'] ?? 0))) <= 0.01 ? '<strong style="color: var(--success);">COMPLIMENTARY</strong>' : format_price($order['total_amount'] - ($calc_subtotal - (float)($order['discount_amount'] ?? 0))) ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding-top: 0.75rem; border-top: 2px solid var(--border); font-size: 1.35rem; font-weight: 900; color: var(--primary);">
                    <span>Total Settled:</span>
                    <span><?= format_price($order['total_amount']) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Fulfillment Controls & Shipping -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <div class="card">
            <h3 class="card-title">Courier &amp; Fulfillment</h3>
            <form method="POST" action="<?= BASE_URL ?>/admin/order-details.php?id=<?= $order['id'] ?>">
                <input type="hidden" name="action" value="update_status">
                <div class="form-group">
                    <label class="form-label" for="status">Stage Status</label>
                    <select name="status" id="status" class="form-control" style="font-weight: 700;">
                        <?php 
                        $status_options = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
                        foreach ($status_options as $opt):
                        ?>
                            <option value="<?= $opt ?>" <?= $order['status'] === $opt ? 'selected' : '' ?>>
                                <?= $opt ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-pill btn-block">
                    Update Fulfillment Stage
                </button>
            </form>
        </div>

        <div class="card">
            <h3 class="card-title">Collector Certificate Info</h3>

            <div style="margin-bottom: 1.25rem;">
                <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; display: block; letter-spacing: 0.05em;">Client Recipient</span>
                <strong style="font-size: 1.1rem; color: var(--dark);"><?= sanitize($order['shipping_name']) ?></strong>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; display: block; letter-spacing: 0.05em;">Client Email</span>
                <a href="mailto:<?= sanitize($order['shipping_email']) ?>"><?= sanitize($order['shipping_email']) ?></a>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; display: block; letter-spacing: 0.05em;">Direct Phone</span>
                <span><?= sanitize($order['shipping_phone']) ?></span>
            </div>

            <div style="margin-bottom: 1.25rem;">
                <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; display: block; letter-spacing: 0.05em;">Delivery Destination</span>
                <p style="font-size: 0.95rem; line-height: 1.6; margin: 0; color: var(--text);">
                    <?= nl2br(sanitize($order['shipping_address'])) ?><br>
                    <?= sanitize($order['city']) ?>, <?= sanitize($order['zip_code']) ?>
                </p>
            </div>

            <div>
                <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; display: block; letter-spacing: 0.05em;">Settlement Method</span>
                <strong style="color: var(--dark);"><?= sanitize($order['payment_method']) ?></strong>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
