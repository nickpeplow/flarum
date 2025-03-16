<?php
/**
 * Direct Keywords Page
 * 
 * This file provides direct access to the keywords page without routing
 */

// Mark that we're running in MVC context
define('MVC_APP', true);

// Error reporting for production
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Require the autoloader
    require_once __DIR__ . '/Core/Autoloader.php';

    // Register the autoloader
    \Core\Autoloader::register();

    // Initialize the application
    \Core\Bootstrap::init();

    // Create and run the KeywordController directly
    $controller = new \Controllers\KeywordController();
    
    // Check if this is a POST request for actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->processAction();
    } else {
        // Otherwise, show the keywords index page
        $controller->index();
    }
} catch (Exception $e) {
    // Handle any uncaught exceptions
    echo "<div style='background-color: #f8d7da; color: #721c24; padding: 20px; margin: 20px; border-radius: 5px; border: 1px solid #f5c6cb;'>";
    echo "<h3>Application Error</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . " on line " . $e->getLine() . "</p>";
    echo "</div>";
} 