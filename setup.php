<?php
/**
 * IKE PRODUCTS LOUNGE - Database Initialization & Production Seeder
 * Access via: http://localhost/codealpha_ecommerce/setup.php
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'ecommerce_store';

$status = [];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['run'])) {
    try {
        $pdo = new PDO("mysql:host=$host", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $status[] = "Connected to MySQL server successfully.";

        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        $pdo->exec("USE `$dbName`;");
        $status[] = "Database `$dbName` initialized.";

        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $pdo->exec("DROP TABLE IF EXISTS `reviews`;");
        $pdo->exec("DROP TABLE IF EXISTS `coupons`;");
        $pdo->exec("DROP TABLE IF EXISTS `order_items`;");
        $pdo->exec("DROP TABLE IF EXISTS `orders`;");
        $pdo->exec("DROP TABLE IF EXISTS `products`;");
        $pdo->exec("DROP TABLE IF EXISTS `categories`;");
        $pdo->exec("DROP TABLE IF EXISTS `users`;");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        // 1. Create Tables
        $pdo->exec("
            CREATE TABLE `users` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `name` VARCHAR(100) NOT NULL,
              `email` VARCHAR(150) NOT NULL UNIQUE,
              `password` VARCHAR(255) NOT NULL,
              `role` ENUM('admin','customer') DEFAULT 'customer',
              `phone` VARCHAR(20) DEFAULT NULL,
              `address` TEXT DEFAULT NULL,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE `categories` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `name` VARCHAR(100) NOT NULL,
              `slug` VARCHAR(100) NOT NULL UNIQUE,
              `description` TEXT DEFAULT NULL,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE `products` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `category_id` INT NOT NULL,
              `name` VARCHAR(150) NOT NULL,
              `description` TEXT DEFAULT NULL,
              `price` DECIMAL(10,2) NOT NULL,
              `original_price` DECIMAL(10,2) DEFAULT NULL,
              `brand` VARCHAR(100) DEFAULT 'Prime 1 Studio',
              `scale` VARCHAR(50) DEFAULT '1/4 Scale',
              `rating` DECIMAL(2,1) DEFAULT 4.9,
              `reviews_count` INT DEFAULT 1,
              `stock_quantity` INT NOT NULL DEFAULT 0,
              `image` VARCHAR(255) DEFAULT 'default-product.svg',
              `is_featured` TINYINT(1) DEFAULT 0,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE `orders` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `user_id` INT DEFAULT NULL,
              `total_amount` DECIMAL(10,2) NOT NULL,
              `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
              `coupon_code` VARCHAR(50) DEFAULT NULL,
              `shipping_name` VARCHAR(100) NOT NULL,
              `shipping_email` VARCHAR(150) NOT NULL,
              `shipping_phone` VARCHAR(20) NOT NULL,
              `shipping_address` TEXT NOT NULL,
              `city` VARCHAR(100) NOT NULL,
              `zip_code` VARCHAR(20) NOT NULL,
              `payment_method` VARCHAR(50) NOT NULL DEFAULT 'Credit / Debit Card',
              `status` ENUM('Pending','Processing','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE `order_items` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `order_id` INT NOT NULL,
              `product_id` INT NOT NULL,
              `price` DECIMAL(10,2) NOT NULL,
              `quantity` INT NOT NULL,
              FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
              FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE `reviews` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `product_id` INT NOT NULL,
              `user_id` INT DEFAULT NULL,
              `author_name` VARCHAR(100) NOT NULL,
              `rating` INT NOT NULL DEFAULT 5,
              `comment` TEXT NOT NULL,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE `coupons` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `code` VARCHAR(50) NOT NULL UNIQUE,
              `discount_percent` INT NOT NULL DEFAULT 10,
              `description` VARCHAR(150) NOT NULL,
              `is_active` TINYINT(1) DEFAULT 1
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        $status[] = "Database schema verified with foreign keys, reviews, and coupons.";

        // 2. Seed Users
        $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
        $custPass  = password_hash('customer123', PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `address`) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([1, 'Isaac Ofori', 'isaac0594844398@gmail.com', $adminPass, 'admin', '+233594844398', 'Accra, Ghana / Store HQ']);
        $stmt->execute([2, 'Marcus Vance', 'john@example.com', $custPass, 'customer', '+1 (555) 432-1920', '742 Evergreen Terrace, Penthouse 12']);
        $status[] = "Executive Admin Isaac Ofori and customer accounts initialized.";

        // 3. Seed Categories
        $catStmt = $pdo->prepare("INSERT INTO `categories` (`id`, `name`, `slug`, `description`) VALUES (?, ?, ?, ?)");
        $catStmt->execute([1, 'Marvel Universe', 'marvel', 'Museum-scale statues and limited edition masterline collectibles from the Marvel Universe.']);
        $catStmt->execute([2, 'DC Comics', 'dc-comics', 'Masterline polystone statues, Batman Prime editions, and iconic DC superheroes and villains.']);
        $catStmt->execute([3, 'Star Wars & Sci-Fi', 'star-wars', 'Legendary Sith Lords, Mandalorian warriors, and high-fidelity galactic artifacts.']);
        $catStmt->execute([4, 'Anime & Gaming', 'anime-gaming', 'Premium statues from Dragon Ball, Cyberpunk 2077, and world-renowned gaming icons.']);
        $status[] = "4 Collector categories registered.";

        // 4. Seed Products
        $prodStmt = $pdo->prepare("
            INSERT INTO `products` 
            (`id`, `category_id`, `name`, `description`, `price`, `original_price`, `brand`, `scale`, `rating`, `reviews_count`, `stock_quantity`, `image`, `is_featured`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $prodStmt->execute([1, 1, 'Spider-Man 2099', 'Developed and manufactured in partnership with Prime 1 Studio, we are proud to present Spider-Man 2099. The 26-inch-tall statue captures Miguel O''Hara perched dynamically upon an elaborate Nueva York architectural spire base with LED-illuminated reactor core. Sculpted in high-grade polystone with translucent energy cape detailing and metallic suit finish. Includes certificate of authenticity signed by studio sculptors.', 699.00, 799.00, 'Prime 1 Studio', '1/4 Scale', 5.0, 128, 8, 'spiderman-2099.svg', 1]);
        $prodStmt->execute([2, 1, 'Thor: Breaker of Worlds', 'Queen Studios collaboration 1/4 scale museum masterpiece featuring the God of Thunder summoning cosmic storm power. Sculpted with dual-wielded Mjolnir and Stormbreaker with interchangeable lightning burst effects, wired tailored cape, and Asgardian crag rock base.', 685.00, 750.00, 'Queen Studios', '1/4 Scale', 4.9, 84, 5, 'thor-statue.svg', 1]);
        $prodStmt->execute([3, 1, 'Iron Man Mark IV', 'Masterpiece diecast 1/6 scale articulated collector edition with mechanical robotic gantry assembly suite. Features 28 LED illumination points across arc reactor, repulsor palms, and motorized gantry rings. Complete with interchangeable battle-damaged chest armor plates.', 635.00, 699.00, 'Hot Toys', '1/6 Diecast', 4.9, 156, 12, 'ironman-mark4.svg', 1]);
        $prodStmt->execute([4, 1, 'Hulkbuster Heavy Assault', 'Massive 1/4 scale polystone powerhouse standing 31 inches tall. Built with motorized opening helmet revealing the Mark XLIII interior bust, weathered metallic paint finish, and 16 internal LED illumination nodes.', 1289.00, 1450.00, 'Prime 1 Studio', '1/4 Scale', 5.0, 62, 3, 'hulkbuster.svg', 1]);
        $prodStmt->execute([5, 2, 'Cyborg Superman Prime', 'Imposing 32-inch statue capturing Hank Henshaw atop the Fortress of Solitude ruins. Features intricate exposed chrome biomechanical pistons, cybernetic cannon arm with interchangeable hands, real tailored fabric cape, and illuminated cyber-eye.', 1229.00, 1399.00, 'Prime 1 Studio', '1/3 Scale', 4.8, 39, 4, 'cyborg-superman.svg', 1]);
        $prodStmt->execute([6, 2, 'The Batman: Dark Knight', '1/3 scale limited edition polystone statue of Batman perched atop a gothic cathedral gargoyle plinth overlooking Gotham. Includes swap-out cowl portraits, wired posable fabric cape, and magnetic diecast batarangs.', 599.00, 679.00, 'Queen Studios', '1/3 Scale', 5.0, 194, 7, 'batman-gargoyle.svg', 1]);
        $prodStmt->execute([7, 3, 'Darth Vader: Lord of the Sith', 'Museum-quality 1/4 scale statue sculpted with authentic Mustafar molten obsidian base. Features real tailored cloth tunic, billowing wool-blend cape, working chest control sequence lights, and brilliant illuminated crimson lightsaber.', 749.00, 829.00, 'Sideshow Collectibles', '1/4 Scale', 4.9, 112, 6, 'darth-vader.svg', 1]);
        $prodStmt->execute([8, 3, 'Boba Fett: Daimyo Throne Room', 'Commanding collector statue featuring Boba Fett seated atop the carved sandstone throne of Jabba''s Palace. Includes weathered Beskar armor plates, EE-3 carbine blaster rifle, and detailed Rancor relief carvings.', 489.00, 549.00, 'Hot Toys', '1/4 Scale', 4.7, 75, 10, 'boba-fett.svg', 0]);
        $prodStmt->execute([9, 4, 'Goku Ultra Instinct', '1/4 scale dynamic battle statue sculpted in translucent resin with pearlescent silver hair finish. Surrounded by multi-layered silver and azure aura flames inspired by the Tournament of Power climax.', 349.00, 399.00, 'Tsume Art', '1/4 Scale', 4.9, 210, 15, 'goku-ultra.svg', 1]);
        $prodStmt->execute([10, 4, 'Cyberpunk 2077: Yaiba Kusanagi', 'Precision-engineered 1/6 scale diecast replica of Night City''s iconic motorcycle with rolling wheels, steering linkage, working LED headlights, and digital console display. Currently sold out for this production run.', 520.00, 580.00, 'PureArts', '1/6 Diecast', 4.8, 91, 0, 'cyberpunk-bike.svg', 0);
        $status[] = "10 Masterline collector statues & figures seeded with pedestal artworks.";

        // 5. Seed Orders, Coupons & Reviews
        $pdo->exec("
            INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `discount_amount`, `coupon_code`, `shipping_name`, `shipping_email`, `shipping_phone`, `shipping_address`, `city`, `zip_code`, `payment_method`, `status`, `created_at`) VALUES
            (1, 2, 1384.00, 0.00, NULL, 'Marcus Vance', 'john@example.com', '+1 (555) 432-1920', '742 Evergreen Terrace, Penthouse 12', 'San Francisco', '94102', 'Credit / Debit Card', 'Delivered', DATE_SUB(NOW(), INTERVAL 4 DAY)),
            (2, 2, 629.10, 69.90, 'VIP10', 'Marcus Vance', 'john@example.com', '+1 (555) 432-1920', '742 Evergreen Terrace, Penthouse 12', 'San Francisco', '94102', 'Credit / Debit Card', 'Processing', NOW());

            INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `price`, `quantity`) VALUES
            (1, 1, 1, 699.00, 1),
            (1, 1, 2, 685.00, 1),
            (2, 2, 1, 699.00, 1);

            INSERT INTO `coupons` (`code`, `discount_percent`, `description`, `is_active`) VALUES
            ('VIP10', 10, 'VIP Collector 10% Privilege Discount', 1),
            ('ISAAC10', 10, 'Isaac Ofori Founder Special 10% Off', 1),
            ('WELCOME5', 5, 'New Club Member 5% Welcome Incentive', 1);

            INSERT INTO `reviews` (`product_id`, `author_name`, `rating`, `comment`, `created_at`) VALUES
            (1, 'Alexander Wright (Verified Collector)', 5, 'The polystone weight and LED illuminated reactor core on the spire are astonishing. Arrived in a custom reinforced wooden crate in pristine condition.', DATE_SUB(NOW(), INTERVAL 5 DAY)),
            (1, 'Elena Rostova (Museum Curator)', 5, 'Unmatched 1/4 scale museum fidelity. Miguel O''Hara suit texture catches the light brilliantly.', DATE_SUB(NOW(), INTERVAL 2 DAY)),
            (2, 'Marcus Vance (VIP Collector)', 5, 'Thor looks commanding on display. The interchangeable lightning arcs around Stormbreaker are sculpted to perfection.', DATE_SUB(NOW(), INTERVAL 7 DAY)),
            (6, 'Bruce Wayne Collector', 5, 'The cathedral gargoyle base alone weighs over 15 lbs. The interchangeable cowl portraits are flawless.', DATE_SUB(NOW(), INTERVAL 10 DAY));
        ");
        $status[] = "Recent store orders, discount vouchers, and verified collector reviews initialized.";


        // 6. Sync Visual Assets from assets/images to uploads/products
        $assetDir = __DIR__ . '/assets/images';
        $uploadDir = __DIR__ . '/uploads/products';
        if (is_dir($assetDir) && is_dir($uploadDir)) {
            $syncedCount = 0;
            foreach (glob($assetDir . '/*.svg') as $srcFile) {
                $targetFile = $uploadDir . '/' . basename($srcFile);
                if (!file_exists($targetFile)) {
                    if (@copy($srcFile, $targetFile)) {
                        $syncedCount++;
                    }
                }
            }
            if ($syncedCount > 0) {
                $status[] = "Synchronized $syncedCount product visual assets into uploads directory.";
            }
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IKE PRODUCTS LOUNGE - Database Initialization</title>
    <link rel="icon" type="image/svg+xml" href="/codealpha_ecommerce/assets/images/logo-icon.svg">
    <link rel="stylesheet" href="/codealpha_ecommerce/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body style="background: #0a0e2a; color: #cbd5e1; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 2rem 1rem;">
    <div style="background: #12183a; border-radius: 24px; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 25px 50px rgba(0,0,0,0.5); max-width: 640px; width: 100%; padding: 3rem;">
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <div style="display: inline-flex; align-items: center; justify-content: center; gap: 0.85rem; margin-bottom: 1.25rem;">
                <img src="/codealpha_ecommerce/assets/images/logo-icon.svg" alt="IPL Emblem" style="height: 52px; width: 52px; display: block; border-radius: 12px;">
                <div style="display: flex; flex-direction: column; line-height: 1.1; text-align: left;">
                    <span style="font-size: 1.5rem; font-weight: 900; letter-spacing: -0.01em; color: #ffffff;">IKE PRODUCTS</span>
                    <span style="font-size: 0.8rem; font-weight: 800; letter-spacing: 3.5px; color: #FF5436; text-transform: uppercase;">LOUNGE</span>
                </div>
            </div>
            <p style="color: #94a3b8; font-size: 0.95rem;">Database Initialization &amp; Collector Catalog Provisioning</p>
        </div>

        <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid #ef4444; color: #fca5a5; padding: 1.25rem; border-radius: 12px; margin-bottom: 1.5rem;">
                <strong>⚠️ Initialization Error:</strong>
                <p style="margin-top: 0.5rem; font-size: 0.9rem;"><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($status)): ?>
            <div style="background: rgba(16, 185, 129, 0.12); border: 1px solid #10b981; color: #6ee7b7; padding: 1.25rem; border-radius: 12px; margin-bottom: 2rem;">
                <h3 style="font-size: 1.1rem; margin-bottom: 0.75rem; color: #34d399;">✓ Studio Database Provisioned Successfully</h3>
                <ul style="padding-left: 1.25rem; font-size: 0.875rem; line-height: 1.7;">
                    <?php foreach ($status as $msg): ?>
                        <li><?= htmlspecialchars($msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 1.25rem; margin-bottom: 2.5rem;">
                <h4 style="font-size: 0.95rem; margin-bottom: 0.75rem; color: white;">🔑 Access Credentials:</h4>
                <div style="font-size: 0.85rem; line-height: 1.8; color: #cbd5e1;">
                    <strong>Executive Admin (Isaac Ofori):</strong> <code>isaac0594844398@gmail.com</code> / <code>admin123</code><br>
                    <strong>Collector Account:</strong> <code>john@example.com</code> / <code>customer123</code>
                </div>
            </div>

            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="/codealpha_ecommerce/index.php" class="btn btn-buy-now" style="flex: 1;">Explore Flagship Store</a>
                <a href="/codealpha_ecommerce/admin/index.php" class="btn btn-secondary btn-pill" style="flex: 1;">Studio Dashboard</a>
            </div>
        <?php else: ?>
            <div style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 1.5rem; margin-bottom: 2.5rem; font-size: 0.95rem; line-height: 1.7; color: #94a3b8;">
                <p>Clicking below will provision the <code>ecommerce_store</code> database with:</p>
                <ul style="margin-left: 1.25rem; margin-top: 0.5rem;">
                    <li>10 Museum-Scale Collector Statues &amp; Figures</li>
                    <li>Curated Categories (Marvel, DC Comics, Star Wars, Anime &amp; Gaming)</li>
                    <li>Administrator: Isaac Ofori (<code>isaac0594844398@gmail.com</code> / <code>admin123</code>)</li>
                    <li>Synchronized high-resolution vector artwork assets</li>
                </ul>
            </div>

            <form method="POST">
                <button type="submit" class="btn btn-buy-now btn-block btn-lg" style="width: 100%;">
                    ⚡ Provision Studio Database
                </button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
