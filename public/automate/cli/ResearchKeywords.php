<?php
/**
 * ResearchKeywords Class
 * 
 * A class for generating research content for keywords using AI
 * Can be used as a standalone CLI or included in other scripts
 */

namespace cli;

use Utils\OpenRouter;

class ResearchKeywords {
    private $pdo;
    private $researchModel;
    private $openRouter;
    private $dryRun = false;
    private $format = 'json';
    
    /**
     * Constructor
     *
     * @param \PDO $pdo Database connection
     * @param bool $dryRun Whether to run in dry-run mode
     * @param string $format Output format (json or text)
     */
    public function __construct($pdo, $dryRun = false, $format = 'json') {
        $this->pdo = $pdo;
        $this->dryRun = $dryRun;
        $this->format = $format;
        
        // Load research model from environment
        $this->loadResearchModel();
        
        // Initialize OpenRouter
        $this->openRouter = new OpenRouter();
    }
    
    /**
     * Load research model from environment
     */
    private function loadResearchModel() {
        $this->researchModel = getenv('OPENROUTER_RESEARCH_MODEL');
        if (!$this->researchModel) {
            echo "Warning: OPENROUTER_RESEARCH_MODEL not found in environment variables. Using default model.\n";
            $this->researchModel = 'anthropic/claude-3.7-sonnet';
        } else {
            echo "✓ Using research model: {$this->researchModel} from environment variables\n";
        }
    }
    
