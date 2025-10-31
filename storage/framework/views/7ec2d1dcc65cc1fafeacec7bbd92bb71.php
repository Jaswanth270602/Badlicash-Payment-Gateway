<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'BadliCash - Payment Gateway'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-violet: #6366f1;
            --primary-violet-dark: #4f46e5;
            --primary-violet-light: #818cf8;
            --gradient-start: #6366f1;
            --gradient-end: #8b5cf6;
            --sidebar-bg: linear-gradient(180deg, #1e1b4b 0%, #312e81 100%);
            --sidebar-hover: rgba(99, 102, 241, 0.2);
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --card-shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #1f2937;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 260px;
            background: var(--sidebar-bg);
            color: #fff;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.2);
        }

        .sidebar-brand {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #fff 0%, #e0e7ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-brand i {
            background: linear-gradient(135deg, var(--primary-violet-light) 0%, var(--primary-violet) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .mode-badge {
            margin-top: 8px;
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sidebar-menu {
            padding: 12px 0;
        }

        .sidebar-menu-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
            font-weight: 500;
        }

        .sidebar-menu-item i {
            font-size: 18px;
            width: 20px;
            text-align: center;
        }

        .sidebar-menu-item:hover {
            background: var(--sidebar-hover);
            color: #fff;
            border-left-color: var(--primary-violet-light);
            padding-left: 24px;
        }

        .sidebar-menu-item.active {
            background: var(--sidebar-hover);
            color: #fff;
            border-left-color: var(--primary-violet);
            font-weight: 600;
        }

        .sidebar-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            margin: 12px 20px;
        }

        /* Main Content Area */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        /* Top Bar */
        .topbar {
            height: 70px;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            box-shadow: var(--card-shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar-title {
            font-size: 24px;
            font-weight: 600;
            color: #1f2937;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        /* Mode Toggle */
        .mode-toggle {
            display: flex;
            background: #f3f4f6;
            border-radius: 8px;
            padding: 4px;
            gap: 4px;
        }

        .mode-toggle-btn {
            padding: 6px 16px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            background: transparent;
            color: #6b7280;
        }

        .mode-toggle-btn.active.test {
            background: #fef3c7;
            color: #d97706;
        }

        .mode-toggle-btn.active.live {
            background: #d1fae5;
            color: #059669;
        }

        /* Content Area */
        .content-wrapper {
            padding: 32px;
        }

        /* Cards */
        .stat-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 24px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            box-shadow: var(--card-shadow-lg);
            transform: translateY(-2px);
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-violet) 0%, var(--primary-violet-dark) 100%);
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-violet-dark) 0%, var(--primary-violet) 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }

        /* Loader */
        .loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .spinner-violet {
            width: 50px;
            height: 50px;
            border: 4px solid #e5e7eb;
            border-top-color: var(--primary-violet);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Pagination */
        .pagination {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-top: 24px;
        }

        .page-link {
            padding: 8px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            color: #6b7280;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .page-link:hover {
            background: var(--primary-violet);
            color: #fff;
            border-color: var(--primary-violet);
        }

        .page-link.active {
            background: var(--primary-violet);
            color: #fff;
            border-color: var(--primary-violet);
        }

        /* Tables */
        .table {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
        }

        .table thead {
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        }

        .table thead th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            color: #6b7280;
            border: none;
            padding: 16px;
        }

        .table tbody td {
            padding: 16px;
            vertical-align: middle;
        }

        /* Badges */
        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 12px;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .sidebar {
                width: 240px;
            }
            .main-content {
                margin-left: 240px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 260px;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .topbar {
                padding: 0 16px;
            }
            
            .topbar-title {
                font-size: 18px;
            }

            .content-wrapper {
                padding: 16px;
            }
            
            .stat-card {
                padding: 16px;
            }
            
            .row.g-4 {
                --bs-gutter-y: 1rem;
            }
            
            /* Mobile menu toggle */
            .mobile-menu-toggle {
                display: block;
                background: none;
                border: none;
                color: #fff;
                font-size: 24px;
                cursor: pointer;
                padding: 8px;
            }
        }

        @media (min-width: 769px) {
            .mobile-menu-toggle {
                display: none;
            }
        }
        
        /* Responsive tables */
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 14px;
            }
            
            .table thead th,
            .table tbody td {
                padding: 8px 4px;
            }
        }

        /* Toast Notifications */
        .toast-container {
            z-index: 1055;
        }

        .toast {
            border-radius: 12px;
            box-shadow: var(--card-shadow-lg);
        }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.3/angular.min.js"></script>
    <script>
    // Initialize Angular module immediately after Angular loads
    (function() {
        if (typeof angular !== 'undefined') {
            // Create module if it doesn't exist
            try {
                angular.module('badlicashApp');
            } catch(e) {
                angular.module('badlicashApp', []);
            }
        }
    })();
    </script>
