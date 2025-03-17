<?php
/**
 * Users Index View
 * 
 * Displays the user management interface
 */
?>

<!-- Page Toolbar -->
<div class="card mb-4">
    <div class="card-body p-3">
        <div class="row align-items-center">
            <!-- Search Form -->
            <div class="col-md-6 mb-2 mb-md-0">
                <form method="get" action="<?php echo \Config\App::get('base_url'); ?>/users" class="d-flex">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Filter Buttons and Create Button -->
            <div class="col-md-6">
                <div class="d-flex justify-content-md-end">
                    <!-- Group Filter Dropdown -->
                    <div class="dropdown me-2">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="groupFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo $group ? 'Group: ' . htmlspecialchars($groups[$group - 1]['name_singular'] ?? $group) : 'All Groups'; ?>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="groupFilterDropdown">
                            <li><a class="dropdown-item <?php echo $group === null ? 'active' : ''; ?>" href="<?php echo \Config\App::get('base_url'); ?>/users<?php echo !empty($search) ? '?search=' . urlencode($search) : ''; ?>">All Groups</a></li>
                            <?php foreach ($groups as $g): ?>
                                <li><a class="dropdown-item <?php echo $group == $g['id'] ? 'active' : ''; ?>" href="<?php echo \Config\App::get('base_url'); ?>/users?group=<?php echo $g['id']; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?php echo htmlspecialchars($g['name_singular']); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-users me-1"></i>
        Users List
        <span class="badge bg-primary rounded-pill ms-2"><?php echo $total; ?> total</span>
    </div>
    <div class="card-body">
        <?php if (count($users) > 0): ?>
            <div class="table-responsive">
                <table id="usersTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Joined</th>
                            <th>Role</th>
                            <th>Posts</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td>
                                    <a href="<?php echo \Config\App::get('base_url'); ?>/users/view/<?php echo $user['id']; ?>" class="fw-bold text-decoration-none">
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if (!empty($user['joined_at'])): ?>
                                        <?php echo date('M j, Y', strtotime($user['joined_at'])); ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($user['primary_group_name'])): ?>
                                        <span class="badge" style="background-color: <?php echo !empty($user['primary_group_color']) ? $user['primary_group_color'] : '#6c757d'; ?>">
                                            <?php echo htmlspecialchars($user['primary_group_name']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Member</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary rounded-pill"><?php echo $user['post_count']; ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($user['suspended_until']) && strtotime($user['suspended_until']) > time()): ?>
                                        <span class="badge bg-danger">Suspended</span>
                                    <?php elseif (!$user['is_email_confirmed']): ?>
                                        <span class="badge bg-warning text-dark">Unconfirmed</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="<?php echo \Config\App::get('base_url'); ?>/users/view/<?php echo $user['id']; ?>" class="btn btn-sm btn-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#suspendUserModal<?php echo $user['id']; ?>" title="Suspend">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Suspend User Modal -->
                                    <div class="modal fade" id="suspendUserModal<?php echo $user['id']; ?>" tabindex="-1" aria-labelledby="suspendUserModalLabel<?php echo $user['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="suspendUserModalLabel<?php echo $user['id']; ?>">
                                                        <?php if (!empty($user['suspended_until']) && strtotime($user['suspended_until']) > time()): ?>
                                                            Unsuspend User
                                                        <?php else: ?>
                                                            Suspend User
                                                        <?php endif; ?>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <?php if (!empty($user['suspended_until']) && strtotime($user['suspended_until']) > time()): ?>
                                                        <p>Are you sure you want to unsuspend <strong><?php echo htmlspecialchars($user['username']); ?></strong>?</p>
                                                    <?php else: ?>
                                                        <p>Are you sure you want to suspend <strong><?php echo htmlspecialchars($user['username']); ?></strong>?</p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <form method="post" action="<?php echo \Config\App::get('base_url'); ?>/users/process-action">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        
                                                        <?php if (!empty($user['suspended_until']) && strtotime($user['suspended_until']) > time()): ?>
                                                            <input type="hidden" name="action" value="unsuspend">
                                                            <button type="submit" class="btn btn-success">Unsuspend</button>
                                                        <?php else: ?>
                                                            <input type="hidden" name="action" value="suspend">
                                                            <button type="submit" class="btn btn-danger">Suspend</button>
                                                        <?php endif; ?>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
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
                    <ul class="pagination justify-content-center mt-4">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo \Config\App::get('base_url'); ?>/users?page=<?php echo ($page - 1); ?><?php echo $group ? '&group=' . $group : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
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
                                <a class="page-link" href="<?php echo \Config\App::get('base_url'); ?>/users?page=<?php echo $i; ?><?php echo $group ? '&group=' . $group : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo \Config\App::get('base_url'); ?>/users?page=<?php echo ($page + 1); ?><?php echo $group ? '&group=' . $group : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
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
                No users found. <?php echo !empty($search) || $group ? 'Try adjusting your search or filter criteria.' : ''; ?>
            </div>
        <?php endif; ?>
    </div>
</div> 