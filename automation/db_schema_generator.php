<?php
/**
 * Database Schema Documentation Generator
 * 
 * This script connects to the database and generates detailed schema documentation
 * for all tables in the database.
 */

// Load the configuration from the config file
$config = require __DIR__ . '/../config.php';
$dbConfig = $config['database'];

// Set up database connection
try {
    // Try connecting to 127.0.0.1 (which we know works from db_test.php)
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
    
    echo "✅ Connected to database: {$dbConfig['database']}\n\n";
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Get a list of all tables
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Start building the Markdown documentation
$markdown = "# Flarum Database Schema Documentation\n\n";
$markdown .= "This document describes the complete database schema for the Flarum forum application.\n\n";
$markdown .= "## Database Information\n\n";
$markdown .= "- **Database Name**: {$dbConfig['database']}\n";
$markdown .= "- **Database Engine**: MySQL\n";
$markdown .= "- **MySQL Version**: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
$markdown .= "- **Character Set**: {$dbConfig['charset']}\n";
$markdown .= "- **Collation**: {$dbConfig['collation']}\n\n";
$markdown .= "## Table Overview\n\n";
$markdown .= "The database contains " . count($tables) . " tables:\n\n";

// Create categorized table lists
$userManagement = ['users', 'access_tokens', 'api_keys', 'email_tokens', 'password_tokens', 
                  'login_providers', 'registration_tokens', 'unsubscribe_tokens'];
$contentManagement = ['discussions', 'posts', 'flags', 'post_likes', 'post_mentions_user', 
                     'post_mentions_post', 'post_mentions_group', 'post_mentions_tag'];
$categorization = ['tags', 'discussion_tag', 'tag_user'];
$relationships = ['discussion_user', 'post_user', 'group_user'];
$permissions = ['groups', 'group_permission'];
$system = ['settings', 'migrations', 'notifications'];

// Function to output table list by category
function listTablesByCategory($tables, $category, &$markdown) {
    $markdown .= "### $category\n";
    foreach ($tables as $table) {
        $markdown .= "- `$table`\n";
    }
    $markdown .= "\n";
}

$markdown .= listTablesByCategory($userManagement, "User Management", $markdown);
$markdown .= listTablesByCategory($contentManagement, "Content Management", $markdown);
$markdown .= listTablesByCategory($categorization, "Categorization", $markdown);
$markdown .= listTablesByCategory($relationships, "Relationships", $markdown);
$markdown .= listTablesByCategory($permissions, "Permissions", $markdown);
$markdown .= listTablesByCategory($system, "System", $markdown);

// Get detailed schema for each table
$markdown .= "## Detailed Table Schemas\n\n";

foreach ($tables as $table) {
    // Get table structure
    $stmt = $pdo->prepare("DESCRIBE `$table`");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    // Get any foreign keys
    $stmt = $pdo->prepare("
        SELECT 
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME 
        FROM 
            INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE 
            TABLE_SCHEMA = :dbname AND
            TABLE_NAME = :table AND 
            REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $stmt->execute(['dbname' => $dbConfig['database'], 'table' => $table]);
    $foreignKeys = $stmt->fetchAll();
    
    // Get indexes
    $stmt = $pdo->prepare("SHOW INDEXES FROM `$table`");
    $stmt->execute();
    $indexes = $stmt->fetchAll();
    
    // Build table documentation
    $markdown .= "### `$table`\n\n";
    
    // Table columns
    $markdown .= "#### Columns\n\n";
    $markdown .= "| Column | Type | Nullable | Key | Default | Extra |\n";
    $markdown .= "|--------|------|----------|-----|---------|-------|\n";
    
    foreach ($columns as $column) {
        $default = $column['Default'] === null ? 'NULL' : $column['Default'];
        $markdown .= "| {$column['Field']} | {$column['Type']} | {$column['Null']} | {$column['Key']} | {$default} | {$column['Extra']} |\n";
    }
    $markdown .= "\n";
    
    // Foreign keys
    if (count($foreignKeys) > 0) {
        $markdown .= "#### Foreign Keys\n\n";
        $markdown .= "| Column | References |\n";
        $markdown .= "|--------|------------|\n";
        
        foreach ($foreignKeys as $fk) {
            $markdown .= "| {$fk['COLUMN_NAME']} | `{$fk['REFERENCED_TABLE_NAME']}`.`{$fk['REFERENCED_COLUMN_NAME']}` |\n";
        }
        $markdown .= "\n";
    }
    
    // Indexes (grouped by key name)
    if (count($indexes) > 0) {
        $indexGroups = [];
        foreach ($indexes as $index) {
            $keyName = $index['Key_name'];
            if (!isset($indexGroups[$keyName])) {
                $indexGroups[$keyName] = [
                    'type' => $index['Index_type'],
                    'unique' => $index['Non_unique'] == 0 ? 'Yes' : 'No',
                    'columns' => []
                ];
            }
            $indexGroups[$keyName]['columns'][] = $index['Column_name'];
        }
        
        $markdown .= "#### Indexes\n\n";
        $markdown .= "| Name | Type | Unique | Columns |\n";
        $markdown .= "|------|------|--------|--------|\n";
        
        foreach ($indexGroups as $name => $index) {
            $columns = implode(', ', $index['columns']);
            $markdown .= "| $name | {$index['type']} | {$index['unique']} | $columns |\n";
        }
        $markdown .= "\n";
    }
}

$markdown .= "## Relationships\n\n";
$markdown .= "The database follows a relational structure where:\n";
$markdown .= "- Users create discussions and posts\n";
$markdown .= "- Discussions contain posts\n";
$markdown .= "- Tags categorize discussions\n";
$markdown .= "- Groups manage permissions\n";
$markdown .= "- Various token tables handle authentication and security functions\n\n";

$markdown .= "## Notes\n\n";
$markdown .= "This schema follows typical forum database design patterns with:\n";
$markdown .= "- Core content tables (discussions, posts)\n";
$markdown .= "- User management (users, authentication)\n";
$markdown .= "- Categorization system (tags)\n";
$markdown .= "- Permission system (groups, permissions)\n";
$markdown .= "- Relationship tracking (mentions, likes)\n";

// Save to file
file_put_contents(__DIR__ . '/db_schema_full.md', $markdown);

echo "Schema documentation generated at: automation/db_schema_full.md\n"; 