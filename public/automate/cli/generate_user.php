#!/usr/bin/env php
<?php
/**
 * Generate User CLI Script
 * 
 * Creates a new user with an AI-generated username in the Flarum database
 */

/**
 * Usage Examples:
 * 
 * Create a regular member (default):
 * php generate_user.php
 * 
 * Create a moderator:
 * php generate_user.php --group=mod
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
    echo "✓ Environment variables loaded from .env file\n";
}

// Parse command line options
$options = getopt('', ['email::', 'password::', 'domain::', 'dry-run', 'group::']);
$email = $options['email'] ?? null;
$password = $options['password'] ?? null;
$domain = $options['domain'] ?? 'example.com';
$dryRun = isset($options['dry-run']);
$requestedGroup = isset($options['group']) ? strtolower($options['group']) : 'member';

echo "-----------------------------------------------------\n";
echo "User Generator Tool\n";
echo "-----------------------------------------------------\n";

// Initialize database connection
try {
    global $pdo;
    if (!isset($pdo) || !($pdo instanceof \PDO)) {
        throw new \Exception("No database connection available");
    }
    
    echo "✓ Database connection established\n";
} catch (\Exception $e) {
    echo "Error: Failed to connect to the database: " . $e->getMessage() . "\n";
    exit(1);
}

// Check that the template file exists
$templatePath = __DIR__ . '/templates/username_prompt.tpl';
if (!file_exists($templatePath)) {
    echo "Creating username prompt template...\n";
    
    // Create templates directory if it doesn't exist
    $templatesDir = __DIR__ . '/templates';
    if (!is_dir($templatesDir)) {
        mkdir($templatesDir, 0755, true);
    }
    
    // Template content
    $templateContent = <<<EOT
Generate THREE creative, unique usernames for a forum user on a forum about: "{{forum_description}}".

Each username should be:
1. Between 5-15 characters long
2. Not contain any spaces (can use underscores instead)
3. Not include any offensive or inappropriate terms
4. Be memorable and distinct
5. Be thematically appropriate for the forum topic

Consider using one of these username formats as inspiration (but create completely original usernames, DO NOT use the example usernames provided below):
- {{format_1}}
- {{format_2}}
- {{format_3}}

IMPORTANT: Your usernames must be completely original. DO NOT return any of the example usernames like "HappyCat", "CoolCat", etc. Create new, unique usernames based on the format patterns but with different words.

Please provide your response in valid JSON format as follows:
{
  "usernames": [
    "username1",
    "username2",
    "username3"
  ]
}

Do not include any explanation or additional text, just the JSON.
EOT;
    
    // Save the template file
    file_put_contents($templatePath, $templateContent);
    echo "✓ Username prompt template created at: $templatePath\n";
}

// Run the process
try {
    // Create a new UserGenerator instance
    $userGenerator = new UserGenerator($pdo, $dryRun);
    
    // Create the user
    $result = $userGenerator->createUser([
        'email' => $email,
        'password' => $password,
        'domain' => $domain,
        'group' => $requestedGroup
    ]);
    
    // Exit with success
    exit($result['success'] ? 0 : 1);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}