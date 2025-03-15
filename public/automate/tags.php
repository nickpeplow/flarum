<?php
/**
 * Tags Management Page
 * 
 * This page allows administrators to view, add, edit, and delete tags.
 */

// Start session for messages
session_start();

// Debug output
echo "<!-- Debug: Tags page is loading -->";

// Include database connection and helper functions
require_once __DIR__ . '/../db_connection.php';

// Include components
require_once __DIR__ . '/templates/components.php';

// Include the Tag model
require_once __DIR__ . '/models/Tag.php';

// Initialize Tag model with database connection
$tagModel = new Tag($pdo);

// Set page title and page for active menu highlighting
$pageTitle = 'Manage Tags';
$currentPage = 'tags';
$pageHeader = 'Manage Tags';

// Initialize variables for alerts
$alertType = '';
$alertMessage = '';

// Process form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Import tags from CSV
        if (isset($_POST['action']) && $_POST['action'] === 'import_csv' && isset($_FILES['csv_file'])) {
            $file = $_FILES['csv_file'];
            
            // Validate file upload
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Error uploading file: " . $file['error']);
            }
            
            // Validate file type
            $mimeType = mime_content_type($file['tmp_name']);
            if ($mimeType !== 'text/csv' && $mimeType !== 'text/plain' && $mimeType !== 'application/vnd.ms-excel') {
                throw new Exception("Invalid file type. Please upload a CSV file.");
            }
            
            // Read CSV
            $handle = fopen($file['tmp_name'], 'r');
            if (!$handle) {
                throw new Exception("Error opening file.");
            }
            
            // Read header row
            $header = fgetcsv($handle);
            if (!$header) {
                throw new Exception("Could not read CSV header.");
            }
            
            // Validate required columns
            $requiredColumns = ['id', 'type', 'parent_id', 'name', 'description'];
            $missingColumns = array_diff($requiredColumns, $header);
            if (!empty($missingColumns)) {
                throw new Exception("Missing required columns: " . implode(', ', $missingColumns));
            }
            
            // Get column indexes
            $columns = array_flip($header);
            
            // First pass: Read all entries
            $entries = [];
            $nameToDbIdMap = [];
            $line = 1; // Header is line 1
            
            while (($row = fgetcsv($handle)) !== false) {
                $line++;
                
                // Skip empty rows
                if (count($row) <= 1 && empty($row[0])) {
                    continue;
                }
                
                // Ensure row has enough columns
                if (count($row) < count($requiredColumns)) {
                    throw new Exception("Line $line: Not enough columns.");
                }
                
                $csvId = trim($row[$columns['id']]);
                $type = trim($row[$columns['type']]);
                $parentCsvId = trim($row[$columns['parent_id']]);
                $name = trim($row[$columns['name']]);
                $description = trim($row[$columns['description']]);
                
                // Validate data
                if (empty($csvId)) {
                    throw new Exception("Line $line: Missing ID.");
                }
                if (empty($type)) {
                    throw new Exception("Line $line: Missing type.");
                }
                if (empty($name)) {
                    throw new Exception("Line $line: Missing name.");
                }
                if ($type !== 'category' && $type !== 'tag') {
                    throw new Exception("Line $line: Type must be 'category' or 'tag'.");
                }
                
                // Store entry
                $entries[$csvId] = [
                    'csv_id' => $csvId,
                    'type' => $type,
                    'parent_csv_id' => $parentCsvId,
                    'name' => $name,
                    'description' => $description,
                    'line' => $line
                ];
                
                // Check if tag already exists
                $stmt = $pdo->prepare("SELECT id FROM tags WHERE name = ?");
                $stmt->execute([$name]);
                $existingTag = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existingTag) {
                    $nameToDbIdMap[$name] = $existingTag['id'];
                }
            }
            
            fclose($handle);
            
            // Process and import tags
            $stats = [
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => 0
            ];
            
            $messages = [];
            
            // Group entries by parent ID for position ordering
            $parentGroups = [];
            foreach ($entries as $csvId => $entry) {
                $parentCsvId = $entry['parent_csv_id'] ?: 'root'; // Use 'root' for top-level items
                if (!isset($parentGroups[$parentCsvId])) {
                    $parentGroups[$parentCsvId] = [];
                }
                $parentGroups[$parentCsvId][] = $csvId;
            }
            
            // Reset all positions for a clean hierarchical structure
            try {
                $pdo->exec("UPDATE tags SET position = NULL");
                $messages[] = "All tag positions reset for clean hierarchical ordering.";
            } catch (Exception $e) {
                $messages[] = "Warning: Could not reset tag positions: " . $e->getMessage();
            }
            
            // Initialize position counters for each parent
            $positionCounters = [];
            
            // Second pass: Create/update tags
            foreach ($entries as $entry) {
                try {
                    $parentId = null;
                    
                    // Determine parent ID if specified
                    if (!empty($entry['parent_csv_id'])) {
                        $parentCsvId = $entry['parent_csv_id'];
                        
                        // Check if parent exists in entries
                        if (!isset($entries[$parentCsvId])) {
                            $messages[] = "Warning: Line {$entry['line']}: Parent ID $parentCsvId not found in CSV.";
                            $stats['errors']++;
                            continue;
                        }
                        
                        $parentName = $entries[$parentCsvId]['name'];
                        
                        // Check if parent already exists in database
                        if (isset($nameToDbIdMap[$parentName])) {
                            $parentId = $nameToDbIdMap[$parentName];
                        } else {
                            $messages[] = "Warning: Line {$entry['line']}: Parent '{$parentName}' must be created first.";
                            $stats['errors']++;
                            continue;
                        }
                    }
                    
                    // Tag data
                    $tagData = [
                        'name' => $entry['name'],
                        'description' => $entry['description'],
                        'is_primary' => 1, // Set is_primary to 1 for all tags
                        'parent_id' => $parentId,
                        // Don't set position here, we'll do it for all tags later
                        // Default values for other fields
                        'is_hidden' => 0,
                        'is_restricted' => 0
                    ];
                    
                    // Check if tag exists
                    if (isset($nameToDbIdMap[$entry['name']])) {
                        // Update existing tag
                        $tagId = $nameToDbIdMap[$entry['name']];
                        $tagModel->update($tagId, $tagData);
                        $stats['updated']++;
                    } else {
                        // Create new tag
                        $tagId = $tagModel->add($tagData);
                        $nameToDbIdMap[$entry['name']] = $tagId;
                        $stats['created']++;
                    }
                } catch (Exception $e) {
                    $messages[] = "Error: Line {$entry['line']}: " . $e->getMessage();
                    $stats['errors']++;
                }
            }
            
            // Now reorder ALL tags in the database to ensure consistent sequential ordering
            try {
                // First get all tags ordered by parent_id and name
                $reorderQuery = $pdo->query("
                    SELECT id, parent_id
                    FROM tags
                    ORDER BY COALESCE(parent_id, 0), name
                ");
                
                $allDbTags = $reorderQuery->fetchAll(PDO::FETCH_ASSOC);
                
                // Group tags by parent_id for sequential ordering
                $parentGroups = [];
                foreach ($allDbTags as $dbTag) {
                    $parentKey = $dbTag['parent_id'] ?: 'root';
                    if (!isset($parentGroups[$parentKey])) {
                        $parentGroups[$parentKey] = [];
                    }
                    $parentGroups[$parentKey][] = $dbTag['id'];
                }
                
                // Update positions for all tags
                $updateStmt = $pdo->prepare("UPDATE tags SET position = ? WHERE id = ?");
                
                foreach ($parentGroups as $parentKey => $tagIds) {
                    $position = 1;
                    foreach ($tagIds as $tagId) {
                        $updateStmt->execute([$position, $tagId]);
                        $position++;
                    }
                }
                
                $messages[] = "All tags in database have been reordered with sequential positions.";
            } catch (Exception $e) {
                $messages[] = "Warning: Could not reorder all tags: " . $e->getMessage();
            }
            
            // Store success message and details in session
            $_SESSION['alert_type'] = 'success';
            $_SESSION['alert_message'] = "CSV import completed: {$stats['created']} tags created, {$stats['updated']} updated, {$stats['errors']} errors.";
            $_SESSION['import_messages'] = $messages;
            
            // Redirect to prevent form resubmission
            header('Location: tags.php');
            exit;
        }
        
        // Add new tag
        else if (isset($_POST['action']) && $_POST['action'] === 'add') {
            $tagData = [
                'name' => sanitizeInput($_POST['name']),
                'slug' => !empty($_POST['slug']) ? sanitizeInput($_POST['slug']) : null,
                'description' => sanitizeInput($_POST['description']),
                'color' => sanitizeInput($_POST['color']),
                'icon' => sanitizeInput($_POST['icon']),
                'is_primary' => isset($_POST['is_primary']) ? 1 : 0,
                'is_hidden' => isset($_POST['is_hidden']) ? 1 : 0,
                'is_restricted' => isset($_POST['is_restricted']) ? 1 : 0,
                'position' => !empty($_POST['position']) ? (int)$_POST['position'] : null,
                'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null
            ];
            
            $tagId = $tagModel->add($tagData);
            
            // Store success message in session
            $_SESSION['alert_type'] = 'success';
            $_SESSION['alert_message'] = "Tag \"{$tagData['name']}\" added successfully.";
            
            // Redirect to prevent form resubmission
            header('Location: tags.php');
            exit;
        }
        
        // Update existing tag
        else if (isset($_POST['action']) && $_POST['action'] === 'edit' && isset($_POST['id'])) {
            $tagId = (int)$_POST['id'];
            $tagData = [
                'name' => sanitizeInput($_POST['name']),
                'slug' => !empty($_POST['slug']) ? sanitizeInput($_POST['slug']) : null,
                'description' => sanitizeInput($_POST['description']),
                'color' => sanitizeInput($_POST['color']),
                'icon' => sanitizeInput($_POST['icon']),
                'is_primary' => isset($_POST['is_primary']) ? 1 : 0,
                'is_hidden' => isset($_POST['is_hidden']) ? 1 : 0,
                'is_restricted' => isset($_POST['is_restricted']) ? 1 : 0,
                'position' => !empty($_POST['position']) ? (int)$_POST['position'] : null,
                'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null
            ];
            
            $tagModel->update($tagId, $tagData);
            
            // Store success message in session
            $_SESSION['alert_type'] = 'success';
            $_SESSION['alert_message'] = "Tag \"{$tagData['name']}\" updated successfully.";
            
            // Redirect to prevent form resubmission
            header('Location: tags.php');
            exit;
        }
        
        // Delete tag
        else if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
            $tagId = (int)$_POST['id'];
            $tag = $tagModel->getById($tagId);
            
            if ($tag) {
                $tagModel->delete($tagId);
                
                // Store success message in session
                $_SESSION['alert_type'] = 'success';
                $_SESSION['alert_message'] = "Tag \"{$tag['name']}\" deleted successfully.";
            } else {
                // Store error message in session
                $_SESSION['alert_type'] = 'danger';
                $_SESSION['alert_message'] = "Tag not found.";
            }
            
            // Redirect to prevent form resubmission
            header('Location: tags.php');
            exit;
        }
    } catch (Exception $e) {
        // Store error message in session
        $_SESSION['alert_type'] = 'danger';
        $_SESSION['alert_message'] = "Error: " . $e->getMessage();
        
        // Redirect to prevent form resubmission
        header('Location: tags.php');
        exit;
    }
}

