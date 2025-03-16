<?php
/**
 * OpenRouterRequest Model
 *
 * Handles database operations for OpenRouter API requests
 */

namespace Models;

use Core\Model;

class OpenRouterRequest extends Model {
    protected $table = "openrouter_requests";
    
    /**
     * Create a new request record before sending to API
     * 
     * @param string $prompt The prompt text
     * @param string $model The model being used
     * @param array $options Additional request options
     * @return int The ID of the created record
     */
    public function createRequest($prompt, $model, $options = []) {
        try {
            error_log("OpenRouterRequest: Starting createRequest method");
            $additionalParams = isset($options['additional_params']) ? 
                json_encode($options['additional_params']) : null;
                
            $data = [
                'prompt' => $prompt,
                'model' => $model,
                'temperature' => $options['temperature'] ?? 0.7,
                'max_tokens' => $options['max_tokens'] ?? 1024,
                'request_type' => $options['request_type'] ?? 'content',
                'request_source' => $options['request_source'] ?? null,
                'additional_params' => $additionalParams,
                'status' => 'pending'
            ];
            
            error_log("OpenRouterRequest: Prepared data for insert: " . json_encode($data));
            $result = $this->insert($data);
            error_log("OpenRouterRequest: Insert result: " . $result);
            return $result;
        } catch (\Exception $e) {
            error_log("OpenRouterRequest Error: " . $e->getMessage());
            error_log("OpenRouterRequest Stack trace: " . $e->getTraceAsString());
            throw $e; // Re-throw to be caught by the caller
        }
    }
    
