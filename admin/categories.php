<?php
/**
 * IKE PRODUCTS LOUNGE - Universe Galleries Management (admin/categories.php)
 * Register, browse, and manage collectible universes and lines
 */

$page_title = 'Universe Galleries';
require_once __DIR__ . '/includes/admin_header.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_category') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));

    if (empty($name)) {
        $errors['name'] = 'Universe title is required.';
    } elseif (empty($slug)) {
        $errors['slug'] = 'Invalid universe name.';
    } else {
        $chk = $db->prepare("SELECT id FROM categories WHERE slug = ?");
        $chk->execute([$slug]);
        if ($chk->fetch()) {
            $errors['slug'] = 'A universe with this title is already registered.';
        }
    }

    if (empty($errors)) {
        $ins = $db->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
        $ins->execute([$name, $slug, $description]);
        set_flash('success', '✓ Universe "' . sanitize($name) . '" created successfully.');
        redirect(BASE_URL . '/admin/categories.php');
    }
}

// Handle Delete Category
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $cat_id = (int)($_GET['id'] ?? 0);
    if ($cat_id > 0) {
        $p_chk = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $p_chk->execute([$cat_id]);
        $prod_count = (int)$p_chk->fetchColumn();

        if ($prod_count > 0) {
            set_flash('error', 'Cannot remove this universe because it currently houses ' . $prod_count . ' active masterline statues.');
        } else {
            $del = $db->prepare("DELETE FROM categories WHERE id = ?");
            $del->execute([$cat_id]);
            set_flash('success', 'Universe removed from catalog.');
        }
    }
    redirect(BASE_URL . '/admin/categories.php');
}

$cat_list = $db->query("
    SELECT c.*, COUNT(p.id) AS product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id 
    GROUP BY c.id 
    ORDER BY c.name ASC
")->fetchAll();
?>

<!-- Top Bar -->
<div class="admin-topbar">
    <div>
        <h1 style="font-size: 1.85rem; margin-bottom: 0.25rem;">Universe Galleries Management</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Organize masterline statues into pop culture universes and franchise departments.</p>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div style="background: var(--danger-bg); border: 1px solid #fecaca; color: var(--danger); padding: 1rem 1.25rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;">
        <strong>⚠️ Attention:</strong>
        <ul style="margin-left: 1.5rem; margin-top: 0.35rem; font-size: 0.85rem;">
            <?php foreach ($errors as $err): ?>
                <li><?= sanitize($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1.8fr; gap: 2rem; align-items: start;">
    <!-- Left: Add Form -->
    <div class="card">
        <h3 class="card-title">Register Universe Gallery</h3>
        <form method="POST" action="<?= BASE_URL ?>/admin/categories.php" data-validate>
            <input type="hidden" name="action" value="add_category">

            <div class="form-group">
                <label class="form-label" for="name">Universe Title *</label>
                <input type="text" name="name" id="name" class="form-control" placeholder="e.g. The Lord of the Rings" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Curatorial Description</label>
                <textarea name="description" id="description" rows="3" class="form-control" placeholder="Brief summary of collectibles housed in this franchise"></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-pill btn-block">
                ➕ Add Universe
            </button>
        </form>
    </div>

    <!-- Right: Table -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="padding: 1.25rem 1.75rem; border-bottom: 1px solid var(--border);">
            <h3 style="font-size: 1.2rem; margin: 0;">Registered Universes (<?= count($cat_list) ?>)</h3>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Universe Name</th>
                        <th>Slug</th>
                        <th>Statues</th>
                        <th style="text-align: right; width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cat_list as $c): ?>
                        <tr>
                            <td>#<?= $c['id'] ?></td>
                            <td>
                                <strong style="color: var(--dark); font-size: 1rem;"><?= sanitize($c['name']) ?></strong>
                                <?php if ($c['description']): ?>
                                    <div style="font-size: 0.775rem; color: var(--text-muted);"><?= truncate(sanitize($c['description']), 55) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><code><?= sanitize($c['slug']) ?></code></td>
                            <td>
                                <span class="badge badge-secondary"><?= (int)$c['product_count'] ?> statue(s)</span>
                            </td>
                            <td style="text-align: right;">
                                <a href="<?= BASE_URL ?>/admin/categories.php?action=delete&id=<?= $c['id'] ?>" class="btn btn-secondary btn-sm btn-pill" data-confirm="Remove universe '<?= sanitize($c['name']) ?>'? Universes containing active statues cannot be deleted." style="color: var(--danger) !important; border-color: #fca5a5;">
                                    ✕
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