// Get alert messages from session if they exist
if (isset($_SESSION['alert_type']) && isset($_SESSION['alert_message'])) {
    $alertType = $_SESSION['alert_type'];
    $alertMessage = $_SESSION['alert_message'];
    
    // Clear the session variables
    unset($_SESSION['alert_type']);
    unset($_SESSION['alert_message']);
}

// Get filter parameters
$search = sanitizeInput(get_array_value($_GET, 'search', ''));
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 100; // Show more tags per page to see the hierarchy better
$offset = ($page - 1) * $perPage;

// Get all tags
$allTags = $tagModel->getList(1000, 0); // Get all tags to build hierarchy
$totalTags = count($allTags);
$totalPages = ceil($totalTags / $perPage);

// Get parent tags for dropdown
$parentTags = $tagModel->getParentTags();

// Organize tags into a hierarchy
$tagHierarchy = [];
$childTags = [];

// First, identify all child tags
foreach ($allTags as $tag) {
    if (!empty($tag['parent_id'])) {
        if (!isset($childTags[$tag['parent_id']])) {
            $childTags[$tag['parent_id']] = [];
        }
        $childTags[$tag['parent_id']][] = $tag;
    }
}

// Then, get all root tags (no parent)
foreach ($allTags as $tag) {
    if (empty($tag['parent_id'])) {
        $tagHierarchy[] = $tag;
    }
}

