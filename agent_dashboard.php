<?php
session_start();
require_once 'config.php';

$agent_id = isset($_SESSION['user_id']) && ($_SESSION['user_type'] === 'Agent') ? $_SESSION['user_id'] : 1;
$agent_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Professional Agent';

// --- DATA PIPELINE ---
try {
    // 1. Most Viewed
    $stmt = $pdo->prepare("
        SELECT p.*, COUNT(v.view_id) AS total_clicks
        FROM properties p
        LEFT JOIN property_views v ON p.property_id = v.property_id
        WHERE p.agent_id = ?
        GROUP BY p.property_id ORDER BY total_clicks DESC LIMIT 1
    ");
    $stmt->execute([$agent_id]);
    $most_viewed = $stmt->fetch();

    // 2. Fetch ALL properties for the table
    $props_stmt = $pdo->prepare("SELECT * FROM properties WHERE agent_id = ? ORDER BY created_at DESC");
    $props_stmt->execute([$agent_id]);
    $all_properties = $props_stmt->fetchAll();

} catch (\PDOException $e) {
    $error_msg = "Database Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agent Dashboard - EstateMarket</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; background: #f4f6f9; color: #333; }
        .navbar { background: #2c3e50; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .navbar .logo { font-size: 20px; font-weight: bold; color: #2ecc71; text-decoration: none; }
        .nav-links { display: flex; gap: 15px; }
        .nav-links a { color: #ecf0f1; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 4px; }
        .nav-links a:hover { background: #34495e; color: #2ecc71; }
        
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); padding: 30px; margin-bottom: 30px; }
        
        /* Table Styles */
        .prop-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .prop-table th { background: #f8f9fa; padding: 12px; text-align: left; border-bottom: 2px solid #ddd; }
        .prop-table td { padding: 12px; border-bottom: 1px solid #eee; }
        .prop-table tr:hover { background: #f1f7fe; cursor: pointer; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="agent_dashboard.php" class="logo">💼 Agent Hub</a>
        <div class="nav-links">
            <!-- Inside <div class="nav-links"> -->
            <a href="agent_profile.php">👤 Profile</a>
            <a href="add_property.php">➕ Add</a>
            <a href="my_property.php">🏡 My Properties</a>
            <a href="agent_inquiries.php">📩 Inquiries</a>
            <a href="logout.php">🚪 Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1>Welcome Back, <?= htmlspecialchars($agent_name) ?>!</h1>

        <div class="card" style="border-top: 4px solid #e67e22;">
            <h2>🔥 Most Viewed Property</h2>
            <?php if ($most_viewed): ?>
                <h3><?= htmlspecialchars($most_viewed['title']) ?></h3>
                <p>Total Clicks: <strong><?= intval($most_viewed['total_clicks']) ?></strong></p>
            <?php else: ?>
                <p>No analytics data available yet.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>My Properties</h2>
            <table class="prop-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_properties as $p): ?>
                    <tr onclick="window.location='my_property_details.php?id=<?= $p['property_id'] ?>';">
                        <td><?= htmlspecialchars($p['title']) ?></td>
                        <td>$<?= number_format($p['price'], 2) ?></td>
                        <td><?= htmlspecialchars($p['status']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>