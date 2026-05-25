<?php
// my_property.php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Agent') {
    header("Location: login.php");
    exit;
}

$agent_id = $_SESSION['user_id'];
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$sql = "SELECT * FROM properties WHERE agent_id = ?";
$params = [$agent_id];

if ($filter === 'pending') {
    $sql .= " AND status = 'Pending_Approval'";
} elseif ($filter === 'rent') {
    $sql .= " AND listing_type = 'Rent'";
} elseif ($filter === 'sale') {
    $sql .= " AND listing_type = 'Sale'";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Properties - Agent Portal</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 0; }
        .navbar { background: #2c3e50; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .navbar .logo { font-size: 20px; font-weight: bold; color: #2ecc71; text-decoration: none; }
        .nav-links { display: flex; gap: 15px; }
        .nav-links a { color: #ecf0f1; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 4px; transition: all 0.2s; }
        .nav-links a:hover { background: #34495e; color: #2ecc71; }
        .nav-links a.logout-btn { background: #e74c3c; color: white; }

        .container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        
        .filter-bar { margin-bottom: 30px; display: flex; gap: 10px; }
        .filter-bar a { padding: 8px 15px; background: #fff; border: 1px solid #ddd; border-radius: 20px; text-decoration: none; color: #333; font-size: 14px; }
        .filter-bar a.active { background: #2ecc71; color: #fff; border-color: #2ecc71; }

        /* Property Grid Layout */
        .property-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        
        /* Fully Clickable Card Styles */
        .property-card { 
            background: #fff; border-radius: 8px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; text-decoration: none; color: inherit; display: block;
        }
        .property-card:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0,0,0,0.15); }
        .property-card h3 { margin-top: 0; color: #2c3e50; }
        .price { font-size: 18px; font-weight: bold; color: #2ecc71; }
        .status-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; text-transform: uppercase; margin-top: 10px; }
        .status-available { background: #d4edda; color: #155724; }
        .status-pending-approval { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="agent_dashboard.php" class="logo">💼 Agent Hub</a>
        <div class="nav-links">
             <a href="agent_profile.php">👤 Profile</a>

            <a href="add_property.php">➕ Add Property</a>
            <a href="my_property.php">🏡 My Properties</a>
            <a href="agent_inquiries.php">📩 My Inquiries</a>
            <a href="agent_transaction_history.php">💰 My Transactions</a>
            <a href="logout.php" class="logout-btn">🚪 Logout</a>
        </div>  
    </nav>

    <div class="container">
        <h1>My Properties</h1>
        
        <div class="filter-bar">
            <a href="my_property.php?filter=all" class="<?= $filter === 'all' ? 'active' : '' ?>">All</a>
            <a href="my_property.php?filter=sale" class="<?= $filter === 'sale' ? 'active' : '' ?>">For Sale</a>
            <a href="my_property.php?filter=rent" class="<?= $filter === 'rent' ? 'active' : '' ?>">For Rent</a>
            <a href="my_property.php?filter=pending" class="<?= $filter === 'pending' ? 'active' : '' ?>">Pending Approval</a>
        </div>
        
        <div class="property-grid">
            <?php if (count($properties) > 0): foreach ($properties as $p): 
                $status_class = strtolower(str_replace('_', '-', $p['status']));
            ?>
                <a href="my_property_details.php?id=<?= (int)$p['property_id'] ?>" class="property-card">
                    <h3><?= htmlspecialchars($p['title']) ?></h3>
                    <p><strong>Type:</strong> <?= htmlspecialchars($p['property_type']) ?> | <?= htmlspecialchars($p['listing_type']) ?></p>
                    <div class="price">$<?= number_format($p['price'], 2) ?></div>
                    <span class="status-badge status-<?= $status_class ?>">
                        <?= str_replace('_', ' ', $p['status']) ?>
                    </span>
                </a>
            <?php endforeach; else: ?>
                <p>No properties found in this category.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>