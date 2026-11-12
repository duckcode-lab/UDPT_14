/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.19-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: booking_db
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
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bookings` (
  `id` varchar(36) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `showtime_id` int(11) NOT NULL,
  `seat_code` varchar(20) NOT NULL,
  `price` int(11) NOT NULL,
  `status` enum('PENDING','CONFIRMED','CANCELLED') DEFAULT 'PENDING',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `movie_title` varchar(255) DEFAULT NULL,
  `theater_name` varchar(255) DEFAULT NULL,
  `room` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_show_seat` (`showtime_id`,`seat_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` VALUES
('99fdd46c-e3fa-4305-a9a8-d2b203807c8c',1,'khachhang@gmail.com',1,'C4',120000,'CONFIRMED','2026-11-11 13:34:01','2026-11-11 13:34:01','Avengers: Secret Wars','CGV Vincom','Ph├▓ng 01 (IMAX)'),
('ab7dbd1a-f8c9-49ff-ae7a-a93c5778eb99',1,'test.user@cinema.com',1,'D4',120000,'CONFIRMED','2026-11-11 13:35:12','2026-11-11 13:35:12','Avengers: Secret Wars','CGV Vincom','Ph├▓ng 01 (IMAX)'),
('ed6d25bc-6049-4465-8d78-8e3b22b3443e',2,'lethiba@gmail.com',1,'B2',120000,'CONFIRMED','2026-11-11 13:34:42','2026-11-11 13:34:42','Avengers: Secret Wars','CGV Vincom','Ph├▓ng 01 (IMAX)');
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `outbox`
--

DROP TABLE IF EXISTS `outbox`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `outbox` (
  `id` varchar(36) NOT NULL,
  `aggregate_type` varchar(50) NOT NULL,
  `aggregate_id` varchar(36) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `status` enum('PENDING','PROCESSED','FAILED') DEFAULT 'PENDING',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `outbox`
--

LOCK TABLES `outbox` WRITE;
/*!40000 ALTER TABLE `outbox` DISABLE KEYS */;
INSERT INTO `outbox` VALUES
('2027a77b-4224-4096-9e1a-496a6a7e723d','BOOKING','99fdd46c-e3fa-4305-a9a8-d2b203807c8c','BOOKING_CONFIRMED','{\"booking_id\":\"99fdd46c-e3fa-4305-a9a8-d2b203807c8c\",\"user_id\":1,\"user_email\":\"khachhang@gmail.com\",\"showtime_id\":1,\"seat_code\":\"C4\",\"price\":120000,\"movie_title\":\"Avengers: Secret Wars\",\"theater_name\":\"CGV Vincom\",\"room\":\"Ph├▓ng 01 (IMAX)\",\"transaction_id\":\"31fefdd7-7ef9-4a85-9358-b7f2dc0c8ffe\",\"timestamp\":\"2026-11-11T13:34:01.341Z\"}','PROCESSED','2026-11-11 13:34:01'),
('23b86ce4-d023-4a07-9fd7-f3f1d668321b','BOOKING','ed6d25bc-6049-4465-8d78-8e3b22b3443e','BOOKING_CONFIRMED','{\"booking_id\":\"ed6d25bc-6049-4465-8d78-8e3b22b3443e\",\"user_id\":2,\"user_email\":\"lethiba@gmail.com\",\"showtime_id\":1,\"seat_code\":\"B2\",\"price\":120000,\"movie_title\":\"Avengers: Secret Wars\",\"theater_name\":\"CGV Vincom\",\"room\":\"Ph├▓ng 01 (IMAX)\",\"transaction_id\":\"3bfa6505-10ae-4421-894a-f9847f11b0e0\",\"timestamp\":\"2026-11-11T13:34:42.789Z\"}','PROCESSED','2026-11-11 13:34:42'),
('c2990b1c-b1bf-4309-9ba8-acf352dea0a7','BOOKING','ab7dbd1a-f8c9-49ff-ae7a-a93c5778eb99','BOOKING_CONFIRMED','{\"booking_id\":\"ab7dbd1a-f8c9-49ff-ae7a-a93c5778eb99\",\"user_id\":1,\"user_email\":\"test.user@cinema.com\",\"showtime_id\":1,\"seat_code\":\"D4\",\"price\":120000,\"movie_title\":\"Avengers: Secret Wars\",\"theater_name\":\"CGV Vincom\",\"room\":\"Ph├▓ng 01 (IMAX)\",\"transaction_id\":\"17f6d1db-b1b2-42b8-a6cf-f7a9b308523b\",\"timestamp\":\"2026-11-11T13:35:12.879Z\"}','PROCESSED','2026-11-11 13:35:12');
/*!40000 ALTER TABLE `outbox` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-11 13:56:14
