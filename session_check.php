<?php
require_once "session_bootstrap.php";
require_once "db.php";

header("Content-Type: application/json");

if (!isset($_SESSION["role"], $_SESSION["user_id"])) {
    echo json_encode(["ok" => false, "message" => "Session not found."]);
    exit;
}

$role = $_SESSION["role"];
$userId = (int)$_SESSION["user_id"];

if ($role === "student") {
    $stmt = $conn->prepare("SELECT is_active FROM Students WHERE id = ? LIMIT 1");
    if (!$stmt) {
        echo json_encode(["ok" => false, "message" => "Session check failed."]);
        exit;
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows !== 1) {
        $stmt->close();
        echo json_encode(["ok" => false, "message" => "Your account is deactive."]);
        exit;
    }
    $stmt->bind_result($isActive);
    $stmt->fetch();
    $stmt->close();
    if ((int)$isActive !== 1) {
        echo json_encode(["ok" => false, "message" => "Your account is deactive."]);
        exit;
    }
    echo json_encode(["ok" => true]);
    exit;
}

if ($role === "supervisor") {
    $stmt = $conn->prepare("SELECT status, is_active FROM Supervisors WHERE id = ? LIMIT 1");
    if (!$stmt) {
        echo json_encode(["ok" => false, "message" => "Session check failed."]);
        exit;
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows !== 1) {
        $stmt->close();
        echo json_encode(["ok" => false, "message" => "Your account is deactive."]);
        exit;
    }
    $stmt->bind_result($status, $isActive);
    $stmt->fetch();
    $stmt->close();
    if ($status !== "approved" || (int)$isActive !== 1) {
        echo json_encode(["ok" => false, "message" => "Your account is deactive."]);
        exit;
    }
    echo json_encode(["ok" => true]);
    exit;
}

// Admin and other roles remain untouched by this auto-logout checker.
echo json_encode(["ok" => true]);
exit;
?>
