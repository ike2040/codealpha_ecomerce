<?php
/**
 * IKE PRODUCTS LOUNGE - Studio Catalog Management (admin/products.php)
 * Manage statue editions, inventory levels, pricing, and artwork
 */

$page_title = 'Statue Catalog Management';
require_once __DIR__ . '/includes/admin_header.php';

$search   = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');

$where = ["1=1"];
$params = [];

if ($search !== '') {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category !== '') {
    $where[] = "c.slug = ?";
    $params[] = $category;
}

$cat_stmt = $db->query("SELECT * FROM categories ORDER BY name ASC");
$all_categories = $cat_stmt->fetchAll();

$sql = "
    SELECT p.*, c.name AS category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE " . implode(' AND ', $where) . " 
    ORDER BY p.id ASC
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<!-- Top Bar -->
<div class="admin-topbar">
    <div>
        <h1 style="font-size: 1.85rem; margin-bottom: 0.25rem;">Masterline Catalog Management</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Review museum-scale statues, edit technical specifications, and update allocations.</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/product-add.php" class="btn btn-primary btn-pill">
        ➕ Register New Statue
    </a>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 1.5rem; padding: 1rem 1.5rem;">
    <form method="GET" action="<?= BASE_URL ?>/admin/products.php" style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
        <div style="flex: 1; min-width: 220px;">
            <input type="text" name="search" class="form-control" placeholder="Search by character or studio..." value="<?= sanitize($search) ?>" style="border-radius: var(--radius-pill);">
        </div>
        <div style="min-width: 180px;">
            <select name="category" class="form-control" style="border-radius: var(--radius-pill);">
                <option value="">All Universes</option>
                <?php foreach ($all_categories as $cat): ?>
                    <option value="<?= sanitize($cat['slug']) ?>" <?= $category === $cat['slug'] ? 'selected' : '' ?>>
                        <?= sanitize($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary btn-sm btn-pill">Filter</button>
        <?php if ($search !== '' || $category !== ''): ?>
            <a href="<?= BASE_URL ?>/admin/products.php" class="btn btn-sm btn-pill" style="color: var(--text-muted);">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Products Table -->
<div class="card" style="padding: 0; overflow: hidden;">
    <?php if (empty($products)): ?>
        <div style="text-align: center; padding: 4rem 1.5rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📦</div>
            <h3>No Statues Found</h3>
            <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Try adjusting your search criteria or register a new masterline statue.</p>
            <a href="<?= BASE_URL ?>/admin/product-add.php" class="btn btn-primary btn-pill btn-sm">➕ Add Statue</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th style="width: 70px;">Piece</th>
                        <th>Statue Title</th>
                        <th>Universe</th>
                        <th>Price</th>
                        <th>Stock Allocation</th>
                        <th>Featured</th>
                        <th style="text-align: right; width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td>#<?= $p['id'] ?></td>
                            <td>
                                <img src="<?= get_product_image($p['image']) ?>" alt="" class="table-thumb" style="width: 48px; height: 48px;">
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/product-details.php?id=<?= $p['id'] ?>" target="_blank" style="font-weight: 800; color: var(--dark);">
                                    <?= sanitize($p['name']) ?> ↗
                                </a>
                                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">
                                    <?= sanitize($p['brand'] ?? 'Prime 1 Studio') ?> &bull; <?= sanitize($p['scale'] ?? '1/4 Scale') ?> &bull; <span style="color: #f59e0b;">★</span> <?= number_format((float)($p['rating'] ?? 5.0), 1) ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-secondary"><?= sanitize($p['category_name']) ?></span>
                            </td>
                            <td style="font-weight: 900;"><?= format_price($p['price']) ?></td>
                            <td>
                                <?php if ($p['stock_quantity'] <= 0): ?>
                                    <span class="badge badge-danger">Sold Out (0)</span>
                                <?php elseif ($p['stock_quantity'] <= 5): ?>
                                    <span class="badge badge-warning">Low (<?= $p['stock_quantity'] ?>)</span>
                                <?php else: ?>
                                    <span class="badge badge-success"><?= $p['stock_quantity'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $p['is_featured'] ? '<span class="badge badge-primary">Yes</span>' : '<span style="color: var(--text-subtle); font-size: 0.85rem;">No</span>' ?>
                            </td>
                            <td style="text-align: right;">
                                <a href="<?= BASE_URL ?>/admin/product-edit.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm btn-pill" title="Edit Piece">
                                    ✏️ Edit
                                </a>
                                <a href="<?= BASE_URL ?>/admin/product-delete.php?id=<?= $p['id'] ?>" class="btn btn-secondary btn-sm btn-pill" data-confirm="Permanently remove '<?= sanitize($p['name']) ?>' from the studio archive?" style="color: var(--danger) !important; border-color: #fca5a5;">
                                    🗑️
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
