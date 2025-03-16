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
        try {
            // Fetch parent tags (those with null parent_id)
            $parentTagsQuery = "SELECT * FROM tags WHERE parent_id IS NULL ORDER BY position, name";
            $parentTags = $this->db->query($parentTagsQuery)->fetchAll(\PDO::FETCH_ASSOC);
            
            // Initialize the tags array to hold structured data
            $tags = [];
            
            foreach ($parentTags as $parentTag) {
                // For each parent tag, get its children
                $childTagsQuery = "SELECT * FROM tags WHERE parent_id = :parent_id ORDER BY position, name";
                $stmt = $this->db->prepare($childTagsQuery);
                $stmt->bindParam(':parent_id', $parentTag['id'], \PDO::PARAM_INT);
                $stmt->execute();
                $childTags = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
                // Add the parent tag with its children to the result array
                $parentTag['children'] = $childTags;
                $tags[] = $parentTag;
            }
            
            // If no tags found, provide sample data as a fallback
            if (empty($tags)) {
                error_log("No tags found in database, using sample data");
                $tags = $this->getSampleTagData();
            } else {
                error_log("Found " . count($tags) . " parent tags in database");
            }
            
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
        } catch (\PDOException $e) {
            error_log("Error in TagController::index: Database error: " . $e->getMessage());
            
            // Fallback to sample data if database query fails
            $tags = $this->getSampleTagData();
            
            // Add error alert
            $this->addAlert('danger', 'Database Error', 'Could not retrieve tags from the database: ' . $e->getMessage());
            $alerts = $this->getAlerts();
            
            // Page metadata
            $pageTitle = 'Tag Management - Keywords Automation';
            $pageHeader = 'Tag Management';
            
            // Render the view with sample data
            $this->render('tags/index', [
                'pageTitle' => $pageTitle,
                'pageHeader' => $pageHeader,
                'tags' => $tags,
                'alerts' => $alerts,
                'db' => $this->db
            ]);
        }
    }
    
    /**
     * Get sample tag data as a fallback
     * 
     * @return array Sample tag data
     */
    private function getSampleTagData() {
        return [
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
    }
} 