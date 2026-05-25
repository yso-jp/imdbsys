<?php
// my_property_details.php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'Agent') {
    header("Location: login.php");
    exit;
}

$agent_id = $_SESSION['user_id'];
$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch property
$stmt = $pdo->prepare("SELECT * FROM properties WHERE property_id = ? AND agent_id = ?");
$stmt->execute([$property_id, $agent_id]);
$property = $stmt->fetch();

if (!$property) {
    die("Property not found or access denied.");
}

// Handle Update
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    $update = $pdo->prepare("UPDATE properties SET title = ?, price = ?, description = ? WHERE property_id = ?");
    if ($update->execute([$title, $price, $description, $property_id])) {
        $message = "Property updated successfully!";
        // Refresh data
        $stmt->execute([$property_id, $agent_id]);
        $property = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Property - <?= htmlspecialchars($property['title']) ?></title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; margin: 0; }
        .navbar { background: #2c3e50; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar .logo { font-size: 20px; font-weight: bold; color: #2ecc71; text-decoration: none; }
        .nav-links { display: flex; gap: 15px; }
        .nav-links a { color: #ecf0f1; text-decoration: none; font-size: 14px; padding: 8px 16px; }
        
        .container { max-width: 900px; margin: 40px auto; padding: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .card { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; }
        label { display: block; margin-top: 15px; font-weight: bold; }
        input, textarea { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { margin-top: 20px; background: #2ecc71; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .back-btn { background: #95a5a6; margin-top: 20px; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; color: white; text-decoration: none; display: inline-block; }
        .alert { padding: 10px; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 20px; }
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
            <a href="logout.php">🚪 Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h2>Property Details</h2>
            <?php if ($message) echo "<div class='alert'>$message</div>"; ?>
            <p><strong>Title:</strong> <?= htmlspecialchars($property['title']) ?></p>
            <p><strong>Price:</strong> $<?= number_format($property['price'], 2) ?></p>
            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($property['description'])) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($property['status']) ?></p>
            
            <button class="back-btn" onclick="history.back();">← Back</button>
        </div>

        <div class="card">
            <h2>Edit Listing</h2>
            <form method="POST">
                <label>Title</label>
                <input type="text" name="title" value="<?= htmlspecialchars($property['title']) ?>" required>
                
                <label>Price</label>
                <input type="number" step="0.01" name="price" value="<?= $property['price'] ?>" required>
                
                <label>Description</label>
                <textarea name="description" rows="5" required><?= htmlspecialchars($property['description']) ?></textarea>
                
                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>
</body>
</html>