<?php
/**
 * Discussions Index View
 * 
 * Displays the discussions management interface
 */
?>

<!-- Page Toolbar -->
<div class="card mb-4">
    <div class="card-body p-3">
        <div class="row align-items-center">
            <!-- Search Form -->
            <div class="col-md-6 mb-2 mb-md-0">
                <form method="get" action="<?php echo \Config\App::get('base_url'); ?>/discussions" class="d-flex">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search discussions..." value="<?php echo htmlspecialchars($search); ?>">
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
                        <a href="<?php echo \Config\App::get('base_url'); ?>/discussions" class="btn <?php echo ($isSticky === null && $isLocked === null) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            All
                        </a>
                        <a href="<?php echo \Config\App::get('base_url'); ?>/discussions?sticky=1" class="btn <?php echo $isSticky === true ? 'btn-warning' : 'btn-outline-warning'; ?>">
                            Sticky
                        </a>
                        <a href="<?php echo \Config\App::get('base_url'); ?>/discussions?locked=1" class="btn <?php echo $isLocked === true ? 'btn-danger' : 'btn-outline-danger'; ?>">
                            Locked
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Discussions Table -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-comments me-1"></i>
        Discussions List
        <span class="badge bg-primary rounded-pill ms-2"><?php echo $total; ?> total</span>
    </div>
    <div class="card-body">
        <?php if (count($discussions) > 0): ?>
            <div class="table-responsive">
                <table id="discussionsTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Posts</th>
                            <th>Participants</th>
                            <th>Tags</th>
                            <th>Author</th>
                            <th>Created</th>
                            <th>Last Post</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($discussions as $discussion): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo \Config\App::get('base_url'); ?>/discussions/view/<?php echo $discussion['id']; ?>" class="fw-bold text-decoration-none">
                                        <?php if ($discussion['is_sticky']): ?>
                                            <i class="fas fa-thumbtack text-warning me-1" title="Sticky"></i>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($discussion['title']); ?>
                                    </a>
                                </td>
                                <td><?php echo $discussion['comment_count']; ?></td>
                                <td><?php echo $discussion['participant_count']; ?></td>
                                <td>
                                    <?php if (isset($discussionTags[$discussion['id']])): ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach ($discussionTags[$discussion['id']] as $tag): ?>
                                                <span class="badge rounded-pill" style="background-color: <?php echo !empty($tag['color']) ? $tag['color'] : '#aaaaaa'; ?>">
                                                    <?php if (!empty($tag['icon'])): ?>
                                                        <i class="fas fa-<?php echo $tag['icon']; ?> me-1"></i>
                                                    <?php endif; ?>
                                                    <?php echo htmlspecialchars($tag['name']); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">No tags</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($discussion['author_username'])): ?>
                                        <?php echo htmlspecialchars($discussion['author_username']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Unknown</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($discussion['created_at'])): ?>
                                        <?php echo date('M j, Y', strtotime($discussion['created_at'])); ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($discussion['last_posted_at'])): ?>
                                        <?php echo date('M j, Y', strtotime($discussion['last_posted_at'])); ?>
                                        <br>
                                        <small class="text-muted">
                                            by <?php echo !empty($discussion['last_posted_username']) ? htmlspecialchars($discussion['last_posted_username']) : 'Unknown'; ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($discussion['is_locked']): ?>
                                        <span class="badge bg-danger">Locked</span>
                                    <?php endif; ?>
                                    <?php if ($discussion['hidden_at']): ?>
                                        <span class="badge bg-secondary">Hidden</span>
                                    <?php endif; ?>
                                    <?php if ($discussion['is_private']): ?>
                                        <span class="badge bg-info">Private</span>
                                    <?php endif; ?>
                                    <?php if (!$discussion['is_approved']): ?>
                                        <span class="badge bg-warning">Unapproved</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo \Config\App::get('base_url'); ?>/discussions/view/<?php echo $discussion['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mt-4">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo \Config\App::get('base_url'); ?>/discussions?page=<?php echo ($page - 1); ?><?php echo $isSticky === true ? '&sticky=1' : ''; ?><?php echo $isLocked === true ? '&locked=1' : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                    Previous
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">Previous</span>
                            </li>
                        <?php endif; ?>
                        
                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $startPage + 4);
                        if ($endPage - $startPage < 4 && $startPage > 1) {
                            $startPage = max(1, $endPage - 4);
                        }
                        ?>
                        
                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo \Config\App::get('base_url'); ?>/discussions?page=<?php echo $i; ?><?php echo $isSticky === true ? '&sticky=1' : ''; ?><?php echo $isLocked === true ? '&locked=1' : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo \Config\App::get('base_url'); ?>/discussions?page=<?php echo ($page + 1); ?><?php echo $isSticky === true ? '&sticky=1' : ''; ?><?php echo $isLocked === true ? '&locked=1' : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                    Next
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">Next</span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info">
                No discussions found. <?php echo !empty($search) || $isSticky !== null || $isLocked !== null ? 'Try adjusting your search or filter criteria.' : ''; ?>
            </div>
        <?php endif; ?>
    </div>
</div> 