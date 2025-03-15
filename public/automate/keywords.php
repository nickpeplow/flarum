<?php
/**
 * Keywords Automation Dashboard - Keywords Management
 * 
 * This page allows administrators to manage keywords in the database.
 */

// Database connection
require_once __DIR__ . '/../db_connection.php';

// Include component functions
require_once __DIR__ . '/templates/components.php';

// Models
require_once 'models/Keyword.php';

// Initialize models
$keywordModel = new Keyword($pdo);

// Page metadata
$pageTitle = 'Keywords Management - Keywords Automation';
$pageHeader = 'Keywords Management';

// Initialize alerts array
$alerts = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle status updates
        if (isset($_POST['action']) && isset($_POST['keyword_id'])) {
            $keywordId = (int)$_POST['keyword_id'];
            $action = $_POST['action'];
            
            switch ($action) {
                case 'approve':
                    $keywordModel->updateStatus($keywordId, 'approved');
                    $alerts[] = ['type' => 'success', 'message' => 'Keyword approved successfully.'];
                    break;
                case 'pending':
                    $keywordModel->updateStatus($keywordId, 'pending');
                    $alerts[] = ['type' => 'warning', 'message' => 'Keyword marked as pending.'];
                    break;
                case 'reject':
                    $keywordModel->updateStatus($keywordId, 'rejected');
                    $alerts[] = ['type' => 'danger', 'message' => 'Keyword rejected.'];
                    break;
                case 'delete':
                    $keywordModel->delete($keywordId);
                    $alerts[] = ['type' => 'danger', 'message' => 'Keyword deleted permanently.'];
                    break;
            }
        }
        
        // Handle adding new keyword
        if (isset($_POST['add_keyword'])) {
            $newKeyword = sanitizeInput($_POST['keyword']);
            $postId = sanitizeInput($_POST['post_id']);
            $tagId = sanitizeInput($_POST['tag_id']);
            
            if (empty($newKeyword)) {
                throw new Exception('Keyword cannot be empty.');
            }
            
            $keywordModel->add($newKeyword, $postId, $tagId);
            $alerts[] = ['type' => 'success', 'message' => 'New keyword added successfully.'];
        }
    } catch (Exception $e) {
        $alerts[] = ['type' => 'danger', 'message' => 'Error: ' . $e->getMessage()];
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Filtering
$status = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Get keyword list
$keywordsList = $keywordModel->getList($perPage, $offset, $status, $search);
$total = $keywordModel->getTotal($status, $search);
$totalPages = ceil($total / $perPage);

// Build query parameters for pagination links
$queryParams = [];
if (!empty($status)) {
    $queryParams['status'] = $status;
}
if (!empty($search)) {
    $queryParams['search'] = $search;
}

// Build page content
ob_start();
?>

<!-- Keyword Filter Form -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <i class="fas fa-filter me-1"></i> Filter Keywords
    </div>
    <div class="card-body">
        <form method="GET" action="keywords.php" class="row g-3">
            <div class="col-md-4">
                <?php echo formGroup('Status', '
                <select name="status" id="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending"' . ($status === 'pending' ? ' selected' : '') . '>Pending</option>
                    <option value="approved"' . ($status === 'approved' ? ' selected' : '') . '>Approved</option>
                    <option value="rejected"' . ($status === 'rejected' ? ' selected' : '') . '>Rejected</option>
                </select>', 'status'); ?>
            </div>
            <div class="col-md-6">
                <?php echo formGroup('Search', '
                <input type="text" class="form-control" id="search" name="search" placeholder="Search keywords..." value="' . htmlspecialchars($search) . '">', 'search'); ?>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <div class="w-100">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Keywords Table -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
        <div>
            <i class="fas fa-table me-1"></i> Keywords
            <?php if ($status || $search): ?>
                <span class="badge bg-light text-dark ms-2">Filtered</span>
            <?php endif; ?>
        </div>
        <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addKeywordModal">
            <i class="fas fa-plus me-1"></i> Add New Keyword
        </button>
    </div>
    <div class="card-body">
        <?php if (count($keywordsList) > 0): ?>
            <div class="table-responsive">
                <?php
                $tableRows = [];
                foreach ($keywordsList as $keyword) {
                    $actionButtons = '
                    <div class="btn-group btn-group-sm">
                        <form method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure you want to approve this keyword?\');">
                            <input type="hidden" name="keyword_id" value="' . $keyword['id'] . '">
                            <input type="hidden" name="action" value="approve">
                            <button type="submit" class="btn btn-success me-1" data-bs-toggle="tooltip" data-bs-title="Approve">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        <form method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure you want to mark this keyword as pending?\');">
                            <input type="hidden" name="keyword_id" value="' . $keyword['id'] . '">
                            <input type="hidden" name="action" value="pending">
                            <button type="submit" class="btn btn-warning me-1" data-bs-toggle="tooltip" data-bs-title="Mark as Pending">
                                <i class="fas fa-clock"></i>
                            </button>
                        </form>
                        <form method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure you want to reject this keyword?\');">
                            <input type="hidden" name="keyword_id" value="' . $keyword['id'] . '">
                            <input type="hidden" name="action" value="reject">
                            <button type="submit" class="btn btn-danger me-1" data-bs-toggle="tooltip" data-bs-title="Reject">
                                <i class="fas fa-ban"></i>
                            </button>
                        </form>
                        <form method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure you want to DELETE this keyword? This action cannot be undone!\');">
                            <input type="hidden" name="keyword_id" value="' . $keyword['id'] . '">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-dark" data-bs-toggle="tooltip" data-bs-title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>';
                    
                    $tableRows[] = [
                        $keyword['id'],
                        htmlspecialchars($keyword['keyword']),
                        Keyword::getStatusBadge($keyword['status']),
                        !empty($keyword['post_id']) ? '<a href="../flarum/d/' . $keyword['post_id'] . '" target="_blank">' . $keyword['post_id'] . ' <i class="fas fa-external-link-alt"></i></a>' : '<span class="text-muted">None</span>',
                        !empty($keyword['tag_id']) ? $keyword['tag_id'] : '<span class="text-muted">None</span>',
                        date('M j, Y g:i A', strtotime($keyword['created_at'])),
                        $actionButtons
                    ];
                }
                
                echo dataTable(['ID', 'Keyword', 'Status', 'Post ID', 'Tag ID', 'Created', 'Actions'], $tableRows, 'table-hover');
                ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="d-flex justify-content-center mt-4">
                    <?php echo pagination($page, $totalPages, 'keywords.php', $queryParams); ?>
                </div>
            <?php endif; ?>
            
            <div class="text-muted mt-3">
                Showing <?php echo min($total, $offset + 1); ?> to <?php echo min($total, $offset + count($keywordsList)); ?> of <?php echo $total; ?> keywords
                <?php if ($status): ?>
                    with status <strong><?php echo htmlspecialchars(ucfirst($status)); ?></strong>
                <?php endif; ?>
                <?php if ($search): ?>
                    matching <strong><?php echo htmlspecialchars($search); ?></strong>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <?php if ($status || $search): ?>
                    No keywords found matching your filter criteria.
                <?php else: ?>
                    No keywords have been added yet.
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Keyword Modal -->
<?php
$modalBody = '
<form method="POST" action="keywords.php" id="addKeywordForm">
    <input type="hidden" name="add_keyword" value="1">
    
    <div class="mb-3">
        <label for="keyword" class="form-label">Keyword</label>
        <input type="text" class="form-control" id="keyword" name="keyword" required>
    </div>
    
    <div class="mb-3">
        <label for="post_id" class="form-label">Post ID (Optional)</label>
        <input type="text" class="form-control" id="post_id" name="post_id">
        <div class="form-text">The ID of the post where this keyword appears.</div>
    </div>
    
    <div class="mb-3">
        <label for="tag_id" class="form-label">Tag ID (Optional)</label>
        <input type="text" class="form-control" id="tag_id" name="tag_id">
        <div class="form-text">The ID of the tag associated with this keyword.</div>
    </div>
</form>';

$modalFooter = '
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="submit" form="addKeywordForm" class="btn btn-primary">
    <i class="fas fa-plus me-1"></i> Add Keyword
</button>';

echo modal('addKeywordModal', '<i class="fas fa-plus me-1"></i> Add New Keyword', $modalBody, $modalFooter);
?>

<?php
$content = ob_get_clean();

// Include the layout template
include __DIR__ . '/templates/layout.php';
?> 