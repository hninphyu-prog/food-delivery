// Function to update notification count
function updateNotificationCount() {
    fetch('../../api/fetch_restaurant_notifications.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.notification-badge');
            const notificationLink = document.getElementById('nav-notifications');
            
            if (data.count > 0) {
                // If badge doesn't exist, create it
                if (!badge) {
                    const newBadge = document.createElement('span');
                    newBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge';
                    newBadge.style.cssText = 'font-size: 0.65em;';
                    newBadge.textContent = data.count;
                    notificationLink.appendChild(newBadge);
                } else {
                    // Update existing badge
                    badge.textContent = data.count;
                    badge.style.display = 'block';
                }
            } else {
                // Remove badge if count is 0
                if (badge) {
                    badge.remove();
                }
            }
        })
        .catch(error => console.error('Error fetching notifications:', error));
}

// Update notification count every 30 seconds
setInterval(updateNotificationCount, 30000);

// Initial load
document.addEventListener('DOMContentLoaded', function() {
    updateNotificationCount();
});
