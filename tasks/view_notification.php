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
    <title>Task Notification - TaskReminder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'bounce-slow': 'bounce 3s infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                        bounce: {
                            '0%, 100%': { transform: 'translateY(-5%)' },
                            '50%': { transform: 'translateY(0)' },
                        },
                    },
                },
            },
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-primary-50 to-secondary-50 font-sans min-h-screen" x-data="{ showNotification: true }">
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
                <div class="flex items-center">
                    <a href="list.php" class="text-gray-600 hover:text-gray-900">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Notification Card -->
        <div x-show="showNotification" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="bg-white/90 backdrop-blur-md rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center space-x-3">
                        <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                            <svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Task Reminder</h2>
                            <p class="text-sm text-gray-500"><?php echo date('F j, Y h:i A', strtotime($task['due_date'])); ?></p>
                        </div>
                    </div>
                    <button @click="showNotification = false" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($task['title']); ?></h3>
                        <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 rounded-full text-sm font-medium 
                            <?php echo $task['priority'] === 'high' ? 'bg-red-100 text-red-800' : 
                                    ($task['priority'] === 'medium' ? 'bg-yellow-100 text-yellow-800' : 
                                    'bg-green-100 text-green-800'); ?>">
                            <?php echo ucfirst($task['priority']); ?> Priority
                        </span>
                        <span class="px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                            Due: <?php echo date('h:i A', strtotime($task['due_date'])); ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                <?php if ($task['status'] !== 'completed'): ?>
                <a href="complete_task.php?id=<?php echo $task['id']; ?>" 
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-primary-600 to-secondary-600 hover:from-primary-700 hover:to-secondary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-300">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Mark as Complete
                </a>
                <?php endif; ?>
                <a href="edit.php?id=<?php echo $task['id']; ?>" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-300">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Task
                </a>
            </div>
        </div>

        <!-- Task Details Card -->
        <div class="bg-white/90 backdrop-blur-md rounded-2xl shadow-xl overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Task Details</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Created</h4>
                        <p class="mt-1 text-sm text-gray-900"><?php echo date('F j, Y h:i A', strtotime($task['created_at'])); ?></p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Last Updated</h4>
                        <p class="mt-1 text-sm text-gray-900"><?php echo date('F j, Y h:i A', strtotime($task['updated_at'])); ?></p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Status</h4>
                        <p class="mt-1">
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                <?php echo $task['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                                        ($task['status'] === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 
                                        'bg-gray-100 text-gray-800'); ?>">
                                <?php echo ucfirst($task['status']); ?>
                            </span>
                        </p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Reminder Time</h4>
                        <p class="mt-1 text-sm text-gray-900"><?php echo date('h:i A', strtotime($task['reminder_time'])); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 