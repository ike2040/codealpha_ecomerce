<?php
/**
 * IKE PRODUCTS LOUNGE - Acquisition Receipt & Certificate (order-confirmation.php)
 * Displays verified order receipt, numbered certificate allocation, and transit tracking
 */

$page_title = 'Acquisition Confirmed | IKE PRODUCTS LOUNGE';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/functions.php';

$db = getDB();
$order_id = (int)($_GET['order_id'] ?? 0);

if ($order_id <= 0) {
    set_flash('error', 'Invalid acquisition reference.');
    redirect(BASE_URL . '/index.php');
}

// Fetch order
$stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    set_flash('error', 'Acquisition record not found.');
    redirect(BASE_URL . '/index.php');
}

// Fetch order items
$item_stmt = $db->prepare("
    SELECT oi.*, p.name AS product_name, p.image AS product_image 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$item_stmt->execute([$order_id]);
$order_items = $item_stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="section">
    <div class="container container-sm">
        <!-- Success Banner -->
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <div style="width: 76px; height: 76px; background: var(--primary-light); color: var(--primary); font-size: 2.5rem; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; margin-bottom: 1.25rem; box-shadow: var(--shadow-blue);">
                ✓
            </div>
            <h1 style="font-size: 2.4rem; margin-bottom: 0.5rem; color: var(--dark);">Acquisition Successfully Confirmed</h1>
            <p style="color: var(--text-muted); font-size: 1.1rem; max-width: 540px; margin: 0 auto;">
                Thank you for your patronage. Your limited-edition pieces are now reserved for custom crating and insured transit.
            </p>
        </div>

        <!-- Receipt Card -->
        <div class="card" style="margin-bottom: 2.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border); margin-bottom: 1.75rem;">
                <div>
                    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block;">Studio Acquisition Reference:</span>
                    <strong style="font-size: 1.4rem; color: var(--primary);">#IPL-<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></strong>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; display: block;">Allocation Date:</span>
                    <span style="font-weight: 700; color: var(--dark);"><?= date('F j, Y, g:i A', strtotime($order['created_at'])) ?></span>
                </div>
                <div>
                    <span class="badge <?= get_status_class($order['status']) ?>" style="font-size: 0.85rem; padding: 0.4rem 1rem;">
                        ● <?= sanitize($order['status']) ?>
                    </span>
                </div>
            </div>

            <!-- Customer & Shipping Summary Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; background: #f8fafc; padding: 1.5rem; border-radius: var(--radius-md);">
                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; display: block; margin-bottom: 0.35rem; letter-spacing: 0.05em;">Certificate Issued To</span>
                    <strong style="font-size: 1.05rem; color: var(--dark);"><?= sanitize($order['shipping_name']) ?></strong><br>
                    <span style="font-size: 0.85rem; color: var(--text-muted);"><?= sanitize($order['shipping_email']) ?></span><br>
                    <span style="font-size: 0.85rem; color: var(--text-muted);">📞 <?= sanitize($order['shipping_phone']) ?></span>
                </div>
                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; display: block; margin-bottom: 0.35rem; letter-spacing: 0.05em;">Insured Delivery Address</span>
                    <p style="font-size: 0.9rem; line-height: 1.5; color: var(--text); margin: 0;">
                        <?= nl2br(sanitize($order['shipping_address'])) ?><br>
                        <?= sanitize($order['city']) ?>, <?= sanitize($order['zip_code']) ?>
                    </p>
                </div>
                <div>
                    <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 800; display: block; margin-bottom: 0.35rem; letter-spacing: 0.05em;">Payment Settlement</span>
                    <strong style="color: var(--dark);"><?= sanitize($order['payment_method']) ?></strong><br>
                    <span style="font-size: 0.85rem; color: var(--success); font-weight: 600;">Transit Insurance Active</span>
                </div>
            </div>

            <!-- Items Table -->
            <h3 style="font-size: 1.2rem; margin-bottom: 1.25rem;">Allocated Masterline Collectibles</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Piece</th>
                            <th>Unit Price</th>
                            <th>Quantity</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): 
                            $line_total = $item['price'] * $item['quantity'];
                        ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.85rem;">
                                        <img src="<?= get_product_image($item['product_image']) ?>" alt="" class="table-thumb" style="width: 50px; height: 50px;">
                                        <span style="font-weight: 800; color: var(--dark); font-size: 1.05rem;"><?= sanitize($item['product_name'] ?? 'Edition #' . $item['product_id']) ?></span>
                                    </div>
                                </td>
                                <td style="font-weight: 600;"><?= format_price($item['price']) ?></td>
                                <td><strong><?= (int)$item['quantity'] ?></strong></td>
                                <td style="text-align: right; font-weight: 900; color: var(--dark); font-size: 1.1rem;"><?= format_price($line_total) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Total Paid Line -->
            <div style="display: flex; flex-direction: column; align-items: flex-end; margin-top: 1.75rem; padding-top: 1.25rem; border-top: 2px dashed var(--border);">
                <?php if (!empty($order['discount_amount']) && (float)$order['discount_amount'] > 0): ?>
                    <div style="min-width: 250px; text-align: right; margin-bottom: 0.5rem; color: var(--success); font-weight: 700;">
                        <span style="font-size: 0.95rem; margin-right: 1rem;">Voucher Privilege (<?= sanitize($order['coupon_code'] ?? 'VIP') ?>):</span>
                        <span style="font-size: 1.1rem;">-<?= format_price($order['discount_amount']) ?></span>
                    </div>
                <?php endif; ?>
                <div style="min-width: 250px; text-align: right;">
                    <span style="font-size: 1rem; color: var(--text-muted); margin-right: 1rem;">Total Settled:</span>
                    <span style="font-size: 1.85rem; font-weight: 900; color: var(--primary);"><?= format_price($order['total_amount']) ?></span>
                </div>
            </div>

            <!-- Official Studio Authenticity Plaque (Printed & On-Screen) -->
            <div style="margin-top: 2.5rem; padding: 1.5rem; background: #fafafa; border: 1.5px solid var(--border); border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 52px; height: 52px; background: #0A0E2A; color: #FF5436; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 900; border: 2px solid #FF5436;">
                        IPL
                    </div>
                    <div>
                        <div style="font-weight: 900; color: var(--dark); font-size: 0.95rem; letter-spacing: 0.05em; text-transform: uppercase;">
                            Certificate of Authenticity Verified
                        </div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">
                            Officially licensed masterline casting &bull; Insured transit warranty active
                        </div>
                    </div>
                </div>
                <div style="text-align: right;">
                    <div style="font-family: serif; font-style: italic; font-size: 1.25rem; color: #0A0E2A; font-weight: bold;">
                        Isaac Ofori
                    </div>
                    <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--text-subtle); font-weight: 800;">
                        Founder &amp; Managing Director
                    </div>
                </div>
            </div>
        </div>

        <!-- Next Actions & Print Trigger -->
        <div style="display: flex; justify-content: center; gap: 1.25rem; flex-wrap: wrap;" class="no-print">
            <button onclick="window.print()" class="btn btn-secondary btn-pill btn-lg" style="display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 700;">
                🖨️ Print Certificate &amp; Receipt
            </button>
            <a href="<?= BASE_URL ?>/products.php" class="btn btn-primary btn-pill btn-lg">
                Explore More Statues &rarr;
            </a>
            <?php if (is_logged_in()): ?>
                <a href="<?= BASE_URL ?>/profile.php" class="btn btn-secondary btn-pill btn-lg">
                    Collector Order Vault
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
