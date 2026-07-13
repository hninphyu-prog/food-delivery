
<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /foodandme/views/admin/adminlogin.php");
    exit;
}

// Fetch user info
$stmt = $pdo->prepare("SELECT name FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Current file for active link
$current = basename($_SERVER['PHP_SELF']);
$userName = isset($user['name']) && $user['name'] !== '' ? htmlspecialchars($user['name']) : 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Panel</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
/* ===== Sidebar ===== */
body {
    margin: 0; 
    font-family: "Segoe UI", Arial, sans-serif; 
    background: #fffaf5;
}
.sidebar {
    position: fixed; 
    left: 0; 
    top: 0; 
    width: 250px; 
    height: 100%;
    background: linear-gradient(180deg, #ff9a3d, #ff6a00);
    color: white; 
    padding: 20px 10px;
    box-sizing: border-box; 
    transition: width 0.3s ease;
    box-shadow: 2px 0 12px rgba(0,0,0,0.15); 
    z-index: 1000;

    /* ✅ Enable scroll */
    overflow-y: auto;
    overflow-x: hidden;
}
.sidebar::-webkit-scrollbar {
    width: 6px;
}
.sidebar::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 4px;
}
.sidebar::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.6);
}

.sidebar.minimized { width: 80px; padding-top: 30px; }
.pac { font-size: 30px; margin-left: 20px; }
.sidebar.minimized .pac, .sidebar.minimized ul li a span { display: none; }

.sidebar ul { list-style: none; padding: 0; margin: 0; }
.sidebar ul li { margin: 10px 0; }
.sidebar ul li a {
    color: #fff; 
    text-decoration: none; 
    display: flex; 
    align-items: center;
    padding: 10px 14px; 
    border-radius: 10px; 
    transition: all 0.3s ease; 
    font-size: 14px;
}
.sidebar ul li a img { 
    width: 26px; 
    height: 26px; 
    margin-right: 12px; 
    filter: invert(100%) brightness(200%); 
    transition: all 0.3s ease; 
}
.sidebar ul li a.active img { 
    filter: invert(43%) sepia(85%) saturate(5098%) hue-rotate(10deg) brightness(101%) contrast(104%); 
}
/* Active link */
.sidebar ul li a.active {
    background: #fff;
    color: #ff6a00;
    font-weight: bold;
}
.sidebar ul li a.active i {
    color: #ff6a00;
}
.sidebar ul li a:hover { 
    background: rgba(255,255,255,0.2); 
    transform: translateX(4px); 
}
.sidebar ul li a:hover img { transform: scale(1.1); }

/* ===== Sidebar Toggle ===== */
#sidebarToggle {
    position: fixed; 
    top: 0px; 
    left: 250px; 
    font-size: 26px;
    border: none; 
    color: #ff6a00; 
    cursor: pointer;
    background:#ffff;
    padding: 6px 12px;
    z-index: 1100;
    transition: all 0.3s ease;
}
#sidebarToggle:hover { 
    background: #ff6a003d;  
    box-shadow: 0 2px 8px rgba(0,0,0,0.15); 
}
.sidebar.minimized + #sidebarToggle { left: 80px; }

/* ===== Top Header ===== */
.top-header {
    display: flex; 
    align-items: center; 
    justify-content: space-between;
    background: #fff; 
    padding: 10px 20px; 
    border-bottom: 1px solid #ddd;
    position: fixed; 
    top: 0; 
    right: 0; 
    left: 0; 
    z-index: 900; 
    transition: margin-left 0.3s ease;
    margin-left: 250px;
}
.sidebar.minimized ~ .top-header { margin-left: 80px; }
.search-bar { 
    width: 250px; 
    padding: 6px 12px; 
    border: 1px solid #ddd; 
    border-radius: 20px; 
}

