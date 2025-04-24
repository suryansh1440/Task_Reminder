<?php
// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'task';

try {
    // Create connection without database
    $conn = new PDO("mysql:host=$host", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $conn->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    echo "Database created successfully<br>";
    
    // Select the database
    $conn->exec("USE $dbname");
    
    // Create users table
    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    echo "Users table created successfully<br>";
    
    // Create tasks table
    $conn->exec("CREATE TABLE IF NOT EXISTS tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        due_date DATE NOT NULL,
        reminder_time TIME,
        priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
        status ENUM('pending', 'completed', 'overdue') DEFAULT 'pending',
        last_notification TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "Tasks table created successfully<br>";
    
    // Create notifications table
    $conn->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        task_id INT NOT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
    )");
    echo "Notifications table created successfully<br>";
    
    // Check if demo user exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute(['demo@example.com']);
    $userExists = $stmt->fetchColumn();
    
    if (!$userExists) {
        // Insert demo user (password: demo123)
        $hashedPassword = password_hash('demo123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute(['Demo User', 'demo@example.com', $hashedPassword]);
        echo "Demo user created successfully<br>";
        
        // Insert demo tasks
        $userId = $conn->lastInsertId();
        $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description, due_date, reminder_time, priority, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $demoTasks = [
            [
                $userId,
                'Complete Project Documentation',
                'Finish writing the project documentation and submit it',
                date('Y-m-d', strtotime('+2 days')),
                '09:00:00',
                'high',
                'pending'
            ],
            [
                $userId,
                'Team Meeting',
                'Weekly team meeting to discuss progress',
                date('Y-m-d', strtotime('+1 day')),
                '14:30:00',
                'medium',
                'pending'
            ],
            [
                $userId,
                'Review Code Changes',
                'Review and approve pull requests',
                date('Y-m-d'),
                '16:00:00',
                'low',
                'pending'
            ]
        ];
        
        foreach ($demoTasks as $task) {
            $stmt->execute($task);
        }
        echo "Demo tasks created successfully<br>";
    } else {
        echo "Demo user already exists<br>";
    }
    
    echo "<br><strong>Setup completed successfully!</strong><br>";
    echo "You can now log in with:<br>";
    echo "Email: demo@example.com<br>";
    echo "Password: demo123<br>";
    echo "<br><a href='auth/login.php'>Go to Login Page</a>";
    
} catch(PDOException $e) {
    echo "<strong>Error:</strong> " . $e->getMessage() . "<br>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        a:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Task Reminder System - Database Setup</h1>
</body>
</html> 