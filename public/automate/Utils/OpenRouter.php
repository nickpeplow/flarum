<?php
/**
 * OpenRouter API Utility
 * 
 * A utility class for interacting with the OpenRouter API
 * to access various language models
 */

namespace Utils;

class OpenRouter {
    private $apiKey;
    private $contentModel;
    private $researchModel;
    private $maxTokens;
    private $temperature;
    private $apiUrl = 'https://openrouter.ai/api/v1/chat/completions';
    
    /**
     * Constructor - loads configuration from .env file
     */
    public function __construct() {
        $this->loadConfig();
    }
    
    /**
     * Load configuration from .env file
     */
    private function loadConfig() {
        try {
            $dotenv = new DotEnv();
            
            // Set properties from config
            $this->apiKey = $dotenv->get('OPENROUTER_API_KEY');
            $this->contentModel = $dotenv->get('OPENROUTER_CONTENT_MODEL', 'anthropic/claude-3-haiku');
            $this->researchModel = $dotenv->get('OPENROUTER_RESEARCH_MODEL', 'anthropic/claude-3-opus');
            $this->maxTokens = intval($dotenv->get('OPENROUTER_MAX_TOKENS', 1024));
            $this->temperature = floatval($dotenv->get('OPENROUTER_TEMPERATURE', 0.7));
            
            if (!$this->apiKey || $this->apiKey === 'your_openrouter_api_key') {
                throw new \Exception("OpenRouter API key not configured. Please update your .env file.");
            }
        } catch (\Exception $e) {
            throw new \Exception("Failed to load OpenRouter configuration: " . $e->getMessage());
        }
    }
    
    /**
     * Generate content using the specified model
     *
     * @param string $prompt The prompt to send to the model
     * @param string $modelType The type of model to use ('content' or 'research')
     * @param array $options Additional options for the API call
     * @return array The API response
     */
    public function generate($prompt, $modelType = 'content', $options = []) {
        // Determine which model to use
        $model = $modelType === 'research' ? $this->researchModel : $this->contentModel;
        
        // Prepare request data
        $data = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
            'temperature' => $options['temperature'] ?? $this->temperature
        ];
        
        // Add any additional options
        foreach ($options as $key => $value) {
            if (!in_array($key, ['max_tokens', 'temperature'])) {
                $data[$key] = $value;
            }
        }
        
        return $this->makeRequest($data);
    }
    
    /**
     * Make an API request to OpenRouter
     *
     * @param array $data The request payload
     * @return array The API response
     */
    private function makeRequest($data) {
        $ch = curl_init($this->apiUrl);
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
            'HTTP-Referer: https://flarum-keywords.example.com', // Replace with your actual domain
            'X-Title: Flarum Keywords Automation'
        ];
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new \Exception('cURL error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            $error = json_decode($response, true);
            throw new \Exception('API error: ' . ($error['error']['message'] ?? 'Unknown error'));
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Extract the generated text from the API response
     *
     * @param array $response The API response
     * @return string The generated text
     */
    public function extractContent($response) {
        if (isset($response['choices'][0]['message']['content'])) {
            return $response['choices'][0]['message']['content'];
        }
        
        return '';
    }
    
    /**
     * Get the available models information
     * 
     * @return array The list of available models
     */
    public function getModels() {
        $ch = curl_init('https://openrouter.ai/api/v1/models');
        
        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
        ];
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new \Exception('cURL error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            $error = json_decode($response, true);
            throw new \Exception('API error: ' . ($error['error']['message'] ?? 'Unknown error'));
        }
        
        return json_decode($response, true);
    }
} 