// Sort root tags by position then name
usort($tagHierarchy, function($a, $b) {
    if (isset($a['position']) && isset($b['position']) && $a['position'] !== $b['position']) {
        return $a['position'] <=> $b['position'];
    }
    return $a['name'] <=> $b['name'];
});

// Get tag for editing if ID is provided
$editTag = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editTag = $tagModel->getById((int)$_GET['edit']);
}

// Build pagination URL
$paginationUrl = "tags.php?";
if (!empty($search)) {
    $paginationUrl .= "search=" . urlencode($search) . "&";
}

// Function to recursively display tag hierarchy
function displayTagHierarchy($tags, $childTags, $level = 0) {
    $html = '';
    
    foreach ($tags as $tag) {
        $hasChildren = isset($childTags[$tag['id']]);
        $indentation = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
        $prefix = $level > 0 ? '<i class="fas fa-level-down-alt fa-rotate-90 text-muted me-2"></i>' : '';
        
        // Format tag badge with color
        $tagBadge = Tag::getBadge($tag);
        
        // Create indicators
        $isPrimary = $tag['is_primary'] ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>';
        $isHidden = $tag['is_hidden'] ? '<i class="fas fa-check text-warning"></i>' : '<i class="fas fa-times text-muted"></i>';
        $isRestricted = $tag['is_restricted'] ? '<i class="fas fa-check text-danger"></i>' : '<i class="fas fa-times text-muted"></i>';
        
        // Usage count
        $usageCount = !empty($tag['discussion_count']) ? $tag['discussion_count'] : (!empty($tag['count']) ? $tag['count'] : 0);
        
        // Actions buttons
        $actions = '
            <a href="tags.php?edit=' . $tag['id'] . '" class="btn btn-sm btn-primary">
                <i class="fas fa-edit"></i>
            </a>
            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" 
                    data-bs-target="#deleteTagModal" 
                    data-tag-id="' . $tag['id'] . '" 
                    data-tag-name="' . htmlspecialchars($tag['name']) . '">
                <i class="fas fa-trash"></i>
            </button>
        ';
        
        $html .= '<tr class="' . ($level > 0 ? 'tag-child' : 'tag-parent') . '">';
        $html .= '<td>' . $tag['id'] . '</td>';
        $html .= '<td>' . $indentation . $prefix . $tagBadge . ' <small class="text-muted">(' . htmlspecialchars($tag['slug']) . ')</small></td>';
        $html .= '<td>' . $isPrimary . '</td>';
        $html .= '<td>' . $isHidden . '</td>';
        $html .= '<td>' . $isRestricted . '</td>';
        $html .= '<td>' . $usageCount . '</td>';
        $html .= '<td>' . $actions . '</td>';
        $html .= '</tr>';
        
        // Recursively display children
        if ($hasChildren) {
            $childrenSorted = $childTags[$tag['id']];
            // Sort children by position then name
            usort($childrenSorted, function($a, $b) {
                if (isset($a['position']) && isset($b['position']) && $a['position'] !== $b['position']) {
                    return $a['position'] <=> $b['position'];
                }
                return $a['name'] <=> $b['name'];
            });
            
            $html .= displayTagHierarchy($childrenSorted, $childTags, $level + 1);
        }
    }
    
    return $html;
}

