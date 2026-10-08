<?php
/**
 * IKE PRODUCTS LOUNGE - Secure Checkout (checkout.php)
 * White-glove shipping details, payment method selection, and transactional order execution
 */

$page_title = 'Secure Acquisition Checkout | IKE PRODUCTS LOUNGE';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/functions.php';

$db = getDB();
$cart_items = get_cart_items();

// Redirect to cart if empty
if (empty($cart_items)) {
    set_flash('error', 'Your collection bag is empty. Please select a statue before checkout.');
    redirect(BASE_URL . '/products.php');
}

// Pre-fill user data if logged in
// Validate session user actually exists in DB (guards against stale sessions after re-seed)
$user_id = null;
if (!empty($_SESSION['user_id'])) {
    $uid_check = $db->prepare("SELECT id FROM users WHERE id = ?");
    $uid_check->execute([$_SESSION['user_id']]);
    if ($uid_check->fetchColumn()) {
        $user_id = (int)$_SESSION['user_id'];
    } else {
        // Stale session — clear user_id to prevent FK violation
        unset($_SESSION['user_id']);
    }
}
$shipping_name    = '';
$shipping_email   = '';
$shipping_phone   = '';
$shipping_address = '';
$city             = '';
$zip_code         = '';

if ($user_id) {
    $u_stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $u_stmt->execute([$user_id]);
    $user = $u_stmt->fetch();
    if ($user) {
        $shipping_name    = $user['name'];
        $shipping_email   = $user['email'];
        $shipping_phone   = $user['phone'] ?? '';
        $shipping_address = $user['address'] ?? '';
    }
}

$subtotal = get_cart_total();
$applied_coupon = get_applied_coupon();
$discount_amount = 0.00;
if ($applied_coupon && $subtotal > 0) {
    $discount_amount = round(($subtotal * ($applied_coupon['discount_percent'] / 100)), 2);
}
$shipping = (($subtotal - $discount_amount) >= 500 || $subtotal == 0) ? 0.00 : 35.00;
$total = max(0, $subtotal - $discount_amount + $shipping);

$errors = [];

