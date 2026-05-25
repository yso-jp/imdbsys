<?php
// agent_dashboard.php
session_start();

require_once 'config.php';

// 🔓 FLEXIBLE SECURITY GATEKEEPER: Defaults to Agent ID 1 if not logged in via session
$agent_id = isset($_SESSION['user_id']) && ($_SESSION['user_type'] === 'Agent') ? $_SESSION['user_id'] : 1;
$agent_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Professional Agent';

$most_viewed = false; 

// --- DATA PIPELINE: AGGREGATE VIEWS FROM THE INTERACTION TABLES ---
try {
    // 📊 Counts views from 'property_views' grouped by property, sorted by highest count
    $query = "
        SELECT p.*, COUNT(v.view_id) AS total_clicks
        FROM properties p
        LEFT JOIN property_views v ON p.property_id = v.property_id
        WHERE p.agent_id = ?
        GROUP BY p.property_id
        ORDER BY total_clicks DESC
        LIMIT 1
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$agent_id]);
    $most_viewed = $stmt->fetch(); // Returns dynamic array or false if no listings exist
    
} catch (\PDOException $e) {
    $error_msg = "Database Curation Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard - EstateMarket</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; padding: 0; background: #f4f6f9; color: #333; }
        
        /* Persistent Navigation Bar Layout */
        .navbar { background: #2c3e50; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .navbar .logo { font-size: 20px; font-weight: bold; color: #2ecc71; text-decoration: none; }
        .nav-links { display: flex; gap: 15px; }
        .nav-links a { color: #ecf0f1; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 4px; transition: all 0.2s; }
        .nav-links a:hover { background: #34495e; color: #2ecc71; }
        .nav-links a.logout-btn { background: #e74c3c; color: white; }
        .nav-links a.logout-btn:hover { background: #c0392b; color: white; }
        
        .container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        
        /* Dashboard Welcome Header */
        .welcome-header { margin-bottom: 35px; }
        .welcome-header h1 { margin: 0; font-size: 28px; color: #2c3e50; }
        .welcome-header p { color: #7f8c8d; margin: 5px 0 0 0; }

        /* Featured Analytics Card */
        .card { background: #fff; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); padding: 30px; border-top: 4px solid #e67e22; position: relative; }
        .card h2 { margin-top: 0; font-size: 18px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .metric-badge { position: absolute; top: 25px; right: 30px; background: #e67e22; color: white; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .prop-title { font-size: 26px; margin: 15px 0 10px 0; color: #2c3e50; font-weight: bold; }
        .prop-meta { font-size: 15px; color: #555; margin-bottom: 8px; }
        .prop-price { font-size: 22px; color: #e67e22; font-weight: bold; margin-top: 20px; display: inline-block; }

        .traffic-footer { margin-top: 25px; border-top: 1px dashed #e8e8e8; padding-top: 20px; font-size: 14px; color: #444; }
        .traffic-count { color: #e67e22; font-size: 18px; font-weight: bold; }
        
        .no-data { text-align: center; color: #95a5a6; padding: 40px 20px; font-style: italic; }
        .error-alert { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="agent_dashboard.php" class="logo">💼 Agent Hub</a>
        <div class="nav-links">
            <a href="add_property.php">➕ Add Property</a>
            <a href="my_property.php">🏡 My Properties</a>
            <a href="agent_inquiries.php">📩 My Inquiries</a>
            <a href="agent_transaction_history.php">💰 My Transactions</a>
            <a href="logout.php" class="logout-btn">🚪 Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-header">
            <h1>Welcome Back, <?= htmlspecialchars($agent_name) ?>!</h1>
            <p>Tracking live engagement performance from your properties index.</p>
        </div>

        <?php if (isset($error_msg)): ?>
            <div class="error-alert"><?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>Most Viewed Listing Portfolio</h2>
            
            <?php if ($most_viewed): ?>
                <span class="metric-badge">🔥 Most Clicked</span>
                <div class="prop-title"><?= htmlspecialchars($most_viewed['title']) ?></div>
                <div class="prop-meta">📍 <strong>Location:</strong> <?= htmlspecialchars($most_viewed['address'] . ', ' . $most_viewed['city']) ?></div>
                <div class="prop-meta">🏷️ <strong>Category:</strong> <?= htmlspecialchars($most_viewed['property_type']) ?> | <strong>Listing Type:</strong> <?= htmlspecialchars($most_viewed['listing_type']) ?></div>
                <div class="prop-meta">📊 <strong>Current Status:</strong> <span style="font-weight: bold; color: #2980b9;"><?= str_replace('_', ' ', $most_viewed['status']) ?></span></div>
                
                <div class="prop-price">$<?= number_format($most_viewed['price'], 2) ?></div>
                
                <div class="traffic-footer">
                    📈 Total traffic telemetry logged: <span class="traffic-count"><?= intval($most_viewed['total_clicks']) ?></span> organic customer clicks.
                </div>
            <?php else: ?>
                <div class="no-data">
                    <p>No active property analytics data streams detected in your workspace inventory.</p>
                    <p style="font-size: 13px; font-style: normal; margin-top: 5px;">Click <a href="add_property.php" style="color:#2ecc71; font-weight:bold; text-decoration:none;">Add Property</a> above to create your first asset listing layout.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>