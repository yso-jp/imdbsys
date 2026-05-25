<?php
// admin_dashboard.php
session_start(); // Maintained strictly to populate your name badge if a session exists

// 🔓 SECURITY GATEKEEPER REMOVED: Anyone can view this page directly now.
require_once 'config.php';

$message = '';

// 1. --- ADMINISTRATIVE ACTION HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $target_id = intval($_POST['id']);
    
    // Fallback admin ID to prevent database errors if no session exists
    $admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

    try {
        if ($action === 'approve_agent') {
            $stmt = $pdo->prepare("UPDATE agents SET approval_status = 'Approved', approved_by_admin = ?, approved_at = CURRENT_TIMESTAMP WHERE agent_id = ?");
            $stmt->execute([$admin_id, $target_id]);
            $message = "🟢 Agent profile successfully verified and activated.";
        } elseif ($action === 'reject_agent') {
            $stmt = $pdo->prepare("UPDATE agents SET approval_status = 'Rejected', approved_by_admin = ?, approved_at = CURRENT_TIMESTAMP WHERE agent_id = ?");
            $stmt->execute([$admin_id, $target_id]);
            $message = "🔴 Agent profile has been rejected.";
        } elseif ($action === 'approve_property') {
            $stmt = $pdo->prepare("UPDATE properties SET status = 'Available', approved_by_admin = ?, approved_at = CURRENT_TIMESTAMP WHERE property_id = ?");
            $stmt->execute([$admin_id, $target_id]);
            $message = "🟢 Property listing approved and published live.";
        } elseif ($action === 'reject_property') {
            $stmt = $pdo->prepare("UPDATE properties SET status = 'Rejected', approved_by_admin = ?, approved_at = CURRENT_TIMESTAMP WHERE property_id = ?");
            $stmt->execute([$admin_id, $target_id]);
            $message = "🔴 Property listing layout has been rejected.";
        }
    } catch (\PDOException $e) {
        $message = "❌ Database Operational Failure: " . $e->getMessage();
    }
}

