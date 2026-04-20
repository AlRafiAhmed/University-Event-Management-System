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
        echo json_encode(["ok" => false, "message" => "Invalid student id."]);
        exit;
    }

    $stmt = $conn->prepare("SELECT id, student_id, name, email, password, department, is_active FROM Students WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$student) {
        echo json_encode(["ok" => false, "message" => "Student not found."]);
        exit;
    }

    echo json_encode(["ok" => true, "data" => $student]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = (int)($_POST["id"] ?? 0);
    $studentId = trim($_POST["student_id"] ?? "");
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $department = trim($_POST["department"] ?? "");
    $isActive = (int)($_POST["is_active"] ?? 1);

    if ($id <= 0 || $studentId === "" || $name === "" || $email === "" || $password === "" || $department === "") {
        echo json_encode(["ok" => false, "message" => "All fields are required."]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE Students SET student_id = ?, name = ?, email = ?, password = ?, department = ?, is_active = ? WHERE id = ?");
    $stmt->bind_param("sssssii", $studentId, $name, $email, $password, $department, $isActive, $id);
    $ok = $stmt->execute();
    $error = $stmt->error;
    $stmt->close();

    if (!$ok) {
        echo json_encode(["ok" => false, "message" => "Update failed: " . $error]);
        exit;
    }

    echo json_encode(["ok" => true, "message" => "Student updated successfully."]);
    exit;
}

echo json_encode(["ok" => false, "message" => "Unsupported method."]);
exit;
?>
