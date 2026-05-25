<?php
// full_details.php
session_start();
require_once 'config.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$property_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch Property Details
$stmt = $pdo->prepare("
    SELECT p.*, a.first_name, a.last_name, a.phone, a.email, a.agent_id 
    FROM properties p 
    JOIN agents a ON p.agent_id = a.agent_id 
    WHERE p.property_id = ?
");
$stmt->execute([$property_id]);
$property = $stmt->fetch();

if (!$property) {
    die("Property not found.");
}

// Check for existing conversation to avoid duplicate threads
$stmtConv = $pdo->prepare("
    SELECT cp.conversation_id 
    FROM conversation_participants cp
    JOIN conversations c ON cp.conversation_id = c.id
    WHERE cp.user_id = ? AND c.property_id = ?
");
$stmtConv->execute([$user_id, $property_id]);
$existing_conv = $stmtConv->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($property['title']) ?> - Details</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: #f8f9fa; }
        .navbar { background-color: #343a40; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar .logo { color: #fff; font-size: 22px; font-weight: bold; text-decoration: none; }
        .nav-links { list-style: none; display: flex; margin: 0; padding: 0; }
        .nav-links li { margin-left: 20px; }
        .nav-links a { color: #f8f9fa; text-decoration: none; font-size: 16px; }
        .nav-links a:hover { color: #ffc107; }

        .container { max-width: 900px; margin: 30px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .price { font-size: 32px; color: #007bff; font-weight: bold; }
        .specs { display: flex; gap: 20px; margin: 20px 0; font-size: 16px; color: #555; }
        .action-box { display: flex; gap: 15px; margin-top: 30px; padding: 20px; background: #f1f3f5; border-radius: 8px; }
        .inquiry-form { flex: 2; }
        .transaction-form { flex: 1; display: flex; align-items: center; justify-content: center; border-left: 2px solid #ddd; }
        textarea { width: 100%; height: 80px; margin: 10px 0; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button.btn-success { background: #28a745; width: 100%; font-size: 16px; font-weight: bold; }
        .btn-chat { background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo">🏠 EstateMarket</a>
        <ul class="nav-links">
            <li><a href="buy.php">Buy</a></li>
            <li><a href="rent.php">Rent</a></li>
            <li><a href="list_property.php">List Property</a></li>
            <li><a href="transaction_history.php">My Transactions</a></li>
            <li><a href="client_inquiries.php">My Messages</a></li>
            <li><a href="logout.php" style="color: #dc3545; font-weight: bold;">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1><?= htmlspecialchars($property['title']) ?></h1>
        <div class="price">$<?= number_format($property['price'], 2) ?></div>
        <p>📍 <?= htmlspecialchars($property['address']) ?>, <?= htmlspecialchars($property['city']) ?></p>
        
        <div class="specs">
            <span>🛏️ <?= $property['bedrooms'] ?> Beds</span>
            <span>🛁 <?= $property['bathrooms'] ?> Baths</span>
            <span>🏷️ <?= htmlspecialchars($property['property_type']) ?></span>
            <span>💳 Type: <?= htmlspecialchars($property['listing_type']) ?></span>
        </div>

        <h3>Description</h3>
        <p><?= nl2br(htmlspecialchars($property['description'])) ?></p>

        <div class="action-box">
            <div class="inquiry-form">
                <h3><?= $existing_conv ? "Continue Conversation" : "Start a Conversation" ?></h3>
                <?php if ($existing_conv): ?>
                    <a href="conversation.php?conv_id=<?= $existing_conv['conversation_id'] ?>" class="btn-chat">View Existing Conversation</a>
                <?php else: ?>
                    <form action="conversation.php?pid=<?= $property['property_id'] ?>&aid=<?= $property['agent_id'] ?>" method="POST">
                        <textarea name="message" placeholder="Start a chat about this property..." required></textarea>
                        <button type="submit">Start Chat</button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="transaction-form">
                <form action="process_transaction.php" method="POST">
                    <input type="hidden" name="property_id" value="<?= $property['property_id'] ?>">
                    <input type="hidden" name="agent_id" value="<?= $property['agent_id'] ?>">
                    <input type="hidden" name="transaction_type" value="<?= $property['listing_type'] ?>">
                    <input type="hidden" name="amount" value="<?= $property['price'] ?>">
                    <button type="submit" class="btn-success">Proceed to <?= htmlspecialchars($property['listing_type']) ?></button>
                </form>
            </div>
        </div>
        <br>
        <a href="index.php">← Back to Search</a>
    </div>
</body>
</html>