<?php
/**
 * IKE PRODUCTS LOUNGE - Product Details (product-details.php)
 * Faithful recreation of the reference UI/UX design:
 * Organic cobalt blue wave, centerpiece pedestal statue, big price, coral pill BUY NOW & circular cart button
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/functions.php';

$db = getDB();
$product_id = (int)($_GET['id'] ?? 0);

if ($product_id <= 0) {
    set_flash('error', 'Please select a valid collector piece.');
    redirect(BASE_URL . '/products.php');
}

// Fetch product with category info
$stmt = $db->prepare("
    SELECT p.*, c.name AS category_name, c.slug AS category_slug 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('error', 'The requested collectible is not in the archive.');
    redirect(BASE_URL . '/products.php');
}

$page_title = $product['name'] . ' | IKE PRODUCTS LOUNGE';

// Handle Add to Cart / Buy Now
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $qty = max(1, (int)($_POST['quantity'] ?? 1));
    $redirect_to = trim($_POST['redirect_to'] ?? '');

    if ($product['stock_quantity'] <= 0) {
        set_flash('error', 'This limited edition statue is currently sold out.');
    } elseif ($qty > $product['stock_quantity']) {
        set_flash('warning', 'Only ' . $product['stock_quantity'] . ' unit(s) remaining. Maximum added to bag.');
        add_to_cart($product['id'], $product['name'], $product['price'], $product['image'], $product['stock_quantity'], $product['stock_quantity']);
        redirect(BASE_URL . '/cart.php');
    } else {
        add_to_cart($product['id'], $product['name'], $product['price'], $product['image'], $qty, $product['stock_quantity']);
        set_flash('success', '✓ Added "' . sanitize($product['name']) . '" to your collection bag.');
        if ($redirect_to === 'checkout') {
            redirect(BASE_URL . '/checkout.php');
        } else {
            redirect(BASE_URL . '/cart.php');
        }
    }
}

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    $author = trim($_POST['author_name'] ?? '');
    $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
    $comment = trim($_POST['comment'] ?? '');

    if (empty($author)) {
        $author = is_logged_in() ? $_SESSION['user_name'] : 'Collector';
    }

    if (!empty($comment)) {
        $uid = $_SESSION['user_id'] ?? null;
        $r_ins = $db->prepare("INSERT INTO reviews (product_id, user_id, author_name, rating, comment) VALUES (?, ?, ?, ?, ?)");
        $r_ins->execute([$product['id'], $uid, $author, $rating, $comment]);

        // Refresh stats
        $avg_stmt = $db->prepare("SELECT AVG(rating) as avg_r, COUNT(*) as cnt FROM reviews WHERE product_id = ?");
        $avg_stmt->execute([$product['id']]);
        $stats = $avg_stmt->fetch();
        if ($stats) {
            $upd = $db->prepare("UPDATE products SET rating = ?, reviews_count = ? WHERE id = ?");
            $upd->execute([round((float)$stats['avg_r'], 1), (int)$stats['cnt'], $product['id']]);
        }

        set_flash('success', '✨ Thank you! Your collector review has been published.');
    } else {
        set_flash('error', 'Please enter your review feedback.');
    }
    redirect(BASE_URL . '/product-details.php?id=' . $product['id'] . '#reviewsSection');
}

// Fetch Reviews for this piece
$rev_stmt = $db->prepare("SELECT * FROM reviews WHERE product_id = ? ORDER BY id DESC");
$rev_stmt->execute([$product['id']]);
$product_reviews = $rev_stmt->fetchAll();

$in_wishlist = is_in_wishlist($product['id']);

// Fetch related statues in same category
$rel_stmt = $db->prepare("
    SELECT p.*, c.name AS category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.category_id = ? AND p.id != ? 
    ORDER BY p.id ASC 
    LIMIT 4
");
$rel_stmt->execute([$product['category_id'], $product['id']]);
$related_products = $rel_stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="section" style="padding-top: 2rem;">
    <div class="container container-sm">

        <!-- Top Navigation Header (Replicating Reference Design) -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <a href="<?= BASE_URL ?>/products.php" class="btn btn-secondary btn-sm btn-pill" style="font-weight: 700;">
                &larr; Back to Gallery
            </a>
            <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.08em;">
                <?= sanitize($product['category_name']) ?>
            </div>
            <a href="<?= BASE_URL ?>/cart.php" class="cart-pill" style="font-size: 0.85rem;">
                🛒 Bag (<?= get_cart_count() ?>)
            </a>
        </div>

        <!-- Master Curved Detail Card (Replicating Left Phone Screen) -->
        <div class="detail-curved-container">
            
            <!-- Curved Wave Backdrop with Centerpiece Pedestal Statue -->
            <div class="curved-wave-header">
                <img src="<?= get_product_image($product['image']) ?>" alt="<?= sanitize($product['name']) ?>" class="statue-centerpiece">
            </div>

            <!-- Product Information Body -->
            <div class="detail-body">
                <!-- Scale Subtitle & Price Row -->
                <div class="detail-top-row">
                    <div>
                        <div style="font-size: 0.8rem; font-weight: 800; color: var(--text-subtle); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 0.4rem;">
                            <?= sanitize($product['scale'] ?? '1/4 Scale') ?> COLLECTIBLE &bull; <?= sanitize($product['brand'] ?? 'Prime 1 Studio') ?>
                        </div>
                        <h1 class="detail-title"><?= sanitize($product['name']) ?></h1>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem;">
                            <?= render_stars($product['rating'] ?? 5.0) ?>
                            <span style="font-size: 0.9rem; font-weight: 700; color: var(--dark);">
                                <?= number_format((float)($product['rating'] ?? 5.0), 1) ?>
                            </span>
                            <a href="#reviewsSection" style="font-size: 0.85rem; color: var(--text-muted); text-decoration: underline;">
                                (<?= (int)($product['reviews_count'] ?? count($product_reviews)) ?> collector reviews)
                            </a>
                        </div>
                    </div>

                    <div style="text-align: right;">
                        <?php if (!empty($product['original_price']) && (float)$product['original_price'] > (float)$product['price']): ?>
                            <div style="font-size: 1.1rem; color: #94a3b8; text-decoration: line-through; font-weight: 600;">
                                <?= format_price($product['original_price']) ?>
                            </div>
                        <?php endif; ?>
                        <div class="detail-price-big">
                            <?= format_price($product['price']) ?>
                        </div>
                        <?php if (!empty($product['original_price']) && (float)$product['original_price'] > (float)$product['price']): ?>
                            <span class="badge badge-warning" style="font-size: 0.75rem; font-weight: 800;">
                                SAVE <?= format_price((float)$product['original_price'] - (float)$product['price']) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Description -->
                <p class="detail-desc">
                    <?= nl2br(sanitize($product['description'])) ?>
                </p>

                <!-- Studio Technical Specifications -->
                <div class="specs-grid">
                    <div class="spec-item">
                        <span>Edition Scale</span>
                        <strong><?= sanitize($product['scale'] ?? '1/4 Masterline') ?></strong>
                    </div>
                    <div class="spec-item">
                        <span>Manufactured By</span>
                        <strong><?= sanitize($product['brand'] ?? 'Prime 1 Studio') ?></strong>
                    </div>
                    <div class="spec-item">
                        <span>Material Casting</span>
                        <strong>High-Density Polystone</strong>
                    </div>
                    <div class="spec-item">
                        <span>Stock Status</span>
                        <?php if ($product['stock_quantity'] <= 0): ?>
                            <strong style="color: var(--danger);">Sold Out</strong>
                        <?php elseif ($product['stock_quantity'] <= 5): ?>
                            <strong style="color: var(--warning);">Only <?= $product['stock_quantity'] ?> Remaining</strong>
                        <?php else: ?>
                            <strong style="color: var(--success);">In Stock (<?= $product['stock_quantity'] ?>)</strong>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Action Bar (BUY NOW Coral Pill + Circular Blue Cart Button + Wishlist) -->
                <?php if ($product['stock_quantity'] > 0): ?>
                    <form method="POST" action="<?= BASE_URL ?>/product-details.php?id=<?= $product['id'] ?>" style="margin: 0;">
                        <input type="hidden" name="action" value="add_to_cart">
                        <input type="hidden" name="quantity" value="1">

                        <div class="action-bar-row">
                            <!-- Large Coral-Orange BUY NOW Button (proceeds to checkout) -->
                            <button type="submit" name="redirect_to" value="checkout" class="btn btn-buy-now">
                                BUY NOW
                            </button>

                            <!-- Circular Cobalt Blue Shopping Cart Button (adds to cart) -->
                            <button type="submit" name="redirect_to" value="cart" class="btn-cart-circle" title="Add to Bag">
                                🛒
                            </button>

                            <!-- Wishlist Toggle -->
                            <a href="<?= BASE_URL ?>/wishlist.php?action=toggle&id=<?= $product['id'] ?>&redirect=<?= urlencode(BASE_URL . '/product-details.php?id=' . $product['id']) ?>" class="btn btn-secondary btn-pill" style="padding: 0.95rem 1.25rem; font-weight: 700; color: <?= $in_wishlist ? 'var(--accent)' : 'var(--text)' ?>;" title="Save to Wishlist">
                                <?= $in_wishlist ? '❤️ In Wishlist' : '🤍 Save to Wishlist' ?>
                            </a>
                        </div>
                    </form>
                <?php else: ?>
                    <div style="display: flex; gap: 1rem; align-items: center; justify-content: center; flex-wrap: wrap;">
                        <div style="padding: 1rem 1.5rem; background: #fef2f2; border-radius: var(--radius-pill); color: var(--danger); font-weight: 700;">
                            ⚠️ This limited edition statue has reached maximum allocation and is sold out.
                        </div>
                        <a href="<?= BASE_URL ?>/wishlist.php?action=toggle&id=<?= $product['id'] ?>&redirect=<?= urlencode(BASE_URL . '/product-details.php?id=' . $product['id']) ?>" class="btn btn-secondary btn-pill">
                            <?= $in_wishlist ? '❤️ Saved' : '🤍 Notify Me (Wishlist)' ?>
                        </a>
                    </div>
                <?php endif; ?>

                <!-- Collector Guarantees -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid var(--border); font-size: 0.85rem; color: var(--text-muted);">
                    <div>🛡️ <strong>Certificate of Authenticity</strong> included</div>
                    <div>📦 <strong>Custom Foam &amp; Wood Crate</strong> packaging</div>
                    <div>⚡ <strong>Full Transit Insurance</strong> provided</div>
                    <div>💳 <strong>Safe &amp; Encrypted</strong> payment processing</div>
                </div>
            </div>
        </div>

        <!-- 3. Collector Reviews & Verification Section -->
        <div id="reviewsSection" style="margin-top: 4rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
                <div>
                    <h2 style="font-size: 1.85rem; margin-bottom: 0.25rem;">Verified Collector Feedback</h2>
                    <p style="color: var(--text-muted);">Curated collector impressions and authentication testimonials for <?= sanitize($product['name']) ?>.</p>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem; background: #ffffff; padding: 0.75rem 1.25rem; border-radius: var(--radius-pill); border: 1px solid var(--border);">
                    <?= render_stars($product['rating'] ?? 5.0) ?>
                    <strong style="font-size: 1.1rem; color: var(--dark);"><?= number_format((float)($product['rating'] ?? 5.0), 1) ?> / 5.0</strong>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 2rem; align-items: start;">
                <!-- Left: List of Reviews -->
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php if (empty($product_reviews)): ?>
                        <div class="card" style="text-align: center; padding: 3rem 1.5rem; color: var(--text-muted);">
                            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">📜</div>
                            <h4>Be the first to review this Masterline piece</h4>
                            <p style="font-size: 0.9rem;">Share your impressions with the collector community using the form.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($product_reviews as $rev): ?>
                            <div class="card" style="padding: 1.5rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                                    <div>
                                        <strong style="color: var(--dark); font-size: 1rem;"><?= sanitize($rev['author_name']) ?></strong>
                                        <span class="badge badge-success" style="font-size: 0.65rem; margin-left: 0.5rem;">Verified Patron</span>
                                    </div>
                                    <span style="font-size: 0.8rem; color: var(--text-muted);"><?= date('M j, Y', strtotime($rev['created_at'])) ?></span>
                                </div>
                                <div style="margin-bottom: 0.65rem;">
                                    <?= render_stars($rev['rating']) ?>
                                </div>
                                <p style="margin: 0; color: var(--text); font-size: 0.95rem; line-height: 1.6;">
                                    <?= nl2br(sanitize($rev['comment'])) ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Right: Submit Review Form -->
                <div class="card">
                    <h3 class="card-title">Submit Collector Review</h3>
                    <form method="POST" action="<?= BASE_URL ?>/product-details.php?id=<?= $product['id'] ?>" data-validate>
                        <input type="hidden" name="action" value="submit_review">

                        <div class="form-group">
                            <label class="form-label" for="author_name">Your Name / Title *</label>
                            <input type="text" name="author_name" id="author_name" class="form-control" value="<?= is_logged_in() ? sanitize($_SESSION['user_name']) : '' ?>" placeholder="e.g. Master Collector John" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="rating">Studio Score Rating *</label>
                            <select name="rating" id="rating" class="form-control" style="font-weight: 700;">
                                <option value="5" selected>★★★★★ 5 Stars (Museum Grade Perfection)</option>
                                <option value="4">★★★★☆ 4 Stars (Exceptional Sculpting)</option>
                                <option value="3">★★★☆☆ 3 Stars (Satisfactory)</option>
                                <option value="2">★★☆☆☆ 2 Stars (Minor Imperfections)</option>
                                <option value="1">★☆☆☆☆ 1 Star (Requires Studio Attention)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="comment">Curatorial Feedback / Comments *</label>
                            <textarea name="comment" id="comment" rows="3" class="form-control" placeholder="Share your assessment on the paint application, packaging, and LED features..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-pill btn-block" style="margin-top: 1rem;">
                            Publish Collector Review
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Related Pieces Gallery -->
        <?php if (!empty($related_products)): ?>
            <div style="margin-top: 4rem;">
                <h2 style="font-size: 1.6rem; margin-bottom: 1.5rem;">More from <?= sanitize($product['category_name']) ?></h2>
                <div class="product-grid">
                    <?php foreach ($related_products as $rp): ?>
                        <div class="product-card">
                            <div class="product-img-wrap">
                                <a href="<?= BASE_URL ?>/product-details.php?id=<?= $rp['id'] ?>">
                                    <img src="<?= get_product_image($rp['image']) ?>" alt="<?= sanitize($rp['name']) ?>">
                                </a>
                            </div>
                            <h3 class="product-title">
                                <a href="<?= BASE_URL ?>/product-details.php?id=<?= $rp['id'] ?>">
                                    <?= sanitize($rp['name']) ?>
                                </a>
                            </h3>
                            <div class="product-scale-tag">1/4 Scale Collectible</div>
                            <div class="product-card-footer">
                                <div class="product-price"><?= format_price($rp['price']) ?></div>
                                <a href="<?= BASE_URL ?>/product-details.php?id=<?= $rp['id'] ?>" class="btn btn-buy">
                                    BUY
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
