<?php
/**
 * AI Generation Controller
 * 
 * Handles AI content generation using OpenRouter
 */

namespace Controllers;

use Core\Controller;
use Utils\OpenRouter;

class AiGenerationController extends Controller {
    private $openRouter;
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        
        // Initialize OpenRouter
        try {
            $this->openRouter = new OpenRouter();
        } catch (\Exception $e) {
            $this->addAlert('error', 'OpenRouter configuration error: ' . $e->getMessage());
        }
    }
    
    /**
     * Index/Form Page
     * 
     * @return void
     */
    public function index() {
        // Get any previously generated content from session
        $generatedContent = $_SESSION['generated_content'] ?? '';
        $generationType = $_SESSION['generation_type'] ?? '';
        $prompt = $_SESSION['prompt'] ?? '';
        
        // Clear session variables if not needed
        unset($_SESSION['generated_content']);
        unset($_SESSION['generation_type']);
        unset($_SESSION['prompt']);
        
        // Get alerts
        $alerts = $this->getAlerts();
        
        // Render the view
        $this->render('ai/index', [
            'pageTitle' => 'AI Content Generator',
            'pageHeader' => 'AI Content Generator',
            'generatedContent' => $generatedContent,
            'generationType' => $generationType,
            'prompt' => $prompt,
            'alerts' => $alerts
        ]);
    }
    
    /**
     * Generate Content
     * 
     * @return void
     */
    public function generate() {
        // Get parameters
        $generationType = $_POST['type'] ?? 'summary';
        $prompt = $_POST['prompt'] ?? '';
        
        // Validate input
        if (empty($prompt)) {
            $this->addAlert('warning', 'Please provide a prompt for generation.');
            $this->redirect('ai-generation');
            return;
        }
        
        // Determine OpenRouter model type and construct prompt
        switch ($generationType) {
            case 'summary':
                $modelType = 'content';
                $aiPrompt = "Summarize the following text concisely: $prompt";
                break;
                
            case 'expand':
                $modelType = 'content';
                $aiPrompt = "Expand on the following idea with more details: $prompt";
                break;
                
            case 'analyze':
                $modelType = 'research';
                $aiPrompt = "Analyze the following in detail, providing insights and observations: $prompt";
                break;
                
            case 'keyword':
                $modelType = 'content';
                $aiPrompt = "Extract 5-10 important keywords from the following text: $prompt";
                break;
                
            default:
                $modelType = 'content';
                $aiPrompt = $prompt;
        }
        
        try {
            // Generate content
            $response = $this->openRouter->generate($aiPrompt, $modelType);
            $generatedContent = $this->openRouter->extractContent($response);
            
            // Store in session for display
            $_SESSION['generated_content'] = $generatedContent;
            $_SESSION['generation_type'] = $generationType;
            $_SESSION['prompt'] = $prompt;
            
            // Add success message
            $this->addAlert('success', 'Content generated successfully!');
            
        } catch (\Exception $e) {
            // Handle error
            $this->addAlert('error', 'Error generating content: ' . $e->getMessage());
        }
        
        // Redirect back to form
        $this->redirect('ai-generation');
    }
} 