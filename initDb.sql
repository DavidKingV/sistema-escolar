-- =============================================================
-- initDb.sql — Script para inicializar la base de datos local
-- Base de datos: control_escolar_esmefis (local)
-- =============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS `control_escolar_esmefis`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE `control_escolar_esmefis`;

-- =============================================================
-- TABLAS SIN DEPENDENCIAS (sin FK hacia otras tablas)
-- =============================================================

CREATE TABLE IF NOT EXISTS `carreers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) NOT NULL,
  `area` varchar(100) NOT NULL,
  `subarea` varchar(100) NOT NULL,
  `descripcion` varchar(400) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `data_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefono` varchar(12) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) NOT NULL,
  `name` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `students_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(30) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `practical_hours_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `microsoft_admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `microsoft_id` text NOT NULL,
  `microsoft_user_name` varchar(200) NOT NULL,
  `microsoft_user_email` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `registrationApplications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstName` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lastNames` varchar(200) NOT NULL,
  `gender` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `birthday` date NOT NULL,
  `placeBirth` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nationality` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `curp` varchar(300) NOT NULL,
  `age` int(10) NOT NULL,
  `civilStatus` varchar(200) NOT NULL,
  `adress` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(13) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lastStudies` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `program` varchar(200) NOT NULL,
  `pdf` longblob NOT NULL,
  `approved` tinyint(1) DEFAULT 0,
  `controlNo` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clave` varchar(100) DEFAULT NULL,
  `nombre` text NOT NULL,
  `descripcion` varchar(300) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `teachers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) NOT NULL,
  `genero` varchar(25) NOT NULL,
  `nacimiento` varchar(11) NOT NULL,
  `estado_civil` varchar(30) NOT NULL,
  `telefono` varchar(12) NOT NULL,
  `email` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================================
-- TABLAS CON DEPENDENCIAS DE PRIMER NIVEL
-- =============================================================

CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_carreer` int(11) NOT NULL,
  `clave` varchar(100) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `fecha_inicio` varchar(11) NOT NULL,
  `fecha_termino` varchar(11) NOT NULL,
  `descripcion` varchar(400) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_carreer` (`id_carreer`),
  CONSTRAINT `groups_ibfk_2` FOREIGN KEY (`id_carreer`) REFERENCES `carreers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `login_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data_user` int(11) NOT NULL,
  `user` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `hashed_password` text DEFAULT NULL,
  `type` varchar(100) NOT NULL,
  `estado` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `data_user` (`data_user`),
  CONSTRAINT `login_users_ibfk_1` FOREIGN KEY (`data_user`) REFERENCES `data_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `subject_child` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_subject` int(11) NOT NULL,
  `clave` varchar(100) DEFAULT NULL,
  `nombre` varchar(200) NOT NULL,
  `descripcion` varchar(300) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_subject` (`id_subject`),
  CONSTRAINT `subject_child_ibfk_1` FOREIGN KEY (`id_subject`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `subjects_teachers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_subjet` int(11) NOT NULL,
  `id_teacher` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_subjet` (`id_subjet`),
  KEY `id_teacher` (`id_teacher`),
  CONSTRAINT `subjects_teachers_ibfk_1` FOREIGN KEY (`id_teacher`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `subjects_teachers_ibfk_2` FOREIGN KEY (`id_subjet`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `login_teachers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_teacher` int(11) NOT NULL,
  `user` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `hashed_password` text NOT NULL,
  `status` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_techaer` (`id_teacher`),
  CONSTRAINT `login_teachers_ibfk_1` FOREIGN KEY (`id_teacher`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================================
-- TABLAS CON DEPENDENCIAS DE SEGUNDO NIVEL
-- =============================================================

CREATE TABLE IF NOT EXISTS `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `no_control` varchar(30) NOT NULL,
  `noControlSEP` varchar(30) DEFAULT NULL,
  `nombre` varchar(250) NOT NULL,
  `genero` varchar(25) NOT NULL,
  `nacimiento` varchar(11) NOT NULL,
  `estado_civil` varchar(30) NOT NULL,
  `nacionalidad` varchar(50) NOT NULL,
  `curp` text NOT NULL,
  `telefono` varchar(13) NOT NULL,
  `email` varchar(100) NOT NULL,
  `id_group` int(11) DEFAULT NULL,
  `academical_status` int(11) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_control` (`no_control`),
  UNIQUE KEY `noControlSEP` (`noControlSEP`),
  KEY `id_group` (`id_group`),
  KEY `academical_status` (`academical_status`),
  CONSTRAINT `academicalStatus` FOREIGN KEY (`academical_status`) REFERENCES `students_status` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `students_ibfk_1` FOREIGN KEY (`id_group`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `groupsMaterial` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idGroup` int(11) NOT NULL,
  `name` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lastUpdate` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idGroup` (`idGroup`),
  CONSTRAINT `groupIds` FOREIGN KEY (`idGroup`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_group` int(11) NOT NULL,
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `date` date NOT NULL,
  `start` time NOT NULL,
  `end` time NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_group` (`id_group`),
  CONSTRAINT `groupId` FOREIGN KEY (`id_group`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `carreers_subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_subject` int(11) NOT NULL,
  `id_child_subject` int(11) DEFAULT NULL,
  `id_carreer` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subject_carreer` (`id_subject`,`id_carreer`),
  KEY `id_subjet` (`id_subject`),
  KEY `id_career` (`id_carreer`),
  KEY `id_child_subject` (`id_child_subject`),
  CONSTRAINT `carreers_subjects_ibfk_1` FOREIGN KEY (`id_subject`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `carreers_subjects_ibfk_2` FOREIGN KEY (`id_carreer`) REFERENCES `carreers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `carreers_subjects_ibfk_3` FOREIGN KEY (`id_child_subject`) REFERENCES `subject_child` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `childsubjects_techers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_childsubject` int(11) NOT NULL,
  `id_teacher` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_childsubject` (`id_childsubject`),
  KEY `id_teacher` (`id_teacher`),
  CONSTRAINT `childsubjects_techers_ibfk_1` FOREIGN KEY (`id_teacher`) REFERENCES `teachers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `childsubjects_techers_ibfk_2` FOREIGN KEY (`id_childsubject`) REFERENCES `subject_child` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================================
-- TABLAS CON DEPENDENCIAS DE TERCER NIVEL (dependen de students)
-- =============================================================

CREATE TABLE IF NOT EXISTS `login_students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `user` varchar(100) NOT NULL,
  `password` varchar(150) NOT NULL,
  `hashed_password` text DEFAULT NULL,
  `status` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_alumno` (`student_id`),
  CONSTRAINT `login_students_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `microsoft_students` (
  `id` varchar(300) NOT NULL,
  `student_id` int(11) NOT NULL,
  `displayName` varchar(200) NOT NULL,
  `mail` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_id` (`student_id`),
  CONSTRAINT `microsoft_students_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `payments_dates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_student` int(11) NOT NULL,
  `payment_day` int(11) NOT NULL,
  `concept` varchar(200) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_student` (`id_student`),
  CONSTRAINT `student_payments_date` FOREIGN KEY (`id_student`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `practical_hours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `googleCalendarId` varchar(300) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `id_student` int(15) NOT NULL,
  `date` date NOT NULL,
  `start` time NOT NULL,
  `end` time NOT NULL,
  `hours` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_spanish_ci DEFAULT NULL,
  `isConfirmed` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `no_repeat_date` (`id_student`,`date`),
  KEY `status_id` (`status_id`),
  CONSTRAINT `srudent_hours` FOREIGN KEY (`id_student`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `status_id` FOREIGN KEY (`status_id`) REFERENCES `practical_hours_status` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `students_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_student` int(11) NOT NULL,
  `payment_date` date NOT NULL DEFAULT current_timestamp(),
  `payment_method` int(11) NOT NULL,
  `invoice` tinyint(1) NOT NULL DEFAULT 0,
  `invoice_id` varchar(100) DEFAULT NULL,
  `concept` varchar(100) NOT NULL,
  `cost` decimal(8,2) NOT NULL,
  `extra` decimal(5,2) DEFAULT NULL,
  `total` decimal(6,2) NOT NULL,
  `comments` text NOT NULL,
  `registred_by` int(11) DEFAULT NULL,
  `password` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_payment` (`id_student`),
  CONSTRAINT `student_payment` FOREIGN KEY (`id_student`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `student_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `assigned_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_id` (`student_id`,`group_id`),
  KEY `group_id` (`group_id`),
  CONSTRAINT `student_groups_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `student_groups_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `tyc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `studentID` int(11) NOT NULL,
  `accepted` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `studentID` (`studentID`),
  CONSTRAINT `tyc_ibfk_1` FOREIGN KEY (`studentID`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================================
-- TABLAS CON DEPENDENCIAS DE CUARTO NIVEL (dependen de student_grades / practical_hours)
-- =============================================================

CREATE TABLE IF NOT EXISTS `student_grades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_student` int(11) NOT NULL,
  `id_subject` int(11) NOT NULL,
  `continuos_grade` decimal(5,2) NOT NULL,
  `exam_grade` decimal(5,2) NOT NULL,
  `final_grade` decimal(5,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `makeOver` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_subject` (`id_student`,`id_subject`),
  KEY `id_student` (`id_student`),
  KEY `id_subject` (`id_subject`),
  CONSTRAINT `student_grades_ibfk_1` FOREIGN KEY (`id_student`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `student_grades_ibfk_2` FOREIGN KEY (`id_subject`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------
-- makeOverGrades depende de student_grades, se crea después
-- student_grades.makeOver depende de makeOverGrades → ciclo
-- Se resuelve: crear makeOverGrades sin la FK circular primero,
-- luego agregar la FK en student_grades mediante ALTER TABLE.
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `makeOverGrades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `studentId` int(11) NOT NULL,
  `subjectId` int(11) NOT NULL,
  `subjectChildId` int(11) DEFAULT NULL,
  `continuosGrade` decimal(5,2) NOT NULL,
  `examGrade` decimal(5,2) NOT NULL,
  `finalGrade` decimal(5,2) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `lastModify` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `studentId` (`studentId`),
  KEY `subjectId` (`subjectId`),
  KEY `subjectChildId` (`subjectChildId`),
  CONSTRAINT `studentsId` FOREIGN KEY (`studentId`) REFERENCES `student_grades` (`id_student`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `subjectsId` FOREIGN KEY (`subjectId`) REFERENCES `student_grades` (`id_subject`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `subjectChildId` FOREIGN KEY (`subjectChildId`) REFERENCES `subject_child` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Agregar FK circular: student_grades.makeOver → makeOverGrades.id
ALTER TABLE `student_grades`
  ADD CONSTRAINT `makeover` FOREIGN KEY (`makeOver`) REFERENCES `makeOverGrades` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `student_grades_child` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_student` int(11) NOT NULL,
  `id_subject` int(11) NOT NULL,
  `id_subject_child` int(11) NOT NULL,
  `continuos_grade` decimal(5,2) NOT NULL,
  `exam_grade` decimal(5,2) NOT NULL,
  `final_grade` decimal(5,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `makeOverId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `studend_childsubject` (`id_student`,`id_subject`,`id_subject_child`),
  KEY `id_student` (`id_student`),
  KEY `id_subject` (`id_subject`),
  KEY `id_subject_child` (`id_subject_child`),
  KEY `makeOverId` (`makeOverId`),
  CONSTRAINT `makeOverChild` FOREIGN KEY (`makeOverId`) REFERENCES `makeOverGrades` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `student_grades_child_ibfk_1` FOREIGN KEY (`id_student`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `student_grades_child_ibfk_2` FOREIGN KEY (`id_subject_child`) REFERENCES `subject_child` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `student_practice_qr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `practical_hour_id` int(11) NOT NULL,
  `qr_payload` varchar(255) NOT NULL,
  `qr_image_url` varchar(500) NOT NULL,
  `qr_storage_provider` varchar(50) NOT NULL DEFAULT 'cloudflare',
  `qr_storage_key` varchar(255) DEFAULT NULL,
  `qr_hash` char(64) NOT NULL,
  `generated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_qr_student` (`student_id`),
  KEY `idx_practical_hour` (`practical_hour_id`),
  KEY `idx_student_active` (`student_id`,`active`),
  CONSTRAINT `fk_qr_practical_hour` FOREIGN KEY (`practical_hour_id`) REFERENCES `practical_hours` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_qr_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =============================================================
-- TABLAS DE ROLES/PERMISOS POR USUARIO
-- =============================================================

CREATE TABLE IF NOT EXISTS `user_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(150) NOT NULL,
  `provider` enum('local','microsoft') NOT NULL,
  `role_id` int(11) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `user_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(150) NOT NULL,
  `provider` enum('local','microsoft') NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- =============================================================
-- DATOS SEMILLA MÍNIMOS PARA PODER USAR EL SISTEMA
-- =============================================================

-- Estados académicos de alumnos
INSERT IGNORE INTO `students_status` (`id`, `status`) VALUES
  (1, 'Activo'),
  (2, 'Baja temporal'),
  (3, 'Baja definitiva'),
  (4, 'Egresado');

-- Estados de horas prácticas
INSERT IGNORE INTO `practical_hours_status` (`id`, `status`) VALUES
  (1, 'Pendiente'),
  (2, 'Confirmada'),
  (3, 'Rechazada');

-- Roles base del sistema
INSERT IGNORE INTO `roles` (`id`, `slug`, `name`, `is_admin`) VALUES
  (1, 'admin',    'Administrador', 1),
  (2, 'control',  'Control Escolar', 0),
  (3, 'teacher',  'Docente', 0),
  (4, 'student',  'Alumno', 0);

-- Permisos base del sistema
INSERT IGNORE INTO `permissions` (`slug`, `name`) VALUES
  ('ver_alumnos',      'Ver alumnos'),
  ('editar_alumnos',   'Editar alumnos'),
  ('ver_calificaciones',   'Ver calificaciones'),
  ('editar_calificaciones','Editar calificaciones'),
  ('ver_pagos',        'Ver pagos'),
  ('editar_pagos',     'Editar pagos'),
  ('ver_grupos',       'Ver grupos'),
  ('editar_grupos',    'Editar grupos'),
  ('ver_horarios',     'Ver horarios'),
  ('editar_horarios',  'Editar horarios');
