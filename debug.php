<?php
// Debug script to test Azure MySQL connection
$connectionString = getenv("AZURE_MYSQL_CONNECTIONSTRING");

echo "<h2>Debug Information</h2>";

if ($connectionString) {
    echo "<p><strong>Connection String Found:</strong> " . htmlspecialchars($connectionString) . "</p>";
    
    $parts = explode(';', $connectionString);
    $config = [];
    
    foreach ($parts as $part) {
        if (strpos($part, '=') !== false) {
            list($key, $value) = explode('=', $part, 2);
            $config[trim($key)] = trim($value);
            echo "<p><strong>" . htmlspecialchars(trim($key)) . ":</strong> " . htmlspecialchars(trim($value)) . "</p>";
        }
    }
    
    $host = $config['Server'];
    $dbname = isset($config['Database']) ? $config['Database'] : 'student_management';
    $user = $config['User Id'];
    $pass = '***'; // Don't show password
    
    echo "<p><strong>Parsed Host:</strong> $host</p>";
    echo "<p><strong>Parsed Database:</strong> $dbname</p>";
    echo "<p><strong>Parsed User:</strong> $user</p>";
    
    // Test connection
    try {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ];
        
        // Try with SSL
        $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        
        $dsn = "mysql:host=$host;port=3306;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $config['Password'], $options);
        
        echo "<p style='color: green;'><strong>✅ Connection Successful!</strong></p>";
        
        // Test query
        $stmt = $pdo->query("SELECT 1 as test");
        $result = $stmt->fetch();
        echo "<p>Query test: " . $result['test'] . "</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'><strong>❌ Connection Failed:</strong> " . $e->getMessage() . "</p>";
        
        // Try without SSL
        try {
            $options_no_ssl = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ];
            
            $pdo = new PDO($dsn, $user, $config['Password'], $options_no_ssl);
            echo "<p style='color: green;'><strong>✅ Connection Successful without SSL!</strong></p>";
            
        } catch (Exception $e2) {
            echo "<p style='color: red;'><strong>❌ Connection without SSL also failed:</strong> " . $e2->getMessage() . "</p>";
        }
    }
    
} else {
    echo "<p style='color: red;'><strong>No connection string found in environment variables.</strong></p>";
    echo "<p>Available environment variables:</p>";
    echo "<pre>";
    print_r($_SERVER);
    echo "</pre>";
}
?>