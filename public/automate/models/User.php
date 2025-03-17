<?php
/**
 * User Model
 * 
 * Handles user data operations
 */

namespace Models;

use Core\Model;

class User {
    /**
     * @var \PDO Database connection
     */
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
     * Get users with pagination
     *
     * @param int $page Page number
     * @param int $perPage Items per page
     * @param string $search Search term
     * @param int|null $groupId Filter by group ID
     * @return array Array with users and total count
     */
    public function getUsers($page = 1, $perPage = 10, $search = '', $groupId = null) {
        $offset = ($page - 1) * $perPage;
        $params = [];
        
        // Base query for users
        $query = "SELECT u.id, u.username, u.email, u.joined_at, u.is_email_confirmed, 
                        u.suspended_until, COUNT(p.id) as post_count,
                        g.name_singular as primary_group_name, g.color as primary_group_color,
                        g.id as primary_group_id
                  FROM users u
                  LEFT JOIN posts p ON u.id = p.user_id
                  LEFT JOIN (
                      SELECT gu.user_id, MIN(gu.group_id) as min_group_id
                      FROM group_user gu
                      GROUP BY gu.user_id
                  ) as min_groups ON u.id = min_groups.user_id
                  LEFT JOIN groups g ON min_groups.min_group_id = g.id";
        
        // Add group filter if provided
        if ($groupId) {
            $query .= " JOIN group_user gu ON u.id = gu.user_id AND gu.group_id = ?";
            $params[] = $groupId;
        }
        
        // Add search condition
        if (!empty($search)) {
            $query .= " WHERE (u.username LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        } else {
            $query .= " WHERE 1=1"; // Always true condition for consistency
        }
        
        // Group by user ID
        $query .= " GROUP BY u.id";
        
        // Count total
        $countQuery = "SELECT COUNT(*) FROM (" . $query . ") as count_table";
        $stmt = $this->db->prepare($countQuery);
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        $stmt->execute();
        $total = $stmt->fetchColumn();
        
        // Get users with pagination
        $query .= " ORDER BY u.id DESC LIMIT ?, ?";
        $stmt = $this->db->prepare($query);
        
        // Bind parameters for the main query
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        $paramCount = count($params);
        $stmt->bindValue($paramCount + 1, $offset, \PDO::PARAM_INT);
        $stmt->bindValue($paramCount + 2, $perPage, \PDO::PARAM_INT);
        
        $stmt->execute();
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        return [
            'users' => $users,
            'total' => $total
        ];
    }
    
