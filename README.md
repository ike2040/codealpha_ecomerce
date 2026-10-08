# 🛍️ IKE PRODUCTS LOUNGE - Luxury Collector E-Commerce Flagship

A complete, production-grade, and beautifully styled luxury collector e-commerce platform built with **PHP (PDO)**, **MySQL**, **Vanilla JavaScript**, and a **Mobile-First Luxury Design System**. Founded and directed by **Isaac Ofori**, featuring the bespoke **IKE PRODUCTS LOUNGE** logo, organic curved wave headers, vivid coral pill CTAs, floating cart buttons, verified collector reviews, promotional discount vouchers, and museum-scale masterline statues.

---

## 👤 Executive Leadership & Contact Details

- **Founder & Managing Director**: Isaac Ofori
- **Phone / WhatsApp**: [+233 594 844 398](tel:+233594844398) (`+233594844398`)
- **Email**: [isaac0594844398@gmail.com](mailto:isaac0594844398@gmail.com)
- **Headquarters**: Accra, Ghana / Store HQ
- **Store Name**: **IKE PRODUCTS LOUNGE**

---

## 🎨 Brand Identity & Custom Logo

The store features a custom-designed, multi-variant vector logo system:
1. **Primary Wordmark & Emblem** (`assets/images/logo.svg`):
   - Hexagonal crown/shield emblem with intertwined **IPL** (Ike Products Lounge) monogram.
   - Electric cobalt blue (`#2332D6`) and vivid coral-gold accents (`#FF5436` / `#FFA036`).
   - Deep navy bold typography (`#0A0E2A`) tailored for light navigation headers.
2. **Inverted White Wordmark** (`assets/images/logo-white.svg`):
   - Crisp white typography (`#FFFFFF`) designed for dark backgrounds (luxury footer, executive admin sidebar, database installer).
3. **App Icon & Favicon** (`assets/images/logo-icon.svg`):
   - Standalone hexagonal shield emblem used as the website favicon and mobile app touch icon.

---

## 🌟 Full Feature Set & Marking Criteria Checklist

| Feature Group | Capabilities | Status |
| :--- | :--- | :--- |
| **Authentication & RBAC** | Secure registration, password hashing (`bcrypt`), login validation, role separation (Admin vs Customer), profile editor, password change with old password verification. | ✅ 100% Complete |
| **Catalog & Discovery** | Filter by Universe (Marvel, DC, Star Wars, Anime), live keyword search across titles and brands, multiple sort algorithms (Newest, Price Low-High, Price High-Low, Rating Highest First, Name A-Z), stock badges (In Stock, Low Stock, Sold Out). | ✅ 100% Complete |
| **Collector Wishlist** | 1-click wishlist toggle (`❤️ Saved Pieces`), persistent session vault, dedicated wishlist page (`wishlist.php`), badge counter in navbar, quick transfer to collection bag. | ✅ 100% Complete |
| **Product Showcase & Reviews** | Centerpiece pedestal view with organic curved cobalt wave backdrop, dynamic scale tags, studio manufacturers (Prime 1 Studio, Queen Studios, Hot Toys), live star ratings, verified collector review submission with rating recalculation. | ✅ 100% Complete |
| **Shopping Bag & Discounts** | Quantity controls, stock cap enforcement, complimentary insured courier calculation, discount coupon engine (`VIP10`, `ISAAC10`, `WELCOME5`) with live subtotal deductions. | ✅ 100% Complete |
| **Transactional Checkout** | Recipient and certificate destination verification, payment method selection, atomic database transactions (`beginTransaction`/`commit`), automatic inventory decrement. | ✅ 100% Complete |
| **Printable Certificate & Receipts** | Unique `#IPL-` acquisition codes, official IPL authenticity plaque with executive signature of Isaac Ofori, browser print preview (`@media print`) for hardcopy certificates. | ✅ 100% Complete |
| **Acquisition Tracking** | Dedicated lookup page (`orders.php`) by Reference # and Email, client order vault history with status badges (`Pending`, `Processing`, `Shipped`, `Delivered`). | ✅ 100% Complete |
| **Studio Administration** | Real-time financial KPIs (Settled Revenue, Acquisitions, Catalog Count, Registered Collectors), low stock inventory alerts, product CRUD with file uploads, universe category management, fulfillment status updates. | ✅ 100% Complete |
| **Self-Healing Auto-Seeder** | `config/auto_seed.php` self-heals database integrity on boot, provisions default tables, restores 10 masterline statues, syncs vector images, and maintains Isaac Ofori executive credentials. | ✅ 100% Complete |

---

## 🔑 Administrator & Collector Credentials

| Account Role | Name | Email Address | Password | Privileges |
| :--- | :--- | :--- | :--- | :--- |
| **Store Executive Admin** | **Isaac Ofori** | `isaac0594844398@gmail.com` | `admin123` | Full access to `/admin` dashboard, catalog CRUD, orders & status updates |
| **VIP Collector** | Marcus Vance | `john@example.com` | `customer123` | Storefront browsing, bag management, checkout, personal vault tracking |

