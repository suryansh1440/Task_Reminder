<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get tasks due today or tomorrow
$stmt = $conn->prepare("
    SELECT id, title, due_date, reminder_time 
    FROM tasks 
    WHERE user_id = ? 
    AND status = 'pending'
    AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)
    ORDER BY due_date, reminder_time
");

$stmt->execute([$_SESSION['user_id']]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['tasks' => $tasks]);
?> 