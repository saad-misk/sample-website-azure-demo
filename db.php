<?php
// Get Azure MySQL connection string
$connectionString = getenv("AZURE_MYSQL_CONNECTIONSTRING");

if ($connectionString) {
    // Parse connection string
    $parts = explode(';', $connectionString);
    $config = [];
    
    foreach ($parts as $part) {
        if (strpos($part, '=') !== false) {
            list($key, $value) = explode('=', $part, 2);
            $config[trim($key)] = trim($value);
        }
    }
    
    $host = $config['Server'];
    $dbname = $config['Database'] ?: 'student_management'; // Fallback if not specified
    $user = $config['User Id'];
    $pass = $config['Password'];
} else {
    // Local development fallback
    $host = "localhost";
    $dbname = "student_management";
    $user = "root";
    $pass = "";
}

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];
    
    // For Azure MySQL with SSL
    if (strpos($host, 'mysql.database.azure.com') !== false) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/Baltimore_CyberTrust_Root.pem';
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, $options);
    
    // Create table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>