<?php
/**
 * Database Connection
 * 
 * This file establishes a PDO connection to the database using
 * the configuration from config.php.
 */

// Load the configuration from the config file
$config = require __DIR__ . '/../config.php';
$dbConfig = $config['database'];

// Set up database connection
try {
    // Use 127.0.0.1 which we know works from our previous tests
    $dsn = "{$dbConfig['driver']}:host=127.0.0.1;port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
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
    
    // Make the database configuration available to other files
    // that include this connection file
    
} catch (PDOException $e) {
    // Handle connection error
    die("Database connection failed: " . $e->getMessage());
}

/**
 * Helper function to safely get a value from an array with a default fallback
 */
function get_array_value($array, $key, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}

/**
 * Helper function to sanitize user input
 */
function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
} 