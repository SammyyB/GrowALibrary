<?php 
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

// Optional: Check for admin-only access
if (isset($require_admin) && $_SESSION["role"] !== "admin") {
    header("Location: index.php");
    exit();
}


?>