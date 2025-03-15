<?php
/**
 * Keywords Automation Dashboard - Schema Viewer
 * 
 * This page displays the database schema and information about tables.
 */

// Database connection
require_once __DIR__ . '/../db_connection.php';

// Include component functions
require_once __DIR__ . '/templates/components.php';

// Page metadata
$pageTitle = 'Database Schema - Keywords Automation';
$pageHeader = 'Database Schema Viewer';

// Initialize alerts array
$alerts = [];

// Get database info
try {
    // Get database name
    $config = require __DIR__ . '/../../config.php';
    $dbConfig = $config['database'];
    $dbName = $dbConfig['database'];
    
    // Get tables list
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get database size
    $stmt = $pdo->query("SELECT 
        SUM(data_length + index_length) AS size,
        SUM(data_length) AS data_size,
        SUM(index_length) AS index_size
        FROM information_schema.TABLES
        WHERE table_schema = '{$dbName}'");
    $dbSize = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Format sizes
    $totalSize = formatBytes($dbSize['size'] ?? 0);
    $dataSize = formatBytes($dbSize['data_size'] ?? 0);
    $indexSize = formatBytes($dbSize['index_size'] ?? 0);
    
    // Get selected table or default to keywords
    $selectedTable = isset($_GET['table']) ? sanitizeInput($_GET['table']) : 'keywords';
    
    // Validate selected table exists
    if (!in_array($selectedTable, $tables)) {
        $selectedTable = $tables[0] ?? '';
    }
    
    // Get table structure
    $tableStructure = [];
    if (!empty($selectedTable)) {
        $stmt = $pdo->query("DESCRIBE `{$selectedTable}`");
        $tableStructure = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get table indexes
        $stmt = $pdo->query("SHOW INDEX FROM `{$selectedTable}`");
        $tableIndexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get table status (size, rows, etc)
        $stmt = $pdo->query("SHOW TABLE STATUS WHERE Name = '{$selectedTable}'");
        $tableStatus = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $alerts[] = ['type' => 'danger', 'message' => 'Error retrieving database schema: ' . $e->getMessage()];
}

// Helper function to format bytes
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Build page content
ob_start();
?>

<!-- Database Info Card -->
<div class="row mb-4">
    <div class="col-lg-12">
        <?php
        $dbInfoHeader = '<i class="fas fa-database me-1"></i> Database Information';
        
        $dbInfoRows = [
            ['<i class="fas fa-server me-2"></i>Database Name', '<span class="badge bg-primary">' . htmlspecialchars($dbName) . '</span>'],
            ['<i class="fas fa-table me-2"></i>Tables Count', '<span class="badge bg-info">' . count($tables) . '</span>'],
            ['<i class="fas fa-hdd me-2"></i>Total Size', '<span class="badge bg-success">' . $totalSize . '</span>'],
            ['<i class="fas fa-file me-2"></i>Data Size', '<span class="badge bg-secondary">' . $dataSize . '</span>'],
            ['<i class="fas fa-list me-2"></i>Index Size', '<span class="badge bg-secondary">' . $indexSize . '</span>']
        ];
        
        $dbInfoContent = dataTable(['Property', 'Value'], $dbInfoRows, 'table-hover');
        
        echo contentCard($dbInfoHeader, $dbInfoContent, 'bg-dark text-white');
        ?>
    </div>
</div>

<!-- Table Selection and Structure -->
<div class="row">
    <!-- Tables List -->
    <div class="col-lg-3">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-list me-1"></i> Tables
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($tables as $table): ?>
                    <a href="?table=<?php echo urlencode($table); ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo ($table === $selectedTable) ? 'active' : ''; ?>">
                        <span>
                            <i class="fas fa-table me-2"></i>
                            <?php echo htmlspecialchars($table); ?>
                        </span>
                        <?php if ($table === 'keywords'): ?>
                            <span class="badge bg-primary rounded-pill">Primary</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Table Structure -->
    <div class="col-lg-9">
        <?php if (!empty($selectedTable) && !empty($tableStructure)): ?>
            <!-- Table Status Card -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-info-circle me-1"></i> Table Status: <?php echo htmlspecialchars($selectedTable); ?>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3 text-center h-100">
                                <h3 class="fs-5 text-secondary mb-1">Rows</h3>
                                <p class="fs-4 fw-bold mb-0"><?php echo number_format($tableStatus['Rows']); ?></p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3 text-center h-100">
                                <h3 class="fs-5 text-secondary mb-1">Size</h3>
                                <p class="fs-4 fw-bold mb-0"><?php echo formatBytes($tableStatus['Data_length'] + $tableStatus['Index_length']); ?></p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3 text-center h-100">
                                <h3 class="fs-5 text-secondary mb-1">Engine</h3>
                                <p class="fs-4 fw-bold mb-0"><?php echo $tableStatus['Engine']; ?></p>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="border rounded p-3 text-center h-100">
                                <h3 class="fs-5 text-secondary mb-1">Collation</h3>
                                <p class="fs-4 fw-bold mb-0"><?php echo $tableStatus['Collation']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Table Columns Card -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-columns me-1"></i> Table Structure: <?php echo htmlspecialchars($selectedTable); ?>
                </div>
                <div class="card-body">
                    <?php
                    $tableRows = [];
                    foreach ($tableStructure as $column) {
                        // Format key
                        $keyBadge = '';
                        if ($column['Key'] === 'PRI') {
                            $keyBadge = '<span class="badge bg-danger">Primary</span>';
                        } elseif ($column['Key'] === 'UNI') {
                            $keyBadge = '<span class="badge bg-warning text-dark">Unique</span>';
                        } elseif ($column['Key'] === 'MUL') {
                            $keyBadge = '<span class="badge bg-info">Index</span>';
                        }
                        
                        // Format extra
                        $extraBadge = '';
                        if ($column['Extra'] === 'auto_increment') {
                            $extraBadge = '<span class="badge bg-secondary">Auto Increment</span>';
                        } elseif (!empty($column['Extra'])) {
                            $extraBadge = '<span class="badge bg-secondary">' . htmlspecialchars($column['Extra']) . '</span>';
                        }
                        
                        $tableRows[] = [
                            htmlspecialchars($column['Field']),
                            htmlspecialchars($column['Type']),
                            ($column['Null'] === 'YES' ? '<span class="text-success">Yes</span>' : '<span class="text-danger">No</span>'),
                            htmlspecialchars($column['Default'] ?? 'NULL'),
                            $keyBadge,
                            $extraBadge
                        ];
                    }
                    
                    echo dataTable(['Column', 'Type', 'Nullable', 'Default', 'Key', 'Extra'], $tableRows, 'table-hover');
                    ?>
                </div>
            </div>
            
            <!-- Table Indexes Card -->
            <?php if (!empty($tableIndexes)): ?>
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <i class="fas fa-key me-1"></i> Table Indexes: <?php echo htmlspecialchars($selectedTable); ?>
                    </div>
                    <div class="card-body">
                        <?php
                        $indexRows = [];
                        $currentKey = '';
                        $indexColumns = [];
                        
                        foreach ($tableIndexes as $index) {
                            if ($currentKey !== $index['Key_name']) {
                                if (!empty($currentKey)) {
                                    $indexRows[] = [
                                        htmlspecialchars($currentKey),
                                        ($currentKey === 'PRIMARY' ? '<span class="badge bg-danger">Primary</span>' : 
                                            ($index['Non_unique'] == 0 ? '<span class="badge bg-warning text-dark">Unique</span>' : 
                                            '<span class="badge bg-info">Index</span>')),
                                        implode(', ', $indexColumns)
                                    ];
                                }
                                $currentKey = $index['Key_name'];
                                $indexColumns = [];
                            }
                            
                            $indexColumns[] = htmlspecialchars($index['Column_name']) . 
                                ($index['Sub_part'] ? ' (' . $index['Sub_part'] . ')' : '');
                        }
                        
                        // Add the last index
                        if (!empty($currentKey)) {
                            $isUnique = 0;
                            foreach ($tableIndexes as $index) {
                                if ($index['Key_name'] === $currentKey) {
                                    $isUnique = $index['Non_unique'];
                                    break;
                                }
                            }
                            
                            $indexRows[] = [
                                htmlspecialchars($currentKey),
                                ($currentKey === 'PRIMARY' ? '<span class="badge bg-danger">Primary</span>' : 
                                    ($isUnique == 0 ? '<span class="badge bg-warning text-dark">Unique</span>' : 
                                    '<span class="badge bg-info">Index</span>')),
                                implode(', ', $indexColumns)
                            ];
                        }
                        
                        echo dataTable(['Index Name', 'Type', 'Columns'], $indexRows, 'table-hover');
                        ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Create Table SQL -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <i class="fas fa-code me-1"></i> Create Table SQL
                </div>
                <div class="card-body">
                    <?php
                    try {
                        $stmt = $pdo->query("SHOW CREATE TABLE `{$selectedTable}`");
                        $createTableSql = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if (isset($createTableSql['Create Table'])) {
                            echo '<pre class="bg-light p-3 rounded"><code>' . htmlspecialchars($createTableSql['Create Table']) . '</code></pre>';
                        }
                    } catch (Exception $e) {
                        echo '<div class="alert alert-danger">Error retrieving SQL: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                    ?>
                </div>
                <!-- Export Structure Button -->
                <div class="card-footer">
                    <button class="btn btn-sm btn-primary" type="button" onclick="copyToClipboard()">
                        <i class="fas fa-copy me-1"></i> Copy SQL to Clipboard
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> Please select a table to view its structure.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript for Copy to Clipboard functionality -->
<script>
function copyToClipboard() {
    const sqlElement = document.querySelector('pre code');
    if (sqlElement) {
        const textArea = document.createElement('textarea');
        textArea.value = sqlElement.textContent;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        // Show feedback
        alert('SQL copied to clipboard!');
    }
}
</script>

<?php
$content = ob_get_clean();

// Include the layout template
include __DIR__ . '/templates/layout.php';
?> 