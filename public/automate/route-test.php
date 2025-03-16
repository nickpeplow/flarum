<?php
/**
 * Route Testing Script
 * 
 * This script demonstrates how the router processes URLs
 */

// Set error reporting for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Route Testing Tool</h1>";

// Get the current URL
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];
$scriptName = $_SERVER['SCRIPT_NAME'];
$basePath = dirname($_SERVER['SCRIPT_NAME']);

echo "<h2>Current Request Information</h2>";
echo "<ul>";
echo "<li><strong>Full URL:</strong> " . htmlspecialchars($currentUrl) . "</li>";
echo "<li><strong>Request URI:</strong> " . htmlspecialchars($requestUri) . "</li>";
echo "<li><strong>Request Method:</strong> " . htmlspecialchars($requestMethod) . "</li>";
echo "<li><strong>Script Name:</strong> " . htmlspecialchars($scriptName) . "</li>";
echo "<li><strong>Base Path:</strong> " . htmlspecialchars($basePath) . "</li>";
echo "</ul>";

echo "<h2>Router URL Processing</h2>";
echo "<p>This shows how the Router class would process this URL:</p>";

// Apply the Router's logic to clean up the URI
$cleanUri = $requestUri;

// Remove base path from URI
if ($basePath !== '/') {
    $cleanUri = substr($cleanUri, strlen($basePath));
    echo "<p>After removing base path: <code>" . htmlspecialchars($cleanUri) . "</code></p>";
}

// Handle URLs with index.php in them
if (strpos($cleanUri, '/index.php') === 0) {
    $cleanUri = substr($cleanUri, strlen('/index.php'));
    echo "<p>After removing index.php: <code>" . htmlspecialchars($cleanUri) . "</code></p>";
}

// Special handling for empty paths
if (empty($cleanUri) || $cleanUri === '/') {
    $cleanUri = '/index';
    echo "<p>After handling empty path: <code>" . htmlspecialchars($cleanUri) . "</code></p>";
}

// Handle common error where keywords.php is appended after index.php
if ($cleanUri === '/keywords.php') {
    echo "<p>Detected keywords.php path, would be changed to: <code>/keywords</code></p>";
    $cleanUri = '/keywords';
}

echo "<div style='background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
echo "<strong>Final cleaned URI that would be matched against routes:</strong> <code>" . htmlspecialchars($cleanUri) . "</code>";
echo "</div>";

// Test how the URL would be reconstructed for redirection
echo "<h2>URL Reconstruction Tests</h2>";

// Test how the router would build redirect URLs
echo "<p>If the router needed to redirect to <code>/keywords</code>, it would use:</p>";
$redirectUrl = $basePath . "/keywords";
echo "<div style='background-color: #cce5ff; color: #004085; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
echo "<code>header(\"Location: " . htmlspecialchars($redirectUrl) . "\");</code>";
echo "</div>";

// Test various route patterns
echo "<h2>Route Pattern Tests</h2>";

$testPatterns = [
    '/keywords' => 'KeywordController::index',
    '/keywords.php' => 'KeywordController::index',
    '/index' => 'HomeController::index',
    '/' => 'HomeController::index',
];

echo "<p>Testing how route patterns would match against <code>" . htmlspecialchars($cleanUri) . "</code>:</p>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Pattern</th><th>Would Match?</th><th>Controller/Action</th></tr>";

