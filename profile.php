<?php
/**
 * IKE PRODUCTS LOUNGE - Collector Profile & Vault (profile.php)
 * Member profile settings, security management, and order history
 */

$page_title = 'Collector Profile & Vault | IKE PRODUCTS LOUNGE';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/functions.php';

require_login();

$db = getDB();
$user_id = $_SESSION['user_id'];

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $name    = trim($_POST['name'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if (!empty($name)) {
            $upd = $db->prepare("UPDATE users SET name = ?, phone = ?, address = ? WHERE id = ?");
            $upd->execute([$name, $phone, $address, $user_id]);
            $_SESSION['user_name'] = $name;
            set_flash('success', 'Profile information updated.');
        } else {
            set_flash('error', 'Full name cannot be left blank.');
        }
        redirect(BASE_URL . '/profile.php');
    }

    if ($_POST['action'] === 'change_password') {
        $current_pass = $_POST['current_password'] ?? '';
        $new_pass     = $_POST['new_password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        $p_stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $p_stmt->execute([$user_id]);
        $existing_hash = $p_stmt->fetchColumn();

        if (!password_verify($current_pass, $existing_hash)) {
            set_flash('error', 'Current password was entered incorrectly.');
        } elseif (strlen($new_pass) < 6) {
            set_flash('error', 'New password must contain at least 6 characters.');
        } elseif ($new_pass !== $confirm_pass) {
            set_flash('error', 'New passwords do not match.');
        } else {
            $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $upd = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upd->execute([$new_hash, $user_id]);
            set_flash('success', 'Security password updated successfully.');
        }
        redirect(BASE_URL . '/profile.php');
    }
}

// Fetch user profile data
$u_stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$u_stmt->execute([$user_id]);
$user = $u_stmt->fetch();

// Fetch orders history
$o_stmt = $db->prepare("
    SELECT o.*, COUNT(oi.id) AS total_items 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.id DESC
");
$o_stmt->execute([$user_id]);
$orders = $o_stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="section">
    <div class="container">
        <!-- Account Header -->
        <div style="margin-bottom: 2.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 style="font-size: 2.25rem; margin-bottom: 0.25rem;">Collector Archive &amp; Vault</h1>
                <p style="color: var(--text-muted);">
                    Welcome, <strong><?= sanitize($user['name']) ?></strong> (<?= sanitize($user['email']) ?>)
                </p>
            </div>
            <div>
                <span class="badge badge-scale" style="background: var(--primary-light); color: var(--primary);">
                    <?= $user['role'] === 'admin' ? 'Studio Executive' : 'VIP Collector Member' ?>
                </span>
                <?php if (is_admin()): ?>
                    <a href="<?= BASE_URL ?>/admin/index.php" class="btn btn-admin btn-sm" style="margin-left: 0.5rem;">Studio Admin Panel &rarr;</a>
                <?php endif; ?>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">
            <!-- Left: Settings -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div class="card">
                    <h3 class="card-title">Member Details</h3>
                    <form method="POST" action="<?= BASE_URL ?>/profile.php">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="form-group">
                            <label class="form-label" for="name">Legal Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="<?= sanitize($user['name']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Collector Email</label>
                            <input type="email" class="form-control" value="<?= sanitize($user['email']) ?>" disabled style="background: #f1f5f9; cursor: not-allowed;">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="phone">Telephone Number</label>
                            <input type="tel" name="phone" id="phone" class="form-control" value="<?= sanitize($user['phone'] ?? '') ?>" placeholder="+1 (555) 000-0000">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="address">Dispatch Destination</label>
                            <textarea name="address" id="address" rows="2" class="form-control"><?= sanitize($user['address'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-pill btn-block">Save Details</button>
                    </form>
                </div>

                <div class="card">
                    <h3 class="card-title">Account Security</h3>
                    <form method="POST" action="<?= BASE_URL ?>/profile.php">
                        <input type="hidden" name="action" value="change_password">

                        <div class="form-group">
                            <label class="form-label" for="current_password">Current Password</label>
                            <input type="password" name="current_password" id="current_password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="new_password">New Password</label>
                            <input type="password" name="new_password" id="new_password" class="form-control" required minlength="6">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="confirm_password">Confirm Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="6">
                        </div>

                        <button type="submit" class="btn btn-secondary btn-pill btn-block">Update Security</button>
                    </form>
                </div>
            </div>

            <!-- Right: Order History -->
            <div class="card">
                <h3 class="card-title">Acquisition Vault History (<?= count($orders) ?>)</h3>

                <?php if (empty($orders)): ?>
                    <div style="text-align: center; padding: 3rem 1.5rem; color: var(--text-muted);">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🏛️</div>
                        <h4>No Pieces in Your Collection Vault</h4>
                        <p style="margin-bottom: 1.5rem;">Your acquired limited edition pieces will be permanently logged here with tracking info.</p>
                        <a href="<?= BASE_URL ?>/products.php" class="btn btn-primary btn-pill btn-sm">Explore Galleries</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Reference #</th>
                                    <th>Date</th>
                                    <th>Allocations</th>
                                    <th>Settlement</th>
                                    <th>Status</th>
                                    <th style="text-align: right;">Receipt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $ord): ?>
                                    <tr>
                                        <td>
                                            <strong style="color: var(--primary);">#IPL-<?= str_pad($ord['id'], 5, '0', STR_PAD_LEFT) ?></strong>
                                        </td>
                                        <td style="font-size: 0.85rem; color: var(--text-muted);">
                                            <?= date('M j, Y', strtotime($ord['created_at'])) ?>
                                        </td>
                                        <td><strong><?= (int)$ord['total_items'] ?></strong> piece(s)</td>
                                        <td style="font-weight: 800; color: var(--dark);">
                                            <?= format_price($ord['total_amount']) ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= get_status_class($ord['status']) ?>">
                                                <?= sanitize($ord['status']) ?>
                                            </span>
                                        </td>
                                        <td style="text-align: right;">
                                            <a href="<?= BASE_URL ?>/order-confirmation.php?order_id=<?= $ord['id'] ?>" class="btn btn-secondary btn-sm btn-pill" title="View Certificate">
                                                Receipt
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
