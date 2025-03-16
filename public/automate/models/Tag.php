<?php
/**
 * Tag Model
 * 
 * Handles all database operations for the tags table
 */

namespace Models;

use \PDO;

class Tag {
    private $db;
    
    // Constructor - accepts a PDO connection
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get all tags with optional filtering and pagination
     * 
     * @param int $perPage Items per page
     * @param int $offset Offset for pagination
     * @param string $search Search term (optional)
     * @param int $parentId Filter by parent ID (optional)
     * @return array Array of tags
     */
    public function getList($perPage = 25, $offset = 0, $search = '', $parentId = null) {
        $params = [];
        
        // Base query
        $sql = "SELECT t.*, 
                    pt.name as parent_name,
                    (SELECT COUNT(*) FROM discussion_tag WHERE tag_id = t.id) as discussion_count
                FROM tags t
                LEFT JOIN tags pt ON t.parent_id = pt.id
                WHERE 1=1";
        
        // Add search filter if provided
        if (!empty($search)) {
            $sql .= " AND (t.name LIKE :search OR t.slug LIKE :search OR t.description LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        // Add parent filter if provided
        if ($parentId !== null) {
            $sql .= " AND t.parent_id " . ($parentId === 0 ? "IS NULL" : "= :parentId");
            if ($parentId !== 0) {
                $params[':parentId'] = $parentId;
            }
        }
        
        // Add order by clause
        $sql .= " ORDER BY t.position IS NULL, t.position, t.name LIMIT :offset, :perPage";
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
     * Get total count of tags with optional filtering
     * 
     * @param string $search Search term (optional)
     * @param int $parentId Filter by parent ID (optional)
     * @return int Total count
     */
    public function getTotal($search = '', $parentId = null) {
        $params = [];
        
        // Base query
        $sql = "SELECT COUNT(*) FROM tags t WHERE 1=1";
        
        // Add search filter if provided
        if (!empty($search)) {
            $sql .= " AND (t.name LIKE :search OR t.slug LIKE :search OR t.description LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        // Add parent filter if provided
        if ($parentId !== null) {
            $sql .= " AND t.parent_id " . ($parentId === 0 ? "IS NULL" : "= :parentId");
            if ($parentId !== 0) {
                $params[':parentId'] = $parentId;
            }
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
     * Get tag by ID
     * 
     * @param int $id Tag ID
     * @return array|null Tag data or null if not found
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT t.*, pt.name as parent_name
            FROM tags t
            LEFT JOIN tags pt ON t.parent_id = pt.id
            WHERE t.id = :id
        ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all parent tags (tags that can be used as parents)
     * 
     * @param int $excludeId Tag ID to exclude (optional)
     * @return array Array of parent tags
     */
    public function getParentTags($excludeId = null) {
        $sql = "SELECT id, name FROM tags WHERE 1=1";
        $params = [];
        
        if ($excludeId !== null) {
            $sql .= " AND id != :excludeId";
            $params[':excludeId'] = $excludeId;
        }
        
        $sql .= " ORDER BY position IS NULL, position, name";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Add a new tag
     * 
     * @param array $data Tag data
     * @return int|bool The ID of the new tag or false on failure
     */
    public function add($data) {
        $columns = [
            'name', 'slug', 'description', 'color', 'icon', 
            'is_primary', 'is_hidden', 'is_restricted', 
            'position', 'parent_id'
        ];
        
        $fields = [];
        $placeholders = [];
        $values = [];
        
        // Build query parts
        foreach ($columns as $column) {
            if (isset($data[$column]) && $data[$column] !== '') {
                $fields[] = $column;
                $placeholders[] = ":$column";
                $values[":$column"] = $data[$column];
            }
        }
        
        // Add created_at and updated_at
        $fields[] = 'created_at';
        $placeholders[] = 'NOW()';
        $fields[] = 'updated_at';
        $placeholders[] = 'NOW()';
        
        // Generate slug if not provided
        if (!in_array('slug', $fields) && isset($data['name'])) {
            $fields[] = 'slug';
            $placeholders[] = ':slug';
            $values[':slug'] = $this->generateSlug($data['name']);
        }
        
        // Build and execute the query
        $sql = "INSERT INTO tags (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        try {
            $stmt = $this->db->prepare($sql);
            foreach ($values as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            // Handle unique constraint violations
            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'tags_slug_unique') !== false) {
                    throw new Exception("A tag with this slug already exists.");
                }
            }
            throw $e;
        }
    }
    
    /**
     * Update an existing tag
     * 
     * @param int $id Tag ID
     * @param array $data Updated tag data
     * @return bool Success flag
     */
    public function update($id, $data) {
        $columns = [
            'name', 'slug', 'description', 'color', 'icon', 
            'is_primary', 'is_hidden', 'is_restricted', 
            'position', 'parent_id'
        ];
        
        $sets = [];
        $values = [':id' => $id];
        
        // Build SET clause
        foreach ($columns as $column) {
            if (array_key_exists($column, $data)) {
                if ($data[$column] === '' && in_array($column, ['parent_id', 'description', 'color', 'icon', 'position'])) {
                    $sets[] = "$column = NULL";
                } else {
                    $sets[] = "$column = :$column";
                    $values[":$column"] = $data[$column];
                }
            }
        }
        
        // Update updated_at timestamp
        $sets[] = "updated_at = NOW()";
        
        // Generate slug if name was updated but slug wasn't
        if (isset($data['name']) && !isset($data['slug'])) {
            $sets[] = "slug = :slug";
            $values[':slug'] = $this->generateSlug($data['name'], $id);
        }
        
        if (empty($sets)) {
            return true; // Nothing to update
        }
        
        // Build and execute the query
        $sql = "UPDATE tags SET " . implode(', ', $sets) . " WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            foreach ($values as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            // Handle unique constraint violations
            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'tags_slug_unique') !== false) {
                    throw new Exception("A tag with this slug already exists.");
                }
            }
            throw $e;
        }
    }
    
    /**
     * Delete a tag
     * 
     * @param int $id Tag ID
     * @return bool Success flag
     */
    public function delete($id) {
        // First check if this tag has any children
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tags WHERE parent_id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $childCount = $stmt->fetchColumn();
        
        if ($childCount > 0) {
            throw new Exception("Cannot delete this tag because it has child tags. Please reassign or delete the child tags first.");
        }
        
        // Now check if this tag is used in discussions
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM discussion_tag WHERE tag_id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $usageCount = $stmt->fetchColumn();
        
        if ($usageCount > 0) {
            throw new Exception("Cannot delete this tag because it is used in $usageCount discussions. Please remove the tag from all discussions first.");
        }
        
        // Delete the tag
        $stmt = $this->db->prepare("DELETE FROM tags WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Generate a unique slug for a tag
     * 
     * @param string $name Tag name
     * @param int $excludeId Tag ID to exclude (for updates)
     * @return string Unique slug
     */
    private function generateSlug($name, $excludeId = null) {
        // Base slug
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
        
        // Check if the slug exists
        $sql = "SELECT COUNT(*) FROM tags WHERE slug = :slug";
        $params = [':slug' => $slug];
        
        if ($excludeId !== null) {
            $sql .= " AND id != :excludeId";
            $params[':excludeId'] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $count = $stmt->fetchColumn();
        
        // If the slug already exists, append a number
        if ($count > 0) {
            $i = 1;
            do {
                $newSlug = "$slug-$i";
                
                $sql = "SELECT COUNT(*) FROM tags WHERE slug = :slug";
                $params = [':slug' => $newSlug];
                
                if ($excludeId !== null) {
                    $sql .= " AND id != :excludeId";
                    $params[':excludeId'] = $excludeId;
                }
                
                $stmt = $this->db->prepare($sql);
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
                $stmt->execute();
                $count = $stmt->fetchColumn();
                
                $i++;
            } while ($count > 0);
            
            $slug = $newSlug;
        }
        
        return $slug;
    }
    
    /**
     * Get tag statistics
     * 
     * @return array Statistics data
     */
    public function getStatistics() {
        // Total tags
        $totalStmt = $this->db->query("SELECT COUNT(*) FROM tags");
        $total = $totalStmt->fetchColumn();
        
        // Primary tags
        $primaryStmt = $this->db->query("SELECT COUNT(*) FROM tags WHERE is_primary = 1");
        $primary = $primaryStmt->fetchColumn();
        
        // Secondary tags (not primary)
        $secondaryStmt = $this->db->query("SELECT COUNT(*) FROM tags WHERE is_primary = 0");
        $secondary = $secondaryStmt->fetchColumn();
        
        // Hidden tags
        $hiddenStmt = $this->db->query("SELECT COUNT(*) FROM tags WHERE is_hidden = 1");
        $hidden = $hiddenStmt->fetchColumn();
        
        // Restricted tags
        $restrictedStmt = $this->db->query("SELECT COUNT(*) FROM tags WHERE is_restricted = 1");
        $restricted = $restrictedStmt->fetchColumn();
        
        // Most used tags (top 5)
        $mostUsedStmt = $this->db->query("
            SELECT t.id, t.name, COUNT(dt.discussion_id) as count
            FROM tags t
            JOIN discussion_tag dt ON t.id = dt.tag_id
            GROUP BY t.id
            ORDER BY count DESC
            LIMIT 5
        ");
        $mostUsed = $mostUsedStmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'total' => $total,
            'primary' => $primary,
            'secondary' => $secondary,
            'hidden' => $hidden,
            'restricted' => $restricted,
            'mostUsed' => $mostUsed
        ];
    }
    
    /**
     * Get tag badge HTML
     * 
     * @param array $tag Tag data
     * @return string HTML for the badge
     */
    public static function getBadge($tag) {
        $color = !empty($tag['color']) ? $tag['color'] : '#888888';
        $name = htmlspecialchars($tag['name']);
        $style = "background-color: $color; color: " . self::getContrastColor($color) . ";";
        
        $icon = '';
        if (!empty($tag['icon'])) {
            $icon = '<i class="fas fa-' . htmlspecialchars($tag['icon']) . ' me-1"></i>';
        }
        
        return "<span class='badge' style='$style'>$icon$name</span>";
    }
    
    /**
     * Get a contrasting text color (black or white) for a background color
     * 
     * @param string $hexColor Hex color code
     * @return string Text color (#ffffff or #000000)
     */
    private static function getContrastColor($hexColor) {
        // Remove the # if present
        $hexColor = ltrim($hexColor, '#');
        
        // Convert to RGB
        $r = hexdec(substr($hexColor, 0, 2));
        $g = hexdec(substr($hexColor, 2, 2));
        $b = hexdec(substr($hexColor, 4, 2));
        
        // Calculate the brightness (0-255)
        $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
        
        // Return white for dark colors, black for light colors
        return ($brightness > 130) ? '#000000' : '#ffffff';
    }
} 