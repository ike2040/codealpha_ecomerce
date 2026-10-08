<?php
/**
 * IKE PRODUCTS LOUNGE - Delete Product (admin/product-delete.php)
 * Removes product record from database and cleans up custom images
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/functions.php';

require_admin();

$product_id = (int)($_GET['id'] ?? 0);

if ($product_id > 0) {
    $db = getDB();

    // Look up product image
    $stmt = $db->prepare("SELECT name, image FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if ($product) {
        // Remove uploaded file if custom upload
        $builtin_images = [
            'default-product.svg', 'default-product.jpg',
            'spiderman-2099.svg', 'thor-statue.svg', 'ironman-mark4.svg',
            'hulkbuster.svg', 'cyborg-superman.svg', 'batman-gargoyle.svg',
            'darth-vader.svg', 'boba-fett.svg', 'goku-ultra.svg', 'cyberpunk-bike.svg'
        ];
        if ($product['image'] && !in_array($product['image'], $builtin_images)) {
            $image_path = __DIR__ . '/../uploads/products/' . $product['image'];
            if (file_exists($image_path)) {
                @unlink($image_path);
            }
            $asset_path = __DIR__ . '/../assets/images/' . $product['image'];
            if (file_exists($asset_path)) {
                @unlink($asset_path);
            }
        }

        // Delete from database
        $delete_stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $delete_stmt->execute([$product_id]);

        set_flash('success', 'Masterline edition "' . sanitize($product['name']) . '" removed from catalog.');
    } else {
        set_flash('error', 'Collectible not found.');
    }
} else {
    set_flash('error', 'Invalid product ID specified.');
}

redirect(BASE_URL . '/admin/products.php');
