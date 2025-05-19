// Get base path for API calls
const basePath = window.location.pathname.includes('/admin/') ? '../' :
    window.location.pathname.includes('/patient/') ? '../' : '';

// Notification sound
const notificationSound = new Audio(basePath + 'assets/sounds/notification.wav');

// Function to update notification count
function updateNotificationCount(count) {
    const countElement = document.querySelector('.notification-count, .admin-notification-count');
    if (countElement) {
        const oldCount = parseInt(countElement.textContent);
        countElement.textContent = count;
        countElement.style.display = count > 0 ? 'block' : 'none';

        // Play sound if there are new notifications
        if (count > oldCount) {
            notificationSound.play().catch(error => {
                console.log('Error playing notification sound:', error);
            });
        }
    }
}

// Function to format notification item
function formatNotification(notification) {
    const isRead = notification.is_read ? 'bg-light' : 'bg-info bg-opacity-10';
    return `
        <div class="notification-item p-2 border-bottom ${isRead}" data-id="${notification.notification_id}">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <strong>${notification.title}</strong>
                <small class="text-muted">${notification.created_at_formatted}</small>
            </div>
            <p class="mb-0 text-muted">${notification.message}</p>
            ${!notification.is_read ? `
                <button class="btn btn-sm btn-link mark-read-btn p-0 mt-1" data-id="${notification.notification_id}">
                    Mark as Read
                </button>
            ` : ''}
        </div>
    `;
}

// Function to fetch and update notifications
function fetchNotifications() {
    fetch(basePath + 'notifications/get_notifications.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationCount(data.unread_count);
                
                const notificationsList = document.querySelector('.notifications-list, .admin-notifications-list');
                if (notificationsList) {
                    if (data.notifications.length === 0) {
                        notificationsList.innerHTML = '<div class="p-3 text-center text-muted">No notifications</div>';
                    } else {
                        notificationsList.innerHTML = data.notifications.map(formatNotification).join('');
                        
                        // Add click handlers for mark as read buttons
                        notificationsList.querySelectorAll('.mark-read-btn').forEach(btn => {
                            btn.addEventListener('click', function(e) {
                                e.preventDefault();
                                markAsRead(this.dataset.id);
                            });
                        });
                    }
                }
            }
        })
        .catch(error => console.error('Error fetching notifications:', error));
}

// Function to mark a notification as read
function markAsRead(notificationId) {
    const formData = new FormData();
    formData.append('action', 'mark_read');
    formData.append('notification_id', notificationId);
    
    fetch(basePath + 'notifications/update_notifications.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchNotifications();
        }
    })
    .catch(error => console.error('Error marking notification as read:', error));
}

// Function to mark all notifications as read
function markAllAsRead() {
    fetch(basePath + 'notifications/update_notifications.php', {
        method: 'POST',
        body: new URLSearchParams({
            'action': 'mark_all_read'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchNotifications();
        }
    })
    .catch(error => console.error('Error marking all notifications as read:', error));
}

// Initialize notifications
document.addEventListener('DOMContentLoaded', function() {
    // Initial fetch
    fetchNotifications();
    
    // Set up periodic updates
    setInterval(fetchNotifications, 30000); // Check every 30 seconds
    
    // Add click handler for mark all as read button
    const markAllReadBtn = document.querySelector('.mark-all-read');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', markAllAsRead);
    }
});