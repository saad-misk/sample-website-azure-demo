<?php
// Function to find database configuration from multiple sources
function getDatabaseConfig() {
    // 1. Check for the actual Azure MySQL connection string name found in debug
    $connectionString = getenv("MYSQLCONNSTR_AZURE_MYSQL_CONNECTIONSTRING");
    if ($connectionString) {
        return parseConnectionString($connectionString);
    }
    
    // 2. Check for other possible connection string names
    $azureConnectionNames = [
        'AZURE_MYSQL_CONNECTIONSTRING',
        'CUSTOMCONNSTR_AZURE_MYSQL_CONNECTIONSTRING', 
        'CUSTOMCONNSTR_MYSQL',
        'SQLAZURECONNSTR_AZURE_MYSQL'
    ];
    
    foreach ($azureConnectionNames as $name) {
        $connectionString = getenv($name);
        if ($connectionString) {
            return parseConnectionString($connectionString);
        }
    }
    
    // 3. Check for individual environment variables
    $host = getenv("DB_HOST");
    $dbname = getenv("DB_NAME");
    $user = getenv("DB_USER");
    $pass = getenv("DB_PASS");
    
    if ($host && $user) {
        return [
            'host' => $host,
            'dbname' => $dbname ?: 'student_management',
            'user' => $user,
            'pass' => $pass ?: '',
            'port' => '3306'
        ];
    }
    
    // 4. Local development fallback
    return [
        'host' => 'localhost',
        'dbname' => 'student_management',
        'user' => 'root',
        'pass' => '',
        'port' => '3306'
    ];
}

function parseConnectionString($connectionString) {
    $parts = explode(';', $connectionString);
    $config = [];
    
    foreach ($parts as $part) {
        if (strpos($part, '=') !== false) {
            list($key, $value) = explode('=', $part, 2);
            $config[trim($key)] = trim($value);
        }
    }
    
    // Map connection string keys to our standard format
    return [
        'host' => $config['Server'],
        'dbname' => $config['Database'] ?: 'student_management', // Use 'mysql' from connection string or fallback
        'user' => $config['User Id'],
        'pass' => $config['Password'],
        'port' => '3306'
    ];
}

// Get database configuration
$dbConfig = getDatabaseConfig();

$host = $dbConfig['host'];
$dbname = $dbConfig['dbname'];
$user = $dbConfig['user'];
$pass = $dbConfig['pass'];
$port = $dbConfig['port'];

// For debugging - remove this after it works
error_log("Connecting to: host=$host, db=$dbname, user=$user");

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    
    // For Azure MySQL with SSL
    if (strpos($host, 'mysql.database.azure.com') !== false) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    
    // First, try to connect to the specific database
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Create table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
} catch (Exception $e) {
    // If connecting to 'mysql' database fails (system database), try creating our database
    if (strpos($e->getMessage(), 'Unknown database') !== false && $dbname === 'mysql') {
        try {
            // Connect without database specified
            $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, $options);
            
            // Create our database
            $pdo->exec("CREATE DATABASE IF NOT EXISTS student_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Switch to our database
            $pdo->exec("USE student_management");
            
            // Create table
            $pdo->exec("CREATE TABLE IF NOT EXISTS students (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
        } catch (Exception $e2) {
            die("Failed to create database: " . $e2->getMessage());
        }
    } else {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Connection successful!
?>