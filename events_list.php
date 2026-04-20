<?php
require_once "session_bootstrap.php";
header("Content-Type: application/json");
require_once "db.php";

if (!isset($_SESSION["role"]) || !in_array($_SESSION["role"], ["admin", "supervisor", "student"], true)) {
    http_response_code(401);
    echo json_encode(["ok" => false, "message" => "Unauthorized"]);
    exit;
}

$events = [];
$isSupervisor = ($_SESSION["role"] === "supervisor");
$currentSupervisorId = (int)($_SESSION["user_id"] ?? 0);
$scope = $_GET["scope"] ?? "all";

$query = "SELECT e.id, e.title, e.event_type, e.description, e.date, e.last_registration_date, e.fee_type, e.location, e.capacity, e.status, e.assigned_supervisor_id, s.name AS assigned_supervisor_name
          FROM Events e
          LEFT JOIN Supervisors s ON s.id = e.assigned_supervisor_id";

if ($isSupervisor && $scope === "my") {
    $query .= " WHERE e.assigned_supervisor_id = " . $currentSupervisorId;
}

$query .= " ORDER BY e.date DESC, e.id DESC";

$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row["can_manage"] = $isSupervisor && ((int)$row["assigned_supervisor_id"] === $currentSupervisorId);
        $events[] = $row;
    }
}

echo json_encode(["ok" => true, "events" => $events]);
exit;
?>