// Start output buffering for page content
ob_start();
?>

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Manage Tags</h1>
    <div>
        <button type="button" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm me-2" 
                data-bs-toggle="modal" data-bs-target="#importCsvModal">
            <i class="fas fa-file-import fa-sm text-white-50"></i> Import CSV
        </button>
        <button type="button" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" 
                data-bs-toggle="modal" data-bs-target="#addTagModal">
            <i class="fas fa-plus fa-sm text-white-50"></i> Add New Tag
        </button>
    </div>
</div>

<!-- Alert Messages -->
<?php if (!empty($alertMessage)): ?>
<div class="alert alert-<?php echo $alertType; ?> alert-dismissible fade show" role="alert">
    <?php echo $alertMessage; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Import Messages -->
<?php if (isset($_SESSION['import_messages']) && !empty($_SESSION['import_messages'])): ?>
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Import Results</h6>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('importResults').classList.toggle('d-none');">
            Toggle Details
        </button>
    </div>
    <div class="card-body">
        <div id="importResults" class="d-none">
            <ul class="list-group">
                <?php foreach ($_SESSION['import_messages'] as $message): ?>
                    <li class="list-group-item <?php echo strpos($message, 'Error') !== false ? 'list-group-item-danger' : 'list-group-item-warning'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<?php 
    // Clear import messages
    unset($_SESSION['import_messages']);
