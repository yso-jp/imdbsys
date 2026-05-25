<?php
// index.php
require_once 'config.php';

// Route back to login if the session isn't authenticated
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Real Estate Marketplace</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: #f8f9fa; }
        
        /* Navbar Style */
        .navbar { background-color: #343a40; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar .logo { color: #fff; font-size: 22px; font-weight: bold; text-decoration: none; }
        .navbar .nav-links { list-style: none; display: flex; margin: 0; padding: 0; }
        .navbar .nav-links li { margin-left: 20px; }
        .navbar .nav-links a { color: #f8f9fa; text-decoration: none; font-size: 16px; transition: color 0.2s; }
        .navbar .nav-links a:hover { color: #ffc107; }
        .welcome-msg { color: #fff; font-size: 14px; margin-right: 15px; font-style: italic; }

        /* Hero Section */
        .hero { 
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&w=1200&q=80') no-repeat center center/cover;
            height: 60vh; display: flex; flex-direction: column; justify-content: center; align-items: center; color: white; text-align: center; padding: 0 20px;
        }
        .hero h1 { font-size: 42px; margin-bottom: 10px; text-shadow: 2px 2px 4px rgba(0,0,0,0.6); }
        .search-container { background: rgba(255, 255, 255, 0.95); padding: 20px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); width: 100%; max-width: 800px; display: flex; gap: 10px; flex-wrap: wrap; }
        .search-container input, .search-container select { flex: 1; min-width: 150px; padding: 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 15px; }
        .search-container button { background: #007bff; color: white; border: none; padding: 12px 25px; font-size: 16px; border-radius: 4px; cursor: pointer; }

        /* Property Grid */
        .property-grid { max-width: 1200px; margin: 40px auto; padding: 0 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; }
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); overflow: hidden; transition: transform 0.2s; }
        .card:hover { transform: translateY(-5px); }
        .card img { width: 100%; height: 200px; object-fit: cover; }
        .card-body { padding: 15px; }
        .price { color: #007bff; font-weight: bold; font-size: 1.2em; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo">🏠 EstateMarket</a>
        <ul class="nav-links">
            <li><span class="welcome-msg">Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></span></li>
            <li><a href="buy.php">Buy</a></li>
            <li><a href="rent.php">Rent</a></li>
            <li><a href="list_property.php">List Property</a></li>
            <li><a href="transaction_history.php">My Transactions</a></li>
            <li><a href="client_inquiries.php">My Messages</a></li>
            <li><a href="logout.php" style="color: #dc3545; font-weight: bold;">Logout</a></li>
        </ul>
    </nav>

    <div class="hero">
        <h1>Find Your Dream Home</h1>
        <form class="search-container" action="search_gateway.php" method="GET">
            <input type="text" name="location" placeholder="City or address..." required>
            <select name="purpose"><option value="Sale">For Sale</option><option value="Rent">For Rent</option></select>
            <select name="type"><option value="House">House</option><option value="Apartment">Apartment</option><option value="Condo">Condo</option></select>
            <button type="submit">Search</button>
        </form>
    </div>

    <div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
        <h2>Recently Listed</h2>
        <div class="property-grid">
            <?php
            $stmt = $pdo->query("SELECT p.*, pi.image_url 
                                 FROM properties p 
                                 LEFT JOIN property_images pi ON p.property_id = pi.property_id AND pi.is_thumbnail = 1 
                                 WHERE p.status = 'Available' 
                                 ORDER BY p.created_at DESC LIMIT 6");
            while ($p = $stmt->fetch()): ?>
                <div class="card">
                    <?php if ($p['image_url']): ?>
                        <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="Property Image">
                    <?php endif; ?>
                    <div class="card-body">
                        <h3><?= htmlspecialchars($p['title']) ?></h3>
                        <p class="price">$<?= number_format($p['price'], 2) ?></p>
                        <p>📍 <?= htmlspecialchars($p['city']) ?></p>
                        <a href="full_details.php?id=<?= $p['property_id'] ?>" style="background:#343a40; color:#fff; display:block; padding:10px; text-align:center; text-decoration:none; border-radius:4px;">View Details</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

</body>
</html>