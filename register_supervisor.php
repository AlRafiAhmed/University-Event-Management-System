<?php
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register_supervisor.html");
    exit;
}

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$password = trim($_POST["password"] ?? "");
$designation = trim($_POST["designation"] ?? "");
$department = trim($_POST["department"] ?? "");
$profileImagePath = null;

if ($name === "" || $email === "" || $password === "" || $designation === "" || $department === "") {
    header("Location: register_supervisor.html?error=" . urlencode("All fields are required."));
    exit;
}

if (isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES["profile_image"]["error"] !== UPLOAD_ERR_OK) {
        header("Location: register_supervisor.html?error=" . urlencode("Profile image upload failed."));
        exit;
    }
    $allowedExtensions = ["jpg", "jpeg", "png", "gif", "webp"];
    $extension = strtolower(pathinfo($_FILES["profile_image"]["name"] ?? "", PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        header("Location: register_supervisor.html?error=" . urlencode("Only image files are allowed."));
        exit;
    }
    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "profiles";
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
        header("Location: register_supervisor.html?error=" . urlencode("Could not create upload directory."));
        exit;
    }
    $fileName = "supervisor_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $extension;
    $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
    if (!move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetPath)) {
        header("Location: register_supervisor.html?error=" . urlencode("Could not save profile image."));
        exit;
    }
    $profileImagePath = "uploads/profiles/" . $fileName;
}

$status = "pending";
$stmt = $conn->prepare("INSERT INTO Supervisors (name, email, password, designation, department, status, profile_image) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $name, $email, $password, $designation, $department, $status, $profileImagePath);

if ($stmt->execute()) {
    header("Location: login.html?success=" . urlencode("Supervisor registered. Wait for approval before login."));
    exit;
}

header("Location: register_supervisor.html?error=" . urlencode("Registration failed: " . $stmt->error));
exit;
