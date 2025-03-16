<?php
/**
 * Home Controller
 * 
 * Handles dashboard and home page
 */

namespace Controllers;

use Core\Controller;
use Models\Keyword;

class HomeController extends Controller {
    private $keywordModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->keywordModel = new Keyword($this->db);
    }
    
    /**
     * Dashboard/Index page
     *
     * @return void
     */
    public function index() {
        // Get keyword statistics
        $stats = $this->keywordModel->getStatistics();
        
        // Get recent keywords
        $recentKeywords = $this->keywordModel->getRecentKeywords(5);
        
        // Page metadata
        $pageTitle = 'Dashboard - Keywords Automation';
        $pageHeader = 'Dashboard';
        
        // Get alerts
        $alerts = $this->getAlerts();
        
        // Render the view
        $this->render('home/index', [
            'pageTitle' => $pageTitle,
            'pageHeader' => $pageHeader,
            'stats' => $stats,
            'recentKeywords' => $recentKeywords,
            'alerts' => $alerts,
            'db' => $this->db
        ]);
    }
    
    /**
     * Temporary redirect for tags page
     * 
     * This will be replaced by a proper TagController
     * @return void
     */
    public function tagsRedirect() {
        $this->addAlert('info', 'The tags functionality is currently under development.');
        $this->redirect(\Config\App::get('base_url'));
    }
    
    /**
     * Temporary redirect for schema viewer
     * 
     * This will be replaced by a proper SchemaController
     * @return void
     */
    public function schemaRedirect() {
        $this->addAlert('info', 'The schema viewer is currently under development.');
        $this->redirect(\Config\App::get('base_url'));
    }
    
    /**
     * Redirect for keywords page
     * 
     * @return void
     */
    public function keywordsRedirect() {
        $this->addAlert('info', 'The keywords functionality is currently under development.');
        $this->redirect(\Config\App::get('base_url'));
    }
} 