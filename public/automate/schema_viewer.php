<?php
/**
 * Keywords Automation Dashboard - Schema Viewer
 * 
 * This file directly includes the HomeController to handle the schema redirect.
 */

// Mark that we're running in MVC context
define('MVC_APP', true);

// Require the autoloader
require_once __DIR__ . '/Core/Autoloader.php';

// Register the autoloader
\Core\Autoloader::register();

// Initialize the application
\Core\Bootstrap::init();

// Include helpers
require_once __DIR__ . '/Views/partials/helpers.php';

// Create and run the HomeController
try {
    $controller = new \Controllers\HomeController();
    $controller->schemaRedirect();
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