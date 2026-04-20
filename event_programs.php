<?php
require_once "session_bootstrap.php";
header("Content-Type: application/json");
require_once "db.php";

if (!isset($_SESSION["role"]) || !in_array($_SESSION["role"], ["admin", "supervisor", "student"], true)) {
    http_response_code(401);
    echo json_encode(["ok" => false, "message" => "Unauthorized"]);
    exit;
}

$eventId = (int)($_GET["event_id"] ?? 0);
if ($eventId <= 0) {
    http_response_code(422);
    echo json_encode(["ok" => false, "message" => "Invalid event id."]);
    exit;
}

$programs = [];
$stmt = $conn->prepare("SELECT id, program_name, serial_no, participant_name, description, start_time, end_time FROM Programs WHERE event_id = ? ORDER BY serial_no ASC, id ASC");
if ($stmt) {
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }
    $stmt->close();
}

echo json_encode(["ok" => true, "programs" => $programs]);
exit;
?>
