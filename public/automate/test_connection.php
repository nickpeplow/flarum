<?php
/**
 * Keywords Automation Dashboard - Test Connection
 * 
 * This page allows testing the database connection and displays connection details.
 */

// Database connection
require_once __DIR__ . '/../db_connection.php';

// Include component functions
require_once __DIR__ . '/templates/components.php';

// Page metadata
$pageTitle = 'Test Connection - Keywords Automation';
$pageHeader = 'Database Connection Test';

// Initialize alerts array
$alerts = [];

// Get database information
try {
    // Test if the connection is active
    $pdo->query("SELECT 1");
    $connectionStatus = [
        'status' => 'success',
        'message' => 'Database connection is working properly!'
    ];
    
    // Get database info
    $config = require __DIR__ . '/../../config.php';
    $dbConfig = $config['database'];
    
    // Get server variables
    $stmt = $pdo->query("SHOW VARIABLES LIKE '%version%'");
    $variables = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Get status
    $stmt = $pdo->query("SHOW STATUS LIKE 'Threads_%'");
    $threadStatus = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Get number of tables
    $stmt = $pdo->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = '{$dbConfig['database']}'");
    $tables = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get database size
    $stmt = $pdo->query("SELECT 
        SUM(data_length + index_length) as size 
        FROM information_schema.tables 
        WHERE table_schema = '{$dbConfig['database']}'");
    $size = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Format database size
    $databaseSize = formatSize($size['size'] ?? 0);
    
    // Get connection ID
    $stmt = $pdo->query("SELECT CONNECTION_ID() as connection_id");
    $connectionId = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $connectionStatus = [
        'status' => 'danger',
        'message' => 'Database connection failed: ' . $e->getMessage()
    ];
}

// Run a query test if requested
$queryTest = null;
if (isset($_POST['run_test_query'])) {
    try {
        // Get the query from the form
        $testQuery = isset($_POST['test_query']) ? $_POST['test_query'] : "SELECT 'Hello World' AS message";
        
        // Execute the query
        $startTime = microtime(true);
        $stmt = $pdo->query($testQuery);
        $executionTime = round((microtime(true) - $startTime) * 1000, 2); // in milliseconds
        
        // Get the results
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Build response
        $queryTest = [
            'status' => 'success',
            'query' => $testQuery,
            'execution_time' => $executionTime,
            'rows' => count($results),
            'results' => $results
        ];
        
        $alerts[] = [
            'type' => 'success',
            'message' => 'Query executed successfully in ' . $executionTime . 'ms, returning ' . count($results) . ' rows.'
        ];
    } catch (Exception $e) {
        $queryTest = [
            'status' => 'danger',
            'query' => $testQuery ?? '',
            'error' => $e->getMessage()
        ];
        
        $alerts[] = [
            'type' => 'danger',
            'message' => 'Query execution failed: ' . $e->getMessage()
        ];
    }
}

// Helper function to format file size
function formatSize($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Build page content
ob_start();
?>

<!-- Connection Status Card -->
<div class="card mb-4">
    <div class="card-header <?php echo $connectionStatus['status'] === 'success' ? 'bg-success' : 'bg-danger'; ?> text-white">
        <i class="fas fa-<?php echo $connectionStatus['status'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-1"></i>
        Connection Status
    </div>
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div class="me-3 fs-1 text-<?php echo $connectionStatus['status']; ?>">
                <i class="fas fa-<?php echo $connectionStatus['status'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            </div>
            <div>
                <h4><?php echo $connectionStatus['message']; ?></h4>
                <?php if ($connectionStatus['status'] === 'success'): ?>
                    <p class="mb-0">The application is successfully connected to the MySQL database.</p>
                <?php else: ?>
                    <p class="mb-0">Please check your database configuration and server status.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($connectionStatus['status'] === 'success'): ?>
    <!-- Connection Details Row -->
    <div class="row">
        <!-- Database Information Card -->
        <div class="col-lg-6">
            <?php
            $dbInfoHeader = '<i class="fas fa-database me-1"></i> Database Information';
            
            $dbInfoRows = [
                ['<i class="fas fa-server me-2"></i>Host', '<span class="badge bg-secondary">' . htmlspecialchars($dbConfig['host']) . '</span>'],
                ['<i class="fas fa-database me-2"></i>Database Name', '<span class="badge bg-primary">' . htmlspecialchars($dbConfig['database']) . '</span>'],
                ['<i class="fas fa-user me-2"></i>Username', '<span class="badge bg-info">' . htmlspecialchars($dbConfig['username']) . '</span>'],
                ['<i class="fas fa-table me-2"></i>Tables Count', '<span class="badge bg-success">' . (isset($tables) ? $tables['table_count'] : 'N/A') . '</span>'],
                ['<i class="fas fa-hdd me-2"></i>Database Size', '<span class="badge bg-warning text-dark">' . ($databaseSize ?? 'N/A') . '</span>'],
                ['<i class="fas fa-plug me-2"></i>Connection ID', '<span class="badge bg-dark">' . (isset($connectionId) ? $connectionId['connection_id'] : 'N/A') . '</span>']
            ];
            
            $dbInfoContent = dataTable(['Property', 'Value'], $dbInfoRows, 'table-hover');
            
            echo contentCard($dbInfoHeader, $dbInfoContent, 'bg-light');
            ?>
        </div>
        
        <!-- Server Information Card -->
        <div class="col-lg-6">
            <?php
            $serverInfoHeader = '<i class="fas fa-server me-1"></i> Server Information';
            
            if (isset($variables)) {
                $serverInfoRows = [
                    ['<i class="fas fa-tag me-2"></i>MySQL Version', '<span class="badge bg-primary">' . htmlspecialchars($variables['version'] ?? 'N/A') . '</span>'],
                    ['<i class="fas fa-info-circle me-2"></i>Version Comment', '<span class="badge bg-secondary">' . htmlspecialchars($variables['version_comment'] ?? 'N/A') . '</span>'],
                    ['<i class="fas fa-calendar me-2"></i>Compilation Date', '<span class="badge bg-secondary">' . htmlspecialchars($variables['version_compile_date'] ?? 'N/A') . '</span>'],
                    ['<i class="fas fa-server me-2"></i>Server OS', '<span class="badge bg-secondary">' . htmlspecialchars($variables['version_compile_os'] ?? 'N/A') . '</span>'],
                    ['<i class="fas fa-clock me-2"></i>Uptime', '<span class="badge bg-info">' . formatUptime($variables['uptime'] ?? 0) . '</span>'],
                    ['<i class="fas fa-project-diagram me-2"></i>Current Threads', '<span class="badge bg-warning text-dark">' . ($threadStatus['Threads_connected'] ?? 'N/A') . '</span>']
                ];
            } else {
                $serverInfoRows = [
                    ['<i class="fas fa-exclamation-triangle me-2"></i>Status', '<span class="badge bg-danger">Information Unavailable</span>']
                ];
            }
            
            $serverInfoContent = dataTable(['Property', 'Value'], $serverInfoRows, 'table-hover');
            
            echo contentCard($serverInfoHeader, $serverInfoContent, 'bg-light');
            ?>
        </div>
    </div>
    
    <!-- Test Query Card -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-terminal me-1"></i> Run Test Query
        </div>
        <div class="card-body">
            <form method="post" action="">
                <div class="mb-3">
                    <label for="test_query" class="form-label">SQL Query</label>
                    <textarea class="form-control font-monospace" id="test_query" name="test_query" rows="3" placeholder="Enter SQL query to execute..."><?php echo isset($queryTest) ? htmlspecialchars($queryTest['query']) : "SELECT 'Hello World' AS message"; ?></textarea>
                    <div class="form-text">Enter a simple SQL query to test. Be careful with write operations (INSERT, UPDATE, DELETE).</div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" name="run_test_query" class="btn btn-primary">
                        <i class="fas fa-play me-1"></i> Execute Query
                    </button>
                </div>
            </form>
            
            <?php if ($queryTest): ?>
                <hr>
                <h5 class="mt-4 mb-3">
                    <i class="fas fa-<?php echo $queryTest['status'] === 'success' ? 'check-circle text-success' : 'exclamation-circle text-danger'; ?> me-1"></i>
                    Query Results
                </h5>
                
                <?php if ($queryTest['status'] === 'success'): ?>
                    <div class="alert alert-success">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-check-circle me-2"></i>
                                Query executed successfully
                            </div>
                            <div>
                                <span class="badge bg-secondary me-2"><?php echo $queryTest['rows']; ?> row(s)</span>
                                <span class="badge bg-info"><?php echo $queryTest['execution_time']; ?> ms</span>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (count($queryTest['results']) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <?php foreach (array_keys($queryTest['results'][0]) as $column): ?>
                                            <th><?php echo htmlspecialchars($column); ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($queryTest['results'] as $row): ?>
                                        <tr>
                                            <?php foreach ($row as $value): ?>
                                                <td><?php echo htmlspecialchars($value !== null ? $value : 'NULL'); ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            The query executed successfully but returned no results.
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Error:</strong> <?php echo htmlspecialchars($queryTest['error']); ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- PDO Connection Information -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <i class="fas fa-info-circle me-1"></i> PDO Information
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3">PDO Drivers Available</h5>
                    <ul class="list-group mb-4">
                        <?php foreach (PDO::getAvailableDrivers() as $driver): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-plug me-2"></i><?php echo htmlspecialchars($driver); ?></span>
                                <?php if ($driver === 'mysql'): ?>
                                    <span class="badge bg-success">In Use</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Available</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5 class="mb-3">Connection Attributes</h5>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-exchange-alt me-2"></i>AUTOCOMMIT</span>
                            <span class="badge bg-<?php echo $pdo->getAttribute(PDO::ATTR_AUTOCOMMIT) ? 'success' : 'danger'; ?>">
                                <?php echo $pdo->getAttribute(PDO::ATTR_AUTOCOMMIT) ? 'Enabled' : 'Disabled'; ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-exclamation-triangle me-2"></i>ERROR MODE</span>
                            <span class="badge bg-info">
                                <?php 
                                $errorMode = $pdo->getAttribute(PDO::ATTR_ERRMODE);
                                switch($errorMode) {
                                    case PDO::ERRMODE_SILENT: echo 'SILENT'; break;
                                    case PDO::ERRMODE_WARNING: echo 'WARNING'; break;
                                    case PDO::ERRMODE_EXCEPTION: echo 'EXCEPTION'; break;
                                    default: echo 'UNKNOWN';
                                }
                                ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-quote-right me-2"></i>EMULATE PREPARES</span>
                            <span class="badge bg-<?php echo $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES) ? 'warning' : 'success'; ?> <?php echo $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES) ? 'text-dark' : ''; ?>">
                                <?php echo $pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES) ? 'Enabled' : 'Disabled'; ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-server me-2"></i>SERVER VERSION</span>
                            <span class="badge bg-secondary">
                                <?php echo htmlspecialchars($pdo->getAttribute(PDO::ATTR_SERVER_VERSION)); ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-info-circle me-2"></i>SERVER INFO</span>
                            <span class="badge bg-secondary">
                                <?php echo htmlspecialchars($pdo->getAttribute(PDO::ATTR_SERVER_INFO)); ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
// Helper function to format uptime
function formatUptime($seconds) {
    $days = floor($seconds / 86400);
    $hours = floor(($seconds % 86400) / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    
    $uptime = '';
    if ($days > 0) {
        $uptime .= $days . 'd ';
    }
    if ($hours > 0 || $days > 0) {
        $uptime .= $hours . 'h ';
    }
    $uptime .= $minutes . 'm';
    
    return $uptime;
}

$content = ob_get_clean();

// Include the layout template
include __DIR__ . '/templates/layout.php';
?> 