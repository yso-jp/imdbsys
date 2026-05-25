<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$buyer_id = $_SESSION['user_id'];
$property_id = $_POST['property_id'];
$agent_id = $_POST['agent_id'];
$type = $_POST['transaction_type']; // Sale or Rent
$amount = $_POST['amount'];
$ref = 'TXN-' . strtoupper(uniqid());

$success = false;
$error_message = '';

try {
    $pdo->beginTransaction();

    // 1. Insert Transaction
    $stmt = $pdo->prepare("INSERT INTO transactions (property_id, buyer_id, agent_id, transaction_type, amount, reference_number) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$property_id, $buyer_id, $agent_id, $type, $amount, $ref]);

    // 2. Update Property Status
    $new_status = ($type === 'Sale') ? 'Sold' : 'Rented';
    $update = $pdo->prepare("UPDATE properties SET status = ? WHERE property_id = ?");
    $update->execute([$new_status, $property_id]);

    $pdo->commit();
    $success = true;
} catch (Exception $e) {
    $pdo->rollBack();
    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction Result - EstateMarket</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: #f8f9fa; }
        .navbar { background-color: #343a40; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar .logo { color: #fff; font-size: 22px; font-weight: bold; text-decoration: none; }
        .nav-links { list-style: none; display: flex; margin: 0; padding: 0; }
        .nav-links li { margin-left: 20px; }
        .nav-links a { color: #f8f9fa; text-decoration: none; font-size: 16px; }
        
        .container { max-width: 600px; margin: 50px auto; padding: 30px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .btn { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo">🏠 EstateMarket</a>
        <ul class="nav-links">
            <li><a href="client_profile.php">👤 Profile</a></li>
            <li><a href="buy.php">Buy</a></li>
            <li><a href="rent.php">Rent</a></li>
            <li><a href="transaction_history.php">My Transactions</a></li>
            <li><a href="client_inquiries.php">My Messages</a></li>
            <li><a href="logout.php" style="color: #dc3545; font-weight: bold;">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <?php if ($success): ?>
            <h1 class="success">✅ Transaction Successful!</h1>
            <p>Your request for this property has been processed.</p>
            <p><strong>Reference Number:</strong> <?= $ref ?></p>
        <?php else: ?>
            <h1 class="error">❌ Transaction Failed</h1>
            <p><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>
        <a href="index.php" class="btn">Return to Home</a>
    </div>

</body>
</html>