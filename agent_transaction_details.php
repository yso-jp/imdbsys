<?php
// agent_transaction_details.php
session_start();
require_once 'config.php';

// Security: Ensure only agents can access this, and only for their own transactions
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Agent') {
    header("Location: login.php");
    exit;
}

$txn_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$agent_id = $_SESSION['user_id'];

// Fetch transaction details, joining properties for listing info and users for client info
$stmt = $pdo->prepare("
    SELECT t.*, p.title, p.address, p.city, u.first_name, u.last_name, u.email, u.phone 
    FROM transactions t
    JOIN properties p ON t.property_id = p.property_id
    JOIN users u ON t.buyer_id = u.user_id
    WHERE t.transaction_id = ? AND t.agent_id = ?
");
$stmt->execute([$txn_id, $agent_id]);
$txn = $stmt->fetch();

if (!$txn) {
    die("Transaction not found or you do not have permission to view it.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction Details - #<?= htmlspecialchars($txn['reference_number']) ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; margin: 0; }
        .navbar { background: #2c3e50; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar .logo { font-size: 20px; font-weight: bold; color: #2ecc71; text-decoration: none; }
        .nav-links { display: flex; gap: 15px; }
        .nav-links a { color: #ecf0f1; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 4px; }
        .nav-links a:hover { background: #34495e; color: #2ecc71; }
        
        .container { max-width: 800px; margin: 40px auto; padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .section { margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
        h2 { color: #2c3e50; font-size: 1.2em; margin-bottom: 15px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .label { display: block; font-size: 12px; color: #7f8c8d; text-transform: uppercase; margin-bottom: 5px; }
        .value { font-size: 16px; color: #333; font-weight: 600; }
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
        <h1>Transaction Record</h1>
        
        <div class="section">
            <h2>Property Details</h2>
            <div class="info-grid">
                <div><span class="label">Property Title</span><div class="value"><?= htmlspecialchars($txn['title']) ?></div></div>
                <div><span class="label">Location</span><div class="value"><?= htmlspecialchars($txn['address'] . ', ' . $txn['city']) ?></div></div>
            </div>
        </div>

        <div class="section">
            <h2>Financial & Reference Info</h2>
            <div class="info-grid">
                <div><span class="label">Amount</span><div class="value">$<?= number_format($txn['amount'], 2) ?></div></div>
                <div><span class="label">Type</span><div class="value"><?= htmlspecialchars($txn['transaction_type']) ?></div></div>
                <div><span class="label">Reference Number</span><div class="value"><?= htmlspecialchars($txn['reference_number']) ?></div></div>
                <div><span class="label">Date</span><div class="value"><?= date('M d, Y', strtotime($txn['transaction_date'])) ?></div></div>
            </div>
        </div>

        <div class="section">
            <h2>Client Information</h2>
            <div class="info-grid">
                <div><span class="label">Client Name</span><div class="value"><?= htmlspecialchars($txn['first_name'] . ' ' . $txn['last_name']) ?></div></div>
                <div><span class="label">Contact Email</span><div class="value"><?= htmlspecialchars($txn['email']) ?></div></div>
                <div><span class="label">Contact Phone</span><div class="value"><?= htmlspecialchars($txn['phone']) ?></div></div>
            </div>
        </div>

        <a href="agent_transaction_history.php" style="color: #2ecc71; text-decoration: none; font-weight: bold;">&larr; Back to History</a>
    </div>
</body>
</html>