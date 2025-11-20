<?php
function loadLocalEnv() {
    $envPath = __DIR__ . '/.env';
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                // Remove quotes if present
                if (preg_match('/^["\'](.*)["\']$/', $value, $matches)) {
                    $value = $matches[1];
                }
                putenv("$key=$value");
            }
        }
    }
}

loadLocalEnv();

$host = getenv("DB_HOST") ?: "localhost";
$db   = getenv("DB_NAME") ?: "student_management";
$user = getenv("DB_USER") ?: "root";
$pass = getenv("DB_PASS") ?: "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (Exception $e) {
    if (getenv("DB_HOST") === false) {
        die("Database connection failed. Please check your local MySQL setup and .env file.");
    } else {
        die("Database connection failed: " . $e->getMessage());
    }
}
?>