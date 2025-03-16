<?php
/**
 * Database Connection Test
 * 
 * This file tests the database connection directly using the config.php file
 */

// Start with a clean output
ob_clean();

// Set error reporting for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Connection Test</h1>";

echo "<h2>1. Testing Config File</h2>";

// Path to the config file
$rootConfig = dirname(dirname(__DIR__)) . '/config.php';
$localConfig = __DIR__ . '/config.php';

echo "<p>Checking for config file at: <code>" . htmlspecialchars($rootConfig) . "</code></p>";
if (file_exists($rootConfig)) {
    echo "<div style='background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "✅ Root config file exists!";
    echo "</div>";
    $configPath = $rootConfig;
} else {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "❌ Root config file not found.";
    echo "</div>";
    
    echo "<p>Checking for local config file at: <code>" . htmlspecialchars($localConfig) . "</code></p>";
    if (file_exists($localConfig)) {
        echo "<div style='background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "✅ Local config file exists!";
        echo "</div>";
        $configPath = $localConfig;
    } else {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "❌ Local config file not found.";
        echo "</div>";
        $configPath = null;
    }
}

echo "<h2>2. Loading Configuration</h2>";

if ($configPath) {
    try {
        echo "<p>Loading config from: <code>" . htmlspecialchars($configPath) . "</code></p>";
        $config = require $configPath;
        echo "<pre>";
        echo "Config structure: \n";
        print_r(array_keys($config));
        echo "</pre>";
        
        if (isset($config['database'])) {
            echo "<div style='background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
            echo "✅ Database configuration found!";
            echo "</div>";
            
            $dbConfig = $config['database'];
            echo "<pre>";
            echo "Database config (sanitized): \n";
            $sanitizedConfig = $dbConfig;
            if (isset($sanitizedConfig['password'])) {
                $sanitizedConfig['password'] = !empty($sanitizedConfig['password']) ? "[REDACTED]" : "EMPTY";
            }
            print_r($sanitizedConfig);
            echo "</pre>";
        } else {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
            echo "❌ Database configuration not found in config file.";
            echo "</div>";
            $dbConfig = null;
        }
    } catch (Exception $e) {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "❌ Error loading config file: " . htmlspecialchars($e->getMessage());
        echo "</div>";
        $dbConfig = null;
    }
} else {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "❌ No config file path available.";
    echo "</div>";
    $dbConfig = null;
}

echo "<h2>3. Testing Database Connection</h2>";

if ($dbConfig) {
    try {
        // Extract database configuration
        $driver = $dbConfig['driver'] ?? 'mysql';
        $host = $dbConfig['host'] ?? 'localhost';
        $dbname = $dbConfig['database'] ?? '';
        $username = $dbConfig['username'] ?? 'root';
        $password = $dbConfig['password'] ?? '';
        
        echo "<p>Connection details:</p>";
        echo "<ul>";
        echo "<li>Driver: $driver</li>";
        echo "<li>Host: $host</li>";
        echo "<li>Database: $dbname</li>";
        echo "<li>Username: $username</li>";
        echo "<li>Password: " . (!empty($password) ? "[REDACTED]" : "EMPTY") . "</li>";
        echo "</ul>";
        
        if (empty($dbname)) {
            throw new Exception("Database name is empty");
        }
        
        echo "<p>Attempting to connect to the database...</p>";
        
        $dsn = "$driver:host=$host;dbname=$dbname;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, $username, $password, $options);
        
        echo "<div style='background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "✅ Database connection successful!";
        echo "</div>";
        
        // Test a simple query
        $stmt = $pdo->query("SELECT VERSION() as version");
        $result = $stmt->fetch();
        
        echo "<p>MySQL Version: <strong>" . htmlspecialchars($result['version']) . "</strong></p>";
        
        // Check the keywords table
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'keywords'");
            $tableExists = $stmt->fetch();
            
            if ($tableExists) {
                echo "<div style='background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
                echo "✅ 'keywords' table exists!";
                echo "</div>";
                
                // Count keywords
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM keywords");
                $result = $stmt->fetch();
                echo "<p>Keywords count: <strong>" . $result['count'] . "</strong></p>";
                
                // Show some sample keywords if available
                $stmt = $pdo->query("SELECT * FROM keywords LIMIT 5");
                $keywords = $stmt->fetchAll();
                
                if (!empty($keywords)) {
                    echo "<p>Sample keywords:</p>";
                    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
                    echo "<tr>";
                    foreach (array_keys($keywords[0]) as $column) {
                        echo "<th>" . htmlspecialchars($column) . "</th>";
                    }
                    echo "</tr>";
                    
                    foreach ($keywords as $keyword) {
                        echo "<tr>";
                        foreach ($keyword as $value) {
                            echo "<td>" . htmlspecialchars($value) . "</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No keywords found in the table.</p>";
                }
            } else {
                echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
                echo "❌ 'keywords' table does not exist!";
                echo "</div>";
            }
        } catch (Exception $e) {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
            echo "❌ Error checking keywords table: " . htmlspecialchars($e->getMessage());
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "❌ Database connection failed: " . htmlspecialchars($e->getMessage());
        echo "</div>";
    }
} else {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "❌ No database configuration available to test connection.";
    echo "</div>";
}

echo "<h2>4. Global Connection Status</h2>";

// Check if there's already a global PDO connection
echo "<p>Checking global \$pdo variable:</p>";
if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
    echo "<div style='background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "✅ Global PDO connection exists!";
    echo "</div>";
    
    try {
        // Test the connection
        $stmt = $GLOBALS['pdo']->query("SELECT 1");
        $result = $stmt->fetch();
        
        echo "<div style='background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "✅ Global connection is active!";
        echo "</div>";
    } catch (Exception $e) {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "❌ Global connection exists but is not working: " . htmlspecialchars($e->getMessage());
        echo "</div>";
    }
} else {
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "❌ No global PDO connection found.";
    echo "</div>";
}

echo "<h2>Summary</h2>";
echo "<p>If all items above have green checkmarks, your database connection is working correctly.</p>";
echo "<p>If you see any red error messages, those need to be fixed before the application will work properly.</p>";
echo "<p><a href='./'>Return to Dashboard</a></p>"; 