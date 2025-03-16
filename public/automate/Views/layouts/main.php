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
            padding: 0.5rem 1rem;
            color: rgba(255, 255, 255, 0.8);
        }
        .sidebar .nav-link:hover {
            color: #fff;
        }
        .sidebar .nav-link.active {
            color: #fff;
            font-weight: 500;
        }
        .sidebar .nav-link i {
            margin-right: 0.5rem;
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <!-- Top Navigation Bar -->
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <!-- Navbar Brand-->
        <a class="navbar-brand ps-3" href="<?php echo \Config\App::get('base_url'); ?>">Keywords Automation</a>
        
        <!-- Sidebar Toggle-->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!">
            <i class="fas fa-bars"></i>
        </button>
        
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
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="#!">Settings</a></li>
                    <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                    <li><hr class="dropdown-divider" /></li>
                    <li><a class="dropdown-item" href="#!">Logout</a></li>
                </ul>
            </li>
        </ul>
    </nav>
    
    <div id="layoutSidenav">
        <!-- Sidebar Navigation -->
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Core</div>
                        <a class="nav-link" href="<?php echo \Config\App::get('base_url'); ?>/">
                            <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                            Dashboard
                        </a>
                        
                        <a class="nav-link" href="<?php echo \Config\App::get('base_url'); ?>/keywords">
                            <div class="sb-nav-link-icon"><i class="fas fa-key"></i></div>
                            Manage Keywords
                        </a>
                        
                        <a class="nav-link" href="<?php echo \Config\App::get('base_url'); ?>/discussions">
                            <div class="sb-nav-link-icon"><i class="fas fa-comments"></i></div>
                            Discussions
                        </a>
                        
                        <a class="nav-link" href="<?php echo \Config\App::get('base_url'); ?>/tags">
                            <div class="sb-nav-link-icon"><i class="fas fa-tags"></i></div>
                            Manage Tags
                        </a>
                        
                        <div class="sb-sidenav-menu-heading">System</div>
                        <a class="nav-link" href="<?php echo \Config\App::get('base_url'); ?>/schema">
                            <div class="sb-nav-link-icon"><i class="fas fa-database"></i></div>
                            Schema Viewer
                        </a>
                        
                        <a class="nav-link" href="<?php echo \Config\App::get('base_url'); ?>/openrouter/settings">
                            <div class="sb-nav-link-icon"><i class="fas fa-robot"></i></div>
                            Open Router
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Logged in as:</div>
                    Administrator
                </div>
            </nav>
        </div>
        
        <!-- Page Content -->
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4"><?php echo isset($pageHeader) ? $pageHeader : 'Keywords Automation'; ?></h1>
                    
                    <?php if (!empty($alerts)): ?>
                        <div class="mt-3">
                            <?php foreach ($alerts as $alert): ?>
                                <div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show" role="alert">
                                    <?php echo $alert['message']; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Page Content -->
                    <?php echo $content; ?>
                </div>
            </main>
            
            <!-- Footer -->
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Keywords Automation Dashboard v1.0</div>
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
    
    <!-- Bootstrap core JavaScript-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Core theme JS-->
    <script src="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin@7.0.7/dist/js/scripts.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Page-specific scripts -->
    <?php if (isset($pageScripts)): ?>
        <?php echo $pageScripts; ?>
    <?php endif; ?>
</body>
</html> 