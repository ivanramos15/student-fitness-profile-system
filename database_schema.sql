-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: student_health
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(20) NOT NULL,
  `time_done` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `remark` varchar(255) NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fitness_test`
--

DROP TABLE IF EXISTS `fitness_test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fitness_test` (
  `test_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `test-type` enum('pre-test','post-test') NOT NULL,
  `height` decimal(5,2) NOT NULL,
  `weight` decimal(5,2) NOT NULL,
  `body_mass_index` decimal(5,2) NOT NULL,
  `max_volume_of_oxygen` decimal(5,2) NOT NULL,
  `flexibility` int(11) NOT NULL,
  `strength` int(11) NOT NULL,
  `agility` int(11) NOT NULL,
  `speed` int(11) NOT NULL,
  `endurance` int(11) NOT NULL,
  `date-taken` date NOT NULL,
  PRIMARY KEY (`test_id`),
  KEY `test_id` (`test_id`),
  KEY `student_id` (`student_id`),
  KEY `strength` (`strength`),
  KEY `agility` (`agility`),
  KEY `speed` (`speed`),
  KEY `endurance` (`endurance`),
  KEY `criterion` (`flexibility`,`strength`,`agility`,`speed`,`endurance`) USING BTREE,
  CONSTRAINT `fitness_test_ibfk_1` FOREIGN KEY (`flexibility`) REFERENCES `score` (`score_value`),
  CONSTRAINT `fitness_test_ibfk_2` FOREIGN KEY (`strength`) REFERENCES `score` (`score_value`),
  CONSTRAINT `fitness_test_ibfk_3` FOREIGN KEY (`agility`) REFERENCES `score` (`score_value`),
  CONSTRAINT `fitness_test_ibfk_4` FOREIGN KEY (`speed`) REFERENCES `score` (`score_value`),
  CONSTRAINT `fitness_test_ibfk_5` FOREIGN KEY (`endurance`) REFERENCES `score` (`score_value`),
  CONSTRAINT `fitness_test_ibfk_6` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `generate_test_id` BEFORE INSERT ON `fitness_test` FOR EACH ROW BEGIN
    DECLARE max_num INT DEFAULT 0;
    
    -- Find the maximum existing test number
    SELECT IFNULL(MAX(CAST(SUBSTRING(test_id, 6) AS UNSIGNED)), 0) INTO max_num
    FROM `fitness_test`
    WHERE test_id LIKE 'TEST-%';
    
    -- Set the new test_id in TEST-0001 format
    SET NEW.test_id = CONCAT('TEST-', LPAD(max_num + 1, 4, '0'));
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `program`
--

