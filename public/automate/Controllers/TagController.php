<?php
/**
 * Tag Controller
 * 
 * Handles tag and category management
 */

namespace Controllers;

use Core\Controller;

class TagController extends Controller {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Tags index page
     *
     * @return void
     */
    public function index() {
        // Sample tag data - in a real implementation, this would come from the database
        $tags = [
            [
                'id' => 1,
                'name' => 'General',
                'slug' => 'general',
                'description' => 'General discussion topics',
                'color' => '#3498db',
                'parent_id' => null,
                'position' => 1,
                'children' => [
                    [
                        'id' => 4,
                        'name' => 'Announcements',
                        'slug' => 'announcements',
                        'description' => 'Official announcements',
                        'color' => '#9b59b6',
                        'parent_id' => 1,
                        'position' => 1
                    ],
                    [
                        'id' => 5,
                        'name' => 'Help',
                        'slug' => 'help',
                        'description' => 'Get help with issues',
                        'color' => '#2ecc71',
                        'parent_id' => 1,
                        'position' => 2
                    ]
                ]
            ],
            [
                'id' => 2,
                'name' => 'Development',
                'slug' => 'development',
                'description' => 'Development related topics',
                'color' => '#e74c3c',
                'parent_id' => null,
                'position' => 2,
                'children' => [
                    [
                        'id' => 6,
                        'name' => 'Support',
                        'slug' => 'support',
                        'description' => 'Technical support',
                        'color' => '#f1c40f',
                        'parent_id' => 2,
                        'position' => 1
                    ]
                ]
            ],
            [
                'id' => 3,
                'name' => 'Feedback',
                'slug' => 'feedback',
                'description' => 'Provide feedback on the forum',
                'color' => '#1abc9c',
                'parent_id' => null,
                'position' => 3,
                'children' => []
            ]
        ];
        
        // Page metadata
        $pageTitle = 'Tag Management - Keywords Automation';
        $pageHeader = 'Tag Management';
        
        // Get alerts
        $alerts = $this->getAlerts();
        
        // Render the view
        $this->render('tags/index', [
            'pageTitle' => $pageTitle,
            'pageHeader' => $pageHeader,
            'tags' => $tags,
            'alerts' => $alerts,
            'db' => $this->db
        ]);
    }
} 