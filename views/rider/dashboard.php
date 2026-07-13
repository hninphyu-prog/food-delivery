<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'delivery') {
    header("Location: ../../login.php");
    exit;
}

$delivery_boy_name = htmlspecialchars($_SESSION['name'] ?? 'Rider');
$delivery_boy_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Delivery Dashboard</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <style>
        body {
            font-family: sans-serif;
            background: #f4f6f8;
            margin: 0;
        }
        .chat-header {
            background: #f8f9fa;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-panel {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s ease;
            margin-top: 8px;
            border-radius: 10px;
            border: 1px solid #ddd;
            background: white;
        }

        .chat-panel.open {
            max-height: 400px; /* enough for smooth expand */
        }

        .chat-messages {
            height: 250px;
            overflow-y: auto;
            padding: 10px;
        }

        .chat-input-area {
            display: flex;
            padding: 10px;
        }

        .chat-input-area input {
            flex: 1;
            padding: 8px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }

        .chat-input-area button {
            margin-left: 8px;
            padding: 8px 14px;
            background: #17a2b8;
            color: white;
            border: none;
            border-radius: 8px;
        }

        .chat-message {
            margin: 8px 0;
            padding: 8px 12px;
            border-radius: 8px;
            max-width: 80%;
            word-wrap: break-word;
        }

        .chat-message.you {
            background: #e3f2fd;
            margin-left: auto;
            margin-right: 0;
            text-align: right;
        }

        .chat-message.other {
            background: #f1f1f1;
            margin-right: auto;
            margin-left: 0;
        }

        .chat-message strong {
            color: #333;
        }

        .header {
            background: rgb(255, 102, 0);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 200;
        }
        
        .brand-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .customer_brand__name {
            font-size: 25px;
            font-weight: bold;
        }
        
        .customer_brand__name span {
            color: #0c0c0cff;
        }
        #brand-logo{
             width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    color:  rgb(255, 102, 0);
    border-radius: 50%;
    font-size: 1.2rem;
        }
        .user-menu {
            position: relative;
            display: inline-block;
            z-index: 1000;
        }
        
        .user-menu-btn {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .user-menu-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            background: white;
            min-width: 200px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-radius: 4px;
            z-index: 1000;
            margin-top: 5px;
            border: 1px solid #ddd;
        }
        
        .dropdown-menu.show {
            display: block;
            animation: fadeIn 0.2s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            color: #333;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 13px;
        }
        
        .dropdown-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            color: #666;
        }
        
        .dropdown-item:hover {
            background: #f8f9fa;
            color: #000;
        }
        
        .dropdown-divider {
            height: 1px;
            background: #eee;
            margin: 5px 0;
        }

            /* Ensure proper touch targets for mobile */
@media (max-width: 768px) {
    .user-menu-btn {
        min-height: 44px !important;
        min-width: 44px !important;
    }
    
    .dropdown-item {
        min-height: 44px !important;
        display: flex !important;
        align-items: center !important;
    }
    
    /* Improve dropdown positioning on mobile */
    .dropdown-menu {
        position: fixed !important;
        top: auto !important;
        left: 10 !important;
        right: 0 !important;
        width: 60% !important;
        max-width: 100vw !important;
        margin-top: 5px !important;
        z-index: 10000 !important;
    }
}

