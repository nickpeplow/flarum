<?php
/**
 * Discussion Model
 * 
 * Handles database operations for discussions
 */

namespace Models;

class Discussion {
    private $db;
    
    /**
     * Constructor
     *
     * @param \PDO $db Database connection
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get discussions with pagination
     *
     * @param int $page Page number
     * @param int $perPage Items per page
     * @param string|null $search Search term
     * @param bool|null $isSticky Filter sticky discussions
     * @param bool|null $isLocked Filter locked discussions
     * @return array Discussions and total count
     */
    public function getDiscussions($page = 1, $perPage = 10, $search = null, $isSticky = null, $isLocked = null) {
        try {
            // Calculate offset
            $offset = ($page - 1) * $perPage;
            
            // Base query
            $sql = "SELECT d.*, 
                    u.username as author_username,
                    lu.username as last_posted_username
                    FROM discussions d
                    LEFT JOIN users u ON d.user_id = u.id
                    LEFT JOIN users lu ON d.last_posted_user_id = lu.id
                    WHERE 1=1";
            $params = [];
            
            // Add search condition if provided
            if (!empty($search)) {
                $sql .= " AND (d.title LIKE :search OR d.slug LIKE :search)";
                $params[':search'] = '%' . $search . '%';
            }
            
            // Add sticky filter if provided
            if ($isSticky !== null) {
                $sql .= " AND d.is_sticky = :is_sticky";
                $params[':is_sticky'] = $isSticky ? 1 : 0;
            }
            
            // Add locked filter if provided
            if ($isLocked !== null) {
                $sql .= " AND d.is_locked = :is_locked";
                $params[':is_locked'] = $isLocked ? 1 : 0;
            }
            
            // Add ordering
            $sql .= " ORDER BY d.is_sticky DESC, d.last_posted_at DESC, d.created_at DESC";
            
            // Add limit and offset
            $sql .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $perPage;
            $params[':offset'] = $offset;
            
            // Prepare and execute the query
            $stmt = $this->db->prepare($sql);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                if ($key == ':limit' || $key == ':offset') {
                    $stmt->bindValue($key, $value, \PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value, \PDO::PARAM_STR);
                }
            }
            
            $stmt->execute();
            $discussions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Count total discussions for pagination
            $countSql = "SELECT COUNT(*) as total FROM discussions d WHERE 1=1";
            $countParams = [];
            
            // Add search condition if provided
            if (!empty($search)) {
                $countSql .= " AND (d.title LIKE :search OR d.slug LIKE :search)";
                $countParams[':search'] = '%' . $search . '%';
            }
            
            // Add sticky filter if provided
            if ($isSticky !== null) {
                $countSql .= " AND d.is_sticky = :is_sticky";
                $countParams[':is_sticky'] = $isSticky ? 1 : 0;
            }
            
            // Add locked filter if provided
            if ($isLocked !== null) {
                $countSql .= " AND d.is_locked = :is_locked";
                $countParams[':is_locked'] = $isLocked ? 1 : 0;
            }
            
            // Prepare and execute the count query
            $countStmt = $this->db->prepare($countSql);
            
            // Bind parameters for count query
            foreach ($countParams as $key => $value) {
                $countStmt->bindValue($key, $value, \PDO::PARAM_STR);
            }
            
            $countStmt->execute();
            $totalResult = $countStmt->fetch(\PDO::FETCH_ASSOC);
            $total = $totalResult['total'];
            
            return [
                'discussions' => $discussions,
                'total' => $total
            ];
        } catch (\PDOException $e) {
            error_log("Error in Discussion::getDiscussions: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get a single discussion by ID
     *
     * @param int $id Discussion ID
     * @return array|null Discussion data or null if not found
     */
    public function getDiscussion($id) {
        try {
            $sql = "SELECT d.*, 
                    u.username as author_username,
                    lu.username as last_posted_username
                    FROM discussions d
                    LEFT JOIN users u ON d.user_id = u.id
                    LEFT JOIN users lu ON d.last_posted_user_id = lu.id
                    WHERE d.id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            
            $discussion = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $discussion ?: null;
        } catch (\PDOException $e) {
            error_log("Error in Discussion::getDiscussion: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get posts for a discussion
     *
     * @param int $discussionId Discussion ID
     * @param int $page Page number
     * @param int $perPage Posts per page
     * @return array Posts and total count
     */
    public function getDiscussionPosts($discussionId, $page = 1, $perPage = 20) {
        try {
            // Calculate offset
            $offset = ($page - 1) * $perPage;
            
            // Base query for posts
            $sql = "SELECT p.*, u.username as author_username
                    FROM posts p
                    LEFT JOIN users u ON p.user_id = u.id
                    WHERE p.discussion_id = :discussion_id
                    ORDER BY p.number ASC
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':discussion_id', $discussionId, \PDO::PARAM_INT);
            $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Count total posts
            $countSql = "SELECT COUNT(*) as total FROM posts WHERE discussion_id = :discussion_id";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->bindValue(':discussion_id', $discussionId, \PDO::PARAM_INT);
            $countStmt->execute();
            
            $totalResult = $countStmt->fetch(\PDO::FETCH_ASSOC);
            $total = $totalResult['total'];
            
            return [
                'posts' => $posts,
                'total' => $total
            ];
        } catch (\PDOException $e) {
            error_log("Error in Discussion::getDiscussionPosts: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get tags for a single discussion
     *
     * @param int $discussionId Discussion ID
     * @return array Tags associated with the discussion
     */
    public function getDiscussionTags($discussionId) {
        try {
            $sql = "SELECT t.* 
                    FROM tags t
                    JOIN discussion_tag dt ON dt.tag_id = t.id
                    WHERE dt.discussion_id = :discussion_id
                    ORDER BY t.position, t.name";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':discussion_id', $discussionId, \PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error in Discussion::getDiscussionTags: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get tags for multiple discussions
     *
     * @param array $discussionIds Array of discussion IDs
     * @return array Associative array with discussion_id as key and array of tags as value
     */
    public function getTagsForDiscussions(array $discussionIds) {
        if (empty($discussionIds)) {
            return [];
        }
        
        try {
            // Convert array to comma-separated string for the IN clause
            $idPlaceholders = implode(',', array_fill(0, count($discussionIds), '?'));
            
            $sql = "SELECT dt.discussion_id, t.* 
                    FROM tags t
                    JOIN discussion_tag dt ON dt.tag_id = t.id
                    WHERE dt.discussion_id IN ($idPlaceholders)
                    ORDER BY t.position, t.name";
            
            $stmt = $this->db->prepare($sql);
            
            // Bind each discussion ID to its placeholder
            foreach ($discussionIds as $index => $id) {
                $stmt->bindValue($index + 1, $id, \PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Organize tags by discussion_id
            $discussionTags = [];
            foreach ($results as $row) {
                $discussionId = $row['discussion_id'];
                unset($row['discussion_id']); // Remove discussion_id from the tag data
                
                if (!isset($discussionTags[$discussionId])) {
                    $discussionTags[$discussionId] = [];
                }
                
                $discussionTags[$discussionId][] = $row;
            }
            
            return $discussionTags;
        } catch (\PDOException $e) {
            error_log("Error in Discussion::getTagsForDiscussions: " . $e->getMessage());
            return [];
        }
    }
} 