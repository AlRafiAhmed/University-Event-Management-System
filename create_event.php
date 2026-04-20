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
    echo json_encode(["ok" => true, "message" => "Create Event page ready.", "supervisors" => $supervisors]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["ok" => false, "message" => "Method not allowed"]);
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
$createdByAdmin = (int)($_SESSION["user_id"] ?? 0);

if ($title === "" || $eventType === "" || $date === "" || $lastRegistrationDate === "" || $location === "" || $capacity <= 0 || $assignedSupervisorId <= 0 || $createdByAdmin <= 0) {
    http_response_code(422);
    echo json_encode(["ok" => false, "message" => "Please fill in all required fields correctly."]);
    exit;
}

if (!in_array($feeType, ["free", "paid"], true)) {
    http_response_code(422);
    echo json_encode(["ok" => false, "message" => "Please select a valid event type (free/paid)."]);
    exit;
}

if ($lastRegistrationDate > $date) {
    http_response_code(422);
    echo json_encode(["ok" => false, "message" => "Last registration date cannot be after event date."]);
    exit;
}

$supCheckStmt = $conn->prepare("SELECT id FROM Supervisors WHERE id = ? AND status = 'approved' AND is_active = 1 LIMIT 1");
if (!$supCheckStmt) {
    http_response_code(500);
    echo json_encode(["ok" => false, "message" => "Failed to validate supervisor."]);
    exit;
}
$supCheckStmt->bind_param("i", $assignedSupervisorId);
$supCheckStmt->execute();
$supCheckStmt->store_result();
if ($supCheckStmt->num_rows !== 1) {
    $supCheckStmt->close();
    http_response_code(422);
    echo json_encode(["ok" => false, "message" => "Please select a valid supervisor."]);
    exit;
}
$supCheckStmt->close();

$stmt = $conn->prepare("INSERT INTO Events (title, event_type, description, date, last_registration_date, fee_type, assigned_supervisor_id, location, capacity, created_by_admin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["ok" => false, "message" => "Failed to prepare query."]);
    exit;
}

$stmt->bind_param("ssssssissi", $title, $eventType, $description, $date, $lastRegistrationDate, $feeType, $assignedSupervisorId, $location, $capacity, $createdByAdmin);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
    http_response_code(500);
    echo json_encode(["ok" => false, "message" => "Failed to create event."]);
    exit;
}

echo json_encode(["ok" => true, "message" => "Event created successfully."]);
exit;
?>
