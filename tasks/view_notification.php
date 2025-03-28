<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Get user data from database
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get task ID
$task_id = $_GET['id'] ?? null;

if (!$task_id) {
    header("Location: list.php");
    exit();
}

// Get task details
$stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$task_id, $_SESSION['user_id']]);
$task = $stmt->fetch();

if (!$task) {
    header("Location: list.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Notification - Task Reminder System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            200: '#fed7aa',
                            300: '#fdba74',
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                            800: '#9a3412',
                            900: '#7c2d12',
                        },
                        secondary: {
                            50: '#fff1f2',
                            100: '#ffe4e6',
                            200: '#fecdd3',
                            300: '#fda4af',
                            400: '#fb7185',
                            500: '#f43f5e',
                            600: '#e11d48',
                            700: '#be123c',
                            800: '#9f1239',
                            900: '#881337',
                        }
                    },
                },
            },
        }
    </script>
</head>
<body class="bg-gradient-to-br from-primary-50 to-secondary-50 font-sans min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white/90 backdrop-blur-md shadow-lg sticky top-0 z-50 animate-fade-in">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <div class="flex items-center space-x-2">
                            <svg class="h-8 w-8 text-primary-600 animate-bounce-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <h1 class="text-2xl font-bold bg-gradient-to-r from-primary-600 to-secondary-600 bg-clip-text text-transparent">TaskReminder</h1>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <div class="h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center">
                            <span class="text-primary-600 font-medium"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></span>
                        </div>
                        <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
                    </div>
                    <a href="../auth/logout.php" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-100 transition-colors duration-200">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white/90 backdrop-blur-md rounded-2xl shadow-xl p-6 animate-fade-in">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Task Reminder</h1>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $task['status'] === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                        <?php echo ucfirst($task['status']); ?>
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        <?php echo $task['priority'] === 'high' ? 'bg-red-100 text-red-800' : 
                            ($task['priority'] === 'medium' ? 'bg-yellow-100 text-yellow-800' : 
                            'bg-green-100 text-green-800'); ?>">
                        <?php echo ucfirst($task['priority']); ?> Priority
                    </span>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900"><?php echo htmlspecialchars($task['title']); ?></h2>
                    <p class="mt-2 text-gray-600"><?php echo htmlspecialchars($task['description']); ?></p>
                </div>

                <div class="flex items-center space-x-4 text-sm text-gray-500">
                    <span class="flex items-center">
                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Due: <?php echo date('M d, Y', strtotime($task['due_date'])); ?>
                    </span>
                    <span class="flex items-center">
                        <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Reminder: <?php echo date('h:i A', strtotime($task['reminder_time'])); ?>
                    </span>
                </div>

                <div class="flex items-center space-x-4 mt-6 pt-6 border-t">
                    <?php if ($task['status'] !== 'completed'): ?>
                    <a href="complete_task.php?id=<?php echo $task['id']; ?>" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-300">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Complete Task
                    </a>
                    <?php endif; ?>
                    <a href="list.php" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-100 transition-colors duration-200">
                        Back to Tasks
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 