<?php
/**
 * Keywords Automation Dashboard - Main Page
 * 
 * Entry point for the keywords automation dashboard.
 */

// Database connection
require_once __DIR__ . '/../db_connection.php';

// Include component functions
require_once __DIR__ . '/templates/components.php';

// Models
require_once 'models/Keyword.php';

// Initialize models
$keywordModel = new Keyword($pdo);

// Get keyword statistics
$stats = $keywordModel->getStatistics();

// Get recent keywords
$recentKeywords = $keywordModel->getRecentKeywords(5);

// Page metadata
$pageTitle = 'Dashboard - Keywords Automation';
$pageHeader = 'Dashboard';

// Initialize alerts array
$alerts = [];

// Build page content
ob_start();
?>

<!-- Dashboard Cards Row -->
<div class="row">
    <!-- Total Keywords Card -->
    <div class="col-xl-3 col-md-6">
        <?php echo dashboardCard('Total Keywords', $stats['total'], 'key', 'primary', 'keywords.php', 'View All Keywords'); ?>
    </div>
    
    <!-- Pending Keywords Card -->
    <div class="col-xl-3 col-md-6">
        <?php echo dashboardCard('Pending Keywords', $stats['pending'], 'clock', 'warning', 'keywords.php?status=pending', 'View Pending'); ?>
    </div>
    
    <!-- Approved Keywords Card -->
    <div class="col-xl-3 col-md-6">
        <?php echo dashboardCard('Approved Keywords', $stats['approved'], 'check', 'success', 'keywords.php?status=approved', 'View Approved'); ?>
    </div>
    
    <!-- Rejected Keywords Card -->
    <div class="col-xl-3 col-md-6">
        <?php echo dashboardCard('Rejected Keywords', $stats['rejected'], 'ban', 'danger', 'keywords.php?status=rejected', 'View Rejected'); ?>
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
        <?php
        $recentKeywordsHeader = '<i class="fas fa-clock me-1"></i> Recent Keywords';
        
        $recentKeywordsContent = '';
        if (count($recentKeywords) > 0) {
            $tableRows = [];
            foreach ($recentKeywords as $keyword) {
                $tableRows[] = [
                    htmlspecialchars($keyword['keyword']),
                    Keyword::getStatusBadge($keyword['status']),
                    date('M j, Y', strtotime($keyword['created_at']))
                ];
            }
            
            $recentKeywordsContent = dataTable(['Keyword', 'Status', 'Added'], $tableRows, 'table-hover');
        } else {
            $recentKeywordsContent = '<div class="alert alert-light text-center">
                <i class="fas fa-info-circle me-2"></i>No keywords have been added yet.
            </div>';
        }
        
        $recentKeywordsFooter = '<a href="keywords.php" class="btn btn-sm btn-primary">
            <i class="fas fa-arrow-right me-1"></i> View All Keywords
        </a>';
        
        echo contentCard($recentKeywordsHeader, $recentKeywordsContent, 'bg-success text-white', $recentKeywordsFooter);
        ?>
    </div>
    
    <!-- Right Column: System Information Card -->
    <div class="col-lg-6">
        <?php
        $sysInfoHeader = '<i class="fas fa-server me-1"></i> System Information';
        
        // Get system information
        $stmt = $pdo->query('SELECT VERSION() as version');
        $mysqlVersion = $stmt->fetch();
        
        $config = require __DIR__ . '/../../config.php';
        $dbConfig = $config['database'];
        
        $sysInfoRows = [
            ['<i class="fab fa-php me-2"></i>PHP Version', '<span class="badge bg-primary rounded-pill">' . phpversion() . '</span>'],
            ['<i class="fas fa-database me-2"></i>MySQL Version', '<span class="badge bg-primary rounded-pill">' . $mysqlVersion['version'] . '</span>'],
            ['<i class="fas fa-table me-2"></i>Database', '<span class="badge bg-primary rounded-pill">' . $dbConfig['database'] . '</span>'],
            ['<i class="fas fa-calendar me-2"></i>Server Time', '<span class="badge bg-primary rounded-pill">' . date('Y-m-d H:i:s') . '</span>']
        ];
        
        $sysInfoContent = dataTable(['Component', 'Value'], $sysInfoRows, 'table-hover');
        
        $sysInfoFooter = '<a href="schema_viewer.php" class="btn btn-sm btn-primary">
            <i class="fas fa-database me-1"></i> View Schema
        </a>';
        
        echo contentCard($sysInfoHeader, $sysInfoContent, 'bg-dark text-white', $sysInfoFooter);
        ?>
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

<?php
$content = ob_get_clean();

// Include the layout template
include __DIR__ . '/templates/layout.php';
?> 