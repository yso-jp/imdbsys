<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['conv_id'])) {
    $sender_type = ($_SESSION['user_type'] === 'Agent') ? 'Agent' : 'User';
    $stmt = $pdo->prepare("INSERT INTO messages (conversation_id, sender_type, sender_id, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_GET['conv_id'], $sender_type, $_SESSION['user_id'], $_POST['message']]);
}
header("Location: conversation.php?conv_id=" . $_GET['conv_id']);