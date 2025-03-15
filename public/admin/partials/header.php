<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keywords Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            padding-top: 20px;
            background-color: #f8f9fa;
        }
        .navbar {
            margin-bottom: 20px;
        }
        .dashboard-header {
            background-color: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 0;
            margin-bottom: 30px;
        }
        .table-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            padding: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="dashboard-header">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="display-6">Flarum Keywords Dashboard</h1>
            </div>
            <div class="col-auto">
                <a href="../db_test.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-database"></i> Test DB Connection
                </a>
                <a href="/admin" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-left"></i> Flarum Admin
                </a>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'keywords.php' ? 'active' : ''; ?>" href="keywords.php">
                            <i class="fas fa-tags"></i> Keywords
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