// 2. --- DATA EXTRACTION PIPELINE ---
try {
    // Fetch Agents awaiting system verification (Pending Accounts)
    $pending_agents = $pdo->query("SELECT agent_id, first_name, last_name, email, phone, agency_name FROM agents WHERE approval_status = 'Pending' ORDER BY created_at DESC")->fetchAll();

    // Fetch Properties awaiting content curation review (Pending Approvals)
    $pending_properties = $pdo->query("SELECT property_id, title, price, property_type, address, city FROM properties WHERE status = 'Pending_Approval' ORDER BY created_at DESC")->fetchAll();

    // Fetch All Properties (Regardless of current marketplace status)
    $all_properties = $pdo->query("SELECT p.property_id, p.title, p.price, p.status, p.property_type, p.city, a.first_name, a.last_name FROM properties p JOIN agents a ON p.agent_id = a.agent_id ORDER BY p.created_at DESC")->fetchAll();

} catch (\PDOException $e) {
    die("Fatal Pipeline Failure: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Administrative Terminal - EstateMarket (Bypassed)</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background: #f4f6f9; color: #333; }
        
        /* Layout Header Navigation Bar */
        .navbar { background: #212529; color: #fff; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 4px solid #dc3545; }
        .navbar .logo { font-size: 20px; font-weight: bold; color: #ffc107; text-decoration: none; }
        .navbar .logout-btn { color: #fff; background: #dc3545; text-decoration: none; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 14px; }
        .navbar .logout-btn:hover { background: #bd2130; }

        .container { max-width: 1300px; margin: 30px auto; padding: 0 20px; }
        .system-msg { background: #e2f0fe; color: #007bff; padding: 12px 20px; border-radius: 4px; border: 1px solid #b8daff; margin-bottom: 25px; font-weight: 600; }
        .bypass-warning { background: #fff5f5; color: #dc3545; padding: 12px 20px; border-radius: 4px; border: 1px solid #fab6b6; margin-bottom: 25px; font-weight: bold; text-align: center; }
        
        h1 { margin: 0 0 5px 0; font-size: 28px; }
        p.subtitle { color: #6c757d; margin: 0 0 30px 0; }

        /* Module System Display Cards */
        .module-card { background: #fff; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); padding: 25px; margin-bottom: 35px; border-top: 4px solid #6c757d; }
        .module-card.priority { border-top-color: #ffc107; } 
        .module-card.info-tint { border-top-color: #17a2b8; } 
        
        .module-card h2 { margin-top: 0; font-size: 18px; color: #212529; display: flex; justify-content: space-between; align-items: center; }
        .counter-badge { background: #e9ecef; padding: 3px 10px; border-radius: 12px; font-size: 13px; font-weight: bold; }
        .priority .counter-badge { background: #fff3cd; color: #856404; }

        /* Tabular Layouts */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { text-align: left; padding: 12px 15px; border-bottom: 1px solid #dee2e6; font-size: 14px; }
        th { background: #f8f9fa; color: #495057; font-weight: 600; }
        tr:hover { background-color: #fafbfc; }
        
        /* Buttons & Badges */
        .btn { padding: 6px 14px; border: none; border-radius: 4px; font-size: 12px; font-weight: bold; cursor: pointer; display: inline-block; text-decoration: none; margin-right: 5px; }
        .btn-approve { background: #28a745; color: white; }
        .btn-approve:hover { background: #218838; }
        .btn-reject { background: #dc3545; color: white; }
        .btn-reject:hover { background: #c82333; }
        
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .status-Available { background-color: #d4edda; color: #155724; }
        .status-Pending_Approval { background-color: #fff3cd; color: #856404; }
        .status-Rejected { background-color: #f8d7da; color: #721c24; }
        .status-Sold { background-color: #e2e3e5; color: #383d41; }
        .status-Rent { background-color: #cce5ff; color: #004085; }
        .status-Pending_Sale { background-color: #e2d1f9; color: #4a148c; }
        
        .no-records { text-align: center; color: #999; padding: 20px; font-style: italic; }
        .inline-form { display: inline; margin: 0; padding: 0; }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="admin_dashboard.php" class="logo">🛠️ Administration Control Hub (Unlocked)</a>
        <div>
            <span style="margin-right: 15px; font-size: 14px;">Logged in as: <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'DevMode_Guest') ?></strong></span>
            <a href="login.php" class="logout-btn">Back to Login</a>
        </div>
    </nav>

    <div class="container">
        <div class="bypass-warning">⚠️ SECURITY LAID BARE: Session validation is turned off for fluid sandbox development.</div>

        <h1>Global Ecosystem Control Center</h1>
        <p class="subtitle">Platform verification panel. Audit registration queues, authorize real estate listings, and overview property indexes.</p>

        <?php if (!empty($message)): ?>
            <div class="system-msg"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="module-card priority">
            <h2>Pending Agent Accounts <span class="counter-badge"><?= count($pending_agents) ?> awaiting review</span></h2>
            <table>
                <thead>
                    <tr>
                        <th>Agent Name</th>
                        <th>Email Address</th>
                        <th>Phone Contacts</th>
                        <th>Agency Affiliation</th>
                        <th>Authorization Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($pending_agents) === 0): ?>
                        <tr><td colspan="5" class="no-records">No agent profile accounts are stuck in the approval registration queue.</td></tr>
                    <?php else: ?>
                        <?php foreach ($pending_agents as $agent): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($agent['first_name'] . ' ' . $agent['last_name']) ?></strong></td>
                                <td><?= htmlspecialchars($agent['email']) ?></td>
                                <td><?= htmlspecialchars($agent['phone']) ?></td>
                                <td><?= htmlspecialchars($agent['agency_name'] ?? 'Independent Broker') ?></td>
                                <td>
                                    <form action="admin_dashboard.php" method="POST" class="inline-form">
                                        <input type="hidden" name="id" value="<?= $agent['agent_id'] ?>">
                                        <button type="submit" name="action" value="approve_agent" class="btn btn-approve">Approve</button>
                                        <button type="submit" name="action" value="reject_agent" class="btn btn-reject">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="module-card priority">
            <h2>Pending Property Approvals <span class="counter-badge"><?= count($pending_properties) ?> listings to audit</span></h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Property Title</th>
                        <th>Classification</th>
                        <th>Location Address</th>
                        <th>Pricing Metric</th>
                        <th>Content Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($pending_properties) === 0): ?>
                        <tr><td colspan="6" class="no-records">No property listing submissions are waiting for administrative review.</td></tr>
                    <?php else: ?>
                        <?php foreach ($pending_properties as $prop): ?>
                            <tr>
                                <td>#<?= $prop['property_id'] ?></td>
                                <td><strong><?= htmlspecialchars($prop['title']) ?></strong></td>
                                <td><?= htmlspecialchars($prop['property_type']) ?></td>
                                <td><?= htmlspecialchars($prop['address'] . ', ' . $prop['city']) ?></td>
                                <td>$<?= number_format($prop['price'], 2) ?></td>
                                <td>
                                    <form action="admin_dashboard.php" method="POST" class="inline-form">
                                        <input type="hidden" name="id" value="<?= $prop['property_id'] ?>">
                                        <button type="submit" name="action" value="approve_property" class="btn btn-approve">Approve & Publish</button>
                                        <button type="submit" name="action" value="reject_property" class="btn btn-reject">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="module-card info-tint">
            <h2>Complete Properties Core Registry <span class="counter-badge">Total Listings: <?= count($all_properties) ?></span></h2>
            <table>
                <thead>
                    <tr>
                        <th>ID Reference</th>
                        <th>Property Title</th>
                        <th>Type</th>
                        <th>City Context</th>
                        <th>Price Target</th>
                        <th>Assigned Listing Agent</th>
                        <th>System State Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($all_properties) === 0): ?>
                        <tr><td colspan="7" class="no-records">No structural asset listings exist across the current marketplace configuration index.</td></tr>
                    <?php else: ?>
                        <?php foreach ($all_properties as $all_prop): ?>
                            <tr>
                                <td>#<?= $all_prop['property_id'] ?></td>
                                <td><strong><?= htmlspecialchars($all_prop['title']) ?></strong></td>
                                <td><?= htmlspecialchars($all_prop['property_type']) ?></td>
                                <td><?= htmlspecialchars($all_prop['city']) ?></td>
                                <td>$<?= number_format($all_prop['price'], 2) ?></td>
                                <td><?= htmlspecialchars($all_prop['first_name'] . ' ' . $all_prop['last_name']) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $all_prop['status'] ?>">
                                        <?= str_replace('_', ' ', $all_prop['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>