</head>
<body>
<?php if(auth()->guard()->check()): ?>
<div class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <i class="bi bi-wallet2"></i>
            <span>BadliCash</span>
        </div>
        <?php if(auth()->user()->merchant): ?>
            <div class="mode-badge <?php echo e(auth()->user()->merchant->test_mode ? 'bg-warning text-dark' : 'bg-success'); ?>">
                <?php echo e(auth()->user()->merchant->test_mode ? 'TEST MODE' : 'LIVE MODE'); ?>

            </div>
        <?php endif; ?>
    </div>
    
    <nav class="sidebar-menu">
        <a href="<?php echo e(route('dashboard')); ?>" class="sidebar-menu-item <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
            <i class="bi bi-grid-1x2"></i>
            <span>Dashboard</span>
        </a>
        
        <?php if(auth()->user()->isMerchant()): ?>
        <a href="<?php echo e(route('merchant.transactions.index')); ?>" class="sidebar-menu-item <?php echo e(request()->routeIs('merchant.transactions.*') ? 'active' : ''); ?>">
            <i class="bi bi-credit-card-2-front"></i>
            <span>Transactions</span>
        </a>
        <a href="<?php echo e(route('merchant.orders.index')); ?>" class="sidebar-menu-item <?php echo e(request()->routeIs('merchant.orders.*') ? 'active' : ''); ?>">
            <i class="bi bi-receipt-cutoff"></i>
            <span>Orders</span>
        </a>
        <a href="<?php echo e(route('merchant.payment_links.index')); ?>" class="sidebar-menu-item <?php echo e(request()->routeIs('merchant.payment_links.*') ? 'active' : ''); ?>">
            <i class="bi bi-link-45deg"></i>
            <span>Payment Links</span>
        </a>
        <a href="<?php echo e(route('merchant.refunds.index')); ?>" class="sidebar-menu-item <?php echo e(request()->routeIs('merchant.refunds.*') ? 'active' : ''); ?>">
            <i class="bi bi-arrow-counterclockwise"></i>
            <span>Refunds</span>
        </a>
        <a href="<?php echo e(route('merchant.settlements.index')); ?>" class="sidebar-menu-item <?php echo e(request()->routeIs('merchant.settlements.*') ? 'active' : ''); ?>">
            <i class="bi bi-wallet2"></i>
            <span>Settlements</span>
        </a>
        <div class="sidebar-divider"></div>
        <a href="<?php echo e(route('merchant.api_keys.index')); ?>" class="sidebar-menu-item <?php echo e(request()->routeIs('merchant.api_keys.*') ? 'active' : ''); ?>">
            <i class="bi bi-key"></i>
            <span>API Keys</span>
        </a>
        <a href="<?php echo e(route('merchant.integration.index')); ?>" class="sidebar-menu-item <?php echo e(request()->routeIs('merchant.integration.*') ? 'active' : ''); ?>">
            <i class="bi bi-code-square"></i>
            <span>Integration</span>
        </a>
        <a href="<?php echo e(route('merchant.webhooks.index')); ?>" class="sidebar-menu-item <?php echo e(request()->routeIs('merchant.webhooks.*') ? 'active' : ''); ?>">
            <i class="bi bi-webhook"></i>
            <span>Webhooks</span>
        </a>
        <a href="<?php echo e(route('merchant.reports.index')); ?>" class="sidebar-menu-item <?php echo e(request()->routeIs('merchant.reports.*') ? 'active' : ''); ?>">
            <i class="bi bi-bar-chart-line"></i>
            <span>Reports</span>
        </a>
        <div class="sidebar-divider"></div>
        <a href="<?php echo e(route('merchant.settings.index')); ?>" class="sidebar-menu-item <?php echo e(request()->routeIs('merchant.settings.*') ? 'active' : ''); ?>">
            <i class="bi bi-gear"></i>
            <span>Settings</span>
        </a>
        <?php endif; ?>

        <?php if(auth()->user()->isAdmin()): ?>
        <div class="sidebar-divider"></div>
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="sidebar-menu-item <?php echo e(request()->routeIs('admin.*') ? 'active' : ''); ?>">
            <i class="bi bi-shield-check"></i>
            <span>Admin Panel</span>
        </a>
        <a href="<?php echo e(route('admin.merchants.index')); ?>" class="sidebar-menu-item <?php echo e(request()->routeIs('admin.merchants.*') ? 'active' : ''); ?>">
            <i class="bi bi-people"></i>
            <span>Merchants</span>
        </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-header mt-auto" style="border-top: 1px solid rgba(255,255,255,0.1); border-bottom: none;">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-light text-dark d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                <?php echo e(strtoupper(substr(auth()->user()->name, 0, 1))); ?>

            </div>
            <div class="flex-grow-1">
                <div class="small fw-bold"><?php echo e(auth()->user()->name); ?></div>
                <div class="small text-muted" style="opacity: 0.7;"><?php echo e(auth()->user()->email); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="topbar">
        <button class="mobile-menu-toggle d-md-none" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
        <div class="topbar-title"><?php echo $__env->yieldContent('page-title', 'Dashboard'); ?></div>
        <div class="topbar-actions">
            <?php if(auth()->user()->merchant): ?>
            <div class="mode-toggle">
                <button class="mode-toggle-btn <?php echo e(auth()->user()->merchant->test_mode ? 'active test' : ''); ?>" onclick="switchMode('test')">
                    <i class="bi bi-flask"></i> Test
                </button>
                <button class="mode-toggle-btn <?php echo e(!auth()->user()->merchant->test_mode ? 'active live' : ''); ?>" onclick="switchMode('live')">
                    <i class="bi bi-check-circle"></i> Live
                </button>
            </div>
            <?php endif; ?>
            <form action="<?php echo e(route('logout')); ?>" method="POST" class="d-inline">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <div class="content-wrapper">
        <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php echo $__env->yieldContent('content'); ?>
    </div>
</div>
<?php else: ?>
<div class="container py-4">
    <?php echo $__env->yieldContent('content'); ?>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function switchMode(mode) {
    const overlay = document.createElement('div');
    overlay.className = 'loader-overlay';
    overlay.innerHTML = '<div class="spinner-violet"></div>';
    document.body.appendChild(overlay);

    fetch("<?php echo e(route('merchant.settings.switch-mode')); ?>", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
        },
        body: JSON.stringify({mode})
    })
    .then(response => response.json())
    .then(data => {
        document.body.removeChild(overlay);
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to switch mode');
        }
    })
    .catch(error => {
        document.body.removeChild(overlay);
        alert('Failed to switch mode');
        console.error('Error:', error);
    });
}

function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('show');
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar');
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    
    if (window.innerWidth <= 768 && sidebar && menuToggle) {
        if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
            sidebar.classList.remove('show');
        }
    }
});
</script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
 <?php /**PATH C:\Users\dell\Desktop\gateway\resources\views/layouts/app-sidebar.blade.php ENDPATH**/ ?>