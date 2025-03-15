<?php
/**
 * Database Setup - Keywords Table
 * 
 * This script creates a new table called 'keywords' for storing and tracking keywords
 * associated with posts. This doesn't modify any existing tables in the database.
 */

// Load the configuration from the config file
$config = require __DIR__ . '/../config.php';
$dbConfig = $config['database'];

echo "Starting keywords table setup...\n";

// Connect to the database (using 127.0.0.1 which works from our previous tests)
try {
    $dsn = "{$dbConfig['driver']}:host=127.0.0.1;port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO(
        $dsn, 
        $dbConfig['username'], 
        $dbConfig['password'], 
        $options
    );
    
    echo "✅ Connected to database: {$dbConfig['database']}\n";
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Check if keywords table already exists
$tableExists = false;
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'keywords'");
    $tableExists = $stmt->rowCount() > 0;
} catch (PDOException $e) {
    echo "Error checking for existing table: " . $e->getMessage() . "\n";
    exit(1);
}

// If table already exists, ask before proceeding
if ($tableExists) {
    echo "⚠️ The 'keywords' table already exists in the database.\n";
    echo "Do you want to drop and recreate it? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim(strtolower($line)) != 'y') {
        echo "Operation cancelled. No changes were made.\n";
        exit(0);
    }
    
    // Drop the existing table
    try {
        $pdo->exec("DROP TABLE `keywords`");
        echo "Dropped existing 'keywords' table.\n";
    } catch (PDOException $e) {
        echo "❌ Failed to drop table: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Create the keywords table
try {
    $sql = "CREATE TABLE `keywords` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `keyword` VARCHAR(191) NOT NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
        `post_id` INT UNSIGNED NULL,
        `tag_id` INT UNSIGNED NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE INDEX `idx_keywords_unique` (`keyword`),
        INDEX `idx_keywords_post_id` (`post_id`),
        INDEX `idx_keywords_tag_id` (`tag_id`),
        INDEX `idx_keywords_status` (`status`),
        CONSTRAINT `fk_keywords_post_id` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE SET NULL,
        CONSTRAINT `fk_keywords_tag_id` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET={$dbConfig['charset']} COLLATE={$dbConfig['collation']}";
    
    $pdo->exec($sql);
    echo "✅ Successfully created 'keywords' table with unique keyword constraint.\n";
    echo "   Default status is set to 'pending'.\n";
    echo "   Added nullable tag_id field with foreign key reference to tags table.\n";
    echo "   Made post_id field nullable with ON DELETE SET NULL.\n";
    
    // Display table structure
    $stmt = $pdo->query("DESCRIBE `keywords`");
    $columns = $stmt->fetchAll();
    
    echo "\nTable structure:\n";
    echo "----------------\n";
    foreach ($columns as $column) {
        echo "{$column['Field']} - {$column['Type']} - {$column['Null']} - {$column['Key']} - {$column['Default']} - {$column['Extra']}\n";
    }
    
    // Show indexes
    $stmt = $pdo->query("SHOW INDEXES FROM `keywords`");
    $indexes = $stmt->fetchAll();
    
    echo "\nIndexes:\n";
    echo "--------\n";
    foreach ($indexes as $index) {
        echo "{$index['Key_name']} - Column: {$index['Column_name']} - {$index['Index_type']} - Unique: " . ($index['Non_unique'] == 0 ? 'Yes' : 'No') . "\n";
    }
    
    // Show foreign keys
    $stmt = $pdo->prepare("
        SELECT 
            COLUMN_NAME, 
            CONSTRAINT_NAME, 
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM 
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE 
            TABLE_SCHEMA = :dbname AND
            TABLE_NAME = 'keywords' AND
            REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $stmt->execute(['dbname' => $dbConfig['database']]);
    $foreignKeys = $stmt->fetchAll();
    
    echo "\nForeign Keys:\n";
    echo "-------------\n";
    foreach ($foreignKeys as $fk) {
        echo "{$fk['CONSTRAINT_NAME']} - {$fk['COLUMN_NAME']} references {$fk['REFERENCED_TABLE_NAME']}({$fk['REFERENCED_COLUMN_NAME']})\n";
    }
    
    echo "\n✨ The 'keywords' table is now ready to use.\n";
    echo "This table doesn't modify any existing tables in the database.\n";
    
} catch (PDOException $e) {
    echo "❌ Failed to create table: " . $e->getMessage() . "\n";
    exit(1);
} 