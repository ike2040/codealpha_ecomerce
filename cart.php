<?php
/**
 * IKE PRODUCTS LOUNGE - Shopping Bag (cart.php)
 * Review reserved collectibles, update quantities, and calculate insured shipping
 */

$page_title = 'Collection Bag | IKE PRODUCTS LOUNGE';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/functions.php';

$db = getDB();

// Handle POST actions: update_qty, remove_item, clear_cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_qty') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $new_qty    = (int)($_POST['quantity'] ?? 1);

        if ($product_id > 0) {
            $s_stmt = $db->prepare("SELECT stock_quantity, name FROM products WHERE id = ?");
            $s_stmt->execute([$product_id]);
            $prod = $s_stmt->fetch();

            if ($prod) {
                if ($new_qty <= 0) {
                    remove_from_cart($product_id);
                    set_flash('info', 'Removed item from your collection bag.');
                } elseif ($new_qty > $prod['stock_quantity']) {
                    update_cart_quantity($product_id, $prod['stock_quantity']);
                    set_flash('warning', 'Only ' . $prod['stock_quantity'] . ' unit(s) of "' . sanitize($prod['name']) . '" available. Quantity adjusted.');
                } else {
                    update_cart_quantity($product_id, $new_qty);
                    set_flash('success', 'Collection bag updated.');
                }
            }
        }
        redirect(BASE_URL . '/cart.php');
    }

    if ($action === 'remove_item') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        if ($product_id > 0) {
            remove_from_cart($product_id);
            set_flash('info', 'Piece removed from your collection bag.');
        }
        redirect(BASE_URL . '/cart.php');
    }

    if ($action === 'clear_cart') {
        clear_cart();
        remove_coupon();
        set_flash('info', 'Your collection bag has been cleared.');
        redirect(BASE_URL . '/cart.php');
    }

    if ($action === 'apply_coupon') {
        $coupon_code = trim($_POST['coupon_code'] ?? '');
        $subtotal    = get_cart_total();
        $res = apply_coupon($coupon_code, $subtotal);
        if ($res['success']) {
            set_flash('success', $res['message']);
        } else {
            set_flash('error', $res['message']);
        }
        redirect(BASE_URL . '/cart.php');
    }

    if ($action === 'remove_coupon') {
        remove_coupon();
        set_flash('info', 'Voucher removed from your collection bag.');
        redirect(BASE_URL . '/cart.php');
    }
}

$cart_items = get_cart_items();
$subtotal = get_cart_total();

// Coupon Discount
$applied_coupon = get_applied_coupon();
$discount_amount = 0.00;
if ($applied_coupon && $subtotal > 0) {
    $discount_amount = round(($subtotal * ($applied_coupon['discount_percent'] / 100)), 2);
    $_SESSION['applied_coupon']['discount_amount'] = $discount_amount;
} else {
    remove_coupon();
    $applied_coupon = null;
}

// High-end collectibles: Free insured courier on orders over $500, otherwise $35 flat rate
$shipping = (($subtotal - $discount_amount) >= 500 || $subtotal == 0) ? 0.00 : 35.00;
$total = max(0, $subtotal - $discount_amount + $shipping);


