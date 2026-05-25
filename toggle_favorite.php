<?php
// toggle_favorite.php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$property_id = intval($_GET['id']);

// Check if it's already a favorite
$stmt = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id = ? AND property_id = ?");
$stmt->execute([$user_id, $property_id]);

if ($stmt->fetch()) {
    // Already favorited, so remove it
    $del = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND property_id = ?");
    $del->execute([$user_id, $property_id]);
} else {
    // Not favorited, so add it
    $ins = $pdo->prepare("INSERT INTO favorites (user_id, property_id) VALUES (?, ?)");
    $ins->execute([$user_id, $property_id]);
}

header("Location: full_details.php?id=" . $property_id);
exit;