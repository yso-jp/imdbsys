<?php
// rent.php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';

$sql = "SELECT p.*, i.image_url 
        FROM properties p 
        LEFT JOIN property_images i ON p.property_id = i.property_id AND i.is_thumbnail = 1
        WHERE p.listing_type = 'Rent' 
        AND p.status = 'Available'";

$params = [];
if (!empty($location)) {
    $sql .= " AND (p.city LIKE ? OR p.address LIKE ?)";
    $params[] = "%$location%";
    $params[] = "%$location%";
}
if (!empty($type)) {
    $sql .= " AND p.property_type = ?";
    $params[] = $type;
}
$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Properties for Rent - Real Estate</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: #f8f9fa; }
        .navbar { background-color: #343a40; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar .logo { color: #fff; font-size: 22px; font-weight: bold; text-decoration: none; }
        .navbar .nav-links { list-style: none; display: flex; margin: 0; padding: 0; }
        .navbar .nav-links li { margin-left: 20px; }
        .navbar .nav-links a { color: #f8f9fa; text-decoration: none; font-size: 16px; }
        .navbar .nav-links a:hover { color: #ffc107; }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .property-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; margin-top: 20px; }
        .property-card { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.08); transition: transform 0.2s; }
        .property-card:hover { transform: translateY(-5px); }
        .property-img { width: 100%; height: 200px; object-fit: cover; background-color: #e9ecef; }
        .property-content { padding: 20px; }
        .property-price { font-size: 24px; font-weight: bold; color: #007bff; margin-bottom: 10px; }
        .property-title { font-size: 18px; font-weight: bold; margin: 0 0 10px 0; color: #333; }
        .property-location { color: #6c757d; font-size: 14px; margin-bottom: 15px; }
        .property-specs { display: flex; gap: 15px; font-size: 14px; color: #495057; border-top: 1px solid #eee; padding-top: 15px; }
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
        <h1>Properties For Rent</h1>
        <div class="property-grid">
            <?php foreach ($properties as $property): ?>
                <a href="full_details.php?id=<?= $property['property_id'] ?>" style="text-decoration: none; color: inherit;">
                    <div class="property-card">
                        <img class="property-img" src="<?= !empty($property['image_url']) ? htmlspecialchars($property['image_url']) : 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=400&q=80' ?>" alt="Property Image">
                        <div class="property-content">
                            <div class="property-price">$<?= number_format($property['price'], 2) ?> <span style="font-size: 14px; font-weight: normal; color: #6c757d;">/ mo</span></div>
                            <h3 class="property-title"><?= htmlspecialchars($property['title']) ?></h3>
                            <div class="property-location">📍 <?= htmlspecialchars($property['address']) ?>, <?= htmlspecialchars($property['city']) ?></div>
                            <div class="property-specs">
                                <span>🛏️ <?= intval($property['bedrooms']) ?> Beds</span>
                                <span>🛁 <?= intval($property['bathrooms']) ?> Baths</span>
                                <span>🏷️ <?= htmlspecialchars($property['property_type']) ?></span>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>