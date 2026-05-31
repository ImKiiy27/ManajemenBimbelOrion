<?php
// config/app.php - App configuration
// ================================================

return [
    'name' => 'Bimbel Orion',
    'version' => '1.0.0',
    'debug' => $_ENV['DEBUG'] ?? $_SERVER['APP_ENV'] === 'development',
    
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
