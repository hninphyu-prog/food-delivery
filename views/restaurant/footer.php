
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Include order actions JavaScript -->
<script src="order_actions.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle notification panel
    const notificationBell = document.getElementById('notificationBell');
    const closeNotifications = document.getElementById('closeNotifications');

    if (notificationBell) {
        notificationBell.addEventListener('click', function() {
            notificationPanel.classList.toggle('active');
        });
    }

    if (closeNotifications) {
        closeNotifications.addEventListener('click', function() {
            notificationPanel.classList.remove('active');
        });
    }
});

</script>
</body>
</html>