endif; 
?>

<!-- Content Row -->
<div class="row">
    <?php if ($editTag): ?>
    <!-- Edit Tag Form -->
    <div class="col-12 mb-4">
        <?php 
        $parentTagOptions = array_map(function($tag) use ($editTag) {
            $selected = ((int)$tag['id'] === (int)$editTag['parent_id']) ? 'selected' : '';
            return "<option value=\"{$tag['id']}\" {$selected}>{$tag['name']}</option>";
        }, $tagModel->getParentTags($editTag['id']));
        ?>
        <?php echo contentCard("Edit Tag: {$editTag['name']}", '
            <form action="tags.php" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="' . $editTag['id'] . '">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="' . htmlspecialchars($editTag['name']) . '" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" class="form-control" id="slug" name="slug" 
                                   value="' . htmlspecialchars($editTag['slug']) . '">
                            <div class="form-text">Leave empty to auto-generate from name.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3">' . 
                                htmlspecialchars($editTag['description'] ?? '') . 
                            '</textarea>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="color" class="form-label">Color</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color" id="color" 
                                           name="color" value="' . htmlspecialchars($editTag['color'] ?? '#6c757d') . '">
                                    <input type="text" class="form-control" id="colorText" 
                                           value="' . htmlspecialchars($editTag['color'] ?? '#6c757d') . '" 
                                           oninput="document.getElementById(\'color\').value = this.value">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="icon" class="form-label">Icon</label>
                                <div class="input-group">
                                    <span class="input-group-text">fa-</span>
                                    <input type="text" class="form-control" id="icon" name="icon" 
                                           value="' . htmlspecialchars($editTag['icon'] ?? '') . '" 
                                           placeholder="tag">
                                </div>
                                <div class="form-text">
                                    <a href="https://fontawesome.com/search?m=free&o=r" target="_blank">
                                        Browse Free Icons
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="parent_id" class="form-label">Parent Tag</label>
                                <select class="form-select" id="parent_id" name="parent_id">
                                    <option value="">No Parent</option>
                                    ' . implode('', $parentTagOptions) . '
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="position" class="form-label">Position</label>
                                <input type="number" class="form-control" id="position" name="position" 
                                       value="' . htmlspecialchars($editTag['position'] ?? '') . '">
                            </div>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_primary" 
                                           name="is_primary" ' . ($editTag['is_primary'] ? 'checked' : '') . '>
                                    <label class="form-check-label" for="is_primary">
                                        Primary Tag
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_hidden" 
                                           name="is_hidden" ' . ($editTag['is_hidden'] ? 'checked' : '') . '>
                                    <label class="form-check-label" for="is_hidden">
                                        Hidden
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_restricted" 
                                           name="is_restricted" ' . ($editTag['is_restricted'] ? 'checked' : '') . '>
                                    <label class="form-check-label" for="is_restricted">
                                        Restricted
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mt-4">
                    <a href="tags.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        '); ?>
    </div>
    <?php endif; ?>
    
    <!-- Tags List -->
    <div class="col-12">
        <?php echo contentCard('Tag Hierarchy', '
            <!-- Filter Form -->
            <form action="tags.php" method="GET" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search tags..." 
                                   name="search" value="' . htmlspecialchars($search) . '">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <a href="tags.php" class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>

            <!-- Tags Table -->
            ' . ($totalTags > 0 ? '
            <div class="table-responsive">
                <table class="table table-hover" id="tagsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tag</th>
                            <th>Primary</th>
                            <th>Hidden</th>
                            <th>Restricted</th>
                            <th>Usage</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ' . displayTagHierarchy($tagHierarchy, $childTags) . '
                    </tbody>
                </table>
            </div>
            ' : '<div class="alert alert-info">No tags found matching your criteria.</div>') . '
            
            <!-- Pagination -->
            ' . ($totalPages > 1 ? pagination($page, $totalPages, $paginationUrl) : '') . '
        '); ?>
    </div>
</div>

<!-- Add Tag Modal -->
<?php echo modal('addTagModal', 'Add New Tag', '
    <form action="tags.php" method="POST" id="addTagForm">
        <input type="hidden" name="action" value="add">
        
        <div class="mb-3">
            <label for="add-name" class="form-label">Name</label>
            <input type="text" class="form-control" id="add-name" name="name" required>
        </div>
        
        <div class="mb-3">
            <label for="add-slug" class="form-label">Slug</label>
            <input type="text" class="form-control" id="add-slug" name="slug">
            <div class="form-text">Leave empty to auto-generate from name.</div>
        </div>
        
        <div class="mb-3">
            <label for="add-description" class="form-label">Description</label>
            <textarea class="form-control" id="add-description" name="description" rows="3"></textarea>
        </div>
        
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="add-color" class="form-label">Color</label>
                <div class="input-group">
                    <input type="color" class="form-control form-control-color" id="add-color" 
                           name="color" value="#6c757d">
                    <input type="text" class="form-control" id="add-colorText" 
                           value="#6c757d" 
                           oninput="document.getElementById(\'add-color\').value = this.value">
                </div>
            </div>
            <div class="col-md-6">
                <label for="add-icon" class="form-label">Icon</label>
                <div class="input-group">
                    <span class="input-group-text">fa-</span>
                    <input type="text" class="form-control" id="add-icon" name="icon" 
                           placeholder="tag">
                </div>
                <div class="form-text">
                    <a href="https://fontawesome.com/search?m=free&o=r" target="_blank">
                        Browse Free Icons
                    </a>
                </div>
            </div>
        </div>
        
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="add-parent_id" class="form-label">Parent Tag</label>
                <select class="form-select" id="add-parent_id" name="parent_id">
                    <option value="">No Parent</option>
                    ' . implode('', array_map(function($tag) {
                        return "<option value=\"{$tag['id']}\">{$tag['name']}</option>";
                    }, $parentTags)) . '
                </select>
            </div>
            <div class="col-md-6">
                <label for="add-position" class="form-label">Position</label>
                <input type="number" class="form-control" id="add-position" name="position">
            </div>
        </div>
        
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="add-is_primary" 
                           name="is_primary">
                    <label class="form-check-label" for="add-is_primary">
                        Primary Tag
                    </label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="add-is_hidden" 
                           name="is_hidden">
                    <label class="form-check-label" for="add-is_hidden">
                        Hidden
                    </label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="add-is_restricted" 
                           name="is_restricted">
                    <label class="form-check-label" for="add-is_restricted">
                        Restricted
                    </label>
                </div>
            </div>
        </div>
    </form>
