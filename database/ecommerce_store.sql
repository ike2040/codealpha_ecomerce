-- ===================================================
-- IKE PRODUCTS LOUNGE Database Schema & Production Data
-- Luxury Studio Figures & Museum-Scale Statues
-- Founded & Directed by Isaac Ofori
-- Compatible with MySQL 5.7+ and MariaDB 10.3+
-- ===================================================

CREATE DATABASE IF NOT EXISTS `ecommerce_store` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ecommerce_store`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `coupons`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------
-- 1. Users Table
-- ---------------------------------------------------
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

-- ---------------------------------------------------
-- 2. Categories Table
-- ---------------------------------------------------
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------
-- 3. Products Table
-- ---------------------------------------------------
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

-- ---------------------------------------------------
-- 4. Orders Table
-- ---------------------------------------------------
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

-- ---------------------------------------------------
-- 5. Order Items Table
-- ---------------------------------------------------
CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `quantity` INT NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------
-- 6. Reviews Table
-- ---------------------------------------------------
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

-- ---------------------------------------------------
-- 7. Coupons Table
-- ---------------------------------------------------
CREATE TABLE `coupons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `discount_percent` INT NOT NULL DEFAULT 10,
  `description` VARCHAR(150) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===================================================
-- SEED DATA
-- Default Credentials:
-- Administrator: Isaac Ofori (isaac0594844398@gmail.com / admin123)
-- Collector:     Marcus Vance (john@example.com / customer123)
-- ===================================================

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `address`) VALUES
(1, 'Isaac Ofori', 'isaac0594844398@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '+233594844398', 'Accra, Ghana / Store HQ'),
(2, 'Marcus Vance', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', '+1 (555) 432-1920', '742 Evergreen Terrace, Penthouse 12');

INSERT INTO `categories` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Marvel Universe', 'marvel', 'Museum-scale statues and limited edition masterline collectibles from the Marvel Universe.'),
(2, 'DC Comics', 'dc-comics', 'Masterline polystone statues, Batman Prime editions, and iconic DC superheroes and villains.'),
(3, 'Star Wars & Sci-Fi', 'star-wars', 'Legendary Sith Lords, Mandalorian warriors, and high-fidelity galactic artifacts.'),
(4, 'Anime & Gaming', 'anime-gaming', 'Premium statues from Dragon Ball, Cyberpunk 2077, and world-renowned gaming icons.');

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `original_price`, `brand`, `scale`, `rating`, `reviews_count`, `stock_quantity`, `image`, `is_featured`) VALUES
(1, 1, 'Spider-Man 2099', 'Developed and manufactured in partnership with Prime 1 Studio, we are proud to present Spider-Man 2099. The 26-inch-tall statue captures Miguel O''Hara perched dynamically upon an elaborate Nueva York architectural spire base with LED-illuminated reactor core. Sculpted in high-grade polystone with translucent energy cape detailing and metallic suit finish. Includes certificate of authenticity signed by studio sculptors.', 699.00, 799.00, 'Prime 1 Studio', '1/4 Scale', 5.0, 128, 8, 'spiderman-2099.svg', 1),
(2, 1, 'Thor: Breaker of Worlds', 'Queen Studios collaboration 1/4 scale museum masterpiece featuring the God of Thunder summoning cosmic storm power. Sculpted with dual-wielded Mjolnir and Stormbreaker with interchangeable lightning burst effects, wired tailored cape, and Asgardian crag rock base.', 685.00, 750.00, 'Queen Studios', '1/4 Scale', 4.9, 84, 5, 'thor-statue.svg', 1),
(3, 1, 'Iron Man Mark IV', 'Masterpiece diecast 1/6 scale articulated collector edition with mechanical robotic gantry assembly suite. Features 28 LED illumination points across arc reactor, repulsor palms, and motorized gantry rings. Complete with interchangeable battle-damaged chest armor plates.', 635.00, 699.00, 'Hot Toys', '1/6 Diecast', 4.9, 156, 12, 'ironman-mark4.svg', 1),
(4, 1, 'Hulkbuster Heavy Assault', 'Massive 1/4 scale polystone powerhouse standing 31 inches tall. Built with motorized opening helmet revealing the Mark XLIII interior bust, weathered metallic paint finish, and 16 internal LED illumination nodes.', 1289.00, 1450.00, 'Prime 1 Studio', '1/4 Scale', 5.0, 62, 3, 'hulkbuster.svg', 1),
(5, 2, 'Cyborg Superman Prime', 'Imposing 32-inch statue capturing Hank Henshaw atop the Fortress of Solitude ruins. Features intricate exposed chrome biomechanical pistons, cybernetic cannon arm with interchangeable hands, real tailored fabric cape, and illuminated cyber-eye.', 1229.00, 1399.00, 'Prime 1 Studio', '1/3 Scale', 4.8, 39, 4, 'cyborg-superman.svg', 1),
(6, 2, 'The Batman: Dark Knight', '1/3 scale limited edition polystone statue of Batman perched atop a gothic cathedral gargoyle plinth overlooking Gotham. Includes swap-out cowl portraits, wired posable fabric cape, and magnetic diecast batarangs.', 599.00, 679.00, 'Queen Studios', '1/3 Scale', 5.0, 194, 7, 'batman-gargoyle.svg', 1),
(7, 3, 'Darth Vader: Lord of the Sith', 'Museum-quality 1/4 scale statue sculpted with authentic Mustafar molten obsidian base. Features real tailored cloth tunic, billowing wool-blend cape, working chest control sequence lights, and brilliant illuminated crimson lightsaber.', 749.00, 829.00, 'Sideshow Collectibles', '1/4 Scale', 4.9, 112, 6, 'darth-vader.svg', 1),
(8, 3, 'Boba Fett: Daimyo Throne Room', 'Commanding collector statue featuring Boba Fett seated atop the carved sandstone throne of Jabba''s Palace. Includes weathered Beskar armor plates, EE-3 carbine blaster rifle, and detailed Rancor relief carvings.', 489.00, 549.00, 'Hot Toys', '1/4 Scale', 4.7, 75, 10, 'boba-fett.svg', 0),
(9, 4, 'Goku Ultra Instinct', '1/4 scale dynamic battle statue sculpted in translucent resin with pearlescent silver hair finish. Surrounded by multi-layered silver and azure aura flames inspired by the Tournament of Power climax.', 349.00, 399.00, 'Tsume Art', '1/4 Scale', 4.9, 210, 15, 'goku-ultra.svg', 1),
(10, 4, 'Cyberpunk 2077: Yaiba Kusanagi', 'Precision-engineered 1/6 scale diecast replica of Night City''s iconic motorcycle with rolling wheels, steering linkage, working LED headlights, and digital console display. Currently sold out for this production run.', 520.00, 580.00, 'PureArts', '1/6 Diecast', 4.8, 91, 0, 'cyberpunk-bike.svg', 0);

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
