<?php
/**
 * Database Connection
 * 
 * This file establishes a PDO connection to the database
 * and defines helper functions for database operations.
 */

// Load configuration from the main Flarum config file
$config = require __DIR__ . '/../config.php';
$dbConfig = $config['database'];

// Establish database connection using PDO
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
} catch (PDOException $e) {
    // Try alternate connection method (127.0.0.1 instead of localhost)
    try {
        $dsn = "{$dbConfig['driver']}:host=127.0.0.1;port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
        $pdo = new PDO(
            $dsn, 
            $dbConfig['username'], 
            $dbConfig['password'], 
            $options
        );
    } catch (PDOException $e2) {
        // If in web mode, show formatted error
        if (php_sapi_name() !== 'cli') {
            echo '<div style="color:red;font-family:sans-serif;padding:20px;border:1px solid #f88;background:#fee;border-radius:5px;margin:20px;">';
            echo '<h2>Database Connection Error</h2>';
            echo '<p>' . htmlspecialchars($e2->getMessage()) . '</p>';
            echo '<p>Please check your database configuration.</p>';
            echo '</div>';
        }
        // Throw exception to stop script execution
        throw new Exception("Failed to connect to database: " . $e2->getMessage());
    }
}

/**
 * Function to safely get a value from an array
 * 
 * @param array $array The array to extract values from
 * @param string $key The key to look for
 * @param mixed $default Default value if key doesn't exist
 * @return mixed The value or default
 */
function get_array_value($array, $key, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}

/**
 * Function to sanitize user input
 * 
 * @param string $input The input to sanitize
 * @return string Sanitized input
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
} 