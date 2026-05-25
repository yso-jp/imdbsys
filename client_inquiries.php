<?php
// client_inquiries.php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Query: Fetch conversations for this user, including the last message and timestamp for sorting
$sql = "SELECT c.id AS conv_id, p.title AS property_title, 
               CONCAT(a.first_name, ' ', a.last_name) AS agent_name,
               (SELECT message FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) AS last_message,
               (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) AS last_updated
        FROM conversations c
        JOIN properties p ON c.property_id = p.property_id
        JOIN conversation_participants cp ON c.id = cp.conversation_id
        JOIN agents a ON cp.agent_id = a.agent_id
        WHERE cp.user_id = ?
        ORDER BY last_updated DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$conversations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Messages - EstateMarket</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; margin: 0; padding: 0; }
        .navbar { background-color: #343a40; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar .logo { color: #fff; font-size: 22px; font-weight: bold; text-decoration: none; }
        .nav-links { list-style: none; display: flex; margin: 0; padding: 0; }
        .nav-links li { margin-left: 20px; }
        .nav-links a { color: #f8f9fa; text-decoration: none; }
        
        .container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .conv-card { 
            background: white; padding: 20px; margin-bottom: 15px; border-radius: 8px; 
            border-left: 5px solid #007bff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
            display: block; text-decoration: none; color: #333; transition: 0.2s;
        }
        .conv-card:hover { background: #f8f9ff; border-left: 5px solid #0056b3; }
        .msg-preview { color: #666; font-size: 14px; margin-top: 8px; font-style: italic; }
        .timestamp { font-size: 12px; color: #999; float: right; }
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
        <h1>My Messages</h1>
        
        <?php if (empty($conversations)): ?>
            <p>You haven't started any conversations yet.</p>
        <?php else: ?>
            <?php foreach ($conversations as $row): ?>
                <a href="conversation.php?conv_id=<?= $row['conv_id'] ?>" class="conv-card">
                    <span class="timestamp"><?= date('M d, g:i A', strtotime($row['last_updated'])) ?></span>
                    <h3 style="margin:0;"><?= htmlspecialchars($row['property_title']) ?></h3>
                    <div style="font-size: 0.9em; color: #555;">Agent: <?= htmlspecialchars($row['agent_name']) ?></div>
                    <div class="msg-preview">
                        <?= !empty($row['last_message']) ? '"' . substr(htmlspecialchars($row['last_message']), 0, 70) . '..."' : 'No messages yet.' ?>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>