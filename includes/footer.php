<?php
/**
 * IKE PRODUCTS LOUNGE - Site Footer
 * Luxury studio brand footer, executive concierge, and scripts
 */

// Display flash message if one exists
$flash = get_flash();
if ($flash):
?>
<div class="flash-message flash-<?= $flash['type'] ?>" id="flashMessage">
    <div><?= sanitize($flash['message']) ?></div>
    <button class="flash-close" onclick="this.parentElement.remove()">×</button>
</div>
<?php endif; ?>

<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <div style="margin-bottom: 1.25rem;">
                    <a href="<?= BASE_URL ?>/index.php" style="display: inline-flex; align-items: center; text-decoration: none; gap: 0.75rem;">
                        <img src="<?= BASE_URL ?>/assets/images/logo-icon.svg" alt="IKE PRODUCTS LOUNGE" style="height: 44px; width: 44px; flex-shrink: 0; display: block; border-radius: 10px;">
                        <div style="display: flex; flex-direction: column; line-height: 1.1;">
                            <span style="font-size: 1.3rem; font-weight: 900; letter-spacing: -0.01em; color: #ffffff; font-family: var(--font);">IKE PRODUCTS</span>
                            <span style="font-size: 0.75rem; font-weight: 800; letter-spacing: 3.5px; color: #FF5436; text-transform: uppercase;">LOUNGE</span>
                        </div>
                    </a>
                </div>
                <p>The premier luxury lounge for museum-scale statues, limited-edition masterline figures, and curated collectible art.</p>
                <p style="color: var(--text-subtle); font-size: 0.85rem;">Official Licensed Partner of Prime 1 Studio, Queen Studios, Sideshow, and Hot Toys.</p>
            </div>
            <div class="footer-col">
                <h4>Galleries</h4>
                <ul>
                    <li><a href="<?= BASE_URL ?>/products.php?category=marvel">Marvel Universe</a></li>
                    <li><a href="<?= BASE_URL ?>/products.php?category=dc-comics">DC Comics Masterline</a></li>
                    <li><a href="<?= BASE_URL ?>/products.php?category=star-wars">Star Wars &amp; Sci-Fi</a></li>
                    <li><a href="<?= BASE_URL ?>/products.php?category=anime-gaming">Anime &amp; Gaming</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Collector Services</h4>
                <ul>
                    <li><a href="<?= BASE_URL ?>/profile.php">Collector Account</a></li>
                    <li><a href="<?= BASE_URL ?>/orders.php">Acquisition Tracking</a></li>
                    <li><a href="<?= BASE_URL ?>/wishlist.php">Saved Pieces Vault</a></li>
                    <li><a href="<?= BASE_URL ?>/cart.php">Shopping Bag</a></li>
                    <li><a href="<?= BASE_URL ?>/login.php">VIP Lounge</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Executive Concierge</h4>
                <p style="color: white; font-weight: 700; margin-bottom: 0.25rem;">👤 Isaac Ofori</p>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 0.65rem;">Founder &amp; Managing Director</p>
                <p>📞 <a href="tel:+233594844398" style="color: #cbd5e1;">+233594844398</a></p>
                <p>📧 <a href="mailto:isaac0594844398@gmail.com" style="color: #cbd5e1;">isaac0594844398@gmail.com</a></p>
                <p style="font-size: 0.85rem; color: var(--text-subtle); margin-top: 0.65rem;">🛡️ White-Glove Insured Courier &amp; Numbered Certificate of Authenticity.</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> IKE PRODUCTS LOUNGE. Founded &amp; Directed by Isaac Ofori. All Rights Reserved.</p>
            <div style="display: flex; gap: 1.5rem;">
                <span style="color: var(--text-subtle);">Authenticity Guaranteed</span>
                <span style="color: var(--text-subtle);">Insured Dispatch</span>
            </div>
        </div>
    </div>
</footer>

<script src="<?= BASE_URL ?>/assets/js/script.js"></script>
</body>
</html>
