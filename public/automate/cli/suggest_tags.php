#!/usr/bin/env php
<?php
/**
 * Suggest tags for untagged keywords using OpenRouter API
 * 
 * This CLI script identifies keywords without assigned tags,
 * sends requests to OpenRouter to suggest appropriate tags with confidence scores,
 * and optionally assigns the tags if they meet the minimum confidence threshold.
 */

// Define the application path
define('APP_PATH', __DIR__ . '/..');

// Set up autoloading
spl_autoload_register(function ($className) {
    // Convert namespace separators to directory separators
    $className = str_replace('\\', '/', $className);
    $filePath = APP_PATH . '/' . $className . '.php';
    
    if (file_exists($filePath)) {
        require_once $filePath;
        return true;
    }
    return false;
});

// Initialize bootstrap
require_once APP_PATH . '/Core/Bootstrap.php';
\Core\Bootstrap::init();

// Import required classes
use Utils\OpenRouter;
use Models\Keyword;
use Models\Tag;

// Parse command line options
$options = getopt('', ['limit::', 'dry-run', 'min-confidence::', 'batch-size::']);
$limit = isset($options['limit']) ? (int)$options['limit'] : 100; // Default to 100
$dryRun = isset($options['dry-run']);
$minConfidence = isset($options['min-confidence']) ? (float)$options['min-confidence'] : 0.7; // Default confidence threshold
$batchSize = isset($options['batch-size']) ? (int)$options['batch-size'] : 5; // Default batch size of 5 keywords

echo "-----------------------------------------------------\n";
echo "Keyword Tag Suggestion Tool\n";
echo "-----------------------------------------------------\n";
echo "Settings:\n";
echo "- Limit: $limit keyword(s)\n";
echo "- Batch size: $batchSize keyword(s) per request\n";
echo "- Minimum confidence: " . ($minConfidence * 100) . "%\n";
echo "- Dry run mode: " . ($dryRun ? "Yes (no database changes)" : "No (will update database)") . "\n";
echo "-----------------------------------------------------\n\n";

// Load statistics
$statsFile = __DIR__ . '/tag_suggestion_stats.json';
$stats = loadStats($statsFile);
echo "Total tags assigned to date: " . $stats['total_assigned'] . "\n";
echo "Last run: " . ($stats['last_run'] ? date('Y-m-d H:i:s', $stats['last_run']) : 'Never') . "\n\n";

// Initialize database connection
try {
    global $pdo;
    if (!isset($pdo) || !($pdo instanceof \PDO)) {
        throw new \Exception("No database connection available");
    }
    
    echo "✓ Database connection established\n";
} catch (\Exception $e) {
    echo "Error: Failed to connect to the database: " . $e->getMessage() . "\n";
    exit(1);
}

// Initialize models
$keywordModel = new Keyword($pdo);
$tagModel = new Tag($pdo);
$openRouter = new OpenRouter();

// Get untagged keywords
$untaggedKeywords = getUntaggedKeywords($pdo, $limit);

echo "Found " . count($untaggedKeywords) . " untagged keywords\n";

if (empty($untaggedKeywords)) {
    echo "No untagged keywords found. Exiting.\n";
    exit(0);
}

// Get all available tags
$tags = $tagModel->getList(1000, 0);

if (empty($tags)) {
    echo "Error: No tags found in the database. Please create tags first.\n";
    exit(1);
}

// Track successful assignments for statistics
$assignedCount = 0;

// Group keywords into batches
$keywordBatches = array_chunk($untaggedKeywords, $batchSize);

