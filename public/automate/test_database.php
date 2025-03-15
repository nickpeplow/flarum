<?php
/**
 * Database Test Script
 * 
 * This script tests the database connection and table structure.
 */

// For debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
require_once __DIR__ . '/../db_connection.php';

// Function to check if a table exists
function tableExists($pdo, $table) {
    try {
        $result = $pdo->query("SELECT 1 FROM $table LIMIT 1");
        return $result !== false;
    } catch (Exception $e) {
        return false;
    }
}

// Function to create the keywords table
function createKeywordsTable($pdo) {
    $schemaFile = file_get_contents(__DIR__ . '/schema/keywords_schema.sql');
    return $pdo->exec($schemaFile);
}

// Main test
echo "<h1>Database Connection Test</h1>";

// Test connection
echo "<h2>PDO Connection Test</h2>";
echo "<pre>";
echo "PDO Connection: " . (isset($pdo) ? "SUCCESS" : "FAILED") . "\n";

if (isset($pdo)) {
    echo "PDO Driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
    echo "Server Version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
    echo "Client Version: " . $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION) . "\n";
    
    // Test keywords table
    echo "\n<h2>Keywords Table Test</h2>\n";
    $keywordsTableExists = tableExists($pdo, 'keywords');
    echo "Keywords Table Exists: " . ($keywordsTableExists ? "YES" : "NO") . "\n";
    
    if (!$keywordsTableExists) {
        echo "Attempting to create keywords table...\n";
        try {
            createKeywordsTable($pdo);
            $keywordsTableExists = tableExists($pdo, 'keywords');
            echo "Table Creation: " . ($keywordsTableExists ? "SUCCESS" : "FAILED") . "\n";
        } catch (Exception $e) {
            echo "Error creating table: " . $e->getMessage() . "\n";
        }
    }
    
    // Test table structure if it exists
    if ($keywordsTableExists) {
        echo "\n<h2>Keywords Table Structure</h2>\n";
        $stmt = $pdo->query("DESCRIBE keywords");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Columns in keywords table:\n";
        foreach ($columns as $column) {
            echo "- {$column['Field']} ({$column['Type']})" . 
                 (($column['Null'] === 'NO') ? " NOT NULL" : "") . 
                 ($column['Default'] !== null ? " DEFAULT {$column['Default']}" : "") . 
                 ($column['Extra'] ? " {$column['Extra']}" : "") . "\n";
        }
    }
}
echo "</pre>";

// Include link back to dashboard
echo "<p><a href='index.php'>Return to Dashboard</a></p>"; 