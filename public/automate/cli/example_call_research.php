#!/usr/bin/env php
<?php
/**
 * Example Script: Using ResearchKeywords Class
 * 
 * This example demonstrates how to use the ResearchKeywords class
 * from another script to process a specific keyword.
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

// Import the ResearchKeywords class
use cli\ResearchKeywords;

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
 * Function to generate research for a specific keyword
 * 
 * @param int $keywordId The ID of the keyword to process
 * @param bool $dryRun Whether to actually update the database or just test
 * @param string $format Output format (json or text)
 * @return array|bool Result array or false on failure
 */
function generateResearchForKeyword($keywordId, $dryRun = false, $format = 'json') {
    try {
        global $pdo;
        if (!isset($pdo) || !($pdo instanceof \PDO)) {
            throw new \Exception("No database connection available");
        }
        
        // Create a new ResearchKeywords instance
        $researchKeywords = new ResearchKeywords($pdo, $dryRun, $format);
        
        // Process the specific keyword
        $result = $researchKeywords->processKeywordById($keywordId);
        
        if ($result && $result['success']) {
            echo "\nResearch generation successful for keyword: {$result['keyword']} (ID: {$result['keyword_id']})\n";
            return $result;
        } else {
            echo "\nFailed to generate research for keyword ID: {$keywordId}\n";
            if (isset($result['error'])) {
                echo "Error: {$result['error']}\n";
            }
            return false;
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        return false;
    }
}

// Example of calling the function
if ($argc < 2) {
    echo "Usage: php example_call_research.php <keyword_id> [--dry-run] [--format=json|text]\n";
    exit(1);
}

// Parse command line arguments
$keywordId = intval($argv[1]);
$dryRun = in_array('--dry-run', $argv);
$format = 'json';

foreach ($argv as $arg) {
    if (strpos($arg, '--format=') === 0) {
        $format = substr($arg, 9); // Extract format value after "--format="
        break;
    }
}

echo "-----------------------------------------------------\n";
echo "Example: Generating Research for Keyword ID: {$keywordId}\n";
echo "-----------------------------------------------------\n";

// Call the function to generate research
$result = generateResearchForKeyword($keywordId, $dryRun, $format);

if ($result) {
    // Example of accessing the returned research data
    echo "\nResearch result contains " . strlen($result['research']) . " characters\n";
    echo "Sample preview: " . substr($result['research'], 0, 100) . "...\n";
}

echo "\n-----------------------------------------------------\n";
echo "Process complete\n";
echo "-----------------------------------------------------\n"; 