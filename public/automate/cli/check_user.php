#!/usr/bin/env php
<?php
/**
 * Check User
 * 
 * Verifies if a user exists in the database
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

// Parse command line options
$options = getopt('', ['username::', 'id::']);
$username = $options['username'] ?? null;
$id = $options['id'] ?? null;

echo "-----------------------------------------------------\n";
echo "User Check Tool\n";
echo "-----------------------------------------------------\n";

// Initialize database connection
try {
    global $pdo;
    if (!isset($pdo) || !($pdo instanceof \PDO)) {
        throw new \Exception("No database connection available");
    }
    
    echo "✓ Database connection established\n\n";
    
    if ($username) {
        $stmt = $pdo->prepare("SELECT id, username, email, is_email_confirmed, joined_at FROM users WHERE username = :username");
        $stmt->bindValue(':username', $username);
    } elseif ($id) {
        $stmt = $pdo->prepare("SELECT id, username, email, is_email_confirmed, joined_at FROM users WHERE id = :id");
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
    } else {
        echo "Please specify either --username or --id\n";
        exit(1);
    }
    
    $stmt->execute();
    $user = $stmt->fetch(\PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "User found in database:\n";
        echo "ID: " . $user['id'] . "\n";
        echo "Username: " . $user['username'] . "\n";
        echo "Email: " . $user['email'] . "\n";
        echo "Email Confirmed: " . ($user['is_email_confirmed'] ? "Yes" : "No") . "\n";
        echo "Joined At: " . $user['joined_at'] . "\n";
    } else {
        echo "User not found in database.\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1); 