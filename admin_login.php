<?php
// admin_login.php

// 1. Initialize session monitoring
session_start();

// 2. If already logged in as Admin, redirect straight to the dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Admin') {
    header("Location: admin_dashboard.php");
    exit();
}

// 3. Import global database connection module
require_once 'config.php'; 

$error = '';

// 4. Handle authentication payload submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Basic Validation Check
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        try {
            // Locate administrative account matching exact email criteria
            $stmt = $pdo->prepare('SELECT admin_id, username, email, password_hash FROM admins WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $admin = $stmt->fetch();

            // Corrected: Use password_verify to match the seeded BCRYPT hash pattern from the database schema
            if ($admin && password_verify($password, $admin['password_hash'])) {
                
                // Regenerate ID keys to neutralize session hijacking tracking vulnerabilities
                session_regenerate_id(true);

                // Populate security parameters required by dashboard authorization layers
                $_SESSION['user_id']     = $admin['admin_id'];
                $_SESSION['user_name']   = $admin['username'];
                $_SESSION['user_email']  = $admin['email'];
                $_SESSION['user_type']   = 'Admin';

                // Optional Audit Logging Action
                try {
                    $log_stmt = $pdo->prepare('INSERT INTO activity_logs (user_type, associated_id, action, description, ip_address) VALUES (?, ?, ?, ?, ?)');
                    $log_stmt->execute(['Admin', $admin['admin_id'], 'Login', 'Admin portal authentication successful.', $_SERVER['REMOTE_ADDR']]);
                } catch (\PDOException $log_error) {
                    // Fail silently to keep application running smoothly if log tables match uniquely during dev updates
                }

                // Transfer routing path directly to the master terminal dashboard hub
                header("Location: admin_dashboard.php");
                exit();
            } else {
                // Return access failure error message
                $error = "Invalid email or password.";
            }
        } catch (\PDOException $e) {
            $error = "Ecosystem Authentication Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal Login - EstateMarket</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #ffffff;
            padding: 35px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            width: 100%;
            max-width: 400px;
            border-top: 5px solid #212529;
        }
        h2 {
            margin-bottom: 5px;
            color: #212529;
            text-align: center;
            font-size: 24px;
        }
        p.desc {
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            margin-top: 0;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #495057;
            font-weight: 600;
            font-size: 14px;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
            transition: border-color 0.15s ease-in-out;
        }
        input[type="email"]:focus, input[type="password"]:focus {
            border-color: #007bff;
            outline: none;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: #212529;
            border: none;
            border-radius: 4px;
            color: #ffc107;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 10px;
        }
        .btn-submit:hover {
            background-color: #343a40;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            font-size: 14px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Admin Management Portal</h2>
    <p class="desc">Please authenticate to gain structural node privileges.</p>

    <?php if (!empty($error)): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="admin_login.php" method="POST">
        <div class="form-group">
            <label for="email">Administrative Email Address</label>
            <input type="email" id="email" name="email" required autocomplete="email" placeholder="e.g., admin@estate.com">
        </div>

        <div class="form-group">
            <label for="password">Password Key</label>
            <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••">
        </div>

        <button type="submit" class="btn-submit">Authenticate Terminal</button>
    </form>
</div>

</body>
</html>