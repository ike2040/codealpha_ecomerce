<?php
/**
 * Helper Functions
 * Reusable utilities used across the application
 */

// Define application base URL (dynamically detected relative to web root)
if (!defined('BASE_URL')) {
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = str_replace('\\', '/', dirname($script));
    if (basename($dir) === 'admin' || basename($dir) === 'config') {
        $base = dirname($dir);
    } else {
        $base = $dir;
    }
    $base = rtrim($base, '/');
    define('BASE_URL', $base);
}

/**
 * Sanitize output to prevent XSS attacks
 */
function sanitize($value) {
    if (is_null($value)) return '';
    return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
}

/**
 * Format a number as currency (USD)
 */
function format_price($amount) {
    return '$' . number_format((float)$amount, 2);
}

/**
 * Redirect to a URL
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Get total number of items in cart
 */
function get_cart_count() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += (int)$item['quantity'];
    }
    return $count;
}

/**
 * Get all items in the cart
 */
function get_cart_items() {
    return $_SESSION['cart'] ?? [];
}

/**
 * Get cart subtotal price
 */
function get_cart_total() {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0.00;
    }
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += (float)$item['price'] * (int)$item['quantity'];
    }
    return $total;
}

/**
 * Add item to cart session
 */
function add_to_cart($product_id, $name, $price, $image, $quantity = 1, $max_stock = 9999) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $product_id = (int)$product_id;
    $quantity = max(1, (int)$quantity);

    if (isset($_SESSION['cart'][$product_id])) {
        $new_qty = $_SESSION['cart'][$product_id]['quantity'] + $quantity;
        // Cap quantity at available stock
        $_SESSION['cart'][$product_id]['quantity'] = min($new_qty, $max_stock);
    } else {
        $_SESSION['cart'][$product_id] = [
            'product_id' => $product_id,
            'name'       => $name,
            'price'      => (float)$price,
            'image'      => $image,
            'quantity'   => min($quantity, $max_stock)
        ];
    }
}

/**
 * Update quantity of an item in the cart
 */
function update_cart_quantity($product_id, $quantity) {
    $product_id = (int)$product_id;
    $quantity = (int)$quantity;

    if (isset($_SESSION['cart'][$product_id])) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        }
    }
}

/**
 * Remove an item from the cart
 */
function remove_from_cart($product_id) {
    $product_id = (int)$product_id;
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

/**
 * Clear the entire shopping cart
 */
function clear_cart() {
    $_SESSION['cart'] = [];
}

/**
 * Check stock availability for a product in the database
 */
function check_stock($product_id, $quantity = 1) {
    $db = getDB();
    $stmt = $db->prepare("SELECT stock_quantity FROM products WHERE id = ?");
    $stmt->execute([(int)$product_id]);
    $stock = $stmt->fetchColumn();
    
    if ($stock === false) {
        return false;
    }
    return (int)$stock >= (int)$quantity;
}

/**
 * Get product image URL, using an SVG default if none uploaded
 */
function get_product_image($image) {
    $image = trim((string)$image);
    if ($image !== '' && $image !== 'default-product.jpg' && $image !== 'default-product.svg') {
        $uploadPath = __DIR__ . '/../uploads/products/' . $image;
        if (file_exists($uploadPath)) {
            return BASE_URL . '/uploads/products/' . rawurlencode($image);
        }
        $assetPath = __DIR__ . '/../assets/images/' . $image;
        if (file_exists($assetPath)) {
            return BASE_URL . '/assets/images/' . rawurlencode($image);
        }
    }
    return BASE_URL . '/assets/images/default-product.svg';
}

/**
 * Get status badge CSS class
 */
function get_status_class($status) {
    $classes = [
        'Pending'    => 'badge-warning',
        'Processing' => 'badge-info',
        'Shipped'    => 'badge-primary',
        'Delivered'  => 'badge-success',
        'Cancelled'  => 'badge-danger',
    ];
    return $classes[$status] ?? 'badge-secondary';
}

/**
 * Truncate text to a given number of characters
 */
function truncate($text, $limit = 100) {
    if (strlen($text) <= $limit) return $text;
    return substr($text, 0, $limit) . '...';
}

/**
 * Render star rating HTML
 */
function render_stars($rating = 5.0) {
    $rating = (float)$rating;
    $full = floor($rating);
    $half = ($rating - $full) >= 0.5 ? 1 : 0;
    $empty = max(0, 5 - $full - $half);
    
    $html = '<span class="star-rating" title="' . number_format($rating, 1) . ' out of 5 stars">';
    $html .= str_repeat('<span class="star star-full" style="color: #f59e0b;">★</span>', (int)$full);
    if ($half) {
        $html .= '<span class="star star-half" style="color: #f59e0b;">★</span>';
    }
    $html .= str_repeat('<span class="star star-empty" style="color: #cbd5e1;">☆</span>', (int)$empty);
    $html .= '</span>';
    return $html;
}

/**
 * Wishlist Functions
 */
function get_wishlist_items() {
    return $_SESSION['wishlist'] ?? [];
}

function get_wishlist_count() {
    return isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0;
}

function is_in_wishlist($product_id) {
    return isset($_SESSION['wishlist'][(int)$product_id]);
}

function toggle_wishlist($product_id) {
    $product_id = (int)$product_id;
    if (!isset($_SESSION['wishlist'])) {
        $_SESSION['wishlist'] = [];
    }
    if (isset($_SESSION['wishlist'][$product_id])) {
        unset($_SESSION['wishlist'][$product_id]);
        return false; // Removed
    } else {
        $_SESSION['wishlist'][$product_id] = time();
        return true; // Added
    }
}

/**
 * Coupon Functions
 */
function get_applied_coupon() {
    return $_SESSION['applied_coupon'] ?? null;
}

function apply_coupon($code, $subtotal) {
    $code = strtoupper(trim((string)$code));
    if (empty($code)) {
        return ['success' => false, 'message' => 'Please enter a voucher code.'];
    }

    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();

    if (!$coupon) {
        return ['success' => false, 'message' => 'Invalid or expired voucher code. Try code "VIP10".'];
    }

    $discount_percent = (int)$coupon['discount_percent'];
    $discount_amount = round(($subtotal * ($discount_percent / 100)), 2);

    $_SESSION['applied_coupon'] = [
        'code'            => $coupon['code'],
        'discount_percent'=> $discount_percent,
        'discount_amount' => $discount_amount,
        'description'     => $coupon['description']
    ];

    return [
        'success'         => true,
        'message'         => '✓ Voucher code "' . $coupon['code'] . '" applied: ' . $discount_percent . '% off!',
        'discount_amount' => $discount_amount
    ];
}

function remove_coupon() {
    unset($_SESSION['applied_coupon']);
}

