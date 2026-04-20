<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "university_event_system";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Add is_active column for Students if missing.
$studentActiveCol = $conn->query("SHOW COLUMNS FROM Students LIKE 'is_active'");
if ($studentActiveCol && $studentActiveCol->num_rows === 0) {
    $conn->query("ALTER TABLE Students ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1");
}

// Add is_active column for Supervisors if missing.
$supervisorActiveCol = $conn->query("SHOW COLUMNS FROM Supervisors LIKE 'is_active'");
if ($supervisorActiveCol && $supervisorActiveCol->num_rows === 0) {
    $conn->query("ALTER TABLE Supervisors ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1");
}

// Add profile_image column for Admins if missing.
$adminProfileCol = $conn->query("SHOW COLUMNS FROM Admins LIKE 'profile_image'");
if ($adminProfileCol && $adminProfileCol->num_rows === 0) {
    $conn->query("ALTER TABLE Admins ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL");
}

// Add profile_image column for Students if missing.
$studentProfileCol = $conn->query("SHOW COLUMNS FROM Students LIKE 'profile_image'");
if ($studentProfileCol && $studentProfileCol->num_rows === 0) {
    $conn->query("ALTER TABLE Students ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL");
}

// Add profile_image column for Supervisors if missing.
$supervisorProfileCol = $conn->query("SHOW COLUMNS FROM Supervisors LIKE 'profile_image'");
if ($supervisorProfileCol && $supervisorProfileCol->num_rows === 0) {
    $conn->query("ALTER TABLE Supervisors ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL");
}

// Ensure there is at least one admin account for portal login.
$defaultAdminName = "System Admin";
$defaultAdminEmail = "admin@iubat.edu";
$defaultAdminPassword = "admin123";

$adminCheckStmt = $conn->prepare("SELECT id FROM Admins WHERE email = ? LIMIT 1");
if ($adminCheckStmt) {
    $adminCheckStmt->bind_param("s", $defaultAdminEmail);
    $adminCheckStmt->execute();
    $adminCheckStmt->store_result();
    if ($adminCheckStmt->num_rows === 0) {
        $adminInsertStmt = $conn->prepare("INSERT INTO Admins (name, email, password) VALUES (?, ?, ?)");
        if ($adminInsertStmt) {
            $adminInsertStmt->bind_param("sss", $defaultAdminName, $defaultAdminEmail, $defaultAdminPassword);
            $adminInsertStmt->execute();
            $adminInsertStmt->close();
        }
    }
    $adminCheckStmt->close();
}

// Ensure one approved supervisor exists for login testing.
$defaultSupervisorName = "Default Supervisor";
$defaultSupervisorEmail = "supervisor@iubat.edu";
$defaultSupervisorPassword = "super123";
$defaultSupervisorDesignation = "Lecturer";
$defaultSupervisorDepartment = "CSE";
$defaultSupervisorStatus = "approved";

$supCheckStmt = $conn->prepare("SELECT id FROM Supervisors WHERE email = ? LIMIT 1");
if ($supCheckStmt) {
    $supCheckStmt->bind_param("s", $defaultSupervisorEmail);
    $supCheckStmt->execute();
    $supCheckStmt->store_result();
    if ($supCheckStmt->num_rows === 0) {
        $supInsertStmt = $conn->prepare("INSERT INTO Supervisors (name, email, password, designation, department, status) VALUES (?, ?, ?, ?, ?, ?)");
        if ($supInsertStmt) {
            $supInsertStmt->bind_param("ssssss", $defaultSupervisorName, $defaultSupervisorEmail, $defaultSupervisorPassword, $defaultSupervisorDesignation, $defaultSupervisorDepartment, $defaultSupervisorStatus);
            $supInsertStmt->execute();
            $supInsertStmt->close();
        }
    }
    $supCheckStmt->close();
}

// Add event_type column for Events if missing.
$eventTypeCol = $conn->query("SHOW COLUMNS FROM Events LIKE 'event_type'");
if ($eventTypeCol && $eventTypeCol->num_rows === 0) {
    $conn->query("ALTER TABLE Events ADD COLUMN event_type VARCHAR(100) NOT NULL DEFAULT 'General' AFTER title");
}

// Add last_registration_date column for Events if missing.
$lastRegDateCol = $conn->query("SHOW COLUMNS FROM Events LIKE 'last_registration_date'");
if ($lastRegDateCol && $lastRegDateCol->num_rows === 0) {
    $conn->query("ALTER TABLE Events ADD COLUMN last_registration_date DATE NOT NULL DEFAULT '2099-12-31' AFTER date");
}

// Add fee_type column for Events if missing.
$feeTypeCol = $conn->query("SHOW COLUMNS FROM Events LIKE 'fee_type'");
if ($feeTypeCol && $feeTypeCol->num_rows === 0) {
    $conn->query("ALTER TABLE Events ADD COLUMN fee_type ENUM('free','paid') NOT NULL DEFAULT 'free' AFTER last_registration_date");
}

// Add event_banner column for Events if missing.
$eventBannerCol = $conn->query("SHOW COLUMNS FROM Events LIKE 'event_banner'");
if ($eventBannerCol && $eventBannerCol->num_rows === 0) {
    $conn->query("ALTER TABLE Events ADD COLUMN event_banner VARCHAR(255) DEFAULT NULL AFTER fee_type");
}

// Add assigned_supervisor_id column for Events if missing.
$assignedSupervisorCol = $conn->query("SHOW COLUMNS FROM Events LIKE 'assigned_supervisor_id'");
if ($assignedSupervisorCol && $assignedSupervisorCol->num_rows === 0) {
    $conn->query("ALTER TABLE Events ADD COLUMN assigned_supervisor_id INT NULL AFTER event_banner");
    $conn->query("ALTER TABLE Events ADD INDEX idx_events_assigned_supervisor_id (assigned_supervisor_id)");
    $conn->query("ALTER TABLE Events ADD CONSTRAINT fk_events_assigned_supervisor_id FOREIGN KEY (assigned_supervisor_id) REFERENCES Supervisors (id)");
}

// Add participant_name column for Programs if missing.
$participantNameCol = $conn->query("SHOW COLUMNS FROM Programs LIKE 'participant_name'");
if ($participantNameCol && $participantNameCol->num_rows === 0) {
    $conn->query("ALTER TABLE Programs ADD COLUMN participant_name VARCHAR(255) DEFAULT NULL AFTER serial_no");
}
?>
