<?php
/**
 * Admin Panel - Footer Include
 * Renders flash messages and closes main content area & scripts
 */

// Display flash message if one exists
$flash = get_flash();
if ($flash):
?>
<div class="flash-message flash-<?= $flash['type'] ?>" id="flashMessage">
    <?= sanitize($flash['message']) ?>
    <button class="flash-close" onclick="this.parentElement.remove()">×</button>
</div>
<?php endif; ?>

    </main>
</div>

<script src="<?= BASE_URL ?>/assets/js/script.js"></script>
</body>
</html>
