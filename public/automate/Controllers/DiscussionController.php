<?php
/**
 * Discussion Controller
 * 
 * Handles discussion management
 */

namespace Controllers;

use Core\Controller;
use Models\Discussion;

class DiscussionController extends Controller {
    private $discussionModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->discussionModel = new Discussion($this->db);
    }
    
    /**
     * List discussions
     *
     * @return void
     */
    public function index() {
        try {
            // Get page, filters, and search parameters
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $isSticky = isset($_GET['sticky']) ? (bool)$_GET['sticky'] : null;
            $isLocked = isset($_GET['locked']) ? (bool)$_GET['locked'] : null;
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            
            // Get discussions with pagination
            $result = $this->discussionModel->getDiscussions($page, 10, $search, $isSticky, $isLocked);
            $discussions = $result['discussions'];
            $total = $result['total'];
            
            // Get tags for all discussions
            $discussionIds = array_column($discussions, 'id');
            $discussionTags = $this->discussionModel->getTagsForDiscussions($discussionIds);
            
            // Calculate total pages
            $totalPages = ceil($total / 10);
            
            // Page metadata
            $pageTitle = 'Discussions - Keywords Automation';
            $pageHeader = 'Discussions';
            
            // Get alerts
            $alerts = $this->getAlerts();
            
            // Render the view
            $this->render('discussions/index', [
                'pageTitle' => $pageTitle,
                'pageHeader' => $pageHeader,
                'discussions' => $discussions,
                'discussionTags' => $discussionTags,
                'page' => $page,
                'totalPages' => $totalPages,
                'total' => $total,
                'isSticky' => $isSticky,
                'isLocked' => $isLocked,
                'search' => $search,
                'alerts' => $alerts
            ]);
        } catch (\Exception $e) {
            // Log the exception
            error_log("Error in DiscussionController::index: " . $e->getMessage());
            
            // Display error message and render empty view
            $this->addAlert('danger', 'Error loading discussions: ' . $e->getMessage());
            $this->render('discussions/index', [
                'pageTitle' => 'Discussions - Keywords Automation',
                'pageHeader' => 'Discussions - Error',
                'discussions' => [],
                'discussionTags' => [],
                'page' => 1,
                'totalPages' => 0,
                'total' => 0,
                'isSticky' => null,
                'isLocked' => null,
                'search' => '',
                'alerts' => $this->getAlerts()
            ]);
        }
    }
    
    /**
     * View a single discussion with its posts
     *
     * @param int $id Discussion ID
     * @return void
     */
    public function view($id) {
        try {
            // Get discussion by ID
            $discussion = $this->discussionModel->getDiscussion($id);
            
            if (!$discussion) {
                $this->addAlert('danger', 'Discussion not found');
                $this->redirect('discussions');
                return;
            }
            
            // Get posts for this discussion
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $result = $this->discussionModel->getDiscussionPosts($id, $page, 20);
            $posts = $result['posts'];
            $totalPosts = $result['total'];
            
            // Get tags for this discussion
            $tags = $this->discussionModel->getDiscussionTags($id);
            
            // Calculate total pages
            $totalPages = ceil($totalPosts / 20);
            
            // Page metadata
            $pageTitle = $discussion['title'] . ' - Discussions';
            $pageHeader = $discussion['title'];
            
            // Get alerts
            $alerts = $this->getAlerts();
            
            // Render the view
            $this->render('discussions/view', [
                'pageTitle' => $pageTitle,
                'pageHeader' => $pageHeader,
                'discussion' => $discussion,
                'posts' => $posts,
                'tags' => $tags,
                'page' => $page,
                'totalPages' => $totalPages,
                'totalPosts' => $totalPosts,
                'alerts' => $alerts
            ]);
        } catch (\Exception $e) {
            // Log the exception
            error_log("Error in DiscussionController::view: " . $e->getMessage());
            
            // Display error message and redirect
            $this->addAlert('danger', 'Error viewing discussion: ' . $e->getMessage());
            $this->redirect('discussions');
        }
    }
} 