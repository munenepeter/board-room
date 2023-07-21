-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: board_room
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(20) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'IT','Information Technology'),(2,'HR','Human Resources'),(3,'FIN','Finance'),(4,'OTH','Other');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `employee_no` int NOT NULL,
  `username` varchar(30) NOT NULL,
  `email` varchar(30) NOT NULL,
  `password` text NOT NULL,
  `department_id` int DEFAULT NULL,
  KEY `idx_employee_no` (`employee_no`),
  KEY `fk_department_id` (`department_id`),
  CONSTRAINT `fk_department_id` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (201041307,'mwaruguru','mwaruguru@kenyalaw.org','$2y$10$pJwuUF0LHnOvZ6ygCoI4iOmSBqq/MDCFAJvPJdvKfHINvrR06ZOrC',4),(1001,'john_doe','john.doe@example.com','$2y$10$EwtP.50YnqK0LC/yKRO5du/vunq/d8fGcADaOw2MGUabBgDRsF7JK',4),(1002,'jane_smith','jane.smith@example.com','$2y$10$8A1fQNYlSBQi55a5.g6T5.J8lJW6bfsvWs6nsr5w.V1KYN6nnUHzG',4),(1003,'michael_johnson','michael.johnson@example.com','$2y$10$FZPK/4lswVRZmu0ZowkpQOnRpF8W6UOzILc/yzEZG/Lz0kKLLR0jy',4),(1004,'emily_adams','emily.adams@example.com','$2y$10$84M6Rd7qLgEYyVGj3CYmm.xN2MNPYUdKnqMUTyOHgU3zZgt5OzA.6',1),(1005,'william_clark','william.clark@example.com','$2y$10$7kP3eZogQJ4A66qCwhxih.vqETZHKEDbs8y2rUH2r4fHtxaIEE9y6',2);
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meeting_details`
--

DROP TABLE IF EXISTS `meeting_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `meeting_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(10) NOT NULL,
  `duration` varchar(30) NOT NULL,
  `meeting_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meeting_details`
--

LOCK TABLES `meeting_details` WRITE;
/*!40000 ALTER TABLE `meeting_details` DISABLE KEYS */;
INSERT INTO `meeting_details` VALUES (1,'Meeting 1','01:00:00','2023-07-02 08:00:00'),(2,'Meeting 2','00:30:00','2023-07-03 08:00:00'),(3,'Meeting 3','02:00:00','2023-07-04 08:00:00'),(4,'Meeting 4','00:30:00:00','2023-07-14 13:44:00'),(5,'Meeting 5','01:30:00:00','2023-07-24 10:00:00');
/*!40000 ALTER TABLE `meeting_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meeting_types`
--

DROP TABLE IF EXISTS `meeting_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `meeting_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meeting_types`
--

LOCK TABLES `meeting_types` WRITE;
/*!40000 ALTER TABLE `meeting_types` DISABLE KEYS */;
INSERT INTO `meeting_types` VALUES (1,'Internal'),(2,'External'),(3,'Hybrid'),(4,'Other');
/*!40000 ALTER TABLE `meeting_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meetings`
--

DROP TABLE IF EXISTS `meetings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `meetings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `meeting_type_id` int NOT NULL,
  `employee_no` int NOT NULL,
  `meeting_details_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `meeting_type_id` (`meeting_type_id`),
  KEY `employee_no` (`employee_no`),
  KEY `meeting_details_id` (`meeting_details_id`),
  CONSTRAINT `meetings_ibfk_1` FOREIGN KEY (`meeting_type_id`) REFERENCES `meeting_types` (`id`),
  CONSTRAINT `meetings_ibfk_2` FOREIGN KEY (`employee_no`) REFERENCES `employees` (`employee_no`),
  CONSTRAINT `meetings_ibfk_3` FOREIGN KEY (`meeting_details_id`) REFERENCES `meeting_details` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meetings`
--

LOCK TABLES `meetings` WRITE;
/*!40000 ALTER TABLE `meetings` DISABLE KEYS */;
INSERT INTO `meetings` VALUES (10,1,1001,1,'2023-07-02 06:00:00'),(11,2,1002,2,'2023-07-03 11:30:00'),(12,3,1003,3,'2023-07-04 08:00:00'),(13,2,201041307,4,'2023-07-14 18:46:45'),(14,2,201041307,5,'2023-07-21 18:38:56');
/*!40000 ALTER TABLE `meetings` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-07-21 22:50:07
