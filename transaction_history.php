<?php
// transaction_history.php
session_start();
require_once 'config.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch transactions joined with properties to display transaction details
$stmt = $pdo->prepare("
    SELECT t.transaction_id, t.transaction_type, t.amount, t.transaction_date, t.reference_number, p.title 
    FROM transactions t 
    JOIN properties p ON t.property_id = p.property_id 
    WHERE t.buyer_id = ? 
    ORDER BY t.transaction_date DESC
");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Transactions - EstateMarket</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: #f8f9fa; }
        .navbar { background-color: #343a40; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar .logo { color: #fff; font-size: 22px; font-weight: bold; text-decoration: none; }
        .nav-links { list-style: none; display: flex; margin: 0; padding: 0; }
        .nav-links li { margin-left: 20px; }
        .nav-links a { color: #f8f9fa; text-decoration: none; font-size: 16px; }
        .nav-links a:hover { color: #ffc107; }

        .container { max-width: 900px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 15px; border-bottom: 1px solid #dee2e6; text-align: left; }
        th { background-color: #f8f9fa; }
        
        /* Clickable row styles */
        tbody tr { cursor: pointer; transition: background-color 0.2s; }
        tbody tr:hover { background-color: #e9ecef; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo">🏠 EstateMarket</a>
        <ul class="nav-links">
            <li><a href="buy.php">Buy</a></li>
            <li><a href="rent.php">Rent</a></li>
            <li><a href="list_property.php">List Property</a></li>
            <li><a href="transaction_history.php" style="color: #ffc107;">My Transactions</a></li>
            <li><a href="client_inquiries.php">My Messages</a></li>
            <li><a href="logout.php" style="color: #dc3545; font-weight: bold;">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>My Transactions</h1>
        <?php if (empty($transactions)): ?>
            <p>You have no transaction history available.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Property</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Ref #</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $t): ?>
                        <tr onclick="window.location='transaction_details.php?id=<?= $t['transaction_id'] ?>';">
                            <td><?= htmlspecialchars($t['title']) ?></td>
                            <td><?= htmlspecialchars($t['transaction_type']) ?></td>
                            <td>$<?= number_format($t['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($t['reference_number']) ?></td>
                            <td><?= date('M d, Y', strtotime($t['transaction_date'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</body>
</html>