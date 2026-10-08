<?php
/**
 * IKE PRODUCTS LOUNGE - Navigation Bar
 * Luxury studio minimalist header with cart indicator
 */
$cart_count = get_cart_count();
$wishlist_count = get_wishlist_count();
?>
<header class="site-header">
    <div class="container">
        <nav class="navbar">
            <!-- Brand Logo -->
            <a href="<?= BASE_URL ?>/index.php" class="nav-brand" style="display: flex; align-items: center; text-decoration: none; gap: 0.75rem;">
                <img src="<?= BASE_URL ?>/assets/images/logo-icon.svg" alt="IKE PRODUCTS LOUNGE" style="height: 42px; width: 42px; flex-shrink: 0; display: block; border-radius: 10px;">
                <div style="display: flex; flex-direction: column; line-height: 1.1;">
                    <span style="font-size: 1.25rem; font-weight: 900; letter-spacing: -0.01em; color: #0A0E2A; font-family: var(--font);">IKE PRODUCTS</span>
                    <span style="font-size: 0.7rem; font-weight: 800; letter-spacing: 3.5px; color: #FF5436; text-transform: uppercase;">LOUNGE</span>
                </div>
            </a>

            <!-- Mobile menu toggle -->
            <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation">
                <span></span><span></span><span></span>
            </button>

            <!-- Nav links -->
            <ul class="nav-links" id="navLinks">
                <li><a href="<?= BASE_URL ?>/index.php">Featured</a></li>
                <li><a href="<?= BASE_URL ?>/products.php">Collectibles</a></li>
                <li><a href="<?= BASE_URL ?>/products.php?sort=newest">Trending</a></li>
                <li>
                    <a href="<?= BASE_URL ?>/wishlist.php" class="cart-pill" style="background: rgba(255, 84, 54, 0.08); color: var(--accent); border-color: rgba(255, 84, 54, 0.2);">
                        ❤️ Saved
                        <?php if ($wishlist_count > 0): ?>
                            <span class="cart-badge" style="background: var(--accent);"><?= $wishlist_count ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="<?= BASE_URL ?>/cart.php" class="cart-pill">
                        🛒 Bag
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-badge" id="cartBadge"><?= $cart_count ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if (is_logged_in()): ?>
                    <li><a href="<?= BASE_URL ?>/profile.php">My Account</a></li>
                    <?php if (is_admin()): ?>
                        <li><a href="<?= BASE_URL ?>/admin/index.php" class="btn-admin">Studio Admin</a></li>
                    <?php endif; ?>
                    <li><a href="<?= BASE_URL ?>/logout.php" style="color: var(--text-muted);">Sign Out</a></li>
                <?php else: ?>
                    <li><a href="<?= BASE_URL ?>/login.php">Sign In</a></li>
                    <li><a href="<?= BASE_URL ?>/register.php" class="btn btn-primary btn-sm">Join Club</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
