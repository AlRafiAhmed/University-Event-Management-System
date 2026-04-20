CREATE DATABASE IF NOT EXISTS university_event_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE university_event_system;

CREATE TABLE Admins (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  profile_image VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admins_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE Supervisors (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  designation VARCHAR(255) NOT NULL,
  department VARCHAR(255) NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  profile_image VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_supervisors_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE Students (
  id INT NOT NULL AUTO_INCREMENT,
  student_id VARCHAR(100) NOT NULL,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  department VARCHAR(255) NOT NULL,
  profile_image VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_students_student_id (student_id),
  UNIQUE KEY uq_students_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE Events (
  id INT NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  event_type VARCHAR(100) NOT NULL,
  description TEXT,
  date DATE NOT NULL,
  last_registration_date DATE NOT NULL,
  fee_type ENUM('free','paid') NOT NULL DEFAULT 'free',
  event_banner VARCHAR(255) DEFAULT NULL,
  assigned_supervisor_id INT NULL,
  location VARCHAR(255) NOT NULL,
  capacity INT NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  created_by_admin INT NOT NULL,
  approved_by_supervisor INT NULL,
  PRIMARY KEY (id),
  KEY idx_events_created_by_admin (created_by_admin),
  KEY idx_events_assigned_supervisor_id (assigned_supervisor_id),
  KEY idx_events_approved_by_supervisor (approved_by_supervisor),
  CONSTRAINT fk_events_created_by_admin FOREIGN KEY (created_by_admin) REFERENCES Admins (id),
  CONSTRAINT fk_events_assigned_supervisor_id FOREIGN KEY (assigned_supervisor_id) REFERENCES Supervisors (id),
  CONSTRAINT fk_events_approved_by_supervisor FOREIGN KEY (approved_by_supervisor) REFERENCES Supervisors (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE Programs (
  id INT NOT NULL AUTO_INCREMENT,
  event_id INT NOT NULL,
  program_name VARCHAR(255) NOT NULL,
  serial_no INT NOT NULL,
  participant_name VARCHAR(255) DEFAULT NULL,
  description TEXT,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_programs_event_id (event_id),
  CONSTRAINT fk_programs_event_id FOREIGN KEY (event_id) REFERENCES Events (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE Registrations (
  id INT NOT NULL AUTO_INCREMENT,
  student_id INT NOT NULL,
  event_id INT NOT NULL,
  volunteer_assignment VARCHAR(500) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_registrations_student_event (student_id, event_id),
  KEY idx_registrations_student_id (student_id),
  KEY idx_registrations_event_id (event_id),
  CONSTRAINT fk_registrations_student_id FOREIGN KEY (student_id) REFERENCES Students (id),
  CONSTRAINT fk_registrations_event_id FOREIGN KEY (event_id) REFERENCES Events (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
