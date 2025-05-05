php
<?php
session_start();

$log_file = '/var/log/securecorp_access.log';
if (!is_writable('/var/log')) {
    $log_file = __DIR__ . '/securecorp_access.log';
}

// Function to write logs (same as in index.php)
function write_log($log_file, $message) {
    $timestamp = date('[Y-m-d H:i:s]');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
    $uri = $_SERVER['REQUEST_URI'] ?? 'UNKNOWN';

    $log_entry = "$timestamp $ip $method $uri | $message\n";

    $result = @file_put_contents($log_file, $log_entry, FILE_APPEND);
    if ($result === false) {
        error_log("Failed to write to log file: $log_file");
    }
}

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    write_log($log_file, "Unauthorized access attempt to dashboard");
    header('Location: index.php');
    exit;
}

write_log($log_file, "Dashboard accessed by " . $_SESSION['username']);

// File traversal vulnerability
if (isset($_GET['view'])) {
    $file_path = $_GET['view'];
    // No path sanitization - vulnerable to traversal
    if (file_exists($file_path)) {
        $file_content = file_get_contents($file_path);
        write_log($log_file, "File viewed: $file_path");
    } else {
        $file_error = "File not found!";
        write_log($log_file, "File view attempt failed: $file_path");
    }
}

// Download secret file
if (isset($_GET['download'])) {
    $file = 'secret.txt';
    if (file_exists($file)) {
        write_log($log_file, "File downloaded: $file");
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    write_log($log_file, "User logged out: " . $_SESSION['username']);
    session_destroy();
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureCorp Dashboard</title>
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
            margin: 0;
            padding: 0;
            color: #333;
            background-color: #f5f7fa;
        }
        
        .header {
            background: var(--primary);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .logo span {
            color: var(--secondary);
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-menu a {
            color: white;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .container {
            display: flex;
            min-height: calc(100vh - 60px);
        }
        
        .sidebar {
            width: 250px;
            background: white;
            padding: 1.5rem;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
        }
        
        .nav-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .nav-item a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-item.active a {
            color: var(--secondary);
        }
        
        .main-content {
            flex: 1;
            padding: 2rem;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-title {
            margin: 0;
            color: var(--primary);
        }
        
        .btn {
            display: inline-block;
            background: var(--secondary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #2980b9;
        }
        
        .btn-danger {
            background: var(--danger);
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .search-form {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .search-input {
            flex: 1;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .search-btn {
            padding: 0.5rem 1rem;
        }
        
        pre {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            border: 1px solid #eee;
            overflow-x: auto;
            font-family: 'Courier New', Courier, monospace;
        }
        
        .welcome-message {
            font-size: 1.2rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }
        
        .file-list {
            list-style: none;
            padding: 0;
        }
        
        .file-item {
            padding: 0.75rem;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }
        
        .file-item:last-child {
            border-bottom: none;
        }
        
        .alert {
            padding: 0.75rem 1rem;
            border-radius: 4px;
            margin-top: 1rem;
            background: #fadbd8;
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">Secure<span>Corp</span> Portal</div>
        <div class="user-menu">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="index.php?logout=1">Sign Out</a>
        </div>
    </div>
    
    <div class="container">
        <div class="sidebar">
            <div class="nav-item active">
                <a href="dashboard.php">Dashboard</a>
            </div>
            <div class="nav-item">
                <a href="#">User Management</a>
            </div>
            <div class="nav-item">
                <a href="#">Security Settings</a>
            </div>
            <div class="nav-item">
                <a href="#">Audit Logs</a>
            </div>
            <div class="nav-item">
                <a href="#">System Tools</a>
            </div>
        </div>
        
        <div class="main-content">
            <div class="welcome-message">
                Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>! Welcome to the SecureCorp Admin Portal.
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Confidential Documents</h2>
                    <a href="dashboard.php?download=1" class="btn">Download Secret File</a>
                </div>
                <p>This file contains sensitive corporate information. Download with caution.</p>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">File Viewer</h2>
                </div>
                <p>View system files by entering the path below. Useful for checking configuration files.</p>
                
                <form method="GET" class="search-form">
                    <input type="text" name="view" class="search-input" placeholder="Enter file path " value="<?php echo isset($_GET['view']) ? htmlspecialchars($_GET['view']) : ''; ?>">
                    <button type="submit" class="btn search-btn">View File</button>
                </form>
                
                <?php if (isset($file_error)): ?>
                    <div class="alert"><?php echo htmlspecialchars($file_error); ?></div>
                <?php elseif (isset($file_content)): ?>
                    <h3 style="margin-top: 1.5rem;">File Content:</h3>
                    <pre><?php echo htmlspecialchars($file_content); ?></pre>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
