<?php
/**
 * Keyword Model
 * 
 * Handles all database operations for the keywords table
 */

class Keyword {
    private $db;
    
    // Status constants
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;
    
    // Constructor - accepts a PDO connection
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get all keywords with optional filtering and pagination
     * 
     * @param int $page Current page number
     * @param int $perPage Items per page
     * @param string $status Filter by status (optional)
     * @param string $search Search term (optional)
     * @return array Array containing 'keywords' and 'total'
     */
    public function getKeywords($page = 1, $perPage = 10, $status = null, $search = '') {
        $offset = ($page - 1) * $perPage;
        $params = [];
        
        // Base query
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM keywords WHERE 1=1";
        
        // Add status filter if provided
        if ($status !== null && $status !== 'all') {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }
        
        // Add search filter if provided
        if (!empty($search)) {
            $sql .= " AND keyword LIKE :search";
            $params[':search'] = "%$search%";
        }
        
        // Add order by clause
        $sql .= " ORDER BY created_at DESC LIMIT :offset, :perPage";
        $params[':offset'] = $offset;
        $params[':perPage'] = $perPage;
        
        // Execute the query
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            // Properly bind the LIMIT parameters as integers
            if ($key === ':offset' || $key === ':perPage') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        $stmt->execute();
        $keywords = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total count
        $totalStmt = $this->db->query("SELECT FOUND_ROWS()");
        $total = $totalStmt->fetchColumn();
        
        return [
            'keywords' => $keywords,
            'total' => $total
        ];
    }
    
    /**
     * Get keyword by ID
     * 
     * @param int $id Keyword ID
     * @return array|null Keyword data or null if not found
     */
    public function getKeywordById($id) {
        $stmt = $this->db->prepare("SELECT * FROM keywords WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get recent keywords
     * 
     * @param int $limit Number of recent keywords to retrieve
     * @return array Array of keywords
     */
    public function getRecentKeywords($limit = 5) {
        $stmt = $this->db->prepare("
            SELECT * FROM keywords
            ORDER BY created_at DESC 
            LIMIT :limit
        ");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Add a new keyword
     * 
     * @param string $keyword Keyword text
     * @param int $postId Associated post ID
     * @param int $tagId Associated tag ID
     * @param int $status Keyword status
     * @return int|bool The ID of the new keyword or false on failure
     */
    public function addKeyword($keyword, $postId = null, $tagId = null, $status = self::STATUS_PENDING) {
        $sql = "INSERT INTO keywords (keyword, post_id, tag_id, status) 
                VALUES (:keyword, :postId, :tagId, :status)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':keyword', $keyword);
        $stmt->bindParam(':postId', $postId, PDO::PARAM_INT);
        $stmt->bindParam(':tagId', $tagId, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Update keyword status
     * 
     * @param int $id Keyword ID
     * @param int $status New status
     * @return bool Success flag
     */
    public function updateKeywordStatus($id, $status) {
        $sql = "UPDATE keywords SET status = :status WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Delete a keyword
     * 
     * @param int $id Keyword ID
     * @return bool Success flag
     */
    public function deleteKeyword($id) {
        $sql = "DELETE FROM keywords WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Get keyword statistics
     * 
     * @return array Statistics data
     */
    public function getStatistics() {
        // Total keywords
        $totalStmt = $this->db->query("SELECT COUNT(*) FROM keywords");
        $total = $totalStmt->fetchColumn();
        
        // Keywords by status
        $statusStmt = $this->db->query("
            SELECT status, COUNT(*) as count 
            FROM keywords 
            GROUP BY status
        ");
        $statusCounts = $statusStmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Ensure all statuses have a count
        $pending = isset($statusCounts[self::STATUS_PENDING]) ? $statusCounts[self::STATUS_PENDING] : 0;
        $approved = isset($statusCounts[self::STATUS_APPROVED]) ? $statusCounts[self::STATUS_APPROVED] : 0;
        $rejected = isset($statusCounts[self::STATUS_REJECTED]) ? $statusCounts[self::STATUS_REJECTED] : 0;
        
        return [
            'total' => $total,
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected
        ];
    }
    
    /**
     * Get status name
     * 
     * @param int $status Status code
     * @return string Status name
     */
    public static function getStatusName($status) {
        switch ($status) {
            case self::STATUS_PENDING:
                return 'Pending';
            case self::STATUS_APPROVED:
                return 'Approved';
            case self::STATUS_REJECTED:
                return 'Rejected';
            default:
                return 'Unknown';
        }
    }
    
    /**
     * Get status badge HTML
     * 
     * @param int $status Status code
     * @return string HTML for the status badge
     */
    public static function getStatusBadge($status) {
        $statusName = self::getStatusName($status);
        
        switch ($status) {
            case self::STATUS_PENDING:
                return "<span class='badge bg-warning text-dark'>$statusName</span>";
            case self::STATUS_APPROVED:
                return "<span class='badge bg-success'>$statusName</span>";
            case self::STATUS_REJECTED:
                return "<span class='badge bg-danger'>$statusName</span>";
            default:
                return "<span class='badge bg-secondary'>$statusName</span>";
        }
    }
} 