/* Remove tap highlight on mobile */
.user-menu-btn,
.dropdown-item {
    -webkit-tap-highlight-color: transparent;
    touch-action: manipulation;
}

        @media (max-width: 480px) {
            .header {
                padding: 10px 12px;
            }
            
            .customer_brand__name {
                font-size: 18px;
            }
            
            .user-menu-btn {
                font-size: 13px;
                padding: 8px 10px;
                min-height: 44px;
            }
            
            .user-menu-btn span {
                max-width: 120px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            
            .dropdown-menu {
                width: 200px;
            }
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.9);
        }

        #map {
            height: 500px;
            border-radius: 8px;
        }

        .btn {
            padding: 8px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            margin: 2px;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-warning {
            background: #ffc107;
            color: black;
        }

        .btn-info {
            background: #17a2b8;
            color: white;
        }

        .order-list table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-list th,
        .order-list td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            text-align: left;
            font-size: 13px;
        }

        .order-list .actions {
            text-align: right;
        }

        .small {
            font-size: 12px;
            color: #666;
        }

        .status-pending {
            color: #ff9800;
            font-weight: bold;
        }

        .status-preparing {
            color: #2196f3;
            font-weight: bold;
        }

        .status-on_the_way {
            color: #4caf50;
            font-weight: bold;
        }

        .status-delivered {
            color: #607d8b;
            font-weight: bold;
        }

        .status-accepted {
            color: #9c27b0;
            font-weight: bold;
        }

        .order-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }

        .order-details h4 {
            margin: 0 0 10px 0;
            color: #333;
        }

        .order-items {
            margin: 10px 0;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }

        .order-total {
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 10px;
        }

        .delivery-info {
            background: #e7f3ff;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }

        .no-orders {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }

        /* Financial Tabs Styles */
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
        }

        .tab {
            width: 50%;
            font-size: 14px;
            padding: 12px 24px;
            background: #f8f9fa;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-weight: bold;
            color: #666;
        }

        .tab.active {
            background: white;
            color: #007bff;
            border-bottom-color: #007bff;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Financial Cards */
        .financial-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .financial-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.9);
            text-align: center;
        }

        .financial-card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #666;
        }

        .financial-card .amount {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
        }

        .financial-card .description {
            font-size: 12px;
            color: #999;
        }

        /* Transaction List */
        .transaction-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .transaction-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .transaction-item:last-child {
            border-bottom: none;
        }

        .transaction-info {
            flex: 1;
        }

        .transaction-amount {
            font-weight: bold;
        }

        .transaction-positive {
            color: #28a745;
        }

        .transaction-negative {
            color: #dc3545;
        }

        .transaction-date {
            font-size: 12px;
            color: #999;
        }

        /* Timeout and Notification Styles */
        .rider-timeout-warning {
            background: #fff3cd !important;
            border: 2px solid #ffc107 !important;
            color: #856404 !important;
            padding: 12px !important;
            border-radius: 5px !important;
            margin: 10px 0 !important;
            font-weight: bold !important;
            animation: pulse 1.5s infinite;
            text-align: center;
        }

        .status-available {
            background: #d4edda;
            color: #155724;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
        }

        .no-orders.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            color: #721c24;
            padding: 20px;
        }

        .btn-retry {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn-retry:hover {
            background: #0056b3;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.8;
                transform: scale(1.02);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        .notification {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border-left: 4px solid rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 992px) {
            .header {
                display: flex;
                flex-direction: row;
                overflow: hidden;
                align-items: center;
            }
        }

        /* Financial Tabs Styles */
        .tabs-container {
            position: sticky;
            top: 48px;
            z-index: 100;
            background: white;
            border-bottom: 2px solid #ddd;

        }

        .tabs {
            display: flex;
            margin-bottom: 0;
        }

        .tab {
            width: 25%;
            font-size: 14px;
            padding: 12px 24px;
            background: #f8f9fa;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-weight: bold;
            color: #666;
        }

        .tab.active {
            background: white;
            color: #007bff;
            border-bottom-color: #007bff;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Deposit Tab Styles */
        .deposit-tab-content {
            display: none;
        }

        .deposit-tab-content.active {
            display: block;
        }

        .deposit-status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .deposit-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #007bff;
        }

        .deposit-item.rejected {
            border-left-color: #dc3545;
            background: #f8f9fa;
        }

        .deposit-item.approved {
            border-left-color: #28a745;
            background: #f8f9fa;
        }

        .deposit-actions {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="brand-section">
            <i class="fas fa-utensils" id=brand-logo></i>
            <span class="customer_brand__name">Food<span>&amp;</span>Me</span>
        </div>
        
        <div class="user-menu">
            <button class="user-menu-btn" id="userMenuBtn" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-user-circle"></i>
                <span><?= $delivery_boy_name ?> </span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="dropdown-menu" id="userDropdown" role="menu">
                <a href="profile.php" class="dropdown-item" role="menuitem">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <a href="rider_payout_status.php" class="dropdown-item" role="menuitem">
                    <i class="fas fa-money-bill-wave"></i> My Payouts
                </a>
                
                <div class="dropdown-divider"></div>
                <a href="../../logout.php" class="dropdown-item" role="menuitem">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <div>
        <!-- Financial Tabs -->
        <div class="card" style="grid-column: 1 / -1;">
            <div class="tabs-container">
                <div class="tabs">
                    <button class="tab active" onclick="switchTab('delivery')"> Delivery</button>
                    <button class="tab" onclick="switchTab('cod')">COD to Pay</button>
                    <button class="tab" onclick="switchTab('financial')"> Today's Financial</button>
                    <button class="tab" onclick="switchTab('earnings')">Earning Summary</button>
                </div>
            </div>
            <!-- Delivery Tab (Original Content) -->
            <div id="delivery-tab" class="tab-content active">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <h3>New Orders Nearby</h3>
                        <div id="new-orders-list" class="order-list">Loading new orders...</div>
                    </div>
                    <div>
                        <h3>My Active Deliveries</h3>
                        <div id="active-deliveries-list" class="order-list">Loading active deliveries...</div>
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <h3> Live Tracking Map</h3>
                    <div id="map"></div>
                    <p style="margin-top:10px">
                        <button id="start-tracking" class="btn btn-success"> Start Location Tracking</button>
                        <button id="stop-tracking" class="btn btn-danger" disabled> Stop Tracking</button>
                        <span id="tracking-status" style="margin-left:12px; font-size: 14px;">Location tracking not active</span>
                    </p>
                </div>
            </div>

            <!-- Financial Overview Tab -->
            <div id="financial-tab" class="tab-content">
                <h3>Financial Overview</h3>
                <div class="financial-cards">
                    <div class="financial-card primary">
                        <h3>Total amount need to Deposit</h3>
                        <div class="amount" id="cod-balance">Loading...</div>
                        <div class="description">Full order amount (products + delivery)</div>
                    </div>
                    <div class="financial-card success">
                        <h3>Delivery Fees Earned</h3>
                        <div class="amount" id="delivery-balance">Loading...</div>
                        <div class="description">Your earned delivery fees</div>
                    </div>
                    <div class="financial-card warning">
                        <h3>Net Balance</h3>
                        <div class="amount" id="net-balance">Loading...</div>
                        <div class="description">(Earnings - Amounts to Deposit)</div>
                    </div>
                    <div class="financial-card danger">
                        <h3>Pending Deposits</h3>
                        <div class="amount" id="pending-deposits">Loading...</div>
                        <div class="description">Awaiting admin verification</div>
                    </div>
                </div>

                <h4>Recent Transactions</h4>
                <div id="recent-transactions" class="transaction-list">
                    Loading transactions...
                </div>
            </div>

            <!-- COD Tab -->
            <div id="cod-tab" class="tab-content">
                <h3> COD Orders & Deposits</h3>

                <!-- Status Summary Cards -->
                <div class="financial-cards">
                    <div class="financial-card danger">
                        <h3>Total Amount Due</h3>
                        <div class="amount" id="total-cod-due">Loading...</div>
                        <div class="description">Full amount you need to pay to admin</div>
                    </div>
                    <div class="financial-card warning">
                        <h3>Pending Approval</h3>
                        <div class="amount" id="pending-approval">Loading...</div>
                        <div class="description">Deposits awaiting admin verification</div>
                    </div>
                    <div class="financial-card success">
                        <h3>Today's Approved Deposits</h3>
                        <div class="amount" id="approved-deposits">Loading...</div>
                        <div class="description">Verified by admin</div>
                    </div>
                    <div class="financial-card primary">
                        <h3>Rejected Deposits</h3>
                        <div class="amount" id="rejected-deposits">Loading...</div>
                        <div class="description">Need resubmission</div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div style="margin: 20px 0; display: flex; gap: 10px; flex-wrap: wrap;">
                    <button class="btn btn-primary" onclick="makeDeposit()">
                        Make Full Deposit
                    </button>
                    <button class="btn btn-info" onclick="loadCODOrders()">
                        Refresh List
                    </button>
                </div>

                <!-- Deposit Status Tabs -->
                <div class="tabs-container" style="margin: 20px 0;">
                    <div class="tabs">
                        <button class="tab active" onclick="switchDepositTab('pending')"> Pending</button>
                        <button class="tab" onclick="switchDepositTab('approved')"> Approved</button>
                        <button class="tab" onclick="switchDepositTab('rejected')"> Rejected</button>

                    </div>
                </div>

                <!-- Pending Deposits -->
                <div id="pending-deposits-content" class="deposit-tab-content active">
                    <h4> Deposits Pending Approval</h4>
                    <div id="pending-deposits-list" class="order-list">
                        Loading pending deposits...
                    </div>
                </div>

                <!-- Approved Deposits -->
                <div id="approved-deposits-content" class="deposit-tab-content">
                    <h4> Approved Deposits</h4>
                    <div id="approved-deposits-list" class="order-list">
                        Loading approved deposits...
                    </div>
                </div>

                <!-- Rejected Deposits -->
                <div id="rejected-deposits-content" class="deposit-tab-content">
                    <h4> Rejected Deposits</h4>
                    <div id="rejected-deposits-list" class="order-list">
                        Loading rejected deposits...
                    </div>
                </div>

                <!-- COD Orders List -->
                <div id="cod-orders-content" class="deposit-tab-content">
                    <h4> COD Orders Delivered Today</h4>
                    <div id="cod-orders-list" class="order-list">
                        Loading COD orders...
                    </div>
                </div>
            </div>

            <!-- Earnings Tab -->
            <div id="earnings-tab" class="tab-content">
                <h3> My Earnings</h3>
                <div class="financial-cards">
                    <div class="financial-card success">
                        <h3>Today's Earnings</h3>
                        <div class="amount" id="today-earnings">Loading...</div>
                        <div class="description">Delivery fees earned today</div>
                    </div>
                    <div class="financial-card primary">
                        <h3>This Week's Earnings</h3>
                        <div class="amount" id="week-earnings">Loading...</div>
                        <div class="description">Delivery fees this week</div>
                    </div>
                    <div class="financial-card warning">
                        <h3>This Week's Payout</h3>
                        <div class="amount" id="total-payouts">Loading...</div>
                        <div class="description">Paid out amount from admin</div>
                    </div>
                </div>

                <h4>Today's Delivered Orders</h4>
                <div id="today-orders-list" class="order-list">
                    Loading today's orders...
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // CORRECTED: Enhanced dropdown menu for mobile and desktop
        document.addEventListener('DOMContentLoaded', function() {
            const userMenuBtn = document.getElementById('userMenuBtn');
            const userDropdown = document.getElementById('userDropdown');
            let dropdownOpen = false;

            function toggleDropdown(e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                
                dropdownOpen = !userDropdown.classList.contains('show');
                
                if (dropdownOpen) {
                    userDropdown.classList.add('show');
                    userMenuBtn.setAttribute('aria-expanded', 'true');
                    
                    // Handle body scroll on mobile
                    if (window.innerWidth <= 768) {
                        document.body.classList.add('dropdown-open');
                    }
                } else {
                    closeDropdown();
                }
            }
            
            function closeDropdown() {
                userDropdown.classList.remove('show');
                userMenuBtn.setAttribute('aria-expanded', 'false');
                document.body.classList.remove('dropdown-open');
                dropdownOpen = false;
            }
            
            // Use both click and touch events for better mobile support
            userMenuBtn.addEventListener('click', function(e) {
                toggleDropdown(e);
            });
            
            // Add touch event for mobile
            userMenuBtn.addEventListener('touchend', function(e) {
                e.preventDefault();
                toggleDropdown(e);
            });
            
            // Close dropdown when clicking/touching outside
            document.addEventListener('click', function(e) {
                if (dropdownOpen && !userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                    closeDropdown();
                }
            });
            
            // Also close on touch outside for mobile
            document.addEventListener('touchend', function(e) {
                if (dropdownOpen && !userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                    closeDropdown();
                }
            });
            
            // Close dropdown on orientation change and resize
            window.addEventListener('orientationchange', closeDropdown);
            window.addEventListener('resize', closeDropdown);
            
            // Prevent dropdown close when interacting with dropdown items
            userDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
            });
            
            userDropdown.addEventListener('touchend', function(e) {
                e.stopPropagation();
            });
        });
        
        let currentTrackingOrderId = null;

        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });

            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Fix for mobile - event might be undefined
            if (typeof event !== 'undefined' && event.target) {
                event.target.classList.add('active');
            } else {
                // Fallback for mobile - find the clicked tab
                const tabs = document.querySelectorAll('.tab');
                tabs.forEach(tab => {
                    if (tab.textContent.trim().includes(tabName.charAt(0).toUpperCase() + tabName.slice(1))) {
                        tab.classList.add('active');
                    }
                });
            }

            if (tabName === 'financial') {
                loadFinancialOverview();
            } else if (tabName === 'cod') {
                loadCODOrders();
            } else if (tabName === 'earnings') {
                loadEarnings();
            }
        }

        // --- Financial API Functions ---
        async function loadFinancialOverview() {
            try {
                const data = await apiCall('../../api/get_rider_financials.php');

                if (data.success) {
                    document.getElementById('cod-balance').textContent = formatCurrency(data.cod_balance || 0);
                    document.getElementById('delivery-balance').textContent = formatCurrency(data.delivery_balance || 0);
                    document.getElementById('net-balance').textContent = formatCurrency(data.net_balance || 0);
                    document.getElementById('pending-deposits').textContent = formatCurrency(data.pending_deposits || 0);

                    if (data.transactions && data.transactions.length > 0) {
                        const transactionsHtml = data.transactions.map(transaction => `
                    <div class="transaction-item">
                        <div class="transaction-info">
                            <strong>${transaction.description}</strong>
                            <div class="transaction-date">
                                Order #${transaction.order_id} • ${new Date(transaction.created_at).toLocaleDateString()}
                            </div>
                        </div>
                        <div class="transaction-amount ${transaction.amount > 0 ? 'transaction-positive' : 'transaction-negative'}">
                            ${transaction.amount > 0 ? '+' : ''}${formatCurrency(transaction.amount)}
                        </div>
                    </div>
                `).join('');
                        document.getElementById('recent-transactions').innerHTML = transactionsHtml;
                    } else {
                        document.getElementById('recent-transactions').innerHTML = '<div class="no-orders">No transactions yet</div>';
                    }
                }
            } catch (err) {
                console.error('Financial overview error:', err);
                document.getElementById('recent-transactions').innerHTML = '<div class="no-orders error">Error loading financial data</div>';
            }
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffTime = Math.abs(now - date);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays === 1) {
                return 'Today at ' + date.toLocaleTimeString();
            } else if (diffDays === 2) {
                return 'Yesterday at ' + date.toLocaleTimeString();
            } else {
                return date.toLocaleDateString() + ' at ' + date.toLocaleTimeString();
            }
        }

        function resetButton(btn, text, bg) {
            btn.disabled = false;
            btn.textContent = text;
            btn.style.background = bg;
        }

        // --- UPDATED: makeDeposit with Full Amount Logic ---
        async function makeDeposit() {
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = 'image/*,.pdf';
            fileInput.style.display = 'none';
            document.body.appendChild(fileInput);

            fileInput.click();

            fileInput.onchange = async function() {
                if (!fileInput.files.length) return;

                const file = fileInput.files[0];
                const depositBtn = document.querySelector('.btn-primary[onclick*="makeDeposit"]');

                // Validate file
                const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
                const maxSize = 5 * 1024 * 1024;

                if (!allowedTypes.includes(file.type)) {
                    showNotification(' Please upload only JPG, PNG, or PDF files.', 'error');
                    document.body.removeChild(fileInput);
                    return;
                }

                if (file.size > maxSize) {
                    showNotification(' File size too large. Maximum 5MB allowed.', 'error');
                    document.body.removeChild(fileInput);
                    return;
                }

                depositBtn.disabled = true;
                depositBtn.innerHTML = ' Uploading Slip...';
                depositBtn.style.opacity = '0.6';

                try {
                    // Get COD orders to calculate total FULL deposit amount
                    const data = await apiCall('../../api/get_cod_orders.php');
                    if (!data.success || !data.total_cod || data.total_cod == 0) {
                        alert('No COD amount to deposit');
                        resetDepositButton();
                        document.body.removeChild(fileInput);
                        return;
                    }

                    // Calculate FULL amount (order total including delivery fee)
                    const fullAmount = data.total_cod;

                    if (!confirm(`Upload transaction slip for FULL deposit amount?\nAmount: ${formatCurrency(fullAmount)}\nFile: ${file.name}\nSize: ${(file.size / 1024 / 1024).toFixed(2)}MB`)) {
                        document.body.removeChild(fileInput);
                        return;
                    }

                    const formData = new FormData();
                    formData.append('transaction_slip', file);
                    formData.append('amount', fullAmount); // Send the FULL amount

                    const depositData = await apiCall('../../api/make_deposit.php', {
                        method: 'POST',
                        body: formData
                    });

                    if (depositData.success) {
                        depositBtn.innerHTML = 'Full Deposit Submitted';
                        depositBtn.style.background = '#28a745';
                        depositBtn.style.opacity = '0.8';
                        depositBtn.disabled = true;
                        depositBtn.onclick = null;

                        showNotification(`Full deposit of ${formatCurrency(fullAmount)} submitted! Awaiting admin verification.`, 'success');

                        await loadCODOrders();
                        await loadFinancialOverview();
                        await loadEarnings();

                    } else {
                        showNotification(' Deposit failed: ' + depositData.message, 'error');
                        resetDepositButton();
                    }
                } catch (err) {
                    console.error('Deposit error:', err);
                    showNotification(' Error making deposit: ' + err.message, 'error');
                    resetDepositButton();
                } finally {
                    document.body.removeChild(fileInput);
                }
            };
        }

        function resetDepositButton() {
            const depositBtn = document.querySelector('.btn-primary[onclick*="makeDeposit"]');
            depositBtn.disabled = false;
            depositBtn.innerHTML = '💳 Make Full Deposit';
            depositBtn.style.background = '#007bff';
            depositBtn.style.opacity = '1';
            depositBtn.onclick = makeDeposit;
        }

        //view Transaction Slip ---
        function viewTransactionSlip(slipFilename) {
            if (!slipFilename) {
                showNotification('❌ No transaction slip available', 'error');
                return;
            }

            const slipUrl = `../../assets/transaction_slips/${slipFilename}`;
            window.open(slipUrl, '_blank');
        }

        // --- CORRECTED: Resubmit Deposit ---
        async function resubmitDeposit(depositId) {
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = 'image/*,.pdf';
            fileInput.style.display = 'none';
            document.body.appendChild(fileInput);

            fileInput.click();

            fileInput.onchange = async function() {
                if (!fileInput.files.length) return;

                const file = fileInput.files[0];
                const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
                const maxSize = 5 * 1024 * 1024;

                if (!allowedTypes.includes(file.type) || file.size > maxSize) {
                    showNotification('❌ Invalid file. Please upload JPG, PNG, or PDF under 5MB.', 'error');
                    document.body.removeChild(fileInput);
                    return;
                }

                if (!confirm(`Resubmit deposit with this slip?\nFile: ${file.name}`)) {
                    document.body.removeChild(fileInput);
                    return;
                }

                try {
                    const formData = new FormData();
                    formData.append('transaction_slip', file);
                    formData.append('deposit_id', depositId);

                    const response = await apiCall('../../api/resubmit_deposit.php', {
                        method: 'POST',
                        body: formData
                    });

                    if (response.success) {
                        showNotification('✅ Deposit resubmitted successfully!', 'success');
                        loadCODOrders();
                    } else {
                        showNotification('❌ Resubmission failed: ' + response.message, 'error');
                    }
                } catch (err) {
                    showNotification('❌ Error: ' + err.message, 'error');
                } finally {
                    document.body.removeChild(fileInput);
                }
            };
        }

        // --- Earnings ---
        async function loadEarnings() {
            try {
                const data = await apiCall('../../api/get_rider_earnings.php');

                if (data.success) {
                    document.getElementById('today-earnings').textContent = formatCurrency(data.today_earnings || 0);
                    document.getElementById('week-earnings').textContent = formatCurrency(data.week_earnings || 0);
                    document.getElementById('total-payouts').textContent = formatCurrency(data.week_payout || 0);

                    if (data.today_orders && data.today_orders.length > 0) {
                        const ordersHtml = data.today_orders.map(order => `
                    <div class="order-details" style="margin-bottom: 15px; padding: 15px; border: 1px solid #e0e0e0; border-radius: 8px;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                            <div>
                                <h4 style="margin: 0 0 5px 0;">📦 Order #${order.order_id}</h4>
                                <div style="font-size: 14px; color: #666;">
                                    <strong>${order.restaurant_name || 'Unknown Restaurant'}</strong><br>
                                    <small>${formatDeliveryTime(order.delivered_at)}</small>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 16px; font-weight: bold; color: #28a745;">
                                    ${formatCurrency(order.delivery_fee || 0)}
                                </div>
                                <small style="color: #666;">Delivery fee earned</small>
                            </div>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px; border-top: 1px solid #f0f0f0; padding-top: 8px;">
                            <span>Order Total Collected:</span>
                            <span><strong>${formatCurrency(order.total_amount || 0)}</strong></span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 14px; color: #666;">
                            <span>Amount deposited to admin:</span>
                            <span><strong>${formatCurrency(order.total_amount || 0)}</strong></span>
                        </div>
                    </div>
                `).join('');
                        document.getElementById('today-orders-list').innerHTML = ordersHtml;
                    } else {
                        document.getElementById('today-orders-list').innerHTML = `
                    <div class="no-orders" style="text-align: center; padding: 40px 20px;">
                        <p style="margin: 0 0 10px 0; font-size: 16px; color: #666;">No orders delivered today</p>
                        <small>Your earnings from today's deliveries will appear here</small>
                    </div>`;
                    }
                }
            } catch (err) {
                console.error('Earnings error:', err);
                document.getElementById('today-orders-list').innerHTML = `
            <div class="no-orders error" style="text-align: center; padding: 20px;">
                <p>❌ Error loading earnings data</p>
                <small>${err.message}</small>
                <br>
                <button class="btn btn-retry" onclick="loadEarnings()" style="margin-top: 10px;">
                    🔄 Retry
                </button>
            </div>`;
            }
        }

        function formatDeliveryTime(dateString) {
            if (!dateString) return 'Time not available';
            try {
                const date = new Date(dateString);
                return date.toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            } catch (e) {
                return 'Time error';
            }
        }

        function formatCurrency(amount) {
            return 'MMK ' + parseFloat(amount).toLocaleString();
        }

        // --- Map & Location Tracking (Keep existing) ---
        const map = L.map('map').setView([16.8409, 96.1735], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        let watchId = null;
        let activeOrders = [];
        let meMarker = L.marker(map.getCenter(), {
            title: 'You'
        }).addTo(map).bindPopup('Your Location');
        let restaurantMarkers = [];

        const newOrdersList = document.getElementById('new-orders-list');
        const activeDeliveriesList = document.getElementById('active-deliveries-list');
        const startBtn = document.getElementById('start-tracking');
        const stopBtn = document.getElementById('stop-tracking');
        const statusEl = document.getElementById('tracking-status');

        let timeoutCheckIntervals = new Map();

        // --- API Call Function (Keep existing) ---
        async function apiCall(url, options = {}) {
            try {
                console.log(`🔗 API Call: ${url}`);

                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 10000);

                const response = await fetch(url, {
                    credentials: 'include',
                    ...options,
                    signal: controller.signal
                });

                clearTimeout(timeoutId);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                console.log(`✅ API Success: ${url}`, data);
                return data;

            } catch (error) {
                console.error(`❌ API Error: ${url}`, error);

                if (error.name === 'AbortError') {
                    throw new Error('Request timeout - please check your internet connection');
                } else if (error.name === 'TypeError') {
                    throw new Error('Network error - cannot connect to server');
                } else {
                    throw error;
                }
            }
        }

        // --- Order Management Functions (Keep existing) ---
        async function fetchNewOrders() {
            try {
                const data = await apiCall('../../api/get_unassigned_orders.php');

                if (!data.success) {
                    newOrdersList.innerHTML = `<div class='no-orders error'><p>❌ Error loading orders</p><small>${data.message || 'Please try again'}</small><br><button class="btn btn-retry" onclick="fetchNewOrders()">🔄 Retry</button></div>`;
                    return;
                }

                const orders = data.orders || [];
                if (orders.length === 0) {
                    newOrdersList.innerHTML = `<div class='no-orders'><p>No new orders available</p><small>New orders will appear here when available</small></div>`;
                    return;
                }

                newOrdersList.innerHTML = `
            <div style="margin-bottom: 10px; font-size: 12px; color: #666;">
                🟢 ${orders.length} orders available - Last update: ${new Date().toLocaleTimeString()}
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Restaurant</th>
                        <th>Address</th>
                        <th>Amount</th>
                        <th class="actions">Action</th>
                    </tr>
                </thead>
                <tbody>
                    ${orders.map(o => `
                        <tr id="order-row-${o.order_id}">
                            <td><strong>#${o.order_id}</strong></td>
                            <td>${o.restaurant_name || 'Unknown'}</td>
                            <td>${(o.delivery_address || '').substring(0, 25)}...</td>
                            <td><strong>${formatCurrency(o.total_amount || 0)}</strong></td>
                            <td class="actions">
                                <button class="btn btn-primary" onclick="acceptOrder(${o.order_id}, this)">
                                    Accept
                                </button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>`;

            } catch (err) {
                console.error('Fetch new orders error:', err);
                newOrdersList.innerHTML = `<div class='no-orders error'><p>❌ Network Error</p><small>${err.message}</small><br><button class="btn btn-retry" onclick="fetchNewOrders()">🔄 Retry</button></div>`;
            }
        }

        async function acceptOrder(orderId, btn) {
            if (!confirm(`Accept order #${orderId}? You have 15 minutes to pick it up.`)) return;

            const originalText = btn.textContent;
            const originalBg = btn.style.background;

            btn.disabled = true;
            btn.textContent = 'Accepting...';
            btn.style.background = '#6c757d';

            try {
                const formData = new FormData();
                formData.append('order_id', orderId);

                const data = await apiCall('../../api/accept_order.php', {
                    method: 'POST',
                    body: formData
                });

                if (data.success) {
                    // Remove from new orders
                    const row = document.getElementById('order-row-' + orderId);
                    if (row) row.remove();

                    // Start timeout monitoring for this order
                    startTimeoutMonitoring(orderId);

                    // Refresh active deliveries
                    await fetchActiveDeliveries();

                    // Show success message with timeout info
                    showNotification(`✅ Order #${orderId} accepted! You have 15 minutes to pick it up.`, 'success');
                } else {
                    let errorMsg = data.message || data.error || 'Unknown error';
                    if (errorMsg.includes('already_assigned')) {
                        errorMsg = 'This order was just accepted by another rider.';
                        // Refresh to show updated list
                        await fetchNewOrders();
                    }
                    showNotification(`❌ Failed: ${errorMsg}`, 'error');
                    resetButton(btn, originalText, originalBg);
                }
            } catch (err) {
                console.error('Accept order error:', err);
                showNotification(`❌ Network error: ${err.message}`, 'error');
                resetButton(btn, originalText, originalBg);
            }
        }

        function startTimeoutMonitoring(orderId) {
            // Clear any existing interval for this order
            if (timeoutCheckIntervals.has(orderId)) {
                clearInterval(timeoutCheckIntervals.get(orderId));
            }

            // Check every 30 seconds if order is still assigned to this rider
            const intervalId = setInterval(async () => {
                try {
                    const data = await apiCall(`../../api/get_order_status.php?order_id=${orderId}`);

                    if (data.success && data.order) {
                        const order = data.order;
                        const minutesAssigned = order.minutes_since_assigned || 0;
                        const currentRiderId = <?= $delivery_boy_id ?>;

                        console.log(`Monitoring order #${orderId}: ${minutesAssigned} mins, rider: ${order.delivery_boy_id}, current: ${currentRiderId}`);

                        // If order is no longer assigned to current rider, refresh everything
                        if (!order.delivery_boy_id || order.delivery_boy_id !== currentRiderId) {
                            console.log(`🚨 Order #${orderId} was reassigned!`);
                            clearInterval(timeoutCheckIntervals.get(orderId));
                            timeoutCheckIntervals.delete(orderId);

                            showNotification(`Order #${orderId} has been reassigned to another rider.`, 'warning');
                            await Promise.all([fetchActiveDeliveries(), fetchNewOrders()]);
                            return;
                        }

                        // Show timeout warnings
                        if (minutesAssigned >= 12 && minutesAssigned < 15) {
                            showRiderTimeoutWarning(orderId, 15 - minutesAssigned);
                        } else if (minutesAssigned >= 15) {
                            // Order timed out - force refresh
                            console.log(`⏰ Order #${orderId} timed out!`);
                            clearInterval(timeoutCheckIntervals.get(orderId));
                            timeoutCheckIntervals.delete(orderId);

                            // Trigger server-side timeout monitor immediately to unassign and re-list
                            try {
                                await fetch('../../api/timeout_monitor.php', {
                                    credentials: 'include'
                                });
                            } catch (e) {
                                // ignore
                            }
                            showNotification(`Order #${orderId} has been reassigned due to timeout.`, 'error');
                            await Promise.all([fetchActiveDeliveries(), fetchNewOrders()]);
                        }
                    }
                } catch (err) {
                    console.error('Timeout monitoring error for order #' + orderId + ':', err);
                }
            }, 30000); // Check every 30 seconds

            timeoutCheckIntervals.set(orderId, intervalId);
        }

        function showRiderTimeoutWarning(orderId, minutesLeft) {
            const orderElement = document.getElementById(`active-order-${orderId}`);
            if (orderElement) {
                let warningDiv = orderElement.querySelector('.rider-timeout-warning');
                if (!warningDiv) {
                    warningDiv = document.createElement('div');
                    warningDiv.className = 'rider-timeout-warning';
                    const actionsDiv = orderElement.querySelector('.actions');
                    if (actionsDiv) {
                        orderElement.insertBefore(warningDiv, actionsDiv);
                    }
                }
                warningDiv.innerHTML = `⏰ URGENT: Pick up within ${minutesLeft} minutes or order will be reassigned!`;
            }
        }

        function showNotification(message, type = 'info') {
            // Play notification sound for all notifications
            playNotificationSound();

            document.querySelectorAll('.notification').forEach(n => n.remove());

            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = message;
            notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        font-weight: bold;
        z-index: 10000;
        max-width: 300px;
        animation: slideIn 0.3s ease;
    `;

            if (type === 'warning') {
                notification.style.background = '#ffc107';
                notification.style.color = '#856404';
            } else if (type === 'error') {
                notification.style.background = '#dc3545';
            } else {
                notification.style.background = '#28a745';
            }

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 5000);
        }
        async function fetchActiveDeliveries() {
            try {
                const data = await apiCall('../../api/get_assigned_deliveries.php');

                if (data.success && data.deliveries && data.deliveries.length > 0) {
                    activeOrders = data.deliveries;

                    timeoutCheckIntervals.forEach((intervalId, orderId) => {
                        clearInterval(intervalId);
                    });
                    timeoutCheckIntervals.clear();

                    activeOrders.forEach(order => {
                        if (order.delivery_status === 'assigned') {
                            startTimeoutMonitoring(order.order_id);
                        }
                    });

                    renderActiveDeliveries();
                    updateMapWithDeliveries();
                } else {
                    activeOrders = [];
                    activeDeliveriesList.innerHTML = `
                <div class="no-orders">
                    <p> No active deliveries assigned to you</p>
                    <p><small>Accept orders from the "New Orders" section to get started</small></p>
                </div>`;
                    clearRestaurantMarkers();

                    timeoutCheckIntervals.forEach((intervalId, orderId) => {
                        clearInterval(intervalId);
                    });
                    timeoutCheckIntervals.clear();
                }
            } catch (err) {
                console.error('Fetch active deliveries error:', err);
                activeDeliveriesList.innerHTML = `
            <div class='no-orders error'>
                <p>❌ Network Error</p>
                <small>${err.message}</small>
                <br>
                <button class="btn btn-retry" onclick="fetchActiveDeliveries()">🔄 Retry</button>
            </div>`;
            }
        }

        function renderActiveDeliveries() {
            if (activeOrders.length === 0) {
                activeDeliveriesList.innerHTML = "<div class='no-orders'>No active deliveries</div>";
                return;
            }

            activeDeliveriesList.innerHTML = activeOrders.map(order => {
                const minutesAssigned = order.minutes_since_assigned || 0;
                const timeLeft = Math.max(0, 15 - minutesAssigned);
                const isTimeoutWarning = minutesAssigned >= 12;

                return `
        <div class="order-details" id="active-order-${order.order_id}">
            <h4> Order #${order.order_id} </h4>
            
            ${isTimeoutWarning ? `
                <div class="rider-timeout-warning">
                    ⏰ URGENT: Pick up within ${timeLeft} minutes or order will be reassigned!
                </div>
            ` : ''}
            
           <div class="delivery-info">
                <strong>Restaurant Address:</strong>
                    ${order.restaurant_name || 'No address provided'}
            </div>
            <div class="delivery-info" style="margin-top:8px;">
                <strong>  Customer Address :</strong>
                ${order.delivery_address || 'No address provided'}
            </div>
            <div class="delivery-info" style="margin-top:8px;">
                <strong>Customer:</strong>
                ${order.customer_name ? order.customer_name : 'Unknown'}${order.customer_phone ? ` — <span href="tel:${order.customer_phone}" style="text-decoration:none;">${order.customer_phone}</span>` : ''}
                </div>
            
            <div class="order-items">
                <strong>Order Items:</strong>
                ${order.items && order.items.length > 0 ? order.items.map(item => `
                    <div class="order-item">
                        <span>${item.quantity}x ${item.name}</span>
                        <span>${formatCurrency(item.price * item.quantity)}</span>
                    </div>
                `).join('') : `
                    <div class="order-item">
                        <span>No item details available</span>
                        <span>${formatCurrency(order.total_amount || 0)}</span>
                    </div>
                `}
                <div class="order-total">
                    <span>Total Amount:</span>
                    <span>${formatCurrency(order.total_amount || 0)}</span>
                </div>
            </div>
            
            <div style="margin-top: 15px;">
                <strong>Delivery Status:</strong> 
                <span class="status-${order.delivery_status || 'pending'}">
                    ${formatDeliveryStatus(order.delivery_status)}
                </span>
            </div>
            
            <div style="margin-top: 5px;">
                <strong>Order Status:</strong> 
                <span class="status-${order.order_status || 'pending'}">
                    ${formatOrderStatus(order.order_status)}
                </span>
            </div>
            
            ${order.assigned_time ? `
                <div style="margin-top: 5px; font-size: 12px; color: #666;">
                    <strong>Assigned:</strong> ${new Date(order.assigned_time).toLocaleTimeString()} 
                    (${minutesAssigned} mins ago - ${timeLeft} mins left)
                </div>
            ` : ''}
            
            <!-- ENHANCED BUTTON LOGIC -->
            <div class="actions" style="margin-top: 15px; text-align: center;">
                ${order.delivery_status === 'assigned' && order.order_status === 'ready' ? `
                    <button class="btn btn-success" onclick="updateOrderStatus(${order.order_id}, 'picked')">
                         Pick Up Order
                    </button>
                    <button class="btn btn-primary" disabled title="Pick up first to enable delivery">
                        Mark as Delivered
                    </button>
                    <button class="btn btn-info" onclick="startLocationTracking(${order.order_id})">
                         Start Delivery & Tracking
                    </button>
                    <div style="margin-top: 10px; font-size: 12px; color: #28a745;">
                         Restaurant has marked this order as READY for pickup
                    </div>
                ` : ''}
                
                ${order.delivery_status === 'assigned' && order.order_status !== 'ready' ? `
                    <button class="btn btn-warning" disabled>
                         Waiting for Restaurant (Status: ${order.order_status})
                    </button>
                    <div style="margin-top: 10px; font-size: 12px; color: #ffc107;">
                         Restaurant is preparing your order...
                    </div>
                ` : ''}
                
                ${(order.delivery_status === 'pick_up' || order.delivery_status === 'picked') ? `
                    <span class="btn" style="background:#17a2b8;color:white;cursor:default;">
                         Picked
                    </span>
                    <button class="btn btn-primary" onclick="updateOrderStatus(${order.order_id}, 'delivered')">
                         Mark as Delivered
                    </button>
                    <button class="btn btn-info" onclick="startLocationTracking(${order.order_id})">
                         Continue Tracking
                    </button>
                    <div style="margin-top: 10px; font-size: 12px; color: #17a2b8;">
                         Order picked up - on the way to customer
                    </div>
                   <div class="chat-wrapper" id="chat-wrapper-${order.order_id}">
    <div class="chat-header" onclick="toggleChat(${order.order_id})">
        💬 Chat with your customer
        <span id="chat-arrow-${order.order_id}" class="chat-arrow">▼</span>
    </div>
    <div class="chat-panel" id="chat-panel-${order.order_id}" onclick="event.stopPropagation()">
        <div class="chat-messages" id="chat-messages-${order.order_id}"></div>
        <div class="chat-input-area">
            <input type="text" 
                   id="chat-text-${order.order_id}" 
                   placeholder="Type message..." 
                   oninput="saveDraft(${order.order_id})"
                   onkeypress="if(event.key === 'Enter') sendChat(${order.order_id})">
            <button onclick="event.stopPropagation(); sendChat(${order.order_id})">Send</button>
        </div>
    </div>
</div>

                
                ` : ''}
                
                ${order.delivery_status === 'delivered' ? `
                    <span class="btn btn-success" style="background: #28a745; color: white;">
                        Delivery Completed
                    </span>
                    <div style="margin-top: 10px; font-size: 12px; color: #28a745;">
                         Order successfully delivered to customer!
                    </div>
                ` : ''}
            </div>
        </div>
        `;
            }).join('');
        }

        function formatDeliveryStatus(status) {
            const statusMap = {
                'assigned': ' Assigned to You',
                'picked': ' Picked Up',
                'on_the_way': ' On the Way',
                'delivered': ' Delivered',
                'ready': ' Ready for Pickup'
            };
            return statusMap[status] || status || 'Pending';
        }

        function formatOrderStatus(status) {
            const statusMap = {
                'pending': ' Pending',
                'preparing': ' Preparing',
                'accepted': ' Accepted',
                'on_the_way': ' On the Way',
                'delivered': ' Delivered',
                'ready': ' Ready'
            };
            return statusMap[status] || status || 'Pending';
        }

        async function updateOrderStatus(orderId, status) {
            const statusText = status === 'picked' ? 'Picked Up' : 'Delivered';
            if (!confirm(`Mark order #${orderId} as ${statusText}?`)) return;

            try {
                const formData = new FormData();
                formData.append('order_id', orderId);
                formData.append('status', status);

                const data = await apiCall('../../api/update_order_status.php', {
                    method: 'POST',
                    body: formData
                });

                if (data.success) {
                    if (status === 'picked') {
                        if (timeoutCheckIntervals.has(orderId)) {
                            clearInterval(timeoutCheckIntervals.get(orderId));
                            timeoutCheckIntervals.delete(orderId);
                        }
                    }

                    showNotification(`Order #${orderId} marked as ${statusText}`, 'success');
                    await fetchActiveDeliveries();

                    // Refresh financial tabs if they're open
                    if (status === 'delivered') {
                        if (document.getElementById('financial-tab').classList.contains('active')) {
                            loadFinancialOverview();
                        }
                        if (document.getElementById('cod-tab').classList.contains('active')) {
                            loadCODOrders();
                        }
                    }
                } else {
                    showNotification(` Failed: ${data.message || 'Unknown error'}`, 'error');
                }
            } catch (err) {
                console.error('Update status error:', err);
                showNotification(` Network error: ${err.message}`, 'error');
            }
        }

        async function processOrderFinancials(orderId) {
            try {
                const response = await apiCall('../../api/process_order_financials.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        order_id: orderId
                    })
                });

                if (response.success) {
                    if (document.getElementById('financial-tab').classList.contains('active')) {
                        loadFinancialOverview();
                    }
                    if (document.getElementById('cod-tab').classList.contains('active')) {
                        loadCODOrders();
                    }
                    if (document.getElementById('earnings-tab').classList.contains('active')) {
                        loadEarnings();
                    }
                }
            } catch (err) {
                console.error('Financial processing error:', err);
            }
        }

        function startLocationTracking(orderId) {
            const order = activeOrders.find(o => o.order_id == orderId);
            if (!order) {
                showNotification("❌ Order not found in active deliveries.", 'error');
                return;
            }

            if (navigator.geolocation) {
                if (watchId) {
                    navigator.geolocation.clearWatch(watchId);
                }

                // Store the current orderId for location tracking
                currentTrackingOrderId = orderId;

                watchId = navigator.geolocation.watchPosition(
                    (pos) => sendLocation(pos, orderId),
                    handleError, {
                        enableHighAccuracy: true,
                        maximumAge: 2000,
                        timeout: 8000
                    }
                );

                startBtn.disabled = true;
                stopBtn.disabled = false;
                statusEl.textContent = `📍 Tracking order #${orderId}...`;
                showNotification(` Started location tracking for order #${orderId}`, 'success');

                if (order.delivery_status === 'assigned') {
                    updateOrderStatus(orderId, 'picked');
                }
            } else {
                showNotification("❌ Geolocation not supported by your browser.", 'error');
            }
        }

        async function sendLocation(position, orderId) {
            const { latitude, longitude } = position.coords;

            meMarker.setLatLng([latitude, longitude]);
            meMarker.bindPopup(`Your location for order #${orderId}`).openPopup();
            map.setView([latitude, longitude], 15);

            try {
                // Make sure order_id is always included
                const locationData = {
                    lat: latitude,
                    lng: longitude
                };

                // Only include order_id if we have one
                if (orderId && orderId > 0) {
                    locationData.order_id = orderId;
                }

                await apiCall('../../api/update_location.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(locationData)
                });

                statusEl.textContent = `📍 Tracking order #${orderId} (last update: ${new Date().toLocaleTimeString()})`;
            } catch (err) {
                console.error('Location send error:', err);
                statusEl.textContent = '❌ Error sending location: ' + err.message;
            }
        }

        function handleError(error) {
            console.warn('Geolocation Error:', error);
            let message = 'Unknown geolocation error';
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    message = 'Location access denied by user';
                    break;
                case error.POSITION_UNAVAILABLE:
                    message = 'Location information unavailable';
                    break;
                case error.TIMEOUT:
                    message = 'Location request timed out';
                    break;
            }
            statusEl.textContent = '❌ Location Error: ' + message;
        }

        stopBtn.addEventListener('click', () => {
            if (watchId) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
            }
            startBtn.disabled = false;
            stopBtn.disabled = true;
            statusEl.textContent = '📍 Location tracking stopped';
            showNotification('🛑 Location tracking stopped', 'warning');
        });

        function updateMapWithDeliveries() {
            restaurantMarkers.forEach(marker => map.removeLayer(marker));
            restaurantMarkers = [];

            activeOrders.forEach(order => {
                if (order.restaurant_lat && order.restaurant_lng) {
                    const restaurantMarker = L.marker([order.restaurant_lat, order.restaurant_lng])
                        .addTo(map)
                        .bindPopup(`
                    <strong>${order.restaurant_name}</strong><br>
                    Order #${order.order_id}<br>
                    Status: ${formatDeliveryStatus(order.delivery_status)}<br>
                    ${order.delivery_address || ''}
                `);
                    restaurantMarkers.push(restaurantMarker);
                }
                if (order.lat && order.lng) { // This uses o.lat, o.lng from your query
                    const customerMarker = L.marker([order.lat, order.lng])
                        .addTo(map)
                        .bindPopup(`
                    <strong>Customer: ${order.customer_name || 'Unknown'}</strong><br>
                    Order #${order.order_id}<br>
                     Delivery Location<br>
                    ${order.delivery_address || ''}<br>
                    Status: ${formatDeliveryStatus(order.delivery_status)}
                `);
                    restaurantMarkers.push(customerMarker);
                }
            });

            if (restaurantMarkers.length > 0) {
                const group = new L.featureGroup(restaurantMarkers);
                map.fitBounds(group.getBounds().pad(0.1));
            }
        }

        function clearRestaurantMarkers() {
            restaurantMarkers.forEach(marker => map.removeLayer(marker));
            restaurantMarkers = [];
        }

        // --- Initialize ---
        fetchNewOrders();
        fetchActiveDeliveries();

        setInterval(() => {
            if (document.getElementById('financial-tab').classList.contains('active')) {
                loadFinancialOverview();
            }
            if (document.getElementById('cod-tab').classList.contains('active')) {
                loadCODOrders();
            }
            if (document.getElementById('earnings-tab').classList.contains('active')) {
                loadEarnings();
            }
        }, 20000);

        setInterval(fetchNewOrders, 15000);
        setInterval(fetchActiveDeliveries, 15000);

        setInterval(async () => {
            try {
                await fetch('../../api/timeout_monitor.php');
            } catch (e) {
                // Silent fail
            }
        }, 120000);

        window.addEventListener('beforeunload', () => {
            timeoutCheckIntervals.forEach((intervalId, orderId) => {
                clearInterval(intervalId);
            });
        });

        // Deposit Tab Management
        function switchDepositTab(tabName) {
            // Hide all deposit tab contents
            document.querySelectorAll('.deposit-tab-content').forEach(tab => {
                tab.classList.remove('active');
            });

            // Remove active class from all deposit tabs
            document.querySelectorAll('.tabs-container .tab').forEach(tab => {
                tab.classList.remove('active');
            });

            // Show selected tab content and activate tab button
            document.getElementById(tabName + '-deposits-content').classList.add('active');
            event.target.classList.add('active');

            // Load specific data if needed
            if (tabName === 'pending') {
                loadPendingDeposits();
            } else if (tabName === 'approved') {
                loadApprovedDeposits();
            } else if (tabName === 'rejected') {
                loadRejectedDeposits();
            }
        }

        // Enhanced loadCODOrders function
        async function loadCODOrders() {
            try {
                const data = await apiCall('../../api/get_cod_orders.php');

                if (data.success) {
                    // Update summary cards
                    document.getElementById('total-cod-due').textContent = formatCurrency(data.total_cod || 0);

                    // Load deposit summaries
                    await loadDepositSummary();

                    // Load all sections
                    await loadPendingDeposits();
                    await loadApprovedDeposits();
                    await loadRejectedDeposits();
                    await loadCODOrdersList();
                }
            } catch (err) {
                console.error('COD orders error:', err);
                showNotification('❌ Error loading COD data: ' + err.message, 'error');
            }
        }

        // Load Deposit Summary
        async function loadDepositSummary() {
            try {
                const data = await apiCall('../../api/get_deposit_summary.php');
                if (data.success) {
                    document.getElementById('pending-approval').textContent = formatCurrency(data.pending_amount || 0);
                    document.getElementById('approved-deposits').textContent = formatCurrency(data.approved_amount || 0);
                    document.getElementById('rejected-deposits').textContent = formatCurrency(data.rejected_amount || 0);
                }
            } catch (err) {
                console.error('Deposit summary error:', err);
            }
        }

        // Load Pending Deposits
        async function loadPendingDeposits() {
            try {
                const data = await apiCall('../../api/get_deposits.php?status=pending');

                if (data.success && data.deposits && data.deposits.length > 0) {
                    const html = data.deposits.map(deposit => `
                <div class="deposit-item">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <h4 style="margin: 0 0 8px 0;">Deposit #${deposit.id}</h4>
                            <div style="display: grid; grid-template-columns: auto auto; gap: 10px; font-size: 13px;">
                                <div><strong>Amount:</strong> ${formatCurrency(deposit.amount)}</div>
                                <div><strong>Order:</strong> #${deposit.order_id}</div>
                                <div><strong>Submitted:</strong> ${formatDate(deposit.deposited_at)}</div>
                                <div><strong>Status:</strong> <span class="deposit-status status-pending">⏳ Pending</span></div>
                            </div>
                            ${deposit.transaction_slip ? `
                                <div style="margin-top: 10px;">
                                    <button class="btn btn-info btn-sm" onclick="viewTransactionSlip('${deposit.transaction_slip}')">
                                         View Transaction Slip
                                    </button>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                    <div class="deposit-actions">
                        <small style="color: #666;">
                            <strong>Note:</strong> Waiting for admin approval. You'll be notified once verified.
                        </small>
                    </div>
                </div>
            `).join('');
                    document.getElementById('pending-deposits-list').innerHTML = html;
                } else {
                    document.getElementById('pending-deposits-list').innerHTML = `
                <div class="no-orders">
                    <p>No pending deposits</p>
                    <small>All deposits have been processed</small>
                </div>`;
                }
            } catch (err) {
                document.getElementById('pending-deposits-list').innerHTML = `
            <div class="no-orders error">
                <p>❌ Error loading pending deposits</p>
                <small>${err.message}</small>
            </div>`;
            }
        }

        // Load Approved Deposits
        async function loadApprovedDeposits() {
            try {
                const data = await apiCall('../../api/get_deposits.php?status=approved');

                if (data.success && data.deposits && data.deposits.length > 0) {
                    const html = data.deposits.map(deposit => `
                <div class="deposit-item approved">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <h4 style="margin: 0 0 8px 0; color: #155724;">Approved Deposit #${deposit.id}</h4>
                            <div style="display: grid; grid-template-columns: auto auto; gap: 10px; font-size: 13px;">
                                <div><strong>Amount:</strong> ${formatCurrency(deposit.amount)}</div>
                                <div><strong>Order:</strong> #${deposit.order_id}</div>
                                <div><strong>Submitted:</strong> ${formatDate(deposit.deposited_at)}</div>
                                <div><strong>Approved:</strong> ${formatDate(deposit.verified_at)}</div>
                                <div><strong>Status:</strong> <span class="deposit-status status-approved">Approved</span></div>
                            </div>
                            ${deposit.transaction_slip ? `
                                <div style="margin-top: 10px;">
                                    <button class="btn btn-info btn-sm" onclick="viewTransactionSlip('${deposit.transaction_slip}')">
                                         View Transaction Slip
                                    </button>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                    <div class="deposit-actions">
                        <small style="color: #28a745;">
                            <strong>✓ Verified by Admin:</strong> This deposit has been approved and credited to your account.
                        </small>
                    </div>
                </div>
            `).join('');
                    document.getElementById('approved-deposits-list').innerHTML = html;
                } else {
                    document.getElementById('approved-deposits-list').innerHTML = `
                <div class="no-orders">
                    <p>No approved deposits</p>
                    <small>Approved deposits will appear here</small>
                </div>`;
                }
            } catch (err) {
                document.getElementById('approved-deposits-list').innerHTML = `
            <div class="no-orders error">
                <p>❌ Error loading approved deposits</p>
                <small>${err.message}</small>
            </div>`;
            }
        }

        // Load Rejected Deposits
        async function loadRejectedDeposits() {
            try {
                const data = await apiCall('../../api/get_deposits.php?status=rejected');

                if (data.success && data.deposits && data.deposits.length > 0) {
                    const html = data.deposits.map(deposit => `
                <div class="deposit-item rejected">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <h4 style="margin: 0 0 8px 0; "> Rejected Deposit #${deposit.id}</h4>
                            <div style="display: grid; grid-template-columns: auto auto; gap: 10px; font-size: 13px;">
                                <div><strong>Amount:</strong> ${formatCurrency(deposit.amount)}</div>
                                <div><strong>Order:</strong> #${deposit.order_id}</div>
                                <div><strong>Submitted:</strong> ${formatDate(deposit.deposited_at)}</div>
                                <div><strong>Rejected:</strong> ${formatDate(deposit.verified_at)}</div>
                                <div><strong>Status:</strong> <span class="deposit-status status-rejected"> Rejected</span></div>
                            </div>
                            ${deposit.rejection_reason ? `
                                <div style="background: #f8d7da; padding: 8px; border-radius: 4px; margin: 8px 0; font-size: 12px;">
                                    <strong>Rejection Reason:</strong> ${deposit.rejection_reason}
                                </div>
                            ` : ''}
                            ${deposit.transaction_slip ? `
                                <div style="margin-top: 10px;">
                                    <button class="btn btn-info btn-sm" onclick="viewTransactionSlip('${deposit.transaction_slip}')">
                                         View Transaction Slip
                                    </button>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                    <div class="deposit-actions">
                        <button class="btn btn-warning btn-sm" onclick="resubmitDeposit(${deposit.id})">
                             Resubmit with New Slip
                        </button>
                        <small style="color: #dc3545; margin-left: 10px;">
                            <strong>Action Required:</strong> Please resubmit with correct transaction slip
                        </small>
                    </div>
                </div>
            `).join('');
                    document.getElementById('rejected-deposits-list').innerHTML = html;
                } else {
                    document.getElementById('rejected-deposits-list').innerHTML = `
                <div class="no-orders">
                    <p>No rejected deposits</p>
                    <small>Rejected deposits will appear here if any</small>
                </div>`;
                }
            } catch (err) {
                document.getElementById('rejected-deposits-list').innerHTML = `
            <div class="no-orders error">
                <p>❌ Error loading rejected deposits</p>
                <small>${err.message}</small>
            </div>`;
            }
        }

        // Load COD Orders List with FULL amount breakdown
        async function loadCODOrdersList() {
            try {
                const data = await apiCall('../../api/get_cod_orders.php');

                if (data.success && data.orders && data.orders.length > 0) {
                    const ordersHtml = data.orders.map(order => {
                        const orderTotal = parseFloat(order.total_amount) || 0;
                        const deliveryFee = parseFloat(order.delivery_fee) || 0;
                        const productAmount = orderTotal - deliveryFee;

                        return `
                <div class="order-details">
                    <h4>Order #${order.order_id} - ${order.restaurant_name}</h4>
                    <div class="delivery-info">
                        <strong>Customer:</strong> ${order.customer_name}<br>
                        <strong>Address:</strong> ${order.delivery_address}<br>
                        <strong>Delivered:</strong> ${new Date(order.delivered_at).toLocaleString()}
                    </div>
                    <div class="order-items">
                        <div class="order-item">
                            <span>Product Amount:</span>
                            <span>${formatCurrency(productAmount)}</span>
                        </div>
                        <div class="order-item">
                            <span>Delivery Fee:</span>
                            <span>${formatCurrency(deliveryFee)}</span>
                        </div>
                        <div class="order-total" style="background: #f8f9fa; padding: 8px; border-radius: 4px;">
                            <span>FULL Amount to Deposit:</span>
                            <span><strong>${formatCurrency(orderTotal)}</strong></span>
                        </div>
                    </div>
                    ${order.deposit_status ? `
                        <div style="margin-top: 10px;">
                            <strong>Deposit Status:</strong> 
                            <span class="deposit-status status-${order.deposit_status}">
                                ${order.deposit_status === 'pending' ? ' Pending' : 
                                  order.deposit_status === 'approved' ? ' Approved' : 
                                  order.deposit_status === 'rejected' ? ' Rejected' : 'Not Deposited'}
                            </span>
                        </div>
                    ` : ''}
                </div>
            `
                    }).join('');
                    document.getElementById('cod-orders-list').innerHTML = ordersHtml;
                } else {
                    document.getElementById('cod-orders-list').innerHTML = `
                <div class="no-orders">
                    <p>No COD orders delivered today</p>
                    <small>COD orders will appear here after delivery</small>
                </div>`;
                }
            } catch (err) {
                document.getElementById('cod-orders-list').innerHTML = `
            <div class="no-orders error">
                <p>❌ Error loading COD orders</p>
                <small>${err.message}</small>
            </div>`;
            }
        }

        // --- Notification Sound Function ---
        function playNotificationSound() {
            try {
                const audio = new Audio('../../assets/sound/notisound.mp3');
                audio.volume = 0.7; 
                audio.play().catch(error => {
                    console.log('Audio play failed:', error);
                });
            } catch (error) {
                console.log('Notification sound error:', error);
            }
        }
    </script>
    <script>
        let riderChatSocket = null;
        let riderChatQueue = [];

        function initRiderChatSocket() {
            try {
                const scheme = location.protocol === 'https:' ? 'wss://' : 'ws://';
                const url = scheme + location.hostname + ':8080';
                riderChatSocket = new WebSocket(url);

                riderChatSocket.onopen = function() {
                    if (riderChatQueue.length) {
                        riderChatQueue.forEach(function(msg) {
                            riderChatSocket.send(JSON.stringify(msg));
                        });
                        riderChatQueue = [];
                    }
                };

                riderChatSocket.onmessage = function(event) {
                    let data;
                    try {
                        data = JSON.parse(event.data);
                    } catch (e) {
                        return;
                    }
                    if (!data || data.type !== 'chat' || !data.order_id) {
                        return;
                    }

                    const box = document.getElementById('chat-messages-' + data.order_id);
                    if (!box) {
                        return;
                    }

                    const wrapper = document.createElement('div');
                    wrapper.className = 'chat-message other';
                    const strong = document.createElement('strong');
                    strong.textContent = 'Customer: ';
                    const textNode = document.createTextNode(data.text || '');
                    wrapper.appendChild(strong);
                    wrapper.appendChild(textNode);
                    box.appendChild(wrapper);
                    box.scrollTop = box.scrollHeight;

                    const key = 'chat_order_' + data.order_id;
                    let history = [];
                    try {
                        history = JSON.parse(localStorage.getItem(key)) || [];
                    } catch (e) {
                        history = [];
                    }
                    history.push({
                        sender: 'customer',
                        text: data.text || '',
                        time: Date.now()
                    });
                    localStorage.setItem(key, JSON.stringify(history));
                };

                riderChatSocket.onclose = function() {
                    setTimeout(function() {
                        initRiderChatSocket();
                    }, 3000);
                };
                riderChatSocket.onerror = function() {};
            } catch (e) {}
        }

        function sendRiderChatOverSocket(orderId, txt) {
            const payload = {
                type: 'chat',
                order_id: orderId,
                from_role: 'rider',
                text: txt
            };
            if (riderChatSocket && riderChatSocket.readyState === WebSocket.OPEN) {
                riderChatSocket.send(JSON.stringify(payload));
            } else {
                riderChatQueue.push(payload);
                if (!riderChatSocket || riderChatSocket.readyState === WebSocket.CLOSED) {
                    initRiderChatSocket();
                }
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initRiderChatSocket);
        } else {
            initRiderChatSocket();
        }

        function toggleChat(orderId) {
            saveChatState(orderId);

            const panel = document.getElementById("chat-panel-" + orderId);
            const arrow = document.getElementById("chat-arrow-" + orderId);

            panel.classList.toggle("open");

            if (panel.classList.contains("open")) {
                arrow.style.transform = "rotate(180deg)";
                localStorage.setItem("chat_state_" + orderId, "open");
            } else {
                arrow.style.transform = "rotate(0deg)";
                localStorage.setItem("chat_state_" + orderId, "closed");
            }
        }

       function loadChat(orderId) {
    const key = "chat_order_" + orderId;
    const box = document.getElementById("chat-messages-" + orderId);
    if (!box) return;

    // Load chat history from localStorage
    const history = JSON.parse(localStorage.getItem(key) || "[]");
    box.innerHTML = "";

    history.forEach(msg => {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${msg.sender === 'you' ? 'you' : 'other'}`;
        messageDiv.innerHTML = `<strong>${msg.sender === 'you' ? 'You' : 'Customer'}:</strong> ${msg.text}`;
        box.appendChild(messageDiv);
    });

    box.scrollTop = box.scrollHeight;
}

   function sendChat(orderId) {
    const input = document.getElementById("chat-text-" + orderId);
    const box = document.getElementById("chat-messages-" + orderId);
    
    if (!input || !box) return;

    const message = input.value.trim();
    if (!message) return;

    // Save message to localStorage
    const key = "chat_order_" + orderId;
    const history = JSON.parse(localStorage.getItem(key) || "[]");
    
    const newMessage = {
        sender: "you",
        text: message,
        time: Date.now()
    };
    
    history.push(newMessage);
    localStorage.setItem(key, JSON.stringify(history));

    // Update UI
    const messageDiv = document.createElement('div');
    messageDiv.className = 'chat-message you';
    messageDiv.innerHTML = `<strong>You:</strong> ${message}`;
    box.appendChild(messageDiv);
    
    // Clear input and save draft
    input.value = "";
    localStorage.removeItem("chat_draft_" + orderId);
    box.scrollTop = box.scrollHeight;

    // Send via WebSocket
    if (window.sendRiderChatOverSocket) {
        sendRiderChatOverSocket(orderId, message);
    }
}

        document.addEventListener("click", function(e) {
            if (e.target.closest(".chat-panel") || e.target.closest(".chat-input-area")) {
                e.stopPropagation();
            }
        });

        function saveChatState(orderId) {
            const isOpen = document
                .getElementById("chat-panel-" + orderId)
                ?.classList.contains("open");

            localStorage.setItem("chat_state_" + orderId, isOpen ? "open" : "closed");
        }

        function saveDraft(orderId) {
            const text = document.getElementById("chat-text-" + orderId)?.value || "";
            localStorage.setItem("chat_draft_" + orderId, text);
        }
// Add this at the end of your JavaScript, after the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Load chat for any existing orders
    document.querySelectorAll('.chat-messages').forEach(chatBox => {
        const orderId = chatBox.id.replace('chat-messages-', '');
        loadChat(orderId);
    });
});

    </script>
</body>
</html>
