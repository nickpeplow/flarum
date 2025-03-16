<!-- Dashboard Cards Row -->
<div class="row">
    <!-- Total Keywords Card -->
    <div class="col-xl-3 col-md-6">
        <div class="card bg-primary text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Total Keywords</div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo $stats['total']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-key fa-2x text-white-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="keywords.php">View All Keywords</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    
    <!-- Pending Keywords Card -->
    <div class="col-xl-3 col-md-6">
        <div class="card bg-warning text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Pending Keywords</div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo $stats['pending']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-white-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="keywords.php?status=pending">View Pending</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    
    <!-- Approved Keywords Card -->
    <div class="col-xl-3 col-md-6">
        <div class="card bg-success text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Approved Keywords</div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo $stats['approved']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check fa-2x text-white-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="keywords.php?status=approved">View Approved</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
    
    <!-- Rejected Keywords Card -->
    <div class="col-xl-3 col-md-6">
        <div class="card bg-danger text-white mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-uppercase mb-1">Rejected Keywords</div>
                        <div class="h5 mb-0 font-weight-bold"><?php echo $stats['rejected']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-ban fa-2x text-white-300"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="keywords.php?status=rejected">View Rejected</a>
                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Info Alert -->
<div class="alert alert-info">
    <div class="d-flex align-items-center">
        <div class="me-3">
            <i class="fas fa-info-circle fa-2x"></i>
        </div>
        <div>
            <h4 class="alert-heading">Welcome to the Keywords Automation Dashboard</h4>
            <p class="mb-0">This dashboard allows you to manage keywords for your Flarum forum. You can approve, reject, or add new keywords to help organize your forum content.</p>
        </div>
    </div>
</div>

<!-- Content Row -->
<div class="row">
    <!-- Left Column: Recent Keywords Card -->
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <i class="fas fa-clock me-1"></i> Recent Keywords
            </div>
            <div class="card-body">
                <?php if (count($recentKeywords) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Keyword</th>
                                    <th>Status</th>
                                    <th>Added</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentKeywords as $keyword): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($keyword['keyword']); ?></td>
                                        <td><?php echo \Models\Keyword::getStatusBadge($keyword['status']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($keyword['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light text-center">
                        <i class="fas fa-info-circle me-2"></i>No keywords have been added yet.
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <a href="keywords.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-arrow-right me-1"></i> View All Keywords
                </a>
            </div>
        </div>
    </div>
    
    <!-- Right Column: System Information Card -->
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-server me-1"></i> System Information
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Component</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><i class="fab fa-php me-2"></i>PHP Version</td>
                                <td><span class="badge bg-primary rounded-pill"><?php echo phpversion(); ?></span></td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-database me-2"></i>MySQL Version</td>
                                <?php 
                                    $stmt = $db->query('SELECT VERSION() as version');
                                    $mysqlVersion = $stmt->fetch();
                                ?>
                                <td><span class="badge bg-primary rounded-pill"><?php echo $mysqlVersion['version']; ?></span></td>
                            </tr>
                            <tr>
                                <td><i class="fas fa-calendar me-2"></i>Server Time</td>
                                <td><span class="badge bg-primary rounded-pill"><?php echo date('Y-m-d H:i:s'); ?></span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <a href="schema_viewer.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-database me-1"></i> View Schema
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Feature Cards Row -->
<div class="row mt-4">
    <!-- Keywords Management -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-key me-1"></i> Keywords Management
            </div>
            <div class="card-body">
                <h5 class="card-title">Manage Forum Keywords</h5>
                <p class="card-text">View, approve, reject or add new keywords to your forum.</p>
                <div class="d-grid gap-2">
                    <a href="keywords.php" class="btn btn-primary">
                        <i class="fas fa-arrow-right me-1"></i> Manage Keywords
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics (Coming Soon) -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <i class="fas fa-chart-bar me-1"></i> Statistics
            </div>
            <div class="card-body">
                <h5 class="card-title">Keyword Analytics</h5>
                <p class="card-text">View statistics and analytics about keyword usage in your forum.</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-info text-white" disabled>
                        <i class="fas fa-chart-line me-1"></i> Coming Soon
                    </button>
                </div>
            </div>
            <div class="card-footer text-center">
                <i class="fas fa-tools me-1"></i> Feature in development
            </div>
        </div>
    </div>
    
    <!-- Settings (Coming Soon) -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-secondary text-white">
                <i class="fas fa-cog me-1"></i> Settings
            </div>
            <div class="card-body">
                <h5 class="card-title">Dashboard Configuration</h5>
                <p class="card-text">Configure automation settings, notifications, and synchronization options.</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-secondary" disabled>
                        <i class="fas fa-sliders-h me-1"></i> Coming Soon
                    </button>
                </div>
            </div>
            <div class="card-footer text-center">
                <i class="fas fa-tools me-1"></i> Feature in development
            </div>
        </div>
    </div>
</div> 