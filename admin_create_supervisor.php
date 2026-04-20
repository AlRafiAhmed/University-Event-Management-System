<?php
require_once "session_bootstrap.php";
require_once "db.php";
header("Content-Type: application/json");

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    http_response_code(401);
    echo json_encode(["ok" => false, "message" => "Unauthorized"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["ok" => false, "message" => "Method not allowed"]);
    exit;
}

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$password = trim($_POST["password"] ?? "");
$designation = trim($_POST["designation"] ?? "");
$department = trim($_POST["department"] ?? "");

if ($name === "" || $email === "" || $password === "" || $designation === "" || $department === "") {
    http_response_code(422);
    echo json_encode(["ok" => false, "message" => "All fields are required."]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO Supervisors (name, email, password, designation, department, status, is_active) VALUES (?, ?, ?, ?, ?, 'approved', 1)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["ok" => false, "message" => "Failed to prepare request."]);
    exit;
}

$stmt->bind_param("sssss", $name, $email, $password, $designation, $department);
$ok = $stmt->execute();
$error = $stmt->error;
$stmt->close();

if (!$ok) {
    http_response_code(500);
    echo json_encode(["ok" => false, "message" => "Could not create supervisor: " . $error]);
    exit;
}

echo json_encode(["ok" => true, "message" => "Supervisor created and approved successfully."]);
exit;
?>