// Process each batch of keywords
foreach ($keywordBatches as $batchIndex => $keywordBatch) {
    echo "\nProcessing batch " . ($batchIndex + 1) . " of " . count($keywordBatches) . " (" . count($keywordBatch) . " keywords)\n";
    
    // Display keywords in this batch
    foreach ($keywordBatch as $k) {
        echo "  - Keyword: " . $k['keyword'] . " (ID: " . $k['id'] . ")\n";
    }
    
    // Get suggestions for all keywords in the batch
    $suggestions = suggestTagsForKeywords($openRouter, $keywordBatch, $tags);
    
    if (!$suggestions) {
        echo "  ✗ Failed to get tag suggestions for this batch\n";
        continue;
    }
    
    // Process each suggestion
    foreach ($suggestions as $suggestion) {
        $keywordId = $suggestion['keyword_id'];
        
        // Find the corresponding keyword in the batch
        $keywordKey = array_search($keywordId, array_column($keywordBatch, 'id'));
        if ($keywordKey === false) {
            echo "  ✗ Error: Received suggestion for unknown keyword ID: $keywordId\n";
            continue;
        }
        
        $keyword = $keywordBatch[$keywordKey];
        
        echo "\n  Processing suggestion for: " . $keyword['keyword'] . " (ID: " . $keyword['id'] . ")\n";
        echo "  Suggested tag: " . $suggestion['tag_name'] . " (ID: " . $suggestion['tag_id'] . ")\n";
        echo "  Confidence: " . number_format($suggestion['confidence'] * 100, 2) . "%\n";
        
        // Check if confidence meets minimum threshold
        if ($suggestion['confidence'] >= $minConfidence) {
            echo "  Confidence meets minimum threshold of " . number_format($minConfidence * 100, 2) . "%\n";
            
            if (!$dryRun) {
                // Update the keyword with the suggested tag
                $result = $keywordModel->updateKeyword($keyword['id'], $keyword['keyword'], $suggestion['tag_id']);
                
                if ($result) {
                    echo "  ✓ Successfully assigned tag '" . $suggestion['tag_name'] . "' to keyword '" . $keyword['keyword'] . "'\n";
                    $assignedCount++;
                    
                    // Update keyword status to approved
                    try {
                        $stmt = $pdo->prepare("
                            UPDATE keywords 
                            SET status = 'approved' 
                            WHERE id = :id
                        ");
                        $stmt->bindValue(':id', $keyword['id'], PDO::PARAM_INT);
                        $stmt->execute();
                    } catch (\PDOException $e) {
                        echo "  ✗ Error updating keyword status to approved: " . $e->getMessage() . "\n";
                    }
                } else {
                    echo "  ✗ Failed to update keyword with suggested tag\n";
                }
            } else {
                echo "  ℹ Dry run mode - not updating database\n";
            }
        } else {
            echo "  ✗ Confidence below minimum threshold of " . number_format($minConfidence * 100, 2) . "% - not assigning tag\n";
            
            if (!$dryRun) {
                // Update the keyword status to "rejected"
                try {
                    $stmt = $pdo->prepare("
                        UPDATE keywords 
                        SET status = 'rejected' 
                        WHERE id = :id
                    ");
                    $stmt->bindValue(':id', $keyword['id'], PDO::PARAM_INT);
                    $stmt->execute();
                    
                    if ($stmt->rowCount() > 0) {
                        echo "  ✓ Marked keyword as 'rejected' due to low confidence\n";
                    } else {
                        echo "  ✗ Failed to update keyword status\n";
                    }
                } catch (\PDOException $e) {
                    echo "  ✗ Error updating keyword status: " . $e->getMessage() . "\n";
                }
            } else {
                echo "  ℹ Dry run mode - would mark keyword as 'rejected'\n";
            }
        }
    }
}

// Update statistics
if (!$dryRun) {
    $stats['total_assigned'] += $assignedCount;
    $stats['last_run'] = time();
    $stats['runs'][] = [
        'date' => time(),
        'assigned' => $assignedCount,
        'processed' => count($untaggedKeywords),
        'min_confidence' => $minConfidence
    ];
    saveStats($statsFile, $stats);
}

echo "\nCompleted processing " . count($untaggedKeywords) . " untagged keywords\n";
echo "Tags assigned this run: " . $assignedCount . "\n";
echo "Total tags assigned to date: " . $stats['total_assigned'] . "\n";

/**
 * Get keywords without assigned tags
 * 
 * @param \PDO $db Database connection
 * @param int $limit Maximum number of keywords to retrieve
 * @return array Array of untagged keywords
 */
function getUntaggedKeywords($db, $limit = 10) {
    try {
        $stmt = $db->prepare("
            SELECT id, keyword 
            FROM keywords 
            WHERE tag_id IS NULL 
            AND (status IS NULL OR status != 'rejected')
            ORDER BY created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        echo "Error: Failed to retrieve untagged keywords: " . $e->getMessage() . "\n";
        return [];
    }
}

/**
 * Suggest tags for multiple keywords using OpenRouter in a single request
 * 
 * @param OpenRouter $openRouter The OpenRouter client
 * @param array $keywords The array of keyword data
 * @param array $tags Available tags
 * @return array|null The array of suggested tags with confidence scores or null on failure
 */
function suggestTagsForKeywords($openRouter, $keywords, $tags) {
    // First, identify parent categories (tags without parent_id)
    $parentCategories = [];
    $childTags = [];
    
    foreach ($tags as $tag) {
        if (empty($tag['parent_id'])) {
            $parentCategories[$tag['id']] = $tag;
        } else {
            $childTags[$tag['id']] = $tag;
        }
    }
    
    // Create a hierarchical structure with nested tags
    $tagStructure = [
        'categories' => []
    ];
    
    // Internal mapping of category names to their actual IDs (not exposed to AI)
    $categoryIdMap = [];
    
    // Add parent categories with their child tags
    foreach ($parentCategories as $categoryId => $category) {
        // Store the actual category ID in our internal map
        $categoryIdMap[$category['name']] = (int)$categoryId;
        
        $categoryData = [
            // No ID for categories in the JSON to prevent AI from selecting them
            'name' => $category['name'],
            'description' => $category['description'] ?? '',
            'is_assignable' => false, // Categories are never assignable now
            'tags' => [] // Child tags will be nested here
        ];
        
        // Find and add all child tags for this category
        foreach ($childTags as $tagId => $tag) {
            if ((int)$tag['parent_id'] === (int)$categoryId) {
                $tagData = [
                    'id' => (int)$tagId,
                    'name' => $tag['name'],
                    'description' => $tag['description'] ?? '',
                    'is_assignable' => true
                ];
                
                $categoryData['tags'][] = $tagData;
            }
        }
        
        $tagStructure['categories'][] = $categoryData;
    }
    
    // Convert to JSON
    $tagsJson = json_encode($tagStructure, JSON_PRETTY_PRINT);
    
    // Create a JSON array of keywords
    $keywordsArray = [];
    foreach ($keywords as $keyword) {
        $keywordsArray[] = [
            'id' => (int)$keyword['id'],
            'keyword' => $keyword['keyword']
        ];
    }
    $keywordsJson = json_encode($keywordsArray, JSON_PRETTY_PRINT);
    
    // Create the prompt for OpenRouter
    $prompt = "I need to categorize multiple keywords into the most appropriate tags. Please analyze and determine which tag is most appropriate for each keyword, providing your confidence level (0.0 to 1.0) for each.\n\n";
    $prompt .= "Keywords to categorize:\n";
    $prompt .= $keywordsJson . "\n\n";
    $prompt .= "Available tag structure (JSON):\n";
    $prompt .= $tagsJson . "\n\n";
    $prompt .= "Instructions:\n";
    $prompt .= "1. You can only choose items where 'is_assignable' is true\n";
    $prompt .= "2. The structure shows categories (which are not assignable) containing their related tags (which are assignable)\n";
    $prompt .= "3. Select the most appropriate tag for EACH keyword - you must choose a tag that has an ID\n";
    $prompt .= "4. Provide a confidence score between 0.0 and 1.0 for each suggestion\n\n";
    
    $prompt .= "Please respond in JSON format with the following structure:\n";
    $prompt .= "{\n";
    $prompt .= "  \"suggestions\": [\n";
    $prompt .= "    {\n";
    $prompt .= "      \"keyword_id\": [id of the keyword as an integer],\n";
    $prompt .= "      \"tag_id\": [id of the selected tag as an integer],\n";
    $prompt .= "      \"tag_name\": [name of the selected tag],\n";
    $prompt .= "      \"confidence\": [confidence score from 0.0 to 1.0]\n";
    $prompt .= "    },\n";
    $prompt .= "    {\n";
    $prompt .= "      ... repeat for each keyword\n";
    $prompt .= "    }\n";
    $prompt .= "  ]\n";
    $prompt .= "}\n";
    
    try {
        echo "  ℹ Sending batch request to OpenRouter API...\n";
        
        // Generate suggestions using the content model
        $response = $openRouter->generate($prompt, 'content', [
            'request_source' => 'tag_suggestion_cli',
            'temperature' => 0.1 // Lower temperature for more consistent results
        ]);
        
        // Extract content from the response
        $content = $openRouter->extractContent($response);
        
        if (empty($content)) {
            throw new \Exception("Empty response from OpenRouter");
        }
        
        // Try to extract JSON from the response
        $jsonMatches = [];
        if (preg_match('/\{.*\}/s', $content, $jsonMatches)) {
            $jsonString = $jsonMatches[0];
            $suggestionData = json_decode($jsonString, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Failed to parse JSON: " . json_last_error_msg());
            }
            
            // Validate the suggestion format
            if (!isset($suggestionData['suggestions']) || !is_array($suggestionData['suggestions'])) {
                throw new \Exception("Invalid suggestion format: missing 'suggestions' array");
            }
            
            $suggestions = $suggestionData['suggestions'];
            
            // Validate each suggestion
            foreach ($suggestions as &$suggestion) {
                if (!isset($suggestion['keyword_id']) || !isset($suggestion['tag_id']) || 
                    !isset($suggestion['tag_name']) || !isset($suggestion['confidence'])) {
                    throw new \Exception("Invalid suggestion format: missing required fields");
                }
                
                // Ensure IDs are integers
                $suggestion['keyword_id'] = (int)$suggestion['keyword_id'];
                $suggestion['tag_id'] = (int)$suggestion['tag_id'];
                
                // Verify the keyword exists in our batch
                $keywordExists = false;
                foreach ($keywords as $keyword) {
                    if ((int)$keyword['id'] === $suggestion['keyword_id']) {
                        $keywordExists = true;
                        break;
                    }
                }
                
                if (!$keywordExists) {
                    throw new \Exception("Suggestion contains unknown keyword ID: {$suggestion['keyword_id']}");
                }
                
                // Verify the suggested tag exists and is assignable
                $tagExists = false;
                
                // Check in child tags of all categories
                foreach ($tagStructure['categories'] as $category) {
                    foreach ($category['tags'] as $tag) {
                        if ($tag['id'] === $suggestion['tag_id'] && $tag['is_assignable']) {
                            $tagExists = true;
                            break 2;
                        }
                    }
                }
                
                if (!$tagExists) {
                    throw new \Exception("Suggested tag ID {$suggestion['tag_id']} does not exist or is not assignable");
                }
            }
            
            return $suggestions;
        } else {
            throw new \Exception("Could not find valid JSON in the response");
        }
    } catch (\Exception $e) {
        echo "  ✗ Error getting tag suggestions: " . $e->getMessage() . "\n";
        return null;
    }
}

/**
 * Suggest a tag for a single keyword using OpenRouter
 * 
 * @param OpenRouter $openRouter The OpenRouter client
 * @param array $keyword The keyword data
 * @param array $tags Available tags
 * @return array|null The suggested tag with confidence score or null on failure
 */
function suggestTagForKeyword($openRouter, $keyword, $tags) {
    // Create a batch with just this one keyword
    $suggestions = suggestTagsForKeywords($openRouter, [$keyword], $tags);
    
    // Return the first (and only) suggestion if available
    if (is_array($suggestions) && !empty($suggestions)) {
        return $suggestions[0];
    }
    
    return null;
}

/**
 * Load statistics from file
 *
 * @param string $statsFile Path to the stats file
 * @return array Statistics data
 */
function loadStats($statsFile) {
    $defaultStats = [
        'total_assigned' => 0,
        'last_run' => null,
        'runs' => []
    ];
    
    if (file_exists($statsFile)) {
        $statsData = file_get_contents($statsFile);
        $stats = json_decode($statsData, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $defaultStats;
        }
        
        return $stats;
    }
    
    return $defaultStats;
}

/**
 * Save statistics to file
 *
 * @param string $statsFile Path to the stats file
 * @param array $stats Statistics data to save
 * @return bool Success status
 */
function saveStats($statsFile, $stats) {
    $statsData = json_encode($stats, JSON_PRETTY_PRINT);
    return file_put_contents($statsFile, $statsData) !== false;
} 