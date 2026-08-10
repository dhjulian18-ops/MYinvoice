-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for osx10.10 (x86_64)
--
-- Host: localhost    Database: invoice_db
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

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
-- Table structure for table `app_settings`
--

DROP TABLE IF EXISTS `app_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_settings`
--

LOCK TABLES `app_settings` WRITE;
/*!40000 ALTER TABLE `app_settings` DISABLE KEYS */;
INSERT INTO `app_settings` VALUES (1,'support_name','Support Center','2026-08-05 20:25:13','2026-08-05 20:27:37'),(2,'support_subtitle','Aktif 24','2026-08-05 20:25:13','2026-08-05 20:27:37'),(3,'app_name','Pinvoice','2026-08-05 20:25:13','2026-08-05 20:28:49'),(4,'contact_email','support@myinvoice.com','2026-08-05 20:25:13','2026-08-05 20:25:54');
/*!40000 ALTER TABLE `app_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `businesses`
--

DROP TABLE IF EXISTS `businesses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `businesses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address1` varchar(255) DEFAULT NULL,
  `address2` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `tax_id` varchar(255) DEFAULT NULL,
  `tax_number` varchar(255) DEFAULT NULL,
  `license_number` varchar(255) DEFAULT NULL,
  `logo_base64` longtext DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `businesses_user_id_foreign` (`user_id`),
  CONSTRAINT `businesses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `businesses`
--

LOCK TABLES `businesses` WRITE;
/*!40000 ALTER TABLE `businesses` DISABLE KEYS */;
INSERT INTO `businesses` VALUES (6,8,'Bisnis dika',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-07-23 21:37:57','2026-07-23 21:37:57'),(7,9,'Bisnis julian',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-07-23 23:40:02','2026-07-23 23:40:02'),(9,11,'Bisnis dikajulian',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-07-27 00:00:30','2026-07-27 00:00:30');
/*!40000 ALTER TABLE `businesses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat_messages`
--

DROP TABLE IF EXISTS `chat_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` bigint(20) unsigned NOT NULL,
  `receiver_id` bigint(20) unsigned NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `chat_messages_sender_id_foreign` (`sender_id`),
  KEY `chat_messages_receiver_id_foreign` (`receiver_id`),
  CONSTRAINT `chat_messages_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat_messages`
--

LOCK TABLES `chat_messages` WRITE;
/*!40000 ALTER TABLE `chat_messages` DISABLE KEYS */;
INSERT INTO `chat_messages` VALUES (1,12,11,'p',1,'2026-08-04 23:05:49','2026-08-04 23:15:00'),(2,11,12,'p',1,'2026-08-04 23:15:04','2026-08-04 23:15:59'),(3,11,12,'pp',1,'2026-08-04 23:15:07','2026-08-04 23:15:59'),(4,12,11,'p',1,'2026-08-04 23:17:51','2026-08-04 23:18:04'),(5,12,11,'p',1,'2026-08-04 23:17:53','2026-08-04 23:18:04'),(6,12,11,'p',1,'2026-08-04 23:17:53','2026-08-04 23:18:04'),(7,11,12,'jih',1,'2026-08-04 23:18:28','2026-08-04 23:18:35'),(8,11,12,'min\'',1,'2026-08-04 23:20:07','2026-08-04 23:20:09'),(9,12,11,'ya',1,'2026-08-04 23:20:14','2026-08-04 23:20:15'),(10,12,11,'min',1,'2026-08-04 23:20:55','2026-08-04 23:20:56'),(11,11,12,'min',1,'2026-08-04 23:21:02','2026-08-04 23:21:03'),(12,12,11,'p',1,'2026-08-04 23:21:11','2026-08-04 23:21:22'),(13,11,11,'hi',1,'2026-08-04 23:24:20','2026-08-04 23:24:20'),(14,11,12,'min',1,'2026-08-05 19:42:37','2026-08-05 19:42:47'),(15,12,11,'y',1,'2026-08-05 19:42:53','2026-08-05 19:42:54'),(16,12,11,'ya',1,'2026-08-05 19:43:01','2026-08-05 19:43:02'),(17,11,12,'min',1,'2026-08-05 19:49:35','2026-08-05 20:00:27'),(18,11,12,'p',1,'2026-08-05 20:01:07','2026-08-05 20:01:30'),(19,11,12,'p',1,'2026-08-05 20:01:16','2026-08-05 20:01:30'),(20,12,11,'ya',1,'2026-08-05 20:01:33','2026-08-05 20:01:41'),(21,12,11,'p',1,'2026-08-05 20:05:49','2026-08-05 20:08:48'),(22,11,12,'ya',1,'2026-08-05 20:08:52','2026-08-05 20:09:02'),(23,12,11,'p',1,'2026-08-05 20:09:02','2026-08-05 20:09:19'),(24,12,11,'p',1,'2026-08-05 20:09:10','2026-08-05 20:09:19'),(25,12,11,'p',1,'2026-08-05 20:09:11','2026-08-05 20:09:19'),(26,12,11,'p',1,'2026-08-05 20:09:23','2026-08-05 20:09:28'),(27,11,12,'p',1,'2026-08-05 20:09:31','2026-08-05 20:09:32'),(28,11,12,'p',1,'2026-08-05 20:09:55','2026-08-05 20:10:00'),(29,12,11,'o',1,'2026-08-05 20:10:07','2026-08-05 20:17:05'),(30,12,11,'p',1,'2026-08-05 20:17:20','2026-08-05 20:17:21'),(31,12,11,'p',1,'2026-08-05 20:17:27','2026-08-05 20:19:36'),(32,12,11,'p',1,'2026-08-05 20:19:40','2026-08-05 20:19:50'),(33,12,11,'p',1,'2026-08-05 20:30:10','2026-08-05 20:31:53'),(34,12,11,'p',1,'2026-08-06 01:37:20','2026-08-06 01:37:27'),(35,11,12,'p',1,'2026-08-06 01:37:30','2026-08-06 01:37:31'),(36,11,12,'pp',1,'2026-08-06 01:37:37','2026-08-06 01:37:50'),(37,11,12,'p',1,'2026-08-06 01:37:46','2026-08-06 01:37:50'),(38,11,12,'p',1,'2026-08-07 18:55:42','2026-08-07 18:55:55'),(39,12,11,'p',1,'2026-08-07 18:55:57','2026-08-07 18:56:09'),(40,12,11,'p',1,'2026-08-07 18:56:05','2026-08-07 18:56:09'),(41,12,11,'p',1,'2026-08-07 18:56:05','2026-08-07 18:56:09'),(42,11,12,'p',1,'2026-08-07 18:56:15','2026-08-07 18:56:22'),(43,12,11,'p',1,'2026-08-07 18:56:25','2026-08-07 19:01:06'),(44,12,11,'p',1,'2026-08-07 18:59:15','2026-08-07 19:01:06'),(45,12,11,'p',1,'2026-08-07 19:01:11','2026-08-07 19:01:33'),(46,12,11,'p',1,'2026-08-07 19:01:11','2026-08-07 19:01:33'),(47,12,11,'pp',1,'2026-08-07 19:01:40','2026-08-07 20:25:07'),(48,12,11,'p',1,'2026-08-07 20:25:03','2026-08-07 20:25:07'),(49,12,11,'p',1,'2026-08-07 20:25:12','2026-08-07 20:25:22'),(50,11,12,'pp',1,'2026-08-07 20:25:29','2026-08-07 20:25:35'),(51,11,12,'p',0,'2026-08-07 20:25:53','2026-08-07 20:25:53'),(52,11,12,'pp',0,'2026-08-07 20:26:00','2026-08-07 20:26:00'),(53,11,12,'p',0,'2026-08-07 20:26:04','2026-08-07 20:26:04'),(54,11,12,'p',0,'2026-08-07 20:26:05','2026-08-07 20:26:05');
/*!40000 ALTER TABLE `chat_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clients` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address1` varchar(255) DEFAULT NULL,
  `address2` varchar(255) DEFAULT NULL,
  `shipping_address` varchar(255) DEFAULT NULL,
  `tax_id` varchar(255) DEFAULT NULL,
  `tax_number` varchar(255) DEFAULT NULL,
  `license` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clients_business_id_foreign` (`business_id`),
  CONSTRAINT `clients_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` VALUES (5,6,'dika','0909092833','dika13@gmail.com','subang',NULL,'subang','10','19','18','2026-07-23 21:38:01','2026-07-23 21:38:01'),(12,7,'dika','0909090909','dika12@gmail.com','subang',NULL,'subang','123','12','312','2026-07-26 23:52:21','2026-07-26 23:52:21'),(13,9,'dika','09908087979','dika12@gmail.com','subang',NULL,'subang','12','12','13','2026-07-28 21:37:22','2026-07-28 21:37:22');
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoice_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint(20) unsigned NOT NULL,
  `description` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(5,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_items_invoice_id_foreign` (`invoice_id`),
  CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_items`
--

LOCK TABLES `invoice_items` WRITE;
/*!40000 ALTER TABLE `invoice_items` DISABLE KEYS */;
INSERT INTO `invoice_items` VALUES (49,16,'cctv',2,200000.00,0.00,0.00,'2026-07-27 00:30:48','2026-07-27 00:30:48'),(54,21,'kipas',2,1000000.00,0.00,0.00,'2026-08-05 04:12:37','2026-08-05 04:12:37'),(55,22,'kipas',2,1000000.00,0.00,0.00,'2026-08-05 04:13:00','2026-08-05 04:13:00'),(56,23,'kipas',1,1000000.00,0.00,0.00,'2026-08-07 18:57:59','2026-08-07 18:57:59');
/*!40000 ALTER TABLE `invoice_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invoices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` bigint(20) unsigned NOT NULL,
  `client_id` bigint(20) unsigned DEFAULT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `invoice_title` varchar(255) NOT NULL DEFAULT 'INVOICE',
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(255) DEFAULT NULL,
  `date` varchar(255) NOT NULL,
  `due_date` varchar(255) NOT NULL,
  `po_number` varchar(255) DEFAULT NULL,
  `global_discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `global_tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  `shipping` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_status` varchar(255) NOT NULL DEFAULT 'unpaid',
  `status` varchar(255) NOT NULL DEFAULT 'unpaid',
  `payments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payments`)),
  `payment_method` varchar(255) DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `signature` text DEFAULT NULL,
  `currency` varchar(255) NOT NULL DEFAULT 'IDR Rp',
  `template` varchar(255) NOT NULL DEFAULT 'simple',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoices_business_id_foreign` (`business_id`),
  KEY `invoices_client_id_foreign` (`client_id`),
  CONSTRAINT `invoices_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoices_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES (16,6,NULL,'INV-1785137402708','INVOICE','iksan','090909909090','27/07/2026','03/08/2026',NULL,0.00,0.00,0.00,'paid','unpaid','[{\"method\":\"Cash\",\"accountNumber\":null,\"accountName\":null}]',NULL,'Tidak dapat dikembalikan\nHarga sudah termasuk pajak\nGaransi 1 bulan\nTerima kasih atas kepercayaan Anda','image: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AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAADAGPs/KroRnrqhS/sAAAAASUVORK5CYII=','IDR Rp','simple',NULL,'2026-07-27 00:30:48','2026-07-27 00:30:48'),(21,9,NULL,'INV-1785928343797','INVOICE','dika','09908087979','05/08/2026','12/08/2026',NULL,0.00,0.00,0.00,'unpaid','unpaid','[]',NULL,'Terima kasih','none','IDR Rp','simple',NULL,'2026-08-05 04:12:37','2026-08-05 04:12:37'),(22,9,NULL,'INV-1785928360959','INVOICE','dika','09908087979','05/08/2026','12/08/2026',NULL,0.00,0.00,0.00,'paid','unpaid','[]',NULL,'Terima kasih','none','IDR Rp','simple',NULL,'2026-08-05 04:13:00','2026-08-05 04:13:00'),(23,9,NULL,'INV-1786154193461','INVOICE','dika','09908087979','08/08/2026','15/08/2026',NULL,0.00,0.00,200000.00,'paid','unpaid','[{\"method\":\"Cash\",\"accountNumber\":null,\"accountName\":null}]',NULL,'Pembayaran dalam 7 hari\nGaransi 1 bulan','none','IDR Rp','simple',NULL,'2026-08-07 18:57:59','2026-08-07 18:57:59');
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `business_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit` varchar(255) DEFAULT NULL,
  `discount_type` varchar(255) NOT NULL DEFAULT 'none',
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(5,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `items_business_id_foreign` (`business_id`),
  CONSTRAINT `items_business_id_foreign` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `items`
--

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;
INSERT INTO `items` VALUES (3,6,'cctv',150000.00,3,NULL,'none',0.00,0.00,NULL,'2026-07-23 21:38:01','2026-07-23 21:38:01'),(4,7,'cctv',150000.00,3,NULL,'none',0.00,0.00,NULL,'2026-07-23 23:40:09','2026-07-23 23:40:09'),(9,9,'kipas',1000000.00,1,NULL,'none',0.00,0.00,NULL,'2026-08-04 23:32:25','2026-08-04 23:32:25');
/*!40000 ALTER TABLE `items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2024_01_01_000010_create_businesses_table',1),(5,'2024_01_01_000011_create_clients_table',1),(6,'2024_01_01_000012_create_items_table',1),(7,'2024_01_01_000013_create_invoices_table',1),(8,'2024_01_01_000014_create_invoice_items_table',1),(9,'2026_07_23_041658_create_personal_access_tokens_table',1),(10,'2026_07_24_000001_add_phone_to_users_table',2),(11,'2026_07_24_000002_create_otp_tokens_table',2),(12,'2026_07_27_070633_add_is_admin_to_users_table',3),(13,'2026_07_27_071616_create_notifications_table',4),(14,'2026-08-05_000001_add_is_blocked_to_users_table',5),(15,'2026-08-05_000002_create_chat_messages_table',5),(16,'2026_08_06_000001_create_app_settings_table',6);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_foreign` (`user_id`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,NULL,'Informasi Pemeliharaan Sistem','Sistem Invoice App akan diperbarui nanti malam jam 23.00 WIB untuk peningkatan kestabilan.',0,'2026-07-27 00:19:43','2026-07-27 00:19:43'),(4,NULL,'assalaamualaium','p apa',0,'2026-08-04 22:58:47','2026-08-04 22:58:47'),(5,NULL,'tes','tes',0,'2026-08-05 20:10:52','2026-08-05 20:10:52');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `otp_tokens`
--

DROP TABLE IF EXISTS `otp_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `otp_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `phone` varchar(255) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `type` varchar(255) NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `otp_tokens_phone_type_index` (`phone`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `otp_tokens`
--

LOCK TABLES `otp_tokens` WRITE;
/*!40000 ALTER TABLE `otp_tokens` DISABLE KEYS */;
INSERT INTO `otp_tokens` VALUES (1,'6283181411470','418162','register',1,'2026-07-24 04:37:56','2026-07-23 21:37:30','2026-07-23 21:37:56'),(2,'6285175466143','183640','register',1,'2026-07-24 06:40:02','2026-07-23 23:39:33','2026-07-23 23:40:02'),(3,'6283824532713','901200','forgot_password',1,'2026-07-27 06:39:50','2026-07-26 23:37:35','2026-07-26 23:39:50'),(4,'6283824532713','505765','forgot_password',1,'2026-07-27 06:40:13','2026-07-26 23:39:50','2026-07-26 23:40:13'),(5,'6283824532713','668411','register',1,'2026-07-27 06:43:07','2026-07-26 23:42:49','2026-07-26 23:43:07'),(6,'6283824532713','489749','forgot_password',1,'2026-07-27 06:53:48','2026-07-26 23:53:23','2026-07-26 23:53:48'),(7,'6283824532713','337213','register',1,'2026-07-27 07:00:30','2026-07-27 00:00:09','2026-07-27 00:00:30');
/*!40000 ALTER TABLE `otp_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (4,'App\\Models\\User',2,'auth_token','8644a037f61c6d1a165221d6dbd67aa1d168728b0903399f39931569a6fdea7f','[\"*\"]',NULL,NULL,'2026-07-22 23:48:08','2026-07-22 23:48:08'),(5,'App\\Models\\User',3,'auth_token','c6c0db22f52b070e3b6f4ec23029e43fa2e02e814c676635c87026e6c7551dd0','[\"*\"]',NULL,NULL,'2026-07-22 23:57:26','2026-07-22 23:57:26'),(22,'App\\Models\\User',1,'auth_token','5231b982a61377e97a51c51a47b8eac645d480876340d6202d308c1bfabc06a3','[\"*\"]','2026-07-23 19:05:34',NULL,'2026-07-23 19:05:01','2026-07-23 19:05:34'),(25,'App\\Models\\User',5,'auth_token','885d74784e179fceb3539ceb336d40ca514bdfd86245136ed52bbf70a9851ba5','[\"*\"]','2026-07-23 19:37:42',NULL,'2026-07-23 19:37:39','2026-07-23 19:37:42'),(59,'App\\Models\\User',8,'auth_token','f0d18b19b2178ab883f5157bb663681f1247a6c62416419def70e817985c668f','[\"*\"]','2026-07-27 00:47:00',NULL,'2026-07-27 00:07:57','2026-07-27 00:47:00'),(62,'App\\Models\\User',8,'test','b34d302c2edb96f26d2b710940f2ea848642ea591068aeb99abfc346a8717e3a','[\"*\"]',NULL,NULL,'2026-07-27 00:23:39','2026-07-27 00:23:39'),(78,'App\\Models\\User',8,'test','a542de417250c4ea45068a3061485bfd72875fda4b76a71afbdf6966e511c3c2','[\"*\"]','2026-08-04 23:43:59',NULL,'2026-08-04 23:35:14','2026-08-04 23:43:59'),(88,'App\\Models\\User',12,'auth_token','8f984e5260c3b8fea6d2f4cb26eb0e5be0966c85cfb0d9f8d1613b168485e86e','[\"*\"]','2026-08-07 23:04:43',NULL,'2026-08-05 20:26:24','2026-08-07 23:04:43'),(94,'App\\Models\\User',11,'auth_token','df4dfb8562502012b5b4152bc051ede1cdd7b56dfce2f8ae33685b96beccee54','[\"*\"]','2026-08-07 23:12:43',NULL,'2026-08-07 19:01:02','2026-08-07 23:12:43');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `is_blocked` tinyint(1) NOT NULL DEFAULT 0,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_phone_unique` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (8,'dika','6283181411470',0,0,'2026-07-23 21:37:56','6283181411470@phone.local',NULL,'$2y$12$E8k897RG4meeUpLuup/FnOR1EJiXDLtJUTgrck.WluqbWkM7c/fsW',NULL,'2026-07-23 21:37:57','2026-07-23 21:37:57'),(9,'julian','6285175466143',0,0,'2026-07-23 23:40:02','6285175466143@phone.local',NULL,'$2y$12$WDnsSaNDXyl6USkR3nXahebWmAmufT8Wk2NrhCJ8IGoR6bMU/8fI2',NULL,'2026-07-23 23:40:02','2026-07-27 00:17:53'),(11,'dikajulian','6283824532713',0,0,'2026-07-27 00:00:30','6283824532713@phone.local',NULL,'$2y$12$Qhr6OKJtJdLe1JGntwIhXeaT9HbgRLiAzRYLiFcLwyJaaXcj.US2a',NULL,'2026-07-27 00:00:30','2026-08-05 20:35:13'),(12,'Super Admin','081234567890',1,0,NULL,'admin@myinvoice.com',NULL,'$2y$12$o96me.XA0QOZhIc6.ROIbuJoeln6DR4YGTU8Vvp6GoY4me3GhW03q',NULL,'2026-07-28 22:31:22','2026-08-04 22:54:20');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-08 13:15:36
