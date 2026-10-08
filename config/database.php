<?php
/**
 * Database Connection
 * Uses PDO for secure, prepared-statement-ready queries
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Default XAMPP password is empty
define('DB_NAME', 'ecommerce_store');

require_once __DIR__ . '/auto_seed.php';

function getDB() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

            // Auto-verify catalog & Isaac Ofori executive profile
            ensure_database_integrity($pdo);

        } catch (PDOException $e) {
            // Show a friendly error instead of raw DB details
            die('<div style="font-family:sans-serif;padding:2rem;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;border-radius:8px;max-width:500px;margin:2rem auto;"><h2>Database Connection Error</h2><p>Could not connect to the database. Please check your database settings in <strong>config/database.php</strong>.</p><p><em>Error: ' . htmlspecialchars($e->getMessage()) . '</em></p></div>');
        }
    }

    return $pdo;
}
