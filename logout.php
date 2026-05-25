<?php
// logout.php
require_once 'config.php';

// 1. Unset all session global variables
$_SESSION = [];

// 2. Clear the session cookie from the user's browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000, // Backdate the expiration time to delete it immediately
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 3. Destroy the actual session file on the server
session_destroy();

// 4. Redirect the client back to the login gateway
header("Location: login.php");
exit;
?>