    /**
     * Update a request record with the API response
     * 
     * @param int $requestId The request ID to update
     * @param array $response The API response data
     * @param int $duration Request duration in milliseconds
     * @return bool Success status
     */
    public function updateWithResponse($requestId, $response, $duration = null) {
        try {
            error_log("OpenRouterRequest updateWithResponse - Starting for request ID: " . $requestId);
            error_log("OpenRouterRequest updateWithResponse - Full response: " . json_encode($response));
            
            // Extract relevant data from the response
            $responseText = '';
            
            // Try different response formats
            if (isset($response['choices'][0]['message']['content'])) {
                $responseText = $response['choices'][0]['message']['content'];
                error_log("OpenRouterRequest updateWithResponse - Using content from choices[0]['message']['content']");
            } 
            elseif (isset($response['choices'][0]['text'])) {
                $responseText = $response['choices'][0]['text'];
                error_log("OpenRouterRequest updateWithResponse - Using content from choices[0]['text']");
            }
            elseif (isset($response['choices'][0]['content'])) {
                $responseText = $response['choices'][0]['content'];
                error_log("OpenRouterRequest updateWithResponse - Using content from choices[0]['content']");
            }
            elseif (isset($response['choices']) && !empty($response['choices'])) {
                // If we can't find a standard content field, use the whole choice as JSON
                $responseText = json_encode($response['choices'][0]);
                error_log("OpenRouterRequest updateWithResponse - No standard content field found, using JSON of first choice");
            }
            else {
                // Last resort - use the entire response
                $responseText = json_encode($response);
                error_log("OpenRouterRequest updateWithResponse - No choices array found, using JSON of full response");
            }
            
            // Make sure we have a non-empty response text
            if (empty($responseText)) {
                $responseText = "No content returned from API. Full response: " . json_encode($response);
                error_log("OpenRouterRequest updateWithResponse - Empty response text, using fallback message");
            }
            
            error_log("OpenRouterRequest updateWithResponse - Final responseText length: " . strlen($responseText));
            error_log("OpenRouterRequest updateWithResponse - Extracted responseText: " . substr($responseText, 0, 100));
            
            $usage = $response['usage'] ?? [];
            
            // Calculate cost
            $cost = 0;
            if (!empty($response['usage'])) {
                $model = $response['model'] ?? '';
                $promptTokens = $response['usage']['prompt_tokens'] ?? 0;
                $completionTokens = $response['usage']['completion_tokens'] ?? 0;
                
                // Simple cost calculation based on model
                if (strpos($model, 'claude-3-opus') !== false) {
                    $cost = ($promptTokens / 1000 * 0.015) + ($completionTokens / 1000 * 0.075);
                } elseif (strpos($model, 'claude-3.7-sonnet') !== false) {
                    $cost = ($promptTokens / 1000 * 0.003) + ($completionTokens / 1000 * 0.015);
                } elseif (strpos($model, 'claude-3.5-sonnet') !== false) {
                    $cost = ($promptTokens / 1000 * 0.0015) + ($completionTokens / 1000 * 0.006);
                } elseif (strpos($model, 'claude-3-haiku') !== false) {
                    $cost = ($promptTokens / 1000 * 0.00025) + ($completionTokens / 1000 * 0.00125);
                } elseif (strpos($model, 'gpt-4o-mini') !== false) {
                    $cost = ($promptTokens / 1000 * 0.00015) + ($completionTokens / 1000 * 0.0006);
                } elseif (strpos($model, 'deepseek-r1:free') !== false) {
                    $cost = 0;
                } else {
                    $cost = ($promptTokens + $completionTokens) / 1000 * 0.001; // Default
                }
            }
            
            $data = [
                'response_text' => $responseText,
                'completion_tokens' => $usage['completion_tokens'] ?? null,
                'prompt_tokens' => $usage['prompt_tokens'] ?? null,
                'total_tokens' => $usage['total_tokens'] ?? null,
                'cost' => $cost,
                'request_duration' => $duration,
                'status' => 'completed',
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            error_log("OpenRouterRequest updateWithResponse - Prepared update data: " . json_encode(array_keys($data)));
            
            $result = $this->update($requestId, $data);
            error_log("OpenRouterRequest updateWithResponse - Update result: " . ($result ? 'success' : 'failure'));
            
            return $result;
        } catch (\Exception $e) {
            error_log("OpenRouterRequest updateWithResponse - Error: " . $e->getMessage());
            error_log("OpenRouterRequest updateWithResponse - Stack trace: " . $e->getTraceAsString());
            
            // Try to update with error information
            try {
                $this->update($requestId, [
                    'response_text' => "Error processing response: " . $e->getMessage(),
                    'status' => 'completed',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } catch (\Exception $innerEx) {
                error_log("OpenRouterRequest updateWithResponse - Failed to update with error: " . $innerEx->getMessage());
            }
            
            return false;
        }
    }
    
    /**
     * Mark a request as failed with an error message
     * 
     * @param int $requestId The request ID to update
     * @param string $errorMessage The error message
     * @return bool Success status
     */
    public function markAsFailed($requestId, $errorMessage) {
        $data = [
            'error_message' => $errorMessage,
            'status' => 'failed',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->update($requestId, $data);
    }
    
    /**
     * Get usage statistics
     * 
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @return array Usage statistics
     */
    public function getUsageStats($startDate = null, $endDate = null) {
        $whereClause = "status = 'completed'";
        $params = [];
        
        if ($startDate) {
            $whereClause .= " AND created_at >= :start_date";
            $params[':start_date'] = $startDate . ' 00:00:00';
        }
        
        if ($endDate) {
            $whereClause .= " AND created_at <= :end_date";
            $params[':end_date'] = $endDate . ' 23:59:59';
        }
        
        // Get total stats
        $sqlTotal = "SELECT 
                COUNT(*) as total_requests,
                SUM(total_tokens) as total_tokens,
                SUM(cost) as total_cost,
                AVG(request_duration) as avg_duration
            FROM {$this->table} 
            WHERE {$whereClause}";
            
        $stmt = $this->db->prepare($sqlTotal);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $overallStats = $stmt->fetch();
        
        // Get stats by model
        $sqlByModel = "SELECT model, 
                COUNT(*) as request_count, 
                SUM(total_tokens) as total_tokens,
                SUM(cost) as total_cost
            FROM {$this->table} 
            WHERE {$whereClause}
            GROUP BY model
            ORDER BY request_count DESC";
            
        $stmt = $this->db->prepare($sqlByModel);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $modelStats = $stmt->fetchAll();
        
        // Get stats by request type
        $sqlByType = "SELECT request_type, 
                COUNT(*) as request_count, 
                SUM(total_tokens) as total_tokens,
                SUM(cost) as total_cost
            FROM {$this->table} 
            WHERE {$whereClause}
            GROUP BY request_type
            ORDER BY request_count DESC";
            
        $stmt = $this->db->prepare($sqlByType);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $typeStats = $stmt->fetchAll();
        
        return [
            'overall' => $overallStats,
            'by_model' => $modelStats,
            'by_type' => $typeStats
        ];
    }
}