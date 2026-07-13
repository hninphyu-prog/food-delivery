<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Management</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
    
    <style>
        :root{
  --brand-1: #ff9b00;
  --brand-2: #ff6a00;
  --muted: #6c757d;
  --bg: #f4f6f8;
  --card: #ffffff;
}

/* Base */
* { box-sizing: border-box; }
body {
  margin: 0;
  font-family: "Inter", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
  background: var(--bg);
  color: #222;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  font-size: 15px;
}

/* Topbar */
.topbar {
  height: 72px;
  background: linear-gradient(90deg, var(--brand-1), var(--brand-2));
  color: #fff;
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 0 18px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.9);
  position: sticky;
  top: 0;
  z-index: 1000;
}
.brand-icon {
  width:46px;
  height:46px;
  display:flex;
  align-items:center;
  justify-content:center;
  background: rgba(255,255,255,0.12);
  border-radius:10px;
  font-size:18px;
}
.brand-text .h6 { margin: 0; font-size: 16px; font-weight: 600; }
.top-nav .nav-link {
  color: rgba(255,255,255,0.95);
  margin-left: 10px;
  padding: 8px 12px;
  border-radius: 8px;
  text-decoration: none;
}
.top-nav .nav-link:hover { background: rgba(255,255,255,0.08); text-decoration:none; }
.top-nav .nav-link.active { background: rgba(255,255,255,0.18); }

