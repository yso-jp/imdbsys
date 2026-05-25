<?php
// transaction_details.php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$txn_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch transaction details with associated Property and Agent info
$stmt = $pdo->prepare("
    SELECT t.*, p.title, p.address, p.city, p.price, 
           a.first_name AS agent_first, a.last_name AS agent_last, a.phone, a.email
    FROM transactions t
    JOIN properties p ON t.property_id = p.property_id
    JOIN agents a ON t.agent_id = a.agent_id
    WHERE t.transaction_id = ? AND t.buyer_id = ?
");
$stmt->execute([$txn_id, $_SESSION['user_id']]);
$txn = $stmt->fetch();

if (!$txn) {
    die("Transaction not found or access denied.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction Details - #<?= htmlspecialchars($txn['reference_number']) ?></title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; margin: 0; }
        .navbar { background-color: #343a40; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar .logo { color: #fff; font-size: 22px; font-weight: bold; text-decoration: none; }
        .nav-links { list-style: none; display: flex; margin: 0; padding: 0; }
        .nav-links li { margin-left: 20px; }
        .nav-links a { color: #f8f9fa; text-decoration: none; }
        .nav-links a:hover { color: #ffc107; }

        .container { max-width: 800px; margin: 40px auto; padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .section { margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
        h2 { color: #333; margin-top: 0; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .label { font-weight: bold; color: #666; }
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
        <h1>Transaction Details</h1>
        
        <div class="section">
            <h2>Property Information</h2>
            <p><strong>Title:</strong> <?= htmlspecialchars($txn['title']) ?></p>
            <p><strong>Address:</strong> <?= htmlspecialchars($txn['address']) ?>, <?= htmlspecialchars($txn['city']) ?></p>
        </div>

        <div class="section">
            <h2>Transaction Summary</h2>
            <div class="info-grid">
                <div><span class="label">Reference #:</span> <?= htmlspecialchars($txn['reference_number']) ?></div>
                <div><span class="label">Type:</span> <?= htmlspecialchars($txn['transaction_type']) ?></div>
                <div><span class="label">Amount Paid:</span> $<?= number_format($txn['amount'], 2) ?></div>
                <div><span class="label">Date:</span> <?= date('F d, Y', strtotime($txn['transaction_date'])) ?></div>
            </div>
        </div>

        <div class="section">
            <h2>Agent Information</h2>
            <p><strong>Name:</strong> <?= htmlspecialchars($txn['agent_first'] . ' ' . $txn['agent_last']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($txn['phone']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($txn['email']) ?></p>
        </div>

        <a href="transaction_history.php">← Back to History</a>
    </div>
</body>
</html>