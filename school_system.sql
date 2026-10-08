
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";




CREATE TABLE `course` (
  `id` int(11) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `credit` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `course` (`id`, `course_name`, `course_code`, `credit`) VALUES
(1, 'ADBMS', 'ADB101', 3),
(2, 'Assemble Language', 'ASM101', 3),
(3, 'Web Development', 'WEB101', 3),
(4, 'Software Security', 'SEC101', 3),
(5, 'Requirement Engineering', 'REQ101', 3),
(6, 'Tool and Practice Engineering', 'TOOL101', 4),
(10, 'afaan oroomo', '1212', 3),
(11, 'English', '1010', 4),
(12, 'gadaa system', 'gds', 4),
(13, 'java', 'jv', 4),
(14, 'c++', 'cpp', 4);



CREATE TABLE `course_assignments` (
  `assignment_id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `teacher_id` int(11) NOT NULL,
  `course_code` varchar(50) NOT NULL,
  `semester` varchar(20) DEFAULT NULL,
  `section` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `course_assignments` (`assignment_id`, `course_id`, `teacher_id`, `course_code`, `semester`, `section`, `created_at`) VALUES
(5, 1, 2, '', NULL, NULL, '2025-12-09 13:05:17'),
(8, 2, 3, '', NULL, NULL, '2025-12-09 13:05:43'),
(9, 4, 4, '', NULL, NULL, '2025-12-09 13:05:51'),
(10, 6, 8, '', NULL, NULL, '2025-12-09 13:06:02'),
(11, 3, 5, '', NULL, NULL, '2025-12-09 13:06:10'),
(12, 5, 6, '', NULL, NULL, '2025-12-09 13:40:47');



CREATE TABLE `exam` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `quiz` float DEFAULT 0,
  `test` float DEFAULT 0,
  `assignment` float DEFAULT 0,
  `project` float DEFAULT 0,
  `mid` float DEFAULT 0,
  `final` float DEFAULT 0,
  `total` float DEFAULT 0,
  `grade` varchar(2) DEFAULT 'F',
  `gpa` float DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `exam` (`id`, `student_id`, `teacher_id`, `course_code`, `quiz`, `test`, `assignment`, `project`, `mid`, `final`, `total`, `grade`, `gpa`) VALUES
(1, 1, 2, 'ADB101', 4, 5, 10, 13, 12, 40, 84, 'B', 3.36),
(2, 2, 2, 'ASM101', 4, 4, 10, 20, 20, 34, 92, 'A', 4),
(4, 2, 6, 'REQ101', 3, 3, 7, 9, 6, 8, 36, 'F', 0),
(6, 10, 3, 'WEB101', 5, 5, 7, 19, 17, 34, 87, 'A-', 3.7),
(7, 1, 3, 'WEB101', 5, 3, 7, 16, 15, 34.99, 80.99, 'B+', 3.3),
(8, 6, 3, 'WEB101', 5, 5, 9, 16, 19, 38, 92, 'A', 4),
(9, 2, 3, 'WEB101', 5, 5, 9, 20, 20, 38, 97, 'A', 4),
(10, 8, 3, 'WEB101', 5, 4, 9, 20, 16, 39, 93, 'A', 4),
(11, 10, 5, 'ADB101', 5, 5, 9.99, 20, 18, 39, 96.99, 'A', 4),
(12, 1, 5, 'ADB101', 3, 4, 8, 18, 20, 38, 91, 'A', 4),
(13, 11, 5, 'ADB101', 3, 4, 9, 17, 18, 38.99, 89.99, 'A-', 3.7),
(14, 10, 4, 'SEC101', 4, 5, 10, 18, 18, 40, 95, 'A', 4),
(15, 2, 4, 'SEC101', 4, 5, 8, 18, 19, 40, 94, 'A', 4),
(16, 8, 4, 'SEC101', 3, 4, 7, 18, 17, 39, 88, 'A-', 3.7),
(17, 6, 2, 'ADB101', 3, 5, 10, 20, 16, 37, 91, 'A', 4),
(18, 6, 6, 'REQ101', 5, 4, 8, 20, 20, 40, 97, 'A', 4),
(19, 7, 6, 'REQ101', 4, 5, 10, 20, 20, 39, 98, 'A', 4);



CREATE TABLE `student` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `student` (`id`, `name`, `username`, `password`) VALUES
(1, 'Biruk', 'biruk', '123'),
(2, 'Gosa', 'gosa', '123'),
(4, 'Leelisa', 'leelisa', '123'),
(6, 'galanaa', 'galana', '123'),
(7, 'muusa', 'musa', '123'),
(8, 'kumaa', 'kuma', '123'),
(9, 'jireenyaa', 'jirenya', '123'),
(10, 'bilisumma', 'bilisuma', '123'),
(11, 'ebbisa', 'ebisa', '123');


CREATE TABLE `teacher` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teacher') NOT NULL DEFAULT 'teacher',
  `profile_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `teacher` (`id`, `name`, `username`, `password`, `role`, `profile_pic`) VALUES
(1, 'Admin User', 'admin', 'admin123', 'admin', NULL),
(2, 'Mr. Dedefo', 'dedefo', '123', 'teacher', NULL),
(3, 'Mr. Tsion', 'tsion', '123', 'teacher', NULL),
(4, 'Mr. Gemeda', 'gemeda', '123', 'teacher', NULL),
(5, 'Mr. Miratu', 'miratu', '123', 'teacher', NULL),
(6, 'Mr. Abebe', 'abebe', '123', 'teacher', NULL),
(8, 'Mr. Fikiru', 'fikiru', '123', 'teacher', NULL),
(12, 'dr.Gelano', 'glema', '1039', 'teacher', NULL),
(13, 'Dr.caala', 'cala', '123', 'teacher', NULL);



CREATE TABLE `teacher_course` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `course_code` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `teacher_course` (`id`, `teacher_id`, `course_code`) VALUES
(1, 2, 'ADB101'),
(2, 2, 'ASM101'),
(3, 3, 'WEB101'),
(4, 4, 'SEC101'),
(5, 5, 'ADB101'),
(6, 6, 'REQ101'),
(7, 8, 'TOOL101'),
(12, 12, '1010'),
(13, 12, 'cpp'),
(14, 13, 'cpp');


ALTER TABLE `course`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_code` (`course_code`);


ALTER TABLE `course_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `fk_teacher` (`teacher_id`),
  ADD KEY `fk_course` (`course_id`);


ALTER TABLE `exam`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `course_code` (`course_code`);


ALTER TABLE `student`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);


ALTER TABLE `teacher`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);


ALTER TABLE `teacher_course`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `course_code` (`course_code`);


ALTER TABLE `course`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;


ALTER TABLE `course_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;


ALTER TABLE `exam`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;


ALTER TABLE `student`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;


ALTER TABLE `teacher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;


ALTER TABLE `teacher_course`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;


ALTER TABLE `course_assignments`
  ADD CONSTRAINT `fk_course` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE CASCADE;


ALTER TABLE `exam`
  ADD CONSTRAINT `exam_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_ibfk_3` FOREIGN KEY (`course_code`) REFERENCES `course` (`course_code`) ON DELETE CASCADE;


ALTER TABLE `teacher_course`
  ADD CONSTRAINT `teacher_course_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacher_course_ibfk_2` FOREIGN KEY (`course_code`) REFERENCES `course` (`course_code`) ON DELETE CASCADE;
COMMIT;

