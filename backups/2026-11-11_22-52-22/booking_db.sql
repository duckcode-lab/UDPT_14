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
('1539c294-06fc-4ed2-99e3-b290cf9ea6cd',7,'buyer_7_0@gmail.com',1,'E1',135000,'CONFIRMED','2026-11-11 13:57:20','2026-11-11 13:57:20','Avengers: Secret Wars','CGV Vincom','Phòng 01 (IMAX)'),
('3d788492-624b-49a0-8c48-3543531a4623',13,'buyer_13_0@gmail.com',1,'E2',135000,'CONFIRMED','2026-11-11 13:57:20','2026-11-11 13:57:20','Avengers: Secret Wars','CGV Vincom','Phòng 01 (IMAX)'),
('70eaa4a9-279c-44fe-a3fb-466d7441c82d',1,'test.user@cinema.com',1,'D3',120000,'CONFIRMED','2026-11-11 15:43:21','2026-11-11 15:43:21','Avengers: Secret Wars','CGV Vincom','Phòng 01 (IMAX)'),
('99fdd46c-e3fa-4305-a9a8-d2b203807c8c',1,'khachhang@gmail.com',1,'C4',120000,'CONFIRMED','2026-11-11 13:34:01','2026-11-11 13:34:01','Avengers: Secret Wars','CGV Vincom','Phòng 01 (IMAX)'),
('ab7dbd1a-f8c9-49ff-ae7a-a93c5778eb99',1,'test.user@cinema.com',1,'D4',120000,'CONFIRMED','2026-11-11 13:35:12','2026-11-11 13:35:12','Avengers: Secret Wars','CGV Vincom','Phòng 01 (IMAX)'),
('d8725296-1121-447c-9279-f56e734387d5',8,'buyer_8_0@gmail.com',1,'E3',135000,'CONFIRMED','2026-11-11 13:57:20','2026-11-11 13:57:20','Avengers: Secret Wars','CGV Vincom','Phòng 01 (IMAX)'),
('def7dcef-b72a-4557-8272-195917150a81',1,'nguyenvana@gmail.com',1,'B7',120000,'CONFIRMED','2026-11-11 15:51:58','2026-11-11 15:51:58','Avengers: Secret Wars','CGV Vincom','Phòng 01 (IMAX)'),
('ed6d25bc-6049-4465-8d78-8e3b22b3443e',2,'lethiba@gmail.com',1,'B2',120000,'CONFIRMED','2026-11-11 13:34:42','2026-11-11 13:34:42','Avengers: Secret Wars','CGV Vincom','Phòng 01 (IMAX)');
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
('2027a77b-4224-4096-9e1a-496a6a7e723d','BOOKING','99fdd46c-e3fa-4305-a9a8-d2b203807c8c','BOOKING_CONFIRMED','{\"booking_id\":\"99fdd46c-e3fa-4305-a9a8-d2b203807c8c\",\"user_id\":1,\"user_email\":\"khachhang@gmail.com\",\"showtime_id\":1,\"seat_code\":\"C4\",\"price\":120000,\"movie_title\":\"Avengers: Secret Wars\",\"theater_name\":\"CGV Vincom\",\"room\":\"Phòng 01 (IMAX)\",\"transaction_id\":\"31fefdd7-7ef9-4a85-9358-b7f2dc0c8ffe\",\"timestamp\":\"2026-11-11T13:34:01.341Z\"}','PROCESSED','2026-11-11 13:34:01'),
('23b86ce4-d023-4a07-9fd7-f3f1d668321b','BOOKING','ed6d25bc-6049-4465-8d78-8e3b22b3443e','BOOKING_CONFIRMED','{\"booking_id\":\"ed6d25bc-6049-4465-8d78-8e3b22b3443e\",\"user_id\":2,\"user_email\":\"lethiba@gmail.com\",\"showtime_id\":1,\"seat_code\":\"B2\",\"price\":120000,\"movie_title\":\"Avengers: Secret Wars\",\"theater_name\":\"CGV Vincom\",\"room\":\"Phòng 01 (IMAX)\",\"transaction_id\":\"3bfa6505-10ae-4421-894a-f9847f11b0e0\",\"timestamp\":\"2026-11-11T13:34:42.789Z\"}','PROCESSED','2026-11-11 13:34:42'),
('277d0a39-f1f7-4d8c-b2c9-226bcd28ef6e','BOOKING','1539c294-06fc-4ed2-99e3-b290cf9ea6cd','BOOKING_CONFIRMED','{\"booking_id\":\"1539c294-06fc-4ed2-99e3-b290cf9ea6cd\",\"user_id\":7,\"user_email\":\"buyer_7_0@gmail.com\",\"showtime_id\":1,\"seat_code\":\"E1\",\"price\":135000,\"movie_title\":\"Avengers: Secret Wars\",\"theater_name\":\"CGV Vincom\",\"room\":\"Phòng 01 (IMAX)\",\"transaction_id\":\"422e93d6-69df-467c-9864-16fe485f4b92\",\"timestamp\":\"2026-11-11T13:57:20.763Z\"}','PROCESSED','2026-11-11 13:57:20'),
('4a0be2b2-3fd7-4dbb-83c0-7d308e32f20e','BOOKING','3d788492-624b-49a0-8c48-3543531a4623','BOOKING_CONFIRMED','{\"booking_id\":\"3d788492-624b-49a0-8c48-3543531a4623\",\"user_id\":13,\"user_email\":\"buyer_13_0@gmail.com\",\"showtime_id\":1,\"seat_code\":\"E2\",\"price\":135000,\"movie_title\":\"Avengers: Secret Wars\",\"theater_name\":\"CGV Vincom\",\"room\":\"Phòng 01 (IMAX)\",\"transaction_id\":\"4ee62da2-6b10-40c3-a129-1cd02bee453d\",\"timestamp\":\"2026-11-11T13:57:20.760Z\"}','PROCESSED','2026-11-11 13:57:20'),
('6b54f16c-0067-4d30-99e2-a986a75862d2','BOOKING','70eaa4a9-279c-44fe-a3fb-466d7441c82d','BOOKING_CONFIRMED','{\"booking_id\":\"70eaa4a9-279c-44fe-a3fb-466d7441c82d\",\"user_id\":1,\"user_email\":\"test.user@cinema.com\",\"showtime_id\":1,\"seat_code\":\"D3\",\"price\":120000,\"movie_title\":\"Avengers: Secret Wars\",\"theater_name\":\"CGV Vincom\",\"room\":\"Phòng 01 (IMAX)\",\"transaction_id\":\"77e1963b-2db7-4323-bbde-f7f73fbecc19\",\"timestamp\":\"2026-11-11T15:43:21.718Z\"}','PROCESSED','2026-11-11 15:43:21'),
('84f22dc9-1220-482d-be3f-60dd1362aa3e','BOOKING','def7dcef-b72a-4557-8272-195917150a81','BOOKING_CONFIRMED','{\"booking_id\":\"def7dcef-b72a-4557-8272-195917150a81\",\"user_id\":1,\"user_email\":\"nguyenvana@gmail.com\",\"showtime_id\":1,\"seat_code\":\"B7\",\"price\":120000,\"movie_title\":\"Avengers: Secret Wars\",\"theater_name\":\"CGV Vincom\",\"room\":\"Phòng 01 (IMAX)\",\"transaction_id\":\"9d98b169-8abd-4911-bbf6-97be0cdfb710\",\"timestamp\":\"2026-11-11T15:51:58.684Z\"}','PROCESSED','2026-11-11 15:51:58'),
('c2990b1c-b1bf-4309-9ba8-acf352dea0a7','BOOKING','ab7dbd1a-f8c9-49ff-ae7a-a93c5778eb99','BOOKING_CONFIRMED','{\"booking_id\":\"ab7dbd1a-f8c9-49ff-ae7a-a93c5778eb99\",\"user_id\":1,\"user_email\":\"test.user@cinema.com\",\"showtime_id\":1,\"seat_code\":\"D4\",\"price\":120000,\"movie_title\":\"Avengers: Secret Wars\",\"theater_name\":\"CGV Vincom\",\"room\":\"Phòng 01 (IMAX)\",\"transaction_id\":\"17f6d1db-b1b2-42b8-a6cf-f7a9b308523b\",\"timestamp\":\"2026-11-11T13:35:12.879Z\"}','PROCESSED','2026-11-11 13:35:12'),
('d402766c-bf4c-48ae-a767-39dbf031fa90','BOOKING','d8725296-1121-447c-9279-f56e734387d5','BOOKING_CONFIRMED','{\"booking_id\":\"d8725296-1121-447c-9279-f56e734387d5\",\"user_id\":8,\"user_email\":\"buyer_8_0@gmail.com\",\"showtime_id\":1,\"seat_code\":\"E3\",\"price\":135000,\"movie_title\":\"Avengers: Secret Wars\",\"theater_name\":\"CGV Vincom\",\"room\":\"Phòng 01 (IMAX)\",\"transaction_id\":\"3e391a7e-6ed4-4a68-b679-71b5adf9e76f\",\"timestamp\":\"2026-11-11T13:57:20.763Z\"}','PROCESSED','2026-11-11 13:57:20');
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

-- Dump completed on 2026-11-11 15:52:22