/* Notifications */
.notification-wrapper { position: relative; }
.notification .bell { font-size: 22px; color: #ff6a00; cursor: pointer; }
.notification .badge {
    position: absolute; top: -5px; right: -8px;
    background: #e74a3b; color: #fff; font-size: 12px; padding: 2px 6px; border-radius: 50%;
}
.notification-dropdown {
    display: none; position: absolute; top: 50px; right: 0;
    width: 320px; background: #fff; border-radius: 8px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.15); z-index: 1000; border: 1px solid #eee;
}
.notification-dropdown.show { display: block; }
#notification-list { list-style: none; margin: 0; padding: 0; max-height: 400px; overflow-y: auto; }
#notification-list li { padding: 15px; border-bottom: 1px solid #f0f0f0; cursor: pointer; }
#notification-list li:hover { background: #f9f9f9; }
#notification-list .title { font-weight: 600; }
#notification-list .time { display: block; font-size: 12px; color: #888; margin-top: 5px; }

/* Modal */
.modal-overlay { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:2000; justify-content:center; align-items:center; }
.modal-overlay.show { display:flex; }
.modal-content { background:#fff; padding:25px; border-radius:10px; width:90%; max-width:600px; }
.modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; }
.modal-header h2 { margin:0; }
.modal-header span { cursor:pointer; font-size:24px; }

/* ===== Main Content ===== */
.main-content { margin-left: 250px; padding: 20px; transition: margin-left 0.3s; }
.sidebar.minimized ~ .main-content { margin-left: 80px; }

.greeting{
    margin-left:40px;
}
.sidebar-header {
    text-align: center;
    font-weight: bold;
    font-size: 18px;
    margin: 100px 0 20px 0;
    color: #fff;
    letter-spacing: 1px;
}
.profile {
    display: flex;
    align-items: center;
    backdrop-filter: blur(8px);
    margin: 10px;
    border-radius: 15px;
    margin: 20px 0 55px 0;
    padding: 12px;
    transition: all 0.3s ease;
}
.profile:hover { background: rgba(255,255,255,0.25); }
.profile img {
    width: 42px;
    border-radius: 50%;
    margin-right: 10px;
    border: 2px solid #fff;
}
.profile-info strong {
    color: #fff;
    font-size: 14px;
}
.customer_brand__logo {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    color: #ff6a00;
    border-radius: 50%;
    font-size: 1.2rem;
}
/* Enhanced Modal Styles */
.modal-content {
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #eee;
    padding-bottom: 15px;
    margin-bottom: 20px;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
    color: #ff6a00;
}

.modal-header span {
    font-size: 1.8rem;
    cursor: pointer;
    color: #999;
    transition: color 0.3s;
}

.modal-header span:hover {
    color: #ff6a00;
}

.modal-body {
    font-size: 14px;
    line-height: 1.6;
}

.modal-body p {
    margin: 10px 0;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 5px;
}

.modal-body strong {
    color: #495057;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn-primary {
    background: #ff6a00;
    color: white;
}

.btn-primary:hover {
    background: #e55d00;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}
</style>
</head>
<body>

<!-- Sidebar -->
<div id="sidebar" class="sidebar">
    <div class="sidebar-header profile">
        <div class="customer_brand__logo"><i class="fas fa-utensils"></i></div>
        <span class="pac" style="margin-left: 20px;font-size: 30px;">Food<span>&amp;</span>Me</span>
    </div>
    <ul>
       <li><a href="dashboard.php" class="<?= $current=='dashboard.php'?'active':'' ?>"><img src="iconn/dashboards.png"><span>Dashboard</span></a></li>
        <li><a href="restaurants.php" class="<?= $current=='restaurants.php'?'active':'' ?>"><img src="iconn/restaur.png"><span>Restaurants</span></a></li>
        <li><a href="weekly_settlements_export.php" class="<?= $current=='weekly_settlements_export.php'?'active':'' ?>"><img src="iconn/restaur.png"><span>Settlements</span></a></li>
        <li><a href="order.php" class="<?= $current=='order.php'?'active':'' ?>"><img src="iconn/order.png"><span>Orders</span></a></li>
        <li><a href="riders.php" class="<?= $current=='riders.php'?'active':'' ?>"><img src="iconn/delivery.png"><span>Riders</span></a></li>
        <li><a href="deliveries.php" class="<?= $current=='deliveries.php'?'active':'' ?>"><img src="iconn/delivery.png"><span>Deliveries</span></a></li>
        <li><a href="rider_payouts_export.php" class="<?= $current=='rider_payouts_export.php'?'active':'' ?>"><img src="iconn/delivery.png"><span>Rider Payouts</span></a></li>
        <li><a href="rider_deposit_approval.php" class="<?= $current=='rider_deposit_approval.php'?'active':'' ?>"><img src="iconn/delivery.png"><span>Rider Deposits</span></a></li>
        <li><a href="vendor_rider_request.php" class="<?= $current=='vendor_rider_request.php'?'active':'' ?>"><img src="iconn/delivery.png"><span>Partner Requests</span></a></li>
        <li><a href="users.php" class="<?= $current=='users.php'?'active':'' ?>"><img src="iconn/cust.png"><span>Customers</span></a></li>
        <li><a href="reports.php" class="<?= $current=='reports.php'?'active':'' ?>"><img src="iconn/report.png"><span>Review</span></a></li>
        <li><a href="settingadmin.php" class="<?= $current=='settingadmin.php'?'active':'' ?>"><img src="iconn/setting.png"><span>Settings</span></a></li>
        <li><a href="../../logout.php" style="color:red; font-weight:bold;"><img src="iconn/logout2.png"><span>Logout</span></a></li>
     </ul>
</div>

<!-- Sidebar Toggle -->
<button id="sidebarToggle">&#9776;</button>

<!-- Top Header -->
<div class="top-header">
    <span class="greeting">Hello, <?= $userName ?> 👋</span>
    <div class="notification-wrapper">
        <div class="notification" id="notification-bell">
            <span class="bell">&#128276;</span>
            <span class="badge" id="notification-badge" style="display:none;">0</span>
        </div>
        <div class="notification-dropdown" id="notification-dropdown">
            <ul id="notification-list"><li>No new notifications</li></ul>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <!-- Your page content here -->
</div>

<!-- Notification Modal -->
<div id="notification-modal-overlay" class="modal-overlay">
    <div id="notification-modal" class="modal-content">
        <div class="modal-header">
            <h2 id="modal-title">Notification Details</h2>
            <span id="modal-close-btn">&times;</span>
        </div>
        <div class="modal-body" id="modal-body"></div>
    </div>
</div>

<script>
// Sidebar toggle
const sidebar = document.getElementById('sidebar');
const toggle = document.getElementById('sidebarToggle');
const mainContent = document.querySelector('.main-content');

if (localStorage.getItem('sidebarState') === 'minimized') {
    sidebar.classList.add('minimized');
}
toggle.addEventListener('click', () => {
    sidebar.classList.toggle('minimized');
    if (sidebar.classList.contains('minimized')) localStorage.setItem('sidebarState','minimized');
    else localStorage.setItem('sidebarState','expanded');
});

// Notifications
const bell = document.getElementById('notification-bell');
const badge = document.getElementById('notification-badge');
const dropdown = document.getElementById('notification-dropdown');
const notificationList = document.getElementById('notification-list');
const modalOverlay = document.getElementById('notification-modal-overlay');
const modalTitle = document.getElementById('modal-title');
const modalBody = document.getElementById('modal-body');
const modalCloseBtn = document.getElementById('modal-close-btn');

function fetchNotifications() {
    fetch('../../api/get_notification_details.php')
    .then(res => {
        if (!res.ok) {
            throw new Error('Network response was not ok');
        }
        return res.json();
    })
    .then(data => {
        if (data.success) {
            badge.style.display = data.count > 0 ? 'inline-block' : 'none';
            badge.textContent = data.count;

            notificationList.innerHTML = '';
            if(data.notifications && data.notifications.length > 0){
                data.notifications.forEach(n => {
                    const li = document.createElement('li');
                    li.dataset.id = n.notification_id;
                    const date = new Date(n.created_at);
                    const mins = Math.round((new Date()-date)/(1000*60));
                    const time = mins<60?`${mins}m ago`:`${Math.floor(mins/60)}h ago`;
                    li.innerHTML = `<span class="title">${n.title}</span><span class="time">${time}</span>`;
                    notificationList.appendChild(li);
                });
            } else {
                notificationList.innerHTML = '<li>No new notifications</li>';
            }
        } else {
            console.error('Notification fetch error:', data.message);
            notificationList.innerHTML = '<li>Error loading notifications</li>';
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        notificationList.innerHTML = '<li>Connection error</li>';
    });
}

bell.addEventListener('click', e => { 
    e.stopPropagation(); 
    dropdown.classList.toggle('show'); 
});
document.addEventListener('click', () => dropdown.classList.remove('show'));

notificationList.addEventListener('click', e => {
    const li = e.target.closest('li');
    if (!li || !li.dataset.id) return;
    
    // Show loading state
    const titleEl = li.querySelector('.title');
    const originalText = titleEl.textContent;
    titleEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
    
    fetch(`../../api/get_notification_details.php?id=${li.dataset.id}`)
        .then(res => res.json())
        .then(data => {
            // Restore original text
            titleEl.textContent = originalText;
            
            if (data.success && data.notification) {
                // Refresh notifications to update count
                fetchNotifications();
                
                // Close dropdown
                dropdown.classList.remove('show');
                
                // Show modal with notification details
                showNotificationModal(data.notification);
            }
        })
        .catch(error => {
            // Restore original text on error
            titleEl.textContent = originalText;
            console.error('Error fetching notification details:', error);
        });
});

// Function to show notification modal
function showNotificationModal(notification) {
    const modalTitle = document.getElementById('modal-title');
    const modalBody = document.getElementById('modal-body');
    const modalOverlay = document.getElementById('notification-modal-overlay');
    
    // Format the date
    const date = new Date(notification.created_at);
    const formattedDate = date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    // Set modal content
    modalTitle.textContent = notification.title;
    
    let htmlContent = `
        <div style="margin-bottom: 20px;">
            <p><strong>📅 Date:</strong> ${formattedDate}</p>
            ${notification.message ? `<p><strong>📝 Details:</strong> ${notification.message}</p>` : ''}
        </div>
    `;
    
    // Add action buttons based on redirect URL
    if (notification.redirect_url && notification.redirect_url !== '#') {
        htmlContent += `
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                <p><strong>🔗 Related Action:</strong></p>
                <div style="display: flex; gap: 10px; margin-top: 15px;">
                    <button id="go-to-page-btn" class="btn btn-primary" 
                            style="padding: 10px 20px; background: #ff6a00; border: none; color: white; border-radius: 5px; cursor: pointer;">
                        <i class="fas fa-external-link-alt"></i> Go to Related Page
                    </button>
                    <button id="close-modal-btn" class="btn btn-secondary" 
                            style="padding: 10px 20px; background: #6c757d; border: none; color: white; border-radius: 5px; cursor: pointer;">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        `;
    } else {
        htmlContent += `
            <div style="margin-top: 20px;">
                <button id="close-modal-btn" class="btn btn-primary" 
                        style="padding: 10px 20px; background: #ff6a00; border: none; color: white; border-radius: 5px; cursor: pointer; width: 100%;">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        `;
    }
    
    modalBody.innerHTML = htmlContent;
    
    // Show the modal
    modalOverlay.classList.add('show');
    
    // Add event listeners for buttons
    if (notification.redirect_url && notification.redirect_url !== '#') {
        document.getElementById('go-to-page-btn').addEventListener('click', function() {
            // Check if the URL is relative or absolute
            let redirectUrl = notification.redirect_url;
            
            // If it's a relative path, make sure it's from the root
            if (!redirectUrl.startsWith('http')) {
                // Remove any duplicate /foodandme if present
                if (redirectUrl.startsWith('/foodandme/')) {
                    redirectUrl = redirectUrl.replace('/foodandme/', '/');
                }
                // Ensure it starts from the correct base
                redirectUrl = '/foodandme' + (redirectUrl.startsWith('/') ? '' : '/') + redirectUrl;
            }
            
            console.log('Redirecting to:', redirectUrl);
            window.location.href = redirectUrl;
        });
    }
    
    // Close modal button
    document.getElementById('close-modal-btn').addEventListener('click', function() {
        modalOverlay.classList.remove('show');
    });
}

// Also update the modal close button listener to handle dynamic buttons
modalCloseBtn.addEventListener('click', () => modalOverlay.classList.remove('show'));
modalOverlay.addEventListener('click', e => { 
    if(e.target === modalOverlay) modalOverlay.classList.remove('show'); 
});

// Initial fetch
fetchNotifications();
// Refresh notifications every 30 seconds
setInterval(fetchNotifications, 30000);
</script>
</body>
</html>
