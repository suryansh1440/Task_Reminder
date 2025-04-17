// Function to request notification permission
function requestNotificationPermission() {
    if (!("Notification" in window)) {
        console.log("This browser does not support desktop notification");
        return;
    }

    Notification.requestPermission().then(function (permission) {
        if (permission === "granted") {
            console.log("Notification permission granted");
        }
    });
}

// Function to show custom notification
function showCustomNotification(title, options) {
    if (!("Notification" in window)) {
        console.log("This browser does not support desktop notification");
        return;
    }

    if (Notification.permission === "granted") {
        const notification = new Notification(title, {
            ...options,
            icon: '/taskReminder/assets/images/notification-icon.png', // Make sure to add this icon
            badge: '/taskReminder/assets/images/notification-badge.png', // Make sure to add this badge
            silent: false
        });

        notification.onclick = function(event) {
            event.preventDefault();
            window.focus();
            if (options.url) {
                window.location.href = options.url;
            }
            notification.close();
        };

        // Auto close after 10 seconds
        setTimeout(() => notification.close(), 10000);
    }
}

// Request permission when the page loads
document.addEventListener('DOMContentLoaded', function() {
    requestNotificationPermission();
}); 