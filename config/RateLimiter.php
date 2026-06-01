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
        $window = (int)($_ENV['RATE_LIMIT_WINDOW'] ?? self::DEFAULT_WINDOW);
        $limit = (int)($_ENV['RATE_LIMIT_REQUESTS'] ?? self::DEFAULT_LIMIT);

        $data = self::loadData();
        
        $now = time();
        $requests = $data[$identifier] ?? [];
        
        // Hapus request lama
        $requests = array_filter($requests, fn($timestamp) => $now - $timestamp < $window);
        
        if (count($requests) >= $limit) {
            self::respondRateLimited($window, $key);
            return false;
        }
        
        $requests[] = $now;
        $data[$identifier] = $requests;
        self::saveData($data);
        
        return true;
    }

    private static function respondRateLimited(int $window, string $key): void {
        if (self::wantsJson()) {
            http_response_code(429);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status' => 'error',
                'message' => 'Terlalu banyak permintaan. Coba lagi beberapa saat lagi.',
                'data' => (object)[
                    'retry_after_seconds' => $window,
                    'scope' => $key
                ]
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        http_response_code(429);
        header('Content-Type: text/html; charset=utf-8');
        echo '<!doctype html><html lang="id"><head><meta charset="utf-8"><title>Terlalu Banyak Permintaan</title></head><body style="font-family: Arial, sans-serif; padding:24px;">'
            . '<h2>Terlalu banyak permintaan</h2>'
            . '<p>Mohon tunggu beberapa saat sebelum mencoba lagi.</p>'
            . '<p><a href="javascript:history.back()">Kembali</a></p>'
            . '</body></html>';
        exit;
    }

    private static function wantsJson(): bool {
        $accept = strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? ''));
        $requestedWith = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
        return isset($_GET['action'])
            || str_contains($accept, 'application/json')
            || $requestedWith === 'xmlhttprequest';
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