foreach ($testPatterns as $pattern => $action) {
    // Convert pattern to regex (simplified version of Router::patternToRegex)
    $regex = '@^' . preg_replace('/:([a-zA-Z0-9]+)/', '([^/]+)', $pattern) . '$@D';
    $matches = preg_match($regex, $cleanUri);
    
    echo "<tr>";
    echo "<td><code>" . htmlspecialchars($pattern) . "</code></td>";
    echo "<td>" . ($matches ? "✅ Yes" : "❌ No") . "</td>";
    echo "<td>" . htmlspecialchars($action) . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>Link Generation Test</h2>";

function generateLink($path, $basePath, $baseUrl = null) {
    if ($baseUrl === null) {
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    }
    
    // If base path is the document root, normalize it
    if ($basePath === '/') {
        $basePath = '';
    }
    
    // Check if path already has the base path or is an absolute URL
    if (strpos($path, 'http') === 0 || strpos($path, '/') === 0) {
        return $path;
    } else {
        // Construct the proper URL
        return $baseUrl . $basePath . '/' . $path;
    }
}

// Test URL generation
$testUrls = [
    'keywords',
    'keywords.php',
    '/keywords',
    '/keywords.php',
    'https://example.com/keywords',
];

echo "<p>Testing how links would be generated from different paths:</p>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Input Path</th><th>Generated URL</th></tr>";

foreach ($testUrls as $path) {
    $generatedUrl = generateLink($path, $basePath);
    echo "<tr>";
    echo "<td><code>" . htmlspecialchars($path) . "</code></td>";
    echo "<td><code>" . htmlspecialchars($generatedUrl) . "</code></td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>Test Application Base URL</h2>";

// Try to get the base_url from the App class
if (file_exists(__DIR__ . '/Core/Autoloader.php')) {
    require_once __DIR__ . '/Core/Autoloader.php';
    \Core\Autoloader::register();
    
    if (class_exists('\Config\App')) {
        echo "<p>Config\\App base_url: <code>" . htmlspecialchars(\Config\App::get('base_url')) . "</code></p>";
        
        // Test keyword link generation
        $keywordUrl = \Config\App::get('base_url') . '/keywords';
        echo "<p>Keywords URL using App::get('base_url'): <code>" . htmlspecialchars($keywordUrl) . "</code></p>";
    }
}

echo "<h2>Live Test Links</h2>";
echo "<p>Click these links to test different URL formats:</p>";

echo "<ul>";
echo "<li><a href='" . htmlspecialchars(dirname($_SERVER['SCRIPT_NAME']) . "/keywords") . "'>Standard Link: /keywords</a></li>";
echo "<li><a href='" . htmlspecialchars(dirname($_SERVER['SCRIPT_NAME']) . "/keywords.php") . "'>Legacy Link: /keywords.php</a></li>";
echo "</ul>";

echo "<h2>Router Registration Test</h2>";
echo "<p>Would you like to see how the router actually processes the request?</p>";
echo "<form method='post'>";
echo "<input type='submit' name='run_router' value='Test Full Router Dispatch' style='padding: 10px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;'>";
echo "</form>";

// If the form is submitted, try to run the actual router
if (isset($_POST['run_router'])) {
    echo "<h3>Actual Router Execution:</h3>";
    echo "<div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 10px;'>";
    
    try {
        // Include the Router class and create an instance
        if (class_exists('\Core\Router')) {
            $router = new \Core\Router();
            
            // Register some test routes
            $router->register('GET', '/index', 'Test', 'index');
            $router->register('GET', '/', 'Test', 'index');
            $router->register('GET', '/keywords', 'Test', 'keywords');
            $router->register('GET', '/keywords.php', 'Test', 'keywords');
            
            // Create a Test class dynamically
            eval('
            class Test {
                public function index() {
                    echo "<div style=\'background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 5px;\'>
                        Home page action executed
                    </div>";
                }
                
                public function keywords() {
                    echo "<div style=\'background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 5px;\'>
                        Keywords page action executed
                    </div>";
                }
            }
            ');
            
            // Start output buffering to capture the debug comments
            ob_start();
            
            // Run the router's dispatch method
            $router->dispatch();
            
            // Get the output and extract the HTML comments
            $output = ob_get_clean();
            $debugInfo = preg_replace('/.*?(<!--.*?-->).*?/s', '$1<br>', $output);
            $debugInfo = str_replace('-->', '--><br>', $debugInfo);
            
            // Show the debug info
            echo "<h4>Router Debug Output:</h4>";
            echo $debugInfo;
            
            // Show the router output
            echo "<h4>Router Result:</h4>";
            echo $output;
        } else {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
            echo "Router class not found. Make sure Core\\Router exists.";
            echo "</div>";
        }
    } catch (Exception $e) {
        echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "Error running router: " . htmlspecialchars($e->getMessage());
        echo "</div>";
    }
    
    echo "</div>";
} 