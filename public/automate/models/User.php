<?php
/**
 * User Model
 * 
 * Handles database operations for users table
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
     * Get a user by ID
     *
     * @param int $id User ID
     * @return array|null User data or null if not found
     */
    public function getUser($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $user ?: null;
        } catch (\PDOException $e) {
            error_log("Error in User::getUser: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get a user by username
     *
     * @param string $username Username
     * @return array|null User data or null if not found
     */
    public function getUserByUsername($username) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->bindValue(':username', $username);
            $stmt->execute();
            
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $user ?: null;
        } catch (\PDOException $e) {
            error_log("Error in User::getUserByUsername: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get a user by email
     *
     * @param string $email Email address
     * @return array|null User data or null if not found
     */
    public function getUserByEmail($email) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindValue(':email', $email);
            $stmt->execute();
            
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $user ?: null;
        } catch (\PDOException $e) {
            error_log("Error in User::getUserByEmail: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create a new user
     *
     * @param array $userData User data (username, email, password)
     * @return int|false ID of the new user or false on failure
     */
    public function createUser($userData) {
        try {
            // Check if a user with this username or email already exists
            if ($this->getUserByUsername($userData['username'])) {
                throw new \Exception("Username already taken");
            }
            
            if ($this->getUserByEmail($userData['email'])) {
                throw new \Exception("Email already in use");
            }
            
            // Hash the password
            $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            // Prepare the SQL statement
            $stmt = $this->db->prepare("
                INSERT INTO users (
                    username, 
                    email, 
                    is_email_confirmed, 
                    password, 
                    avatar_url, 
                    joined_at
                ) VALUES (
                    :username, 
                    :email, 
                    :is_email_confirmed, 
                    :password, 
                    :avatar_url, 
                    :joined_at
                )
            ");
            
            // Set default values for optional fields if not provided
            $userData['is_email_confirmed'] = $userData['is_email_confirmed'] ?? 0;
            $userData['avatar_url'] = $userData['avatar_url'] ?? null;
            $userData['joined_at'] = $userData['joined_at'] ?? date('Y-m-d H:i:s');
            
            // Bind the parameters
            $stmt->bindValue(':username', $userData['username']);
            $stmt->bindValue(':email', $userData['email']);
            $stmt->bindValue(':is_email_confirmed', $userData['is_email_confirmed'], \PDO::PARAM_INT);
            $stmt->bindValue(':password', $passwordHash);
            $stmt->bindValue(':avatar_url', $userData['avatar_url']);
            $stmt->bindValue(':joined_at', $userData['joined_at']);
            
            // Execute the query
            $success = $stmt->execute();
            
            if ($success) {
                return $this->db->lastInsertId();
            } else {
                return false;
            }
        } catch (\Exception $e) {
            error_log("Error in User::createUser: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update an existing user
     *
     * @param int $id User ID
     * @param array $userData User data to update
     * @return bool Success or failure
     */
    public function updateUser($id, $userData) {
        try {
            // Start building the SQL query
            $sql = "UPDATE users SET ";
            $params = [];
            
            // Add each field to update
            $updateFields = [];
            
            // Handle username update
            if (isset($userData['username'])) {
                // Check if username is already taken by another user
                $existingUser = $this->getUserByUsername($userData['username']);
                if ($existingUser && $existingUser['id'] != $id) {
                    throw new \Exception("Username already taken");
                }
                
                $updateFields[] = "username = :username";
                $params[':username'] = $userData['username'];
            }
            
            // Handle email update
            if (isset($userData['email'])) {
                // Check if email is already in use by another user
                $existingUser = $this->getUserByEmail($userData['email']);
                if ($existingUser && $existingUser['id'] != $id) {
                    throw new \Exception("Email already in use");
                }
                
                $updateFields[] = "email = :email";
                $params[':email'] = $userData['email'];
            }
            
            // Handle password update
            if (isset($userData['password'])) {
                $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);
                $updateFields[] = "password = :password";
                $params[':password'] = $passwordHash;
            }
            
            // Handle other fields
            if (isset($userData['is_email_confirmed'])) {
                $updateFields[] = "is_email_confirmed = :is_email_confirmed";
                $params[':is_email_confirmed'] = $userData['is_email_confirmed'];
            }
            
            if (isset($userData['avatar_url'])) {
                $updateFields[] = "avatar_url = :avatar_url";
                $params[':avatar_url'] = $userData['avatar_url'];
            }
            
            // If no fields to update, return true
            if (empty($updateFields)) {
                return true;
            }
            
            // Complete the SQL query
            $sql .= implode(", ", $updateFields);
            $sql .= " WHERE id = :id";
            $params[':id'] = $id;
            
            // Prepare and execute the query
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                if ($key === ':id' || $key === ':is_email_confirmed') {
                    $stmt->bindValue($key, $value, \PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            
            return $stmt->execute();
        } catch (\Exception $e) {
            error_log("Error in User::updateUser: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Check if a username is available (not already taken)
     *
     * @param string $username Username to check
     * @return bool True if username is available, false if already taken
     */
    public function isUsernameAvailable($username) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
            $stmt->bindValue(':username', $username);
            $stmt->execute();
            
            $count = (int)$stmt->fetchColumn();
            return $count === 0;
        } catch (\PDOException $e) {
            error_log("Error in User::isUsernameAvailable: " . $e->getMessage());
            // If we encounter an error, we assume the username might be taken
            // as a safety measure
            return false;
        }
    }
} 