// Request notification permission
function requestNotificationPermission() {
    if (!("Notification" in window)) {
        console.log("This browser does not support notifications");
        return;
    }

    Notification.requestPermission().then(function(permission) {
        if (permission === "granted") {
            console.log("Notification permission granted");
        }
    });
}

// Check for upcoming tasks and show notifications
function checkUpcomingTasks() {
    fetch('tasks/check_upcoming.php')
        .then(response => response.json())
        .then(data => {
            if (data.tasks && data.tasks.length > 0) {
                data.tasks.forEach(task => {
                    showNotification(task);
                });
            }
        });
}

// Show notification for a task
function showNotification(task) {
    if (!("Notification" in window)) {
        return;
    }

    if (Notification.permission === "granted") {
        const notification = new Notification("Task Reminder", {
            body: `${task.title} is due ${task.due_date}`,
            icon: "/images/icon.png"
        });

        notification.onclick = function() {
            window.open(`tasks/edit.php?id=${task.id}`, '_blank');
        };
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    requestNotificationPermission();
    
    // Check for upcoming tasks every 5 minutes
    setInterval(checkUpcomingTasks, 5 * 60 * 1000);
    
    // Initial check
    checkUpcomingTasks();
}); 