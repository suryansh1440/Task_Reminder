<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Get task ID
$task_id = $_GET['id'] ?? null;

if ($task_id) {
    // Update task status to completed
    $stmt = $conn->prepare("UPDATE tasks SET status = 'completed' WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $_SESSION['user_id']]);
}

// Redirect back to task list
header("Location: list.php");
exit();
?> 