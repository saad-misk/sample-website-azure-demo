<?php
$connectionString = getenv("AZURE_MYSQL_CONNECTIONSTRING");

if ($connectionString) {
    $parts = explode(';', $connectionString);
    $config = [];
    foreach ($parts as $part) {
        if (strpos($part, '=') !== false) {
            list($key, $value) = explode('=', $part, 2);
            $config[trim($key)] = trim($value);
        }
    }
    
    $host = $config['Server'];
    $dbname = $config['Database'];
    $user = $config['User Id'];
    $pass = $config['Password'];
    
    try {
        $pdo = new PDO("mysql:host=$host;port=3306;dbname=$dbname;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create table if needed
        $pdo->exec("CREATE TABLE IF NOT EXISTS students (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
    } catch (Exception $e) {
        die("Connection failed: " . $e->getMessage());
    }
} else {
    die("No database configuration found.");
}
?>