<?php
// =============================================
// Database Connection — SQLite PDO
// =============================================

define('DB_PATH', __DIR__ . '/../database/shop.db');
define('SCHEMA_PATH', __DIR__ . '/../database/schema.sql');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO('sqlite:' . DB_PATH);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->exec("PRAGMA journal_mode=WAL");
            $pdo->exec("PRAGMA foreign_keys=ON");

            // Initialize DB if new
            $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='site_settings'")->fetch();
            if (!$tables) {
                $schema = file_get_contents(SCHEMA_PATH);
                $pdo->exec($schema);
            }
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}