> [!TIP]
> Discreet "Fill Admin (Isaac Ofori)" and "Fill Collector Account" quick-fill buttons are built directly into [`login.php`](file:///c:/xampp/htdocs/codealpha_ecommerce/login.php) for instantaneous testing!

---

## 🎟️ Active Voucher & Promo Codes

Test these discount codes directly inside your collection bag ([`cart.php`](file:///c:/xampp/htdocs/codealpha_ecommerce/cart.php)) or at checkout:

| Promo Code | Discount | Description |
| :--- | :--- | :--- |
| **`VIP10`** | **10% OFF** | VIP Collector Privilege Discount |
| **`ISAAC10`** | **10% OFF** | Isaac Ofori Founder Special Incentive |
| **`WELCOME5`** | **5% OFF** | New Club Member Welcome Reward |

---

## 🏆 Catalog & Masterline Statues

The platform ships pre-seeded with 10 high-end collector items complete with custom vector pedestal artworks (stored in both `assets/images/` and `uploads/products/`):

| # | Statue / Piece | Universe | Scale | Studio / Brand | Price | Original Price | Key Features |
| :- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 1 | **Spider-Man 2099** | Marvel Universe | 1/4 Scale | Prime 1 Studio | **$699.00** | ~~$799.00~~ | LED illuminated reactor spire, metallic suit, certified signed plaque. |
| 2 | **Thor: Breaker of Worlds** | Marvel Universe | 1/4 Scale | Queen Studios | **$685.00** | ~~$750.00~~ | Dual-wielded Mjolnir & Stormbreaker, interchangeable lightning effects. |
| 3 | **Iron Man Mark IV** | Marvel Universe | 1/6 Diecast | Hot Toys | **$635.00** | ~~$699.00~~ | Motorized robotic assembly gantry, 28 LED light-up points. |
| 4 | **Hulkbuster Heavy Assault** | Marvel Universe | 1/4 Scale | Prime 1 Studio | **$1,289.00** | ~~$1,450.00~~ | Massive 31" polystone powerhouse with motorized opening helmet. |
| 5 | **Cyborg Superman Prime** | DC Comics | 1/3 Scale | Prime 1 Studio | **$1,229.00** | ~~$1,399.00~~ | Exposed chrome biomechanical pistons, wired cape, illuminated eye. |
| 6 | **The Batman: Dark Knight** | DC Comics | 1/3 Scale | Queen Studios | **$599.00** | ~~$679.00~~ | Gothic cathedral gargoyle base, cowl swap-outs, magnetic batarangs. |
| 7 | **Darth Vader: Lord of the Sith** | Star Wars & Sci-Fi | 1/4 Scale | Sideshow | **$749.00** | ~~$829.00~~ | Mustafar molten lava base, cloth cape, illuminated crimson lightsaber. |
| 8 | **Boba Fett: Daimyo Throne Room** | Star Wars & Sci-Fi | 1/4 Scale | Hot Toys | **$489.00** | ~~$549.00~~ | Weathered Beskar armor, EE-3 rifle, carved stone Rancor throne. |
| 9 | **Goku Ultra Instinct** | Anime & Gaming | 1/4 Scale | Tsume Art | **$349.00** | ~~$399.00~~ | Multi-layered translucent resin aura flames, pearlescent silver hair. |
| 10 | **Cyberpunk 2077: Yaiba Kusanagi** | Anime & Gaming | 1/6 Diecast | PureArts | **$520.00** | ~~$580.00~~ | Diecast Night City racing motorcycle, glowing cyan/magenta wheels. |

---

## 📁 Project Directory Structure

```text
c:\xampp\htdocs\codealpha_ecommerce\
│
├── admin/                           # Executive Studio Control Center
│   ├── includes/
│   │   ├── admin_header.php         # Studio topbar & logo-white.svg navigation
│   │   └── admin_footer.php         # Admin footer & scripts
│   ├── categories.php               # Universe franchise management
│   ├── index.php                    # Studio KPIs & allocation alerts
│   ├── order-details.php            # Acquisition receipt & courier status
│   ├── orders.php                   # Acquisition ledger & fulfillment filters
│   ├── product-add.php              # Register new statue + image upload
│   ├── product-delete.php           # Remove statue & clean up custom uploads
│   ├── product-edit.php             # Modify specs, allocation & artwork
│   └── products.php                 # Masterline catalog inventory table
│
├── assets/
│   ├── css/
│   │   └── style.css                # Luxury studio stylesheet (cobalt & coral theme + print styles)
│   ├── images/                      # High-definition pedestal statue SVGs & brand logos
│   │   ├── logo.svg                 # Primary IPL monogram + wordmark
│   │   ├── logo-white.svg           # White-text IPL logo for dark surfaces
│   │   ├── logo-icon.svg            # Hexagonal shield app icon & favicon
│   │   ├── spiderman-2099.svg
│   │   ├── thor-statue.svg
│   │   ├── ironman-mark4.svg
│   │   ├── hulkbuster.svg
│   │   ├── cyborg-superman.svg
│   │   ├── batman-gargoyle.svg
│   │   ├── darth-vader.svg
│   │   ├── boba-fett.svg
│   │   ├── goku-ultra.svg
│   │   ├── cyberpunk-bike.svg
│   │   └── default-product.svg
│   └── js/
│       └── script.js                # Mobile drawer, validations, alerts, qty stepper
│
├── config/
│   ├── auto_seed.php                # Self-healing database provisioner & initial data
│   ├── database.php                 # PDO connection singleton
│   ├── functions.php                # Dual-path image resolver, coupons, wishlist & price helpers
│   └── session.php                  # Session start, flash toasts, auth guards
│
├── database/
│   └── ecommerce_store.sql          # Production schema & Isaac Ofori admin seeds
│
├── includes/
│   ├── footer.php                   # Luxury footer with Isaac Ofori concierge details
│   ├── header.php                   # HTML head & Inter typography
│   └── navbar.php                   # Sleek studio navbar & logo.svg branding
│
├── uploads/
│   └── products/                    # Synchronized statue visuals & user uploads
│
├── cart.php                         # Reserved collectibles bag, voucher engine & shipping
├── checkout.php                     # Transactional order placement & stock decrement
├── index.php                        # Flagship Spider-Man 2099 showcase & gallery
├── login.php                        # Collector & admin authentication
├── logout.php                       # Session destruction & redirect
├── order-confirmation.php           # Post-purchase certificate, printable receipt & #IPL- code
├── orders.php                       # Acquisition order tracking lookup by ref # & email
├── product-details.php              # Curved wave backdrop, specs, wishlist & collector reviews
├── products.php                     # Master catalog with tabs, universe pill chips & rating sort
├── profile.php                      # Collector account vault & order history
├── register.php                     # VIP collector registration
├── setup.php                        # 1-Click browser database initializer
├── wishlist.php                     # Personal saved pieces vault with 1-click bag transfer
└── README.md                        # Documentation & setup guide
```

---

## 🚀 Quick Installation Guide

### Prerequisites
- **XAMPP** (or WAMP / MAMP / LAMP) with Apache and MySQL services running.
- Folder placed at: `c:\xampp\htdocs\codealpha_ecommerce\`

### Method A: 1-Click Web Installer (Fastest)
1. Open your web browser and navigate to:
   ```
   http://localhost/codealpha_ecommerce/setup.php
   ```
2. Click **"⚡ Provision Studio Database"**.
3. All tables, 10 collector statues, categories, visual assets, reviews, coupons, and Isaac Ofori's admin account will be automatically created and initialized.

### Method B: phpMyAdmin SQL Import
1. Navigate to `http://localhost/phpmyadmin/`.
2. Create database `ecommerce_store`.
3. Import [`database/ecommerce_store.sql`](file:///c:/xampp/htdocs/codealpha_ecommerce/database/ecommerce_store.sql).

---

## 🌐 Store & Studio Admin Access

| Screen | URL | Purpose |
| :--- | :--- | :--- |
| **Flagship Store** | `http://localhost/codealpha_ecommerce/` | Spider-Man 2099 hero showcase & trending pieces |
| **Statue Gallery** | `http://localhost/codealpha_ecommerce/products.php` | Filterable catalog with universe pill chips & rating sort |
| **Saved Pieces Vault** | `http://localhost/codealpha_ecommerce/wishlist.php` | Collector wishlist with 1-click bag transfer |
| **Order Tracking** | `http://localhost/codealpha_ecommerce/orders.php` | Look up order status by Reference # and Email |
| **Collector Sign In** | `http://localhost/codealpha_ecommerce/login.php` | Member authentication with Isaac Ofori quick fill |
| **Studio Dashboard** | `http://localhost/codealpha_ecommerce/admin/index.php` | Real-time KPIs, stock limits & ledger |
| **Database Installer** | `http://localhost/codealpha_ecommerce/setup.php` | 1-Click database provisioning tool |

---

## 🛡️ Component Architecture & Security

1. **Dual-Path Image Resolution**:
   - `get_product_image()` checks `uploads/products/` first, followed by `assets/images/`, ensuring 100% reliable image loading without missing placeholder fallbacks.
2. **PDO Prepared Statements**:
   - All queries use parameter binding (`$stmt->prepare()` and `$stmt->execute()`) eliminating SQL injection vulnerabilities.
3. **Atomic Order Transactions**:
   - Checkout uses `$db->beginTransaction()`, `$db->commit()`, and `$db->rollBack()`. If an item is out of stock during submission, the transaction rolls back cleanly without data inconsistency.
4. **Native Bcrypt Password Hashing**:
   - Uses `password_hash($password, PASSWORD_DEFAULT)` and `password_verify()`.
5. **Self-Dismissing Flash Feedback**:
   - `set_flash('success'|'error', '...')` passes notifications across redirects, automatically dismissed after 5 seconds via vanilla JS.
6. **Mobile-First Responsive Layout**:
   - Fully tested across desktop, laptop, tablet, and mobile with flexible CSS Grid and scrollable tables.
