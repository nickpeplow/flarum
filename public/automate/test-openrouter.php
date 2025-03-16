<?php
/**
 * OpenRouter API Test Script
 * 
 * This script tests the connection to OpenRouter API and
 * demonstrates the different model types
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Determine if this is running in browser or CLI
$isCLI = (php_sapi_name() === 'cli');

// Function to output formatted text
function output($text, $type = 'info') {
    global $isCLI;
    
    if ($isCLI) {
        $colors = [
            'info' => "\033[0;36m", // Cyan
            'success' => "\033[0;32m", // Green
            'error' => "\033[0;31m", // Red
            'warning' => "\033[0;33m", // Yellow
            'reset' => "\033[0m"
        ];
        
        echo $colors[$type] . $text . $colors['reset'] . PHP_EOL;
    } else {
        $styles = [
            'info' => 'color: blue;',
            'success' => 'color: green;',
            'error' => 'color: red;',
            'warning' => 'color: orange;'
        ];
        
        echo "<div style='{$styles[$type]}'>{$text}</div>";
    }
}

// Header
if (!$isCLI) {
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>OpenRouter Test</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h1 { color: #333; }
            pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
            .success { color: green; }
            .error { color: red; }
            .container { max-width: 800px; margin: 0 auto; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>OpenRouter API Test</h1>';
}

// Require the autoloader
require_once __DIR__ . '/Core/Autoloader.php';

// Register the autoloader
\Core\Autoloader::register();

// Test OpenRouter
output("Testing OpenRouter integration...", "info");

try {
    // Create OpenRouter instance
    $openRouter = new \Utils\OpenRouter();
    
    output("✅ OpenRouter configuration loaded successfully!", "success");
    
    // Get test parameters
    $modelType = $_GET['model'] ?? $_SERVER['argv'][1] ?? 'content';
    $prompt = $_GET['prompt'] ?? $_SERVER['argv'][2] ?? 'Tell me a short joke about programming.';
    
    // Validate model type
    if (!in_array($modelType, ['content', 'research'])) {
        $modelType = 'content';
    }
    
    output("Model type: " . $modelType, "info");
    output("Prompt: " . $prompt, "info");
    
    // Generate content
    output("Generating response...", "info");
    $response = $openRouter->generate($prompt, $modelType);
    
    // Extract and display the content
    $content = $openRouter->extractContent($response);
    
    output("Response:", "success");
    if ($isCLI) {
        output("\n" . $content . "\n", "info");
    } else {
        echo "<pre>" . htmlspecialchars($content) . "</pre>";
    }
    
    // Show model info
    $modelInfo = $response['model'] ?? 'Unknown model';
    output("Used model: " . $modelInfo, "info");
    
    // Show token usage
    if (isset($response['usage'])) {
        $promptTokens = $response['usage']['prompt_tokens'] ?? 0;
        $completionTokens = $response['usage']['completion_tokens'] ?? 0;
        $totalTokens = $response['usage']['total_tokens'] ?? 0;
        
        output("Token usage:", "info");
        output("  Prompt tokens: " . $promptTokens, "info");
        output("  Completion tokens: " . $completionTokens, "info");
        output("  Total tokens: " . $totalTokens, "info");
    }
    
} catch (\Exception $e) {
    output("❌ Error: " . $e->getMessage(), "error");
}

// Helper information
if ($isCLI) {
    output("\nUsage:", "info");
    output("  php test-openrouter.php [model_type] [prompt]", "info");
    output("  model_type: 'content' or 'research' (default: content)", "info");
    output("  prompt: The text prompt to send (default: a joke about programming)", "info");
} else {
    echo '<h2>Try it yourself:</h2>
    <form method="get">
        <div style="margin-bottom: 10px;">
            <label for="model">Model Type:</label>
            <select name="model" id="model">
                <option value="content"' . ($modelType === 'content' ? ' selected' : '') . '>Content (faster, cheaper)</option>
                <option value="research"' . ($modelType === 'research' ? ' selected' : '') . '>Research (more detailed)</option>
            </select>
        </div>
        <div style="margin-bottom: 10px;">
            <label for="prompt">Prompt:</label>
            <textarea name="prompt" id="prompt" style="width: 100%; height: 100px;">' . htmlspecialchars($prompt) . '</textarea>
        </div>
        <button type="submit">Generate</button>
    </form>';
}

// Footer for HTML
if (!$isCLI) {
    echo '</div></body></html>';
} 