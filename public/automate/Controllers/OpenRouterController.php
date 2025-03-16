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
        
        // Get alerts
        $alerts = $this->getAlerts();
        
        // Render the view
        $this->render('openrouter/settings', [
            'pageTitle' => 'OpenRouter Settings',
            'pageHeader' => 'OpenRouter Settings',
            'settings' => $settings,
            'availableModels' => $availableModels,
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
     * Test API connection
     * 
     * @return void
     */
    public function testConnection() {
        try {
            $openRouter = new \Utils\OpenRouter();
            $modelInfo = $openRouter->getModels();
            
            if (!empty($modelInfo)) {
                $this->addAlert('success', 'Successfully connected to OpenRouter API. Found ' . count($modelInfo['data'] ?? []) . ' available models.');
            } else {
                $this->addAlert('warning', 'Connected to OpenRouter API, but no models were returned.');
            }
        } catch (\Exception $e) {
            $this->addAlert('error', 'Failed to connect to OpenRouter API: ' . $e->getMessage());
        }
        
        // Redirect back to settings page
        $this->redirect('openrouter/settings');
    }
} 