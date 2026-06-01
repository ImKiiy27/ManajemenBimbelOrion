<?php
// config/app.php - App configuration
// ================================================

$appEnv = (string)($_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'production');
$debugRaw = $_ENV['DEBUG'] ?? $_SERVER['DEBUG'] ?? null;
$isDebug = $debugRaw !== null
    ? filter_var($debugRaw, FILTER_VALIDATE_BOOLEAN)
    : ($appEnv === 'development');

return [
    'name' => 'Bimbel Orion',
    'version' => '1.0.0',
    'debug' => $isDebug,
    
    'security' => [
        'max_login_attempts' => 5,
        'login_lockout_minutes' => 15,
        'session_lifetime' => 3600, // 1 jam
        'csrf_enabled' => true,
    ],
    
    'features' => [
        'absensi_audit' => true,
        'dark_mode' => true,
        'maintenance_mode' => false,
    ],
    
    'paths' => [
        'views' => __DIR__ . '/../views',
        'public' => __DIR__ . '/../public',
    ],
];
