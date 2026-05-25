<?php
// add_property.php
session_start();
require_once 'config.php';

// 🔓 SECURITY: Check if agent is logged in (defaulting to 1 for dev)
$agent_id = isset($_SESSION['user_id']) && ($_SESSION['user_type'] === 'Agent') ? $_SESSION['user_id'] : 1;

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $type = $_POST['property_type'];
    $listing_type = $_POST['listing_type']; // New field
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $beds = intval($_POST['bedrooms']);
    $baths = intval($_POST['bathrooms']);

    if (!empty($title) && !empty($price) && !empty($address)) {
        try {
            // Updated SQL to include listing_type
            $sql = "INSERT INTO properties (agent_id, title, description, price, property_type, listing_type, status, address, city, bedrooms, bathrooms) 
                    VALUES (?, ?, ?, ?, ?, ?, 'Pending_Approval', ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$agent_id, $title, $description, $price, $type, $listing_type, $address, $city, $beds, $baths]);
            
            $message = "✅ Property successfully submitted! It is now pending admin approval.";
        } catch (\PDOException $e) {
            $error = "❌ Database Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Property - Agent Portal</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 0; }
        
        .navbar { background: #2c3e50; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .navbar .logo { font-size: 20px; font-weight: bold; color: #2ecc71; text-decoration: none; }
        .nav-links { display: flex; gap: 15px; }
        .nav-links a { color: #ecf0f1; text-decoration: none; font-size: 14px; font-weight: 600; padding: 8px 16px; border-radius: 4px; transition: all 0.2s; }
        .nav-links a:hover { background: #34495e; color: #2ecc71; }
        .nav-links a.logout-btn { background: #e74c3c; color: white; }
        .nav-links a.logout-btn:hover { background: #c0392b; color: white; }

        .page-content { padding: 40px 20px; }
        .form-container { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; margin-top: 0; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px; }
        input, textarea, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background: #2ecc71; color: white; padding: 12px; border: none; width: 100%; border-radius: 4px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        button:hover { background: #27ae60; }
        .back-link { display: block; margin-top: 20px; text-align: center; color: #3498db; text-decoration: none; font-size: 14px; }
        .msg { padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
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

    <div class="page-content">
        <div class="form-container">
            <h2>Add New Property</h2>
            
            <?php if($message): ?><div class="msg success"><?= $message ?></div><?php endif; ?>
            <?php if($error): ?><div class="msg error"><?= $error ?></div><?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4"></textarea>
                </div>
                <div class="form-group">
                    <label>Price ($)</label>
                    <input type="number" name="price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Property Type</label>
                    <select name="property_type">
                        <option value="House">House</option>
                        <option value="Apartment">Apartment</option>
                        <option value="Condo">Condo</option>
                        <option value="Land">Land</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Listing Type</label>
                    <select name="listing_type">
                        <option value="Sale">For Sale</option>
                        <option value="Rent">For Rent</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <input type="text" name="address" required>
                </div>
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="city" required>
                </div>
                <div style="display: flex; gap: 10px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Bedrooms</label>
                        <input type="number" name="bedrooms" value="0">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Bathrooms</label>
                        <input type="number" name="bathrooms" value="0">
                    </div>
                </div>
                <button type="submit">Submit for Approval</button>
            </form>
            <a href="agent_dashboard.php" class="back-link">← Return to Dashboard</a>
        </div>
    </div>

</body>
</html>