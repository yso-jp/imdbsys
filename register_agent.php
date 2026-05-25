<?php
// register_agent.php
require_once 'config.php';

// Ensure the session is initialized safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $agency_name = trim($_POST['agency_name']);
    $password = $_POST['password'];

    if (!empty($first_name) && !empty($last_name) && !empty($email) && !empty($phone) && !empty($password)) {
        // Enforce secure password hashing
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            // Note: Ensure ALTER TABLE agents ADD COLUMN password_hash VARCHAR(255) NOT NULL; has been executed.
            $stmt = $pdo->prepare("INSERT INTO agents (first_name, last_name, email, phone, agency_name, approval_status, password_hash) VALUES (?, ?, ?, ?, ?, 'Pending', ?)");
            $stmt->execute([
                $first_name, 
                $last_name, 
                $email, 
                $phone, 
                !empty($agency_name) ? $agency_name : null,
                $password_hash
            ]);
            
            $success = "Application submitted! Your agent profile is now <strong>Pending Approval</strong> by system administrators.";
        } catch (\PDOException $e) {
            // Check standard SQLSTATE error code '23000' or MySQL specific duplicate code 1062
            if ($e->getCode() == '23000' || $e->errorInfo[1] == 1062) {
                $error = "This email address is already registered to an agent profile.";
            } else {
                $error = "Database failure: " . htmlspecialchars($e->getMessage());
            }
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agent Registration - EstateMarket</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px 0; }
        .form-container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { margin-bottom: 5px; color: #28a745; text-align: center; }
        p.subtitle { text-align: center; color: #6c757d; font-size: 14px; margin-bottom: 20px; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #28a745; border: none; color: white; font-size: 16px; border-radius: 4px; cursor: pointer; margin-top: 10px; }
        button:hover { background: #218838; }
        .error { color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
        .success { color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
        .link { text-align: center; margin-top: 15px; font-size: 14px; }
        .link a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Partner Registration</h2>
        <p class="subtitle">Create an agent profile to list properties on our marketplace.</p>
        
        <?php if($error): ?> <div class="error"><?= $error ?></div> <?php endif; ?>
        <?php if($success): ?> <div class="success"><?= $success ?></div> <?php endif; ?>
        
        <form action="register_agent.php" method="POST">
            <input type="text" name="first_name" placeholder="First Name" required>
            <input type="text" name="last_name" placeholder="Last Name" required>
            <input type="email" name="email" placeholder="Business Email Address" required>
            <input type="text" name="phone" placeholder="Contact Phone Number" required>
            <input type="text" name="agency_name" placeholder="Brokerage / Agency Affiliation Name">
            <input type="password" name="password" placeholder="Create Password" required>
            
            <button type="submit">Submit Agent Application</button>
        </form>
        <div class="link">Already have an account? <a href="login.php">Login here</a></div>
    </div>
</body>
</html>