    /**
     * Get a single user by ID
     *
     * @param int $id User ID
     * @return array|bool User data or false if not found
     */
    public function getUser($id) {
        $stmt = $this->db->prepare("
            SELECT u.*, COUNT(p.id) as post_count
            FROM users u
            LEFT JOIN posts p ON u.id = p.user_id
            WHERE u.id = ?
            GROUP BY u.id
        ");
        $stmt->bindValue(1, $id, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get groups that a user belongs to
     *
     * @param int $userId User ID
     * @return array Groups
     */
    public function getUserGroups($userId) {
        $stmt = $this->db->prepare("
            SELECT g.*
            FROM groups g
            JOIN group_user gu ON g.id = gu.group_id
            WHERE gu.user_id = ?
            ORDER BY g.name_singular
        ");
        $stmt->bindValue(1, $userId, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all available groups
     *
     * @return array Groups
     */
    public function getGroups() {
        $stmt = $this->db->prepare("SELECT * FROM groups ORDER BY name_singular");
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Update user information
     *
     * @param int $userId User ID
     * @param array $data User data to update
     * @return bool Success status
     */
    public function updateUser($userId, $data) {
        $fields = [];
        $params = [];
        
        // Build update fields
        foreach ($data as $field => $value) {
            // Special handling for password
            if ($field === 'password') {
                $fields[] = "password = ?";
                $params[] = password_hash($value, PASSWORD_DEFAULT);
            } else {
                $fields[] = "$field = ?";
                $params[] = $value;
            }
        }
        
        // Add updated_at field
        $fields[] = "updated_at = NOW()";
        
        if (empty($fields)) {
            return false;
        }
        
        $query = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $params[] = $userId;
        
        $stmt = $this->db->prepare($query);
        foreach ($params as $i => $param) {
            $stmt->bindValue($i + 1, $param);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Update user groups
     *
     * @param int $userId User ID
     * @param array $groupIds Group IDs
     * @return bool Success status
     */
    public function updateUserGroups($userId, $groupIds) {
        // Begin transaction
        $this->db->beginTransaction();
        
        try {
            // Delete existing group associations
            $stmt = $this->db->prepare("DELETE FROM group_user WHERE user_id = ?");
            $stmt->bindValue(1, $userId, \PDO::PARAM_INT);
            $stmt->execute();
            
            // Insert new group associations
            $now = date('Y-m-d H:i:s');
            $stmt = $this->db->prepare("INSERT INTO group_user (user_id, group_id, created_at) VALUES (?, ?, ?)");
            
            foreach ($groupIds as $groupId) {
                $stmt->bindValue(1, $userId, \PDO::PARAM_INT);
                $stmt->bindValue(2, $groupId, \PDO::PARAM_INT);
                $stmt->bindValue(3, $now);
                $stmt->execute();
            }
            
            // Commit transaction
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            error_log("Error updating user groups: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Suspend a user
     *
     * @param int $userId User ID
     * @param string $until Suspension end date (optional)
     * @return bool Success status
     */
    public function suspendUser($userId, $until = null) {
        $suspendUntil = $until ?: date('Y-m-d H:i:s', strtotime('+30 days'));
        
        $stmt = $this->db->prepare("UPDATE users SET suspended_until = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bindValue(1, $suspendUntil);
        $stmt->bindValue(2, $userId, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Unsuspend a user
     *
     * @param int $userId User ID
     * @return bool Success status
     */
    public function unsuspendUser($userId) {
        $stmt = $this->db->prepare("UPDATE users SET suspended_until = NULL, updated_at = NOW() WHERE id = ?");
        $stmt->bindValue(1, $userId, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Delete a user
     *
     * @param int $userId User ID
     * @return bool Success status
     */
    public function deleteUser($userId) {
        // Begin transaction
        $this->db->beginTransaction();
        
        try {
            // Delete group associations
            $stmt = $this->db->prepare("DELETE FROM group_user WHERE user_id = ?");
            $stmt->bindValue(1, $userId, \PDO::PARAM_INT);
            $stmt->execute();
            
            // Delete user
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bindValue(1, $userId, \PDO::PARAM_INT);
            $stmt->execute();
            
            // Commit transaction
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            error_log("Error deleting user: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if a username is available
     *
     * @param string $username Username to check
     * @return bool True if available, false if taken
     */
    public function isUsernameAvailable($username) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bindValue(1, $username);
        $stmt->execute();
        
        return $stmt->rowCount() === 0;
    }
    
    /**
     * Create a new user
     *
     * @param array $userData User data
     * @return int|bool New user ID or false on failure
     */
    public function createUser($userData) {
        // Check if username and email are provided
        if (empty($userData['username']) || empty($userData['email'])) {
            return false;
        }
        
        // Check if username is available
        if (!$this->isUsernameAvailable($userData['username'])) {
            return false;
        }
        
        // Begin transaction
        $this->db->beginTransaction();
        
        try {
            // Prepare user data
            $username = $userData['username'];
            $email = $userData['email'];
            $password = isset($userData['password']) ? password_hash($userData['password'], PASSWORD_DEFAULT) : null;
            $isEmailConfirmed = isset($userData['is_email_confirmed']) ? (int)$userData['is_email_confirmed'] : 0;
            $joinedAt = isset($userData['joined_at']) ? $userData['joined_at'] : date('Y-m-d H:i:s');
            
            // Insert user
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password, is_email_confirmed, joined_at)
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $stmt->bindValue(1, $username);
            $stmt->bindValue(2, $email);
            $stmt->bindValue(3, $password);
            $stmt->bindValue(4, $isEmailConfirmed, \PDO::PARAM_INT);
            $stmt->bindValue(5, $joinedAt);
            $stmt->execute();
            
            $userId = $this->db->lastInsertId();
            
            // Commit transaction
            $this->db->commit();
            return $userId;
        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            error_log("Error creating user: " . $e->getMessage());
            return false;
        }
    }
} 