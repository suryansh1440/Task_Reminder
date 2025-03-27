<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$priority = isset($_GET['priority']) ? $_GET['priority'] : 'all';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'due_date';

// Build query
$query = "SELECT * FROM tasks WHERE user_id = ?";
$params = [$_SESSION['user_id']];

if ($status !== 'all') {
    $query .= " AND status = ?";
    $params[] = $status;
}

if ($priority !== 'all') {
    $query .= " AND priority = ?";
    $params[] = $priority;
}

// Add sorting
switch ($sort) {
    case 'priority':
        $query .= " ORDER BY FIELD(priority, 'high', 'medium', 'low')";
        break;
    case 'title':
        $query .= " ORDER BY title";
        break;
    default:
        $query .= " ORDER BY due_date";
}

$stmt = $conn->prepare($query);
$stmt->execute($params);
$tasks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task List - Task Reminder System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <a href="../index.php" class="text-xl font-bold text-blue-600">TaskReminder</a>
                        </div>
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <a href="../index.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Dashboard
                            </a>
                            <a href="create.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                New Task
                            </a>
                            <a href="list.php" class="border-blue-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                All Tasks
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <a href="../auth/logout.php" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium">
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">All Tasks</h2>
                    <a href="create.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Create New Task
                    </a>
                </div>

                <!-- Filters -->
                <div class="bg-white shadow rounded-lg p-4 mb-6">
                    <form method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All</option>
                                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </div>

                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                            <select name="priority" id="priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="all" <?php echo $priority === 'all' ? 'selected' : ''; ?>>All</option>
                                <option value="high" <?php echo $priority === 'high' ? 'selected' : ''; ?>>High</option>
                                <option value="medium" <?php echo $priority === 'medium' ? 'selected' : ''; ?>>Medium</option>
                                <option value="low" <?php echo $priority === 'low' ? 'selected' : ''; ?>>Low</option>
                            </select>
                        </div>

                        <div>
                            <label for="sort" class="block text-sm font-medium text-gray-700">Sort By</label>
                            <select name="sort" id="sort" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="due_date" <?php echo $sort === 'due_date' ? 'selected' : ''; ?>>Due Date</option>
                                <option value="priority" <?php echo $sort === 'priority' ? 'selected' : ''; ?>>Priority</option>
                                <option value="title" <?php echo $sort === 'title' ? 'selected' : ''; ?>>Title</option>
                            </select>
                        </div>

                        <div class="sm:col-span-3">
                            <button type="submit" class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Apply Filters
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Task List -->
                <div class="bg-white shadow overflow-hidden sm:rounded-md">
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($tasks as $task): ?>
                            <li>
                                <div class="px-4 py-4 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <input type="checkbox" 
                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                                   <?php echo $task['status'] === 'completed' ? 'checked' : ''; ?>
                                                   onchange="updateTaskStatus(<?php echo $task['id']; ?>, this.checked)">
                                            <p class="ml-3 text-sm font-medium text-gray-900 <?php echo $task['status'] === 'completed' ? 'line-through' : ''; ?>">
                                                <?php echo htmlspecialchars($task['title']); ?>
                                            </p>
                                        </div>
                                        <div class="flex items-center space-x-4">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php
                                                switch ($task['priority']) {
                                                    case 'high':
                                                        echo 'bg-red-100 text-red-800';
                                                        break;
                                                    case 'medium':
                                                        echo 'bg-yellow-100 text-yellow-800';
                                                        break;
                                                    case 'low':
                                                        echo 'bg-green-100 text-green-800';
                                                        break;
                                                }
                                                ?>">
                                                <?php echo ucfirst($task['priority']); ?>
                                            </span>
                                            <span class="text-sm text-gray-500">
                                                Due: <?php echo date('M d, Y', strtotime($task['due_date'])); ?>
                                            </span>
                                            <a href="edit.php?id=<?php echo $task['id']; ?>" class="text-blue-600 hover:text-blue-900">Edit</a>
                                        </div>
                                    </div>
                                    <div class="mt-2 sm:flex sm:justify-between">
                                        <div class="sm:flex">
                                            <p class="flex items-center text-sm text-gray-500">
                                                <?php echo htmlspecialchars($task['description']); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </main>
    </div>

    <script>
    function updateTaskStatus(taskId, completed) {
        fetch('update_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                task_id: taskId,
                status: completed ? 'completed' : 'pending'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
    </script>
</body>
</html> 