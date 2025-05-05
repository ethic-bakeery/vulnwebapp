<?php
session_start();

$log_file = '/var/log/securecorp_access.log';
if (!is_writable('/var/log')) {
    $log_file = __DIR__ . '/securecorp_access.log';
}

// Function to write logs with error handling
function write_log($log_file, $message) {
    $timestamp = date('[Y-m-d H:i:s]');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
    $uri = $_SERVER['REQUEST_URI'] ?? 'UNKNOWN';

    $log_entry = "$timestamp $ip $method $uri | $message\n";

    $result = @file_put_contents($log_file, $log_entry, FILE_APPEND);
    if ($result === false) {
        error_log("Failed to write to log file: $log_file");
        // Try creating the file if it doesn't exist
        if (!file_exists($log_file)) {
            @file_put_contents($log_file, "");
            @chmod($log_file, 0664);
            @file_put_contents($log_file, $log_entry, FILE_APPEND);
        }
    }
}

// Hardcoded credentials
$valid_username = 'admin';
$valid_password = 'password123';

write_log($log_file, "Login page accessed");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        
        write_log($log_file, "Login Attempt: username='$username' | Login Status: SUCCESS");
        
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Invalid credentials!";
        write_log($log_file, "Login Attempt: username='$username' | Login Status: FAILED");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureCorp Portal</title>
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --light: #ecf0f1;
            --danger: #e74c3c;
            --success: #2ecc71;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .login-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo h1 {
            color: var(--primary);
            margin: 0;
            font-size: 1.8rem;
        }
        
        .logo span {
            color: var(--secondary);
        }
        
        .logo p {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--primary);
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--secondary);
        }
        
        .btn {
            display: inline-block;
            background: var(--secondary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            width: 100%;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #2980b9;
        }
        
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
        }
        
        .alert-danger {
            background: #fadbd8;
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
        
        .footer {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.8rem;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <h1>Secure<span>Corp</span></h1>
                <p>Enterprise Security Portal</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Corporate ID</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter your corporate ID" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="btn">Sign In</button>
            </form>
            
            <div class="footer">
                <p>© 2023 SecureCorp. All rights reserved.</p>
                <p>v4.2.1</p>
            </div>
        </div>
    </div>
</body>
</html>
