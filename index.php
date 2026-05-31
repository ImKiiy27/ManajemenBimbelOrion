<?php
// ============================================================
// index.php â€” Entry point utama Bimbel Orion
// Semua request masuk lewat sini
// ============================================================

// Load .env tanpa Composer (jika file ada)
require_once __DIR__ . '/config/env.php';
loadEnvFile(__DIR__ . '/.env');

// Load session config dulu sebelum apapun
require_once __DIR__ . '/config/session.php';
initSession();

// Load config & core
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/Router.php';

// Rate limiting untuk semua requests (kecuali assets public)
$requestPath = (string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
$isPublicAssetRequest = preg_match('#^/public/(css|js|svg|images)(/|$)#i', $requestPath) === 1;

if (!$isPublicAssetRequest) {
    require_once __DIR__ . '/config/RateLimiter.php';
    RateLimiter::check();
}


// Ambil halaman dari query string: ?page=login
// Default: index (landing page)
$page = $_GET['page'] ?? 'index';

// Sanitasi input
$page = preg_replace('/[^a-zA-Z0-9_\-]/', '', $page);

// Routing
$router = new Router();
$router->dispatch($page);
