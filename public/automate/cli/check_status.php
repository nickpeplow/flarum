#!/usr/bin/env php
<?php
/**
 * Check status of recently tagged keywords
 */

// Define the application path
define('APP_PATH', __DIR__ . '/..');

// Set up autoloading
spl_autoload_register(function ($className) {
    // Convert namespace separators to directory separators
    $className = str_replace('\\', '/', $className);
    $filePath = APP_PATH . '/' . $className . '.php';
    
    if (file_exists($filePath)) {
        require_once $filePath;
        return true;
    }
    return false;
});

// Initialize bootstrap
require_once APP_PATH . '/Core/Bootstrap.php';
\Core\Bootstrap::init();

// Check if we have a database connection
try {
    global $pdo;
    if (!isset($pdo) || !($pdo instanceof \PDO)) {
        throw new \Exception("No database connection available");
    }
    
    echo "Database connection established!\n\n";
    
    // Query the keywords we want to check
    $stmt = $pdo->query("SELECT id, keyword, tag_id, status FROM keywords WHERE id IN (76, 77, 78, 79)");
    
    // Print header
    echo "ID\tTag ID\tStatus\t\tKeyword\n";
    echo "-----------------------------------------------------\n";
    
    // Fetch and display results
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['id'] . "\t";
        echo $row['tag_id'] . "\t";
        echo ($row['status'] ?: 'NULL') . "\t\t";
        echo $row['keyword'] . "\n";
    }
    
    echo "\nQuery completed successfully.\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 