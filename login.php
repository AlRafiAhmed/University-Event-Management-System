<?php
require_once "session_bootstrap.php";
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.html");
    exit;
}

$email = trim($_POST["email"] ?? "");
$password = trim($_POST["password"] ?? "");

if ($email === "" || $password === "") {
    header("Location: login.html?error=" . urlencode("All fields are required."));
    exit;
}

// 1) Admin table match
$stmt = $conn->prepare("SELECT id, name FROM Admins WHERE email = ? AND password = ?");
if (!$stmt) {
    header("Location: login.html?error=" . urlencode("Login system error."));
    exit;
}
$stmt->bind_param("ss", $email, $password);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 1) {
    $stmt->bind_result($id, $name);
    $stmt->fetch();
    $_SESSION["user_id"] = $id;
    $_SESSION["name"] = $name;
    $_SESSION["role"] = "admin";
    $stmt->close();
    header("Location: admin_dashboard.php");
    exit;
}
$stmt->close();

// 2) Student table match
$stmt = $conn->prepare("SELECT id, name, is_active FROM Students WHERE email = ? AND password = ?");
if (!$stmt) {
    header("Location: login.html?error=" . urlencode("Login system error."));
    exit;
}
$stmt->bind_param("ss", $email, $password);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 1) {
    $stmt->bind_result($id, $name, $is_active);
    $stmt->fetch();
    if ((int)$is_active !== 1) {
        $stmt->close();
        header("Location: login.html?error=" . urlencode("Your account is deactive."));
        exit;
    }
    $_SESSION["user_id"] = $id;
    $_SESSION["name"] = $name;
    $_SESSION["role"] = "student";
    $stmt->close();
    header("Location: student_dashboard.php");
    exit;
}
$stmt->close();

// 3) Supervisor table match
$stmt = $conn->prepare("SELECT id, name, status, is_active FROM Supervisors WHERE email = ? AND password = ?");
if (!$stmt) {
    header("Location: login.html?error=" . urlencode("Login system error."));
    exit;
}
$stmt->bind_param("ss", $email, $password);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 1) {
    $stmt->bind_result($id, $name, $status, $is_active);
    $stmt->fetch();
    if ((int)$is_active !== 1) {
        $stmt->close();
        header("Location: login.html?error=" . urlencode("Your account is deactive."));
        exit;
    }
    if ($status !== "approved") {
        $stmt->close();
        header("Location: login.html?error=" . urlencode("Supervisor account is pending approval."));
        exit;
    }
    $_SESSION["user_id"] = $id;
    $_SESSION["name"] = $name;
    $_SESSION["role"] = "supervisor";
    $stmt->close();
    header("Location: supervisor_dashboard.php");
    exit;
}
$stmt->close();

header("Location: login.html?error=" . urlencode("Invalid email or password."));
exit;
