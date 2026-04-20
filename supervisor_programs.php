<?php
require_once "session_bootstrap.php";
header("Content-Type: application/json");
require_once "db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "supervisor") {
    http_response_code(401);
    echo json_encode(["ok" => false, "message" => "Unauthorized"]);
    exit;
}

$supervisorId = (int)($_SESSION["user_id"] ?? 0);
$eventId = (int)($_REQUEST["event_id"] ?? 0);
if ($eventId <= 0) {
    http_response_code(422);
    echo json_encode(["ok" => false, "message" => "Invalid event id."]);
    exit;
}

$authStmt = $conn->prepare("SELECT id FROM Events WHERE id = ? AND assigned_supervisor_id = ? LIMIT 1");
if (!$authStmt) {
    http_response_code(500);
    echo json_encode(["ok" => false, "message" => "Failed to verify event access."]);
    exit;
}
$authStmt->bind_param("ii", $eventId, $supervisorId);
$authStmt->execute();
$authStmt->store_result();
if ($authStmt->num_rows !== 1) {
    $authStmt->close();
    http_response_code(403);
    echo json_encode(["ok" => false, "message" => "You can only manage your assigned events."]);
    exit;
}
$authStmt->close();

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $programs = [];
    $listStmt = $conn->prepare("SELECT id, program_name, serial_no, participant_name, description, start_time, end_time FROM Programs WHERE event_id = ? ORDER BY serial_no ASC, id ASC");
    if ($listStmt) {
        $listStmt->bind_param("i", $eventId);
        $listStmt->execute();
        $result = $listStmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $programs[] = $row;
        }
        $listStmt->close();
    }

    echo json_encode(["ok" => true, "programs" => $programs]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["ok" => false, "message" => "Method not allowed"]);
    exit;
}

$programName = trim($_POST["program_name"] ?? "");
$serialNo = (int)($_POST["serial_no"] ?? 0);
$participantName = trim($_POST["participant_name"] ?? "");
$description = trim($_POST["description"] ?? "");
$startTime = trim($_POST["start_time"] ?? "");
$endTime = trim($_POST["end_time"] ?? "");

if ($programName === "" || $serialNo <= 0 || $startTime === "" || $endTime === "") {
    http_response_code(422);
    echo json_encode(["ok" => false, "message" => "Please fill all required program fields."]);
    exit;
}

$insertStmt = $conn->prepare("INSERT INTO Programs (event_id, program_name, serial_no, participant_name, description, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
if (!$insertStmt) {
    http_response_code(500);
    echo json_encode(["ok" => false, "message" => "Failed to prepare program query."]);
    exit;
}
$insertStmt->bind_param("isissss", $eventId, $programName, $serialNo, $participantName, $description, $startTime, $endTime);
$ok = $insertStmt->execute();
$insertStmt->close();

if (!$ok) {
    http_response_code(500);
    echo json_encode(["ok" => false, "message" => "Failed to add program."]);
    exit;
}

echo json_encode(["ok" => true, "message" => "Program and participant added successfully."]);
exit;
?>
