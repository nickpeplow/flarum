<?php
/**
 * Database Connection Test
 * 
 * This script attempts to connect to the database using the credentials
 * from the config.php file and verifies that the connection works.
 */

// Load the configuration from the config file
$config = require __DIR__ . '/../config.php';
$dbConfig = $config['database'];

echo "Database connection details:\n";
echo "- Database: {$dbConfig['database']}\n";
echo "- Host: {$dbConfig['host']}\n";
echo "- Port: {$dbConfig['port']}\n";
echo "- Username: {$dbConfig['username']}\n";
echo "- Driver: {$dbConfig['driver']}\n\n";

// Try different connection methods
$connected = false;
$successfulMethod = '';
$successfulHost = '';
$successfulPort = 0;

// 1. Standard TCP connection
echo "1. Attempting to connect via TCP to {$dbConfig['host']}:{$dbConfig['port']}...\n";
try {
    $dsn = "{$dbConfig['driver']}:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO(
        $dsn, 
        $dbConfig['username'], 
        $dbConfig['password'], 
        $options
    );
    
    testConnection($pdo);
    $connected = true;
    $successfulMethod = 'TCP';
    $successfulHost = $dbConfig['host'];
    $successfulPort = $dbConfig['port'];
    
} catch (PDOException $e) {
    echo "   ❌ Failed: " . $e->getMessage() . "\n\n";
}

// 2. Try connecting to 127.0.0.1 instead of localhost
if (!$connected) {
    echo "2. Attempting to connect via TCP to 127.0.0.1:{$dbConfig['port']}...\n";
    try {
        $dsn = "{$dbConfig['driver']}:host=127.0.0.1;port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
        
        $pdo = new PDO(
            $dsn, 
            $dbConfig['username'], 
            $dbConfig['password'], 
            $options
        );
        
        testConnection($pdo);
        $connected = true;
        $successfulMethod = 'TCP';
        $successfulHost = '127.0.0.1';
        $successfulPort = $dbConfig['port'];
        
    } catch (PDOException $e) {
        echo "   ❌ Failed: " . $e->getMessage() . "\n\n";
    }
}

// 3. Try socket connection (common on macOS)
if (!$connected) {
    echo "3. Attempting to connect via socket...\n";
    
    // Common socket paths for MAMP and macOS MySQL
    $socketPaths = [
        '/Applications/MAMP/tmp/mysql/mysql.sock',
        '/tmp/mysql.sock',
        '/var/mysql/mysql.sock',
        '/var/run/mysqld/mysqld.sock'
    ];
    
    foreach ($socketPaths as $socket) {
        if (file_exists($socket)) {
            echo "   Found socket: {$socket}\n";
            try {
                $dsn = "{$dbConfig['driver']}:unix_socket={$socket};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
                $pdo = new PDO(
                    $dsn,
                    $dbConfig['username'],
                    $dbConfig['password'],
                    $options
                );
                
                echo "   Connected using socket: {$socket}\n";
                testConnection($pdo);
                $connected = true;
                $successfulMethod = 'Socket';
                $successfulHost = $socket;
                break;
            } catch (PDOException $socketError) {
                echo "   ❌ Failed: " . $socketError->getMessage() . "\n";
            }
        }
    }
}

// If all connection attempts failed, provide troubleshooting information
if (!$connected) {
    echo "\n⚠️ All connection attempts failed. Please check:\n";
    echo "1. MySQL server is running\n";
    echo "2. Credentials in config.php are correct\n";
    echo "3. Database '{$dbConfig['database']}' exists\n";
    echo "4. User '{$dbConfig['username']}' has permission to access the database\n";
    echo "5. Firewall settings\n\n";
    
    echo "For MAMP users:\n";
    echo "- Make sure MAMP is running\n";
    echo "- Check if the MySQL port matches (default is 8889, not 3306)\n";
    echo "- Try setting host to '127.0.0.1' and port to 8889 in config.php\n";
    
    exit(1);
}

/**
 * Test the database connection by running some simple queries
 * 
 * @param PDO $pdo The PDO connection object
 */
function testConnection($pdo) {
    global $successfulMethod, $successfulHost, $successfulPort, $dbConfig;
    
    // Test the connection with a simple query
    $stmt = $pdo->query("SELECT NOW() as time");
    $result = $stmt->fetch();
    
    echo "✅ Connection successful!\n";
    echo "Current database time: " . $result['time'] . "\n";
    
    // Check server info
    $serverInfo = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
    echo "Server info: " . $serverInfo . "\n\n";
    
    // List tables in the database
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "Database tables found: " . count($tables) . "\n";
        echo "Tables:\n";
        foreach ($tables as $table) {
            echo "- $table\n";
        }
    } else {
        echo "No tables found in the database.\n";
    }
    
    // Provide config update suggestion if needed
    if ($successfulMethod === 'TCP' && $successfulHost !== $dbConfig['host']) {
        echo "\n📝 Suggestion: Update your config.php file to use host '{$successfulHost}' instead of '{$dbConfig['host']}'.\n";
        echo "   Sample code for config.php:\n\n";
        echo "  'database' => [\n";
        echo "    'driver' => '{$dbConfig['driver']}',\n";
        echo "    'host' => '{$successfulHost}',\n";
        echo "    'port' => {$successfulPort},\n";
        echo "    'database' => '{$dbConfig['database']}',\n";
        echo "    'username' => '{$dbConfig['username']}',\n";
        echo "    'password' => '{$dbConfig['password']}',\n";
        echo "    // ... other settings\n";
        echo "  ],\n";
    } elseif ($successfulMethod === 'Socket') {
        echo "\n📝 Note: You successfully connected using Unix socket: {$successfulHost}\n";
        echo "   If you prefer to explicitly use this socket in your config.php, use PDO_MYSQL_ATTR_SOCKET option.\n";
    }
    
    exit(0);
} 