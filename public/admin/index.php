<?php
/**
 * Admin Dashboard - Main Page
 * 
 * This is the main entry point for the keywords admin dashboard.
 */

// Database connection setup
require_once __DIR__ . '/../db_connection.php';

// Include header
include_once 'partials/header.php';
?>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-body">
                <h2 class="card-title">Welcome to Keywords Admin</h2>
                <p class="card-text">This dashboard allows you to manage keywords for your Flarum forum.</p>
                <p>Use the navigation menu to access different sections of the admin dashboard.</p>
                
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5><i class="fas fa-tags fa-2x mb-3 text-primary"></i></h5>
                                <h5 class="card-title">Keywords</h5>
                                <p class="card-text">Manage all your forum keywords</p>
                                <a href="keywords.php" class="btn btn-primary">View Keywords</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5><i class="fas fa-chart-line fa-2x mb-3 text-success"></i></h5>
                                <h5 class="card-title">Statistics</h5>
                                <p class="card-text">View keyword usage statistics</p>
                                <a href="#" class="btn btn-success disabled">Coming Soon</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h5><i class="fas fa-cog fa-2x mb-3 text-secondary"></i></h5>
                                <h5 class="card-title">Settings</h5>
                                <p class="card-text">Configure keyword settings</p>
                                <a href="#" class="btn btn-secondary disabled">Coming Soon</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Recent Keywords</h5>
            </div>
            <div class="card-body">
                <p>This section will display the most recently added keywords.</p>
                <p class="text-muted">No data to display yet.</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>System Information</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        PHP Version
                        <span class="badge bg-primary rounded-pill"><?php echo phpversion(); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        MySQL Version
                        <span class="badge bg-primary rounded-pill"><?php 
                            try {
                                $stmt = $pdo->query('SELECT VERSION() as version');
                                $result = $stmt->fetch();
                                echo $result['version'];
                            } catch (Exception $e) {
                                echo 'Unknown';
                            }
                        ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Database Name
                        <span class="badge bg-primary rounded-pill"><?php echo $dbConfig['database']; ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once 'partials/footer.php';
?> 