require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="section">
    <div class="container">
        <div style="margin-bottom: 2.5rem;">
            <h1 style="font-size: 2.25rem; margin-bottom: 0.5rem;">Reserved Collectibles Bag</h1>
            <p style="color: var(--text-muted);">Review your allocated statues and limited edition pieces before proceeding to secure acquisition.</p>
        </div>

        <?php if (empty($cart_items)): ?>
            <div class="card" style="text-align: center; padding: 4.5rem 2rem;">
                <div style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.8;">🛍️</div>
                <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Your Collection Bag is Empty</h3>
                <p style="color: var(--text-muted); margin-bottom: 2rem; max-width: 440px; margin-left: auto; margin-right: auto;">
                    You have not reserved any limited edition statues yet. Explore our museum-scale gallery to discover our newest arrivals.
                </p>
                <a href="<?= BASE_URL ?>/products.php" class="btn btn-primary btn-pill btn-lg">
                    Browse All Collectibles
                </a>
            </div>
        <?php else: ?>
            <div class="cart-grid">
                <!-- Left: Items Table -->
                <div class="card" style="padding: 0; overflow: hidden;">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">Piece</th>
                                    <th>Description</th>
                                    <th>Studio Price</th>
                                    <th style="width: 130px;">Allocation</th>
                                    <th>Subtotal</th>
                                    <th style="text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $id => $item): 
                                    $item_subtotal = $item['price'] * $item['quantity'];
                                ?>
                                    <tr>
                                        <td>
                                            <a href="<?= BASE_URL ?>/product-details.php?id=<?= $id ?>">
                                                <img src="<?= get_product_image($item['image']) ?>" alt="<?= sanitize($item['name']) ?>" class="table-thumb">
                                            </a>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/product-details.php?id=<?= $id ?>" style="font-weight: 800; color: var(--dark); font-size: 1.05rem;">
                                                <?= sanitize($item['name']) ?>
                                            </a>
                                            <div style="font-size: 0.775rem; color: var(--text-subtle); text-transform: uppercase;">1/4 Scale Polystone</div>
                                        </td>
                                        <td style="font-weight: 700;"><?= format_price($item['price']) ?></td>
                                        <td>
                                            <form method="POST" action="<?= BASE_URL ?>/cart.php" style="display: flex; align-items: center; gap: 0.35rem; margin: 0;">
                                                <input type="hidden" name="action" value="update_qty">
                                                <input type="hidden" name="product_id" value="<?= $id ?>">
                                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="99" class="form-control" style="width: 60px; padding: 0.35rem; text-align: center; border-radius: var(--radius-sm); font-weight: 700;" onchange="this.form.submit()">
                                                <button type="submit" class="btn btn-secondary btn-sm" title="Update" style="padding: 0.35rem 0.6rem;">↻</button>
                                            </form>
                                        </td>
                                        <td style="font-weight: 900; color: var(--dark); font-size: 1.1rem;">
                                            <?= format_price($item_subtotal) ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <form method="POST" action="<?= BASE_URL ?>/cart.php" style="margin: 0; display: inline;">
                                                <input type="hidden" name="action" value="remove_item">
                                                <input type="hidden" name="product_id" value="<?= $id ?>">
                                                <button type="submit" class="btn btn-secondary btn-sm" data-confirm="Remove '<?= sanitize($item['name']) ?>' from your collection bag?" style="color: var(--danger) !important; border-color: #fca5a5;">
                                                    ✕
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div style="padding: 1.25rem 1.75rem; background: #f8fafc; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                        <a href="<?= BASE_URL ?>/products.php" class="btn btn-secondary btn-sm btn-pill">&larr; Return to Galleries</a>
                        <form method="POST" action="<?= BASE_URL ?>/cart.php" style="margin: 0;">
                            <input type="hidden" name="action" value="clear_cart">
                            <button type="submit" class="btn btn-secondary btn-sm btn-pill" data-confirm="Release all reserved items and clear your bag?" style="color: var(--danger) !important;">
                                Release All Reservations
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right: Summary Card -->
                <div class="card">
                    <h3 class="card-title">Acquisition Summary</h3>

                    <div class="cart-summary-line">
                        <span>Items Subtotal:</span>
                        <strong><?= format_price($subtotal) ?></strong>
                    </div>

                    <?php if ($applied_coupon): ?>
                        <div class="cart-summary-line" style="color: var(--success); font-weight: 700;">
                            <span>
                                🏷️ <?= sanitize($applied_coupon['code']) ?> (<?= $applied_coupon['discount_percent'] ?>% off):
                            </span>
                            <span>-<?= format_price($discount_amount) ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="cart-summary-line">
                        <span>White-Glove Insured Courier:</span>
                        <span>
                            <?php if ($shipping === 0.00): ?>
                                <span style="color: var(--success); font-weight: 700;">COMPLIMENTARY</span>
                            <?php else: ?>
                                <?= format_price($shipping) ?>
                            <?php endif; ?>
                        </span>
                    </div>

                    <?php if ($shipping === 0.00): ?>
                        <div style="background: var(--success-bg); color: var(--success); font-size: 0.85rem; padding: 0.6rem 0.95rem; border-radius: var(--radius-md); margin: 0.75rem 0; font-weight: 600;">
                            ✓ Your order qualifies for Complimentary White-Glove Insured Dispatch!
                        </div>
                    <?php endif; ?>

                    <div class="cart-summary-total">
                        <span>Total:</span>
                        <span style="color: var(--primary);"><?= format_price($total) ?></span>
                    </div>

                    <!-- Voucher Promo Code Box -->
                    <div style="margin-top: 1.5rem; padding: 1.1rem; background: #f8fafc; border-radius: var(--radius-md); border: 1px dashed var(--border);">
                        <?php if ($applied_coupon): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <span class="badge badge-success" style="font-size: 0.8rem;">✓ <?= sanitize($applied_coupon['code']) ?> APPLIED</span>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;"><?= sanitize($applied_coupon['description']) ?></div>
                                </div>
                                <form method="POST" action="<?= BASE_URL ?>/cart.php" style="margin: 0;">
                                    <input type="hidden" name="action" value="remove_coupon">
                                    <button type="submit" class="btn btn-secondary btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; color: var(--danger) !important;">Remove</button>
                                </form>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="<?= BASE_URL ?>/cart.php" style="display: flex; gap: 0.5rem; margin: 0;">
                                <input type="hidden" name="action" value="apply_coupon">
                                <input type="text" name="coupon_code" placeholder="Promo code (e.g. VIP10)" class="form-control" style="font-size: 0.85rem; padding: 0.5rem 0.75rem; text-transform: uppercase;">
                                <button type="submit" class="btn btn-secondary btn-sm btn-pill" style="font-weight: 700; white-space: nowrap;">Apply</button>
                            </form>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">
                                💡 Tip: Use voucher code <strong style="color: var(--primary); cursor: pointer;" onclick="document.querySelector('[name=coupon_code]').value='VIP10'">VIP10</strong> for 10% privilege discount.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="margin-top: 1.5rem;">
                        <a href="<?= BASE_URL ?>/checkout.php" class="btn btn-buy-now btn-block btn-lg" style="text-align: center;">
                            Proceed to Checkout &rarr;
                        </a>
                    </div>

                    <div style="margin-top: 1.5rem; text-align: center; font-size: 0.8rem; color: var(--text-muted); line-height: 1.6;">
                        🛡️ <strong>Collector Peace of Mind</strong><br>
                        Includes Numbered Certificate of Authenticity &amp; Custom Foam Reinforcement.
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
