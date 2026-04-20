<?php
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register_student.html");
    exit;
}

$student_id = trim($_POST["student_id"] ?? "");
$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$password = trim($_POST["password"] ?? "");
$department = trim($_POST["department"] ?? "");
$profileImagePath = null;

if ($student_id === "" || $name === "" || $email === "" || $password === "" || $department === "") {
    header("Location: register_student.html?error=" . urlencode("All fields are required."));
    exit;
}

if (isset($_FILES["profile_image"]) && $_FILES["profile_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES["profile_image"]["error"] !== UPLOAD_ERR_OK) {
        header("Location: register_student.html?error=" . urlencode("Profile image upload failed."));
        exit;
    }
    $allowedExtensions = ["jpg", "jpeg", "png", "gif", "webp"];
    $extension = strtolower(pathinfo($_FILES["profile_image"]["name"] ?? "", PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        header("Location: register_student.html?error=" . urlencode("Only image files are allowed."));
        exit;
    }
    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "profiles";
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
        header("Location: register_student.html?error=" . urlencode("Could not create upload directory."));
        exit;
    }
    $fileName = "student_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $extension;
    $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
    if (!move_uploaded_file($_FILES["profile_image"]["tmp_name"], $targetPath)) {
        header("Location: register_student.html?error=" . urlencode("Could not save profile image."));
        exit;
    }
    $profileImagePath = "uploads/profiles/" . $fileName;
}

$stmt = $conn->prepare("INSERT INTO Students (student_id, name, email, password, department, profile_image) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $student_id, $name, $email, $password, $department, $profileImagePath);

if ($stmt->execute()) {
    header("Location: login.html?success=" . urlencode("Student registration successful. Please login."));
    exit;
}

header("Location: register_student.html?error=" . urlencode("Registration failed: " . $stmt->error));
exit;
