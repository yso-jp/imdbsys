<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'] ?? 'User';
$conv_id = isset($_GET['conv_id']) ? intval($_GET['conv_id']) : null;

// Handle New Conversation Initiation
if (isset($_POST['message']) && isset($_GET['pid'])) {
    $pid = intval($_GET['pid']);
    $aid = intval($_GET['aid']);
    $msg = $_POST['message'];

    $pdo->prepare("INSERT INTO conversations (property_id) VALUES (?)")->execute([$pid]);
    $conv_id = $pdo->lastInsertId();

    $pdo->prepare("INSERT INTO conversation_participants (conversation_id, user_id, agent_id) VALUES (?, ?, ?)")
        ->execute([$conv_id, $user_id, $aid]);

    $pdo->prepare("INSERT INTO messages (conversation_id, sender_type, sender_id, message) VALUES (?, ?, ?, ?)")
        ->execute([$conv_id, $user_type, $user_id, $msg]);

    header("Location: conversation.php?conv_id=" . $conv_id);
    exit;
}

// Fetch Messages
$messages = [];
if ($conv_id) {
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE conversation_id = ? ORDER BY created_at ASC");
    $stmt->execute([$conv_id]);
    $messages = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Messenger - EstateMarket</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; background: #f8f9fa; }
        .navbar { background: #343a40; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar .logo { color: #fff; font-size: 22px; font-weight: bold; text-decoration: none; }
        .nav-links { display: flex; gap: 20px; list-style: none; margin: 0; padding: 0; }
        .nav-links a { color: #ecf0f1; text-decoration: none; font-size: 14px; font-weight: 600; }
        .nav-links a:hover { color: #ffc107; }

        .chat-container { max-width: 600px; margin: 40px auto; background: #fff; border: 1px solid #dee2e6; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .message-wrapper { display: flex; flex-direction: column; margin-bottom: 15px; }
        .msg-self { align-items: flex-end; }
        .msg-self .bubble { background: #007bff; color: white; border-radius: 15px 15px 0 15px; }
        .msg-other { align-items: flex-start; }
        .msg-other .bubble { background: #e9ecef; color: #333; border-radius: 15px 15px 15px 0; }
        .bubble { padding: 12px 16px; max-width: 75%; font-size: 15px; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="<?= $user_type === 'Agent' ? 'agent_dashboard.php' : 'index.php' ?>" class="logo">🏠 EstateMarket</a>
        <ul class="nav-links">
            <?php if ($user_type === 'Agent'): ?>
                <li><a href="agent_dashboard.php">Dashboard</a></li>
                <li><a href="agent_inquiries.php">My Messages</a></li>
                <li><a href="agent_transaction_history.php">My Transactions</a></li>
            <?php else: ?>
            <li><a href="client_profile.php">👤 Profile</a></li>
            <li><a href="buy.php">Buy</a></li>
            <li><a href="rent.php">Rent</a></li>
            <li><a href="transaction_history.php">My Transactions</a></li>
            <li><a href="client_inquiries.php">My Messages</a></li>
            <?php endif; ?>
            <li><a href="logout.php" style="color: #dc3545;">Logout</a></li>
        </ul>
    </nav>

    <div class="chat-container">
        <div style="margin-bottom: 20px;">
            <a href="<?= $user_type === 'Agent' ? 'agent_inquiries.php' : 'client_inquiries.php' ?>" 
               style="text-decoration: none; color: #6c757d; font-size: 14px; font-weight: bold;">
               &larr; Back to My Messages
            </a>
        </div>

        <?php foreach ($messages as $m): ?>
            <?php $isSelf = ($m['sender_id'] == $user_id && $m['sender_type'] == $user_type); ?>
            <div class="message-wrapper <?= $isSelf ? 'msg-self' : 'msg-other' ?>">
                <div class="bubble">
                    <?= htmlspecialchars($m['message']) ?>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if ($conv_id): ?>
            <form method="POST" action="send_message.php?conv_id=<?= $conv_id ?>">
                <textarea name="message" required style="width:100%; height:80px; margin-top:15px; padding: 10px; border: 1px solid #ccc; border-radius: 4px;"></textarea>
                <button type="submit" style="margin-top:10px; width:100%; padding:10px; background:#28a745; color:white; border:none; border-radius:4px; cursor:pointer;">Send Message</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>