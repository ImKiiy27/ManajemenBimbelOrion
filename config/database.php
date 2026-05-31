<?php
// ================================================
// config/database.php
// Koneksi database menggunakan PDO
// ================================================

// Load from .env
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'bimbelku');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_CHAR', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHAR;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        // Samakan collation koneksi dengan tabel (pakai utf8mb4_general_ci sesuai schema)
        $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_general_ci");
    } catch (PDOException $e) {
        // Di production, jangan tampilkan detail error ke user
        error_log("DB Error: " . $e->getMessage());
        die(json_encode(['error' => 'Koneksi database gagal.']));
    }

    return $pdo;
}
