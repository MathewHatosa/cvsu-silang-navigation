CREATE DATABASE IF NOT EXISTS `campus_navigation_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `campus_navigation_db`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `navigation_history`;
DROP TABLE IF EXISTS `departments`;
DROP TABLE IF EXISTS `rooms`;
DROP TABLE IF EXISTS `facilities`;
DROP TABLE IF EXISTS `buildings`;

CREATE TABLE `buildings` (
    `building_id` INT NOT NULL AUTO_INCREMENT,
    `building_name` VARCHAR(255) NOT NULL,
    `latitude` DECIMAL(10, 8) NOT NULL,
    `longitude` DECIMAL(11, 8) NOT NULL,
    `description` TEXT NULL,
    PRIMARY KEY (`building_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `rooms` (
    `room_id` INT NOT NULL AUTO_INCREMENT,
    `building_id` INT NOT NULL,
    `room_number` VARCHAR(50) NOT NULL,
    `room_name` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`room_id`),
    KEY `idx_rooms_building` (`building_id`),
    CONSTRAINT `fk_rooms_building` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`building_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `departments` (
    `department_id` INT NOT NULL AUTO_INCREMENT,
    `building_id` INT NOT NULL,
    `department_name` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`department_id`),
    KEY `idx_departments_building` (`building_id`),
    CONSTRAINT `fk_departments_building` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`building_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `facilities` (
    `facility_id` INT NOT NULL AUTO_INCREMENT,
    `facility_name` VARCHAR(255) NOT NULL,
    `latitude` DECIMAL(10, 8) NOT NULL,
    `longitude` DECIMAL(11, 8) NOT NULL,
    PRIMARY KEY (`facility_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `navigation_history` (
    `history_id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NULL,
    `destination_id` INT NULL,
    `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`history_id`),
    KEY `idx_history_dest` (`destination_id`),
    CONSTRAINT `fk_history_building` FOREIGN KEY (`destination_id`) REFERENCES `buildings` (`building_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `buildings` (`building_id`, `building_name`, `latitude`, `longitude`, `description`) VALUES
(1, 'Building 1 - First Building', 14.24101000, 120.97891500, 'TED Faculty Offices, University Clinic, Classrooms 111-126, Male & Female CRs'),
(2, 'Building 2 - Second Building', 14.24093800, 120.97931000, 'Junior High Faculty, EBA, Diploma Storage, Classrooms 211-226, Male & Female CRs'),
(3, 'Building 3 - Third Building', 14.24152500, 120.97911800, 'Registrar, OSAS, AVR, Computer Labs, Library, Psychology, Management, CSG, 5 Floors & 6 CRs'),
(4, 'Hostel for HM & Tourism', 14.24146500, 120.97943000, 'Function Hall, Kitchen Lab, F&B Lab, Stock Room, CR Hallway'),
(5, 'Campus Gymnasium (Back of Stage)', 14.24115500, 120.97946500, 'Covered Court, ID Picture Studio, Storage Room, and Back-of-Stage Office');

INSERT INTO `departments` (`building_id`, `department_name`) VALUES
(1, 'Teacher Education Department (TED)'),
(2, 'Junior High School Department'),
(3, 'Department of Technology'),
(3, 'Department of Psychology'),
(3, 'Department of Management'),
(4, 'Hospitality Management & Tourism Department');

INSERT INTO `rooms` (`building_id`, `room_number`, `room_name`) VALUES
(1, '111', 'TED Faculty (Higher Position)'),
(1, '112', 'TED Faculty (Instructors)'),
(1, '113', 'Lecture Room 113'),
(1, '114', 'Lecture Room 114'),
(1, '115', 'Lecture Room 115'),
(1, '116', 'Lecture Room 116'),
(1, 'CLINIC', 'University Clinic (1st Floor)'),
(1, '1F-CRF', 'Female CR (1st Floor Left)'),
(1, '1F-CRM', 'Male CR (1st Floor Right)'),
(1, '121', 'Lecture Room 121'),
(1, '122', 'Lecture Room 122'),
(1, '123', 'Lecture Room 123'),
(1, '124', 'Lecture Room 124'),
(1, '125', 'Lecture Room 125'),
(1, '126', 'Lecture Room 126'),
(2, '211', 'Junior High Faculty'),
(2, '212', 'Lecture Room 212'),
(2, '213', 'Lecture Room 213'),
(2, '214', 'Lecture Room 214'),
(2, '215', 'Lecture Room 215'),
(2, '216', 'Lecture Room 216'),
(2, '2F-CRF', 'Female CR (1st Floor Left)'),
(2, '2F-CRM', 'Male CR (1st Floor Right)'),
(2, '221', 'EBA Room'),
(2, '222', 'Storage of Diploma'),
(2, '223', 'Lecture Room 223'),
(2, '224', 'Lecture Room 224'),
(2, '225', 'Lecture Room 225'),
(2, '226', 'Lecture Room 226'),
(3, '311', 'Registrar Office'),
(3, '312', 'OSAS Office'),
(3, '313', 'AVR Room'),
(3, '314', 'Tourism Management Room'),
(3, '315', 'Room 315'),
(3, '316', 'Room 316'),
(3, '3F1-CRF', 'Female CR (1st Floor Left)'),
(3, '3F1-CRM', 'Male CR (1st Floor Right)'),
(3, '321', 'Department of Technology'),
(3, '322', 'Computer Lab 1 (CL1)'),
(3, '323A', 'Computer Lab 2 (CL2)'),
(3, '323B', 'Computer Lab 3 (CL3)'),
(3, '324', 'Computer Lab 4 (CL4)'),
(3, '325', 'University Library'),
(3, '331', 'Department of Psychology'),
(3, '332', 'Psychology Lab'),
(3, '333A', 'Lecture Room 333A'),
(3, '333B', 'Lecture Room 333B'),
(3, '334A', 'Lecture Room 334A'),
(3, '334B', 'Lecture Room 334B'),
(3, '335', 'Research and Extension Office'),
(3, '3F3-CRF', 'Female CR (3rd Floor East Wing)'),
(3, '3F3-CRM', 'Male CR (3rd Floor East Wing)'),
(3, '341A', 'Lecture Room 341A'),
(3, '341B', 'Lecture Room 341B'),
(3, '342A', 'Lecture Room 342A'),
(3, '342B', 'Lecture Room 342B'),
(3, '343A', 'Lecture Room 343A'),
(3, '343B', 'Lecture Room 343B'),
(3, '344', 'Department of Management'),
(3, '345', 'Lecture Room 345'),
(3, '3F4-CRF', 'Female CR (4th Floor East Wing)'),
(3, '3F4-CRM', 'Male CR (4th Floor East Wing)'),
(3, '351', 'Lecture Room 351'),
(3, '352', 'Lecture Room 352'),
(3, '353', 'Lecture Room 353'),
(3, '354', 'Lecture Room 354'),
(3, '355', 'Lecture Room 355'),
(3, '356', 'Lecture Room 356'),
(3, '357', 'Lecture Room 357'),
(3, '358', 'Lecture Room 358'),
(3, '359', 'Lecture Room 359'),
(3, '360', 'Central Student Government (CSG)'),
(4, 'H-FH', 'Function Hall (Entrance Left)'),
(4, 'H-KL', 'Kitchen Lab (Entrance Right - 1st Room)'),
(4, 'H-FB', 'F&B Lab (Entrance Right - 2nd Room)'),
(4, 'H-SR', 'Stock Room (Entrance Right - End Room)'),
(4, 'H-CR', 'Hostel CR Hallway'),
(5, 'GYM-R', 'Studio for ID Picture (Back of Stage - Right)'),
(5, 'GYM-M', 'Storage Room (Back of Stage - Middle)'),
(5, 'GYM-L', 'Campus Office (Back of Stage - Left)');

INSERT INTO `facilities` (`facility_name`, `latitude`, `longitude`) VALUES
('Guard House / Main Gate', 14.24089000, 120.97874500),
('University Clinic (Bldg 1 - 1st Floor)', 14.24098200, 120.97906500),
('Campus Gymnasium / Covered Court', 14.24121500, 120.97916800),
('Student Canteen', 14.24178500, 120.97920500),
('Kubo 1 (Resting Area - Bldg 1 Front)', 14.24094500, 120.97888000),
('Kubo 2 (Resting Area - Bldg 2 Front)', 14.24087000, 120.97928500),
('Kubo 3 (Resting Area - Back of Bldg 3)', 14.24165500, 120.97904500),
('Main Emergency Exit (Roundabout)', 14.24032000, 120.97856000),
('Evacuation Area (Silang Monitoring Center)', 14.24036000, 120.97914000);

SET FOREIGN_KEY_CHECKS = 1;