<?php
// ============================================================
// core/env.php
// Loader .env sederhana tanpa Composer
// ============================================================

/**
 * Load variabel environment dari file .env ke $_ENV/$_SERVER/putenv.
 * Tidak menimpa environment variable yang sudah ada.
 */
function loadEnvFile(string $envPath): void
{
    if (!is_file($envPath) || !is_readable($envPath)) {
        return;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        $line = ltrim($line, "\xEF\xBB\xBF");
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (str_starts_with($line, 'export ')) {
            $line = trim(substr($line, 7));
        }

        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        if ($key === '' || preg_match('/^[A-Z0-9_]+$/i', $key) !== 1) {
            continue;
        }

        if (array_key_exists($key, $_ENV)) {
            continue;
        }

        $existingValue = getenv($key);
        if ($existingValue !== false) {
            $_ENV[$key] = (string) $existingValue;
            $_SERVER[$key] = (string) $existingValue;
            continue;
        }

        $value = trim($parts[1]);
        if ($value === '') {
            $_ENV[$key] = '';
            $_SERVER[$key] = '';
            putenv($key . '=');
            continue;
        }

        $firstChar = $value[0];
        $lastChar = $value[strlen($value) - 1];
        $isQuoted = ($firstChar === '"' && $lastChar === '"')
            || ($firstChar === "'" && $lastChar === "'");

        if ($isQuoted) {
            $value = substr($value, 1, -1);
        } else {
            $commentPos = strpos($value, ' #');
            if ($commentPos !== false) {
                $value = rtrim(substr($value, 0, $commentPos));
            }
        }

        $value = str_replace(["\\n", "\\r", "\\t"], ["\n", "\r", "\t"], $value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv($key . '=' . $value);
    }
}
