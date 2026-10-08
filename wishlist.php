<?php
/**
 * IKE PRODUCTS LOUNGE - Collector Wishlist & Saved Pieces (wishlist.php)
 * Manage personal wishlist, review saved statues, and quickly transfer to collection bag
 */

$page_title = 'Saved Masterline Pieces | IKE PRODUCTS LOUNGE';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/functions.php';

$db = getDB();

// Handle toggle/remove actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
    $action     = $_POST['action'] ?? ($_GET['action'] ?? '');
    $product_id = (int)($_POST['product_id'] ?? ($_GET['id'] ?? 0));
    $redirect   = $_POST['redirect'] ?? ($_GET['redirect'] ?? (BASE_URL . '/wishlist.php'));

    if ($action === 'toggle' && $product_id > 0) {
        $added = toggle_wishlist($product_id);
        $s_stmt = $db->prepare("SELECT name FROM products WHERE id = ?");
        $s_stmt->execute([$product_id]);
        $pname = $s_stmt->fetchColumn() ?: 'Piece';

        if ($added) {
            set_flash('success', '❤️ Saved "' . sanitize($pname) . '" to your personal vault wishlist.');
        } else {
            set_flash('info', 'Removed "' . sanitize($pname) . '" from your saved pieces.');
        }
        redirect($redirect);
    }

    if ($action === 'remove' && $product_id > 0) {
        if (isset($_SESSION['wishlist'][$product_id])) {
            unset($_SESSION['wishlist'][$product_id]);
            set_flash('info', 'Piece removed from your saved pieces.');
        }
        redirect(BASE_URL . '/wishlist.php');
    }

    if ($action === 'move_to_cart' && $product_id > 0) {
        $p_stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $p_stmt->execute([$product_id]);
        $prod = $p_stmt->fetch();

        if ($prod) {
            if ($prod['stock_quantity'] <= 0) {
                set_flash('error', 'Sorry, "' . sanitize($prod['name']) . '" is currently sold out.');
            } else {
                add_to_cart($prod['id'], $prod['name'], $prod['price'], $prod['image'], 1, $prod['stock_quantity']);
                unset($_SESSION['wishlist'][$product_id]);
                set_flash('success', '✓ Moved "' . sanitize($prod['name']) . '" to your collection bag.');
            }
        }
        redirect(BASE_URL . '/cart.php');
    }
}

$wishlist_ids = array_keys(get_wishlist_items());
$wishlist_products = [];

if (!empty($wishlist_ids)) {
    $in_clause = implode(',', array_fill(0, count($wishlist_ids), '?'));
    $w_stmt = $db->prepare("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        WHERE p.id IN ($in_clause)
        ORDER BY p.id ASC
    ");
    $w_stmt->execute($wishlist_ids);
    $wishlist_products = $w_stmt->fetchAll();
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="section">
    <div class="container">
        <!-- Header -->
        <div style="margin-bottom: 2.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <span class="badge badge-scale" style="background: rgba(255, 84, 54, 0.1); color: var(--accent); margin-bottom: 0.5rem; display: inline-block;">
                    ❤️ Collector Wishlist Vault
                </span>
                <h1 style="font-size: 2.25rem; margin-bottom: 0.25rem;">Saved Masterline Pieces</h1>
                <p style="color: var(--text-muted);">Keep track of statues you intend to acquire for your personal collection gallery.</p>
            </div>
            <div>
                <a href="<?= BASE_URL ?>/products.php" class="btn btn-secondary btn-pill btn-sm">
                    &larr; Explore Full Catalog
                </a>
            </div>
        </div>

        <?php if (empty($wishlist_products)): ?>
            <div class="card" style="text-align: center; padding: 4.5rem 2rem;">
                <div style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.8;">❤️</div>
                <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Your Wishlist Vault is Empty</h3>
                <p style="color: var(--text-muted); margin-bottom: 2rem; max-width: 460px; margin-left: auto; margin-right: auto;">
                    You haven't bookmarked any masterline statues yet. Browse our licensed galleries and tap the heart icon on any piece to save it here.
                </p>
                <a href="<?= BASE_URL ?>/products.php" class="btn btn-primary btn-pill btn-lg">
                    Discover Masterline Statues &rarr;
                </a>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($wishlist_products as $p): ?>
                    <div class="product-card">
                        <div class="product-card-badges">
                            <?php if ($p['stock_quantity'] <= 0): ?>
                                <span class="badge badge-danger">Sold Out</span>
                            <?php elseif ($p['stock_quantity'] <= 5): ?>
                                <span class="badge badge-warning">Only <?= $p['stock_quantity'] ?> left</span>
                            <?php else: ?>
                                <span class="badge badge-success">In Stock</span>
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

                        <div class="product-card-footer" style="gap: 0.5rem;">
                            <div class="product-price">
                                <?= format_price($p['price']) ?>
                            </div>

                            <div style="display: flex; gap: 0.4rem; align-items: center;">
                                <?php if ($p['stock_quantity'] > 0): ?>
                                    <form method="POST" action="<?= BASE_URL ?>/wishlist.php" style="margin: 0;">
                                        <input type="hidden" name="action" value="move_to_cart">
                                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn btn-buy" title="Transfer to Bag">
                                            BAG
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <form method="POST" action="<?= BASE_URL ?>/wishlist.php" style="margin: 0;">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="btn btn-secondary btn-sm" title="Remove from Saved" style="padding: 0.5rem 0.65rem; color: var(--danger) !important; border-color: #fca5a5;">
                                        ✕
                                    </button>
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
