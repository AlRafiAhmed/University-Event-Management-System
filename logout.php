<?php
require_once "session_bootstrap.php";
$errorMessage = trim($_GET["error"] ?? "");
$_SESSION = [];
session_destroy();
if ($errorMessage !== "") {
    header("Location: login.html?error=" . urlencode($errorMessage));
    exit;
}
header("Location: login.html?success=" . urlencode("Logged out successfully."));
exit;
?>
