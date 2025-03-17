<?php
/**
 * User Controller
 * 
 * Handles user management
 */

namespace Controllers;

use Core\Controller;
use Models\User;

class UserController extends Controller {
    private $userModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->userModel = new User($this->db);
    }
    
    /**
     * List users
     *
     * @return void
     */
    public function index() {
        try {
            // Get page and search parameters
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $search = isset($_GET['search']) ? $_GET['search'] : '';
            $group = isset($_GET['group']) ? $_GET['group'] : null;
            
            // Get users with pagination
            $result = $this->userModel->getUsers($page, 10, $search, $group);
            $users = $result['users'];
            $total = $result['total'];
            
            // Calculate total pages
            $totalPages = ceil($total / 10);
            
            // Get available groups
            $groups = $this->userModel->getGroups();
            
            // Page metadata
            $pageTitle = 'User Management - Keywords Automation';
            $pageHeader = 'User Management';
            
            // Get alerts
            $alerts = $this->getAlerts();
            
            // Render the view
            $this->render('users/index', [
                'pageTitle' => $pageTitle,
                'pageHeader' => $pageHeader,
                'users' => $users,
                'page' => $page,
                'totalPages' => $totalPages,
                'total' => $total,
                'search' => $search,
                'group' => $group,
                'groups' => $groups,
                'alerts' => $alerts
            ]);
        } catch (\Exception $e) {
            // Log the exception
            error_log("Error in UserController::index: " . $e->getMessage());
            
            // Display error message and render empty view
            $this->addAlert('danger', 'Error loading users: ' . $e->getMessage());
            $this->render('users/index', [
                'pageTitle' => 'User Management - Keywords Automation',
                'pageHeader' => 'User Management - Error',
                'users' => [],
                'page' => 1,
                'totalPages' => 0,
                'total' => 0,
                'search' => '',
                'group' => null,
                'groups' => [],
                'alerts' => $this->getAlerts()
            ]);
        }
    }
    
    /**
     * View a single user
     *
     * @param int $id User ID
     * @return void
     */
    public function view($id) {
        try {
            // Get user by ID
            $user = $this->userModel->getUser($id);
            
            if (!$user) {
                $this->addAlert('danger', 'User not found');
                $this->redirect('users');
                return;
            }
            
            // Get user groups
            $groups = $this->userModel->getUserGroups($id);
            
            // Page metadata
            $pageTitle = $user['username'] . ' - User Details';
            $pageHeader = $user['username'];
            
            // Get alerts
            $alerts = $this->getAlerts();
            
            // Render the view
            $this->render('users/view', [
                'pageTitle' => $pageTitle,
                'pageHeader' => $pageHeader,
                'user' => $user,
                'groups' => $groups,
                'alerts' => $alerts
            ]);
        } catch (\Exception $e) {
            // Log the exception
            error_log("Error in UserController::view: " . $e->getMessage());
            
            // Display error message and redirect
            $this->addAlert('danger', 'Error viewing user: ' . $e->getMessage());
            $this->redirect('users');
        }
    }
    
    /**
     * Process user actions (edit, delete, etc.)
     *
     * @return void
     */
    public function processAction() {
        // Check if form was submitted
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('users');
            return;
        }
        
        try {
            // Handle actions
            if (isset($_POST['action']) && isset($_POST['user_id'])) {
                $userId = (int)$_POST['user_id'];
                $action = $_POST['action'];
                
                switch ($action) {
                    case 'update':
                        // Update user information
                        $data = [];
                        
                        if (isset($_POST['username'])) {
                            $data['username'] = trim($_POST['username']);
                        }
                        
                        if (isset($_POST['email'])) {
                            $data['email'] = trim($_POST['email']);
                        }
                        
                        if (!empty($_POST['password'])) {
                            $data['password'] = $_POST['password'];
                        }
                        
                        if (!empty($data)) {
                            $success = $this->userModel->updateUser($userId, $data);
                            if ($success) {
                                $this->addAlert('success', 'User updated successfully.');
                            } else {
                                $this->addAlert('danger', 'Failed to update user.');
                            }
                        }
                        
                        // Update user groups if provided
                        if (isset($_POST['groups']) && is_array($_POST['groups'])) {
                            $this->userModel->updateUserGroups($userId, $_POST['groups']);
                            $this->addAlert('success', 'User groups updated.');
                        }
                        break;
                        
                    case 'suspend':
                        // Suspend user
                        $success = $this->userModel->suspendUser($userId);
                        if ($success) {
                            $this->addAlert('warning', 'User suspended.');
                        } else {
                            $this->addAlert('danger', 'Failed to suspend user.');
                        }
                        break;
                        
                    case 'unsuspend':
                        // Unsuspend user
                        $success = $this->userModel->unsuspendUser($userId);
                        if ($success) {
                            $this->addAlert('success', 'User unsuspended.');
                        } else {
                            $this->addAlert('danger', 'Failed to unsuspend user.');
                        }
                        break;
                        
                    case 'delete':
                        // Delete user
                        $success = $this->userModel->deleteUser($userId);
                        if ($success) {
                            $this->addAlert('danger', 'User deleted permanently.');
                        } else {
                            $this->addAlert('danger', 'Failed to delete user.');
                        }
                        break;
                }
            }
        } catch (\Exception $e) {
            $this->addAlert('danger', 'Error: ' . $e->getMessage());
        }
        
        // Redirect back to users page
        $this->redirect('users');
    }
} 