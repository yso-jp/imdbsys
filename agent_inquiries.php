<?php
// agent_inquiries.php
session_start();
require_once 'config.php';

// Ensure the agent is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Agent') {
    header("Location: login.php");
    exit;
}

$agent_id = $_SESSION['user_id'];

// Query: Fetch conversations where this agent is a participant
$sql = "SELECT c.id AS conv_id, p.title AS property_title, 
               CONCAT(u.first_name, ' ', u.last_name) AS client_name,
               (SELECT message FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) AS last_message
        FROM conversations c
        JOIN properties p ON c.property_id = p.property_id
        JOIN conversation_participants cp ON c.id = cp.conversation_id
        JOIN users u ON cp.user_id = u.user_id
        WHERE cp.agent_id = ?
        ORDER BY c.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$agent_id]);
$conversations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Messages - Agent Portal</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f9; margin: 0; padding: 0; }
        .navbar { background: #2c3e50; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .nav-links a { color: #ecf0f1; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 4px; }
        .nav-links a:hover { background: #34495e; color: #2ecc71; }
        
        .container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .conv-card { 
            background: #fff; padding: 20px; margin-bottom: 15px; border-radius: 8px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); text-decoration: none; color: #333; 
            display: block; transition: 0.2s; border-left: 5px solid #2ecc71;
        }
        .conv-card:hover { background: #f9fdfa; border-left: 5px solid #27ae60; }
        .client-name { font-weight: bold; color: #2c3e50; font-size: 1.1em; }
        .prop-title { color: #e67e22; font-weight: 600; font-size: 0.9em; }
        .msg-preview { color: #666; font-size: 14px; margin-top: 8px; font-style: italic; }
    </style>
</head>
<body>
    <nav class="navbar">
        <a href="agent_dashboard.php" class="logo">💼 Agent Hub</a>
        <div class="nav-links">
            <a href="add_property.php">➕ Add Property</a>
            <a href="my_property.php">🏡 My Properties</a>
            <a href="agent_inquiries.php" style="background: #34495e; color: #2ecc71;">📩 My Messages</a>
            <a href="agent_transaction_history.php">💰 My Transactions</a>
            <a href="logout.php">🚪 Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1>Active Conversations</h1>
        <?php if (empty($conversations)): ?>
            <p>No active conversations at this time.</p>
        <?php else: ?>
            <?php foreach ($conversations as $row): ?>
                <a href="conversation.php?conv_id=<?= $row['conv_id'] ?>" class="conv-card">
                    <div class="client-name"><?= htmlspecialchars($row['client_name']) ?></div>
                    <div class="prop-title">Regarding: <?= htmlspecialchars($row['property_title']) ?></div>
                    <div class="msg-preview">
                        <?= !empty($row['last_message']) ? '"' . substr(htmlspecialchars($row['last_message']), 0, 100) . '..."' : 'No messages yet.' ?>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>