<?php
require_once "session_bootstrap.php";
require_once "db.php";
header("Content-Type: application/json");

if (!isset($_SESSION["role"], $_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["ok" => false, "message" => "Unauthorized"]);
    exit;
}

$role = $_SESSION["role"];
$userId = (int)$_SESSION["user_id"];

if ($role === "admin") {
    $table = "Admins";
    $fields = ["name", "email"];
} elseif ($role === "supervisor") {
    $table = "Supervisors";
    $fields = ["name", "email", "designation", "department", "status"];
} elseif ($role === "student") {
    $table = "Students";
    $fields = ["student_id", "name", "email", "department"];
} else {
    http_response_code(403);
    echo json_encode(["ok" => false, "message" => "Invalid role"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $selectFields = implode(", ", array_merge(["id"], $fields, ["profile_image"]));
    $stmt = $conn->prepare("SELECT {$selectFields} FROM {$table} WHERE id = ? LIMIT 1");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["ok" => false, "message" => "Failed to load profile."]);
        exit;
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $profile = $result ? $result->fetch_assoc() : null;
    $stmt->close();
    if (!$profile) {
        http_response_code(404);
        echo json_encode(["ok" => false, "message" => "Profile not found."]);
        exit;
    }
    echo json_encode(["ok" => true, "role" => $role, "profile" => $profile]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["ok" => false, "message" => "Method not allowed"]);
    exit;
}

$currentImage = trim($_POST["current_profile_image"] ?? "");
$newImagePath = $currentImage;

if (isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES["profile_image"]["error"] !== UPLOAD_ERR_OK) {
        http_response_code(422);
        echo json_encode(["ok" => false, "message" => "Profile image upload failed."]);
        exit;
    }
    $allowedExtensions = ["jpg", "jpeg", "png", "gif", "webp"];
    $extension = strtolower(pathinfo($_FILES["profile_image"]["name"] ?? "", PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        http_response_code(422);
        echo json_encode(["ok" => false, "message" => "Only image files are allowed."]);
        exit;
    }
    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "profiles";
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
        http_response_code(500);
        echo json_encode(["ok" => false, "message" => "Failed to create upload directory."]);
        exit;
    }
    $fileName = $role . "_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $extension;
    $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
    if (!move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetPath)) {
        http_response_code(500);
        echo json_encode(["ok" => false, "message" => "Failed to save profile image."]);
        exit;
    }
    $newImagePath = "uploads/profiles/" . $fileName;

    if ($currentImage !== "") {
        $oldPath = __DIR__ . DIRECTORY_SEPARATOR . str_replace("/", DIRECTORY_SEPARATOR, $currentImage);
        if (is_file($oldPath)) {
            @unlink($oldPath);
        }
    }
}

if (!isset($_FILES["profile_image"]) || $_FILES["profile_image"]["error"] === UPLOAD_ERR_NO_FILE) {
    http_response_code(422);
    echo json_encode(["ok" => false, "message" => "Please choose a profile picture first."]);
    exit;
}

if ($role === "admin") {
    $stmt = $conn->prepare("UPDATE Admins SET profile_image = ? WHERE id = ?");
    $stmt->bind_param("si", $newImagePath, $userId);
} elseif ($role === "supervisor") {
    $stmt = $conn->prepare("UPDATE Supervisors SET profile_image = ? WHERE id = ?");
    $stmt->bind_param("si", $newImagePath, $userId);
} else {
    $stmt = $conn->prepare("UPDATE Students SET profile_image = ? WHERE id = ?");
    $stmt->bind_param("si", $newImagePath, $userId);
}

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["ok" => false, "message" => "Failed to update profile."]);
    exit;
}

$ok = $stmt->execute();
$stmt->close();
echo json_encode(["ok" => (bool)$ok, "message" => $ok ? "Profile updated successfully." : "Could not update profile."]);
exit;
?>