', '
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button type="submit" form="addTagForm" class="btn btn-primary">Add Tag</button>
'); ?>

<!-- Delete Tag Confirmation Modal -->
<?php echo modal('deleteTagModal', 'Delete Tag', '
    <p>Are you sure you want to delete the tag "<span id="delete-tag-name"></span>"?</p>
    <p class="text-danger">This action cannot be undone.</p>
    <form id="deleteTagForm" action="tags.php" method="POST">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="delete-tag-id">
    </form>
', '
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button type="submit" form="deleteTagForm" class="btn btn-danger">Delete</button>
', 'sm'); ?>

<!-- Import CSV Modal -->
<?php echo modal('importCsvModal', 'Import Tags from CSV', '
    <form action="tags.php" method="POST" id="importCsvForm" enctype="multipart/form-data">
        <input type="hidden" name="action" value="import_csv">
        
        <div class="mb-3">
            <label for="csv_file" class="form-label">CSV File</label>
            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
        </div>
        
        <div class="alert alert-info">
            <h6 class="alert-heading">CSV Format Requirements:</h6>
            <p class="mb-0">Your CSV file should have the following columns:</p>
            <ul class="mb-2">
                <li><strong>id</strong> - Numeric ID (only used for hierarchy in the CSV)</li>
                <li><strong>type</strong> - Either "category" or "tag"</li>
                <li><strong>parent_id</strong> - The CSV ID of the parent tag/category (or empty for top-level)</li>
                <li><strong>name</strong> - The name of the tag/category</li>
                <li><strong>description</strong> - A description (optional)</li>
            </ul>
            <p class="mb-0"><strong>Note:</strong> All imported tags will be marked as primary. Parent tags must come before child tags in the CSV. Tags will be positioned sequentially within their parent category, preserving the order from the CSV file.</p>
        </div>
        
        <div class="alert alert-warning">
            <p class="mb-0"><strong>Important:</strong> Existing tags with the same name will be updated.</p>
        </div>
        
        <div class="alert alert-warning">
            <p class="mb-0"><strong>Note:</strong> All tag positions will be reset and renumbered during import to ensure a clean hierarchical structure.</p>
        </div>
    </form>
', '
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button type="submit" form="importCsvForm" class="btn btn-success">Upload & Import</button>
', 'lg'); ?>

<style>
/* Styling for hierarchical tags */
.tag-child {
    background-color: #f8f9fa;
}
.tag-parent {
    background-color: #ffffff;
    font-weight: 500;
}
</style>

<script>
// Script to handle the delete tag modal
document.addEventListener('DOMContentLoaded', function() {
    // Set up the delete tag modal
    const deleteTagModal = document.getElementById('deleteTagModal');
    if (deleteTagModal) {
        deleteTagModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const tagId = button.getAttribute('data-tag-id');
            const tagName = button.getAttribute('data-tag-name');
            
            document.getElementById('delete-tag-id').value = tagId;
            document.getElementById('delete-tag-name').textContent = tagName;
        });
    }
    
    // Color input sync for add form
    const addColorInput = document.getElementById('add-color');
    const addColorText = document.getElementById('add-colorText');
    if (addColorInput && addColorText) {
        addColorInput.addEventListener('input', function() {
            addColorText.value = this.value;
        });
    }
    
    // Color input sync for edit form
    const editColorInput = document.getElementById('color');
    const editColorText = document.getElementById('colorText');
    if (editColorInput && editColorText) {
        editColorInput.addEventListener('input', function() {
            editColorText.value = this.value;
        });
    }
});
</script>

<?php
// Debug output
echo "<!-- Debug: Before getting page content -->";

// Get the page content
$pageContent = ob_get_clean();

// Another debug output, this will be direct output
echo "<!-- Debug: After getting page content -->";

// Include the layout template
require_once __DIR__ . '/templates/layout.php';
?> 