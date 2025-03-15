<?php
/**
 * Database Connection Test
 * 
 * This script tests the database connection and displays the results.
 */

// Load the configuration from the config file
$config = require __DIR__ . '/../config.php';
$dbConfig = $config['database'];

// Basic styling
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Connection Test</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='bg-light'>
    <div class='container py-5'>
        <div class='card'>
            <div class='card-header bg-primary text-white'>
                <h2>Database Connection Test</h2>
            </div>
            <div class='card-body'>";

echo "<h3>Connection Details</h3>";
echo "<ul class='list-group mb-4'>";
echo "<li class='list-group-item'><strong>Database:</strong> {$dbConfig['database']}</li>";
echo "<li class='list-group-item'><strong>Host:</strong> {$dbConfig['host']}</li>";
echo "<li class='list-group-item'><strong>Driver:</strong> {$dbConfig['driver']}</li>";
echo "</ul>";

// Try different connection methods
echo "<h3>Connection Attempts</h3>";

// 1. Standard TCP connection (original config)
echo "<div class='alert alert-info'>1. Attempting connection with original config...</div>";
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
    
    echo "<div class='alert alert-success'>✅ Connection successful with original config!</div>";
    displayDatabaseInfo($pdo);
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>❌ Failed: " . $e->getMessage() . "</div>";
    
    // 2. Try connecting to 127.0.0.1 instead of localhost
    echo "<div class='alert alert-info'>2. Attempting connection with 127.0.0.1...</div>";
    try {
        $dsn = "{$dbConfig['driver']}:host=127.0.0.1;port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
        
        $pdo = new PDO(
            $dsn, 
            $dbConfig['username'], 
            $dbConfig['password'], 
            $options
        );
        
        echo "<div class='alert alert-success'>✅ Connection successful with 127.0.0.1!</div>";
        displayDatabaseInfo($pdo);
        
    } catch (PDOException $e2) {
        echo "<div class='alert alert-danger'>❌ Failed: " . $e2->getMessage() . "</div>";
        
        echo "<div class='alert alert-warning'><strong>All connection attempts failed</strong><br>
        Please check your database server is running and credentials are correct.</div>";
    }
}

// Display database information
function displayDatabaseInfo($pdo) {
    // Display MySQL version
    $stmt = $pdo->query("SELECT VERSION() as version");
    $result = $stmt->fetch();
    echo "<div class='card mb-3'>";
    echo "<div class='card-header bg-secondary text-white'>Database Info</div>";
    echo "<div class='card-body'>";
    echo "<p><strong>MySQL Version:</strong> " . $result['version'] . "</p>";
    
    // Display tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<strong>Tables (" . count($tables) . "):</strong>";
    echo "<ul class='list-group'>";
    foreach ($tables as $table) {
        echo "<li class='list-group-item'>" . $table . "</li>";
    }
    echo "</ul>";
    echo "</div></div>";
}

echo "<div class='mt-4'>
    <a href='automate/' class='btn btn-primary'>Go to Keywords Dashboard</a>
</div>";

echo "</div></div></div></body></html>";