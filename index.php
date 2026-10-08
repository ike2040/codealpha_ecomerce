<?php
/**
 * IKE PRODUCTS LOUNGE - Flagship Showcase (index.php)
 * High-end studio collectible statues, Spider-Man 2099 centerpiece & trending gallery
 */

$page_title = 'Exclusive Statues & Masterline Figures | IKE PRODUCTS LOUNGE';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/functions.php';

$db = getDB();

// Fetch categories
$cat_stmt = $db->query("SELECT * FROM categories ORDER BY id ASC");
$categories = $cat_stmt->fetchAll();

// Fetch featured products for trending showcase
$feat_stmt = $db->query("
    SELECT p.*, c.name AS category_name, c.slug AS category_slug 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.is_featured = 1 
    ORDER BY p.id ASC 
    LIMIT 8
");
$featured_products = $feat_stmt->fetchAll();

// Fetch Flagship centerpiece (Spider-Man 2099)
$flagship_stmt = $db->query("
    SELECT p.*, c.name AS category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.name LIKE '%Spider-Man 2099%' OR p.id = 1 
    ORDER BY (p.name LIKE '%Spider-Man 2099%') DESC 
    LIMIT 1
");
$flagship = $flagship_stmt->fetch();


// Handle quick add-to-cart or buy now
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity   = max(1, (int)($_POST['quantity'] ?? 1));
    $redirect_to = trim($_POST['redirect_to'] ?? '');

    $p_stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $p_stmt->execute([$product_id]);
    $product = $p_stmt->fetch();

    if ($product) {
        if ($product['stock_quantity'] <= 0) {
            set_flash('error', 'Sorry, ' . sanitize($product['name']) . ' is currently out of stock.');
        } else {
            add_to_cart($product['id'], $product['name'], $product['price'], $product['image'], $quantity, $product['stock_quantity']);
            set_flash('success', '✓ Added "' . sanitize($product['name']) . '" to your collection bag.');
        }
    }

    if ($redirect_to === 'checkout') {
        redirect(BASE_URL . '/checkout.php');
    }
    redirect(BASE_URL . '/index.php');
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- 1. Flagship Hero Showcase (Spider-Man 2099) -->
<section class="hero-showcase">
    <div class="container">
        <div class="hero-grid">
            <div>
                <span class="hero-tag">✨ Masterline Studio Limited Edition</span>
                <h1 class="hero-title"><?= sanitize($flagship['name'] ?? 'Spider-Man 2099') ?></h1>
                <p class="hero-subtitle">
                    Developed and manufactured in collaboration with Prime 1 Studio. Standing 26 inches tall, this definitive 1/4 scale museum masterpiece captures Miguel O'Hara perched upon an illuminated Nueva York spire.
                </p>

                <div class="hero-price-row">
                    <span class="hero-price"><?= format_price($flagship['price'] ?? 699.00) ?></span>
                    <span class="badge badge-scale" style="background: rgba(255,255,255,0.15); color: #ffffff;">1/4 Scale Polystone</span>
                </div>

                <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                    <!-- Buy Now Button (Direct to checkout) -->
                    <form method="POST" action="<?= BASE_URL ?>/index.php" style="margin: 0; display: flex; gap: 1rem; align-items: center; flex: 1; min-width: 260px;">
                        <input type="hidden" name="action" value="add_to_cart">
                        <input type="hidden" name="product_id" value="<?= $flagship['id'] ?? 1 ?>">
                        <input type="hidden" name="quantity" value="1">
                        <input type="hidden" name="redirect_to" value="checkout">
                        <button type="submit" class="btn btn-buy-now">
                            BUY NOW
                        </button>

                        <!-- Circular Floating Cart Button -->
                        <button type="submit" formaction="<?= BASE_URL ?>/index.php" name="redirect_to" value="" class="btn-cart-circle" title="Add to Bag">
                            🛒
                        </button>
                    </form>

                    <a href="<?= BASE_URL ?>/product-details.php?id=<?= $flagship['id'] ?? 1 ?>" class="btn btn-secondary btn-pill" style="padding: 0.95rem 1.75rem;">
                        View Full Specs
                    </a>
                </div>
            </div>

            <!-- Statue Display Card -->
            <div class="hero-figure-container">
                <a href="<?= BASE_URL ?>/product-details.php?id=<?= $flagship['id'] ?? 1 ?>" style="display: block; width: 100%; text-align: center;">
                    <img src="<?= get_product_image($flagship['image'] ?? 'spiderman-2099.svg') ?>" alt="<?= sanitize($flagship['name'] ?? 'Spider-Man 2099') ?>">
                </a>
            </div>
        </div>
    </div>
</section>

<!-- 2. Collector Trust & Value Pillars -->
<section class="pillars-bar">
    <div class="container">
        <div class="pillars-grid">
            <div class="pillar-item">
                <div class="pillar-icon">🛡️</div>
                <div class="pillar-text">
                    <h4>100% Officially Licensed</h4>
                    <p>Direct authentic studio collaboration</p>
                </div>
            </div>
            <div class="pillar-item">
                <div class="pillar-icon">💎</div>
                <div class="pillar-text">
                    <h4>Museum-Grade Casting</h4>
                    <p>High-density polystone &amp; diecast metal</p>
                </div>
            </div>
            <div class="pillar-item">
                <div class="pillar-icon">✈️</div>
                <div class="pillar-text">
                    <h4>Insured Global Delivery</h4>
                    <p>Heavy-duty custom wooden crate packing</p>
                </div>
            </div>
            <div class="pillar-item">
                <div class="pillar-icon">📜</div>
                <div class="pillar-text">
                    <h4>Signed Certificates</h4>
                    <p>Numbered authenticity plaque with every piece</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3. Popular & Trending Gallery (Matching Right Phone Screen) -->
<section class="section" style="background: #ffffff;">
    <div class="container">
        <div class="catalog-nav-header">
            <!-- Tabs -->
            <div class="catalog-tabs">
                <div class="catalog-tab active">Popular</div>
                <a href="<?= BASE_URL ?>/products.php?sort=newest" class="catalog-tab">Trending</a>
                <a href="<?= BASE_URL ?>/products.php" class="catalog-tab">Characters</a>
            </div>

            <!-- Filter Pill Chips -->
            <div class="filter-pills">
                <a href="<?= BASE_URL ?>/products.php" class="pill-chip active">All Universes</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="<?= BASE_URL ?>/products.php?category=<?= urlencode($cat['slug']) ?>" class="pill-chip">
                        <?= sanitize($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Product Cards Grid -->
        <div class="product-grid">
            <?php foreach ($featured_products as $p): 
                $is_saved = is_in_wishlist($p['id']);
            ?>
                <div class="product-card">
                    <div class="product-card-badges">
                        <?php if (!empty($p['original_price']) && (float)$p['original_price'] > (float)$p['price']): ?>
                            <span class="badge badge-warning" style="font-weight: 800;">SAVE <?= format_price((float)$p['original_price'] - (float)$p['price']) ?></span>
                        <?php endif; ?>
                        <?php if ($p['stock_quantity'] <= 0): ?>
                            <span class="badge badge-danger">Sold Out</span>
                        <?php elseif ($p['stock_quantity'] <= 5): ?>
                            <span class="badge badge-warning">Only <?= $p['stock_quantity'] ?> left</span>
                        <?php endif; ?>
                    </div>

                    <div class="product-img-wrap">
                        <a href="<?= BASE_URL ?>/product-details.php?id=<?= $p['id'] ?>">
                            <img src="<?= get_product_image($p['image']) ?>" alt="<?= sanitize($p['name']) ?>" loading="lazy">
                        </a>
                    </div>

                    <h3 class="product-title">
                        <a href="<?= BASE_URL ?>/product-details.php?id=<?= $p['id'] ?>">
                            <?= sanitize($p['name']) ?>
                        </a>
                    </h3>
                    <div class="product-scale-tag">
                        <?= sanitize($p['category_name']) ?> &bull; <?= sanitize($p['scale'] ?? '1/4 Scale') ?>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.75rem;">
                        <?= render_stars($p['rating'] ?? 5.0) ?>
                        <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">(<?= (int)($p['reviews_count'] ?? 1) ?>)</span>
                    </div>

                    <div class="product-card-footer">
                        <div class="product-price">
                            <?= format_price($p['price']) ?>
                        </div>

                        <div style="display: flex; gap: 0.4rem; align-items: center;">
                            <a href="<?= BASE_URL ?>/wishlist.php?action=toggle&id=<?= $p['id'] ?>&redirect=<?= urlencode(BASE_URL . '/index.php') ?>" class="btn-wishlist-circle" title="Toggle Wishlist" style="color: <?= $is_saved ? 'var(--accent)' : '#94a3b8' ?>; text-decoration: none; font-size: 1.1rem; padding: 0.2rem 0.4rem;">
                                <?= $is_saved ? '❤️' : '🤍' ?>
                            </a>

                            <form method="POST" action="<?= BASE_URL ?>/index.php" style="margin: 0;">
                                <input type="hidden" name="action" value="add_to_cart">
                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                <input type="hidden" name="quantity" value="1">
                                <?php if ($p['stock_quantity'] > 0): ?>
                                    <button type="submit" class="btn btn-buy">
                                        BUY
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-buy disabled" disabled style="background: #94a3b8; box-shadow: none;">
                                        SOLD
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 3.5rem;">
            <a href="<?= BASE_URL ?>/products.php" class="btn btn-primary btn-lg" style="padding: 1rem 3rem;">
                Explore Entire Collection &rarr;
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
