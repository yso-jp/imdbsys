<?php
// client_profile.php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. Fetch User Profile
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 2. Fetch User's Favorited Properties
$prop_stmt = $pdo->prepare("
    SELECT p.*, pi.image_url 
    FROM properties p
    JOIN favorites f ON p.property_id = f.property_id
    LEFT JOIN property_images pi ON p.property_id = pi.property_id AND pi.is_thumbnail = 1
    WHERE f.user_id = ?
");
$prop_stmt->execute([$user_id]);
$favorites = $prop_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - <?= htmlspecialchars($user['first_name']) ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f8f9fa; margin: 0; }
        
        /* Navbar Styles */
        .navbar { background-color: #343a40; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar .logo { color: #fff; font-size: 22px; font-weight: bold; text-decoration: none; }
        .navbar .nav-links { list-style: none; display: flex; margin: 0; padding: 0; align-items: center; }
        .navbar .nav-links li { margin-left: 20px; }
        .navbar .nav-links a { color: #f8f9fa; text-decoration: none; font-size: 16px; }
        .navbar .nav-links a:hover { color: #ffc107; }

        .container { max-width: 900px; margin: 40px auto; padding: 20px; }
        .card { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 30px; }
        h2 { border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .profile-info { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        
        .fav-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .fav-card { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1); transition: transform 0.2s; }
        .fav-card:hover { transform: translateY(-5px); }
        .fav-card img { width: 100%; height: 150px; object-fit: cover; }
        .fav-body { padding: 15px; }
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
    <div class="card">
        <h2>My Profile</h2>
        <div class="profile-info">
            <p><strong>Name:</strong> <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($user['phone'] ?? 'Not provided') ?></p>
            <p><strong>Joined:</strong> <?= date('F j, Y', strtotime($user['created_at'])) ?></p>
        </div>
    </div>

    <h2>My Favorite Properties</h2>
    <div class="fav-grid">
        <?php if (count($favorites) > 0): foreach ($favorites as $p): ?>
            <a href="full_details.php?id=<?= $p['property_id'] ?>" style="text-decoration: none; color: inherit;">
                <div class="fav-card">
                    <?php if ($p['image_url']): ?>
                        <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="Property Image">
                    <?php endif; ?>
                    <div class="fav-body">
                        <h3 style="margin-top: 0;"><?= htmlspecialchars($p['title']) ?></h3>
                        <p style="margin-bottom: 0; font-weight: bold; color: #007bff;">$<?= number_format($p['price'], 2) ?></p>
                    </div>
                </div>
            </a>
        <?php endforeach; else: ?>
            <p>You haven't favorited any properties yet.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>