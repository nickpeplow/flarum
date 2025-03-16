<?php
/**
 * Test file to check MVC routing
 */

// Mark that we're running in MVC context
define('MVC_APP', true);

// Include the MVC framework
require_once __DIR__ . '/Core/Autoloader.php';

// Register the autoloader
\Core\Autoloader::register();

echo "<h1>MVC Test</h1>";
echo "<p>This file is running through the MVC framework.</p>";
echo "<p>Time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Server Info: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";

// Show debug info
echo "<h2>Available Controllers:</h2>";
echo "<ul>";
$controllersDir = __DIR__ . '/Controllers';
if (is_dir($controllersDir)) {
    $files = scandir($controllersDir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && strpos($file, '.php') !== false) {
            echo "<li>" . htmlspecialchars($file) . "</li>";
        }
    }
}
echo "</ul>";

// Show available views
echo "<h2>Available Views:</h2>";
echo "<ul>";
$viewsDir = __DIR__ . '/Views';
if (is_dir($viewsDir)) {
    $dirs = scandir($viewsDir);
    foreach ($dirs as $dir) {
        if ($dir != '.' && $dir != '..' && is_dir($viewsDir . '/' . $dir)) {
            echo "<li>" . htmlspecialchars($dir) . "/</li>";
            $subFiles = scandir($viewsDir . '/' . $dir);
            echo "<ul>";
            foreach ($subFiles as $file) {
                if ($file != '.' && $file != '..' && strpos($file, '.php') !== false) {
                    echo "<li>" . htmlspecialchars($file) . "</li>";
                }
            }
            echo "</ul>";
        }
    }
}
echo "</ul>"; 