<?php
/**
 * Bootstrap file for CLI scripts
 * 
 * This file loads all required dependencies for CLI scripts
 */

// Define base path
define('BASE_PATH', __DIR__);

// Load configuration
require_once BASE_PATH . '/config.php';

// Autoloader function for classes
spl_autoload_register(function ($className) {
    // Convert namespace separators to directory separators
    $className = str_replace('\\', '/', $className);
    $filePath = BASE_PATH . '/' . $className . '.php';
    
    if (file_exists($filePath)) {
        require_once $filePath;
        return true;
    }
    return false;
});

/**
 * Get database connection
 * 
 * @return PDO Database connection
 */
function get_db_connection() {
    static $db = null;
    
    if ($db === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $db = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }
    
    return $db;
}

// Load environment variables
$dotenv = new Utils\DotEnv();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Register shutdown function for error handling
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        echo "FATAL ERROR: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line'] . PHP_EOL;
    }
}); 