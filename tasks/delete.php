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
    try {
        // First check if the task belongs to the current user
        $check_stmt = $conn->prepare("SELECT id FROM tasks WHERE id = ? AND user_id = ?");
        $check_stmt->execute([$task_id, $_SESSION['user_id']]);
        
        if ($check_stmt->rowCount() > 0) {
            // Delete the task
            $delete_stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
            $delete_stmt->execute([$task_id, $_SESSION['user_id']]);
            
            // Set success message in session
            $_SESSION['success_message'] = "Task deleted successfully.";
        } else {
            // Set error message in session
            $_SESSION['error_message'] = "Task not found or you don't have permission to delete it.";
        }
    } catch (PDOException $e) {
        // Set error message in session
        $_SESSION['error_message'] = "Error deleting task. Please try again.";
    }
} else {
    // Set error message in session
    $_SESSION['error_message'] = "Invalid task ID.";
}

// Redirect back to task list
header("Location: list.php");
exit();
?> 