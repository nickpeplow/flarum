<?php
/**
 * Discussion View Template
 * 
 * Displays a single discussion with its posts
 */
?>

<!-- Back Button -->
<div class="mb-4">
    <a href="<?php echo \Config\App::get('base_url'); ?>/discussions" class="btn btn-outline-primary">
        <i class="fas fa-arrow-left me-1"></i> Back to Discussions
    </a>
</div>

<!-- Discussion Header -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <div class="d-flex justify-content-between align-items-start">
            <h5 class="mb-0">
                <?php if ($discussion['is_sticky']): ?>
                    <i class="fas fa-thumbtack text-warning me-2" title="Sticky Discussion"></i>
                <?php endif; ?>
                <?php echo htmlspecialchars($discussion['title']); ?>
            </h5>
            <div>
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
            </div>
        </div>
        <?php if (!empty($tags)): ?>
            <div class="mt-2">
                <?php foreach ($tags as $tag): ?>
                    <span class="badge rounded-pill" style="background-color: <?php echo !empty($tag['color']) ? $tag['color'] : '#aaaaaa'; ?>">
                        <?php if (!empty($tag['icon'])): ?>
                            <i class="fas fa-<?php echo $tag['icon']; ?> me-1"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($tag['name']); ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-user me-2"></i>Author:</span>
                        <span class="badge bg-primary rounded-pill"><?php echo htmlspecialchars($discussion['author_username'] ?? 'Unknown'); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-calendar me-2"></i>Created:</span>
                        <span class="badge bg-primary rounded-pill"><?php echo date('M j, Y', strtotime($discussion['created_at'])); ?></span>
                    </li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-comments me-2"></i>Posts:</span>
                        <span class="badge bg-primary rounded-pill"><?php echo $discussion['comment_count']; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-users me-2"></i>Participants:</span>
                        <span class="badge bg-primary rounded-pill"><?php echo $discussion['participant_count']; ?></span>
                    </li>
                </ul>
            </div>
        </div>
        
        <?php if (!empty($discussion['last_posted_at'])): ?>
            <div class="alert alert-light mt-3">
                <small>
                    <i class="fas fa-clock me-1"></i> Last post by 
                    <strong><?php echo htmlspecialchars($discussion['last_posted_username'] ?? 'Unknown'); ?></strong> 
                    on <?php echo date('M j, Y g:i A', strtotime($discussion['last_posted_at'])); ?>
                </small>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Posts List -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-comments me-1"></i>
        Posts
        <span class="badge bg-primary rounded-pill ms-2"><?php echo $totalPosts; ?> total</span>
    </div>
    <div class="card-body">
        <!-- Pagination Controls - Top -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mb-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo \Config\App::get('base_url'); ?>/discussions/view/<?php echo $discussion['id']; ?>?page=<?php echo ($page - 1); ?>">
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
                            <a class="page-link" href="<?php echo \Config\App::get('base_url'); ?>/discussions/view/<?php echo $discussion['id']; ?>?page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo \Config\App::get('base_url'); ?>/discussions/view/<?php echo $discussion['id']; ?>?page=<?php echo ($page + 1); ?>">
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

        <?php if (count($posts) > 0): ?>
            <?php foreach ($posts as $index => $post): ?>
                <div class="card mb-3 <?php echo $index % 2 ? 'bg-light' : ''; ?>">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo htmlspecialchars($post['author_username'] ?? 'Unknown'); ?></strong>
                                <small class="text-muted ms-2">
                                    <i class="fas fa-clock me-1"></i> <?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?>
                                </small>
                                <?php if ($post['edited_at']): ?>
                                    <small class="text-muted ms-2">
                                        (edited <?php echo date('M j, Y g:i A', strtotime($post['edited_at'])); ?>)
                                    </small>
                                <?php endif; ?>
                            </div>
                            <div>
                                <span class="badge bg-secondary">#<?php echo $post['number']; ?></span>
                                <?php if ($post['is_private']): ?>
                                    <span class="badge bg-info">Private</span>
                                <?php endif; ?>
                                <?php if (!$post['is_approved']): ?>
                                    <span class="badge bg-warning">Unapproved</span>
                                <?php endif; ?>
                                <?php if ($post['hidden_at']): ?>
                                    <span class="badge bg-secondary">Hidden</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- Pagination Controls - Bottom -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo \Config\App::get('base_url'); ?>/discussions/view/<?php echo $discussion['id']; ?>?page=<?php echo ($page - 1); ?>">
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
                                <a class="page-link" href="<?php echo \Config\App::get('base_url'); ?>/discussions/view/<?php echo $discussion['id']; ?>?page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo \Config\App::get('base_url'); ?>/discussions/view/<?php echo $discussion['id']; ?>?page=<?php echo ($page + 1); ?>">
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
                No posts found for this discussion.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Keywords Association Card -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-tags me-1"></i>
        Discussion Tags
    </div>
    <div class="card-body">
        <?php if (!empty($tags)): ?>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($tags as $tag): ?>
                    <div class="p-2 border rounded" style="border-left: 5px solid <?php echo !empty($tag['color']) ? $tag['color'] : '#aaaaaa'; ?> !important;">
                        <span class="badge rounded-pill" style="background-color: <?php echo !empty($tag['color']) ? $tag['color'] : '#aaaaaa'; ?>">
                            <?php if (!empty($tag['icon'])): ?>
                                <i class="fas fa-<?php echo $tag['icon']; ?> me-1"></i>
                            <?php else: ?>
                                <i class="fas fa-tag me-1"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($tag['name']); ?>
                        </span>
                        
                        <?php if (!empty($tag['description'])): ?>
                            <div class="mt-2 small text-muted">
                                <?php echo htmlspecialchars($tag['description']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-light">
                <i class="fas fa-info-circle me-2"></i>
                This discussion doesn't have any tags assigned.
            </div>
        <?php endif; ?>
    </div>
</div> 