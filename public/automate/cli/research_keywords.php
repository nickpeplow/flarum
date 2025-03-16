#!/usr/bin/env php
<?php
/**
 * Research Keywords CLI Script
 * 
 * Finds keywords with tag_id assigned, generates research content using AI,
 * and saves the research to the keywords table.
 */

/**
 * Usage Examples:
 * 
 * Process all keywords with tag_id assigned:
 * php research_keywords.php
 * 
 * Process specific keyword IDs:
 * php research_keywords.php --keyword_ids=1,2,3
 * 
 * Limit number of keywords to process:
 * php research_keywords.php --limit=10
 * 
 * Process keywords for a specific tag:
 * php research_keywords.php --tag_id=5
 * 
 * Dry run (don't update database):
 * php research_keywords.php --dry-run
 * 
 * Use text format instead of JSON:
 * php research_keywords.php --format=text
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

// Import the ResearchKeywords class
use cli\ResearchKeywords;

// Parse command line options
$options = getopt('', ['keyword_ids::', 'tag_id::', 'limit::', 'dry-run', 'format::']);
$keywordIds = isset($options['keyword_ids']) ? explode(',', $options['keyword_ids']) : null;
$tagId = isset($options['tag_id']) ? (int)$options['tag_id'] : null;
$limit = isset($options['limit']) ? (int)$options['limit'] : null;
$dryRun = isset($options['dry-run']);
$format = isset($options['format']) ? $options['format'] : 'json';

echo "-----------------------------------------------------\n";
echo "Keyword Research Tool\n";
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

// Run the process
try {
    // Create a new ResearchKeywords instance
    $researchKeywords = new ResearchKeywords($pdo, $dryRun, $format);
    
    // Process keywords
    $results = $researchKeywords->findAndProcessKeywords($keywordIds, $tagId, $limit);
    
    // Exit with success
    exit(0);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 