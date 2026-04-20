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

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $targetId = (int)($_POST["target_id"] ?? 0);
    $newValue = (int)($_POST["new_value"] ?? 0);

    if ($action === "toggle_active" && $targetId > 0) {
        $stmt = $conn->prepare("UPDATE Students SET is_active = ? WHERE id = ?");
        if (!$stmt) {
            echo json_encode(["ok" => false, "message" => "Update failed."]);
            exit;
        }
        $stmt->bind_param("ii", $newValue, $targetId);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["ok" => true, "message" => "Student status updated."]);
        exit;
    }

    echo json_encode(["ok" => false, "message" => "Invalid action."]);
    exit;
}

$students = [];
$query = $conn->query("SELECT id, student_id, name, email, password, department, profile_image, is_active FROM Students ORDER BY id DESC");
if ($query) {
    while ($row = $query->fetch_assoc()) {
        $students[] = $row;
    }
}

echo json_encode(["ok" => true, "data" => $students]);
exit;
?>
