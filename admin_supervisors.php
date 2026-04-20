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

    if ($targetId <= 0) {
        echo json_encode(["ok" => false, "message" => "Invalid supervisor id."]);
        exit;
    }

    if ($action === "approve") {
        $stmt = $conn->prepare("UPDATE Supervisors SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $targetId);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["ok" => true, "message" => "Supervisor approved."]);
        exit;
    }

    if ($action === "reject") {
        $stmt = $conn->prepare("UPDATE Supervisors SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $targetId);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["ok" => true, "message" => "Supervisor rejected."]);
        exit;
    }

    if ($action === "toggle_active") {
        $newValue = (int)($_POST["new_value"] ?? 0);
        $stmt = $conn->prepare("UPDATE Supervisors SET is_active = ? WHERE id = ?");
        $stmt->bind_param("ii", $newValue, $targetId);
        $stmt->execute();
        $stmt->close();
        echo json_encode(["ok" => true, "message" => "Supervisor status updated."]);
        exit;
    }

    echo json_encode(["ok" => false, "message" => "Invalid action."]);
    exit;
}

$supervisors = [];
$query = $conn->query("SELECT id, name, email, password, designation, department, profile_image, status, is_active FROM Supervisors ORDER BY id DESC");
if ($query) {
    while ($row = $query->fetch_assoc()) {
        $supervisors[] = $row;
    }
}

echo json_encode(["ok" => true, "data" => $supervisors]);
exit;
?>
