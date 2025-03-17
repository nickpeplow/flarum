<?php
/**
 * User Detail View
 * 
 * Displays detailed information about a single user
 */
?>

<!-- Back Button -->
<div class="mb-3">
    <a href="<?php echo \Config\App::get('base_url'); ?>/users" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Users
    </a>
</div>

<!-- User Profile Card -->
<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-user me-1"></i>
                User Profile
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="avatar-placeholder rounded-circle bg-primary text-white d-inline-flex justify-content-center align-items-center mb-3" style="width: 100px; height: 100px; font-size: 40px;">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                    <h5 class="card-title"><?php echo htmlspecialchars($user['username']); ?></h5>
                    <p class="card-text text-muted">User ID: <?php echo $user['id']; ?></p>
                </div>
                
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Email:</span>
                        <span><?php echo htmlspecialchars($user['email']); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Email Status:</span>
                        <span>
                            <?php if ($user['is_email_confirmed']): ?>
                                <span class="badge bg-success">Confirmed</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Unconfirmed</span>
                            <?php endif; ?>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Joined:</span>
                        <span><?php echo date('M j, Y', strtotime($user['joined_at'])); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Role(s):</span>
                        <span>
                            <?php if (!empty($groups)): ?>
                                <div class="d-flex flex-wrap gap-1 justify-content-end">
                                    <?php foreach ($groups as $group): ?>
                                        <span class="badge" style="background-color: <?php echo !empty($group['color']) ? $group['color'] : '#6c757d'; ?>">
                                            <?php echo htmlspecialchars($group['name_singular']); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="badge bg-secondary">Member</span>
                            <?php endif; ?>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Account Status:</span>
                        <span>
                            <?php if (!empty($user['suspended_until']) && strtotime($user['suspended_until']) > time()): ?>
                                <span class="badge bg-danger">Suspended until <?php echo date('M j, Y', strtotime($user['suspended_until'])); ?></span>
                            <?php else: ?>
                                <span class="badge bg-success">Active</span>
                            <?php endif; ?>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Post Count:</span>
                        <span class="badge bg-secondary rounded-pill"><?php echo $user['post_count']; ?></span>
                    </li>
                </ul>
            </div>
            
            <div class="card-footer">
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#editUserModal">
                        <i class="fas fa-edit me-1"></i> Edit User
                    </button>
                    
                    <?php if (!empty($user['suspended_until']) && strtotime($user['suspended_until']) > time()): ?>
                        <form method="post" action="<?php echo \Config\App::get('base_url'); ?>/users/process-action">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <input type="hidden" name="action" value="unsuspend">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-user-check me-1"></i> Unsuspend User
                            </button>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-warning" type="button" data-bs-toggle="modal" data-bs-target="#suspendUserModal">
                            <i class="fas fa-ban me-1"></i> Suspend User
                        </button>
                    <?php endif; ?>
                    
                    <button class="btn btn-danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteUserModal">
                        <i class="fas fa-trash-alt me-1"></i> Delete User
                    </button>
                </div>
            </div>
        </div>
    </div>
</div> 