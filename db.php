<?php

$host = getenv("DB_HOST");
$db   = getenv("DB_NAME");
$user = getenv("DB_USER");
$pass = getenv("DB_PASS");
$port = "3306";

try {
    $dsn = "mysql:host=$host;dbname=$db;port=$port;charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    // Azure SSL
    if (strpos($host, 'mysql.database.azure.com') !== false) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = '/var/www/html/DigiCertGlobalRootCA.crt.pem';
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }

    $pdo = new PDO($dsn, $user, $pass, $options);

    // Create the table (this is allowed)
    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}