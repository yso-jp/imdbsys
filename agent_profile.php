<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Agent') {
    header("Location: login.php");
    exit;
}

$agent_id = $_SESSION['user_id'];
$message = "";

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'];
    
    $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE user_id = ?");
    $stmt->execute([$_POST['first_name'], $_POST['last_name'], $phone, $agent_id]);
    $message = "Profile updated successfully!";
}

// Fetch current data
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$agent_id]);
$agent = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile - Agent Hub</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; margin: 0; }
        
        /* Persistent Navbar */
        .navbar { background: #2c3e50; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .navbar .logo { font-size: 20px; font-weight: bold; color: #2ecc71; text-decoration: none; }
        .nav-links { display: flex; gap: 15px; }
        .nav-links a { color: #ecf0f1; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 4px; }
        .nav-links a:hover { background: #34495e; color: #2ecc71; }
        
        /* Card Layout */
        .container { max-width: 500px; margin: 40px auto; padding: 20px; }
        .card { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h2 { margin-top: 0; color: #2c3e50; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        input { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .btn-group { margin-top: 25px; display: flex; gap: 10px; align-items: center; }
        button { background: #2ecc71; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .cancel-btn { color: #7f8c8d; text-decoration: none; font-size: 14px; }
        .alert { padding: 10px; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="agent_dashboard.php" class="logo">💼 Agent Hub</a>
        <div class="nav-links">
        <a href="agent_profile.php">👤 Profile</a>

            <a href="add_property.php">➕ Add Property</a>
            <a href="my_property.php">🏡 My Properties</a>
            <a href="agent_inquiries.php">📩 My Inquiries</a>
            <a href="agent_transaction_history.php">💰 My Transactions</a>
            <a href="logout.php">🚪 Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h2>Edit Profile</h2>
            <?php if ($message) echo "<div class='alert'>$message</div>"; ?>
            
            <form method="POST">
                <label>First Name</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($agent['first_name']) ?>" required>
                
                <label>Last Name</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($agent['last_name']) ?>" required>
                
                <label>Phone</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($agent['phone'] ?? '') ?>">
                
                <div class="btn-group">
                    <button type="submit">Save Changes</button>
                    <a href="agent_dashboard.php" class="cancel-btn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>