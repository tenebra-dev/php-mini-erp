<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mini ERP - <?= htmlspecialchars($pageTitle ?? 'Dashboard', ENT_QUOTES, 'UTF-8') ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --sidebar-width: 280px;
            --top-navbar-height: 56px;
        }
        
        body {
            padding-top: var(--top-navbar-height);
            background-color: #f8f9fa;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            height: calc(100vh - var(--top-navbar-height));
            position: fixed;
            left: 0;
            top: var(--top-navbar-height);
            background-color: #ffffff;
            border-right: 1px solid #dee2e6;
            overflow-y: auto;
            transition: all 0.3s;
            box-shadow: 2px 0 4px rgba(0,0,0,0.1);
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s;
            min-height: calc(100vh - var(--top-navbar-height));
            background-color: #ffffff;
            border-radius: 8px;
            margin-top: 10px;
            margin-right: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: #495057;
            border-radius: 0.5rem;
            margin: 0.25rem 1rem;
            padding: 0.75rem 1rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
        }
        
        .sidebar .nav-link i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }
        
        .sidebar .nav-link.active {
            background-color: #0d6efd;
            color: white;
            box-shadow: 0 2px 4px rgba(13, 110, 253, 0.3);
        }
        
        .sidebar .nav-link:hover:not(.active) {
            background-color: #e9ecef;
            color: #0d6efd;
            transform: translateX(2px);
        }
        
        .navbar-brand {
            padding: 0.5rem 1rem;
            font-weight: 600;
            font-size: 1.5rem;
        }
        
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .cart-counter {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -var(--sidebar-width);
                z-index: 1050;
            }
            
            .sidebar.show {
                margin-left: 0;
            }
            
            .main-content {
                margin-left: 0;
                margin-right: 0;
                border-radius: 0;
                margin-top: 0;
            }
            
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: var(--top-navbar-height);
                left: 0;
                width: 100%;
                height: calc(100vh - var(--top-navbar-height));
                background-color: rgba(0,0,0,0.5);
                z-index: 1040;
            }
            
            .sidebar-overlay.show {
                display: block;
            }
        }
        
        .sidebar-header {
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            background-color: #f8f9fa;
        }
        
        .sidebar-header h6 {
            margin: 0;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-md navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <button class="navbar-toggler me-2 d-md-none" type="button" onclick="toggleSidebar()">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand" href="/">
                <i class="fas fa-chart-line me-2"></i>Mini ERP
            </a>
            
            <div class="d-flex align-items-center">
                <!-- Cart Icon with Counter (if applicable) -->
                <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
                    <a href="/orders/cart" class="btn btn-outline-light me-3 position-relative">
                        <i class="bi bi-cart3"></i>
                        <span class="cart-counter"><?= count($_SESSION['cart']) ?></span>
                    </a>
                <?php endif; ?>
                
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i>
                        <?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuário', ENT_QUOTES, 'UTF-8') ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/profile"><i class="bi bi-person me-2"></i>Perfil</a></li>
                        <li><a class="dropdown-item" href="/settings"><i class="bi bi-gear me-2"></i>Configurações</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar Navigation -->
            <?php require __DIR__ . '/navigation.php'; ?>
            
            <!-- Main Content -->
            <main class="main-content">
                <!-- Breadcrumb (optional) -->
                <?php if (isset($breadcrumbs) && !empty($breadcrumbs)): ?>
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <?php foreach ($breadcrumbs as $index => $breadcrumb): ?>
                            <?php if ($index === count($breadcrumbs) - 1): ?>
                                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($breadcrumb['title']) ?></li>
                            <?php else: ?>
                                <li class="breadcrumb-item">
                                    <a href="<?= htmlspecialchars($breadcrumb['url']) ?>"><?= htmlspecialchars($breadcrumb['title']) ?></a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>
                <?php endif; ?>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    
    sidebar.classList.toggle('show');
    overlay.classList.toggle('show');
}

// Close sidebar when clicking outside on mobile
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.querySelector('.sidebar-overlay');
    if (overlay) {
        overlay.addEventListener('click', function() {
            toggleSidebar();
        });
    }
});
</script>
