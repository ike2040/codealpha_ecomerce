<?php
/**
 * IKE PRODUCTS LOUNGE - Edit Statue & Allocation (admin/product-edit.php)
 * Updates statue details, technical specifications, allocation counts, or replaces artwork
 */

$page_title = 'Edit Statue Details';
require_once __DIR__ . '/includes/admin_header.php';

$product_id = (int)($_GET['id'] ?? 0);

if ($product_id <= 0) {
    set_flash('error', 'Invalid statue reference.');
    redirect(BASE_URL . '/admin/products.php');
}

// Fetch existing product
$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('error', 'Statue record not found.');
    redirect(BASE_URL . '/admin/products.php');
}

$cat_stmt = $db->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $cat_stmt->fetchAll();

$errors = [];
$name           = $product['name'];
$category_id    = $product['category_id'];
$price          = $product['price'];
$original_price = $product['original_price'] ?? '';
$brand          = $product['brand'] ?? 'Prime 1 Studio';
$scale          = $product['scale'] ?? '1/4 Scale';
$stock_quantity = $product['stock_quantity'];
$description    = $product['description'];
$is_featured    = $product['is_featured'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name           = trim($_POST['name'] ?? '');
    $category_id    = (int)($_POST['category_id'] ?? 0);
    $price          = (float)($_POST['price'] ?? 0);
    $original_price = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
    $brand          = trim($_POST['brand'] ?? 'Prime 1 Studio');
    $scale          = trim($_POST['scale'] ?? '1/4 Scale');
    $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
    $description    = trim($_POST['description'] ?? '');
    $is_featured    = isset($_POST['is_featured']) ? 1 : 0;

    // Validation
    if (empty($name)) {
        $errors['name'] = 'Statue title is required.';
    }
    if ($category_id <= 0) {
        $errors['category_id'] = 'Please select an affiliated universe.';
    }
    if ($price <= 0) {
        $errors['price'] = 'Please enter a valid price greater than $0.00.';
    }
    if ($stock_quantity < 0) {
        $errors['stock_quantity'] = 'Stock allocation cannot be negative.';
    }

    $image_filename = $product['image'];

    // Handle Image Replacement
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp  = $_FILES['image']['tmp_name'];
            $file_name = $_FILES['image']['name'];
            $file_size = $_FILES['image']['size'];
            $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
            $max_size = 10 * 1024 * 1024; // 10MB limit

            if (!in_array($file_ext, $allowed_exts)) {
                $errors['image'] = 'Invalid format. Allowed: SVG, PNG, JPG, WEBP.';
            } elseif ($file_size > $max_size) {
                $errors['image'] = 'Artwork file exceeds 10MB size limit.';
            } else {
                $upload_dir = __DIR__ . '/../uploads/products/';
                $assets_dir = __DIR__ . '/../assets/images/';

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                if (!is_dir($assets_dir)) {
                    mkdir($assets_dir, 0777, true);
                }

                $new_name = 'statue_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
                $target_file = $upload_dir . $new_name;

                if (move_uploaded_file($file_tmp, $target_file)) {
                    // Dual-write to assets/images/
                    @copy($target_file, $assets_dir . $new_name);

                    // Delete previous custom image if exists
                    $builtin_images = [
                        'default-product.svg', 'default-product.jpg',
                        'spiderman-2099.svg', 'thor-statue.svg', 'ironman-mark4.svg',
                        'hulkbuster.svg', 'cyborg-superman.svg', 'batman-gargoyle.svg',
                        'darth-vader.svg', 'boba-fett.svg', 'goku-ultra.svg', 'cyberpunk-bike.svg'
                    ];
                    if ($image_filename && !in_array($image_filename, $builtin_images)) {
                        $old_upload = $upload_dir . $image_filename;
                        if (file_exists($old_upload)) {
                            @unlink($old_upload);
                        }
                        $old_asset = $assets_dir . $image_filename;
                        if (file_exists($old_asset)) {
                            @unlink($old_asset);
                        }
                    }

                    $image_filename = $new_name;
                } else {
                    $errors['image'] = 'Failed to save uploaded artwork to server.';
                }
            }
        } else {
            $upload_errors = [
                UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
                UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form.',
                UPLOAD_ERR_PARTIAL    => 'The artwork file was only partially uploaded. Please try again.',
                UPLOAD_ERR_NO_TMP_DIR => 'Server missing temporary upload folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write artwork file to disk on server.',
                UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.'
            ];
            $errors['image'] = $upload_errors[$_FILES['image']['error']] ?? 'An unknown upload error occurred.';
        }
    }

    if (empty($errors)) {
        $upd = $db->prepare("
            UPDATE products 
            SET name = ?, category_id = ?, price = ?, original_price = ?, brand = ?, scale = ?, stock_quantity = ?, description = ?, image = ?, is_featured = ? 
            WHERE id = ?
        ");
        $upd->execute([
            $name,
            $category_id,
            $price,
            $original_price,
            $brand,
            $scale,
            $stock_quantity,
            $description,
            $image_filename,
            $is_featured,
            $product_id
        ]);

        set_flash('success', '✓ Statue "' . sanitize($name) . '" updated successfully!');
        redirect(BASE_URL . '/admin/products.php');
    }
}
?>

