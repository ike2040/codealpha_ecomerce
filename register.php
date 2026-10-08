<?php
/**
 * IKE PRODUCTS LOUNGE - Collector Registration (register.php)
 * Creates new collector accounts with validation and secure password hashing
 */

$page_title = 'Join Collector Club | IKE PRODUCTS LOUNGE';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/functions.php';

if (is_logged_in()) {
    redirect(BASE_URL . '/profile.php');
}

$db = getDB();

$name     = '';
$email    = '';
$phone    = '';
$address  = '';
$errors   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name             = trim($_POST['name'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $phone            = trim($_POST['phone'] ?? '');
    $address          = trim($_POST['address'] ?? '');
    $password         = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Validation
    if (empty($name)) {
        $errors['name'] = 'Please enter your full legal name.';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'A valid email address is required.';
    } else {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'An account with this email address already exists.';
        }
    }

    if (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters in length.';
    } elseif ($password !== $password_confirm) {
        $errors['password_confirm'] = 'Passwords do not match. Please re-enter.';
    }

    // Process registration if validation passes
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $insert_stmt = $db->prepare("
            INSERT INTO users (name, email, password, role, phone, address)
            VALUES (?, ?, ?, 'customer', ?, ?)
        ");
        $insert_stmt->execute([$name, $email, $hashed_password, $phone, $address]);

        $new_user_id = $db->lastInsertId();

        $_SESSION['user_id']    = $new_user_id;
        $_SESSION['user_name']  = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role']  = 'customer';

        set_flash('success', '✨ Welcome to IKE PRODUCTS LOUNGE, ' . sanitize($name) . '. Your membership is now active.');
        redirect(BASE_URL . '/profile.php');
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="auth-wrapper">
    <div class="auth-card" style="max-width: 520px;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <a href="<?= BASE_URL ?>/index.php" style="display: inline-flex; align-items: center; text-decoration: none; gap: 0.85rem; margin-bottom: 1.25rem;">
                <img src="<?= BASE_URL ?>/assets/images/logo-icon.svg" alt="IKE PRODUCTS LOUNGE" style="height: 48px; width: 48px; flex-shrink: 0; display: block; border-radius: 12px;">
                <div style="display: flex; flex-direction: column; line-height: 1.1; text-align: left;">
                    <span style="font-size: 1.4rem; font-weight: 900; letter-spacing: -0.01em; color: #0A0E2A; font-family: var(--font);">IKE PRODUCTS</span>
                    <span style="font-size: 0.75rem; font-weight: 800; letter-spacing: 3.5px; color: #FF5436; text-transform: uppercase;">LOUNGE</span>
                </div>
            </a>
            <h2 style="font-size: 1.75rem; margin-bottom: 0.35rem;">Join the Collector Club</h2>
            <p style="color: var(--text-muted); font-size: 0.95rem;">Unlock priority pre-orders, studio allocations, and vault access</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div style="background: var(--danger-bg); border: 1px solid #fecaca; color: var(--danger); padding: 0.85rem 1.25rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;">
                <strong>⚠️ Please address the following items:</strong>
                <ul style="margin-left: 1.25rem; margin-top: 0.35rem; font-size: 0.85rem;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= sanitize($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/register.php" data-validate>
            <div class="form-group">
                <label class="form-label" for="name">Full Legal Name *</label>
                <input type="text" name="name" id="name" class="form-control" value="<?= sanitize($name) ?>" placeholder="e.g. Victoria Sterling" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address *</label>
                <input type="email" name="email" id="email" class="form-control" value="<?= sanitize($email) ?>" placeholder="victoria@sterling.com" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="tel" name="phone" id="phone" class="form-control" value="<?= sanitize($phone) ?>" placeholder="+233 00 000 0000">
                </div>
                <div class="form-group">
                    <label class="form-label" for="address">Primary Dispatch Address</label>
                    <input type="text" name="address" id="address" class="form-control" value="<?= sanitize($address) ?>" placeholder="City, Country">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="password">Password (min 6 chars) *</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password_confirm">Confirm Password *</label>
                    <input type="password" name="password_confirm" id="password_confirm" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <div style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary btn-pill btn-block btn-lg">
                    Create Membership &rarr;
                </button>
            </div>
        </form>

        <div style="text-align: center; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border); font-size: 0.9rem; color: var(--text-muted);">
            Already an existing member? 
            <a href="<?= BASE_URL ?>/login.php" style="font-weight: 700;">Sign in to your account</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
