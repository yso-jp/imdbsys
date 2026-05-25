<?php
// inquiry_detail.php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Agent') {
    header("Location: login.php");
    exit;
}

$inquiry_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$agent_id = $_SESSION['user_id'];

// Handle Reply Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reply'])) {
    $reply = trim($_POST['reply_message']);
    if (!empty($reply)) {
        $stmt = $pdo->prepare("INSERT INTO inquiry_replies (inquiry_id, agent_id, reply_message) VALUES (?, ?, ?)");
        $stmt->execute([$inquiry_id, $agent_id, $reply]);
        
        // Update inquiry status
        $pdo->prepare("UPDATE inquiries SET status = 'Replied' WHERE inquiry_id = ?")->execute([$inquiry_id]);
    }
}

// Fetch Inquiry Details
$stmt = $pdo->prepare("SELECT i.*, p.title, CONCAT(u.first_name, ' ', u.last_name) AS client_name, u.email 
                       FROM inquiries i 
                       JOIN properties p ON i.property_id = p.property_id 
                       JOIN users u ON i.user_id = u.user_id 
                       WHERE i.inquiry_id = ? AND i.agent_id = ?");
$stmt->execute([$inquiry_id, $agent_id]);
$inquiry = $stmt->fetch();

// Fetch existing replies
$replies = $pdo->prepare("SELECT * FROM inquiry_replies WHERE inquiry_id = ? ORDER BY created_at ASC");
$replies->execute([$inquiry_id]);
$conversation = $replies->fetchAll();

if (!$inquiry) die("Inquiry not found.");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Inquiry Details</title>
    <style>
        body { font-family: sans-serif; background: #f4f6f9; padding: 20px; }
        .card { max-width: 700px; margin: auto; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .message-box { background: #f1f3f5; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .reply-box { background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #2ecc71; }
        textarea { width: 100%; height: 80px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Inquiry: <?= htmlspecialchars($inquiry['title']) ?></h2>
        <p><strong>From:</strong> <?= htmlspecialchars($inquiry['client_name']) ?> (<?= htmlspecialchars($inquiry['email']) ?>)</p>
        <div class="message-box"><strong>Client Message:</strong><br><?= nl2br(htmlspecialchars($inquiry['message'])) ?></div>
        
        <hr>
        <h4>Conversation History</h4>
        <?php foreach ($conversation as $reply): ?>
            <div class="reply-box"><strong>Agent Reply:</strong><br><?= nl2br(htmlspecialchars($reply['reply_message'])) ?></div>
        <?php endforeach; ?>

        <form method="POST">
            <textarea name="reply_message" placeholder="Type your response here..." required></textarea>
            <button type="submit" name="submit_reply">Send Reply</button>
        </form>
        <br><a href="agent_inquiries.php">← Back to Inbox</a>
    </div>
</body>
</html>