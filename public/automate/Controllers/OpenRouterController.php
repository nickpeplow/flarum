<?php
/**
 * OpenRouter Controller
 * 
 * Handles OpenRouter settings and configuration
 */

namespace Controllers;

use Core\Controller;
use Utils\DotEnv;

class OpenRouterController extends Controller {
    private $dotEnv;
    private $envFilePath;
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->envFilePath = __DIR__ . '/../.env';
        
        try {
            $this->dotEnv = new DotEnv();
        } catch (\Exception $e) {
            $this->addAlert('error', 'Error loading .env file: ' . $e->getMessage());
        }
    }
    
    /**
     * Settings page
     * 
     * @return void
     */
    public function settings() {
        // Get current settings
        $settings = $this->getSettings();
        
        // Available models
        $availableModels = [
            'deepseek/deepseek-r1:free' => 'DeepSeek R1 (Free)',
            'anthropic/claude-3.5-sonnet' => 'Claude 3.5 Sonnet',
            'openai/gpt-4o-mini' => 'GPT-4o Mini',
            'anthropic/claude-3.7-sonnet' => 'Claude 3.7 Sonnet',
            'anthropic/claude-3-haiku' => 'Claude 3 Haiku',
            'anthropic/claude-3-opus' => 'Claude 3 Opus',
            'mistral/mistral-large' => 'Mistral Large',
            'google/gemini-pro' => 'Google Gemini Pro'
        ];
        
        // Get log data
        $requestModel = new \Models\OpenRouterRequest();
        
        // Get page parameters
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Get logs
        $logs = $requestModel->getAll($limit, $offset, 'created_at', 'DESC');
        $totalLogs = $requestModel->count();
        $totalPages = ceil($totalLogs / $limit);
        
        // Get usage statistics
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        $stats = $requestModel->getUsageStats($startDate, $endDate);
        
        // Get alerts
        $alerts = $this->getAlerts();
        
        // Render the view
        $this->render('openrouter/settings', [
            'pageTitle' => 'OpenRouter Settings',
            'pageHeader' => 'OpenRouter Settings',
            'settings' => $settings,
            'availableModels' => $availableModels,
            'logs' => $logs,
            'totalLogs' => $totalLogs,
            'totalPages' => $totalPages,
            'page' => $page,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'stats' => $stats,
            'alerts' => $alerts
        ]);
    }
    
    /**
     * Save settings
     * 
     * @return void
     */
    public function save() {
        // Get form data
        $apiKey = $_POST['api_key'] ?? '';
        $contentModel = $_POST['content_model'] ?? 'anthropic/claude-3-haiku';
        $researchModel = $_POST['research_model'] ?? 'anthropic/claude-3-opus';
        $maxTokens = (int)($_POST['max_tokens'] ?? 1024);
        $temperature = (float)($_POST['temperature'] ?? 0.7);
        
        // Validate input
        if (empty($apiKey)) {
            $this->addAlert('warning', 'API Key cannot be empty.');
            $this->redirect('openrouter/settings');
            return;
        }
        
        // Prepare new contents for .env file
        $envContent = "# OpenRouter API Configuration\n";
        $envContent .= "OPENROUTER_API_KEY=" . trim($apiKey) . "\n\n";
        $envContent .= "# Model configurations\n";
        $envContent .= "# Content generation model (for shorter creative content, completions)\n";
        $envContent .= "OPENROUTER_CONTENT_MODEL=" . trim($contentModel) . "\n\n";
        $envContent .= "# Research model (for more detailed research, analysis, longer responses)\n";
        $envContent .= "OPENROUTER_RESEARCH_MODEL=" . trim($researchModel) . "\n\n";
        $envContent .= "# Default settings\n";
        $envContent .= "OPENROUTER_MAX_TOKENS=" . $maxTokens . "\n";
        $envContent .= "OPENROUTER_TEMPERATURE=" . $temperature . "\n";
        
        // Save to .env file
        try {
            if (file_put_contents($this->envFilePath, $envContent)) {
                $this->addAlert('success', 'OpenRouter settings saved successfully!');
            } else {
                $this->addAlert('error', 'Failed to save settings. Check file permissions for .env file.');
            }
        } catch (\Exception $e) {
            $this->addAlert('error', 'Error saving settings: ' . $e->getMessage());
        }
        
        // Redirect back to settings page
        $this->redirect('openrouter/settings');
    }
    
    /**
     * Test content model with a simple prompt
     * 
     * @return void
     */
    public function testContentModel() {
        try {
            $openRouter = new \Utils\OpenRouter();
            $prompt = "Hello, please respond with a short greeting.";
            
            // Debug message before making the call
            error_log("Making content model test call with prompt: " . $prompt);
            
            // Use the environment settings for max_tokens instead of hardcoding
            $response = $openRouter->generate($prompt, 'content', [
                'request_source' => 'test_button'
                // No max_tokens override - will use the value from .env
            ]);
            
            // Log the full raw response for debugging
            error_log("Raw API response: " . json_encode($response));
            
            if (!empty($response)) {
                $content = $openRouter->extractContent($response);
                error_log("Extracted content: " . $content);
                
                // Get the last request ID for debugging
                $requestModel = new \Models\OpenRouterRequest();
                $lastLog = $requestModel->getAll(1, 0, 'id', 'DESC');
                if (!empty($lastLog)) {
                    error_log("Last log record ID: " . $lastLog[0]['id'] . ", response_text: " . substr($lastLog[0]['response_text'] ?? 'NULL', 0, 100));
                }
                
                // Check the full response structure
                if (isset($response['choices']) && isset($response['choices'][0])) {
                    error_log("Response choices structure: " . json_encode($response['choices'][0]));
                }
                
                $this->addAlert('success', 'Content model test successful! Response: "' . substr($content, 0, 100) . (strlen($content) > 100 ? '...' : '') . '"' . 
                    '<br>Response structure logged to error log for debugging.');
            } else {
                $this->addAlert('warning', 'Content model connected but returned an empty response.');
            }
        } catch (\Exception $e) {
            $this->addAlert('error', 'Failed to test content model: ' . $e->getMessage());
        }
        
        // Redirect back to settings page
        $this->redirect('openrouter/settings');
    }
    
    /**
     * Test research model with a simple prompt
     * 
     * @return void
     */
    public function testResearchModel() {
        try {
            $openRouter = new \Utils\OpenRouter();
            $prompt = "What are the main benefits of using OpenRouter for AI applications?";
            
            // Debug message before making the call
            error_log("Making research model test call with prompt: " . $prompt);
            
            // Use the environment settings for max_tokens instead of hardcoding
            $response = $openRouter->generate($prompt, 'research', [
                'request_source' => 'test_button'
                // No max_tokens override - will use the value from .env
            ]);
            
            // Log the full raw response for debugging
            error_log("Raw API response: " . json_encode($response));
            
            if (!empty($response)) {
                $content = $openRouter->extractContent($response);
                error_log("Extracted content: " . $content);
                
                // Get the last request ID for debugging
                $requestModel = new \Models\OpenRouterRequest();
                $lastLog = $requestModel->getAll(1, 0, 'id', 'DESC');
                if (!empty($lastLog)) {
                    error_log("Last log record ID: " . $lastLog[0]['id'] . ", response_text: " . substr($lastLog[0]['response_text'] ?? 'NULL', 0, 100));
                }
                
                // Check the full response structure
                if (isset($response['choices']) && isset($response['choices'][0])) {
                    error_log("Response choices structure: " . json_encode($response['choices'][0]));
                }
                
                $this->addAlert('success', 'Research model test successful! Response: "' . substr($content, 0, 100) . (strlen($content) > 100 ? '...' : '') . '"' . 
                    '<br>Response structure logged to error log for debugging.');
            } else {
                $this->addAlert('warning', 'Research model connected but returned an empty response.');
            }
        } catch (\Exception $e) {
            $this->addAlert('error', 'Failed to test research model: ' . $e->getMessage());
        }
        
        // Redirect back to settings page
        $this->redirect('openrouter/settings');
    }
    
    /**
     * Get current settings from .env file
     * 
     * @return array
     */
    private function getSettings() {
        $settings = [
            'api_key' => '',
            'content_model' => 'anthropic/claude-3-haiku',
            'research_model' => 'anthropic/claude-3-opus',
            'max_tokens' => 1024,
            'temperature' => 0.7
        ];
        
        try {
            if (file_exists($this->envFilePath)) {
                // Get settings from DotEnv
                $settings['api_key'] = $this->dotEnv->get('OPENROUTER_API_KEY', '');
                $settings['content_model'] = $this->dotEnv->get('OPENROUTER_CONTENT_MODEL', 'anthropic/claude-3-haiku');
                $settings['research_model'] = $this->dotEnv->get('OPENROUTER_RESEARCH_MODEL', 'anthropic/claude-3-opus');
                $settings['max_tokens'] = (int)$this->dotEnv->get('OPENROUTER_MAX_TOKENS', 1024);
                $settings['temperature'] = (float)$this->dotEnv->get('OPENROUTER_TEMPERATURE', 0.7);
            }
        } catch (\Exception $e) {
            $this->addAlert('warning', 'Could not load current settings: ' . $e->getMessage());
        }
        
        return $settings;
    }
    
    /**
     * Display OpenRouter API usage logs
     *
     * @return void
     */
    public function logs() {
        // Get the request model
        $requestModel = new \Models\OpenRouterRequest();
        
        // Get page parameters
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Get logs
        $logs = $requestModel->getAll($limit, $offset, 'created_at', 'DESC');
        $totalLogs = $requestModel->count();
        $totalPages = ceil($totalLogs / $limit);
        
        // Get usage statistics
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        $stats = $requestModel->getUsageStats($startDate, $endDate);
        
        // Render the view
        include __DIR__ . '/../Views/openrouter/logs.php';
    }
} 