DROP TABLE IF EXISTS `program`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `program` (
  `program_id` varchar(20) NOT NULL,
  `program_name` varchar(100) NOT NULL,
  PRIMARY KEY (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `role`
--

DROP TABLE IF EXISTS `role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role` (
  `role_id` varchar(10) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `generate_role_id` BEFORE INSERT ON `role` FOR EACH ROW BEGIN
    DECLARE max_num INT DEFAULT 0;
    
    -- Find the maximum existing number
    SELECT IFNULL(MAX(CAST(SUBSTRING(role_id, 6) AS UNSIGNED)), 0) INTO max_num
    FROM `role`
    WHERE role_id LIKE 'ROLE-%';
    
    -- Set the new role_id in ROLE-0001 format
    SET NEW.role_id = CONCAT('ROLE-', LPAD(max_num + 1, 4, '0'));
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `score`
--

DROP TABLE IF EXISTS `score`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `score` (
  `score_value` int(11) NOT NULL,
  `score_name` varchar(15) NOT NULL,
  PRIMARY KEY (`score_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `section`
--

DROP TABLE IF EXISTS `section`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `section` (
  `section_id` varchar(3) NOT NULL,
  `program_id` varchar(20) NOT NULL,
  PRIMARY KEY (`section_id`,`program_id`),
  KEY `program_id` (`program_id`),
  CONSTRAINT `section_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `program` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `student`
--

DROP TABLE IF EXISTS `student`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student` (
  `student_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `user_id` varchar(20) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `student_sex` enum('Male','Female') NOT NULL,
  `date_of_birth` date NOT NULL,
  PRIMARY KEY (`student_id`),
  UNIQUE KEY `user_id_2` (`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `student_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `generate_student_id` BEFORE INSERT ON `student` FOR EACH ROW BEGIN
    DECLARE max_id INT DEFAULT 0;
    
    SELECT IFNULL(MAX(CAST(SUBSTRING(student_id, 5) AS UNSIGNED)), 0) INTO max_id
    FROM student;
    
    SET NEW.student_id = CONCAT('ST-', LPAD(max_id + 1, 4, '0'));
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `student_section`
--

DROP TABLE IF EXISTS `student_section`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_section` (
  `student_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `section_id` varchar(3) NOT NULL,
  `program_id` varchar(20) NOT NULL,
  KEY `section_id` (`section_id`,`program_id`),
  KEY `student_id` (`student_id`),
  KEY `program_id` (`program_id`),
  CONSTRAINT `student_section_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`),
  CONSTRAINT `student_section_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `section` (`section_id`),
  CONSTRAINT `student_section_ibfk_3` FOREIGN KEY (`program_id`) REFERENCES `section` (`program_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `teacher`
--

DROP TABLE IF EXISTS `teacher`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher` (
  `teacher_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `user_id` varchar(20) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  PRIMARY KEY (`teacher_id`),
  UNIQUE KEY `user_id_2` (`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `teacher_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `generate_teacher_id` BEFORE INSERT ON `teacher` FOR EACH ROW BEGIN
    DECLARE max_id INT DEFAULT 0;
    
    SELECT IFNULL(MAX(CAST(SUBSTRING(teacher_id, 5) AS UNSIGNED)), 0) INTO max_id
    FROM teacher;
    
    SET NEW.teacher_id = CONCAT('TC-', LPAD(max_id + 1, 4, '0'));
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `user_id` varchar(20) NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `role` varchar(10) NOT NULL,
  KEY `user_id` (`user_id`),
  KEY `role` (`role`),
  CONSTRAINT `user_ibfk_2` FOREIGN KEY (`role`) REFERENCES `role` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `generate_user_id` BEFORE INSERT ON `user` FOR EACH ROW BEGIN
    DECLARE max_num INT DEFAULT 0;
    
    -- Find the maximum existing number
    SELECT IFNULL(MAX(CAST(SUBSTRING(user_id, 6) AS UNSIGNED)), 0) INTO max_num
    FROM `user`
    WHERE user_id LIKE 'USER-%';
    
    -- Set the new user_id in USER-0001 format
    SET NEW.user_id = CONCAT('USER-', LPAD(max_num + 1, 4, '0'));
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Dumping routines for database 'student_health'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

--
-- Seed data: reference rows needed for a working install
--

INSERT INTO `role` (`role_id`, `role_name`) VALUES
('ROLE-0001', 'student'),
('ROLE-0002', 'teacher');

INSERT INTO `program` (`program_id`, `program_name`) VALUES
('BLIS', 'Bachelor of Library and Information Science'),
('BSIS', 'Bachelor of Science in Information Systems'),
('BSIT', 'Bachelor of Science in Information Technology');

INSERT INTO `section` (`section_id`, `program_id`) VALUES
('1A', 'BLIS'),
('1A', 'BSIS'),
('1A', 'BSIT'),
('1B', 'BLIS'),
('1B', 'BSIS'),
('1B', 'BSIT'),
('2A', 'BLIS'),
('2A', 'BSIS'),
('2A', 'BSIT'),
('2B', 'BLIS'),
('2B', 'BSIS'),
('2B', 'BSIT');


/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-29 15:10:16
