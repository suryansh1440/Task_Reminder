<?php
require_once '../config/database.php';

// Function to send notification
function sendNotification($task) {
    global $conn;
    
    // Create notification in database
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, task_id, message, created_at) VALUES (?, ?, ?, NOW())");
    $message = "Reminder: Task '{$task['title']}' is due at " . date('h:i A', strtotime($task['due_date']));
    $stmt->execute([$task['user_id'], $task['id'], $message]);
    
    // Update last notification time
    $stmt = $conn->prepare("UPDATE tasks SET last_notification = NOW() WHERE id = ?");
    $stmt->execute([$task['id']]);

    // Return notification data for browser notification
    return [
        'title' => $task['title'],
        'message' => $message,
        'priority' => $task['priority'],
        'due_date' => date('h:i A', strtotime($task['due_date'])),
        'task_id' => $task['id']
    ];
}

header('Content-Type: application/json');

// Check if this is an AJAX request for notifications
if (isset($_GET['check_notifications'])) {
    // Get all tasks that are due within the next 5 minutes
    $now = date('Y-m-d H:i:s');
    $five_minutes_later = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    $five_minutes_ago = date('Y-m-d H:i:s', strtotime('-5 minutes'));

    $stmt = $conn->prepare("
        SELECT t.*, u.email 
        FROM tasks t 
        JOIN users u ON t.user_id = u.id 
        WHERE t.status != 'completed' 
        AND t.due_date BETWEEN ? AND ?
        AND (
            t.last_notification IS NULL 
            OR t.last_notification < ?
        )
        AND t.reminder_time IS NOT NULL
        AND t.reminder_time <= ?
    ");

    $stmt->execute([$now, $five_minutes_later, $five_minutes_ago, $now]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $notifications = [];
    foreach ($tasks as $task) {
        $notificationData = sendNotification($task);
        
        // Prepare notification data for browser
        $notifications[] = [
            'title' => 'Task Reminder',
            'options' => [
                'body' => $notificationData['message'],
                'icon' => '/taskReminder/assets/images/notification-icon.png',
                'badge' => '/taskReminder/assets/images/notification-badge.png',
                'tag' => 'task-' . $task['id'],
                'data' => [
                    'taskId' => $task['id'],
                    'priority' => $task['priority']
                ],
                'actions' => [
                    ['action' => 'view', 'title' => 'View Task'],
                    ['action' => 'complete', 'title' => 'Mark Complete']
                ],
                'url' => '/taskReminder/tasks/view_notification.php?id=' . $task['id'],
                'requireInteraction' => true
            ]
        ];
        
        // Send email notification
        $to = $task['email'];
        $subject = "Task Reminder: " . $task['title'];
        $message = "This is a reminder that your task '{$task['title']}' is due at " . 
                   date('h:i A', strtotime($task['due_date'])) . ".\n\n" .
                   "Task Description: " . $task['description'] . "\n\n" .
                   "Priority: " . ucfirst($task['priority']) . "\n\n" .
                   "View task: http://localhost/taskReminder/tasks/view_notification.php?id=" . $task['id'];
        
        $headers = "From: TaskReminder <noreply@taskreminder.com>\r\n" .
                   "Reply-To: noreply@taskreminder.com\r\n" .
                   "X-Mailer: PHP/" . phpversion();
        
        mail($to, $subject, $message, $headers);
    }
    
    echo json_encode(['notifications' => $notifications]);
    exit;
}
?> 

<!-- Add notification checker script -->
<script src="/taskReminder/assets/js/notifications.js"></script>
<script>
// Check for new notifications every minute
setInterval(function() {
    fetch('/taskReminder/tasks/notifications.php?check_notifications')
        .then(response => response.json())
        .then(data => {
            if (data.notifications && data.notifications.length > 0) {
                data.notifications.forEach(notification => {
                    showCustomNotification(notification.title, notification.options);
                });
            }
        })
        .catch(error => console.error('Error checking notifications:', error));
}, 60000); // Check every minute
</script> 