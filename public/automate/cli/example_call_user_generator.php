#!/usr/bin/env php
<?php
/**
 * Example Script: Using UserGenerator Class
 * 
 * This example demonstrates how to use the UserGenerator class
 * from another script to create a user programmatically.
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

// Import the UserGenerator class
use cli\UserGenerator;

// Load environment variables from .env file if it exists
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse the line
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Remove quotes if present
            if (strpos($value, '"') === 0 || strpos($value, "'") === 0) {
                $value = trim($value, '"\'');
            }
            
            // Set the environment variable
            putenv("$name=$value");
            $_ENV[$name] = $value;
        }
    }
}

/**
 * Function to create a new user
 * 
 * @param string $email Email address for the user (optional)
 * @param string $password Password for the user (optional)
 * @param string $userType Type of user (member or mod)
 * @param bool $dryRun Whether to actually create the user
 * @return array Result array with user data
 */
function createNewUser($email = null, $password = null, $userType = 'member', $dryRun = false) {
    try {
        global $pdo;
        if (!isset($pdo) || !($pdo instanceof \PDO)) {
            throw new \Exception("No database connection available");
        }
        
        // Create a new UserGenerator instance
        $userGenerator = new UserGenerator($pdo, $dryRun);
        
        // Create the user
        $result = $userGenerator->createUser([
            'email' => $email,
            'password' => $password,
            'group' => $userType
        ]);
        
        if ($result['success']) {
            echo "\nUser creation successful: {$result['username']} (ID: {$result['user_id']})\n";
            return $result;
        } else {
            echo "\nFailed to create user\n";
            if (isset($result['error'])) {
                echo "Error: {$result['error']}\n";
            }
            return $result;
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        return [
            'error' => $e->getMessage(),
            'success' => false
        ];
    }
}

// Example of calling the function
$userType = 'member';
$dryRun = true; // Set to false to actually create the user

echo "-----------------------------------------------------\n";
echo "Example: Creating a {$userType} user\n";
echo "-----------------------------------------------------\n";

// Call the function to create a user
$result = createNewUser(null, null, $userType, $dryRun);

if ($result['success']) {
    // Example of accessing the returned user data
    echo "\nCreated user details:\n";
    echo "- Username: {$result['username']}\n";
    echo "- Email: {$result['email']}\n";
    echo "- Password: {$result['password']}\n";
    echo "- Group: {$result['group']}\n";
    
    if (isset($result['user_id'])) {
        echo "- User ID: {$result['user_id']}\n";
    }
}

echo "\n-----------------------------------------------------\n";
echo "Process complete\n";
echo "-----------------------------------------------------\n"; 