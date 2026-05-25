<?php
// fix_admin.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<h2>🔧 Admin Account Diagnostics Engine (V2)</h2>";

try {
    // 1. Force fix the column size
    echo "• Checking and fixing database column limits...<br>";
    $pdo->exec("ALTER TABLE admins MODIFY COLUMN password_hash VARCHAR(255) NOT NULL");
    
    // 2. 🔥 FIXED: Purge conflicting records by BOTH email and username to prevent error 1062
    echo "• Purging old conflicting records by email and username...<br>";
    $stmt = $pdo->prepare("DELETE FROM admins WHERE email = ? OR username = ?");
    $stmt->execute(['admin@estate.com', 'admin']);

    // 3. Generate a fresh native BCRYPT hash
    echo "• Generating fresh local crypt-hash...<br>";
    $raw_password = 'Admin123!';
    $native_hash = password_hash($raw_password, PASSWORD_BCRYPT);

    // 4. Insert the clean account layout
    echo "• Registering new clean admin profile...<br>";
    $insert_stmt = $pdo->prepare("INSERT INTO admins (username, email, password_hash) VALUES (?, ?, ?)");
    $insert_stmt->execute(['admin', 'admin@estate.com', $native_hash]);

    echo "<h3 style='color: green;'>✅ SUCCESS! Master admin account has been created.</h3>";
    echo "<strong>Target Email:</strong> admin@estate.com<br>";
    echo "<strong>Target Password:</strong> Admin123!<br><br>";
    
    // 5. Test validation directly on the spot
    echo "<h4>• Running Immediate Validation Test:</h4>";
    $test_stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $test_stmt->execute(['admin@estate.com']);
    $test_user = $test_stmt->fetch();

    if ($test_user && password_verify($raw_password, $test_user['password_hash'])) {
        echo "<b style='color:green;'>🎉 Internal test passed! Your PHP engine successfully verified this account.</b><br><br>";
        echo "<a href='admin_login.php' style='font-size: 16px; font-weight: bold;'>Go to admin_login.php and try logging in now!</a>";
    } else {
        echo "<b style='color:red;'>❌ Internal test failed! Something is modifying strings between PHP and your MySQL instance.</b>";
    }

} catch (\PDOException $e) {
    echo "<h3 style='color: red;'>❌ Database Error:</h3> " . $e->getMessage();
}
?>