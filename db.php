<?php
// Function to parse Azure MySQL connection string
function parseAzureMySQLConnectionString() {
    $connectionString = getenv("AZURE_MYSQL_CONNECTIONSTRING");
    
    if (!$connectionString) {
        return null;
    }
    
    $parts = explode(';', $connectionString);
    $config = [];
    
    foreach ($parts as $part) {
        if (strpos($part, '=') !== false) {
            list($key, $value) = explode('=', $part, 2);
            $config[trim($key)] = trim($value);
        }
    }
    
    return $config;
}

// Try to get configuration from Azure connection string first
$azureConfig = parseAzureMySQLConnectionString();

if ($azureConfig) {
    // Use Azure connection string configuration
    $host = $azureConfig['Server'];
    $dbname = $azureConfig['Database'];
    $user = $azureConfig['User Id'];
    $pass = $azureConfig['Password'];
} else {
    // Fallback to individual environment variables (for local development)
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
    $dbname = getenv("DB_NAME") ?: "student_management";
    $user = getenv("DB_USER") ?: "root";
    $pass = getenv("DB_PASS") ?: "";
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
    
    // Connect to MySQL server first (without specific database)
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, $options);
    
    // Check if our target database exists, if not create it
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    $databaseExists = $stmt->fetch();
    
    if (!$databaseExists) {
        $pdo->exec("CREATE DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        error_log("Database '$dbname' created successfully");
    }
    
    // Now connect to the specific database
    $pdo->exec("USE `$dbname`");
    
    // Create table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
} catch (Exception $e) {
    // If the first approach fails, try connecting directly to the database
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, $options);
        
        // Create table if it doesn't exist
        $pdo->exec("CREATE TABLE IF NOT EXISTS students (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
    } catch (Exception $e2) {
        error_log("Database connection failed: " . $e2->getMessage());
        
        $error_msg = "Database connection failed. ";
        
        if (strpos($e2->getMessage(), 'Access denied') !== false) {
            $error_msg .= "Please check your username and password.";
        } elseif (strpos($e2->getMessage(), 'Unknown database') !== false) {
            $error_msg .= "Database '$dbname' doesn't exist.";
        } else {
            $error_msg .= "Error: " . $e2->getMessage();
        }
        
        die($error_msg);
    }
}

?>