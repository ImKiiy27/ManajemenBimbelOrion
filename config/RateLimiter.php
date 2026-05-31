<?php
// security/rate_limiter.php - Anti-bot protection
// ================================================

class RateLimiter {
    private const STORAGE_FILE = __DIR__ . '/../storage/rate_limit.json';
    private const DEFAULT_WINDOW = 60;  // 1 menit
    private const DEFAULT_LIMIT = 100;  // requests per IP

    public static function check(string $key = 'global'): bool {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $identifier = $ip . '_' . $key;
        $window = $_ENV['RATE_LIMIT_WINDOW'] ?? self::DEFAULT_WINDOW;
        $limit = $_ENV['RATE_LIMIT_REQUESTS'] ?? self::DEFAULT_LIMIT;

        $data = self::loadData();
        
        $now = time();
        $requests = $data[$identifier] ?? [];
        
        // Hapus request lama
        $requests = array_filter($requests, fn($timestamp) => $now - $timestamp < $window);
        
        if (count($requests) >= $limit) {
            http_response_code(429);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Rate limit exceeded. Coba lagi nanti.']);
            exit;
        }
        
        $requests[] = $now;
        $data[$identifier] = $requests;
        self::saveData($data);
        
        return true;
    }

    private static function loadData(): array {
        if (!file_exists(self::STORAGE_FILE)) {
            return [];
        }
        $json = file_get_contents(self::STORAGE_FILE);
        return json_decode($json, true) ?: [];
    }

    private static function saveData(array $data): void {
        $dir = dirname(self::STORAGE_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents(self::STORAGE_FILE, json_encode($data));
    }
}

// Gunakan di controllers yang rentan bot:
// RateLimiter::check('login');
// RateLimiter::check('register');

