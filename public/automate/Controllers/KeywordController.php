<?php
/**
 * Keyword Controller
 * 
 * Handles keyword management
 */

namespace Controllers;

use Core\Controller;
use Models\Keyword;

class KeywordController extends Controller {
    private $keywordModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->keywordModel = new Keyword($this->db);
    }
    
    /**
     * List keywords
     *
     * @return void
     */
    public function index() {
        try {
            // Get page, status, and search parameters
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $status = isset($_GET['status']) ? $_GET['status'] : null;
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            
            // Get keywords with pagination
            $result = $this->keywordModel->getKeywords($page, 10, $status, $search);
            $keywords = $result['keywords'];
            $total = $result['total'];
            
            // Calculate total pages
            $totalPages = ceil($total / 10);
            
            // Page metadata
            $pageTitle = 'Keywords Management - Keywords Automation';
            $pageHeader = 'Keywords Management';
            
            // Get alerts
            $alerts = $this->getAlerts();
            
            // Render the view
            $this->render('keywords/index', [
                'pageTitle' => $pageTitle,
                'pageHeader' => $pageHeader,
                'keywords' => $keywords,
                'page' => $page,
                'totalPages' => $totalPages,
                'total' => $total,
                'status' => $status,
                'search' => $search,
                'alerts' => $alerts
            ]);
        } catch (\Exception $e) {
            // Log the exception
            error_log("Error in KeywordController::index: " . $e->getMessage());
            
            // Display error message and render empty view
            $this->addAlert('danger', 'Error loading keywords: ' . $e->getMessage());
            $this->render('keywords/index', [
                'pageTitle' => 'Keywords Management - Keywords Automation',
                'pageHeader' => 'Keywords Management - Error',
                'keywords' => [],
                'page' => 1,
                'totalPages' => 0,
                'total' => 0,
                'status' => null,
                'search' => '',
                'alerts' => $this->getAlerts()
            ]);
        }
    }
    
    /**
     * Process keyword actions (approve, reject, delete)
     *
     * @return void
     */
    public function processAction() {
        // Debug information
        error_log("KeywordController::processAction called");
        error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
        
        // Check if form was submitted
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("Not a POST request, redirecting to keywords");
            $this->redirect('keywords');
            return;
        }
        
        try {
            // Handle status updates
            if (isset($_POST['action']) && isset($_POST['keyword_id'])) {
                $keywordId = (int)$_POST['keyword_id'];
                $action = $_POST['action'];
                
                error_log("Processing action: $action for keyword ID: $keywordId");
                
                switch ($action) {
                    case 'approve':
                        $this->keywordModel->updateStatus($keywordId, 'approved');
                        $this->addAlert('success', 'Keyword approved successfully.');
                        break;
                    case 'pending':
                        $this->keywordModel->updateStatus($keywordId, 'pending');
                        $this->addAlert('warning', 'Keyword marked as pending.');
                        break;
                    case 'reject':
                        $this->keywordModel->updateStatus($keywordId, 'rejected');
                        $this->addAlert('danger', 'Keyword rejected.');
                        break;
                    case 'delete':
                        $this->keywordModel->delete($keywordId);
                        $this->addAlert('danger', 'Keyword deleted permanently.');
                        break;
                    case 'update':
                        if (isset($_POST['keyword_text'])) {
                            $keywordText = trim($_POST['keyword_text']);
                            $tagId = isset($_POST['tag_id']) && !empty($_POST['tag_id']) ? (int)$_POST['tag_id'] : null;
                            
                            if (empty($keywordText)) {
                                $this->addAlert('danger', 'Keyword text cannot be empty.');
                            } else {
                                $success = $this->keywordModel->updateKeyword($keywordId, $keywordText, $tagId);
                                if ($success) {
                                    $this->addAlert('success', 'Keyword updated successfully.');
                                } else {
                                    $this->addAlert('danger', 'Failed to update keyword.');
                                }
                            }
                        } else {
                            $this->addAlert('danger', 'Keyword text is required.');
                        }
                        break;
                }
            }
            
            // Handle new keyword submission
            if (isset($_POST['new_keyword'])) {
                $keyword = trim($_POST['new_keyword']);
                error_log("Adding new keyword: $keyword");
                
                if (empty($keyword)) {
                    $this->addAlert('danger', 'Keyword cannot be empty.');
                } else {
                    $id = $this->keywordModel->addKeyword($keyword);
                    if ($id) {
                        $this->addAlert('success', 'New keyword added successfully.');
                    } else {
                        $this->addAlert('danger', 'Failed to add new keyword.');
                    }
                }
            }
        } catch (\Exception $e) {
            error_log("Error in KeywordController::processAction: " . $e->getMessage());
            $this->addAlert('danger', 'Error: ' . $e->getMessage());
        }
        
        // Redirect back to keywords page
        error_log("Redirecting to keywords after processing");
        $this->redirect('keywords');
    }
} 