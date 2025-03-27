<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);
$task_id = $data['task_id'] ?? null;
$status = $data['status'] ?? null;

if (!$task_id || !$status) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

// Update task status
$stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?");
$success = $stmt->execute([$status, $task_id, $_SESSION['user_id']]);

echo json_encode(['success' => $success]);
?> 