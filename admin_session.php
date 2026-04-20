<?php
require_once "session_bootstrap.php";

header("Content-Type: application/json");

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    http_response_code(401);
    echo json_encode(["ok" => false, "message" => "Unauthorized"]);
    exit;
}

echo json_encode([
    "ok" => true,
    "name" => $_SESSION["name"] ?? "Admin"
]);
exit;
?>
