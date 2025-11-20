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
    $dbname = isset($azureConfig['Database']) ? $azureConfig['Database'] : 'student_management';
    $user = $azureConfig['User Id'];
    $pass = $azureConfig['Password'];
    $port = '3306'; // Default MySQL port
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
    $port = '3306';
}

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT => false,
    ];
    
    // Force TCP connection by specifying port
    $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
    
    // For Azure MySQL with SSL
    if (strpos($host, 'mysql.database.azure.com') !== false) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt'; // Azure Linux path
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    
    // First, try to connect to MySQL server
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Check if our target database exists, if not create it
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    $databaseExists = $stmt->fetch();
    
    if (!$databaseExists) {
        $pdo->exec("CREATE DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        error_log("Database '$dbname' created successfully");
    }
    
    // Now switch to the specific database
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
        // Try direct connection with database name
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, $options);
        
        // Create table if it doesn't exist
        $pdo->exec("CREATE TABLE IF NOT EXISTS students (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
    } catch (Exception $e2) {
        // Final fallback - try without SSL for testing
        try {
            $options_no_ssl = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, $options_no_ssl);
            
            // Create table if it doesn't exist
            $pdo->exec("CREATE TABLE IF NOT EXISTS students (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
        } catch (Exception $e3) {
            error_log("Database connection failed: " . $e3->getMessage());
            
            // Provide detailed error information
            echo "<h3>Database Connection Error</h3>";
            echo "<p><strong>Error:</strong> " . $e3->getMessage() . "</p>";
            echo "<p><strong>Host:</strong> " . $host . "</p>";
            echo "<p><strong>Database:</strong> " . $dbname . "</p>";
            echo "<p><strong>User:</strong> " . $user . "</p>";
            echo "<p>Please check your Azure MySQL configuration and ensure the server is running.</p>";
            exit;
        }
    }
}

// Connection successful
// echo "Database connected successfully!"; // Remove this line after testing
?>