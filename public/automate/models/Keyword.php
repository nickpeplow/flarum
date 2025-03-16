<?php
/**
 * Keyword Model
 * 
 * Handles all database operations for the keywords table
 */

namespace Models;

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
            if ($status === 'pending') {
                $sql .= " AND status = :status";
                $params[':status'] = self::STATUS_PENDING;
            } elseif ($status === 'approved') {
                $sql .= " AND status = :status";
                $params[':status'] = self::STATUS_APPROVED;
            } elseif ($status === 'rejected') {
                $sql .= " AND status = :status";
                $params[':status'] = self::STATUS_REJECTED;
            }
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
        
        try {
            // Execute the query
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                if ($key === ':offset' || $key === ':perPage') {
                    $stmt->bindValue($key, $value, \PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            
            $stmt->execute();
            $keywords = $stmt->fetchAll();
            
            // Get total count
            $stmt = $this->db->query("SELECT FOUND_ROWS() as total");
            $total = $stmt->fetch()['total'];
            
            return [
                'keywords' => $keywords,
                'total' => $total
            ];
        } catch (\PDOException $e) {
            error_log("Error in Keyword::getKeywords: " . $e->getMessage());
            throw new \Exception("Database error: " . $e->getMessage());
        }
    }
    
    /**
     * Get a list of keywords with optional filtering and pagination (alias for getKeywords)
     * 
     * @param int $perPage Items per page
     * @param int $offset Offset for pagination
     * @param string $status Filter by status (optional)
     * @param string $search Search term (optional)
     * @return array Array of keywords
     */
    public function getList($perPage = 10, $offset = 0, $status = '', $search = '') {
        // Convert string status to integer if needed
        $statusValue = null;
        if (!empty($status)) {
            switch ($status) {
                case 'pending':
                    $statusValue = self::STATUS_PENDING;
                    break;
                case 'approved':
                    $statusValue = self::STATUS_APPROVED;
                    break;
                case 'rejected':
                    $statusValue = self::STATUS_REJECTED;
                    break;
            }
        }
        
        $params = [];
        
        // Base query
        $sql = "SELECT * FROM keywords WHERE 1=1";
        
        // Add status filter if provided
        if ($statusValue !== null) {
            $sql .= " AND status = :status";
            $params[':status'] = $statusValue;
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get total count of keywords with optional filtering
     * 
     * @param string $status Filter by status (optional)
     * @param string $search Search term (optional)
     * @return int Total count
     */
    public function getTotal($status = '', $search = '') {
        // Convert string status to integer if needed
        $statusValue = null;
        if (!empty($status)) {
            switch ($status) {
                case 'pending':
                    $statusValue = self::STATUS_PENDING;
                    break;
                case 'approved':
                    $statusValue = self::STATUS_APPROVED;
                    break;
                case 'rejected':
                    $statusValue = self::STATUS_REJECTED;
                    break;
            }
        }
        
        $params = [];
        
        // Base query
        $sql = "SELECT COUNT(*) FROM keywords WHERE 1=1";
        
        // Add status filter if provided
        if ($statusValue !== null) {
            $sql .= " AND status = :status";
            $params[':status'] = $statusValue;
        }
        
        // Add search filter if provided
        if (!empty($search)) {
            $sql .= " AND keyword LIKE :search";
            $params[':search'] = "%$search%";
        }
        
        // Execute the query
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchColumn();
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
     * @param int $limit Number of keywords to retrieve
     * @return array Array of keywords
     */
    public function getRecentKeywords($limit = 5) {
        $stmt = $this->db->prepare("SELECT * FROM keywords ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Add a new keyword
     * 
     * @param string $keyword Keyword text
     * @param int $postId Associated post ID (optional)
     * @param int $tagId Associated tag ID (optional)
     * @param int $status Keyword status
     * @return int ID of the newly added keyword
     */
    public function addKeyword($keyword, $postId = null, $tagId = null, $status = self::STATUS_PENDING) {
        $stmt = $this->db->prepare("
            INSERT INTO keywords (keyword, post_id, tag_id, status, created_at, updated_at)
            VALUES (:keyword, :post_id, :tag_id, :status, NOW(), NOW())
        ");
        
        $stmt->bindParam(':keyword', $keyword);
        $stmt->bindParam(':post_id', $postId);
        $stmt->bindParam(':tag_id', $tagId);
        $stmt->bindParam(':status', $status);
        
        $stmt->execute();
        return $this->db->lastInsertId();
    }
    
    /**
     * Update keyword status
     * 
     * @param int $id Keyword ID
     * @param int $status New status
     * @return bool Success or failure
     */
    public function updateStatus($id, $status) {
        // Convert status string to int if needed
        if (is_string($status)) {
            switch (strtolower($status)) {
                case 'pending':
                    $status = self::STATUS_PENDING;
                    break;
                case 'approved':
                    $status = self::STATUS_APPROVED;
                    break;
                case 'rejected':
                    $status = self::STATUS_REJECTED;
                    break;
            }
        }
        
        $stmt = $this->db->prepare("
            UPDATE keywords
            SET status = :status, updated_at = NOW()
            WHERE id = :id
        ");
        
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Delete a keyword
     * 
     * @param int $id Keyword ID
     * @return bool Success or failure
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM keywords WHERE id = :id");
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Get keyword statistics
     * 
     * @return array Array of statistics
     */
    public function getStatistics() {
        // Get total count
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM keywords");
        $totalRow = $stmt->fetch();
        $total = $totalRow['total'];
        
        // Get counts by status
        $stmt = $this->db->query("
            SELECT 
                SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as rejected
            FROM keywords
        ");
        $counts = $stmt->fetch();
        
        return [
            'total' => $total,
            'pending' => (int)$counts['pending'],
            'approved' => (int)$counts['approved'],
            'rejected' => (int)$counts['rejected']
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
     * @return string HTML for status badge
     */
    public static function getStatusBadge($status) {
        $name = self::getStatusName($status);
        $class = '';
        
        switch ($status) {
            case self::STATUS_PENDING:
                $class = 'bg-warning';
                break;
            case self::STATUS_APPROVED:
                $class = 'bg-success';
                break;
            case self::STATUS_REJECTED:
                $class = 'bg-danger';
                break;
            default:
                $class = 'bg-secondary';
        }
        
        return '<span class="badge ' . $class . '">' . $name . '</span>';
    }
} 