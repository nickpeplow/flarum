<?php
// Debugging section - will only appear when needed
$debug = false; // Set to false to hide debug information
if ($debug): 
?>
<!-- Debug Information -->
<div class="card mb-4 bg-light">
    <div class="card-header bg-info text-white">
        <i class="fas fa-bug me-1"></i>
        Debug Information
    </div>
    <div class="card-body">
        <h5>Page Variables</h5>
        <ul>
            <li>Total Keywords: <?php echo isset($total) ? $total : 'Not set'; ?></li>
            <li>Current Page: <?php echo isset($page) ? $page : 'Not set'; ?></li>
            <li>Total Pages: <?php echo isset($totalPages) ? $totalPages : 'Not set'; ?></li>
            <li>Status Filter: <?php echo isset($status) ? ($status === null ? 'null' : $status) : 'Not set'; ?></li>
            <li>Search Query: <?php echo isset($search) ? ($search === '' ? 'Empty' : htmlspecialchars($search)) : 'Not set'; ?></li>
        </ul>
        
        <h5>Database Connection Test</h5>
        <?php
        try {
            global $pdo;
            if (isset($pdo) && $pdo instanceof PDO) {
                echo '<div class="alert alert-success">Database connection established successfully.</div>';
                
                // Test a simple query
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM keywords");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo '<div class="alert alert-info">Keywords count from direct query: ' . $result['count'] . '</div>';
            } else {
                echo '<div class="alert alert-danger">No database connection available!</div>';
            }
        } catch (Exception $e) {
            echo '<div class="alert alert-danger">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
    </div>
</div>
<?php endif; ?>

<!-- Page Toolbar -->
<div class="card mb-4">
    <div class="card-body p-3">
        <div class="row align-items-center">
            <!-- Search Form -->
            <div class="col-md-6 mb-2 mb-md-0">
                <form method="get" action="<?php echo \Config\App::get('base_url'); ?>/keywords.php" class="d-flex">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search keywords..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Filter Buttons -->
            <div class="col-md-6">
                <div class="d-flex justify-content-md-end">
                    <div class="btn-group" role="group">
                        <a href="<?php echo \Config\App::get('base_url'); ?>/keywords.php" class="btn <?php echo $status === null ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            All
                        </a>
                        <a href="<?php echo \Config\App::get('base_url'); ?>/keywords.php?status=pending" class="btn <?php echo $status === 'pending' ? 'btn-warning' : 'btn-outline-warning'; ?>">
                            Pending
                        </a>
                        <a href="<?php echo \Config\App::get('base_url'); ?>/keywords.php?status=approved" class="btn <?php echo $status === 'approved' ? 'btn-success' : 'btn-outline-success'; ?>">
                            Approved
                        </a>
                        <a href="<?php echo \Config\App::get('base_url'); ?>/keywords.php?status=rejected" class="btn <?php echo $status === 'rejected' ? 'btn-danger' : 'btn-outline-danger'; ?>">
                            Rejected
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Keywords Table -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Keywords List
        <span class="badge bg-primary rounded-pill ms-2"><?php echo $total; ?> total</span>
    </div>
    <div class="card-body">
        <?php if (count($keywords) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Keyword</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($keywords as $keyword): ?>
                            <tr>
                                <td>#<?php echo $keyword['id']; ?></td>
                                <td><?php echo htmlspecialchars($keyword['keyword']); ?></td>
                                <td><?php echo \Models\Keyword::getStatusBadge($keyword['status']); ?></td>
                                <td><?php echo date('M j, Y H:i', strtotime($keyword['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <form method="post" action="<?php echo \Config\App::get('base_url'); ?>/keywords" class="d-inline">
                                            <input type="hidden" name="keyword_id" value="<?php echo $keyword['id']; ?>">
                                            
                                            <!-- Approve Button -->
                                            <?php if ($keyword['status'] != \Models\Keyword::STATUS_APPROVED): ?>
                                                <button type="submit" name="action" value="approve" class="btn btn-sm btn-success me-1" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <!-- Pending Button -->
                                            <?php if ($keyword['status'] != \Models\Keyword::STATUS_PENDING): ?>
                                                <button type="submit" name="action" value="pending" class="btn btn-sm btn-warning me-1" title="Mark as Pending">
                                                    <i class="fas fa-clock"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <!-- Reject Button -->
                                            <?php if ($keyword['status'] != \Models\Keyword::STATUS_REJECTED): ?>
                                                <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger me-1" title="Reject">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                            
                                            <!-- Delete Button -->
                                            <button type="submit" name="action" value="delete" class="btn btn-sm btn-secondary" 
                                                   onclick="return confirm('Are you sure you want to delete this keyword? This action cannot be undone.');" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <!-- Previous Button -->
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo \Config\App::get('base_url'); ?>/keywords.php?page=<?php echo $page - 1; ?><?php echo !empty($status) ? '&status=' . $status : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                    &laquo; Previous
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">&laquo; Previous</a>
                            </li>
                        <?php endif; ?>
                        
                        <!-- Page Numbers -->
                        <?php 
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            
                            // Show first page and ellipsis if needed
                            if ($startPage > 1) {
                                echo '<li class="page-item"><a class="page-link" href="' . \Config\App::get('base_url') . '/keywords.php?page=1' . (!empty($status) ? '&status=' . $status : '') . (!empty($search) ? '&search=' . urlencode($search) : '') . '">1</a></li>';
                                if ($startPage > 2) {
                                    echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                }
                            }
                            
                            // Show page numbers
                            for ($i = $startPage; $i <= $endPage; $i++) {
                                echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">';
                                echo '<a class="page-link" href="' . \Config\App::get('base_url') . '/keywords.php?page=' . $i . (!empty($status) ? '&status=' . $status : '') . (!empty($search) ? '&search=' . urlencode($search) : '') . '">' . $i . '</a>';
                                echo '</li>';
                            }
                            
                            // Show last page and ellipsis if needed
                            if ($endPage < $totalPages) {
                                if ($endPage < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="' . \Config\App::get('base_url') . '/keywords.php?page=' . $totalPages . (!empty($status) ? '&status=' . $status : '') . (!empty($search) ? '&search=' . urlencode($search) : '') . '">' . $totalPages . '</a></li>';
                            }
                        ?>
                        
                        <!-- Next Button -->
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo \Config\App::get('base_url'); ?>/keywords.php?page=<?php echo $page + 1; ?><?php echo !empty($status) ? '&status=' . $status : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                    Next &raquo;
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">Next &raquo;</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>No keywords found.
                <?php if (!empty($search)): ?>
                    <a href="<?php echo \Config\App::get('base_url'); ?>/keywords.php" class="alert-link">Clear search</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add New Keyword Card -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-plus-circle me-1"></i>
        Add New Keyword
    </div>
    <div class="card-body">
        <form method="post" action="<?php echo \Config\App::get('base_url'); ?>/keywords.php" class="row g-3">
            <div class="col-md-8">
                <input type="text" name="new_keyword" class="form-control" placeholder="Enter new keyword..." required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-plus-circle me-2"></i>Add Keyword
                </button>
            </div>
        </form>
    </div>
</div> 