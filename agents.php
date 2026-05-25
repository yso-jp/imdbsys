<?php
// agents.php
require_once 'config.php';

// Route back to login if the session isn't authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

try {
    // Fetch only verified/approved agents to display to clients
    $sql = "SELECT agent_id, first_name, last_name, email, phone, agency_name, created_at 
            FROM agents 
            WHERE approval_status = 'Approved' 
            ORDER BY agency_name ASC, last_name ASC";
    
    $stmt = $pdo->query($sql);
    $agents = $stmt->fetchAll();
} catch (\PDOException $e) {
    die("Could not fetch agents: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Our Real Estate Agents - EstateMarket</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: #f8f9fa; }
        
        /* Navigation Bar */
        .navbar { background-color: #343a40; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar .logo { color: #fff; font-size: 22px; font-weight: bold; text-decoration: none; }
        .navbar .nav-links { list-style: none; display: flex; margin: 0; padding: 0; }
        .navbar .nav-links li { margin-left: 20px; }
        .navbar .nav-links a { color: #f8f9fa; text-decoration: none; font-size: 16px; }
        .navbar .nav-links a:hover { color: #ffc107; }
        
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        h1 { color: #333; margin-bottom: 10px; }
        .subtitle { color: #6c757d; margin-bottom: 30px; font-size: 16px; }
        
        /* Grid Layout for Agents */
        .agent-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
        .agent-card { background: #fff; border-radius: 8px; border: 1px solid #e3e6f0; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; padding: 25px 20px; transition: transform 0.2s; }
        .agent-card:hover { transform: translateY(-3px); box-shadow: 0 6px 12px rgba(0,0,0,0.1); }
        
        /* Profile Avatar Placeholder */
        .agent-avatar { width: 90px; height: 90px; border-radius: 50%; background: #e9ecef; color: #495057; font-size: 32px; font-weight: bold; line-height: 90px; margin: 0 auto 15px auto; text-transform: uppercase; border: 3px solid #007bff; }
        
        .agent-name { font-size: 20px; font-weight: bold; color: #333; margin: 0 0 5px 0; }
        .agency-badge { display: inline-block; background: #e2f0fe; color: #007bff; font-size: 13px; font-weight: 600; padding: 4px 12px; border-radius: 20px; margin-bottom: 20px; }
        
        /* Contact info styling */
        .agent-contact { border-top: 1px solid #edf2f9; padding-top: 15px; text-align: left; }
        .contact-item { font-size: 14px; color: #495057; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; }
        .contact-item a { color: #007bff; text-decoration: none; }
        .contact-item a:hover { text-decoration: underline; }
        
        .no-results { text-align: center; font-size: 18px; color: #6c757d; grid-column: 1 / -1; padding: 40px; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="index.php" class="logo">🏠 EstateMarket</a>
        <ul class="nav-links">
        <li><a href="buy.php">Buy</a></li>
            <li><a href="rent.php">Rent</a></li>
            <li><a href="transaction_history.php">My Transactions</a></li>
            <li><a href="client_inquiries.php">My Messages</a></li>
            <li><a href="logout.php" style="color: #dc3545; font-weight: bold;">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>Our Certified Partners</h1>
        <p class="subtitle">Connect with top-rated, admin-verified agents to secure your next property transaction smoothly.</p>
        
        <div class="agent-grid">
            <?php if (count($agents) === 0): ?>
                <div class="no-results">
                    <p>No registered agents are verified on our platform yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($agents as $agent): ?>
                    <?php 
                        // Generate initials for the profile avatar placeholder
                        $initials = strtoupper(substr($agent['first_name'], 0, 1) . substr($agent['last_name'], 0, 1));
                        $fullName = htmlspecialchars($agent['first_name'] . ' ' . $agent['last_name']);
                        $agency = !empty($agent['agency_name']) ? htmlspecialchars($agent['agency_name']) : 'Independent Broker';
                    ?>
                    <div class="agent-card">
                        <div class="agent-avatar"><?= $initials ?></div>
                        
                        <h3 class="agent-name"><?= $fullName ?></h3>
                        <span class="agency-badge">🏢 <?= $agency ?></span>
                        
                        <div class="agent-contact">
                            <div class="contact-item">
                                📞 <span><?= htmlspecialchars($agent['phone']) ?></span>
                            </div>
                            <div class="contact-item">
                                ✉️ <a href="mailto:<?= htmlspecialchars($agent['email']) ?>"><?= htmlspecialchars($agent['email']) ?></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>