    /**
     * Ensure the research column exists in the keywords table
     */
    public function ensureResearchColumnExists() {
        try {
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM keywords LIKE 'research'");
            $stmt->execute();
            if ($stmt->rowCount() === 0) {
                echo "Research column doesn't exist, creating it...\n";
                $this->pdo->exec("ALTER TABLE keywords ADD COLUMN research TEXT");
                echo "✓ Research column created\n";
            } else {
                echo "✓ Research column already exists\n";
            }
            return true;
        } catch (\Exception $e) {
            echo "Error checking or creating research column: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Get tag information for better context
     *
     * @param int $tagId Tag ID
     * @return array Tag information
     */
    private function getTagInfo($tagId) {
        try {
            $stmt = $this->pdo->prepare("SELECT id, name, description, slug, color FROM tags WHERE id = ?");
            $stmt->bindValue(1, $tagId);
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            echo "Warning: Couldn't fetch tag info: " . $e->getMessage() . "\n";
            return ['name' => 'Unknown', 'description' => ''];
        }
    }
    
    /**
     * Load and prepare the prompt template
     *
     * @param string $keyword Keyword to research
     * @param string $tagName Tag name
     * @param string $tagDescription Tag description
     * @param string $format Output format (json or text)
     * @return string Prepared prompt
     */
    private function loadPromptTemplate($keyword, $tagName, $tagDescription, $format = 'json') {
        $templateFile = ($format === 'text') ? 'research_prompt.tpl' : 'json_research_prompt.tpl';
        $templatePath = __DIR__ . '/templates/' . $templateFile;
        
        if (!file_exists($templatePath)) {
            throw new \Exception("Template file not found: $templatePath");
        }
        
        // Load the template
        $template = file_get_contents($templatePath);
        
        // Replace placeholders
        $replacements = [
            '{{keyword}}' => $keyword,
            '{{tag_name}}' => $tagName,
            '{{tag_description}}' => $tagDescription
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
    
    /**
     * Validate JSON structure
     *
     * @param string $json JSON string to validate
     * @return array Validation result
     */
    private function validateJson($json) {
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'valid' => false,
                'error' => 'Invalid JSON: ' . json_last_error_msg()
            ];
        }
        
        // Check for required structure
        if (!isset($data['key_points']) || !is_array($data['key_points'])) {
            return [
                'valid' => false,
                'error' => 'Missing or invalid key_points array'
            ];
        }
        
        // Check if we have exactly 6 key points
        if (count($data['key_points']) !== 6) {
            return [
                'valid' => false,
                'error' => 'Expected exactly 6 key points, found ' . count($data['key_points'])
            ];
        }
        
        // Check each key point for required structure
        foreach ($data['key_points'] as $index => $point) {
            if (!isset($point['title']) || !isset($point['summary']) || !isset($point['research_points'])) {
                return [
                    'valid' => false,
                    'error' => "Key point #{$index} is missing required fields"
                ];
            }
            
            if (!is_array($point['research_points'])) {
                return [
                    'valid' => false,
                    'error' => "Key point #{$index} has invalid research_points (not an array)"
                ];
            }
            
            if (count($point['research_points']) < 2 || count($point['research_points']) > 4) {
                return [
                    'valid' => false,
                    'error' => "Key point #{$index} should have 2-4 research points, found " . count($point['research_points'])
                ];
            }
        }
        
        return [
            'valid' => true,
            'data' => $data
        ];
    }
    
    /**
     * Clean citation references from JSON research
     *
     * @param string $json JSON string to clean
     * @return string Cleaned JSON string
     */
    private function cleanCitationReferences($json) {
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // If JSON is invalid, try to clean citation references from the raw text
            return preg_replace('/\[\d+\]/', '', $json);
        }
        
        // Clean citations from key points
        foreach ($data['key_points'] as &$point) {
            // Clean title
            $point['title'] = preg_replace('/\[\d+\]/', '', $point['title']);
            
            // Clean summary
            $point['summary'] = preg_replace('/\[\d+\]/', '', $point['summary']);
            
            // Clean each research point
            foreach ($point['research_points'] as &$research_point) {
                $research_point = preg_replace('/\[\d+\]/', '', $research_point);
            }
        }
        
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
    
    /**
     * Process a single keyword
     *
     * @param int $keywordId Keyword ID to process
     * @return array|bool Result array or false on failure
     */
    public function processKeywordById($keywordId) {
        // Get keyword data
        try {
            $stmt = $this->pdo->prepare("SELECT id, keyword, tag_id FROM keywords WHERE id = ?");
            $stmt->bindValue(1, $keywordId);
            $stmt->execute();
            $keyword = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$keyword) {
                echo "Error: Keyword with ID {$keywordId} not found.\n";
                return false;
            }
            
            return $this->processKeyword($keyword);
        } catch (\Exception $e) {
            echo "Error fetching keyword: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Process a keyword
     *
     * @param array $keyword Keyword data array
     * @return array|bool Result array or false on failure
     */
    public function processKeyword($keyword) {
        $id = $keyword['id'];
        $name = $keyword['keyword'];
        $tagId = $keyword['tag_id'];
        
        echo "\nProcessing keyword: $name (ID: $id, Tag ID: $tagId)\n";
        
        // Get tag information for better context
        $tag = $this->getTagInfo($tagId);
        $tagName = $tag ? $tag['name'] : 'Unknown';
        $tagDescription = $tag ? $tag['description'] : '';
        
        echo "  Tag: $tagName\n";
        
        // Generate research using OpenRouter
        try {
            // Load the prompt template
            $prompt = $this->loadPromptTemplate($name, $tagName, $tagDescription, $this->format);
            
            echo "  Generating research using AI in {$this->format} format...\n";
            
            // Use the research model that was checked earlier
            echo "  Using model: {$this->researchModel}\n";
            
            // Create options array without model to prevent conflict
            $options = [
                'temperature' => 0.7,
                'request_source' => 'research_keywords_cli'
            ];
            
            // Pass the model type instead of explicitly setting the model
            $response = $this->openRouter->generate($prompt, 'research', $options);
            
            // Extract the generated content
            $research = $this->openRouter->extractContent($response);
            
            if (empty($research)) {
                throw new \Exception("Empty research content returned from AI");
            }
            
            // For JSON format, validate and clean the response
            if ($this->format !== 'text') {
                // Extract the JSON part if it's wrapped in markdown code blocks
                if (preg_match('/```(?:json)?\s*({.*})\s*```/s', $research, $matches)) {
                    $research = $matches[1];
                }
                
                // Validate the JSON
                $validation = $this->validateJson($research);
                
                if (!$validation['valid']) {
                    echo "  Warning: " . $validation['error'] . "\n";
                    echo "  Attempting to fix JSON format issues...\n";
                    
                    // Try to clean up the JSON
                    $research = preg_replace('/^[^{]*({.*})[^}]*$/s', '$1', $research);
                    
                    // Validate again
                    $validation = $this->validateJson($research);
                    if (!$validation['valid']) {
                        throw new \Exception("Failed to get valid JSON: " . $validation['error']);
                    } else {
                        echo "  ✓ JSON validation successful after cleanup\n";
                    }
                } else {
                    echo "  ✓ JSON validation successful\n";
                }
                
                // Clean citation references
                echo "  Cleaning citation references...\n";
                $research = $this->cleanCitationReferences($research);
            }
            
            // Show a preview of the research
            $preview = substr(trim($research), 0, 150) . (strlen($research) > 150 ? '...' : '');
            echo "  Research preview: $preview\n";
            
            // Update the keyword with the research
            if (!$this->dryRun) {
                $updateStmt = $this->pdo->prepare("UPDATE keywords SET research = ?, updated_at = NOW() WHERE id = ?");
                $updateStmt->bindValue(1, $research);
                $updateStmt->bindValue(2, $id);
                $updateStmt->execute();
                echo "  ✓ Research saved to database\n";
            } else {
                echo "  (Dry run) - Research not saved to database\n";
            }
            
            // Return the result
            return [
                'keyword_id' => $id,
                'keyword' => $name,
                'tag_id' => $tagId,
                'tag_name' => $tagName,
                'research' => $research,
                'success' => true
            ];
            
        } catch (\Exception $e) {
            echo "  Error generating research: " . $e->getMessage() . "\n";
            return [
                'keyword_id' => $id,
                'keyword' => $name,
                'error' => $e->getMessage(),
                'success' => false
            ];
        }
    }
    
    /**
     * Find and process keywords
     *
     * @param array|null $keywordIds Specific keyword IDs to process
     * @param int|null $tagId Specific tag ID to filter by
     * @param int|null $limit Limit the number of keywords to process
     * @return array Results for each keyword
     */
    public function findAndProcessKeywords($keywordIds = null, $tagId = null, $limit = null) {
        // Ensure research column exists
        if (!$this->ensureResearchColumnExists()) {
            return false;
        }
        
        // Build the query to get keywords
        $query = "SELECT id, keyword, tag_id FROM keywords WHERE tag_id IS NOT NULL";
        $params = [];
        
        // Add specific keyword IDs if provided
        if ($keywordIds) {
            $placeholders = implode(',', array_fill(0, count($keywordIds), '?'));
            $query .= " AND id IN ($placeholders)";
            $params = array_merge($params, $keywordIds);
        }
        
        // Add specific tag ID if provided
        if ($tagId) {
            $query .= " AND tag_id = ?";
            $params[] = $tagId;
        }
        
        // Filter out keywords that already have research
        $query .= " AND (research IS NULL OR research = '')";
        
        // Add limit if provided
        if ($limit) {
            $query .= " LIMIT ?";
            $params[] = $limit;
        }
        
        // Prepare and execute the query
        try {
            $stmt = $this->pdo->prepare($query);
            foreach ($params as $i => $param) {
                $stmt->bindValue($i + 1, $param);
            }
            $stmt->execute();
            $keywords = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $count = count($keywords);
            echo "Found $count keywords to process\n";
            
            if ($count === 0) {
                echo "No keywords to process. Exiting.\n";
                return [];
            }
            
            // Process each keyword
            $results = [];
            foreach ($keywords as $keyword) {
                $result = $this->processKeyword($keyword);
                if ($result) {
                    $results[] = $result;
                }
            }
            
            echo "\n-----------------------------------------------------\n";
            echo "Process completed: " . count($results) . " keywords processed\n";
            if ($this->dryRun) {
                echo "Dry run - No database changes were made\n";
            }
            echo "-----------------------------------------------------\n";
            
            return $results;
            
        } catch (\Exception $e) {
            echo "Error fetching keywords: " . $e->getMessage() . "\n";
            return false;
        }
    }
} 