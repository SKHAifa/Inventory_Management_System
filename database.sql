-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: SupplySync
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
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `email` varchar(190) NOT NULL,
  `reset_code` varchar(100) NOT NULL,
  `expires` datetime NOT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(150) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `supplier_id` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'Chicken Breast','SKU-001',25,1,320.00,'chicken breast.png'),(2,'Basmati Rice','SKU-002',60,2,85.00,'rice.png'),(3,'Fresh Beef','SKU-003',18,3,560.00,'chicken breast.png'),(4,'Prawns','SKU-004',22,4,480.00,'chicken breast.png'),(5,'Wheat Flour','SKU-005',45,5,52.00,'aata.png'),(6,'Fresh Milk','SKU-006',30,6,65.00,'milk.png'),(7,'Masoor Dal','SKU-007',40,7,110.00,'masoor dal.png'),(8,'Kitchen Cleaner','SKU-008',15,8,180.00,'oil.png'),(9,'Bread Flour','SKU-009',35,9,70.00,'aata.png'),(10,'Farm Eggs','SKU-010',50,10,9.00,'milk.png'),(11,'Watermelon','SKU-011',12,11,55.00,'watermelon.png'),(12,'Toor Dal','SKU-012',42,12,125.00,'toor dal.png'),(13,'Mineral Water','SKU-013',80,13,25.00,'milk.png'),(14,'Fresh Herbs','SKU-014',20,14,45.00,'okra.png'),(15,'Frozen Potatoes','SKU-015',28,15,150.00,'potato.png'),(16,'Food Storage Oil','SKU-016',34,16,190.00,'oil.png'),(17,'Premium Atta','SKU-017',38,17,78.00,'aata.png'),(18,'Frozen Vegetables','SKU-018',16,18,210.00,'okra.png'),(19,'Local Tomatoes','SKU-019',25,19,60.00,'tomato.png'),(20,'Onions','SKU-020',32,20,48.00,'onion.png'),(21,'Chicken Breasts','SKU-021',10,15,0.00,'chicken breast.png');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_requests`
--

DROP TABLE IF EXISTS `stock_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(150) NOT NULL,
  `quantity_needed` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `staff_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_requests`
--

LOCK TABLES `stock_requests` WRITE;
/*!40000 ALTER TABLE `stock_requests` DISABLE KEYS */;
INSERT INTO `stock_requests` VALUES (1,'Chicken',10,'For Biryani',2,'rejected','2026-09-07 08:22:35'),(2,'Chicken Breasts',10,'For Biryani',2,'approved','2026-09-07 08:48:32');
/*!40000 ALTER TABLE `stock_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_name` varchar(150) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,'Fresh Harvest Foods','+91 9474723616','orders@freshharvest.example','12 Market Street'),(2,'Green Valley Produce','+91 9755486026','sales@greenvalley.example','48 Garden Avenue'),(3,'Prime Meat Distributors','+91 9390535435','orders@primemeat.example','7 Butcher Road'),(4,'Ocean Catch Seafood','+91 9266661967','hello@oceancatch.example','21 Harbor Lane'),(5,'Golden Grain Mills','+91 9427820728','sales@goldengrain.example','90 Mill Road'),(6,'Dairy Best Supplies','+91 9582451528','orders@dairybest.example','15 Creamery Way'),(7,'Spice Route Traders','+91 9102611827','info@spiceroute.example','33 Bazaar Street'),(8,'Clean Kitchen Essentials','+91 9273587503','sales@cleankitchen.example','6 Service Road'),(9,'Bakers Choice Wholesale','+91 9442020171','orders@bakerschoice.example','28 Bakery Lane'),(10,'Farmhouse Eggs Co','+91 9474051182','contact@farmhouseeggs.example','4 Country Road'),(11,'Tropical Fruit Market','+91 9838121857','sales@tropicalfruit.example','77 Orchard Drive'),(12,'Harvest Rice and Pulses','+91 9983421662','orders@harvestrice.example','19 Warehouse Street'),(13,'Reliable Beverage Supply','+91 9681450675','orders@reliablebeverage.example','52 Bottling Avenue'),(14,'Herb Garden Organics','+91 9534113214','hello@herbgarden.example','8 Herb Lane'),(15,'Cold Storage Logistics','+91 9465187430','dispatch@coldstorage.example','41 Depot Road'),(16,'Kitchen Packaging Hub','+91 9389542812','sales@packaginghub.example','63 Industrial Park'),(17,'Premium Flour Company','+91 9583100365','orders@premiumflour.example','11 Silo Street'),(18,'Sunrise Frozen Foods','+91 9924820106','sales@sunrisefrozen.example','25 Freezer Way'),(19,'Local Roots Vegetables','+91 9526469512','orders@localroots.example','3 Farm Track'),(20,'Hotel Pantry Partners','+91 9262359595','contact@hotelpantry.example','100 Supply Boulevard');
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') NOT NULL DEFAULT 'staff',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Aifa Shaikh','AifaShaikh@hotel.com','$2y$10$4JlbCZG5CyIjnfa14Qii.O6jVL2CE3dYlt5k3tj8iHTJOfVa27mcW','admin'),(2,'Jamie','Jamie@hotel.com','$2y$10$.vjDmf7ziScF/00rRs2.7e9LYk27muEr1IkZGyrCgNxl.vYSGwVPu','staff');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'SupplySync'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-07 14:34:07
