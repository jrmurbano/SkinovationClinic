// Create an audio element for notification sound
const notificationSound = new Audio('/assets/sounds/notification.mp3');

// Function to play notification sound
function playNotificationSound() {
    notificationSound.play().catch(error => {
        console.log('Error playing notification sound:', error);
    });
}

// Function to check for new notifications
function checkNewNotifications() {
    fetch('/check_notifications.php')
        .then(response => response.json())
        .then(data => {
                if (data.hasNew) {
                    // Play notification sound
                    playNotificationSound();

                    // Show browser notification if permission is granted
                    if (Notification.permission === 'granted') {
                        const notification = new Notification(data.title, {
                            body: data.message,
                            icon: '/assets/img/ISCAP1-303-Skinovation-Clinic-COLORED-Logo.png'
                        });
                    }

                    // Update notification badge count
                    const badge = document.querySelector('.notification-count');
                    if (badge) {
                        badge.textContent = data.unreadCount;
                        badge.style.display = data.unreadCount > 0 ? 'block' : 'none';
                    }

                    // Update notification list if it exists
                    const notificationsList = document.querySelector('.notifications-list');
                    if (notificationsList && data.notifications) {
                        notificationsList.innerHTML = data.notifications.map(notification => `
                        <div class="notification-item unread">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="title">${notification.title}</h5>
                                    <p class="message">${notification.message}</p>
                                    ${notification.service_name ? `
                                        <p class="service-name mb-2">
                                            ${notification.service_name}
                                            ${notification.appointment_date ? `
                                                on ${new Date(notification.appointment_date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}
                                                at ${new Date(notification.appointment_time).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' })}
                                            ` : ''}
                                        </p>
                                    ` : ''}
                                    <span class="time">
                                        <i class="fas fa-clock"></i> 
                                        ${new Date(notification.created_at).toLocaleString()}
                                    </span>
                                </div>
                                <form method="POST" class="ms-3">
                                    <input type="hidden" name="action" value="mark_read">
                                    <input type="hidden" name="notification_id" value="${notification.notification_id}">
                                    <button type="submit" class="btn btn-outline-primary btn-sm mark-read-btn">
                                        <i class="fas fa-check"></i> Mark as Read
                                    </button>
                                </form>
                            </div>
                        </div>
                    `).join('');
                }
            }
        })
        .catch(error => {
            console.error('Error checking notifications:', error);
        });
}

// Request notification permission
if (Notification.permission !== 'granted') {
    Notification.requestPermission();
}

// Check for new notifications every 30 seconds
setInterval(checkNewNotifications, 30000);