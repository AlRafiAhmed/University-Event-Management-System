<?php
require_once "session_bootstrap.php";
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.html");
    exit;
}
header("Location: admin_dashboard.html");
exit;
