<?php
/**
 * OpenRouter API Utility
 * 
 * A utility class for interacting with the OpenRouter API
 * to access various language models
 */

namespace Utils;

use Models\OpenRouterRequest;

class OpenRouter {
    private $apiKey;
    private $contentModel;
    private $researchModel;
    private $maxTokens;
    private $temperature;
    private $apiUrl = 'https://openrouter.ai/api/v1/chat/completions';
    private $requestModel;
    
    /**
     * Constructor - loads configuration from .env file
     */
    public function __construct() {
        $this->loadConfig();
        // Initialize the request model for logging
        $this->requestModel = new OpenRouterRequest();
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
        
        // Create log record before making the request
        $requestId = $this->logRequest($prompt, $model, [
            'temperature' => $data['temperature'],
            'max_tokens' => $data['max_tokens'],
            'request_type' => $modelType,
            'request_source' => $options['request_source'] ?? 'manual',
            'additional_params' => array_diff_key($options, array_flip(['max_tokens', 'temperature', 'request_source']))
        ]);
        
        // Record start time
        $startTime = microtime(true);
        
        try {
            // Make the request
            $response = $this->makeRequest($data);
            
            // Record end time and calculate duration
            $duration = round((microtime(true) - $startTime) * 1000); // Duration in milliseconds
            
            // Log the successful response
            $this->logResponse($requestId, $response, $duration);
            
            return $response;
            
        } catch (\Exception $e) {
            // Log the failed request
            $this->logError($requestId, $e->getMessage());
            
            // Rethrow the exception
            throw $e;
        }
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
    
    /**
     * Log a request to the database
     *
     * @param string $prompt The prompt text
     * @param string $model The model being used
     * @param array $options Additional request options
     * @return int The ID of the created record
     */
    private function logRequest($prompt, $model, $options) {
        try {
            error_log("OpenRouter: Attempting to log request with model: $model");
            error_log("OpenRouter: Using class: " . get_class($this->requestModel));
            $result = $this->requestModel->createRequest($prompt, $model, $options);
            error_log("OpenRouter: Successfully logged request with ID: $result");
            return $result;
        } catch (\Exception $e) {
            // Log error but don't fail the main request
            error_log("Error logging OpenRouter request: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return 0;
        }
    }
    
    /**
     * Log a successful response
     *
     * @param int $requestId The request ID to update
     * @param array $response The API response data
     * @param int $duration Request duration in milliseconds
     * @return bool Success status
     */
    private function logResponse($requestId, $response, $duration) {
        if (!$requestId) return false;
        
        try {
            return $this->requestModel->updateWithResponse($requestId, $response, $duration);
        } catch (\Exception $e) {
            // Log error but don't fail the main request
            error_log("Error logging OpenRouter response: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log an error response
     *
     * @param int $requestId The request ID to update
     * @param string $errorMessage The error message
     * @return bool Success status
     */
    private function logError($requestId, $errorMessage) {
        if (!$requestId) return false;
        
        try {
            return $this->requestModel->markAsFailed($requestId, $errorMessage);
        } catch (\Exception $e) {
            // Log error but don't fail the main request
            error_log("Error logging OpenRouter error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get usage statistics for OpenRouter requests
     *
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return array Usage statistics
     */
    public function getUsageStats($startDate = null, $endDate = null) {
        try {
            return $this->requestModel->getUsageStats($startDate, $endDate);
        } catch (\Exception $e) {
            error_log("Error getting OpenRouter usage stats: " . $e->getMessage());
            return [];
        }
    }
} 