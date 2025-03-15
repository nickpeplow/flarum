<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keywords Automation Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 20px;
            background-color: #f8f9fa;
        }
        .navbar {
            margin-bottom: 20px;
        }
        .dashboard-header {
            margin-bottom: 30px;
        }
        .table-container {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        .card {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-header d-flex justify-content-between align-items-center">
            <h1><i class="fas fa-robot me-2"></i> Keywords Automation</h1>
            <div>
                <a href="../db_test.php" class="btn btn-outline-primary me-2" title="Test database connection">
                    <i class="fas fa-database me-1"></i> Test DB
                </a>
                <a href="/admin" class="btn btn-secondary" title="Go to Flarum admin" target="_blank">
                    <i class="fas fa-cog me-1"></i> Flarum Admin
                </a>
            </div>
        </div>
        
        <nav class="navbar navbar-expand-lg navbar-light bg-light rounded">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['SCRIPT_NAME']) == 'index.php' ? 'active fw-bold' : ''; ?>" href="index.php">
                                <i class="fas fa-home me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['SCRIPT_NAME']) == 'keywords.php' ? 'active fw-bold' : ''; ?>" href="keywords.php">
                                <i class="fas fa-key me-1"></i> Keywords
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['SCRIPT_NAME']) == 'schema_viewer.php' ? 'active fw-bold' : ''; ?>" href="schema_viewer.php">
                                <i class="fas fa-database me-1"></i> Schema
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="content-container">
            <!-- Main content starts here -->
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 