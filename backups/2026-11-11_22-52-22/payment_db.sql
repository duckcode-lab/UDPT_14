/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.19-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: payment_db
-- ------------------------------------------------------
-- Server version	10.11.19-MariaDB-ubu2204

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
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `id` varchar(36) NOT NULL,
  `booking_id` varchar(36) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'VNPAY_MOCK',
  `status` enum('SUCCESS','FAILED') DEFAULT 'SUCCESS',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_id` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES
('17f6d1db-b1b2-42b8-a6cf-f7a9b308523b','ab7dbd1a-f8c9-49ff-ae7a-a93c5778eb99',1,120000,'VNPAY','SUCCESS','2026-11-11 13:35:12'),
('31fefdd7-7ef9-4a85-9358-b7f2dc0c8ffe','99fdd46c-e3fa-4305-a9a8-d2b203807c8c',1,120000,'VNPAY','SUCCESS','2026-11-11 13:34:01'),
('3bfa6505-10ae-4421-894a-f9847f11b0e0','ed6d25bc-6049-4465-8d78-8e3b22b3443e',2,120000,'VNPAY','SUCCESS','2026-11-11 13:34:42'),
('3e391a7e-6ed4-4a68-b679-71b5adf9e76f','d8725296-1121-447c-9279-f56e734387d5',8,135000,'VNPAY_FLASH_SALE','SUCCESS','2026-11-11 13:57:20'),
('422e93d6-69df-467c-9864-16fe485f4b92','1539c294-06fc-4ed2-99e3-b290cf9ea6cd',7,135000,'VNPAY_FLASH_SALE','SUCCESS','2026-11-11 13:57:20'),
('4ee62da2-6b10-40c3-a129-1cd02bee453d','3d788492-624b-49a0-8c48-3543531a4623',13,135000,'VNPAY_FLASH_SALE','SUCCESS','2026-11-11 13:57:20'),
('77e1963b-2db7-4323-bbde-f7f73fbecc19','70eaa4a9-279c-44fe-a3fb-466d7441c82d',1,120000,'VNPAY','SUCCESS','2026-11-11 15:43:21'),
('9d98b169-8abd-4911-bbf6-97be0cdfb710','def7dcef-b72a-4557-8272-195917150a81',1,120000,'VNPAY','SUCCESS','2026-11-11 15:51:58'),
('c23dcf22-ee3d-46a1-a4c5-c3f41d848eba','447b8c30-2b17-4b05-98c0-b87848655ca6',1,120000,'VNPAY','SUCCESS','2026-11-11 13:33:45');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-11 15:52:22