// Process Checkout Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_name    = trim($_POST['shipping_name'] ?? '');
    $shipping_email   = trim($_POST['shipping_email'] ?? '');
    $shipping_phone   = trim($_POST['shipping_phone'] ?? '');
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    $city             = trim($_POST['city'] ?? '');
    $zip_code         = trim($_POST['zip_code'] ?? '');
    $payment_method   = trim($_POST['payment_method'] ?? 'Credit / Debit Card');

    // Validation
    if (empty($shipping_name))    $errors['shipping_name'] = 'Full legal name is required for certificate issuance.';
    if (empty($shipping_email) || !filter_var($shipping_email, FILTER_VALIDATE_EMAIL)) {
        $errors['shipping_email'] = 'A valid email address is required for receipt & tracking.';
    }
    if (empty($shipping_phone))   $errors['shipping_phone'] = 'Contact phone number is required for courier coordination.';
    if (empty($shipping_address)) $errors['shipping_address'] = 'Street address is required.';
    if (empty($city))             $errors['city'] = 'City is required.';
    if (empty($zip_code))         $errors['zip_code'] = 'ZIP / Postal code is required.';

    // Validate Stock Availability for all items before committing
    if (empty($errors)) {
        foreach ($cart_items as $prod_id => $item) {
            $stk_stmt = $db->prepare("SELECT stock_quantity, name FROM products WHERE id = ?");
            $stk_stmt->execute([$prod_id]);
            $current_product = $stk_stmt->fetch();

            if (!$current_product || $current_product['stock_quantity'] < $item['quantity']) {
                $avail = $current_product ? $current_product['stock_quantity'] : 0;
                $errors['stock'] = 'Sorry, "' . sanitize($item['name']) . '" only has ' . $avail . ' unit(s) remaining in the studio archive.';
                break;
            }
        }
    }

    // Execute Database Transaction
    if (empty($errors)) {
        try {
            $db->beginTransaction();

            $coupon_code = $applied_coupon ? $applied_coupon['code'] : null;

            // 1. Insert Order
            $order_stmt = $db->prepare("
                INSERT INTO `orders` 
                (`user_id`, `total_amount`, `discount_amount`, `coupon_code`, `shipping_name`, `shipping_email`, `shipping_phone`, `shipping_address`, `city`, `zip_code`, `payment_method`, `status`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
            ");
            $order_stmt->execute([
                $user_id,
                $total,
                $discount_amount,
                $coupon_code,
                $shipping_name,
                $shipping_email,
                $shipping_phone,
                $shipping_address,
                $city,
                $zip_code,
                $payment_method
            ]);

            $order_id = $db->lastInsertId();

            // 2. Insert Order Items & Deduct Stock
            $item_stmt  = $db->prepare("INSERT INTO `order_items` (`order_id`, `product_id`, `price`, `quantity`) VALUES (?, ?, ?, ?)");
            $stock_stmt = $db->prepare("UPDATE `products` SET `stock_quantity` = `stock_quantity` - ? WHERE `id` = ?");

            foreach ($cart_items as $prod_id => $item) {
                $item_stmt->execute([$order_id, $prod_id, $item['price'], $item['quantity']]);
                $stock_stmt->execute([$item['quantity'], $prod_id]);
            }

            // Commit transaction
            $db->commit();

            // Clear session cart & voucher
            clear_cart();
            remove_coupon();

            // Flash success & redirect to confirmation
            set_flash('success', '✨ Order successfully placed! Your acquisition receipt and certificate of authenticity have been created.');
            redirect(BASE_URL . '/order-confirmation.php?order_id=' . $order_id);

        } catch (Exception $e) {
            $db->rollBack();
            $errors['system'] = 'An unexpected error occurred while placing your order. Please try again. (' . $e->getMessage() . ')';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="section">
    <div class="container">
        <div style="margin-bottom: 2.5rem;">
            <h1 style="font-size: 2.25rem; margin-bottom: 0.5rem;">Collector Acquisition &amp; Checkout</h1>
            <p style="color: var(--text-muted);">Provide recipient verification for your numbered certificate of authenticity and white-glove transit delivery.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div style="background: var(--danger-bg); border: 1px solid #fca5a5; color: var(--danger); padding: 1rem 1.25rem; border-radius: var(--radius-md); margin-bottom: 2rem;">
                <strong>⚠️ Attention Required:</strong>
                <ul style="margin-left: 1.5rem; margin-top: 0.5rem; font-size: 0.9rem;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= sanitize($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/checkout.php" data-validate>
            <div class="checkout-grid">
                <!-- Left: Shipping & Verification Form -->
                <div class="card">
                    <h3 class="card-title">1. Certificate &amp; Delivery Destination</h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="shipping_name">Full Legal Name (for Certificate) *</label>
                            <input type="text" name="shipping_name" id="shipping_name" class="form-control" value="<?= sanitize($shipping_name) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="shipping_email">Email Address (for Transit Notifications) *</label>
                            <input type="email" name="shipping_email" id="shipping_email" class="form-control" value="<?= sanitize($shipping_email) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="shipping_phone">Direct Telephone (for Courier Appointment) *</label>
                        <input type="tel" name="shipping_phone" id="shipping_phone" class="form-control" placeholder="+1 (555) 000-0000" value="<?= sanitize($shipping_phone) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="shipping_address">Delivery Address *</label>
                        <textarea name="shipping_address" id="shipping_address" rows="2" class="form-control" placeholder="Street address, penthouse, suite, or apartment" required><?= sanitize($shipping_address) ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="city">City *</label>
                            <input type="text" name="city" id="city" class="form-control" value="<?= sanitize($city) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="zip_code">Postal / ZIP Code *</label>
                            <input type="text" name="zip_code" id="zip_code" class="form-control" value="<?= sanitize($zip_code) ?>" required>
                        </div>
                    </div>

                    <h3 class="card-title" style="margin-top: 2rem;">2. Payment Method</h3>

                    <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                        <label class="card" style="display: flex; align-items: center; gap: 1rem; cursor: pointer; padding: 1.25rem; margin: 0; border: 1.5px solid var(--border-dark);">
                            <input type="radio" name="payment_method" value="Credit / Debit Card" checked style="accent-color: var(--primary); transform: scale(1.25);">
                            <div>
                                <strong style="display: block; color: var(--dark); font-size: 1rem;">💳 Credit / Debit Card (Visa, MasterCard, Amex)</strong>
                                <span style="font-size: 0.825rem; color: var(--text-muted);">Immediate encrypted studio payment with transit fraud protection.</span>
                            </div>
                        </label>

                        <label class="card" style="display: flex; align-items: center; gap: 1rem; cursor: pointer; padding: 1.25rem; margin: 0; border: 1.5px solid var(--border-dark);">
                            <input type="radio" name="payment_method" value="Direct Bank Wire Transfer" style="accent-color: var(--primary); transform: scale(1.25);">
                            <div>
                                <strong style="display: block; color: var(--dark); font-size: 1rem;">🏦 Studio Wire Transfer (Escrow)</strong>
                                <span style="font-size: 0.825rem; color: var(--text-muted);">Direct bank transfer to our secured client escrow account.</span>
                            </div>
                        </label>

                        <label class="card" style="display: flex; align-items: center; gap: 1rem; cursor: pointer; padding: 1.25rem; margin: 0; border: 1.5px solid var(--border-dark);">
                            <input type="radio" name="payment_method" value="Cash on Delivery (White-Glove)" style="accent-color: var(--primary); transform: scale(1.25);">
                            <div>
                                <strong style="display: block; color: var(--dark); font-size: 1rem;">💵 Cash / Certified Check on Delivery</strong>
                                <span style="font-size: 0.825rem; color: var(--text-muted);">Inspect crate and certificate upon arrival before payment release.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Right: Breakdown -->
                <div class="card">
                    <h3 class="card-title">Order Allocation</h3>

                    <div style="max-height: 260px; overflow-y: auto; margin-bottom: 1.25rem; padding-right: 0.5rem;">
                        <?php foreach ($cart_items as $item): ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; padding: 0.75rem 0; border-bottom: 1px solid #f1f5f9;">
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <img src="<?= get_product_image($item['image']) ?>" alt="" style="width: 44px; height: 44px; object-fit: contain; border-radius: 6px; background: #f8fafc;">
                                    <div>
                                        <div style="font-weight: 800; color: var(--dark); line-height: 1.2; font-size: 0.95rem;"><?= sanitize($item['name']) ?></div>
                                        <span style="color: var(--text-muted); font-size: 0.8rem;">Qty: <?= $item['quantity'] ?> &times; <?= format_price($item['price']) ?></span>
                                    </div>
                                </div>
                                <span style="font-weight: 800; color: var(--dark); font-size: 1rem;"><?= format_price($item['price'] * $item['quantity']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="cart-summary-line">
                        <span>Items Subtotal:</span>
                        <strong><?= format_price($subtotal) ?></strong>
                    </div>

                    <?php if ($applied_coupon): ?>
                        <div class="cart-summary-line" style="color: var(--success); font-weight: 700;">
                            <span>🏷️ Privilege Voucher (<?= sanitize($applied_coupon['code']) ?> - <?= $applied_coupon['discount_percent'] ?>%):</span>
                            <span>-<?= format_price($discount_amount) ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="cart-summary-line">
                        <span>Insured Transit:</span>
                        <span><?= $shipping === 0.00 ? '<strong style="color: var(--success);">COMPLIMENTARY</strong>' : format_price($shipping) ?></span>
                    </div>

                    <div class="cart-summary-total">
                        <span>Total Payable:</span>
                        <span style="color: var(--primary);"><?= format_price($total) ?></span>
                    </div>

                    <div style="margin-top: 2rem;">
                        <button type="submit" class="btn btn-buy-now btn-block btn-lg" style="width: 100%;">
                            🔒 Confirm &amp; Complete Acquisition
                        </button>
                    </div>

                    <div style="margin-top: 1.25rem; text-align: center;">
                        <a href="<?= BASE_URL ?>/cart.php" style="font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">&larr; Modify Collection Bag</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
