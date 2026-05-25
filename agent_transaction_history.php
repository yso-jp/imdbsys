<?php
// agent_transaction_history.php
session_start();
require_once 'config.php';

// Security: Ensure only agents can access this
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Agent') {
    header("Location: login.php");
    exit;
}

$agent_id = $_SESSION['user_id'];

// Query: Fetch transactions for properties belonging to this agent
$stmt = $pdo->prepare("
    SELECT t.*, p.title AS property_title, u.first_name, u.last_name 
    FROM transactions t 
    JOIN properties p ON t.property_id = p.property_id 
    JOIN users u ON t.buyer_id = u.user_id 
    WHERE t.agent_id = ? 
    ORDER BY t.transaction_date DESC
");
$stmt->execute([$agent_id]);
$transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Transactions - Agent Portal</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; padding: 0; background: #f4f6f9; }
        
        /* Persistent Navbar */
        .navbar { background: #2c3e50; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .navbar .logo { font-size: 20px; font-weight: bold; color: #2ecc71; text-decoration: none; }
        .nav-links { display: flex; gap: 15px; }
        .nav-links a { color: #ecf0f1; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 4px; transition: all 0.2s; }
        .nav-links a:hover { background: #34495e; color: #2ecc71; }
        
        /* Main Container */
        .container { max-width: 1000px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        h1 { color: #2c3e50; }
        
        /* Table Styles */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; color: #7f8c8d; text-transform: uppercase; font-size: 13px; }
        
        /* Clickable row interaction */
        tbody tr { cursor: pointer; transition: background-color 0.2s ease; }
        tbody tr:hover { background-color: #e9ecef; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="agent_dashboard.php" class="logo">💼 Agent Hub</a>
        <div class="nav-links">
            <a href="add_property.php">➕ Add Property</a>
            <a href="my_property.php">🏡 My Properties</a>
            <a href="agent_inquiries.php">📩 My Inquiries</a>
            <a href="agent_transaction_history.php" style="background: #34495e; color: #2ecc71;">💰 My Transactions</a>
            <a href="logout.php">🚪 Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1>Transaction History</h1>
        <p>Review all closed sales and rentals for your listed properties.</p>
        
        <?php if (empty($transactions)): ?>
            <p style="text-align: center; color: #95a5a6; padding: 40px;">No completed transactions found in your portfolio.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $t): ?>
                        <tr onclick="window.location='agent_transaction_details.php?id=<?= $t['transaction_id'] ?>';">
                            <td><?= htmlspecialchars($t['property_title']) ?></td>
                            <td><?= htmlspecialchars($t['first_name'] . ' ' . $t['last_name']) ?></td>
                            <td><?= htmlspecialchars($t['transaction_type']) ?></td>
                            <td>$<?= number_format($t['amount'], 2) ?></td>
                            <td><?= date('M d, Y', strtotime($t['transaction_date'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</body>
</html>