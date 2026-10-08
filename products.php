<?php
/**
 * IKE PRODUCTS LOUNGE - Master Catalog (products.php)
 * Filterable gallery of high-end collector statues with search, category tabs & pill chips
 */

$page_title = 'Studio Collectibles & Statues | IKE PRODUCTS LOUNGE';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/functions.php';

$db = getDB();

// Handle quick add-to-cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity   = max(1, (int)($_POST['quantity'] ?? 1));

    $p_stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $p_stmt->execute([$product_id]);
    $product = $p_stmt->fetch();

    if ($product) {
        if ($product['stock_quantity'] <= 0) {
            set_flash('error', 'Sorry, ' . sanitize($product['name']) . ' is currently sold out.');
        } else {
            add_to_cart($product['id'], $product['name'], $product['price'], $product['image'], $quantity, $product['stock_quantity']);
            set_flash('success', '✓ Added "' . sanitize($product['name']) . '" to your collection bag.');
        }
    }

    $redirect_url = BASE_URL . '/products.php';
    if (!empty($_SERVER['QUERY_STRING'])) {
        $redirect_url .= '?' . $_SERVER['QUERY_STRING'];
    }
    redirect($redirect_url);
}

// Fetch all categories
$cat_stmt = $db->query("SELECT * FROM categories ORDER BY id ASC");
$all_categories = $cat_stmt->fetchAll();

// Filters & Search
$search   = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$sort     = trim($_GET['sort'] ?? 'newest');
$in_stock = isset($_GET['in_stock']) ? (int)$_GET['in_stock'] : 0;

$where = ["1=1"];
$params = [];

if ($search !== '') {
    $where[] = "(p.name LIKE ? OR p.description LIKE ? OR p.brand LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category !== '') {
    $where[] = "c.slug = ?";
    $params[] = $category;
}

if ($in_stock === 1) {
    $where[] = "p.stock_quantity > 0";
}

$order_by = "p.id ASC";
switch ($sort) {
    case 'price_low':
        $order_by = "p.price ASC";
        break;
    case 'price_high':
        $order_by = "p.price DESC";
        break;
    case 'rating_high':
        $order_by = "p.rating DESC, p.reviews_count DESC";
        break;
    case 'name_asc':
        $order_by = "p.name ASC";
        break;
    case 'newest':
    default:
        $order_by = "p.id DESC";
        break;
}

$sql = "
    SELECT p.*, c.name AS category_name, c.slug AS category_slug 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE " . implode(' AND ', $where) . " 
    ORDER BY $order_by
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="section">
    <div class="container">
        <!-- Catalog Navigation Header (Replicating Reference Design) -->
        <div class="catalog-nav-header">
            <!-- Tabs -->
            <div class="catalog-tabs">
                <a href="<?= BASE_URL ?>/products.php" class="catalog-tab <?= empty($sort) || $sort === 'newest' ? 'active' : '' ?>">Popular</a>
                <a href="<?= BASE_URL ?>/products.php?sort=price_high" class="catalog-tab <?= $sort === 'price_high' ? 'active' : '' ?>">Trending</a>
                <a href="<?= BASE_URL ?>/products.php?sort=name_asc" class="catalog-tab <?= $sort === 'name_asc' ? 'active' : '' ?>">Characters</a>
            </div>

            <!-- Horizontal Filter Pills -->
            <div class="filter-pills" style="margin-bottom: 1.5rem;">
                <a href="<?= BASE_URL ?>/products.php" class="pill-chip <?= empty($category) ? 'active' : '' ?>">All Universes</a>
                <?php foreach ($all_categories as $cat): ?>
                    <a href="<?= BASE_URL ?>/products.php?category=<?= urlencode($cat['slug']) ?>" class="pill-chip <?= $category === $cat['slug'] ? 'active' : '' ?>">
                        <?= sanitize($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Search and Sort Filter Bar -->
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; background: #ffffff; padding: 1rem 1.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
                <form method="GET" action="<?= BASE_URL ?>/products.php" style="display: flex; gap: 0.75rem; flex: 1; min-width: 260px; align-items: center;">
                    <?php if ($category): ?>
                        <input type="hidden" name="category" value="<?= sanitize($category) ?>">
                    <?php endif; ?>
                    <input type="text" name="search" class="form-control" placeholder="🔍 Search statues, superheroes, or studios..." value="<?= sanitize($search) ?>" style="border-radius: var(--radius-pill); padding-left: 1.25rem;">
                    <button type="submit" class="btn btn-primary btn-sm btn-pill">Search</button>
                    <?php if ($search !== '' || $category !== ''): ?>
                        <a href="<?= BASE_URL ?>/products.php" class="btn btn-secondary btn-sm btn-pill">Reset</a>
                    <?php endif; ?>
                </form>

                <form method="GET" action="<?= BASE_URL ?>/products.php" style="display: flex; align-items: center; gap: 0.75rem; margin: 0;">
                    <?php if ($category): ?>
                        <input type="hidden" name="category" value="<?= sanitize($category) ?>">
                    <?php endif; ?>
                    <?php if ($search): ?>
                        <input type="hidden" name="search" value="<?= sanitize($search) ?>">
                    <?php endif; ?>
                    <select name="sort" class="form-control" style="border-radius: var(--radius-pill); font-size: 0.85rem; font-weight: 600;" onchange="this.form.submit()">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Sort: Newest Editions</option>
                        <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="rating_high" <?= $sort === 'rating_high' ? 'selected' : '' ?>>Rating: Highest First ★</option>
                        <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name: A to Z</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Result Stats -->
        <div style="margin-bottom: 1.5rem; color: var(--text-muted); font-size: 0.9rem; font-weight: 600;">
            Showing <strong><?= count($products) ?></strong> Masterline Collectibles
            <?php if ($category): ?>
                in <strong><?= sanitize(ucwords(str_replace('-', ' ', $category))) ?></strong>
            <?php endif; ?>
        </div>

        <!-- Product Grid (Replicating Right Phone Screen) -->
        <?php if (empty($products)): ?>
            <div class="card" style="text-align: center; padding: 4rem 1.5rem;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                <h3>No Matching Collectibles Found</h3>
                <p style="color: var(--text-muted); margin-bottom: 1.5rem;">We could not find any statues matching your query. Browse all licensed pieces below.</p>
                <a href="<?= BASE_URL ?>/products.php" class="btn btn-primary btn-pill">Browse All Statues</a>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($products as $p): 
                    $is_saved = is_in_wishlist($p['id']);
                ?>
                    <div class="product-card">
                        <div class="product-card-badges">
                            <?php if (!empty($p['original_price']) && (float)$p['original_price'] > (float)$p['price']): ?>
                                <span class="badge badge-warning" style="font-weight: 800;">SAVE <?= format_price((float)$p['original_price'] - (float)$p['price']) ?></span>
                            <?php endif; ?>
                            <?php if ($p['is_featured']): ?>
                                <span class="badge badge-primary">Featured</span>
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
                                <a href="<?= BASE_URL ?>/wishlist.php?action=toggle&id=<?= $p['id'] ?>&redirect=<?= urlencode(BASE_URL . '/products.php' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '')) ?>" class="btn-wishlist-circle" title="Toggle Wishlist" style="color: <?= $is_saved ? 'var(--accent)' : '#94a3b8' ?>; text-decoration: none; font-size: 1.1rem; padding: 0.2rem 0.4rem;">
                                    <?= $is_saved ? '❤️' : '🤍' ?>
                                </a>

                                <form method="POST" action="<?= BASE_URL ?>/products.php?<?= http_build_query($_GET) ?>" style="margin: 0;">
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
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
