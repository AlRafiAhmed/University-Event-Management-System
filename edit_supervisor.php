<?php
ini_set("session.gc_maxlifetime", 86400);
session_set_cookie_params(86400);
session_start();
require_once "db.php";

header("Content-Type: application/json");

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    http_response_code(401);
    echo json_encode(["ok" => false, "message" => "Unauthorized"]);
    exit;
}

setcookie(session_name(), session_id(), time() + (86400 * 30), "/");

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $id = (int)($_GET["id"] ?? 0);
    if ($id <= 0) {
        echo json_encode(["ok" => false, "message" => "Invalid supervisor id."]);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, name, email, password, designation, department, status, is_active FROM Supervisors WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $supervisor = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$supervisor) {
        echo json_encode(["ok" => false, "message" => "Supervisor not found."]);
        exit;
    }

    echo json_encode(["ok" => true, "data" => $supervisor]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = (int)($_POST["id"] ?? 0);
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $designation = trim($_POST["designation"] ?? "");
    $department = trim($_POST["department"] ?? "");
    $status = trim($_POST["status"] ?? "pending");
    $isActive = (int)($_POST["is_active"] ?? 1);

    if ($id <= 0 || $name === "" || $email === "" || $password === "" || $designation === "" || $department === "") {
        echo json_encode(["ok" => false, "message" => "All fields are required."]);
        exit;
    }

    if (!in_array($status, ["pending", "approved", "rejected"], true)) {
        echo json_encode(["ok" => false, "message" => "Invalid status value."]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE Supervisors SET name = ?, email = ?, password = ?, designation = ?, department = ?, status = ?, is_active = ? WHERE id = ?");
    $stmt->bind_param("ssssssii", $name, $email, $password, $designation, $department, $status, $isActive, $id);
    $ok = $stmt->execute();
    $error = $stmt->error;
    $stmt->close();

    if (!$ok) {
        echo json_encode(["ok" => false, "message" => "Update failed: " . $error]);
        exit;
    }

    echo json_encode(["ok" => true, "message" => "Supervisor updated successfully."]);
    exit;
}

echo json_encode(["ok" => false, "message" => "Unsupported method."]);
exit;
?>
