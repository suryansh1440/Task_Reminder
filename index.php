<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

// Get user information
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get upcoming tasks
$stmt = $conn->prepare("
    SELECT * FROM tasks 
    WHERE user_id = ? 
    AND due_date >= CURDATE() 
    AND status != 'completed' 
    ORDER BY due_date ASC 
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$upcoming_tasks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Reminder System</title>
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
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="index.php" class="border-primary-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Dashboard
                        </a>
                        <a href="tasks/create.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200">
                            New Task
                        </a>
                        <a href="tasks/list.php" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200">
                            All Tasks
                        </a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 focus:outline-none">
                            <div class="h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center">
                                <span class="text-primary-600 font-medium"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></span>
                            </div>
                            <span><?php echo htmlspecialchars($user['name']); ?></span>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                            <a href="auth/logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-primary-500 to-secondary-500 animate-fade-in">
        <div class="max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl font-extrabold text-white sm:text-5xl animate-slide-up">
                    Stay Organized, Stay Productive
                </h1>
                <p class="mt-4 text-lg text-white/90 animate-slide-up" style="animation-delay: 0.2s">
                    Manage your tasks efficiently with our smart reminder system
                </p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Total Tasks Card -->
                <div class="bg-white/90 backdrop-blur-md overflow-hidden shadow-lg rounded-xl transform transition duration-500 hover:scale-105 hover:shadow-xl animate-slide-up" style="animation-delay: 0.3s">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-primary-100 rounded-xl p-3">
                                    <svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Total Tasks
                                    </dt>
                                    <dd class="text-2xl font-semibold text-gray-900">
                                        <?php
                                        $stmt = $conn->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ?");
                                        $stmt->execute([$_SESSION['user_id']]);
                                        echo $stmt->fetchColumn();
                                        ?>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Tasks Card -->
                <div class="bg-white/90 backdrop-blur-md overflow-hidden shadow-lg rounded-xl transform transition duration-500 hover:scale-105 hover:shadow-xl animate-slide-up" style="animation-delay: 0.4s">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-secondary-100 rounded-xl p-3">
                                    <svg class="h-6 w-6 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Upcoming Tasks
                                    </dt>
                                    <dd class="text-2xl font-semibold text-gray-900">
                                        <?php
                                        $stmt = $conn->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND due_date >= CURDATE() AND status != 'completed'");
                                        $stmt->execute([$_SESSION['user_id']]);
                                        echo $stmt->fetchColumn();
                                        ?>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Completed Tasks Card -->
                <div class="bg-white/90 backdrop-blur-md overflow-hidden shadow-lg rounded-xl transform transition duration-500 hover:scale-105 hover:shadow-xl animate-slide-up" style="animation-delay: 0.5s">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-green-100 rounded-xl p-3">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Completed Tasks
                                    </dt>
                                    <dd class="text-2xl font-semibold text-gray-900">
                                        <?php
                                        $stmt = $conn->prepare("SELECT COUNT(*) FROM tasks WHERE user_id = ? AND status = 'completed'");
                                        $stmt->execute([$_SESSION['user_id']]);
                                        echo $stmt->fetchColumn();
                                        ?>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Tasks Section -->
            <div class="mt-8 animate-slide-up" style="animation-delay: 0.6s">
                <div class="bg-white/90 backdrop-blur-md shadow-lg rounded-xl">
                    <div class="px-6 py-5">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">
                            Upcoming Tasks
                        </h3>
                        <?php if (empty($upcoming_tasks)): ?>
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                <p class="mt-2 text-gray-500">No upcoming tasks. Create a new task to get started!</p>
                            </div>
                        <?php else: ?>
                            <div class="flow-root">
                                <ul class="-my-5 divide-y divide-gray-200">
                                    <?php foreach ($upcoming_tasks as $task): ?>
                                        <li class="py-4 hover:bg-gray-50 transition-colors duration-200">
                                            <div class="flex items-center space-x-4">
                                                <div class="flex-shrink-0">
                                                    <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                                                        <svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate">
                                                        <?php echo htmlspecialchars($task['title']); ?>
                                                    </p>
                                                    <p class="text-sm text-gray-500">
                                                        Due: <?php echo date('M d, Y', strtotime($task['due_date'])); ?> at <?php echo date('h:i A', strtotime($task['reminder_time'])); ?>
                                                    </p>
                                                </div>
                                                <div>
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
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
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 animate-slide-up" style="animation-delay: 0.7s">
                <a href="tasks/create.php" class="group relative rounded-xl border-2 border-dashed border-gray-300 bg-white/90 backdrop-blur-md px-6 py-8 shadow-sm hover:border-primary-500 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500 transition-all duration-300 hover:shadow-lg">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-xl bg-primary-100 flex items-center justify-center group-hover:bg-primary-200 transition-colors">
                                <svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="absolute inset-0" aria-hidden="true"></span>
                            <p class="text-lg font-medium text-gray-900">Create New Task</p>
                            <p class="text-sm text-gray-500">Add a new task to your list</p>
                        </div>
                    </div>
                </a>

                <a href="tasks/list.php" class="group relative rounded-xl border-2 border-dashed border-gray-300 bg-white/90 backdrop-blur-md px-6 py-8 shadow-sm hover:border-secondary-500 focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-secondary-500 transition-all duration-300 hover:shadow-lg">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-xl bg-secondary-100 flex items-center justify-center group-hover:bg-secondary-200 transition-colors">
                                <svg class="h-6 w-6 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="absolute inset-0" aria-hidden="true"></span>
                            <p class="text-lg font-medium text-gray-900">View All Tasks</p>
                            <p class="text-sm text-gray-500">See your complete task list</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white/90 backdrop-blur-md border-t border-gray-200 mt-8 animate-fade-in">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="text-center text-sm text-gray-500">
                <p>&copy; <?php echo date('Y'); ?> TaskReminder. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Alpine.js for dropdown functionality -->
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
