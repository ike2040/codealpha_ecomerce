<?php
/**
 * IKE PRODUCTS LOUNGE - Acquisition Tracking & Orders Lookup (orders.php)
 * Allows collectors to track transit status by Order Reference # and Email
 */

$page_title = 'Track Acquisition Order | IKE PRODUCTS LOUNGE';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/functions.php';

$db = getDB();

// If logged in, users can also jump to profile vault
$error = '';
$found_order = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['ref'])) {
    $ref_raw = trim($_POST['ref'] ?? ($_GET['ref'] ?? ''));
    $email   = trim($_POST['email'] ?? ($_GET['email'] ?? ''));

    // Strip #IPL- or # prefix if entered
    $clean_id = (int)preg_replace('/[^0-9]/', '', $ref_raw);

    if ($clean_id <= 0) {
        $error = 'Please enter a valid Acquisition Reference number (e.g. #IPL-00001 or 1).';
    } else {
        if (!empty($email)) {
            $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND LOWER(shipping_email) = LOWER(?) LIMIT 1");
            $stmt->execute([$clean_id, $email]);
        } else {
            // If logged in or direct ref check
            $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
            $stmt->execute([$clean_id]);
        }
        $found_order = $stmt->fetch();

        if ($found_order) {
            redirect(BASE_URL . '/order-confirmation.php?order_id=' . $found_order['id']);
        } else {
            $error = 'No acquisition record was found matching reference #' . $clean_id . ($email ? ' and email ' . sanitize($email) : '') . '.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="section">
    <div class="container container-sm">
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <span class="badge badge-scale" style="background: var(--primary-light); color: var(--primary); margin-bottom: 0.5rem; display: inline-block;">
                ✈️ Live Courier &amp; Transit Dispatch
            </span>
            <h1 style="font-size: 2.25rem; margin-bottom: 0.5rem;">Track Your Acquisition</h1>
            <p style="color: var(--text-muted); font-size: 1.05rem; max-width: 500px; margin: 0 auto;">
                Enter your Studio Acquisition Reference code and email to inspect your numbered certificate of authenticity and fulfillment status.
            </p>
        </div>

        <div class="card" style="max-width: 560px; margin: 0 auto;">
            <?php if ($error): ?>
                <div style="background: var(--danger-bg); border: 1px solid #fecaca; color: var(--danger); padding: 0.85rem 1.25rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; font-size: 0.9rem;">
                    ⚠️ <?= sanitize($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/orders.php" data-validate>
                <div class="form-group">
                    <label class="form-label" for="ref">Acquisition Reference # *</label>
                    <input type="text" name="ref" id="ref" class="form-control" placeholder="e.g. #IPL-00001 or 1" required autofocus>
                    <span style="font-size: 0.775rem; color: var(--text-muted);">Provided on your order confirmation receipt.</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Recipient Email Address *</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="e.g. john@example.com" required>
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary btn-pill btn-block btn-lg">
                        Inspect Order Status &rarr;
                    </button>
                </div>
            </form>

            <?php if (is_logged_in()): ?>
                <div style="text-align: center; margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid var(--border); font-size: 0.9rem;">
                    Signed in as <strong><?= sanitize($_SESSION['user_name']) ?></strong>? 
                    <a href="<?= BASE_URL ?>/profile.php" style="font-weight: 700;">View your complete Vault History &rarr;</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