/* Open/Close toggle */
.rest-status-toggle {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-left: 12px;
  background: rgba(0,0,0,0.15);
  color: #fff;
  border: 1px solid rgba(255,255,255,0.25);
  padding: 6px 10px;
  border-radius: 10px;
  cursor: pointer;
}
.rest-status-toggle.closed { background: rgba(220,53,69,0.85); border-color: rgba(255,255,255,0.35); }
.rest-status-dot { width: 8px; height: 8px; border-radius: 50%; background: #28a745; }
.rest-status-toggle.closed .rest-status-dot { background: #ffd166; }
/* Inline status pill next to name */
.rest-status-pill {
  margin-left: 8px;
  padding: 3px 8px;
  border-radius: 999px;
  font-size: 12px;
  line-height: 1;
  background: rgba(25,135,84,.9); /* green */
  color: #fff;
}
.rest-status-pill.closed { background: rgba(220,53,69,.9); }

/* Layout */
.app-body { 
  padding-bottom: 40px; 
  display: flex;
  flex-direction: column;
  min-height: calc(100vh - 72px);
}

/* Main content */
.page-header { margin-bottom: 16px; }
.page-title { font-weight: 700; color: #222; font-size: 20px; }

/* Card */
.content-card { border-radius: 10px; overflow: hidden; background: var(--card); box-shadow: 0 6px 18px rgba(13, 38, 59, 0.03); }
.content-card .card-body { padding: 22px; }

/* Table */
.table > :not(caption) > * > * { padding: 0.9rem 0.9rem; }
.table thead th { font-weight: 600; color: #333; border-bottom: 1px solid rgba(0,0,0,0.06); background: transparent; }
.menu-thumb { width:64px; height:64px; object-fit:cover; border-radius:6px; box-shadow: 0 2px 6px rgba(0,0,0,0.06); }

/* Buttons */
.btn-primary {
  background: linear-gradient(90deg, var(--brand-1), var(--brand-2));
  border: none;
  box-shadow: none;
}
.btn-outline-primary { border-color: rgba(0,0,0,0.08); }

/* Badges */
.badge.bg-success { background-color: #20c997 !important; color: #fff; }

/* small utilities */
.text-muted.small { font-size: 12px; color: var(--card); }

/* Hover */
.table-hover tbody tr:hover { background: rgba(0,0,0,0.02); }

.logout-link{color: white;}

/* Notification Badge */
.notification-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

/* Notification Dropdown */
.notification-dropdown {
    width: 400px;
    max-width: 90vw;
}

.notification-item {
    padding: 12px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    transition: background-color 0.2s;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #f0f8ff;
}

.notification-amount {
    font-weight: bold;
    color: #28a745;
    font-size: 16px;
}

.notification-actions {
    margin-top: 8px;
    display: flex;
    gap: 8px;
}

.btn-sm {
    padding: 4px 8px;
    font-size: 12px;
}

.notification-empty {
    padding: 20px;
    text-align: center;
    color: #6c757d;
}

/* Modal Styles */
.confirmation-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1050;
}

.confirmation-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 25px;
    border-radius: 10px;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.payment-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
    padding: 15px;
    border-radius: 5px;
    margin: 10px 0;
}

.payment-rejected {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
    padding: 15px;
    border-radius: 5px;
    margin: 10px 0;
}

/* User Menu Styles */
.user-menu {
    position: relative;
    margin-left: 15px;
}

.user-menu-toggle {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 8px;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
}

.user-menu-toggle:hover {
    background: rgba(255,255,255,0.15);
}

.dropdown-content {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    min-width: 220px;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    margin-top: 5px;
}

.dropdown-content.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-content ul {
    list-style: none;
    margin: 0;
    padding: 8px 0;
}

.dropdown-content li {
    margin: 0;
}

.dropdown-content a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    color: #333;
    text-decoration: none;
    transition: background-color 0.2s;
    font-size: 14px;
}

.dropdown-content a:hover {
    background-color: #f8f9fa;
    text-decoration: none;
    color: #333;
}

.dropdown-content .divider {
    border-top: 1px solid #eee;
    margin-top: 8px;
    padding-top: 8px;
}

.dropdown-content i {
    width: 16px;
    text-align: center;
    color: #666;
}

.user-avatar-small {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: linear-gradient(180deg, var(--brand-2), var(--brand-1));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

/* Status toggle in dropdown */
.status-toggle-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
}

.status-indicator {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #28a745;
}

.status-dot.closed {
    background: #ffd166;
}

/* Navigation Dropdown */
.nav-dropdown {
    display: none;
    position: relative;
}

.nav-dropdown-toggle {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 8px;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
}

.nav-dropdown-toggle:hover {
    background: rgba(255,255,255,0.15);
}

.nav-dropdown-content {
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    min-width: 200px;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    margin-top: 5px;
}

.nav-dropdown-content.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.nav-dropdown-content a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    color: #333;
    text-decoration: none;
    transition: background-color 0.2s;
    font-size: 14px;
    border-bottom: 1px solid #f0f0f0;
}

.nav-dropdown-content a:last-child {
    border-bottom: none;
}

.nav-dropdown-content a:hover {
    background-color: #f8f9fa;
    text-decoration: none;
    color: #333;
}

.nav-dropdown-content a.active {
    background: linear-gradient(90deg,#fff4eb,#fff);
    border-left: 3px solid var(--brand-1);
    color: #ff9b00;
}

.nav-dropdown-content i {
    width: 16px;
    text-align: center;
    color: #666;
}

/* Notification badge in dropdown */
.nav-notification-badge {
    background: #dc3545;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-left: auto;
}

/* Mobile overlay */
.mobile-overlay {
    display: none;
    position: fixed;
    top: 72px;
    left: 0;
    width: 100%;
    height: calc(100% - 72px);
    background: rgba(0,0,0,0.5);
    z-index: 999;
}

/* Restaurant name and user info */
.restaurant-name {
    color: #fff;
    font-weight: 600;
    font-size: 15px;
    margin-right: 12px;
    border-right: 1px solid rgba(255,255,255,0.25);
    padding-right: 12px;
    white-space: nowrap;
    display: flex;
    align-items: center;
}
.restaurant-name i {
    color: rgba(255,255,255,0.9);
    font-size: 14px;
}

/* Mobile specific styles */
.mobile-restaurant-info {
    display: none;
    flex-direction: column;
    margin-right: auto;
}

.mobile-restaurant-name {
    color: #fff;
    font-weight: 600;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.mobile-user-name {
    color: rgba(255,255,255,0.9);
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Enhanced Responsive Design */
@media (max-width: 1100px) {
    .top-nav { 
        display: none !important; 
    }
    .nav-dropdown { 
        display: block !important; 
    }
    .restaurant-name {
        display: none !important;
    }
    .mobile-restaurant-info {
        display: flex !important;
    }
}

@media (max-width: 768px) {
    .topbar {
        padding: 0 12px;
        height: 60px;
    }
    .brand-icon {
        width: 36px;
        height: 36px;
        font-size: 16px;
    }
    .mobile-restaurant-name {
        font-size: 13px;
    }
    .mobile-user-name {
        font-size: 11px;
    }
    .rest-status-pill {
        font-size: 10px;
        padding: 2px 6px;
    }
    .nav-dropdown-toggle {
        padding: 6px 12px;
        font-size: 14px;
    }
    .user-menu-toggle {
        padding: 6px 12px;
    }
    .notification-dropdown {
        width: 300px;
    }
}

@media (max-width: 576px) {
    .topbar {
        height: 56px;
        padding: 0 8px;
    }
    .brand-icon {
        width: 32px;
        height: 32px;
        font-size: 14px;
    }
    .mobile-restaurant-name {
        font-size: 12px;
    }
    .mobile-user-name {
        font-size: 10px;
    }
    .rest-status-pill {
        font-size: 9px;
        padding: 1px 4px;
    }
    .nav-dropdown-toggle {
        padding: 4px 10px;
        font-size: 13px;
    }
    .nav-dropdown-toggle span {
        display: none;
    }
    .user-menu-toggle {
        padding: 4px 10px;
    }
    .notification-dropdown {
        width: 280px;
    }
    .dropdown-content, .nav-dropdown-content {
        min-width: 180px;
    }
}

@media (max-width: 400px) {
    .mobile-restaurant-name {
        max-width: 120px;
    }
    .brand-icon {
        margin-right: 8px;
    }
}

/* Small polish */
.page-title small { color: var(--muted); }
    </style>
</head>
<body>
    <!-- Topbar -->
    <header class="topbar d-flex align-items-center px-3">
        <div class="brand d-flex align-items-center">
            <div class="brand-icon me-3"><i class="fas fa-utensils"></i></div>
        </div>
        
        <!-- Desktop Restaurant Name -->
        <span class="restaurant-name d-flex align-items-center px-2">
            <i class="fas fa-store me-2"></i> 
            <?php 
            echo isset($_SESSION['restaurant_name']) ? htmlspecialchars($_SESSION['restaurant_name']) : 'Restaurant Name';
            ?>
            <span id="restStatusPill" class="rest-status-pill">
                Open
            </span>
        </span>
        
        <!-- Mobile Restaurant & User Info -->
        <div class="mobile-restaurant-info">
            <div class="mobile-restaurant-name">
                <i class="fas fa-store me-1"></i>
                <?php 
                echo isset($_SESSION['restaurant_name']) ? htmlspecialchars($_SESSION['restaurant_name']) : 'Restaurant';
                ?>
                <span id="mobileRestStatusPill" class="rest-status-pill">
                    Open
                </span>
            </div>
            <!-- <div class="mobile-user-name">
                <i class="fas fa-user me-1"></i>
                <?php 
                echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User';
                ?>
            </div> -->
        </div>

        <!-- Desktop Navigation -->
        <nav class="top-nav d-none d-lg-flex align-items-center ms-auto">
            <!-- Main Navigation Links -->
            <a href="index.php?page=dashboard" class="nav-link" id="nav-dashboard">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="index.php?page=menu" class="nav-link" id="nav-menu">
                <i class="fas fa-concierge-bell"></i> Menu
            </a>
            <a href="index.php?page=orders" class="nav-link" id="nav-orders">
                <i class="fas fa-list-alt"></i> Orders
            </a>
            <a href="index.php?page=financial" class="nav-link" id="nav-financial">
                <i class="fas fa-chart-line"></i> Financial
            </a>
            
               
              <a href="confirm_settlement.php" class="nav-link position-relative" id="nav-payout">
    <i class="fas fa-money-bill-wave"></i> Payout
    <span id="payout-notification" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge" style="display: none;">0</span>
</a>
            <!-- User Dropdown Menu -->
            <div class="user-menu">
                <div class="user-menu-toggle" id="userMenuToggle">
                    <div class="user-avatar-small">
                        <i class="fas fa-user"></i>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-content" id="dropdownContent">
                    <ul>
                        <li>
                            <a href="index.php?page=profile" id="nav-profile">
                                <i class="fas fa-user-circle"></i> 
                                <span>User Profile</span>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="dropdownStatusToggle">
                                <i class="fas fa-store"></i>
                                <div class="status-toggle-item">
                                    <span>Restaurant Status</span>
                                    <div class="status-indicator">
                                        <span class="status-dot"></span>
                                        <span id="dropdownStatusText">Open</span>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="divider">
                            <a href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> 
                                <span>Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Mobile/Tablet Navigation Dropdown -->
        <div class="nav-dropdown d-lg-none">
            <div class="nav-dropdown-toggle" id="navDropdownToggle">
                <i class="fas fa-bars"></i>
                <span>Menu</span>
            </div>
            <div class="nav-dropdown-content" id="navDropdownContent">
                <a href="index.php?page=dashboard" class="nav-mobile-dashboard">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="index.php?page=menu" class="nav-mobile-menu">
                    <i class="fas fa-concierge-bell"></i> Menu Management
                </a>
                <a href="index.php?page=orders" class="nav-mobile-orders">
                    <i class="fas fa-list-alt"></i> Orders Management
                </a>
                <a href="index.php?page=financial" class="nav-mobile-financial">
                    <i class="fas fa-chart-line"></i> Financial
                </a>
                
              <a href="confirm_settlement.php" class="nav-link position-relative" id="nav-payout">
    <i class="fas fa-money-bill-wave"></i> Payout
    <span id="payout-notification" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge" style="display: none;">0</span>
</a>
                
                <a href="index.php?page=profile" class="nav-mobile-profile">
                    <i class="fas fa-user-circle"></i> User Profile
                </a>
                <a href="#" id="mobileStatusToggle">
                    <i class="fas fa-store"></i>
                    <div class="status-toggle-item">
                        <span>Restaurant Status</span>
                        <div class="status-indicator">
                            <span class="status-dot"></span>
                            <span id="mobileStatusText">Open</span>
                        </div>
                    </div>
                </a>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>
    
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Function to set active navigation item
        function setActiveNav() {
            const urlParams = new URLSearchParams(window.location.search);
            const currentPage = urlParams.get('page') || 'dashboard';
            const currentFile = window.location.pathname.split('/').pop();
            
            // Remove active class from all navigation items
            document.querySelectorAll('.nav-link, .nav-item, .nav-dropdown-content a').forEach(item => {
                item.classList.remove('active');
            });
            
            // Set active class based on current page
            if (currentFile === 'confirm_settlement.php') {
                document.getElementById('nav-notifications')?.classList.add('active');
                document.querySelector('.nav-mobile-notifications')?.classList.add('active');
            } else {
                switch(currentPage) {
                    case 'dashboard':
                        document.getElementById('nav-dashboard')?.classList.add('active');
                        document.querySelector('.nav-mobile-dashboard')?.classList.add('active');
                        break;
                    case 'menu':
                        document.getElementById('nav-menu')?.classList.add('active');
                        document.querySelector('.nav-mobile-menu')?.classList.add('active');
                        break;
                    case 'orders':
                        document.getElementById('nav-orders')?.classList.add('active');
                        document.querySelector('.nav-mobile-orders')?.classList.add('active');
                        break;
                    case 'financial':
                        document.getElementById('nav-financial')?.classList.add('active');
                        document.querySelector('.nav-mobile-financial')?.classList.add('active');
                        break;
                    case 'profile':
                        document.getElementById('nav-profile')?.classList.add('active');
                        document.querySelector('.nav-mobile-profile')?.classList.add('active');
                        break;
                }
            }
        }

        // Set active navigation on page load
        setActiveNav();
        
        // Load the initial restaurant status
        loadRestaurantStatus();

        // User dropdown functionality
        const userMenuToggle = document.getElementById('userMenuToggle');
        const dropdownContent = document.getElementById('dropdownContent');
        const userMenu = document.querySelector('.user-menu');

        // Navigation dropdown functionality
        const navDropdownToggle = document.getElementById('navDropdownToggle');
        const navDropdownContent = document.getElementById('navDropdownContent');
        const navDropdown = document.querySelector('.nav-dropdown');

        if (userMenuToggle) {
            userMenuToggle.addEventListener('click', function(event) {
                event.stopPropagation();
                dropdownContent.classList.toggle('show');
            });
        }

        if (navDropdownToggle) {
            navDropdownToggle.addEventListener('click', function(event) {
                event.stopPropagation();
                navDropdownContent.classList.toggle('show');
            });
        }

        // Close dropdowns when clicking outside
        window.addEventListener('click', function(event) {
            if (userMenu && !userMenu.contains(event.target)) {
                dropdownContent.classList.remove('show');
            }
            if (navDropdown && !navDropdown.contains(event.target)) {
                navDropdownContent.classList.remove('show');
            }
        });

        // Restaurant status toggle functionality
        // function toggleRestaurantStatus() {
        //     const isClosed = document.getElementById('dropdownStatusText').textContent === 'Closed';
        //     const newStatus = isClosed ? 'active' : 'closed';
            
        //     // Update all status indicators
        //     const statusIndicators = [
        //         document.getElementById('dropdownStatusText'),
        //         document.getElementById('mobileStatusText'),
        //         document.getElementById('restStatusPill'),
        //         document.getElementById('mobileRestStatusPill')
        //     ];
            
        //     statusIndicators.forEach(indicator => {
        //         if (indicator) {
        //             if (indicator.id.includes('StatusText')) {
        //                 indicator.textContent = newStatus === 'active' ? 'Open' : 'Closed';
        //             } else {
        //                 indicator.textContent = newStatus === 'active' ? 'Open' : 'Closed';
        //                 indicator.classList.toggle('closed', newStatus !== 'active');
        //             }
        //         }
        //     });
            
        //     // Update status dots
        //     const statusDots = document.querySelectorAll('.status-dot');
        //     statusDots.forEach(dot => {
        //         dot.classList.toggle('closed', newStatus !== 'active');
        //     });
            
        //     // Close dropdowns
        //     dropdownContent.classList.remove('show');
        //     navDropdownContent.classList.remove('show');
      
      
    
    // Update the status toggle button appearance
  
// Load and set the initial restaurant status
function loadRestaurantStatus() {
    // Check if we have a cached status in localStorage
    const cachedStatus = localStorage.getItem('restaurantStatus');
    if (cachedStatus) {
        updateStatusIndicators(
            cachedStatus,
            cachedStatus === 'active' ? 'Open' : 'Closed'
        );
    }

    // Then fetch the latest status from the server
    fetch('../../api/get_restaurant_status.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const status = data.status;
                updateStatusIndicators(
                    status,
                    status === 'active' ? 'Open' : 'Closed'
                );
                // Update the cached status
                localStorage.setItem('restaurantStatus', status);
            }
        })
        .catch(error => {
            console.error('Error loading restaurant status:', error);
        });
}

function toggleRestaurantStatus() {
    const isClosed = document.getElementById('dropdownStatusText').textContent === 'Closed';
    const newStatus = isClosed ? 'active' : 'closed';
    const newStatusText = isClosed ? 'Open' : 'Closed';
    
    console.log('Toggling status to:', newStatus);
    
    // Make API call to update database
    const formData = new FormData();
    formData.append('action', 'toggle_status');
    formData.append('status', newStatus);
    
    fetch('toggle_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            // Update all status indicators only if API call succeeded
            updateStatusIndicators(
                data.new_status, 
                data.new_status === 'active' ? 'Open' : 'Closed'
            );
            // Update the cached status
            localStorage.setItem('restaurantStatus', data.new_status);
            showMessage('Restaurant status updated successfully!', 'success');
        } else {
            showMessage('Failed to update restaurant status: ' + (data.message || 'Unknown error'), 'error');
            console.error('Status update failed:', data);
        }
    })
    .catch(error => {
        console.error('Error updating status:', error);
        showMessage('Network error updating restaurant status', 'error');
    });
    
    // Close dropdowns
    if (dropdownContent) dropdownContent.classList.remove('show');
    if (navDropdownContent) navDropdownContent.classList.remove('show');
}

