<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Keywords Automation Dashboard for Flarum">
    <meta name="author" content="">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Keywords Automation Dashboard'; ?></title>
    
    <!-- Bootstrap core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <!-- SB Admin styles -->
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin@7.0.7/dist/css/styles.css" rel="stylesheet">
    
    <!-- Custom styles for this dashboard -->
    <style>
        .dashboard-card {
            border-radius: 5px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        .badge-counter {
            position: absolute;
            transform: scale(0.7);
            transform-origin: top right;
            right: 0.25rem;
            margin-top: -0.25rem;
        }
        .smaller {
            font-size: 0.9rem;
        }
        .dropdown-menu {
            font-size: 0.85rem;
        }
        .icon-card {
            color: #fff;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        .icon-card i {
            font-size: 30px;
            margin-bottom: 10px;
        }
        .sidebar .nav-link {
            font-weight: normal;
            padding: 0.75rem 1rem;
            color: rgba(255, 255, 255, 0.65);
        }
        .sidebar .nav-link.active {
            color: white;
            font-weight: 500;
        }
        .sidebar .nav-link:hover {
            color: white;
        }
    </style>
    
    <?php if (isset($extraStyles)): ?>
        <?php echo $extraStyles; ?>
    <?php endif; ?>
</head>

<body class="sb-nav-fixed">
    <!-- Top Navigation -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <a class="navbar-brand ps-3" href="index.php">Keywords Automation</a>
        <!-- Sidebar Toggle-->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
        <!-- Navbar Search-->
        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
            <div class="input-group">
                <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
                <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
            </div>
        </form>
        <!-- Navbar-->
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="../db_test.php">Test DB Connection</a></li>
                    <li><a class="dropdown-item" href="/admin">Flarum Admin</a></li>
                    <li><hr class="dropdown-divider" /></li>
                    <li><a class="dropdown-item" href="schema_viewer.php">Schema Viewer</a></li>
                </ul>
            </li>
        </ul>
    </nav>
    
    <div id="layoutSidenav">
        <!-- Sidebar -->
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Core</div>
                        <a class="nav-link <?php echo basename($_SERVER['SCRIPT_NAME']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </a>
                        <a class="nav-link <?php echo basename($_SERVER['SCRIPT_NAME']) == 'keywords.php' ? 'active' : ''; ?>" href="keywords.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-key"></i></div>
                            Keywords
                        </a>
                        
                        <div class="sb-sidenav-menu-heading">Configuration</div>
                        <a class="nav-link <?php echo basename($_SERVER['SCRIPT_NAME']) == 'schema_viewer.php' ? 'active' : ''; ?>" href="schema_viewer.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-database"></i></div>
                            Schema
                        </a>
                        <a class="nav-link" href="../db_test.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-vial"></i></div>
                            Test Connection
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Logged in as:</div>
                    Admin User
                </div>
            </nav>
        </div>
        
        <!-- Content -->
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <?php if (isset($pageHeader)): ?>
                        <h1 class="mt-4"><?php echo $pageHeader; ?></h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active"><?php echo $pageHeader; ?></li>
                        </ol>
                    <?php else: ?>
                        <h1 class="mt-4">Dashboard</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    <?php endif; ?>
                    
                    <?php if (isset($alerts) && !empty($alerts)): ?>
                        <?php foreach ($alerts as $alert): ?>
                            <div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show" role="alert">
                                <?php echo $alert['message']; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?php if (isset($content)): ?>
                        <?php echo $content; ?>
                    <?php endif; ?>
                </div>
            </main>
            
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Keywords Automation <?php echo date('Y'); ?></div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <a href="#">Terms &amp; Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    
    <!-- Bootstrap core JavaScript -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SB Admin core JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin@7.0.7/dist/js/scripts.js"></script>
    
    <!-- Initialize Tooltips -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
            
            // SB Admin sidebar toggle
            const sidebarToggle = document.getElementById('sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', event => {
                    event.preventDefault();
                    document.body.classList.toggle('sb-sidenav-toggled');
                    localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
                });
            }
        });
    </script>
    
    <?php if (isset($extraScripts)): ?>
        <?php echo $extraScripts; ?>
    <?php endif; ?>
</body>
</html> 