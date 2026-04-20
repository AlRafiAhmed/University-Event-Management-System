<?php
require_once "session_bootstrap.php";
header("Content-Type: application/json");
require_once "db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    http_response_code(401);
    echo json_encode(["ok" => false, "message" => "Unauthorized"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $supervisors = [];
    $supResult = $conn->query("SELECT id, name, department FROM Supervisors WHERE status = 'approved' AND is_active = 1 ORDER BY name ASC");
    if ($supResult) {
        while ($row = $supResult->fetch_assoc()) {
            $supervisors[] = $row;
        }
    }
    echo json_encode(["ok" => true, "supervisors" => $supervisors]);
    exit;
}

$action = trim($_POST["action"] ?? "");
$eventId = (int)($_POST["event_id"] ?? 0);
if ($eventId <= 0) {
    http_response_code(422);
    echo json_encode(["ok" => false, "message" => "Invalid event id."]);
    exit;
}

if ($action === "delete") {
    $delPrograms = $conn->prepare("DELETE FROM Programs WHERE event_id = ?");
    if ($delPrograms) {
        $delPrograms->bind_param("i", $eventId);
        $delPrograms->execute();
        $delPrograms->close();
    }
    $delRegistrations = $conn->prepare("DELETE FROM Registrations WHERE event_id = ?");
    if ($delRegistrations) {
        $delRegistrations->bind_param("i", $eventId);
        $delRegistrations->execute();
        $delRegistrations->close();
    }

    $stmt = $conn->prepare("DELETE FROM Events WHERE id = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["ok" => false, "message" => "Failed to delete event."]);
        exit;
    }
    $stmt->bind_param("i", $eventId);
    $ok = $stmt->execute();
    $stmt->close();

    echo json_encode(["ok" => (bool)$ok, "message" => $ok ? "Event deleted successfully." : "Could not delete event."]);
    exit;
}

if ($action !== "update") {
    http_response_code(422);
    echo json_encode(["ok" => false, "message" => "Invalid action."]);
    exit;
}

$title = trim($_POST["title"] ?? "");
$eventType = trim($_POST["event_type"] ?? "");
$description = trim($_POST["description"] ?? "");
$date = trim($_POST["date"] ?? "");
$lastRegistrationDate = trim($_POST["last_registration_date"] ?? "");
$feeType = strtolower(trim($_POST["fee_type"] ?? ""));
$location = trim($_POST["location"] ?? "");
$capacity = (int)($_POST["capacity"] ?? 0);
$assignedSupervisorId = (int)($_POST["assigned_supervisor_id"] ?? 0);

if ($title === "" || $eventType === "" || $date === "" || $lastRegistrationDate === "" || $location === "" || $capacity <= 0 || $assignedSupervisorId <= 0) {
    http_response_code(422);
    echo json_encode(["ok" => false, "message" => "Please fill in all required fields correctly."]);
    exit;
}
if (!in_array($feeType, ["free", "paid"], true)) {
    http_response_code(422);
    echo json_encode(["ok" => false, "message" => "Invalid fee type."]);
    exit;
}

$stmt = $conn->prepare("UPDATE Events SET title = ?, event_type = ?, description = ?, date = ?, last_registration_date = ?, fee_type = ?, assigned_supervisor_id = ?, location = ?, capacity = ? WHERE id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["ok" => false, "message" => "Failed to update event."]);
    exit;
}
$stmt->bind_param("ssssssisii", $title, $eventType, $description, $date, $lastRegistrationDate, $feeType, $assignedSupervisorId, $location, $capacity, $eventId);
$ok = $stmt->execute();
$stmt->close();

echo json_encode(["ok" => (bool)$ok, "message" => $ok ? "Event updated successfully." : "Could not update event."]);
exit;
?>