function updateStatusIndicators(status, statusText) {
    console.log('Updating UI to:', status, statusText);
    
    // Update all status indicators
    const statusIndicators = [
        document.getElementById('dropdownStatusText'),
        document.getElementById('mobileStatusText'),
        document.getElementById('restStatusPill'),
        document.getElementById('mobileRestStatusPill')
    ];
    
    statusIndicators.forEach(indicator => {
        if (indicator) {
            if (indicator.id.includes('StatusText')) {
                indicator.textContent = statusText;
            } else {
                indicator.textContent = statusText;
                indicator.classList.toggle('closed', status !== 'active');
            }
        }
    });
    
    // Update status dots
    const statusDots = document.querySelectorAll('.status-dot');
    if (statusDots && statusDots.length > 0) {
        statusDots.forEach(dot => {
            if (dot) {
                dot.classList.toggle('closed', status !== 'active');
            }
        });
    }
    
    // Update the status in the top bar if it exists
    const topBarStatus = document.getElementById('topBarStatus');
    if (topBarStatus) {
        topBarStatus.textContent = statusText;
        topBarStatus.classList.toggle('closed', status !== 'active');
    }
}
        
        // Attach event listeners to status toggles
        const dropdownBtn = document.getElementById('dropdownStatusToggle');
        const mobileBtn = document.getElementById('mobileStatusToggle');
        
        if (dropdownBtn) {
            dropdownBtn.addEventListener('click', function(e) {
                e.preventDefault();
                toggleRestaurantStatus();
            });
        }
        
        if (mobileBtn) {
            mobileBtn.addEventListener('click', function(e) {
                e.preventDefault();
                toggleRestaurantStatus();
            });
        }

        // Navigation click handlers to set active state
        document.querySelectorAll('.nav-link, .nav-dropdown-content a').forEach(link => {
            if (link.getAttribute('href') && !link.getAttribute('href').startsWith('#')) {
                link.addEventListener('click', function() {
                    // Close dropdowns
                    navDropdownContent.classList.remove('show');
                });
            }
        });
    });

    // Show message function
    function showMessage(message, type) {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type === 'success' ? 'success' : 'warning'} alert-dismissible fade show`;
        alert.style.position = 'fixed';
        alert.style.top = '80px';
        alert.style.right = '20px';
        alert.style.zIndex = '1060';
        alert.style.minWidth = '300px';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alert);
        
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 5000);
    }

    // Handle window resize for responsive behavior
    window.addEventListener('resize', function() {
        const navDropdownContent = document.getElementById('navDropdownContent');
        const dropdownContent = document.getElementById('dropdownContent');
        
        if (navDropdownContent) navDropdownContent.classList.remove('show');
        if (dropdownContent) dropdownContent.classList.remove('show');
    });
    </script>
    
    <script>
    // Function to update notification count
    function updateNotificationCount() {
        fetch('/foodandme/api/fetch_restaurant_notifications.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                const notificationBadge = document.getElementById('payout-notification');
                const count = data.count || 0;
                
                if (count > 0) {
                    notificationBadge.textContent = count;
                    notificationBadge.style.display = 'block';
                } else {
                    notificationBadge.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error fetching notifications:', error);
            });
    }

    // Update notification count on page load and every 60 seconds
    document.addEventListener('DOMContentLoaded', function() {
        updateNotificationCount();
        setInterval(updateNotificationCount, 60000);
    });
    </script>
    
    <style>
    /* Notification badge styles */
    #nav-payout {
        position: relative;
    }
    
    #payout-notification {
        top: -5px;
        right: -5px;
        padding: 0.35em 0.5em;
        font-size: 0.65em;
        line-height: 1;
        border-radius: 10px;
        border: 1px solid #fff;
    }
    </style>
</body>
</html>