<div class="admin-topbar">
    <div>
        <h1 style="font-size: 1.85rem; margin-bottom: 0.25rem;">Edit Masterline Statue</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem;">Update edition pricing, allocations, technical specifications, or replace artwork.</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/products.php" class="btn btn-secondary btn-pill btn-sm">
        &larr; Back to Catalog
    </a>
</div>

<?php if (!empty($errors)): ?>
    <div style="background: var(--danger-bg); border: 1px solid #fecaca; color: var(--danger); padding: 1rem 1.25rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;">
        <strong>⚠️ Please fix the following:</strong>
        <ul style="margin-left: 1.5rem; margin-top: 0.35rem; font-size: 0.85rem;">
            <?php foreach ($errors as $err): ?>
                <li><?= sanitize($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 820px;">
    <form method="POST" action="<?= BASE_URL ?>/admin/product-edit.php?id=<?= $product_id ?>" enctype="multipart/form-data" data-validate>
        <div class="form-group">
            <label class="form-label" for="name">Statue Title *</label>
            <input type="text" name="name" id="name" class="form-control" value="<?= sanitize($name) ?>" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="category_id">Universe Gallery *</label>
                <select name="category_id" id="category_id" class="form-control" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (int)$category_id === (int)$cat['id'] ? 'selected' : '' ?>>
                            <?= sanitize($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="brand">Studio Manufacturer *</label>
                <input type="text" name="brand" id="brand" class="form-control" value="<?= sanitize($brand) ?>" placeholder="Prime 1 Studio / Queen Studios" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="scale">Edition Scale *</label>
                <input type="text" name="scale" id="scale" class="form-control" value="<?= sanitize($scale) ?>" placeholder="1/4 Scale, 1/6 Diecast" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="price">Price (USD) *</label>
                <input type="number" step="0.01" min="0.01" name="price" id="price" class="form-control" value="<?= sanitize($price) ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="original_price">Original / Retail Price (USD)</label>
                <input type="number" step="0.01" min="0.01" name="original_price" id="original_price" class="form-control" value="<?= sanitize($original_price) ?>" placeholder="e.g. 799.00 (optional)">
            </div>

            <div class="form-group">
                <label class="form-label" for="stock_quantity">Remaining Allocation *</label>
                <input type="number" min="0" name="stock_quantity" id="stock_quantity" class="form-control" value="<?= sanitize($stock_quantity) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="description">Studio Technical Specifications</label>
            <textarea name="description" id="description" rows="4" class="form-control"><?= sanitize($description) ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Active Pedestal Artwork</label>
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; background: #f8fafc; padding: 0.85rem 1.25rem; border-radius: var(--radius-md); border: 1px solid var(--border);">
                <img src="<?= get_product_image($product['image']) ?>" alt="Current Artwork" style="width: 72px; height: 72px; border-radius: 8px; border: 1px solid var(--border); object-fit: contain; background: #ffffff;">
                <div>
                    <div style="font-size: 0.9rem; font-weight: 700; color: var(--dark);"><?= sanitize($product['image']) ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">Currently active in studio gallery</div>
                </div>
            </div>

            <label class="form-label" for="productImage">Replace Artwork File (optional &bull; SVG, PNG, JPG, WEBP &bull; Max 10MB)</label>
            <div style="border: 2px dashed var(--border-dark); border-radius: var(--radius-md); padding: 1.5rem; background: #fafafa; text-align: center;">
                <input type="file" name="image" id="productImage" class="form-control" accept=".jpg,.jpeg,.png,.webp,.svg" style="max-width: 400px; margin: 0 auto;">
                <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem;">Leave empty to keep existing artwork</p>
                <div id="imagePreviewContainer" style="display: none; margin-top: 1.25rem; align-items: center; justify-content: center; gap: 1rem; background: #ffffff; padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--border); max-width: 420px; margin-left: auto; margin-right: auto;">
                    <img id="imagePreview" src="#" alt="New Artwork Preview" style="max-width: 120px; max-height: 120px; border-radius: 8px; object-fit: contain; background: #f8fafc; border: 1px solid var(--border);">
                    <div style="text-align: left; font-size: 0.85rem;">
                        <div id="previewFilename" style="font-weight: 700; color: var(--dark); word-break: break-all;"></div>
                        <div id="previewFilesize" style="color: var(--text-muted); font-size: 0.8rem; margin-top: 2px;"></div>
                        <span class="badge badge-warning" style="margin-top: 6px; font-size: 0.7rem;">Will Replace Current</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-check" style="cursor: pointer;">
                <input type="checkbox" name="is_featured" value="1" <?= $is_featured ? 'checked' : '' ?> style="transform: scale(1.2); accent-color: var(--primary);">
                <span>Mark as <strong>Featured Masterpiece</strong></span>
            </label>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem; border-top: 1px solid var(--border); padding-top: 1.5rem;">
            <button type="submit" class="btn btn-primary btn-pill btn-lg">
                💾 Update Masterline Statue
            </button>
            <a href="<?= BASE_URL ?>/admin/products.php" class="btn btn-secondary btn-pill btn-lg">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
