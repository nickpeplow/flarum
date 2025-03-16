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
                <form method="get" action="<?php echo \Config\App::get('base_url'); ?>/keywords" class="d-flex">
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
                        <a href="<?php echo \Config\App::get('base_url'); ?>/keywords" class="btn <?php echo $status === null ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            All
                        </a>
                        <a href="<?php echo \Config\App::get('base_url'); ?>/keywords?status=pending" class="btn <?php echo $status === 'pending' ? 'btn-warning' : 'btn-outline-warning'; ?>">
                            Pending
                        </a>
                        <a href="<?php echo \Config\App::get('base_url'); ?>/keywords?status=approved" class="btn <?php echo $status === 'approved' ? 'btn-success' : 'btn-outline-success'; ?>">
                            Approved
                        </a>
                        <a href="<?php echo \Config\App::get('base_url'); ?>/keywords?status=rejected" class="btn <?php echo $status === 'rejected' ? 'btn-danger' : 'btn-outline-danger'; ?>">
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
                <table id="keywordsTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Keyword</th>
                            <th>Tag</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($keywords as $keyword): ?>
                            <tr>
                                <td>#<?php echo $keyword['id']; ?></td>
                                <td><?php echo htmlspecialchars($keyword['keyword']); ?></td>
                                <td>
                                    <?php if (!empty($keyword['tag_id']) && !empty($keyword['tag_name'])): ?>
                                        <span class="badge rounded-pill" style="background-color: <?php echo $keyword['tag_color'] ?? '#6c757d'; ?>">
                                            <i class="fas fa-tag me-1"></i> <?php echo htmlspecialchars($keyword['tag_name']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">None</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo \Models\Keyword::getStatusBadge($keyword['status']); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <!-- Edit Button -->
                                        <button type="button" class="btn btn-sm btn-primary me-1" 
                                                onclick="editKeyword(<?php echo $keyword['id']; ?>, '<?php echo htmlspecialchars(addslashes($keyword['keyword'])); ?>', <?php echo $keyword['tag_id'] ? $keyword['tag_id'] : 'null'; ?>)" 
                                                title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    
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
                                <a class="page-link" href="<?php echo \Config\App::get('base_url'); ?>/keywords?page=<?php echo $page - 1; ?><?php echo !empty($status) ? '&status=' . $status : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
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
                                echo '<li class="page-item"><a class="page-link" href="' . \Config\App::get('base_url') . '/keywords?page=1' . (!empty($status) ? '&status=' . $status : '') . (!empty($search) ? '&search=' . urlencode($search) : '') . '">1</a></li>';
                                if ($startPage > 2) {
                                    echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                }
                            }
                            
                            // Show page numbers
                            for ($i = $startPage; $i <= $endPage; $i++) {
                                echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">';
                                echo '<a class="page-link" href="' . \Config\App::get('base_url') . '/keywords?page=' . $i . (!empty($status) ? '&status=' . $status : '') . (!empty($search) ? '&search=' . urlencode($search) : '') . '">' . $i . '</a>';
                                echo '</li>';
                            }
                            
                            // Show last page and ellipsis if needed
                            if ($endPage < $totalPages) {
                                if ($endPage < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><a class="page-link" href="#">...</a></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="' . \Config\App::get('base_url') . '/keywords?page=' . $totalPages . (!empty($status) ? '&status=' . $status : '') . (!empty($search) ? '&search=' . urlencode($search) : '') . '">' . $totalPages . '</a></li>';
                            }
                        ?>
                        
                        <!-- Next Button -->
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo \Config\App::get('base_url'); ?>/keywords?page=<?php echo $page + 1; ?><?php echo !empty($status) ? '&status=' . $status : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
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
                    <a href="<?php echo \Config\App::get('base_url'); ?>/keywords" class="alert-link">Clear search</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add New Keyword Card -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-plus-circle me-1"></i>
        Add New Keywords
    </div>
    <div class="card-body">
        <form method="post" action="<?php echo \Config\App::get('base_url'); ?>/keywords" class="row g-3">
            <div class="col-md-8">
                <textarea name="new_keyword" class="form-control" rows="4" placeholder="Enter keywords (one per line) for bulk entry..." required></textarea>
                <small class="text-muted">Enter multiple keywords, one per line, for bulk entry.</small>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-plus-circle me-2"></i>Add Keywords
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Keyword Modal -->
<div class="modal fade" id="editKeywordModal" tabindex="-1" aria-labelledby="editKeywordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editKeywordModalLabel">Edit Keyword</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editKeywordForm" method="post" action="<?php echo \Config\App::get('base_url'); ?>/keywords">
                    <input type="hidden" id="editKeywordId" name="keyword_id">
                    <input type="hidden" name="action" value="update">
                    
                    <div class="mb-3">
                        <label for="editKeywordText" class="form-label">Keyword</label>
                        <input type="text" class="form-control" id="editKeywordText" name="keyword_text" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editKeywordTag" class="form-label">Associated Tag</label>
                        <select class="form-select" id="editKeywordTag" name="tag_id">
                            <option value="">None</option>
                            
                            <?php 
                            // Fetch tags from the database - we need to get all parent tags and their children
                            try {
                                global $pdo;
                                $tagModel = new \Models\Tag($pdo);
                                $parentTags = $tagModel->getList(100, 0, '', 0); // Get only parent tags
                            } catch (\Exception $e) {
                                // If there's an error, just show an empty dropdown
                                error_log("Error fetching tags: " . $e->getMessage());
                                $parentTags = [];
                            }
                            
                            foreach ($parentTags as $parentTag): 
                            ?>
                                <!-- Parent tags (categories) are disabled -->
                                <option value="<?php echo $parentTag['id']; ?>" disabled class="fw-bold">
                                    <?php echo htmlspecialchars($parentTag['name']); ?> (Category)
                                </option>
                                
                                <?php 
                                // Get child tags for this parent
                                $childTags = $tagModel->getList(100, 0, '', $parentTag['id']);
                                foreach ($childTags as $childTag): 
                                ?>
                                    <option value="<?php echo $childTag['id']; ?>" class="ps-3">
                                        <?php echo htmlspecialchars($childTag['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                                
                                <!-- Add a visual separator -->
                                <?php if (!empty($childTags)): ?>
                                    <option disabled>──────────</option>
                                <?php endif; ?>
                                
                            <?php endforeach; ?>
                            
                            <!-- Also show top-level tags that aren't parents -->
                            <?php
                            $nonParentTags = [];
                            foreach ($parentTags as $tag) {
                                $isParent = false;
                                foreach ($parentTags as $checkTag) {
                                    if ($checkTag['parent_id'] == $tag['id']) {
                                        $isParent = true;
                                        break;
                                    }
                                }
                                if (!$isParent) {
                                    $nonParentTags[] = $tag;
                                }
                            }
                            
                            if (!empty($nonParentTags)): 
                            ?>
                                <option disabled>── Individual Tags ──</option>
                                <?php foreach ($nonParentTags as $tag): ?>
                                    <option value="<?php echo $tag['id']; ?>">
                                        <?php echo htmlspecialchars($tag['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="updateKeywordBtn">Update Keyword</button>
            </div>
        </div>
    </div>
</div>

<!-- Page Scripts -->
<script>
    // Function to open the Edit Keyword modal
    function editKeyword(id, keyword, tagId) {
        // Set the form values
        document.getElementById('editKeywordId').value = id;
        document.getElementById('editKeywordText').value = keyword;
        
        // Set the tag dropdown
        const tagSelect = document.getElementById('editKeywordTag');
        if (tagId) {
            // Find and select the option with the matching value
            for (let i = 0; i < tagSelect.options.length; i++) {
                if (tagSelect.options[i].value == tagId) {
                    tagSelect.selectedIndex = i;
                    break;
                }
            }
        } else {
            // Select the "None" option
            tagSelect.selectedIndex = 0;
        }
        
        // Open the modal
        const editModal = new bootstrap.Modal(document.getElementById('editKeywordModal'));
        editModal.show();
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Handle the edit keyword form submission
        document.getElementById('updateKeywordBtn').addEventListener('click', function() {
            document.getElementById('editKeywordForm').submit();
        });
        
        // Initialize DataTables
        if (document.getElementById('keywordsTable')) {
            $('#keywordsTable').DataTable({
                responsive: true,
                pageLength: 25,
                language: {
                    search: "Filter records:",
                    info: "Showing _START_ to _END_ of _TOTAL_ keywords",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "<i class='fas fa-chevron-right'></i>",
                        previous: "<i class='fas fa-chevron-left'></i>"
                    }
                },
                columnDefs: [
                    { orderable: false, targets: 4 } // Disable sorting on the actions column
                ]
            });
        }
    });
</script>

<!-- Add DataTables CSS and JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- Custom styles for DataTables -->
<style>
    .dataTables_wrapper .dataTables_length, 
    .dataTables_wrapper .dataTables_filter, 
    .dataTables_wrapper .dataTables_info, 
    .dataTables_wrapper .dataTables_processing, 
    .dataTables_wrapper .dataTables_paginate {
        margin-bottom: 10px;
    }
    .dataTables_filter {
        float: right;
    }
    .dataTables_length {
        float: left;
    }
    .table-responsive {
        overflow-x: auto;
    }
</style> 