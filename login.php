<?php
// login.php
session_start(); // Initialized to allow consistent session monitoring across roles

require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        
        // 1. Check the regular 'users' table first
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_type'] = 'User';

            // Log activity
            $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_type, associated_id, action, description, ip_address) VALUES ('User', ?, 'Login', 'User logged in successfully', ?)");
            $log_stmt->execute([$user['user_id'], $_SERVER['REMOTE_ADDR']]);

            header("Location: index.php");
            exit;
        } 
        
        // 2. If no valid user found, check the 'agents' table
        $stmt = $pdo->prepare("SELECT * FROM agents WHERE email = ?");
        $stmt->execute([$email]);
        $agent = $stmt->fetch();

        // Check password and verify they have been approved by an admin
        if ($agent && password_verify($password, $agent['password_hash'])) {
            if ($agent['approval_status'] === 'Approved') {
                $_SESSION['user_id'] = $agent['agent_id'];
                $_SESSION['user_name'] = $agent['first_name'] . ' ' . $agent['last_name'];
                $_SESSION['user_type'] = 'Agent';

                // Log activity
                $log_stmt = $pdo->prepare("INSERT INTO activity_logs (user_type, associated_id, action, description, ip_address) VALUES ('Agent', ?, 'Login', 'Agent logged in successfully', ?)");
                $log_stmt->execute([$agent['agent_id'], $_SERVER['REMOTE_ADDR']]);

                // 🔄 Redirect approved agents directly to their personal workspace dashboard
                header("Location: agent_dashboard.php");
                exit;
            } else if ($agent['approval_status'] === 'Pending') {
                $error = "Your agent profile is pending admin approval.";
            } else {
                $error = "Your agent application was rejected.";
            }
        } else if (empty($error)) {
            // If it wasn't a valid user and wasn't a valid agent, trigger fallback credentials error
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Please complete all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Real Estate Marketplace</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        
        /* Added relative positioning to parent container to anchor the Admin link */
        .form-container { 
            position: relative; 
            background: #fff; 
            padding: 40px 30px 30px 30px; /* Increased top padding to look clean with the link */
            border-radius: 8px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.1); 
            width: 100%; 
            max-width: 400px; 
        }
        
        /* Admin Login link pinned to the upper right */
        .admin-corner {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 12px;
        }
        .admin-corner a {
            color: #6c757d;
            text-decoration: none;
            font-weight: 600;
            background: #f8f9fa;
            padding: 4px 8px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
            transition: all 0.2s;
        }
        .admin-corner a:hover {
            color: #dc3545;
            background: #fff5f5;
            border-color: #fab6b6;
        }

        h2 { margin-bottom: 20px; color: #333; text-align: center; }
        input[type="email"], input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #007bff; border: none; color: white; font-size: 16px; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0069d9; }
        .error { color: red; margin-bottom: 10px; text-align: center; font-size: 14px; }
        .link-group { text-align: center; margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px; }
        .link-group div { margin-bottom: 8px; font-size: 14px; }
        .link-group a { color: #007bff; text-decoration: none; font-weight: bold; }
        .link-group a:hover { text-decoration: underline; }
        .agent-link { color: #28a745 !important; }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="admin-corner">
            <a href="admin_dashboard.php">🛠️ Admin Login</a>
        </div>

        <h2>Login</h2>
        <?php if($error): ?> <div class="error"><?= $error ?></div> <?php endif; ?>
        
        <form action="login.php" method="POST">
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        
        <div class="link-group">
            <div>Don't have an account? <a href="register.php">Register here</a></div>
            <div>Are you a real estate professional? <a href="register_agent.php" class="agent-link">Register as an Agent</a></div>
        </div>
    </div>
</body>
</html>