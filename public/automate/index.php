<?php
/**
 * Keywords Automation Dashboard - Entry Point
 * 
 * This is the main entry point for the application.
 */

// Mark that we're running in MVC context
define('MVC_APP', true);
// Prevent including old templates
define('EXCLUDE_OLD_TEMPLATES', true);

// Check if a route parameter is provided
if (isset($_GET['route'])) {
    // Rewrite the request URI to use the route parameter
    $_SERVER['REQUEST_URI'] = '/' . $_GET['route'];
}

// Check if this is a direct access to a PHP file (except for this index.php)
$scriptName = basename($_SERVER['SCRIPT_FILENAME']);
if (substr($scriptName, -4) === '.php' && $scriptName !== 'index.php' && file_exists(__DIR__ . '/Core/Autoloader.php')) {
    // This is a direct PHP file access - rewrite the request to use MVC routing
    $requestUri = $_SERVER['REQUEST_URI'];
    $fileName = pathinfo($scriptName, PATHINFO_FILENAME);
    
    // Replace the PHP filename with just the name (e.g., keywords.php -> keywords)
    $newUrl = str_replace($scriptName, $fileName, $requestUri);
    
    // Preserve query string if present
    if (!empty($_SERVER['QUERY_STRING'])) {
        $newUrl .= (strpos($newUrl, '?') === false ? '?' : '&') . $_SERVER['QUERY_STRING'];
    }
    
    // Redirect to the proper MVC URL
    header("Location: $newUrl");
    exit;
}

// Error handling for production
function handleError($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return;
    }

    // For fatal errors, display a user-friendly message
    if ($errno == E_ERROR || $errno == E_USER_ERROR || $errno == E_PARSE) {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
        echo "<h3>Application Error</h3>";
        echo "<p>An error occurred while processing your request. Please try again later.</p>";
        if (ini_get('display_errors')) {
            echo "<p><strong>Error:</strong> $errstr in $errfile on line $errline</p>";
        }
        echo "</div>";
        exit(1);
    }

    // Let PHP handle other types of errors
    return false;
}

// Set error handler
set_error_handler("handleError");

try {
    // Require the autoloader
    require_once __DIR__ . '/Core/Autoloader.php';

    // Register the autoloader
    \Core\Autoloader::register();

    // Initialize the application
    \Core\Bootstrap::init();

    // Include helpers
    require_once __DIR__ . '/Views/partials/helpers.php';

    // Create a router
    $router = new \Core\Router();

    // Define routes
    $router->register('GET', '/index', '\Controllers\HomeController', 'index');
    $router->register('GET', '/', '\Controllers\HomeController', 'index');
    
    // Keywords routes - ensure these are registered correctly
    $router->register('GET', '/keywords', '\Controllers\KeywordController', 'index');
    $router->register('POST', '/keywords', '\Controllers\KeywordController', 'processAction');
    
    // Legacy keywords.php support
    $router->register('GET', '/keywords.php', '\Controllers\KeywordController', 'index');
    $router->register('POST', '/keywords.php', '\Controllers\KeywordController', 'processAction');
    
    // Tags routes
    $router->register('GET', '/tags', '\Controllers\TagController', 'index');
    $router->register('GET', '/tags.php', '\Controllers\TagController', 'index');
    
    // Discussions routes
    $router->register('GET', '/discussions', '\Controllers\DiscussionController', 'index');
    $router->register('GET', '/discussions/view/(\d+)', '\Controllers\DiscussionController', 'view');
    $router->register('GET', '/discussions.php', '\Controllers\DiscussionController', 'index');
    
    // Schema viewer routes - to be implemented 
    $router->register('GET', '/schema', '\Controllers\HomeController', 'schemaRedirect');
    $router->register('GET', '/schema_viewer.php', '\Controllers\HomeController', 'schemaRedirect');

    // Dispatch the request
    $router->dispatch();
} catch (Exception $e) {
    // Handle any uncaught exceptions
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
    echo "<h3>Application Error</h3>";
    if (ini_get('display_errors')) {
        echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
        echo "<p><strong>File:</strong> " . $e->getFile() . " on line " . $e->getLine() . "</p>";
    } else {
        echo "<p>An error occurred while processing your request. Please try again later.</p>";
    }
    echo "</div>";
} 