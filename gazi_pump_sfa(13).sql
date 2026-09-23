-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 22, 2026 at 06:33 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gazi_pump_sfa`
--

-- --------------------------------------------------------

--
-- Table structure for table `achievements`
--

CREATE TABLE `achievements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `target_id` bigint(20) UNSIGNED NOT NULL,
  `order_achieved` decimal(12,2) NOT NULL,
  `collection_achieved` decimal(12,2) NOT NULL,
  `quantity_achieved` int(10) UNSIGNED NOT NULL,
  `order_pct` decimal(10,2) NOT NULL,
  `collection_pct` decimal(10,2) NOT NULL,
  `quantity_pct` decimal(10,2) NOT NULL,
  `overall_pct` decimal(10,2) NOT NULL,
  `grade` varchar(255) NOT NULL,
  `calculated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `achievements`
--

INSERT INTO `achievements` (`id`, `target_id`, `order_achieved`, `collection_achieved`, `quantity_achieved`, `order_pct`, `collection_pct`, `quantity_pct`, `overall_pct`, `grade`, `calculated_at`, `created_at`, `updated_at`) VALUES
(1, 1, 18000.00, 9200.00, 40, 0.63, 0.50, 60.61, 20.58, 'F', '2026-08-31 00:54:16', '2026-08-23 05:36:10', '2026-08-31 00:54:16'),
(2, 2, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 'F', '2026-08-23 05:36:10', '2026-08-23 05:36:10', '2026-08-23 05:36:10'),
(3, 3, 3600.00, 0.00, 18, 40.00, 0.00, 51.43, 30.48, 'F', '2026-08-27 03:31:05', '2026-08-23 05:36:10', '2026-08-27 03:31:05'),
(4, 4, 536497.36, 312474.72, 65, 140.85, 140.85, 141.30, 141.00, 'A', '2026-08-23 05:36:10', '2026-08-23 05:36:10', '2026-08-23 05:36:10'),
(5, 5, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 'F', '2026-08-23 05:36:10', '2026-08-23 05:36:10', '2026-08-23 05:36:10');

-- --------------------------------------------------------

--
-- Table structure for table `achievement_entries`
--

CREATE TABLE `achievement_entries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `entry_date` date NOT NULL,
  `order_value_achieved` decimal(12,2) NOT NULL,
  `collection_achieved` decimal(12,2) NOT NULL,
  `quantity_achieved` int(10) UNSIGNED NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `achievement_entries`
--

INSERT INTO `achievement_entries` (`id`, `user_id`, `entry_date`, `order_value_achieved`, `collection_achieved`, `quantity_achieved`, `status`, `approved_by`, `approved_at`, `notes`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(2, 80, '2026-08-31', 15000.00, 8000.00, 25, 'approved', 1, '2026-08-31 00:20:00', NULL, NULL, NULL, NULL, NULL, '2026-08-31 00:19:32', '2026-08-31 00:20:51'),
(3, 80, '2026-08-30', 3000.00, 1200.00, 15, 'approved', 1, '2026-08-31 00:23:17', NULL, 1, 1, NULL, NULL, '2026-08-31 00:22:38', '2026-08-31 00:23:17'),
(4, 80, '2026-08-29', 6000.00, 2500.00, 10, 'pending', NULL, NULL, NULL, 80, NULL, NULL, NULL, '2026-08-31 00:54:16', '2026-08-31 00:54:16');

-- --------------------------------------------------------

--
-- Table structure for table `achievement_items`
--

CREATE TABLE `achievement_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `achievement_entry_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `order_achieved` decimal(12,2) NOT NULL DEFAULT 0.00,
  `collection_achieved` decimal(12,2) NOT NULL DEFAULT 0.00,
  `quantity_achieved` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `achievement_items`
--

INSERT INTO `achievement_items` (`id`, `achievement_entry_id`, `product_id`, `order_achieved`, `collection_achieved`, `quantity_achieved`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 3000.00, 1200.00, 15, '2026-08-31 00:22:38', '2026-08-31 00:22:38');

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `log_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `causer_type` varchar(255) DEFAULT NULL,
  `causer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `batch_uuid` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES
(1, 'product_categories', 'created', 'App\\Models\\ProductCategory', 'created', 1, NULL, NULL, '{\"attributes\":{\"name\":\"Industrial Pumps\",\"code\":\"CAT-001\",\"description\":\"Self-priming, centrifugal, and submersible pumps from the Gazi, Pentax, Eifel, and CNP lines (gazipumps.com).\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(2, 'products', 'created', 'App\\Models\\Product', 'created', 1, NULL, NULL, '{\"attributes\":{\"category_id\":1,\"name\":\"Gazi Self-Priming Jet Pump\",\"sku\":\"SKU-00001\",\"price\":\"6500.00\",\"description\":\"Gazi self-priming jet pump for household and light commercial water supply.\",\"image\":\"products\\/gazi-self-priming-jet-pump.jpg\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(3, 'products', 'created', 'App\\Models\\Product', 'created', 2, NULL, NULL, '{\"attributes\":{\"category_id\":1,\"name\":\"Gazi Standardized Centrifugal Pump\",\"sku\":\"SKU-00002\",\"price\":\"8200.00\",\"description\":\"Gazi standardized centrifugal pump for general-purpose water transfer.\",\"image\":\"products\\/gazi-standardized-centrifugal-pump.jpg\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(4, 'products', 'created', 'App\\Models\\Product', 'created', 3, NULL, NULL, '{\"attributes\":{\"category_id\":1,\"name\":\"Pentax Centrifugal Pump\",\"sku\":\"SKU-00003\",\"price\":\"7800.00\",\"description\":\"Pentax (Pentex) centrifugal pump, distributed by Gazi Pumps & Motors.\",\"image\":\"products\\/pentax-centrifugal-pump.jpg\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(5, 'products', 'created', 'App\\Models\\Product', 'created', 4, NULL, NULL, '{\"attributes\":{\"category_id\":1,\"name\":\"Pentax Submersible Pump\",\"sku\":\"SKU-00004\",\"price\":\"9500.00\",\"description\":\"Pentax (Pentex) submersible pump for deep-well water extraction.\",\"image\":\"products\\/pentax-submersible-pump.jpg\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(6, 'products', 'created', 'App\\Models\\Product', 'created', 5, NULL, NULL, '{\"attributes\":{\"category_id\":1,\"name\":\"Eifel EA Series Pump\",\"sku\":\"SKU-00005\",\"price\":\"8800.00\",\"description\":\"Eifel EA Series pump, distributed by Gazi Pumps & Motors.\",\"image\":\"products\\/eifel-ea-series-pump.jpg\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(7, 'products', 'created', 'App\\Models\\Product', 'created', 6, NULL, NULL, '{\"attributes\":{\"category_id\":1,\"name\":\"Eifel EAD Series Pump\",\"sku\":\"SKU-00006\",\"price\":\"9200.00\",\"description\":\"Eifel EAD Series pump, distributed by Gazi Pumps & Motors.\",\"image\":\"products\\/eifel-ead-series-pump.jpg\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(8, 'products', 'created', 'App\\Models\\Product', 'created', 7, NULL, NULL, '{\"attributes\":{\"category_id\":1,\"name\":\"CNP CDLF Series Pump\",\"sku\":\"SKU-00007\",\"price\":\"15500.00\",\"description\":\"CNP CDLF Series vertical multistage pump, distributed by Gazi Pumps & Motors.\",\"image\":\"products\\/cnp-cdlf-series-pump.jpg\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(9, 'products', 'created', 'App\\Models\\Product', 'created', 8, NULL, NULL, '{\"attributes\":{\"category_id\":1,\"name\":\"CNP SZ Series Pump\",\"sku\":\"SKU-00008\",\"price\":\"14200.00\",\"description\":\"CNP SZ Series pump, distributed by Gazi Pumps & Motors.\",\"image\":\"products\\/cnp-sz-series-pump.jpg\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(10, 'product_categories', 'created', 'App\\Models\\ProductCategory', 'created', 2, NULL, NULL, '{\"attributes\":{\"name\":\"Industrial Motors & Equipment\",\"code\":\"CAT-002\",\"description\":\"Fire-fighting pump sets, motors, gas stoves, and tubewells from the Gazi Pumps & Motors industrial line (gazipumps.com).\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(11, 'products', 'created', 'App\\Models\\Product', 'created', 9, NULL, NULL, '{\"attributes\":{\"category_id\":2,\"name\":\"Gazi Fire Fighting Pump Complete Set\",\"sku\":\"SKU-00009\",\"price\":\"185000.00\",\"description\":\"Gazi fire fighting pump complete set for industrial fire-safety installations.\",\"image\":\"products\\/gazi-fire-fighting-pump-set.jpg\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(12, 'products', 'created', 'App\\Models\\Product', 'created', 10, NULL, NULL, '{\"attributes\":{\"category_id\":2,\"name\":\"Gazi Motors YC Series\",\"sku\":\"SKU-00010\",\"price\":\"8400.00\",\"description\":\"Gazi Motors YC Series electric motor for industrial and pump applications.\",\"image\":\"products\\/gazi-motors-yc-series.jpg\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(13, 'products', 'created', 'App\\Models\\Product', 'created', 11, NULL, NULL, '{\"attributes\":{\"category_id\":2,\"name\":\"Gazi Gas Stove (Industrial Line)\",\"sku\":\"SKU-00011\",\"price\":\"3200.00\",\"description\":\"Gazi gas stove from the Gazi Pumps & Motors industrial product line.\",\"image\":\"products\\/gazi-gas-stove-industrial.jpg\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(14, 'products', 'created', 'App\\Models\\Product', 'created', 12, NULL, NULL, '{\"attributes\":{\"category_id\":2,\"name\":\"Gazi Tubewell\",\"sku\":\"SKU-00012\",\"price\":\"4500.00\",\"description\":\"Gazi tubewell equipment for groundwater extraction.\",\"image\":\"products\\/gazi-tubewell.jpg\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(15, 'product_categories', 'created', 'App\\Models\\ProductCategory', 'created', 3, NULL, NULL, '{\"attributes\":{\"name\":\"Gazi Smiss Gas Stoves\",\"code\":\"CAT-003\",\"description\":\"Gazi Smiss and Gazi branded gas stoves from the gcart.com.bd storefront.\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(16, 'products', 'created', 'App\\Models\\Product', 'created', 13, NULL, NULL, '{\"attributes\":{\"category_id\":3,\"name\":\"TG-206 - Gazi Smiss Gas Stove\",\"sku\":\"SKU-00013\",\"price\":\"8466.00\",\"description\":\"Gazi Smiss TG-206 double burner gas stove.\",\"image\":\"products\\/gazi-smiss-gas-stove-tg-206.png\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(17, 'products', 'created', 'App\\Models\\Product', 'created', 14, NULL, NULL, '{\"attributes\":{\"category_id\":3,\"name\":\"GST-102C - Gazi Gas Stove\",\"sku\":\"SKU-00014\",\"price\":\"1864.00\",\"description\":\"Gazi GST-102C single burner gas stove.\",\"image\":\"products\\/gazi-gas-stove-gst-102c.png\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(18, 'products', 'created', 'App\\Models\\Product', 'created', 15, NULL, NULL, '{\"attributes\":{\"category_id\":3,\"name\":\"EG-732S - Gazi Smiss Gas Stove\",\"sku\":\"SKU-00015\",\"price\":\"14448.00\",\"description\":\"Gazi Smiss EG-732S glass-top gas stove.\",\"image\":\"products\\/gazi-smiss-gas-stove-eg-732s.webp\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(19, 'products', 'created', 'App\\Models\\Product', 'created', 16, NULL, NULL, '{\"attributes\":{\"category_id\":3,\"name\":\"TG-213S - Gazi Smiss Gas Stove\",\"sku\":\"SKU-00016\",\"price\":\"11220.00\",\"description\":\"Gazi Smiss TG-213S double burner gas stove.\",\"image\":\"products\\/gazi-smiss-gas-stove-tg-213s.png\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(20, 'products', 'created', 'App\\Models\\Product', 'created', 17, NULL, NULL, '{\"attributes\":{\"category_id\":3,\"name\":\"GH-8204M - Gazi Smiss Gas Stove\",\"sku\":\"SKU-00017\",\"price\":\"16128.00\",\"description\":\"Gazi Smiss GH-8204M gas stove.\",\"image\":\"products\\/gazi-smiss-gas-stove-gh-8204m.webp\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(21, 'product_categories', 'created', 'App\\Models\\ProductCategory', 'created', 4, NULL, NULL, '{\"attributes\":{\"name\":\"Gazi Smiss Kitchen Hoods\",\"code\":\"CAT-004\",\"description\":\"Gazi Smiss kitchen hood\\/chimney models from the gcart.com.bd storefront.\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(22, 'products', 'created', 'App\\Models\\Product', 'created', 18, NULL, NULL, '{\"attributes\":{\"category_id\":4,\"name\":\"HY-716BV - Gazi Smiss Kitchen Hood\",\"sku\":\"SKU-00018\",\"price\":\"22176.00\",\"description\":\"Gazi Smiss HY-716BV kitchen hood.\",\"image\":\"products\\/gazi-smiss-kitchen-hood-hy-716bv.png\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(23, 'products', 'created', 'App\\Models\\Product', 'created', 19, NULL, NULL, '{\"attributes\":{\"category_id\":4,\"name\":\"HY-712BT - Gazi Smiss Kitchen Hood\",\"sku\":\"SKU-00019\",\"price\":\"14616.00\",\"description\":\"Gazi Smiss HY-712BT kitchen hood.\",\"image\":\"products\\/gazi-smiss-kitchen-hood-hy-712bt.webp\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(24, 'products', 'created', 'App\\Models\\Product', 'created', 20, NULL, NULL, '{\"attributes\":{\"category_id\":4,\"name\":\"EG-750S - Gazi Smiss Kitchen Hood\",\"sku\":\"SKU-00020\",\"price\":\"8856.00\",\"description\":\"Gazi Smiss EG-750S kitchen hood.\",\"image\":\"products\\/gazi-smiss-kitchen-hood-eg-750s.png\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(25, 'products', 'created', 'App\\Models\\Product', 'created', 21, NULL, NULL, '{\"attributes\":{\"category_id\":4,\"name\":\"HY-736BV - Gazi Smiss Kitchen Hood\",\"sku\":\"SKU-00021\",\"price\":\"23184.00\",\"description\":\"Gazi Smiss HY-736BV kitchen hood.\",\"image\":\"products\\/gazi-smiss-kitchen-hood-hy-736bv.webp\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(26, 'products', 'created', 'App\\Models\\Product', 'created', 22, NULL, NULL, '{\"attributes\":{\"category_id\":4,\"name\":\"HY-729CP - Gazi Smiss Kitchen Hood\",\"sku\":\"SKU-00022\",\"price\":\"16320.00\",\"description\":\"Gazi Smiss HY-729CP kitchen hood.\",\"image\":\"products\\/gazi-smiss-kitchen-hood-hy-729cp.webp\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(27, 'product_categories', 'created', 'App\\Models\\ProductCategory', 'created', 5, NULL, NULL, '{\"attributes\":{\"name\":\"Gazi Smiss Induction & Infrared Cookers\",\"code\":\"CAT-005\",\"description\":\"Gazi Smiss induction and infrared cooktops from the gcart.com.bd storefront.\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(28, 'products', 'created', 'App\\Models\\Product', 'created', 23, NULL, NULL, '{\"attributes\":{\"category_id\":5,\"name\":\"IF-HL01 - Gazi Smiss Infrared Cooker\",\"sku\":\"SKU-00023\",\"price\":\"5952.00\",\"description\":\"Gazi Smiss IF-HL01 infrared cooker.\",\"image\":\"products\\/gazi-smiss-infrared-cooker-if-hl01.webp\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(29, 'products', 'created', 'App\\Models\\Product', 'created', 24, NULL, NULL, '{\"attributes\":{\"category_id\":5,\"name\":\"A-40G - Gazi Smiss Infrared Cooker\",\"sku\":\"SKU-00024\",\"price\":\"4080.00\",\"description\":\"Gazi Smiss A-40G infrared cooker.\",\"image\":\"products\\/gazi-smiss-infrared-cooker-a-40g.webp\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(30, 'products', 'created', 'App\\Models\\Product', 'created', 25, NULL, NULL, '{\"attributes\":{\"category_id\":5,\"name\":\"A-25S - Gazi Smiss Induction Cooker\",\"sku\":\"SKU-00025\",\"price\":\"4080.00\",\"description\":\"Gazi Smiss A-25S induction cooker.\",\"image\":\"products\\/gazi-smiss-induction-cooker-a-25s.webp\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(31, 'products', 'created', 'App\\Models\\Product', 'created', 26, NULL, NULL, '{\"attributes\":{\"category_id\":5,\"name\":\"A-37G - Gazi Smiss Infrared Cooker\",\"sku\":\"SKU-00026\",\"price\":\"4080.00\",\"description\":\"Gazi Smiss A-37G infrared cooker.\",\"image\":\"products\\/gazi-smiss-infrared-cooker-a-37g.png\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(32, 'products', 'created', 'App\\Models\\Product', 'created', 27, NULL, NULL, '{\"attributes\":{\"category_id\":5,\"name\":\"E-720B - Gazi Smiss Induction & Infrared Cooker\",\"sku\":\"SKU-00027\",\"price\":\"12960.00\",\"description\":\"Gazi Smiss E-720B combined induction and infrared cooker.\",\"image\":\"products\\/gazi-smiss-induction-infrared-cooker-e-720b.png\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(33, 'products', 'created', 'App\\Models\\Product', 'created', 28, NULL, NULL, '{\"attributes\":{\"category_id\":5,\"name\":\"A-01 - Gazi Smiss Infrared Cooker\",\"sku\":\"SKU-00028\",\"price\":\"3672.00\",\"description\":\"Gazi Smiss A-01 infrared cooker.\",\"image\":\"products\\/gazi-smiss-infrared-cooker-a-01.png\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(34, 'product_categories', 'created', 'App\\Models\\ProductCategory', 'created', 6, NULL, NULL, '{\"attributes\":{\"name\":\"Gazi Smiss Electric Ovens\",\"code\":\"CAT-006\",\"description\":\"Gazi Smiss electric oven models from the gcart.com.bd storefront.\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(35, 'products', 'created', 'App\\Models\\Product', 'created', 29, NULL, NULL, '{\"attributes\":{\"category_id\":6,\"name\":\"GEO-03 - Gazi Smiss Electric Oven 30 Liter\",\"sku\":\"SKU-00029\",\"price\":\"15600.00\",\"description\":\"Gazi Smiss GEO-03 electric oven, 30 liter capacity.\",\"image\":\"products\\/gazi-smiss-electric-oven-30l-geo-03.png\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(36, 'products', 'created', 'App\\Models\\Product', 'created', 30, NULL, NULL, '{\"attributes\":{\"category_id\":6,\"name\":\"GEO-04 - Gazi Smiss Electric Oven 40 Liter\",\"sku\":\"SKU-00030\",\"price\":\"16800.00\",\"description\":\"Gazi Smiss GEO-04 electric oven, 40 liter capacity.\",\"image\":\"products\\/gazi-smiss-electric-oven-40l-geo-04.png\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(37, 'products', 'created', 'App\\Models\\Product', 'created', 31, NULL, NULL, '{\"attributes\":{\"category_id\":6,\"name\":\"GEO-05 - Gazi Smiss Electric Oven 50 Liter\",\"sku\":\"SKU-00031\",\"price\":\"19200.00\",\"description\":\"Gazi Smiss GEO-05 electric oven, 50 liter capacity.\",\"image\":\"products\\/gazi-smiss-electric-oven-50l-geo-05.png\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(38, 'product_categories', 'created', 'App\\Models\\ProductCategory', 'created', 7, NULL, NULL, '{\"attributes\":{\"name\":\"Gazi Smiss Air Fryers\",\"code\":\"CAT-007\",\"description\":\"Gazi Smiss air fryer models from the gcart.com.bd storefront.\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(39, 'products', 'created', 'App\\Models\\Product', 'created', 32, NULL, NULL, '{\"attributes\":{\"category_id\":7,\"name\":\"GA-AF-23 - Gazi Smiss Air Fryer\",\"sku\":\"SKU-00032\",\"price\":\"11088.00\",\"description\":\"Gazi Smiss GA-AF-23 air fryer.\",\"image\":\"products\\/gazi-smiss-air-fryer-ga-af-23.png\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(40, 'products', 'created', 'App\\Models\\Product', 'created', 33, NULL, NULL, '{\"attributes\":{\"category_id\":7,\"name\":\"GA-AF-25 - Gazi Smiss Air Fryer\",\"sku\":\"SKU-00033\",\"price\":\"10836.00\",\"description\":\"Gazi Smiss GA-AF-25 air fryer.\",\"image\":\"products\\/gazi-smiss-air-fryer-ga-af-25.png\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(41, 'products', 'created', 'App\\Models\\Product', 'created', 34, NULL, NULL, '{\"attributes\":{\"category_id\":7,\"name\":\"GA-AF-27 - Gazi Smiss Air Fryer\",\"sku\":\"SKU-00034\",\"price\":\"8976.00\",\"description\":\"Gazi Smiss GA-AF-27 air fryer.\",\"image\":\"products\\/gazi-smiss-air-fryer-ga-af-27.png\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(42, 'product_categories', 'created', 'App\\Models\\ProductCategory', 'created', 8, NULL, NULL, '{\"attributes\":{\"name\":\"Gazi Smiss Motors\",\"code\":\"CAT-008\",\"description\":\"Gazi Smiss Y2\\/YC series electric motors from the gcart.com.bd storefront.\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(43, 'products', 'created', 'App\\Models\\Product', 'created', 35, NULL, NULL, '{\"attributes\":{\"category_id\":8,\"name\":\"15.0 HP Y2 Motor 950 RPM\",\"sku\":\"SKU-00035\",\"price\":\"87675.00\",\"description\":\"Gazi Smiss Y2 series electric motor, 15.0 HP, 950 RPM.\",\"image\":\"products\\/gazi-smiss-motor-15hp-y2.png\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(44, 'products', 'created', 'App\\Models\\Product', 'created', 36, NULL, NULL, '{\"attributes\":{\"category_id\":8,\"name\":\"10.0 HP Y2 Motor 2800 RPM\",\"sku\":\"SKU-00036\",\"price\":\"34650.00\",\"description\":\"Gazi Smiss Y2 series electric motor, 10.0 HP, 2800 RPM.\",\"image\":\"products\\/gazi-smiss-motor-10hp-y2.png\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(45, 'products', 'created', 'App\\Models\\Product', 'created', 37, NULL, NULL, '{\"attributes\":{\"category_id\":8,\"name\":\"5.5 HP Y2 Motor 2800 RPM\",\"sku\":\"SKU-00037\",\"price\":\"24700.00\",\"description\":\"Gazi Smiss Y2 series electric motor, 5.5 HP, 2800 RPM.\",\"image\":\"products\\/gazi-smiss-motor-5-5hp-y2.png\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(46, 'products', 'created', 'App\\Models\\Product', 'created', 38, NULL, NULL, '{\"attributes\":{\"category_id\":8,\"name\":\"3 HP YC Motor 1450 RPM\",\"sku\":\"SKU-00038\",\"price\":\"21000.00\",\"description\":\"Gazi Smiss YC series electric motor, 3 HP, 1450 RPM.\",\"image\":\"products\\/gazi-smiss-motor-3hp-yc.png\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(47, 'products', 'created', 'App\\Models\\Product', 'created', 39, NULL, NULL, '{\"attributes\":{\"category_id\":8,\"name\":\"1.0 HP Y2 Motor 2800 RPM\",\"sku\":\"SKU-00039\",\"price\":\"9450.00\",\"description\":\"Gazi Smiss Y2 series electric motor, 1.0 HP, 2800 RPM.\",\"image\":\"products\\/gazi-smiss-motor-1hp-y2.png\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(48, 'products', 'created', 'App\\Models\\Product', 'created', 40, NULL, NULL, '{\"attributes\":{\"category_id\":8,\"name\":\"0.75 HP YC Motor 1450 RPM (Classic)\",\"sku\":\"SKU-00040\",\"price\":\"8400.00\",\"description\":\"Gazi Smiss YC series electric motor (Classic), 0.75 HP, 1450 RPM.\",\"image\":\"products\\/gazi-smiss-motor-0-75hp-yc-classic.png\",\"status\":true}}', NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(49, 'settings', 'updated', 'App\\Models\\Setting', 'updated', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"company_address\":\"37\\/2, Pritom Zaman Tower, Purana Paltan, Dhaka-1000\",\"company_phone\":\"01958538607\",\"company_email\":\"info@gcart.com.bd\"},\"old\":{\"company_address\":null,\"company_phone\":null,\"company_email\":null}}', NULL, '2026-08-23 04:55:46', '2026-08-23 04:55:46'),
(50, 'territories', 'created', 'App\\Models\\Territory', 'created', 1, NULL, NULL, '{\"attributes\":{\"division_id\":6,\"district_id\":47,\"thana_id\":365,\"name\":\"Dhaka - Savar\",\"code\":\"TER-DHK-001\",\"manager_id\":null,\"center_lat\":null,\"center_lng\":null,\"boundary\":null,\"status\":true}}', NULL, '2026-08-23 05:22:02', '2026-08-23 05:22:02'),
(51, 'territories', 'created', 'App\\Models\\Territory', 'created', 2, NULL, NULL, '{\"attributes\":{\"division_id\":6,\"district_id\":47,\"thana_id\":367,\"name\":\"Dhaka - Keraniganj\",\"code\":\"TER-DHK-002\",\"manager_id\":null,\"center_lat\":null,\"center_lng\":null,\"boundary\":null,\"status\":true}}', NULL, '2026-08-23 05:22:02', '2026-08-23 05:22:02'),
(52, 'territories', 'created', 'App\\Models\\Territory', 'created', 3, NULL, NULL, '{\"attributes\":{\"division_id\":6,\"district_id\":47,\"thana_id\":366,\"name\":\"Dhaka - Dhamrai\",\"code\":\"TER-DHK-003\",\"manager_id\":null,\"center_lat\":null,\"center_lng\":null,\"boundary\":null,\"status\":true}}', NULL, '2026-08-23 05:22:02', '2026-08-23 05:22:02'),
(53, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 1, NULL, NULL, '{\"attributes\":{\"dealer_code\":\"DLR-0001\",\"name\":\"Savar Pump House\",\"type\":\"dealer\",\"phone\":\"01821404477\",\"email\":null,\"address\":\"Savar Bazar Road, Savar, Dhaka\",\"image\":\"dealers\\/gazi-showroom-1.jpg\",\"gps_lat\":null,\"gps_lng\":null,\"division_id\":6,\"district_id\":47,\"thana_id\":365,\"territory_id\":1,\"status\":true}}', NULL, '2026-08-23 05:22:02', '2026-08-23 05:22:02'),
(54, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 2, NULL, NULL, '{\"attributes\":{\"dealer_code\":\"DLR-0002\",\"name\":\"Keraniganj Hardware & Motors\",\"type\":\"retailer\",\"phone\":\"01953131136\",\"email\":null,\"address\":\"Aganagar, Keraniganj, Dhaka\",\"image\":\"dealers\\/gazi-showroom-2.jpg\",\"gps_lat\":null,\"gps_lng\":null,\"division_id\":6,\"district_id\":47,\"thana_id\":367,\"territory_id\":2,\"status\":true}}', NULL, '2026-08-23 05:22:02', '2026-08-23 05:22:02'),
(55, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 3, NULL, NULL, '{\"attributes\":{\"dealer_code\":\"DLR-0003\",\"name\":\"Dhamrai Water Solutions\",\"type\":\"dealer\",\"phone\":\"01997886631\",\"email\":null,\"address\":\"Dhamrai Bus Stand, Dhamrai, Dhaka\",\"image\":\"dealers\\/gazi-showroom-3.jpg\",\"gps_lat\":null,\"gps_lng\":null,\"division_id\":6,\"district_id\":47,\"thana_id\":366,\"territory_id\":3,\"status\":true}}', NULL, '2026-08-23 05:22:02', '2026-08-23 05:22:02'),
(56, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 4, NULL, NULL, '{\"attributes\":{\"dealer_code\":\"DLR-0004\",\"name\":\"Gazi Appliance Corner\",\"type\":\"retailer\",\"phone\":\"01707842562\",\"email\":null,\"address\":\"Savar New Market, Savar, Dhaka\",\"image\":\"dealers\\/gazi-showroom-1.jpg\",\"gps_lat\":null,\"gps_lng\":null,\"division_id\":6,\"district_id\":47,\"thana_id\":365,\"territory_id\":1,\"status\":true}}', NULL, '2026-08-23 05:22:02', '2026-08-23 05:22:02'),
(57, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 5, NULL, NULL, '{\"attributes\":{\"dealer_code\":\"DLR-0005\",\"name\":\"Buriganga Distribution House\",\"type\":\"distributor\",\"phone\":\"01852953026\",\"email\":null,\"address\":\"Zinzira, Keraniganj, Dhaka\",\"image\":\"dealers\\/gazi-showroom-2.jpg\",\"gps_lat\":null,\"gps_lng\":null,\"division_id\":6,\"district_id\":47,\"thana_id\":367,\"territory_id\":2,\"status\":true}}', NULL, '2026-08-23 05:22:02', '2026-08-23 05:22:02'),
(58, 'users', 'created', 'App\\Models\\User', 'created', 80, NULL, NULL, '{\"attributes\":{\"employee_id\":\"EMP-10001\",\"name\":\"Rafiqul Islam\",\"email\":\"rafiqul.islam@gazipump.com\",\"phone\":\"01711000001\",\"photo\":null,\"designation\":\"Sales Executive\",\"date_of_birth\":\"1983-01-12\",\"manager_id\":null,\"sales_team_id\":null,\"status\":true,\"password\":\"$2y$12$hnMF5XzSUmqBoT.X5CFLp.P8BwGlSz8tydRtJDmH6OUqXluxX6VWC\"}}', NULL, '2026-08-23 05:32:44', '2026-08-23 05:32:44'),
(59, 'users', 'created', 'App\\Models\\User', 'created', 81, NULL, NULL, '{\"attributes\":{\"employee_id\":\"EMP-10002\",\"name\":\"Shirin Akter\",\"email\":\"shirin.akter@gazipump.com\",\"phone\":\"01711000002\",\"photo\":null,\"designation\":\"Sales Executive\",\"date_of_birth\":\"2001-12-06\",\"manager_id\":null,\"sales_team_id\":null,\"status\":true,\"password\":\"$2y$12$hnMF5XzSUmqBoT.X5CFLp.P8BwGlSz8tydRtJDmH6OUqXluxX6VWC\"}}', NULL, '2026-08-23 05:32:44', '2026-08-23 05:32:44'),
(60, 'attendances', 'created', 'App\\Models\\Attendance', 'created', 1, NULL, NULL, '{\"attributes\":{\"user_id\":80,\"date\":\"2026-08-21\",\"check_in_at\":\"2026-08-21T09:00:00.000000Z\",\"check_in_lat\":\"23.7385030\",\"check_in_lng\":\"90.4641500\",\"check_in_photo\":null,\"check_out_at\":\"2026-08-21T17:00:00.000000Z\",\"check_out_lat\":\"23.7234210\",\"check_out_lng\":\"90.4784480\",\"check_out_photo\":null,\"status\":\"present\",\"late_minutes\":0,\"remarks\":null}}', NULL, '2026-08-23 05:34:23', '2026-08-23 05:34:23'),
(61, 'attendances', 'created', 'App\\Models\\Attendance', 'created', 2, NULL, NULL, '{\"attributes\":{\"user_id\":81,\"date\":\"2026-08-20\",\"check_in_at\":\"2026-08-20T09:30:00.000000Z\",\"check_in_lat\":\"23.7093470\",\"check_in_lng\":\"90.4177140\",\"check_in_photo\":null,\"check_out_at\":\"2026-08-20T17:30:00.000000Z\",\"check_out_lat\":\"23.8309230\",\"check_out_lng\":\"90.3299140\",\"check_out_photo\":null,\"status\":\"late\",\"late_minutes\":30,\"remarks\":null}}', NULL, '2026-08-23 05:34:23', '2026-08-23 05:34:23'),
(62, 'attendances', 'created', 'App\\Models\\Attendance', 'created', 3, NULL, NULL, '{\"attributes\":{\"user_id\":80,\"date\":\"2026-08-19\",\"check_in_at\":\"2026-08-19T09:00:00.000000Z\",\"check_in_lat\":\"23.7280050\",\"check_in_lng\":\"90.3437740\",\"check_in_photo\":null,\"check_out_at\":\"2026-08-19T13:00:00.000000Z\",\"check_out_lat\":\"23.7169730\",\"check_out_lng\":\"90.4464030\",\"check_out_photo\":null,\"status\":\"half_day\",\"late_minutes\":0,\"remarks\":null}}', NULL, '2026-08-23 05:34:23', '2026-08-23 05:34:23'),
(63, 'attendances', 'created', 'App\\Models\\Attendance', 'created', 4, NULL, NULL, '{\"attributes\":{\"user_id\":81,\"date\":\"2026-08-18\",\"check_in_at\":null,\"check_in_lat\":null,\"check_in_lng\":null,\"check_in_photo\":null,\"check_out_at\":null,\"check_out_lat\":null,\"check_out_lng\":null,\"check_out_photo\":null,\"status\":\"absent\",\"late_minutes\":0,\"remarks\":null}}', NULL, '2026-08-23 05:34:23', '2026-08-23 05:34:23'),
(64, 'attendances', 'created', 'App\\Models\\Attendance', 'created', 5, NULL, NULL, '{\"attributes\":{\"user_id\":80,\"date\":\"2026-08-17\",\"check_in_at\":\"2026-08-17T09:00:00.000000Z\",\"check_in_lat\":\"23.8366390\",\"check_in_lng\":\"90.4251380\",\"check_in_photo\":null,\"check_out_at\":\"2026-08-17T17:00:00.000000Z\",\"check_out_lat\":\"23.6009540\",\"check_out_lng\":\"90.3522290\",\"check_out_photo\":null,\"status\":\"present\",\"late_minutes\":0,\"remarks\":null}}', NULL, '2026-08-23 05:34:23', '2026-08-23 05:34:23'),
(65, 'visit_plans', 'created', 'App\\Models\\VisitPlan', 'created', 1, NULL, NULL, '{\"attributes\":{\"user_id\":80,\"dealer_id\":1,\"territory_id\":1,\"planned_date\":\"2026-08-21\",\"status\":\"completed\",\"notes\":null}}', NULL, '2026-08-23 05:34:24', '2026-08-23 05:34:24'),
(66, 'visits', 'created', 'App\\Models\\Visit', 'created', 1, NULL, NULL, '{\"attributes\":{\"visit_plan_id\":1,\"user_id\":80,\"dealer_id\":1,\"check_in_at\":\"2026-08-21T12:06:00.000000Z\",\"check_in_lat\":\"23.7834950\",\"check_in_lng\":\"90.3694400\",\"check_in_photo\":null,\"check_out_at\":\"2026-08-21T12:24:00.000000Z\",\"check_out_lat\":\"23.7834950\",\"check_out_lng\":\"90.3694400\",\"check_out_photo\":null,\"is_gps_verified\":null,\"distance_from_dealer_meters\":null,\"feedback\":\"Consequatur nesciunt ullam sit porro repellat.\"}}', NULL, '2026-08-23 05:34:24', '2026-08-23 05:34:24'),
(67, 'visit_plans', 'created', 'App\\Models\\VisitPlan', 'created', 2, NULL, NULL, '{\"attributes\":{\"user_id\":81,\"dealer_id\":2,\"territory_id\":2,\"planned_date\":\"2026-08-20\",\"status\":\"completed\",\"notes\":null}}', NULL, '2026-08-23 05:34:24', '2026-08-23 05:34:24'),
(68, 'visits', 'created', 'App\\Models\\Visit', 'created', 2, NULL, NULL, '{\"attributes\":{\"visit_plan_id\":2,\"user_id\":81,\"dealer_id\":2,\"check_in_at\":\"2026-08-20T10:13:00.000000Z\",\"check_in_lat\":\"23.7281630\",\"check_in_lng\":\"90.3903100\",\"check_in_photo\":null,\"check_out_at\":\"2026-08-20T10:59:00.000000Z\",\"check_out_lat\":\"23.7281630\",\"check_out_lng\":\"90.3903100\",\"check_out_photo\":null,\"is_gps_verified\":null,\"distance_from_dealer_meters\":null,\"feedback\":\"Est quia ut iure minima voluptate est.\"}}', NULL, '2026-08-23 05:34:24', '2026-08-23 05:34:24'),
(69, 'visit_plans', 'created', 'App\\Models\\VisitPlan', 'created', 3, NULL, NULL, '{\"attributes\":{\"user_id\":80,\"dealer_id\":3,\"territory_id\":3,\"planned_date\":\"2026-08-19\",\"status\":\"completed\",\"notes\":null}}', NULL, '2026-08-23 05:34:24', '2026-08-23 05:34:24'),
(70, 'visits', 'created', 'App\\Models\\Visit', 'created', 3, NULL, NULL, '{\"attributes\":{\"visit_plan_id\":3,\"user_id\":80,\"dealer_id\":3,\"check_in_at\":\"2026-08-19T11:39:00.000000Z\",\"check_in_lat\":\"23.7858560\",\"check_in_lng\":\"90.3871860\",\"check_in_photo\":null,\"check_out_at\":\"2026-08-19T12:11:00.000000Z\",\"check_out_lat\":\"23.7858560\",\"check_out_lng\":\"90.3871860\",\"check_out_photo\":null,\"is_gps_verified\":null,\"distance_from_dealer_meters\":null,\"feedback\":\"Recusandae vel consequatur dolores maiores aut cumque necessitatibus.\"}}', NULL, '2026-08-23 05:34:24', '2026-08-23 05:34:24'),
(71, 'visit_plans', 'created', 'App\\Models\\VisitPlan', 'created', 4, NULL, NULL, '{\"attributes\":{\"user_id\":81,\"dealer_id\":4,\"territory_id\":1,\"planned_date\":\"2026-08-18\",\"status\":\"completed\",\"notes\":null}}', NULL, '2026-08-23 05:34:24', '2026-08-23 05:34:24'),
(72, 'visits', 'created', 'App\\Models\\Visit', 'created', 4, NULL, NULL, '{\"attributes\":{\"visit_plan_id\":4,\"user_id\":81,\"dealer_id\":4,\"check_in_at\":\"2026-08-18T11:25:00.000000Z\",\"check_in_lat\":\"23.8456870\",\"check_in_lng\":\"90.4240890\",\"check_in_photo\":null,\"check_out_at\":\"2026-08-18T12:23:00.000000Z\",\"check_out_lat\":\"23.8456870\",\"check_out_lng\":\"90.4240890\",\"check_out_photo\":null,\"is_gps_verified\":null,\"distance_from_dealer_meters\":null,\"feedback\":\"Corrupti quasi eum voluptatem eligendi ducimus quo.\"}}', NULL, '2026-08-23 05:34:24', '2026-08-23 05:34:24'),
(73, 'visit_plans', 'created', 'App\\Models\\VisitPlan', 'created', 5, NULL, NULL, '{\"attributes\":{\"user_id\":80,\"dealer_id\":5,\"territory_id\":2,\"planned_date\":\"2026-08-17\",\"status\":\"completed\",\"notes\":null}}', NULL, '2026-08-23 05:34:24', '2026-08-23 05:34:24'),
(74, 'visits', 'created', 'App\\Models\\Visit', 'created', 5, NULL, NULL, '{\"attributes\":{\"visit_plan_id\":5,\"user_id\":80,\"dealer_id\":5,\"check_in_at\":\"2026-08-17T13:34:00.000000Z\",\"check_in_lat\":\"23.8114280\",\"check_in_lng\":\"90.3568150\",\"check_in_photo\":null,\"check_out_at\":\"2026-08-17T14:25:00.000000Z\",\"check_out_lat\":\"23.8114280\",\"check_out_lng\":\"90.3568150\",\"check_out_photo\":null,\"is_gps_verified\":null,\"distance_from_dealer_meters\":null,\"feedback\":\"Quis eum delectus quia excepturi ut atque nam.\"}}', NULL, '2026-08-23 05:34:24', '2026-08-23 05:34:24'),
(75, 'orders', 'created', 'App\\Models\\Order', 'created', 1, NULL, NULL, '{\"attributes\":{\"user_id\":80,\"dealer_id\":1,\"order_date\":\"2026-08-23\",\"total_amount\":\"0.00\",\"remarks\":null}}', NULL, '2026-08-23 05:34:25', '2026-08-23 05:34:25'),
(76, 'orders', 'updated', 'App\\Models\\Order', 'updated', 1, NULL, NULL, '{\"attributes\":{\"total_amount\":\"2627944.00\"},\"old\":{\"total_amount\":\"0.00\"}}', NULL, '2026-08-23 05:34:25', '2026-08-23 05:34:25'),
(77, 'orders', 'created', 'App\\Models\\Order', 'created', 2, NULL, NULL, '{\"attributes\":{\"user_id\":81,\"dealer_id\":2,\"order_date\":\"2026-08-22\",\"total_amount\":\"0.00\",\"remarks\":null}}', NULL, '2026-08-23 05:34:25', '2026-08-23 05:34:25'),
(78, 'orders', 'updated', 'App\\Models\\Order', 'updated', 2, NULL, NULL, '{\"attributes\":{\"total_amount\":\"405871.76\"},\"old\":{\"total_amount\":\"0.00\"}}', NULL, '2026-08-23 05:34:25', '2026-08-23 05:34:25'),
(79, 'orders', 'created', 'App\\Models\\Order', 'created', 3, NULL, NULL, '{\"attributes\":{\"user_id\":80,\"dealer_id\":3,\"order_date\":\"2026-08-21\",\"total_amount\":\"0.00\",\"remarks\":null}}', NULL, '2026-08-23 05:34:25', '2026-08-23 05:34:25'),
(80, 'orders', 'updated', 'App\\Models\\Order', 'updated', 3, NULL, NULL, '{\"attributes\":{\"total_amount\":\"341712.00\"},\"old\":{\"total_amount\":\"0.00\"}}', NULL, '2026-08-23 05:34:25', '2026-08-23 05:34:25'),
(81, 'orders', 'created', 'App\\Models\\Order', 'created', 4, NULL, NULL, '{\"attributes\":{\"user_id\":81,\"dealer_id\":4,\"order_date\":\"2026-08-20\",\"total_amount\":\"0.00\",\"remarks\":null}}', NULL, '2026-08-23 05:34:25', '2026-08-23 05:34:25'),
(82, 'orders', 'updated', 'App\\Models\\Order', 'updated', 4, NULL, NULL, '{\"attributes\":{\"total_amount\":\"130625.60\"},\"old\":{\"total_amount\":\"0.00\"}}', NULL, '2026-08-23 05:34:25', '2026-08-23 05:34:25'),
(83, 'orders', 'created', 'App\\Models\\Order', 'created', 5, NULL, NULL, '{\"attributes\":{\"user_id\":80,\"dealer_id\":5,\"order_date\":\"2026-08-19\",\"total_amount\":\"0.00\",\"remarks\":null}}', NULL, '2026-08-23 05:34:25', '2026-08-23 05:34:25'),
(84, 'orders', 'updated', 'App\\Models\\Order', 'updated', 5, NULL, NULL, '{\"attributes\":{\"total_amount\":\"101871.00\"},\"old\":{\"total_amount\":\"0.00\"}}', NULL, '2026-08-23 05:34:25', '2026-08-23 05:34:25'),
(85, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 1, NULL, NULL, '{\"attributes\":{\"user_id\":80,\"dealer_id\":1,\"collection_date\":\"2026-08-23\",\"amount\":\"1629325.28\",\"payment_method\":\"cheque\",\"reference_no\":\"REF-00329VW\",\"remarks\":null}}', NULL, '2026-08-23 05:34:26', '2026-08-23 05:34:26'),
(86, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 2, NULL, NULL, '{\"attributes\":{\"user_id\":81,\"dealer_id\":2,\"collection_date\":\"2026-08-22\",\"amount\":\"235405.62\",\"payment_method\":\"bank_transfer\",\"reference_no\":\"REF-56506JU\",\"remarks\":null}}', NULL, '2026-08-23 05:34:26', '2026-08-23 05:34:26'),
(87, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 3, NULL, NULL, '{\"attributes\":{\"user_id\":80,\"dealer_id\":3,\"collection_date\":\"2026-08-21\",\"amount\":\"252866.88\",\"payment_method\":\"cheque\",\"reference_no\":\"REF-11154PU\",\"remarks\":\"Quis ad est asperiores qui architecto ut tenetur.\"}}', NULL, '2026-08-23 05:34:26', '2026-08-23 05:34:26'),
(88, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 4, NULL, NULL, '{\"attributes\":{\"user_id\":81,\"dealer_id\":4,\"collection_date\":\"2026-08-20\",\"amount\":\"77069.10\",\"payment_method\":\"mobile_banking\",\"reference_no\":\"REF-82410BJ\",\"remarks\":\"Tempora non quidem quis enim error sint minima.\"}}', NULL, '2026-08-23 05:34:26', '2026-08-23 05:34:26'),
(89, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 5, NULL, NULL, '{\"attributes\":{\"user_id\":80,\"dealer_id\":5,\"collection_date\":\"2026-08-19\",\"amount\":\"100852.29\",\"payment_method\":\"cheque\",\"reference_no\":\"REF-62367NZ\",\"remarks\":null}}', NULL, '2026-08-23 05:34:26', '2026-08-23 05:34:26'),
(90, 'targets', 'created', 'App\\Models\\Target', 'created', 1, NULL, NULL, '{\"attributes\":{\"user_id\":80,\"month\":8,\"year\":2026,\"order_value_target\":\"86102.98\",\"collection_target\":\"68075.73\",\"quantity_target\":89,\"notes\":null}}', NULL, '2026-08-23 05:34:27', '2026-08-23 05:34:27'),
(91, 'targets', 'created', 'App\\Models\\Target', 'created', 2, NULL, NULL, '{\"attributes\":{\"user_id\":80,\"month\":7,\"year\":2026,\"order_value_target\":\"99452.77\",\"collection_target\":\"121565.86\",\"quantity_target\":96,\"notes\":null}}', NULL, '2026-08-23 05:34:27', '2026-08-23 05:34:27'),
(92, 'targets', 'created', 'App\\Models\\Target', 'created', 3, NULL, NULL, '{\"attributes\":{\"user_id\":80,\"month\":6,\"year\":2026,\"order_value_target\":\"56521.97\",\"collection_target\":\"59467.02\",\"quantity_target\":90,\"notes\":null}}', NULL, '2026-08-23 05:34:27', '2026-08-23 05:34:27'),
(93, 'targets', 'created', 'App\\Models\\Target', 'created', 4, NULL, NULL, '{\"attributes\":{\"user_id\":81,\"month\":8,\"year\":2026,\"order_value_target\":\"185405.51\",\"collection_target\":\"94652.71\",\"quantity_target\":80,\"notes\":null}}', NULL, '2026-08-23 05:34:27', '2026-08-23 05:34:27'),
(94, 'targets', 'created', 'App\\Models\\Target', 'created', 5, NULL, NULL, '{\"attributes\":{\"user_id\":81,\"month\":7,\"year\":2026,\"order_value_target\":\"75875.89\",\"collection_target\":\"132167.79\",\"quantity_target\":38,\"notes\":null}}', NULL, '2026-08-23 05:34:27', '2026-08-23 05:34:27'),
(95, 'targets', 'created', 'App\\Models\\Target', 'created', 1, NULL, NULL, '{\"attributes\":{\"user_id\":80,\"month\":8,\"year\":2026,\"order_value_target\":\"2856520.11\",\"collection_target\":\"1844231.34\",\"quantity_target\":66,\"notes\":null}}', NULL, '2026-08-23 05:36:10', '2026-08-23 05:36:10'),
(96, 'targets', 'created', 'App\\Models\\Target', 'created', 2, NULL, NULL, '{\"attributes\":{\"user_id\":80,\"month\":7,\"year\":2026,\"order_value_target\":\"129235.75\",\"collection_target\":\"139414.16\",\"quantity_target\":80,\"notes\":null}}', NULL, '2026-08-23 05:36:10', '2026-08-23 05:36:10'),
(97, 'targets', 'created', 'App\\Models\\Target', 'created', 3, NULL, NULL, '{\"attributes\":{\"user_id\":80,\"month\":6,\"year\":2026,\"order_value_target\":\"86946.14\",\"collection_target\":\"51690.77\",\"quantity_target\":72,\"notes\":null}}', NULL, '2026-08-23 05:36:10', '2026-08-23 05:36:10'),
(98, 'targets', 'created', 'App\\Models\\Target', 'created', 4, NULL, NULL, '{\"attributes\":{\"user_id\":81,\"month\":8,\"year\":2026,\"order_value_target\":\"380913.13\",\"collection_target\":\"221857.05\",\"quantity_target\":46,\"notes\":null}}', NULL, '2026-08-23 05:36:10', '2026-08-23 05:36:10'),
(99, 'targets', 'created', 'App\\Models\\Target', 'created', 5, NULL, NULL, '{\"attributes\":{\"user_id\":81,\"month\":7,\"year\":2026,\"order_value_target\":\"187078.77\",\"collection_target\":\"51401.96\",\"quantity_target\":35,\"notes\":null}}', NULL, '2026-08-23 05:36:10', '2026-08-23 05:36:10'),
(100, 'users', 'updated', 'App\\Models\\User', 'updated', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"phone\":\"01988887777\"},\"old\":{\"phone\":\"01173784260\"}}', NULL, '2026-08-23 23:18:06', '2026-08-23 23:18:06'),
(101, 'users', 'updated', 'App\\Models\\User', 'updated', 1, NULL, NULL, '{\"attributes\":{\"phone\":\"01173784260\"},\"old\":{\"phone\":\"01988887777\"}}', NULL, '2026-08-23 23:18:42', '2026-08-23 23:18:42'),
(102, 'settings', 'updated', 'App\\Models\\Setting', 'updated', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"company_logo\":\"settings\\/vKmKYVjGFsZxNpZQN68KHSgQmp62nBks7YjjcW04.png\",\"company_favicon\":\"settings\\/EmqVOJsKAs9g0DDmRQ5XVpVY2MTO221U3wehG7qx.png\"},\"old\":{\"company_logo\":null,\"company_favicon\":null}}', NULL, '2026-08-24 00:31:12', '2026-08-24 00:31:12'),
(103, 'settings', 'updated', 'App\\Models\\Setting', 'updated', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"company_name\":\"Gazi Group SFA\"},\"old\":{\"company_name\":\"Gazi Pump & Motors SFA\"}}', NULL, '2026-08-24 00:31:35', '2026-08-24 00:31:35'),
(104, 'settings', 'updated', 'App\\Models\\Setting', 'updated', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"company_name\":\"SFA\"},\"old\":{\"company_name\":\"Gazi Group SFA\"}}', NULL, '2026-08-24 00:31:43', '2026-08-24 00:31:43'),
(105, 'settings', 'updated', 'App\\Models\\Setting', 'updated', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"company_name\":\"Gazi Group SFA\"},\"old\":{\"company_name\":\"SFA\"}}', NULL, '2026-08-24 00:32:30', '2026-08-24 00:32:30'),
(106, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 6, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":80,\"dealer_id\":5,\"collection_date\":\"2026-08-25\",\"amount\":\"50.00\",\"payment_method\":\"cheque\",\"reference_no\":\"CHQ-BROWSER-TEST\",\"cheque_image\":\"collection-entries\\/UdD72kKIkBeiikoVAdk0HlOoCg6DQUfB20semuLQ.png\",\"remarks\":null}}', NULL, '2026-08-25 02:22:23', '2026-08-25 02:22:23'),
(107, 'collection_entries', 'deleted', 'App\\Models\\CollectionEntry', 'deleted', 6, NULL, NULL, '{\"old\":{\"user_id\":80,\"dealer_id\":5,\"collection_date\":\"2026-08-25\",\"amount\":\"50.00\",\"payment_method\":\"cheque\",\"reference_no\":\"CHQ-BROWSER-TEST\",\"cheque_image\":\"collection-entries\\/UdD72kKIkBeiikoVAdk0HlOoCg6DQUfB20semuLQ.png\",\"remarks\":null}}', NULL, '2026-08-25 02:23:08', '2026-08-25 02:23:08'),
(108, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 7, NULL, NULL, '{\"attributes\":{\"user_id\":80,\"dealer_id\":1,\"collection_date\":\"2026-08-25\",\"amount\":\"10.00\",\"payment_method\":\"cheque\",\"reference_no\":\"REF-75958NM\",\"cheque_image\":\"collection-entries\\/test-existing.jpg\",\"remarks\":\"Molestiae est esse delectus non sunt molestiae.\"}}', NULL, '2026-08-25 02:43:14', '2026-08-25 02:43:14'),
(109, 'collection_entries', 'deleted', 'App\\Models\\CollectionEntry', 'deleted', 7, NULL, NULL, '{\"old\":{\"user_id\":80,\"dealer_id\":1,\"collection_date\":\"2026-08-25\",\"amount\":\"10.00\",\"payment_method\":\"cheque\",\"reference_no\":\"REF-75958NM\",\"cheque_image\":\"collection-entries\\/test-existing.jpg\",\"remarks\":\"Molestiae est esse delectus non sunt molestiae.\"}}', NULL, '2026-08-25 02:43:42', '2026-08-25 02:43:42'),
(110, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 8, NULL, NULL, '{\"attributes\":{\"user_id\":80,\"dealer_id\":1,\"collection_date\":\"2026-08-25\",\"amount\":\"25.00\",\"payment_method\":\"cheque\",\"reference_no\":\"PDF-TEST-001\",\"cheque_image\":\"collection-entries\\/pdf-test-cheque.jpg\",\"remarks\":null}}', NULL, '2026-08-25 02:57:08', '2026-08-25 02:57:08'),
(111, 'collection_entries', 'deleted', 'App\\Models\\CollectionEntry', 'deleted', 8, NULL, NULL, '{\"old\":{\"user_id\":80,\"dealer_id\":1,\"collection_date\":\"2026-08-25\",\"amount\":\"25.00\",\"payment_method\":\"cheque\",\"reference_no\":\"PDF-TEST-001\",\"cheque_image\":\"collection-entries\\/pdf-test-cheque.jpg\",\"remarks\":null}}', NULL, '2026-08-25 02:59:13', '2026-08-25 02:59:13'),
(112, 'collection_entries', 'updated', 'App\\Models\\CollectionEntry', 'updated', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"cheque_image\":\"collection-entries\\/24Z6UTp3dkL3EdI4osm1pp7u4pOZwKyLC6XfQLCC.png\"},\"old\":{\"cheque_image\":null}}', NULL, '2026-08-25 03:11:37', '2026-08-25 03:11:37'),
(113, 'dealers', 'updated', 'App\\Models\\Dealer', 'updated', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"email\":\"savarpump@gmail.com\"},\"old\":{\"email\":null}}', NULL, '2026-08-25 03:15:34', '2026-08-25 03:15:34'),
(114, 'users', 'created', 'App\\Models\\User', 'created', 82, NULL, NULL, '{\"attributes\":{\"employee_id\":\"EMP-04600\",\"name\":\"Prof. Korbin Stracke V\",\"email\":\"ahuels@example.com\",\"phone\":\"01999018857\",\"photo\":null,\"designation\":\"Sales Executive\",\"date_of_birth\":\"1990-08-31\",\"manager_id\":null,\"sales_team_id\":null,\"status\":true,\"password\":\"$2y$12$WrV3fpqhVhGhpRnmaEy7NeElnKtFpIbNLZholPvlet7HNAScS49mK\"}}', NULL, '2026-08-25 03:20:10', '2026-08-25 03:20:10'),
(115, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 9, NULL, NULL, '{\"attributes\":{\"user_id\":82,\"dealer_id\":1,\"collection_date\":\"2026-08-25\",\"amount\":\"777.50\",\"payment_method\":\"cash\",\"reference_no\":\"REF-76570FT\",\"cheque_image\":null,\"remarks\":\"Quia at odit quo consequatur sint iste.\"}}', NULL, '2026-08-25 03:20:10', '2026-08-25 03:20:10'),
(116, 'collection_entries', 'deleted', 'App\\Models\\CollectionEntry', 'deleted', 9, NULL, NULL, '{\"old\":{\"user_id\":82,\"dealer_id\":1,\"collection_date\":\"2026-08-25\",\"amount\":\"777.50\",\"payment_method\":\"cash\",\"reference_no\":\"REF-76570FT\",\"cheque_image\":null,\"remarks\":\"Quia at odit quo consequatur sint iste.\"}}', NULL, '2026-08-25 03:21:45', '2026-08-25 03:21:45'),
(117, 'products', 'updated', 'App\\Models\\Product', 'updated', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"status\":false},\"old\":{\"status\":true}}', NULL, '2026-08-25 03:57:29', '2026-08-25 03:57:29'),
(118, 'products', 'updated', 'App\\Models\\Product', 'updated', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"status\":true},\"old\":{\"status\":false}}', NULL, '2026-08-25 03:57:55', '2026-08-25 03:57:55'),
(119, 'products', 'updated', 'App\\Models\\Product', 'updated', 9, 'App\\Models\\User', 1, '{\"attributes\":{\"status\":false},\"old\":{\"status\":true}}', NULL, '2026-08-25 03:58:28', '2026-08-25 03:58:28'),
(120, 'products', 'created', 'App\\Models\\Product', 'created', 41, NULL, NULL, '{\"attributes\":{\"category_id\":1,\"sales_team_id\":1,\"name\":\"Tinker Team Filter Test Product\",\"sku\":\"SKU-TINKER-TEAMTEST-01\",\"price\":\"500.00\",\"description\":null,\"image\":null,\"status\":true}}', NULL, '2026-08-25 03:59:54', '2026-08-25 03:59:54'),
(121, 'users', 'created', 'App\\Models\\User', 'created', 83, NULL, NULL, '{\"attributes\":{\"employee_id\":\"EMP-52723\",\"name\":\"Prof. Sydney Baumbach\",\"email\":\"lleannon@example.com\",\"phone\":\"01214502719\",\"photo\":null,\"designation\":\"Sales Executive\",\"date_of_birth\":\"1982-10-19\",\"manager_id\":null,\"sales_team_id\":1,\"status\":true,\"password\":\"$2y$12$gE8lU3qVwS.iYrZMV65VVe10tDSXNbTgYSuMP1d3KKeCAFZLHnGXG\"}}', NULL, '2026-08-25 03:59:55', '2026-08-25 03:59:55'),
(122, 'users', 'created', 'App\\Models\\User', 'created', 84, NULL, NULL, '{\"attributes\":{\"employee_id\":\"EMP-94883\",\"name\":\"Dr. Carrie Erdman IV\",\"email\":\"oconnell.abe@example.org\",\"phone\":\"01539455189\",\"photo\":null,\"designation\":\"Sales Executive\",\"date_of_birth\":\"1983-03-21\",\"manager_id\":null,\"sales_team_id\":2,\"status\":true,\"password\":\"$2y$12$gE8lU3qVwS.iYrZMV65VVe10tDSXNbTgYSuMP1d3KKeCAFZLHnGXG\"}}', NULL, '2026-08-25 03:59:55', '2026-08-25 03:59:55'),
(123, 'users', 'created', 'App\\Models\\User', 'created', 85, NULL, NULL, '{\"attributes\":{\"employee_id\":\"EMP-27402\",\"name\":\"Keanu Bergstrom DDS\",\"email\":\"wwisozk@example.org\",\"phone\":\"01093783673\",\"photo\":null,\"designation\":\"Sales Executive\",\"date_of_birth\":\"1986-10-07\",\"manager_id\":null,\"sales_team_id\":null,\"status\":true,\"password\":\"$2y$12$gE8lU3qVwS.iYrZMV65VVe10tDSXNbTgYSuMP1d3KKeCAFZLHnGXG\"}}', NULL, '2026-08-25 03:59:55', '2026-08-25 03:59:55'),
(124, 'products', 'deleted', 'App\\Models\\Product', 'deleted', 41, NULL, NULL, '{\"old\":{\"category_id\":1,\"sales_team_id\":1,\"name\":\"Tinker Team Filter Test Product\",\"sku\":\"SKU-TINKER-TEAMTEST-01\",\"price\":\"500.00\",\"description\":null,\"image\":null,\"status\":true}}', NULL, '2026-08-25 03:59:55', '2026-08-25 03:59:55'),
(125, 'users', 'deleted', 'App\\Models\\User', 'deleted', 83, NULL, NULL, '{\"old\":{\"employee_id\":\"EMP-52723\",\"name\":\"Prof. Sydney Baumbach\",\"email\":\"lleannon@example.com\",\"phone\":\"01214502719\",\"photo\":null,\"designation\":\"Sales Executive\",\"date_of_birth\":\"1982-10-19\",\"manager_id\":null,\"sales_team_id\":1,\"status\":true,\"password\":\"$2y$12$gE8lU3qVwS.iYrZMV65VVe10tDSXNbTgYSuMP1d3KKeCAFZLHnGXG\"}}', NULL, '2026-08-25 03:59:55', '2026-08-25 03:59:55'),
(126, 'users', 'deleted', 'App\\Models\\User', 'deleted', 84, NULL, NULL, '{\"old\":{\"employee_id\":\"EMP-94883\",\"name\":\"Dr. Carrie Erdman IV\",\"email\":\"oconnell.abe@example.org\",\"phone\":\"01539455189\",\"photo\":null,\"designation\":\"Sales Executive\",\"date_of_birth\":\"1983-03-21\",\"manager_id\":null,\"sales_team_id\":2,\"status\":true,\"password\":\"$2y$12$gE8lU3qVwS.iYrZMV65VVe10tDSXNbTgYSuMP1d3KKeCAFZLHnGXG\"}}', NULL, '2026-08-25 03:59:55', '2026-08-25 03:59:55'),
(127, 'users', 'deleted', 'App\\Models\\User', 'deleted', 85, NULL, NULL, '{\"old\":{\"employee_id\":\"EMP-27402\",\"name\":\"Keanu Bergstrom DDS\",\"email\":\"wwisozk@example.org\",\"phone\":\"01093783673\",\"photo\":null,\"designation\":\"Sales Executive\",\"date_of_birth\":\"1986-10-07\",\"manager_id\":null,\"sales_team_id\":null,\"status\":true,\"password\":\"$2y$12$gE8lU3qVwS.iYrZMV65VVe10tDSXNbTgYSuMP1d3KKeCAFZLHnGXG\"}}', NULL, '2026-08-25 03:59:55', '2026-08-25 03:59:55'),
(128, 'products', 'updated', 'App\\Models\\Product', 'updated', 9, 'App\\Models\\User', 1, '{\"attributes\":{\"status\":true},\"old\":{\"status\":false}}', NULL, '2026-08-25 04:45:22', '2026-08-25 04:45:22'),
(129, 'holidays', 'created', 'App\\Models\\Holiday', 'created', 1, NULL, NULL, '{\"attributes\":{\"name\":\"Victory Day\",\"date\":\"2026-12-16\",\"description\":\"National holiday\",\"status\":true}}', NULL, '2026-08-25 04:52:02', '2026-08-25 04:52:02'),
(130, 'holidays', 'created', 'App\\Models\\Holiday', 'created', 2, NULL, NULL, '{\"attributes\":{\"name\":\"Test 2026 Holiday\",\"date\":\"2026-05-01\",\"description\":null,\"status\":true}}', NULL, '2026-08-25 05:08:20', '2026-08-25 05:08:20'),
(131, 'holidays', 'created', 'App\\Models\\Holiday', 'created', 3, NULL, NULL, '{\"attributes\":{\"name\":\"Test 2027 Holiday\",\"date\":\"2027-05-01\",\"description\":null,\"status\":true}}', NULL, '2026-08-25 05:08:20', '2026-08-25 05:08:20'),
(132, 'announcements', 'created', 'App\\Models\\Announcement', 'created', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"title\":\"Test Dealer Notification\",\"message\":\"This is a live verification message sent to a specific dealer.\",\"audience\":\"dealer\",\"audience_role\":null,\"audience_territory_id\":null,\"audience_user_id\":null,\"audience_dealer_id\":1,\"sent_by\":1,\"recipient_count\":1}}', NULL, '2026-08-26 22:06:01', '2026-08-26 22:06:01'),
(133, 'retailers', 'created', 'App\\Models\\Retailer', 'created', 1, NULL, NULL, '{\"attributes\":{\"dealer_id\":1,\"name\":\"Savar Retail Corner\",\"phone\":\"01911111111\",\"email\":null,\"image\":null,\"shipping_address\":null,\"status\":true}}', NULL, '2026-08-26 23:05:34', '2026-08-26 23:05:34');
INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES
(134, 'users', 'updated', 'App\\Models\\User', 'updated', 1, NULL, NULL, '{\"attributes\":{\"password\":\"$2y$12$SS5jB8vCBqAKdtN9v835dOVWYAkwyldeWVpdBgfFc1qxPp0d6aFi2\"},\"old\":{\"password\":\"$2y$12$o9X3YZ1fno7396lCkjj51Ojw5m1DoiHEevsB5Iylz31aBrRnrGBLK\"}}', NULL, '2026-08-27 00:27:43', '2026-08-27 00:27:43'),
(135, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 10, NULL, NULL, '{\"attributes\":{\"user_id\":80,\"dealer_id\":1,\"collection_date\":\"2026-08-27\",\"amount\":\"4000.00\",\"payment_method\":\"cash\",\"reference_no\":\"REF-25485NQ\",\"cheque_image\":null,\"cheque_status\":null,\"otp_verified_at\":null,\"remarks\":null}}', NULL, '2026-08-27 00:41:37', '2026-08-27 00:41:37'),
(136, 'cash_handovers', 'created', 'App\\Models\\CashHandover', 'created', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":80,\"amount\":\"1500.00\",\"handover_date\":\"2026-08-27\",\"status\":\"pending\",\"confirmed_by\":null,\"confirmed_at\":null,\"remarks\":null}}', NULL, '2026-08-27 00:41:57', '2026-08-27 00:41:57'),
(137, 'cash_handovers', 'updated', 'App\\Models\\CashHandover', 'updated', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"status\":\"confirmed\",\"confirmed_by\":1,\"confirmed_at\":\"2026-08-27T06:42:08.000000Z\"},\"old\":{\"status\":\"pending\",\"confirmed_by\":null,\"confirmed_at\":null}}', NULL, '2026-08-27 00:42:08', '2026-08-27 00:42:08'),
(138, 'orders', 'updated', 'App\\Models\\Order', 'updated', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"status\":\"approved\",\"approved_by\":1,\"approved_at\":\"2026-08-27T07:11:56.000000Z\"},\"old\":{\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-27 01:11:56', '2026-08-27 01:11:56'),
(139, 'orders', 'updated', 'App\\Models\\Order', 'updated', 2, 'App\\Models\\User', 1, '{\"attributes\":{\"status\":\"rejected\",\"approved_by\":1,\"approved_at\":\"2026-08-27T07:12:04.000000Z\"},\"old\":{\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-27 01:12:04', '2026-08-27 01:12:04'),
(140, 'collection_entries', 'updated', 'App\\Models\\CollectionEntry', 'updated', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"status\":\"approved\",\"approved_by\":1,\"approved_at\":\"2026-08-27T07:17:55.000000Z\"},\"old\":{\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-27 01:17:55', '2026-08-27 01:17:55'),
(141, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 6, 'App\\Models\\User', 1, '{\"attributes\":{\"dealer_code\":\"DLR-1787816922\",\"name\":\"Karim Traders\",\"phone\":\"01712345678\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":\"23.8103000\",\"gps_lng\":\"90.4125000\",\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true}}', NULL, '2026-08-27 01:48:42', '2026-08-27 01:48:42'),
(142, 'attendances', 'created', 'App\\Models\\Attendance', 'created', 6, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"date\":\"2026-08-27\",\"check_in_at\":\"2026-08-27T07:48:44.000000Z\",\"check_in_lat\":\"23.8103000\",\"check_in_lng\":\"90.4125000\",\"check_in_photo\":\"attendance\\/9Xp8xnuXmswVuC0x8ibDIpWtsM54nLh47Os3QGaw.jpg\",\"check_out_at\":null,\"check_out_lat\":null,\"check_out_lng\":null,\"check_out_photo\":null,\"status\":\"present\",\"late_minutes\":0,\"remarks\":null}}', NULL, '2026-08-27 01:48:44', '2026-08-27 01:48:44'),
(143, 'attendances', 'updated', 'App\\Models\\Attendance', 'updated', 6, 'App\\Models\\User', 1, '{\"attributes\":{\"check_out_at\":\"2026-08-27T07:48:45.000000Z\",\"check_out_lat\":\"23.8110000\",\"check_out_lng\":\"90.4130000\",\"check_out_photo\":\"attendance\\/yQsWGbi0SywCGp8vIicLpEwNypkmUv43ykkCinuQ.jpg\",\"status\":\"half_day\"},\"old\":{\"check_out_at\":null,\"check_out_lat\":null,\"check_out_lng\":null,\"check_out_photo\":null,\"status\":\"present\"}}', NULL, '2026-08-27 01:48:45', '2026-08-27 01:48:45'),
(144, 'visit_plans', 'created', 'App\\Models\\VisitPlan', 'created', 6, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"territory_id\":null,\"planned_date\":\"2026-08-28\",\"status\":\"planned\",\"notes\":\"Follow up on last order.\"}}', NULL, '2026-08-27 01:48:47', '2026-08-27 01:48:47'),
(145, 'visits', 'created', 'App\\Models\\Visit', 'created', 6, 'App\\Models\\User', 1, '{\"attributes\":{\"visit_plan_id\":null,\"user_id\":1,\"dealer_id\":1,\"check_in_at\":\"2026-08-27T07:48:48.000000Z\",\"check_in_lat\":\"23.8103000\",\"check_in_lng\":\"90.4125000\",\"check_in_photo\":\"visits\\/URwOZV5U7581YTcg6vaJqPRKjTVPm2Wrncw6RMl6.jpg\",\"check_out_at\":null,\"check_out_lat\":null,\"check_out_lng\":null,\"check_out_photo\":null,\"is_gps_verified\":false,\"distance_from_dealer_meters\":\"16311.93\",\"feedback\":null}}', NULL, '2026-08-27 01:48:48', '2026-08-27 01:48:48'),
(146, 'visits', 'updated', 'App\\Models\\Visit', 'updated', 6, 'App\\Models\\User', 1, '{\"attributes\":{\"check_out_at\":\"2026-08-27T07:48:48.000000Z\",\"check_out_lat\":\"23.8110000\",\"check_out_lng\":\"90.4130000\",\"check_out_photo\":\"visits\\/cGW6GNQzm4HCUWatWYIIFH6pYd7rvvJzgx8g9uZ5.jpg\",\"feedback\":\"Placed a repeat order.\"},\"old\":{\"check_out_at\":null,\"check_out_lat\":null,\"check_out_lng\":null,\"check_out_photo\":null,\"feedback\":null}}', NULL, '2026-08-27 01:48:48', '2026-08-27 01:48:48'),
(147, 'orders', 'created', 'App\\Models\\Order', 'created', 6, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"retailer_id\":null,\"order_date\":\"2026-08-27\",\"total_amount\":\"42400.00\",\"remarks\":\"Restocked shelf display.\",\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-27 01:48:50', '2026-08-27 01:48:50'),
(148, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 11, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"collection_date\":\"2026-08-27\",\"amount\":\"600.00\",\"payment_method\":\"cash\",\"reference_no\":null,\"cheque_image\":\"collection-entries\\/nZxkmnDxZbHoDmWbV1zuxgJjf3qoGdOyypAH4wOP.jpg\",\"cheque_status\":null,\"otp_verified_at\":null,\"remarks\":\"Collected during routine visit.\",\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-27 01:48:51', '2026-08-27 01:48:51'),
(149, 'users', 'created', 'App\\Models\\User', 'created', 86, NULL, NULL, '{\"attributes\":{\"employee_id\":\"EMP-24764\",\"name\":\"Everett Kuvalis II\",\"email\":\"herman56@example.net\",\"phone\":\"01638329305\",\"photo\":null,\"designation\":\"Sales Executive\",\"date_of_birth\":\"1984-10-31\",\"manager_id\":null,\"sales_team_id\":null,\"status\":true,\"password\":\"$2y$12$KiNuFZn1.9BcH6uDfYFAceuEWBALfk6KBrxji3iM.ROodpAPePVG.\"}}', NULL, '2026-08-27 01:49:25', '2026-08-27 01:49:25'),
(150, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 7, 'App\\Models\\User', 1, '{\"attributes\":{\"dealer_code\":\"DLR-1787816980\",\"name\":\"Karim Traders\",\"phone\":\"01712345678\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":\"23.8103000\",\"gps_lng\":\"90.4125000\",\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true}}', NULL, '2026-08-27 01:49:40', '2026-08-27 01:49:40'),
(151, 'visit_plans', 'created', 'App\\Models\\VisitPlan', 'created', 7, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"territory_id\":null,\"planned_date\":\"2026-08-28\",\"status\":\"planned\",\"notes\":\"Follow up on last order.\"}}', NULL, '2026-08-27 01:49:44', '2026-08-27 01:49:44'),
(152, 'visits', 'created', 'App\\Models\\Visit', 'created', 7, 'App\\Models\\User', 1, '{\"attributes\":{\"visit_plan_id\":null,\"user_id\":1,\"dealer_id\":1,\"check_in_at\":\"2026-08-27T07:49:45.000000Z\",\"check_in_lat\":\"23.8103000\",\"check_in_lng\":\"90.4125000\",\"check_in_photo\":\"visits\\/CyhpQUFedLqcYcSsBKifPYsbZdEOoCTsW4tHkzK7.jpg\",\"check_out_at\":null,\"check_out_lat\":null,\"check_out_lng\":null,\"check_out_photo\":null,\"is_gps_verified\":false,\"distance_from_dealer_meters\":\"16311.93\",\"feedback\":null}}', NULL, '2026-08-27 01:49:45', '2026-08-27 01:49:45'),
(153, 'visits', 'updated', 'App\\Models\\Visit', 'updated', 7, 'App\\Models\\User', 1, '{\"attributes\":{\"check_out_at\":\"2026-08-27T07:49:46.000000Z\",\"check_out_lat\":\"23.8110000\",\"check_out_lng\":\"90.4130000\",\"check_out_photo\":\"visits\\/4Ccf9r2idnlp9Ydov3S7I6pqNCsfyo66H7HRGKF5.jpg\",\"feedback\":\"Placed a repeat order.\"},\"old\":{\"check_out_at\":null,\"check_out_lat\":null,\"check_out_lng\":null,\"check_out_photo\":null,\"feedback\":null}}', NULL, '2026-08-27 01:49:46', '2026-08-27 01:49:46'),
(154, 'orders', 'created', 'App\\Models\\Order', 'created', 7, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"retailer_id\":null,\"order_date\":\"2026-08-27\",\"total_amount\":\"42400.00\",\"remarks\":\"Restocked shelf display.\",\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-27 01:49:47', '2026-08-27 01:49:47'),
(155, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 12, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"collection_date\":\"2026-08-27\",\"amount\":\"600.00\",\"payment_method\":\"cash\",\"reference_no\":null,\"cheque_image\":\"collection-entries\\/23zmotjetcZ5RAhRA0jfzw0hZb7A2v7nwoROypUU.jpg\",\"cheque_status\":null,\"otp_verified_at\":null,\"remarks\":\"Collected during routine visit.\",\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-27 01:49:49', '2026-08-27 01:49:49'),
(156, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 8, 'App\\Models\\User', 1, '{\"attributes\":{\"dealer_code\":\"DLR-1787817010\",\"name\":\"Karim Traders\",\"phone\":\"01712345678\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":\"23.8103000\",\"gps_lng\":\"90.4125000\",\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true}}', NULL, '2026-08-27 01:50:10', '2026-08-27 01:50:10'),
(157, 'visit_plans', 'created', 'App\\Models\\VisitPlan', 'created', 8, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"territory_id\":null,\"planned_date\":\"2026-08-28\",\"status\":\"planned\",\"notes\":\"Follow up on last order.\"}}', NULL, '2026-08-27 01:50:14', '2026-08-27 01:50:14'),
(158, 'visits', 'created', 'App\\Models\\Visit', 'created', 8, 'App\\Models\\User', 1, '{\"attributes\":{\"visit_plan_id\":null,\"user_id\":1,\"dealer_id\":1,\"check_in_at\":\"2026-08-27T07:50:15.000000Z\",\"check_in_lat\":\"23.8103000\",\"check_in_lng\":\"90.4125000\",\"check_in_photo\":\"visits\\/4qnEgByoNYZttwM02FoCPNz8a0eI4APG1OrprfoU.jpg\",\"check_out_at\":null,\"check_out_lat\":null,\"check_out_lng\":null,\"check_out_photo\":null,\"is_gps_verified\":false,\"distance_from_dealer_meters\":\"16311.93\",\"feedback\":null}}', NULL, '2026-08-27 01:50:15', '2026-08-27 01:50:15'),
(159, 'visits', 'updated', 'App\\Models\\Visit', 'updated', 8, 'App\\Models\\User', 1, '{\"attributes\":{\"check_out_at\":\"2026-08-27T07:50:16.000000Z\",\"check_out_lat\":\"23.8110000\",\"check_out_lng\":\"90.4130000\",\"check_out_photo\":\"visits\\/vSy1LxzFRLfYRbwPqaWRcrN0o3VT6GjlnJr7il45.jpg\",\"feedback\":\"Placed a repeat order.\"},\"old\":{\"check_out_at\":null,\"check_out_lat\":null,\"check_out_lng\":null,\"check_out_photo\":null,\"feedback\":null}}', NULL, '2026-08-27 01:50:16', '2026-08-27 01:50:16'),
(160, 'orders', 'created', 'App\\Models\\Order', 'created', 8, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"retailer_id\":null,\"order_date\":\"2026-08-27\",\"total_amount\":\"42400.00\",\"remarks\":\"Restocked shelf display.\",\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-27 01:50:17', '2026-08-27 01:50:17'),
(161, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 13, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"collection_date\":\"2026-08-27\",\"amount\":\"600.00\",\"payment_method\":\"cash\",\"reference_no\":null,\"cheque_image\":\"collection-entries\\/HPc7Ui9qi30pV3uFhKREbbtNsooBYPpIpQytiAEy.jpg\",\"cheque_status\":null,\"otp_verified_at\":null,\"remarks\":\"Collected during routine visit.\",\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-27 01:50:19', '2026-08-27 01:50:19'),
(162, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 9, 'App\\Models\\User', 1, '{\"attributes\":{\"dealer_code\":\"DLR-1787818909\",\"name\":\"Karim Traders\",\"phone\":\"01712345678\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":\"23.8103000\",\"gps_lng\":\"90.4125000\",\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true}}', NULL, '2026-08-27 02:21:49', '2026-08-27 02:21:49'),
(163, 'visit_plans', 'created', 'App\\Models\\VisitPlan', 'created', 9, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"territory_id\":null,\"planned_date\":\"2026-08-28\",\"status\":\"planned\",\"notes\":\"Follow up on last order.\"}}', NULL, '2026-08-27 02:21:54', '2026-08-27 02:21:54'),
(164, 'visits', 'created', 'App\\Models\\Visit', 'created', 9, 'App\\Models\\User', 1, '{\"attributes\":{\"visit_plan_id\":null,\"user_id\":1,\"dealer_id\":1,\"check_in_at\":\"2026-08-27T08:21:55.000000Z\",\"check_in_lat\":\"23.8103000\",\"check_in_lng\":\"90.4125000\",\"check_in_photo\":\"visits\\/MuQcVDS91HLy34IWw1QFHZHfDQ5SXQ34qGWsBESY.jpg\",\"check_out_at\":null,\"check_out_lat\":null,\"check_out_lng\":null,\"check_out_photo\":null,\"is_gps_verified\":false,\"distance_from_dealer_meters\":\"16311.93\",\"feedback\":null}}', NULL, '2026-08-27 02:21:55', '2026-08-27 02:21:55'),
(165, 'visits', 'updated', 'App\\Models\\Visit', 'updated', 9, 'App\\Models\\User', 1, '{\"attributes\":{\"check_out_at\":\"2026-08-27T08:21:56.000000Z\",\"check_out_lat\":\"23.8110000\",\"check_out_lng\":\"90.4130000\",\"check_out_photo\":\"visits\\/vcZDBY3dxpDZPNZHj39aeFOVfNGefGA08kU7Qd1F.jpg\",\"feedback\":\"Placed a repeat order.\"},\"old\":{\"check_out_at\":null,\"check_out_lat\":null,\"check_out_lng\":null,\"check_out_photo\":null,\"feedback\":null}}', NULL, '2026-08-27 02:21:56', '2026-08-27 02:21:56'),
(166, 'orders', 'created', 'App\\Models\\Order', 'created', 9, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"retailer_id\":null,\"order_date\":\"2026-08-27\",\"total_amount\":\"42400.00\",\"remarks\":\"Restocked shelf display.\",\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-27 02:21:57', '2026-08-27 02:21:57'),
(167, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 14, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"collection_date\":\"2026-08-27\",\"amount\":\"600.00\",\"payment_method\":\"cash\",\"reference_no\":null,\"cheque_image\":\"collection-entries\\/vRXJ0ADKVTDfXBfESg7l5yZL2sUGDkyurgwyMFEi.jpg\",\"cheque_status\":null,\"otp_verified_at\":null,\"remarks\":\"Collected during routine visit.\",\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-27 02:21:59', '2026-08-27 02:21:59'),
(168, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 10, 'App\\Models\\User', 1, '{\"attributes\":{\"dealer_code\":\"DLR-1787818944\",\"name\":\"Karim Traders\",\"phone\":\"01712345678\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":\"23.8103000\",\"gps_lng\":\"90.4125000\",\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true}}', NULL, '2026-08-27 02:22:24', '2026-08-27 02:22:24'),
(169, 'visit_plans', 'created', 'App\\Models\\VisitPlan', 'created', 10, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"territory_id\":null,\"planned_date\":\"2026-08-28\",\"status\":\"planned\",\"notes\":\"Follow up on last order.\"}}', NULL, '2026-08-27 02:22:29', '2026-08-27 02:22:29'),
(170, 'visits', 'created', 'App\\Models\\Visit', 'created', 10, 'App\\Models\\User', 1, '{\"attributes\":{\"visit_plan_id\":null,\"user_id\":1,\"dealer_id\":1,\"check_in_at\":\"2026-08-27T08:22:30.000000Z\",\"check_in_lat\":\"23.8103000\",\"check_in_lng\":\"90.4125000\",\"check_in_photo\":\"visits\\/d6ErNxreWFwgrDvVFD90sck5thAisZCLsi6j9zKH.jpg\",\"check_out_at\":null,\"check_out_lat\":null,\"check_out_lng\":null,\"check_out_photo\":null,\"is_gps_verified\":false,\"distance_from_dealer_meters\":\"16311.93\",\"feedback\":null}}', NULL, '2026-08-27 02:22:30', '2026-08-27 02:22:30'),
(171, 'visits', 'updated', 'App\\Models\\Visit', 'updated', 10, 'App\\Models\\User', 1, '{\"attributes\":{\"check_out_at\":\"2026-08-27T08:22:31.000000Z\",\"check_out_lat\":\"23.8110000\",\"check_out_lng\":\"90.4130000\",\"check_out_photo\":\"visits\\/kGmZbC6j2iSCssuUwYsTo4wyyz6TUreX1Er6VwXx.jpg\",\"feedback\":\"Placed a repeat order.\"},\"old\":{\"check_out_at\":null,\"check_out_lat\":null,\"check_out_lng\":null,\"check_out_photo\":null,\"feedback\":null}}', NULL, '2026-08-27 02:22:31', '2026-08-27 02:22:31'),
(172, 'orders', 'created', 'App\\Models\\Order', 'created', 10, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"retailer_id\":null,\"order_date\":\"2026-08-27\",\"total_amount\":\"42400.00\",\"remarks\":\"Restocked shelf display.\",\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-27 02:22:32', '2026-08-27 02:22:32'),
(173, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 15, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"collection_date\":\"2026-08-27\",\"amount\":\"600.00\",\"payment_method\":\"cash\",\"reference_no\":null,\"cheque_image\":\"collection-entries\\/KPfZNaObQgkn6jl1QNg30hXg0DUsQUod5NJSZ0KH.jpg\",\"cheque_status\":null,\"otp_verified_at\":null,\"remarks\":\"Collected during routine visit.\",\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-27 02:22:34', '2026-08-27 02:22:34'),
(174, 'retailers', 'created', 'App\\Models\\Retailer', 'created', 2, NULL, NULL, '{\"attributes\":{\"dealer_id\":1,\"name\":\"Postman Test Retailer\",\"phone\":\"01711111111\",\"email\":null,\"image\":null,\"shipping_address\":null,\"status\":true}}', NULL, '2026-08-27 02:22:48', '2026-08-27 02:22:48'),
(175, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 16, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":80,\"dealer_id\":5,\"collection_date\":\"2026-08-27\",\"amount\":\"18.00\",\"payment_method\":\"cash\",\"reference_no\":null,\"cheque_image\":null,\"cheque_status\":null,\"otp_verified_at\":null,\"remarks\":null,\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-27 02:44:21', '2026-08-27 02:44:21'),
(176, 'collection_entries', 'deleted', 'App\\Models\\CollectionEntry', 'deleted', 16, 'App\\Models\\User', 1, '{\"old\":{\"user_id\":80,\"dealer_id\":5,\"collection_date\":\"2026-08-27\",\"amount\":\"18.00\",\"payment_method\":\"cash\",\"reference_no\":null,\"cheque_image\":null,\"cheque_status\":null,\"otp_verified_at\":null,\"remarks\":null,\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-27 02:44:45', '2026-08-27 02:44:45'),
(177, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 11, 'App\\Models\\User', 1, '{\"attributes\":{\"dealer_code\":\"DLR-1787820714\",\"name\":\"Karim Traders\",\"phone\":\"01712345678\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":\"23.8103000\",\"gps_lng\":\"90.4125000\",\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true}}', NULL, '2026-08-27 02:51:54', '2026-08-27 02:51:54'),
(178, 'visit_plans', 'created', 'App\\Models\\VisitPlan', 'created', 11, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"territory_id\":null,\"planned_date\":\"2026-08-28\",\"status\":\"planned\",\"notes\":\"Follow up on last order.\"}}', NULL, '2026-08-27 02:51:59', '2026-08-27 02:51:59'),
(179, 'visits', 'created', 'App\\Models\\Visit', 'created', 11, 'App\\Models\\User', 1, '{\"attributes\":{\"visit_plan_id\":null,\"user_id\":1,\"dealer_id\":1,\"check_in_at\":\"2026-08-27T08:52:00.000000Z\",\"check_in_lat\":\"23.8103000\",\"check_in_lng\":\"90.4125000\",\"check_in_photo\":\"visits\\/Zs14BKdhfRf9ZmzIqxWWDkA7ISsAfXpBWu8Tpp2m.jpg\",\"check_out_at\":null,\"check_out_lat\":null,\"check_out_lng\":null,\"check_out_photo\":null,\"is_gps_verified\":false,\"distance_from_dealer_meters\":\"16311.93\",\"feedback\":null}}', NULL, '2026-08-27 02:52:00', '2026-08-27 02:52:00'),
(180, 'visits', 'updated', 'App\\Models\\Visit', 'updated', 11, 'App\\Models\\User', 1, '{\"attributes\":{\"check_out_at\":\"2026-08-27T08:52:01.000000Z\",\"check_out_lat\":\"23.8110000\",\"check_out_lng\":\"90.4130000\",\"check_out_photo\":\"visits\\/FsEuXqvyzvvJRRITONdG1tvzSljqzy0yweMHzFZ8.jpg\",\"feedback\":\"Placed a repeat order.\"},\"old\":{\"check_out_at\":null,\"check_out_lat\":null,\"check_out_lng\":null,\"check_out_photo\":null,\"feedback\":null}}', NULL, '2026-08-27 02:52:01', '2026-08-27 02:52:01'),
(181, 'orders', 'created', 'App\\Models\\Order', 'created', 11, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"retailer_id\":null,\"order_date\":\"2026-08-27\",\"total_amount\":\"42400.00\",\"remarks\":\"Restocked shelf display.\",\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-27 02:52:02', '2026-08-27 02:52:02'),
(182, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 17, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"collection_date\":\"2026-08-27\",\"amount\":\"600.00\",\"payment_method\":\"cash\",\"reference_no\":null,\"cheque_image\":\"collection-entries\\/HYOiNVpOT3MyDEMLWFomPL2hcsu6cFtqH2y3jory.jpg\",\"cheque_status\":null,\"otp_verified_at\":\"2026-08-27T08:52:04.000000Z\",\"remarks\":\"Collected during routine visit.\",\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-27 02:52:04', '2026-08-27 02:52:04'),
(183, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 18, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":80,\"dealer_id\":1,\"collection_date\":\"2026-08-27\",\"amount\":\"500.00\",\"payment_method\":\"cash\",\"reference_no\":null,\"cheque_image\":null,\"cheque_status\":null,\"otp_verified_at\":\"2026-08-27T08:57:01.000000Z\",\"remarks\":null,\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-27 02:57:01', '2026-08-27 02:57:01'),
(184, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 19, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":80,\"dealer_id\":1,\"collection_date\":\"2026-08-27\",\"amount\":\"750.00\",\"payment_method\":\"cash\",\"reference_no\":null,\"cheque_image\":null,\"cheque_status\":null,\"otp_verified_at\":\"2026-08-27T09:12:04.000000Z\",\"remarks\":null,\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-27 03:12:04', '2026-08-27 03:12:04'),
(185, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 20, 'App\\Models\\User', 80, '{\"attributes\":{\"user_id\":80,\"dealer_id\":1,\"collection_date\":\"2026-08-27\",\"amount\":\"850.00\",\"payment_method\":\"cash\",\"reference_no\":null,\"cheque_image\":null,\"cheque_status\":null,\"otp_verified_at\":\"2026-08-27T09:18:03.000000Z\",\"remarks\":null,\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-27 03:18:03', '2026-08-27 03:18:03'),
(186, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 12, 'App\\Models\\User', 1, '{\"attributes\":{\"dealer_code\":\"DLR-1787822324\",\"name\":\"Karim Traders\",\"phone\":\"01712345678\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":\"23.8103000\",\"gps_lng\":\"90.4125000\",\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true}}', NULL, '2026-08-27 03:18:44', '2026-08-27 03:18:44'),
(187, 'visit_plans', 'created', 'App\\Models\\VisitPlan', 'created', 12, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"territory_id\":null,\"planned_date\":\"2026-08-28\",\"status\":\"planned\",\"notes\":\"Follow up on last order.\"}}', NULL, '2026-08-27 03:18:49', '2026-08-27 03:18:49'),
(188, 'visits', 'created', 'App\\Models\\Visit', 'created', 12, 'App\\Models\\User', 1, '{\"attributes\":{\"visit_plan_id\":null,\"user_id\":1,\"dealer_id\":1,\"check_in_at\":\"2026-08-27T09:18:50.000000Z\",\"check_in_lat\":\"23.8103000\",\"check_in_lng\":\"90.4125000\",\"check_in_photo\":\"visits\\/1MSw6ZscRb76bz9OClvA8pC61oX8AOvwUWszodZD.jpg\",\"check_out_at\":null,\"check_out_lat\":null,\"check_out_lng\":null,\"check_out_photo\":null,\"is_gps_verified\":false,\"distance_from_dealer_meters\":\"16311.93\",\"feedback\":null}}', NULL, '2026-08-27 03:18:50', '2026-08-27 03:18:50'),
(189, 'visits', 'updated', 'App\\Models\\Visit', 'updated', 12, 'App\\Models\\User', 1, '{\"attributes\":{\"check_out_at\":\"2026-08-27T09:18:51.000000Z\",\"check_out_lat\":\"23.8110000\",\"check_out_lng\":\"90.4130000\",\"check_out_photo\":\"visits\\/gBiz6B61YT1akYFKYLsfmbQHKw768dSe0ndJ1hgF.jpg\",\"feedback\":\"Placed a repeat order.\"},\"old\":{\"check_out_at\":null,\"check_out_lat\":null,\"check_out_lng\":null,\"check_out_photo\":null,\"feedback\":null}}', NULL, '2026-08-27 03:18:51', '2026-08-27 03:18:51'),
(190, 'orders', 'created', 'App\\Models\\Order', 'created', 12, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"retailer_id\":null,\"order_date\":\"2026-08-27\",\"total_amount\":\"42400.00\",\"remarks\":\"Restocked shelf display.\",\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-27 03:18:52', '2026-08-27 03:18:52'),
(191, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 21, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"collection_date\":\"2026-08-27\",\"amount\":\"600.00\",\"payment_method\":\"cash\",\"reference_no\":null,\"cheque_image\":\"collection-entries\\/IWbyhMXcTOy4QKXiFKizzhu393UCktjTsFObPKS8.jpg\",\"cheque_status\":null,\"otp_verified_at\":\"2026-08-27T09:18:54.000000Z\",\"remarks\":\"Collected during routine visit.\",\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-27 03:18:54', '2026-08-27 03:18:54'),
(192, 'targets', 'updated', 'App\\Models\\Target', 'updated', 3, NULL, NULL, '{\"attributes\":{\"order_value_target\":\"9000.00\",\"quantity_target\":35},\"old\":{\"order_value_target\":\"86946.14\",\"quantity_target\":72}}', NULL, '2026-08-27 03:30:45', '2026-08-27 03:30:45'),
(193, 'orders', 'created', 'App\\Models\\Order', 'created', 13, NULL, NULL, '{\"attributes\":{\"user_id\":80,\"dealer_id\":1,\"retailer_id\":null,\"order_date\":\"2026-06-12\",\"total_amount\":\"3600.00\",\"remarks\":null,\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-27 03:30:45', '2026-08-27 03:30:45'),
(194, 'users', 'updated', 'App\\Models\\User', 'updated', 80, NULL, NULL, '{\"attributes\":{\"password\":\"$2y$12$PSAH4Ypwd7mAE90SApW\\/kO884vnDD3F0sj98n\\/ZqS7NnXXl89Nlh6\"},\"old\":{\"password\":\"$2y$12$hnMF5XzSUmqBoT.X5CFLp.P8BwGlSz8tydRtJDmH6OUqXluxX6VWC\"}}', NULL, '2026-08-27 03:48:48', '2026-08-27 03:48:48'),
(195, 'product_categories', 'created', 'App\\Models\\ProductCategory', 'created', 9, 'App\\Models\\User', 1, '{\"attributes\":{\"name\":\"Gazi Smiss Deep Fryers\",\"code\":\"CAT-SUB-TEST\",\"parent_id\":7,\"description\":null,\"status\":true}}', NULL, '2026-08-27 04:45:59', '2026-08-27 04:45:59'),
(196, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 22, NULL, NULL, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"collection_date\":\"2026-08-30\",\"amount\":\"2500.00\",\"payment_method\":\"cash\",\"reference_no\":null,\"cheque_image\":null,\"cheque_status\":null,\"otp_verified_at\":null,\"remarks\":\"Excepturi qui quis aliquid voluptate voluptates.\",\"status\":\"approved\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-29 22:37:32', '2026-08-29 22:37:32'),
(197, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 23, NULL, NULL, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"collection_date\":\"2026-08-30\",\"amount\":\"1800.00\",\"payment_method\":\"bank_transfer\",\"reference_no\":\"REF-11786EA\",\"cheque_image\":null,\"cheque_status\":null,\"otp_verified_at\":null,\"remarks\":\"Sed velit nostrum consequatur a autem autem.\",\"status\":\"approved\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-29 22:37:32', '2026-08-29 22:37:32'),
(198, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 24, NULL, NULL, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"collection_date\":\"2026-08-30\",\"amount\":\"1200.00\",\"payment_method\":\"cheque\",\"reference_no\":\"REF-90478IY\",\"cheque_image\":null,\"cheque_status\":null,\"otp_verified_at\":null,\"remarks\":\"Et necessitatibus nobis tempora omnis deleniti non.\",\"status\":\"approved\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-29 22:37:32', '2026-08-29 22:37:32'),
(199, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 25, NULL, NULL, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"collection_date\":\"2026-08-30\",\"amount\":\"500.00\",\"payment_method\":\"mobile_banking\",\"reference_no\":\"REF-91118VS\",\"cheque_image\":null,\"cheque_status\":null,\"otp_verified_at\":null,\"remarks\":null,\"status\":\"approved\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-29 22:37:32', '2026-08-29 22:37:32'),
(200, 'orders', 'created', 'App\\Models\\Order', 'created', 14, NULL, NULL, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"retailer_id\":null,\"order_date\":\"2026-08-30\",\"total_amount\":\"15000.00\",\"remarks\":\"Numquam rem minima non deserunt cupiditate voluptatem sed nihil.\",\"status\":\"approved\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-29 22:37:32', '2026-08-29 22:37:32'),
(201, 'collection_entries', 'created', 'App\\Models\\CollectionEntry', 'created', 26, NULL, NULL, '{\"attributes\":{\"user_id\":1,\"dealer_id\":1,\"collection_date\":\"2026-08-30\",\"amount\":\"1111.00\",\"payment_method\":\"cash\",\"reference_no\":\"REF-37374KZ\",\"cheque_image\":null,\"cheque_status\":null,\"otp_verified_at\":null,\"remarks\":\"Illo illo aspernatur totam qui.\",\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-29 22:54:21', '2026-08-29 22:54:21'),
(202, 'collection_entries', 'updated', 'App\\Models\\CollectionEntry', 'updated', 26, 'App\\Models\\User', 1, '{\"attributes\":{\"status\":\"approved\",\"approved_by\":1,\"approved_at\":\"2026-08-30T04:54:39.000000Z\"},\"old\":{\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-29 22:54:39', '2026-08-29 22:54:39'),
(203, 'collection_entries', 'deleted', 'App\\Models\\CollectionEntry', 'deleted', 26, NULL, NULL, '{\"old\":{\"user_id\":1,\"dealer_id\":1,\"collection_date\":\"2026-08-30\",\"amount\":\"1111.00\",\"payment_method\":\"cash\",\"reference_no\":\"REF-37374KZ\",\"cheque_image\":null,\"cheque_status\":null,\"otp_verified_at\":null,\"remarks\":\"Illo illo aspernatur totam qui.\",\"status\":\"approved\",\"approved_by\":1,\"approved_at\":\"2026-08-30T04:54:39.000000Z\"}}', NULL, '2026-08-29 22:55:00', '2026-08-29 22:55:00'),
(204, 'orders', 'updated', 'App\\Models\\Order', 'updated', 6, 'App\\Models\\User', 1, '{\"attributes\":{\"status\":\"approved\",\"approved_by\":1,\"approved_at\":\"2026-08-30T05:04:31.000000Z\"},\"old\":{\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-29 23:04:31', '2026-08-29 23:04:31'),
(205, 'orders', 'updated', 'App\\Models\\Order', 'updated', 7, 'App\\Models\\User', 1, '{\"attributes\":{\"status\":\"rejected\",\"approved_by\":1,\"approved_at\":\"2026-08-30T05:06:08.000000Z\"},\"old\":{\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-29 23:06:08', '2026-08-29 23:06:08'),
(206, 'users', 'updated', 'App\\Models\\User', 'updated', 86, 'App\\Models\\User', 1, '{\"attributes\":{\"status\":false},\"old\":{\"status\":true}}', NULL, '2026-08-29 23:09:25', '2026-08-29 23:09:25'),
(207, 'users', 'updated', 'App\\Models\\User', 'updated', 86, 'App\\Models\\User', 1, '{\"attributes\":{\"status\":true},\"old\":{\"status\":false}}', NULL, '2026-08-29 23:10:51', '2026-08-29 23:10:51'),
(208, 'collection_entries', 'updated', 'App\\Models\\CollectionEntry', 'updated', 10, 'App\\Models\\User', 1, '{\"attributes\":{\"status\":\"approved\",\"approved_by\":1,\"approved_at\":\"2026-08-30T05:48:34.000000Z\"},\"old\":{\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-29 23:48:34', '2026-08-29 23:48:34'),
(209, 'collection_entries', 'updated', 'App\\Models\\CollectionEntry', 'updated', 11, 'App\\Models\\User', 1, '{\"attributes\":{\"status\":\"rejected\",\"approved_by\":1,\"approved_at\":\"2026-08-30T05:48:39.000000Z\"},\"old\":{\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-29 23:48:39', '2026-08-29 23:48:39'),
(210, 'orders', 'updated', 'App\\Models\\Order', 'updated', 8, 'App\\Models\\User', 1, '{\"attributes\":{\"status\":\"approved\",\"approved_by\":1,\"approved_at\":\"2026-08-30T05:48:58.000000Z\"},\"old\":{\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-29 23:48:58', '2026-08-29 23:48:58'),
(211, 'orders', 'updated', 'App\\Models\\Order', 'updated', 9, 'App\\Models\\User', 1, '{\"attributes\":{\"status\":\"rejected\",\"approved_by\":1,\"approved_at\":\"2026-08-30T05:49:02.000000Z\"},\"old\":{\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-29 23:49:02', '2026-08-29 23:49:02'),
(212, 'users', 'updated', 'App\\Models\\User', 'updated', 80, NULL, NULL, '{\"attributes\":{\"password\":\"$2y$12$0PDo6N45znvgavPfkeINwO6kLbp3Xw6ZsSuCzkw47xiyqGDpYDGmS\"},\"old\":{\"password\":\"$2y$12$o9X3YZ1fno7396lCkjj51Ojw5m1DoiHEevsB5Iylz31aBrRnrGBLK\"}}', NULL, '2026-08-30 00:38:46', '2026-08-30 00:38:46'),
(213, 'users', 'created', 'App\\Models\\User', 'created', 87, NULL, NULL, '{\"attributes\":{\"employee_id\":\"TM-TEST-01\",\"name\":\"Test Territory Manager\",\"email\":\"territory.manager.test@gazipump.com\",\"phone\":\"01739165833\",\"photo\":null,\"designation\":\"Sales Executive\",\"date_of_birth\":\"2001-09-21\",\"manager_id\":null,\"sales_team_id\":null,\"status\":true,\"password\":\"$2y$12$bXCDDBYMvR1FuuJeWv4BYOgVaJB9DWY2ZN1KbSzoX0.TfmiC8gQ2m\"}}', NULL, '2026-08-30 00:38:46', '2026-08-30 00:38:46'),
(214, 'users', 'deleted', 'App\\Models\\User', 'deleted', 87, NULL, NULL, '{\"old\":{\"employee_id\":\"TM-TEST-01\",\"name\":\"Test Territory Manager\",\"email\":\"territory.manager.test@gazipump.com\",\"phone\":\"01739165833\",\"photo\":null,\"designation\":\"Sales Executive\",\"date_of_birth\":\"2001-09-21\",\"manager_id\":null,\"sales_team_id\":null,\"status\":true,\"password\":\"$2y$12$bXCDDBYMvR1FuuJeWv4BYOgVaJB9DWY2ZN1KbSzoX0.TfmiC8gQ2m\"}}', NULL, '2026-08-30 00:42:21', '2026-08-30 00:42:21'),
(215, 'users', 'updated', 'App\\Models\\User', 'updated', 80, 'App\\Models\\User', 1, '{\"attributes\":{\"sales_team_id\":1},\"old\":{\"sales_team_id\":null}}', NULL, '2026-08-30 00:55:48', '2026-08-30 00:55:48'),
(216, 'products', 'updated', 'App\\Models\\Product', 'updated', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"sales_team_id\":1},\"old\":{\"sales_team_id\":null}}', NULL, '2026-08-30 00:57:41', '2026-08-30 00:57:41'),
(217, 'products', 'updated', 'App\\Models\\Product', 'updated', 2, 'App\\Models\\User', 1, '{\"attributes\":{\"sales_team_id\":1},\"old\":{\"sales_team_id\":null}}', NULL, '2026-08-30 00:57:47', '2026-08-30 00:57:47'),
(218, 'users', 'created', 'App\\Models\\User', 'created', 88, NULL, NULL, '{\"attributes\":{\"employee_id\":\"TMGR-TEST-01\",\"name\":\"Test Team Manager\",\"email\":\"team.manager.test@gazipump.com\",\"phone\":\"01198306364\",\"photo\":null,\"designation\":\"Sales Executive\",\"date_of_birth\":\"1973-08-30\",\"manager_id\":null,\"sales_team_id\":1,\"status\":true,\"password\":\"$2y$12$SGa6974d7iRtydtB3e81oOhczg0ruGY3Cph5jlHwy6B\\/VD7VGuTPm\"}}', NULL, '2026-08-30 01:15:27', '2026-08-30 01:15:27'),
(219, 'users', 'deleted', 'App\\Models\\User', 'deleted', 88, NULL, NULL, '{\"old\":{\"employee_id\":\"TMGR-TEST-01\",\"name\":\"Test Team Manager\",\"email\":\"team.manager.test@gazipump.com\",\"phone\":\"01198306364\",\"photo\":null,\"designation\":\"Sales Executive\",\"date_of_birth\":\"1973-08-30\",\"manager_id\":null,\"sales_team_id\":1,\"status\":true,\"password\":\"$2y$12$SGa6974d7iRtydtB3e81oOhczg0ruGY3Cph5jlHwy6B\\/VD7VGuTPm\"}}', NULL, '2026-08-30 01:16:50', '2026-08-30 01:16:50'),
(220, 'orders', 'created', 'App\\Models\\Order', 'created', 15, 'App\\Models\\User', 80, '{\"attributes\":{\"user_id\":80,\"dealer_id\":4,\"retailer_id\":null,\"order_date\":\"2026-08-30\",\"total_amount\":\"10.00\",\"remarks\":null,\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-30 03:22:35', '2026-08-30 03:22:35'),
(221, 'users', 'created', 'App\\Models\\User', 'created', 89, NULL, NULL, '{\"attributes\":{\"employee_id\":\"EMP-69451\",\"name\":\"Demond Little\",\"email\":\"theodora.goyette@example.net\",\"phone\":\"01093105911\",\"photo\":null,\"designation\":\"Sales Executive\",\"date_of_birth\":\"1995-02-19\",\"manager_id\":null,\"sales_team_id\":null,\"status\":true,\"password\":\"$2y$12$WPXIL61cWtrAx7cD8JYxS..SKhlSyoyLvNtByV9ZhnDiVRKH1IVxO\"}}', NULL, '2026-08-30 04:52:18', '2026-08-30 04:52:18'),
(222, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 13, NULL, NULL, '{\"attributes\":{\"dealer_code\":\"CUST-85241\",\"name\":\"Tangail Hardware\",\"phone\":\"01166526307\",\"email\":\"dominic67@rutherford.biz\",\"address\":\"337 Stamm Port Apt. 131\\nSouth Keshaunport, RI 18925\",\"image\":null,\"gps_lat\":\"22.0545930\",\"gps_lng\":\"91.0388790\",\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true}}', NULL, '2026-08-30 04:52:19', '2026-08-30 04:52:19'),
(223, 'attendances', 'created', 'App\\Models\\Attendance', 'created', 7, NULL, NULL, '{\"attributes\":{\"user_id\":89,\"date\":\"2026-08-30\",\"check_in_at\":\"2026-08-30T09:00:00.000000Z\",\"check_in_lat\":\"23.8159530\",\"check_in_lng\":\"90.4380430\",\"check_in_photo\":null,\"check_out_at\":\"2026-08-30T17:00:00.000000Z\",\"check_out_lat\":\"23.8911820\",\"check_out_lng\":\"90.4200300\",\"check_out_photo\":null,\"status\":\"present\",\"late_minutes\":0,\"remarks\":null}}', NULL, '2026-08-30 04:52:19', '2026-08-30 04:52:19'),
(224, 'visits', 'created', 'App\\Models\\Visit', 'created', 13, NULL, NULL, '{\"attributes\":{\"visit_plan_id\":null,\"user_id\":89,\"dealer_id\":13,\"check_in_at\":\"2026-08-30T09:15:00.000000Z\",\"check_in_lat\":\"23.7018870\",\"check_in_lng\":\"90.3615190\",\"check_in_photo\":null,\"check_out_at\":\"2026-08-30T09:45:00.000000Z\",\"check_out_lat\":\"23.8360040\",\"check_out_lng\":\"90.4476040\",\"check_out_photo\":null,\"is_gps_verified\":true,\"distance_from_dealer_meters\":\"25.86\",\"feedback\":\"Nihil quas aut impedit adipisci.\"}}', NULL, '2026-08-30 04:52:19', '2026-08-30 04:52:19'),
(225, 'users', 'created', 'App\\Models\\User', 'created', 90, NULL, NULL, '{\"attributes\":{\"employee_id\":\"EMP-96116\",\"name\":\"Domingo Bergnaum\",\"email\":\"cole.noemy@example.net\",\"phone\":\"01001579230\",\"photo\":null,\"designation\":\"Sales Executive\",\"date_of_birth\":\"1988-01-01\",\"manager_id\":null,\"sales_team_id\":null,\"status\":true,\"password\":\"$2y$12$I4Zu.3uJmXMubdO4Qb0MI.n.ngr2W8mILMbW16udwq2\\/vfdwutRaC\"}}', NULL, '2026-08-30 04:53:16', '2026-08-30 04:53:16'),
(226, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 14, NULL, NULL, '{\"attributes\":{\"dealer_code\":\"CUST-13532\",\"name\":\"Tangail Hardware\",\"phone\":\"01575829863\",\"email\":\"gerard.wiegand@balistreri.com\",\"address\":\"88225 Lynch Brooks Suite 251\\nNew Jody, CA 76381\",\"image\":null,\"gps_lat\":\"23.0175250\",\"gps_lng\":\"91.7119790\",\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true}}', NULL, '2026-08-30 04:53:16', '2026-08-30 04:53:16'),
(227, 'attendances', 'created', 'App\\Models\\Attendance', 'created', 8, NULL, NULL, '{\"attributes\":{\"user_id\":90,\"date\":\"2026-08-30\",\"check_in_at\":\"2026-08-30T09:00:00.000000Z\",\"check_in_lat\":\"23.7901490\",\"check_in_lng\":\"90.4300580\",\"check_in_photo\":null,\"check_out_at\":\"2026-08-30T17:00:00.000000Z\",\"check_out_lat\":\"23.6703710\",\"check_out_lng\":\"90.3772890\",\"check_out_photo\":null,\"status\":\"present\",\"late_minutes\":0,\"remarks\":null}}', NULL, '2026-08-30 04:53:16', '2026-08-30 04:53:16'),
(228, 'visits', 'created', 'App\\Models\\Visit', 'created', 14, NULL, NULL, '{\"attributes\":{\"visit_plan_id\":null,\"user_id\":90,\"dealer_id\":14,\"check_in_at\":\"2026-08-30T09:15:00.000000Z\",\"check_in_lat\":\"23.8475340\",\"check_in_lng\":\"90.3982480\",\"check_in_photo\":null,\"check_out_at\":\"2026-08-30T09:45:00.000000Z\",\"check_out_lat\":\"23.7104620\",\"check_out_lng\":\"90.3528880\",\"check_out_photo\":null,\"is_gps_verified\":true,\"distance_from_dealer_meters\":\"51.23\",\"feedback\":\"Qui totam voluptas in et assumenda sint soluta.\"}}', NULL, '2026-08-30 04:53:16', '2026-08-30 04:53:16'),
(229, 'attendances', 'created', 'App\\Models\\Attendance', 'created', 9, NULL, NULL, '{\"attributes\":{\"user_id\":80,\"date\":\"2026-08-30\",\"check_in_at\":\"2026-08-30T09:02:00.000000Z\",\"check_in_lat\":null,\"check_in_lng\":null,\"check_in_photo\":null,\"check_out_at\":\"2026-08-30T18:05:00.000000Z\",\"check_out_lat\":null,\"check_out_lng\":null,\"check_out_photo\":null,\"status\":\"present\",\"late_minutes\":0,\"remarks\":null}}', NULL, '2026-08-30 04:55:32', '2026-08-30 04:55:32'),
(230, 'visits', 'created', 'App\\Models\\Visit', 'created', 15, NULL, NULL, '{\"attributes\":{\"visit_plan_id\":null,\"user_id\":80,\"dealer_id\":1,\"check_in_at\":\"2026-08-30T09:45:00.000000Z\",\"check_in_lat\":\"23.7018120\",\"check_in_lng\":\"90.4319180\",\"check_in_photo\":null,\"check_out_at\":\"2026-08-30T10:10:00.000000Z\",\"check_out_lat\":\"23.7624060\",\"check_out_lng\":\"90.4285720\",\"check_out_photo\":null,\"is_gps_verified\":true,\"distance_from_dealer_meters\":\"20.96\",\"feedback\":\"Nesciunt voluptas voluptas amet ratione in.\"}}', NULL, '2026-08-30 04:55:32', '2026-08-30 04:55:32'),
(231, 'visits', 'created', 'App\\Models\\Visit', 'created', 16, NULL, NULL, '{\"attributes\":{\"visit_plan_id\":null,\"user_id\":80,\"dealer_id\":1,\"check_in_at\":\"2026-08-30T15:00:00.000000Z\",\"check_in_lat\":\"23.8069810\",\"check_in_lng\":\"90.3788490\",\"check_in_photo\":null,\"check_out_at\":\"2026-08-30T16:20:00.000000Z\",\"check_out_lat\":\"23.7055080\",\"check_out_lng\":\"90.4403640\",\"check_out_photo\":null,\"is_gps_verified\":true,\"distance_from_dealer_meters\":\"139.60\",\"feedback\":\"Ut est ipsam ut atque.\"}}', NULL, '2026-08-30 04:55:32', '2026-08-30 04:55:32'),
(232, 'users', 'created', 'App\\Models\\User', 'created', 91, NULL, NULL, '{\"attributes\":{\"employee_id\":\"EMP-87966\",\"name\":\"Darrel Weissnat\",\"email\":\"nasir51@example.com\",\"phone\":\"01536437306\",\"photo\":null,\"designation\":\"Sales Executive\",\"date_of_birth\":\"2004-08-09\",\"manager_id\":null,\"sales_team_id\":null,\"status\":true,\"password\":\"$2y$12$3EzB4sz6qQYTtCUFGxNFDelrRdUwqaK4CSEH9hgJx5yHT8RxZfuzy\"}}', NULL, '2026-08-30 23:52:34', '2026-08-30 23:52:34'),
(233, 'users', 'created', 'App\\Models\\User', 'created', 92, NULL, NULL, '{\"attributes\":{\"employee_id\":\"EMP-48811\",\"name\":\"Sydni Herman\",\"email\":\"vhammes@example.net\",\"phone\":\"01972962789\",\"photo\":null,\"designation\":\"Sales Executive\",\"date_of_birth\":\"1999-01-13\",\"manager_id\":null,\"sales_team_id\":null,\"status\":true,\"password\":\"$2y$12$3EzB4sz6qQYTtCUFGxNFDelrRdUwqaK4CSEH9hgJx5yHT8RxZfuzy\"}}', NULL, '2026-08-30 23:52:34', '2026-08-30 23:52:34'),
(234, 'achievement_entries', 'created', 'App\\Models\\AchievementEntry', 'created', 1, NULL, NULL, '{\"attributes\":{\"user_id\":91,\"entry_date\":\"2026-08-31T00:00:00.000000Z\",\"order_value_achieved\":\"55555.00\",\"collection_achieved\":\"20493.56\",\"quantity_achieved\":4,\"status\":\"approved\",\"approved_by\":92,\"approved_at\":\"2026-08-31T05:52:34.000000Z\",\"notes\":null}}', NULL, '2026-08-30 23:52:34', '2026-08-30 23:52:34'),
(235, 'achievement_entries', 'deleted', 'App\\Models\\AchievementEntry', 'deleted', 1, NULL, NULL, '{\"old\":{\"user_id\":91,\"entry_date\":\"2026-08-31\",\"order_value_achieved\":\"55555.00\",\"collection_achieved\":\"20493.56\",\"quantity_achieved\":4,\"status\":\"approved\",\"approved_by\":92,\"approved_at\":\"2026-08-31T05:52:34.000000Z\",\"notes\":null}}', NULL, '2026-08-31 00:03:36', '2026-08-31 00:03:36'),
(236, 'users', 'created', 'App\\Models\\User', 'created', 93, NULL, NULL, '{\"attributes\":{\"employee_id\":\"EMP-13299\",\"name\":\"Temp GM Verify\",\"email\":\"temp-gm-verify@example.com\",\"phone\":\"01007660042\",\"photo\":null,\"designation\":\"Sales Executive\",\"date_of_birth\":\"1982-06-10\",\"manager_id\":null,\"sales_team_id\":null,\"status\":true,\"password\":\"$2y$12$mLQT2DR\\/ys0taim.kb2KM.ZmqdvAU3GOPg0zDIyBAV1ny0mTT5PV6\"}}', NULL, '2026-08-31 00:06:10', '2026-08-31 00:06:10'),
(237, 'achievement_entries', 'created', 'App\\Models\\AchievementEntry', 'created', 2, 'App\\Models\\User', 93, '{\"attributes\":{\"user_id\":80,\"entry_date\":\"2026-08-31\",\"order_value_achieved\":\"15000.00\",\"collection_achieved\":\"8000.00\",\"quantity_achieved\":25,\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null,\"notes\":null}}', NULL, '2026-08-31 00:19:32', '2026-08-31 00:19:32'),
(238, 'achievement_entries', 'updated', 'App\\Models\\AchievementEntry', 'updated', 2, 'App\\Models\\User', 93, '{\"attributes\":{\"status\":\"approved\",\"approved_by\":93,\"approved_at\":\"2026-08-31T06:20:00.000000Z\"},\"old\":{\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-31 00:20:00', '2026-08-31 00:20:00'),
(239, 'achievement_entries', 'updated', 'App\\Models\\AchievementEntry', 'updated', 2, NULL, NULL, '{\"attributes\":{\"approved_by\":1},\"old\":{\"approved_by\":null}}', NULL, '2026-08-31 00:20:51', '2026-08-31 00:20:51'),
(240, 'achievement_entries', 'created', 'App\\Models\\AchievementEntry', 'created', 3, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":80,\"entry_date\":\"2026-08-30\",\"order_value_achieved\":\"3000.00\",\"collection_achieved\":\"1200.00\",\"quantity_achieved\":15,\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null,\"notes\":null}}', NULL, '2026-08-31 00:22:38', '2026-08-31 00:22:38'),
(241, 'achievement_entries', 'updated', 'App\\Models\\AchievementEntry', 'updated', 3, 'App\\Models\\User', 1, '{\"attributes\":{\"status\":\"approved\",\"approved_by\":1,\"approved_at\":\"2026-08-31T06:23:17.000000Z\"},\"old\":{\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null}}', NULL, '2026-08-31 00:23:17', '2026-08-31 00:23:17'),
(242, 'achievement_entries', 'created', 'App\\Models\\AchievementEntry', 'created', 4, 'App\\Models\\User', 80, '{\"attributes\":{\"user_id\":80,\"entry_date\":\"2026-08-29\",\"order_value_achieved\":\"6000.00\",\"collection_achieved\":\"2500.00\",\"quantity_achieved\":10,\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null,\"notes\":null}}', NULL, '2026-08-31 00:54:16', '2026-08-31 00:54:16'),
(243, 'visit_plans', 'created', 'App\\Models\\VisitPlan', 'created', 13, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":80,\"dealer_id\":4,\"territory_id\":1,\"planned_date\":\"2026-08-31\",\"status\":\"planned\",\"notes\":null}}', NULL, '2026-09-01 05:04:22', '2026-09-01 05:04:22'),
(244, 'visits', 'created', 'App\\Models\\Visit', 'created', 17, 'App\\Models\\User', 1, '{\"attributes\":{\"visit_plan_id\":null,\"user_id\":80,\"dealer_id\":4,\"check_in_at\":\"2026-08-31T17:05:00.000000Z\",\"check_in_lat\":null,\"check_in_lng\":null,\"check_in_photo\":null,\"check_out_at\":\"2026-08-31T17:08:00.000000Z\",\"check_out_lat\":null,\"check_out_lng\":null,\"check_out_photo\":null,\"is_gps_verified\":null,\"distance_from_dealer_meters\":null,\"feedback\":null}}', NULL, '2026-09-01 05:05:45', '2026-09-01 05:05:45'),
(245, 'visit_plans', 'created', 'App\\Models\\VisitPlan', 'created', 14, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":80,\"dealer_id\":4,\"territory_id\":1,\"planned_date\":\"2026-09-01\",\"status\":\"planned\",\"notes\":null}}', NULL, '2026-09-01 05:36:02', '2026-09-01 05:36:02'),
(246, 'visit_plans', 'deleted', 'App\\Models\\VisitPlan', 'deleted', 13, 'App\\Models\\User', 1, '{\"old\":{\"user_id\":80,\"dealer_id\":4,\"territory_id\":1,\"planned_date\":\"2026-08-31\",\"status\":\"planned\",\"notes\":null}}', NULL, '2026-09-01 07:00:33', '2026-09-01 07:00:33'),
(247, 'visit_plans', 'restored', 'App\\Models\\VisitPlan', 'restored', 13, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":80,\"dealer_id\":4,\"territory_id\":1,\"planned_date\":\"2026-08-31\",\"status\":\"planned\",\"notes\":null}}', NULL, '2026-09-01 07:01:03', '2026-09-01 07:01:03'),
(248, 'visit_plans', 'deleted', 'App\\Models\\VisitPlan', 'deleted', 14, 'App\\Models\\User', 1, '{\"old\":{\"user_id\":80,\"dealer_id\":4,\"territory_id\":1,\"planned_date\":\"2026-09-01\",\"status\":\"planned\",\"notes\":null}}', NULL, '2026-09-01 08:33:57', '2026-09-01 08:33:57');
INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES
(249, 'visit_plans', 'restored', 'App\\Models\\VisitPlan', 'restored', 14, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":80,\"dealer_id\":4,\"territory_id\":1,\"planned_date\":\"2026-09-01\",\"status\":\"planned\",\"notes\":null}}', NULL, '2026-09-01 08:34:10', '2026-09-01 08:34:10'),
(250, 'visit_plans', 'deleted', 'App\\Models\\VisitPlan', 'deleted', 14, 'App\\Models\\User', 1, '{\"old\":{\"user_id\":80,\"dealer_id\":4,\"territory_id\":1,\"planned_date\":\"2026-09-01\",\"status\":\"planned\",\"notes\":null}}', NULL, '2026-09-01 08:34:26', '2026-09-01 08:34:26'),
(251, 'visit_plans', 'restored', 'App\\Models\\VisitPlan', 'restored', 14, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":80,\"dealer_id\":4,\"territory_id\":1,\"planned_date\":\"2026-09-01\",\"status\":\"planned\",\"notes\":null}}', NULL, '2026-09-01 08:34:38', '2026-09-01 08:34:38'),
(252, 'visit_plans', 'deleted', 'App\\Models\\VisitPlan', 'deleted', 14, 'App\\Models\\User', 1, '{\"old\":{\"user_id\":80,\"dealer_id\":4,\"territory_id\":1,\"planned_date\":\"2026-09-01\",\"status\":\"planned\",\"notes\":null}}', NULL, '2026-09-01 09:00:00', '2026-09-01 09:00:00'),
(253, 'visit_plans', 'created', 'App\\Models\\VisitPlan', 'created', 15, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":80,\"dealer_id\":4,\"territory_id\":1,\"planned_date\":\"2026-09-01\",\"status\":\"planned\",\"notes\":null}}', NULL, '2026-09-01 09:01:17', '2026-09-01 09:01:17'),
(254, 'visits', 'created', 'App\\Models\\Visit', 'created', 18, 'App\\Models\\User', 1, '{\"attributes\":{\"visit_plan_id\":null,\"user_id\":80,\"dealer_id\":4,\"check_in_at\":\"2026-09-01T09:15:00.000000Z\",\"check_in_lat\":null,\"check_in_lng\":null,\"check_in_photo\":null,\"check_out_at\":\"2026-09-01T09:18:00.000000Z\",\"check_out_lat\":null,\"check_out_lng\":null,\"check_out_photo\":null,\"is_gps_verified\":null,\"distance_from_dealer_meters\":null,\"feedback\":null}}', NULL, '2026-09-01 09:15:10', '2026-09-01 09:15:10'),
(255, 'tally_connections', 'created', 'App\\Models\\TallyConnection', 'created', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"connection_name\":\"GDN Tally (Primary)\",\"tally_company_name\":\"GDN tally\",\"tally_company_guid\":null,\"host\":\"localhost\",\"port\":9000,\"protocol\":\"http\",\"api_format\":\"xml\",\"sync_agent_id\":\"69844c04-b0ea-4b45-9a2f-338aac290762\",\"sync_agent_token\":\"ec8f0a6429181cb26e8318b24fa16b08fde3dda47564629754909fdfce4ca8cf\",\"is_active\":true,\"last_heartbeat_at\":null,\"last_successful_sync_at\":null}}', NULL, '2026-09-07 05:19:46', '2026-09-07 05:19:46'),
(256, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-07T05:19:59.000000Z\",\"last_successful_sync_at\":\"2026-09-07T05:19:59.000000Z\"},\"old\":{\"last_heartbeat_at\":null,\"last_successful_sync_at\":null}}', NULL, '2026-09-07 05:19:59', '2026-09-07 05:19:59'),
(257, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"port\":9999},\"old\":{\"port\":9000}}', NULL, '2026-09-07 07:30:45', '2026-09-07 07:30:45'),
(258, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"port\":9000},\"old\":{\"port\":9999}}', NULL, '2026-09-07 07:31:26', '2026-09-07 07:31:26'),
(259, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"port\":9999},\"old\":{\"port\":9000}}', NULL, '2026-09-07 07:34:02', '2026-09-07 07:34:02'),
(260, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"tally_company_name\":\"GDN Tally\"},\"old\":{\"tally_company_name\":\"GDN tally\"}}', NULL, '2026-09-07 07:36:53', '2026-09-07 07:36:53'),
(261, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"port\":9000},\"old\":{\"port\":9999}}', NULL, '2026-09-07 07:52:20', '2026-09-07 07:52:20'),
(262, 'dealers', 'deleted', 'App\\Models\\Dealer', 'deleted', 11, 'App\\Models\\User', 1, '{\"old\":{\"dealer_code\":\"DLR-1787820714\",\"name\":\"Karim Traders\",\"phone\":\"01712345678\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":\"23.8103000\",\"gps_lng\":\"90.4125000\",\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true,\"tally_guid\":null,\"tally_ledger_name\":null}}', NULL, '2026-09-07 10:26:01', '2026-09-07 10:26:01'),
(263, 'dealers', 'deleted', 'App\\Models\\Dealer', 'deleted', 13, 'App\\Models\\User', 1, '{\"old\":{\"dealer_code\":\"CUST-85241\",\"name\":\"Tangail Hardware\",\"phone\":\"01166526307\",\"email\":\"dominic67@rutherford.biz\",\"address\":\"337 Stamm Port Apt. 131\\nSouth Keshaunport, RI 18925\",\"image\":null,\"gps_lat\":\"22.0545930\",\"gps_lng\":\"91.0388790\",\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true,\"tally_guid\":null,\"tally_ledger_name\":null}}', NULL, '2026-09-07 10:28:16', '2026-09-07 10:28:16'),
(264, 'dealers', 'deleted', 'App\\Models\\Dealer', 'deleted', 12, 'App\\Models\\User', 1, '{\"old\":{\"dealer_code\":\"DLR-1787822324\",\"name\":\"Karim Traders\",\"phone\":\"01712345678\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":\"23.8103000\",\"gps_lng\":\"90.4125000\",\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true,\"tally_guid\":null,\"tally_ledger_name\":null}}', NULL, '2026-09-07 10:29:42', '2026-09-07 10:29:42'),
(265, 'dealers', 'deleted', 'App\\Models\\Dealer', 'deleted', 10, 'App\\Models\\User', 1, '{\"old\":{\"dealer_code\":\"DLR-1787818944\",\"name\":\"Karim Traders\",\"phone\":\"01712345678\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":\"23.8103000\",\"gps_lng\":\"90.4125000\",\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true,\"tally_guid\":null,\"tally_ledger_name\":null}}', NULL, '2026-09-07 10:29:46', '2026-09-07 10:29:46'),
(266, 'dealers', 'deleted', 'App\\Models\\Dealer', 'deleted', 9, 'App\\Models\\User', 1, '{\"old\":{\"dealer_code\":\"DLR-1787818909\",\"name\":\"Karim Traders\",\"phone\":\"01712345678\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":\"23.8103000\",\"gps_lng\":\"90.4125000\",\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true,\"tally_guid\":null,\"tally_ledger_name\":null}}', NULL, '2026-09-07 10:29:49', '2026-09-07 10:29:49'),
(267, 'dealers', 'deleted', 'App\\Models\\Dealer', 'deleted', 7, 'App\\Models\\User', 1, '{\"old\":{\"dealer_code\":\"DLR-1787816980\",\"name\":\"Karim Traders\",\"phone\":\"01712345678\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":\"23.8103000\",\"gps_lng\":\"90.4125000\",\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true,\"tally_guid\":null,\"tally_ledger_name\":null}}', NULL, '2026-09-07 10:29:53', '2026-09-07 10:29:53'),
(268, 'dealers', 'deleted', 'App\\Models\\Dealer', 'deleted', 6, 'App\\Models\\User', 1, '{\"old\":{\"dealer_code\":\"DLR-1787816922\",\"name\":\"Karim Traders\",\"phone\":\"01712345678\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":\"23.8103000\",\"gps_lng\":\"90.4125000\",\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true,\"tally_guid\":null,\"tally_ledger_name\":null}}', NULL, '2026-09-07 10:29:56', '2026-09-07 10:29:56'),
(269, 'dealers', 'deleted', 'App\\Models\\Dealer', 'deleted', 8, 'App\\Models\\User', 1, '{\"old\":{\"dealer_code\":\"DLR-1787817010\",\"name\":\"Karim Traders\",\"phone\":\"01712345678\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":\"23.8103000\",\"gps_lng\":\"90.4125000\",\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true,\"tally_guid\":null,\"tally_ledger_name\":null}}', NULL, '2026-09-07 10:30:04', '2026-09-07 10:30:04'),
(270, 'depots', 'created', 'App\\Models\\Depot', 'created', 1, NULL, NULL, '{\"attributes\":{\"name\":\"Main Depot\",\"code\":\"DEP-MAIN\",\"address\":null,\"territory_id\":null,\"tally_guid\":null,\"status\":true}}', NULL, '2026-09-07 11:23:44', '2026-09-07 11:23:44'),
(271, 'products', 'updated', 'App\\Models\\Product', 'updated', 1, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d2\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-07 11:23:44', '2026-09-07 11:23:44'),
(272, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 15, NULL, NULL, '{\"attributes\":{\"dealer_code\":\"TLY-D-162970\",\"name\":\"Dealer 1\",\"phone\":\"0000000000\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":false,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\",\"tally_ledger_name\":null}}', NULL, '2026-09-07 12:00:16', '2026-09-07 12:00:16'),
(273, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 1, NULL, NULL, '{\"attributes\":{\"entity_type\":\"dealer\",\"sfa_id\":15,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\",\"tally_name\":\"Dealer 1\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-07T12:00:16.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-07 12:00:16', '2026-09-07 12:00:16'),
(274, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 16, NULL, NULL, '{\"attributes\":{\"dealer_code\":\"TLY-D-734460\",\"name\":\"Dealer 2\",\"phone\":\"0000000000\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":false,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\",\"tally_ledger_name\":null}}', NULL, '2026-09-07 12:00:16', '2026-09-07 12:00:16'),
(275, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 2, NULL, NULL, '{\"attributes\":{\"entity_type\":\"dealer\",\"sfa_id\":16,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\",\"tally_name\":\"Dealer 2\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-07T12:00:16.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-07 12:00:16', '2026-09-07 12:00:16'),
(276, 'retailers', 'created', 'App\\Models\\Retailer', 'created', 3, NULL, NULL, '{\"attributes\":{\"dealer_id\":1,\"name\":\"Dealer 1\",\"phone\":\"0000000000\",\"email\":null,\"image\":null,\"shipping_address\":null,\"status\":false,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\"}}', NULL, '2026-09-07 12:00:17', '2026-09-07 12:00:17'),
(277, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 3, NULL, NULL, '{\"attributes\":{\"entity_type\":\"retailer\",\"sfa_id\":3,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\",\"tally_name\":\"Dealer 1\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-07T12:00:17.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-07 12:00:17', '2026-09-07 12:00:17'),
(278, 'retailers', 'created', 'App\\Models\\Retailer', 'created', 4, NULL, NULL, '{\"attributes\":{\"dealer_id\":1,\"name\":\"Dealer 2\",\"phone\":\"0000000000\",\"email\":null,\"image\":null,\"shipping_address\":null,\"status\":false,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\"}}', NULL, '2026-09-07 12:00:17', '2026-09-07 12:00:17'),
(279, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 4, NULL, NULL, '{\"attributes\":{\"entity_type\":\"retailer\",\"sfa_id\":4,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\",\"tally_name\":\"Dealer 2\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-07T12:00:17.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-07 12:00:17', '2026-09-07 12:00:17'),
(280, 'products', 'created', 'App\\Models\\Product', 'created', 42, NULL, NULL, '{\"attributes\":{\"category_id\":1,\"sales_team_id\":null,\"name\":\"Pump Model 1\",\"sku\":\"TLY-P-566457\",\"price\":\"0.00\",\"description\":null,\"image\":null,\"status\":false,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d2\"}}', NULL, '2026-09-07 12:00:17', '2026-09-07 12:00:17'),
(281, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 5, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":42,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d2\",\"tally_name\":\"Pump Model 1\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-07T12:00:17.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-07 12:00:17', '2026-09-07 12:00:17'),
(282, 'products', 'created', 'App\\Models\\Product', 'created', 43, NULL, NULL, '{\"attributes\":{\"category_id\":1,\"sales_team_id\":null,\"name\":\"TV Model 1\",\"sku\":\"TLY-P-243999\",\"price\":\"0.00\",\"description\":null,\"image\":null,\"status\":false,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d3\"}}', NULL, '2026-09-07 12:00:17', '2026-09-07 12:00:17'),
(283, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 6, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":43,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d3\",\"tally_name\":\"TV Model 1\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-07T12:00:17.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-07 12:00:17', '2026-09-07 12:00:17'),
(284, 'depots', 'created', 'App\\Models\\Depot', 'created', 2, NULL, NULL, '{\"attributes\":{\"name\":\"Main Location\",\"code\":\"TLY-DEP-508358\",\"address\":null,\"territory_id\":null,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000063\",\"status\":false}}', NULL, '2026-09-07 12:00:17', '2026-09-07 12:00:17'),
(285, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 7, NULL, NULL, '{\"attributes\":{\"entity_type\":\"depot\",\"sfa_id\":2,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000063\",\"tally_name\":\"Main Location\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-07T12:00:17.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-07 12:00:17', '2026-09-07 12:00:17'),
(286, 'depots', 'created', 'App\\Models\\Depot', 'created', 3, NULL, NULL, '{\"attributes\":{\"name\":\"Warehouse\",\"code\":\"TLY-DEP-697660\",\"address\":null,\"territory_id\":null,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d4\",\"status\":false}}', NULL, '2026-09-07 12:00:17', '2026-09-07 12:00:17'),
(287, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 8, NULL, NULL, '{\"attributes\":{\"entity_type\":\"depot\",\"sfa_id\":3,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d4\",\"tally_name\":\"Warehouse\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-07T12:00:17.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-07 12:00:17', '2026-09-07 12:00:17'),
(289, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 18, NULL, NULL, '{\"attributes\":{\"dealer_code\":\"TLY-D-537415\",\"name\":\"Dealer 1\",\"phone\":\"0000000000\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":false,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\",\"tally_ledger_name\":null}}', NULL, '2026-09-08 03:53:13', '2026-09-08 03:53:13'),
(290, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 10, NULL, NULL, '{\"attributes\":{\"entity_type\":\"dealer\",\"sfa_id\":18,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\",\"tally_name\":\"Dealer 1\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T03:53:13.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 03:53:13', '2026-09-08 03:53:13'),
(291, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 19, NULL, NULL, '{\"attributes\":{\"dealer_code\":\"TLY-D-199449\",\"name\":\"Dealer 2\",\"phone\":\"0000000000\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":false,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\",\"tally_ledger_name\":null}}', NULL, '2026-09-08 03:53:13', '2026-09-08 03:53:13'),
(292, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 11, NULL, NULL, '{\"attributes\":{\"entity_type\":\"dealer\",\"sfa_id\":19,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\",\"tally_name\":\"Dealer 2\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T03:53:13.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 03:53:13', '2026-09-08 03:53:13'),
(293, 'products', 'created', 'App\\Models\\Product', 'created', 44, NULL, NULL, '{\"attributes\":{\"category_id\":1,\"sales_team_id\":null,\"name\":\"Pump Model 1\",\"sku\":\"TLY-P-725592\",\"price\":\"0.00\",\"description\":null,\"image\":null,\"status\":false,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d2\"}}', NULL, '2026-09-08 03:53:14', '2026-09-08 03:53:14'),
(294, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 12, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":44,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d2\",\"tally_name\":\"Pump Model 1\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T03:53:14.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 03:53:14', '2026-09-08 03:53:14'),
(295, 'products', 'created', 'App\\Models\\Product', 'created', 45, NULL, NULL, '{\"attributes\":{\"category_id\":1,\"sales_team_id\":null,\"name\":\"TV Model 1\",\"sku\":\"TLY-P-206227\",\"price\":\"0.00\",\"description\":null,\"image\":null,\"status\":false,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d3\"}}', NULL, '2026-09-08 03:53:14', '2026-09-08 03:53:14'),
(296, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 13, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":45,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d3\",\"tally_name\":\"TV Model 1\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T03:53:14.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 03:53:14', '2026-09-08 03:53:14'),
(297, 'depots', 'created', 'App\\Models\\Depot', 'created', 4, NULL, NULL, '{\"attributes\":{\"name\":\"Main Location\",\"code\":\"TLY-DEP-943921\",\"address\":null,\"territory_id\":null,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000063\",\"status\":false}}', NULL, '2026-09-08 03:53:14', '2026-09-08 03:53:14'),
(298, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 14, NULL, NULL, '{\"attributes\":{\"entity_type\":\"depot\",\"sfa_id\":4,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000063\",\"tally_name\":\"Main Location\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T03:53:14.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 03:53:14', '2026-09-08 03:53:14'),
(299, 'depots', 'created', 'App\\Models\\Depot', 'created', 5, NULL, NULL, '{\"attributes\":{\"name\":\"Warehouse\",\"code\":\"TLY-DEP-927918\",\"address\":null,\"territory_id\":null,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d4\",\"status\":false}}', NULL, '2026-09-08 03:53:14', '2026-09-08 03:53:14'),
(300, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 15, NULL, NULL, '{\"attributes\":{\"entity_type\":\"depot\",\"sfa_id\":5,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d4\",\"tally_name\":\"Warehouse\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T03:53:14.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 03:53:14', '2026-09-08 03:53:14'),
(301, 'depots', 'updated', 'App\\Models\\Depot', 'updated', 1, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000da\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:36:51', '2026-09-08 04:36:51'),
(302, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 16, NULL, NULL, '{\"attributes\":{\"entity_type\":\"depot\",\"sfa_id\":1,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000da\",\"tally_name\":\"Main Depot\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:36:51.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:36:51', '2026-09-08 04:36:51'),
(303, 'dealers', 'updated', 'App\\Models\\Dealer', 'updated', 1, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:36:51', '2026-09-08 04:36:51'),
(304, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 17, NULL, NULL, '{\"attributes\":{\"entity_type\":\"dealer\",\"sfa_id\":1,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\",\"tally_name\":\"Savar Pump House\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:36:51.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:36:51', '2026-09-08 04:36:51'),
(305, 'retailers', 'deleted', 'App\\Models\\Retailer', 'deleted', 2, NULL, NULL, '{\"old\":{\"dealer_id\":1,\"name\":\"Postman Test Retailer\",\"phone\":\"01711111111\",\"email\":null,\"image\":null,\"shipping_address\":null,\"status\":true,\"tally_guid\":null}}', NULL, '2026-09-08 04:57:21', '2026-09-08 04:57:21'),
(306, 'dealers', 'updated', 'App\\Models\\Dealer', 'updated', 2, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:57:49', '2026-09-08 04:57:49'),
(307, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 18, NULL, NULL, '{\"attributes\":{\"entity_type\":\"dealer\",\"sfa_id\":2,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\",\"tally_name\":\"Keraniganj Hardware & Motors\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:57:49.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:57:49', '2026-09-08 04:57:49'),
(308, 'dealers', 'updated', 'App\\Models\\Dealer', 'updated', 3, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:57:50', '2026-09-08 04:57:50'),
(309, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 19, NULL, NULL, '{\"attributes\":{\"entity_type\":\"dealer\",\"sfa_id\":3,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\",\"tally_name\":\"Dhamrai Water Solutions\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:57:50.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:57:50', '2026-09-08 04:57:50'),
(310, 'dealers', 'updated', 'App\\Models\\Dealer', 'updated', 4, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:57:50', '2026-09-08 04:57:50'),
(311, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 20, NULL, NULL, '{\"attributes\":{\"entity_type\":\"dealer\",\"sfa_id\":4,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\",\"tally_name\":\"Gazi Appliance Corner\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:57:50.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:57:50', '2026-09-08 04:57:50'),
(312, 'dealers', 'updated', 'App\\Models\\Dealer', 'updated', 5, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:57:50', '2026-09-08 04:57:50'),
(313, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 21, NULL, NULL, '{\"attributes\":{\"entity_type\":\"dealer\",\"sfa_id\":5,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\",\"tally_name\":\"Buriganga Distribution House\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:57:50.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:57:50', '2026-09-08 04:57:50'),
(314, 'dealers', 'updated', 'App\\Models\\Dealer', 'updated', 14, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:57:51', '2026-09-08 04:57:51'),
(315, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 22, NULL, NULL, '{\"attributes\":{\"entity_type\":\"dealer\",\"sfa_id\":14,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\",\"tally_name\":\"Tangail Hardware\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:57:51.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:57:51', '2026-09-08 04:57:51'),
(316, 'products', 'updated', 'App\\Models\\Product', 'updated', 1, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e1\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:57:51', '2026-09-08 04:57:51'),
(317, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 23, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":1,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e1\",\"tally_name\":\"Gazi Self-Priming Jet Pump\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:57:51.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:57:51', '2026-09-08 04:57:51'),
(318, 'products', 'updated', 'App\\Models\\Product', 'updated', 2, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e2\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:57:52', '2026-09-08 04:57:52'),
(319, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 24, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":2,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e2\",\"tally_name\":\"Gazi Standardized Centrifugal Pump\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:57:52.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:57:52', '2026-09-08 04:57:52'),
(320, 'products', 'updated', 'App\\Models\\Product', 'updated', 3, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e3\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:57:52', '2026-09-08 04:57:52'),
(321, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 25, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":3,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e3\",\"tally_name\":\"Pentax Centrifugal Pump\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:57:52.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:57:52', '2026-09-08 04:57:52'),
(322, 'products', 'updated', 'App\\Models\\Product', 'updated', 4, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e4\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:57:53', '2026-09-08 04:57:53'),
(323, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 26, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":4,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e4\",\"tally_name\":\"Pentax Submersible Pump\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:57:53.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:57:53', '2026-09-08 04:57:53'),
(324, 'products', 'updated', 'App\\Models\\Product', 'updated', 5, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e5\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:57:53', '2026-09-08 04:57:53'),
(325, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 27, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":5,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e5\",\"tally_name\":\"Eifel EA Series Pump\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:57:53.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:57:53', '2026-09-08 04:57:53'),
(326, 'products', 'updated', 'App\\Models\\Product', 'updated', 6, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e6\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:57:54', '2026-09-08 04:57:54'),
(327, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 28, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":6,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e6\",\"tally_name\":\"Eifel EAD Series Pump\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:57:54.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:57:54', '2026-09-08 04:57:54'),
(328, 'products', 'updated', 'App\\Models\\Product', 'updated', 7, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e7\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:57:55', '2026-09-08 04:57:55'),
(329, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 29, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":7,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e7\",\"tally_name\":\"CNP CDLF Series Pump\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:57:55.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:57:55', '2026-09-08 04:57:55'),
(330, 'products', 'updated', 'App\\Models\\Product', 'updated', 8, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e8\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:57:55', '2026-09-08 04:57:55'),
(331, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 30, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":8,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e8\",\"tally_name\":\"CNP SZ Series Pump\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:57:55.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:57:55', '2026-09-08 04:57:55'),
(332, 'products', 'updated', 'App\\Models\\Product', 'updated', 9, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e9\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:57:56', '2026-09-08 04:57:56'),
(333, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 31, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":9,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e9\",\"tally_name\":\"Gazi Fire Fighting Pump Complete Set\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:57:56.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:57:56', '2026-09-08 04:57:56'),
(334, 'products', 'updated', 'App\\Models\\Product', 'updated', 10, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ea\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:57:56', '2026-09-08 04:57:56'),
(335, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 32, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":10,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ea\",\"tally_name\":\"Gazi Motors YC Series\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:57:56.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:57:56', '2026-09-08 04:57:56'),
(336, 'products', 'updated', 'App\\Models\\Product', 'updated', 11, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000eb\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:57:57', '2026-09-08 04:57:57'),
(337, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 33, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":11,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000eb\",\"tally_name\":\"Gazi Gas Stove (Industrial Line)\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:57:57.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:57:57', '2026-09-08 04:57:57'),
(338, 'products', 'updated', 'App\\Models\\Product', 'updated', 12, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ec\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:57:57', '2026-09-08 04:57:57'),
(339, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 34, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":12,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ec\",\"tally_name\":\"Gazi Tubewell\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:57:57.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:57:57', '2026-09-08 04:57:57'),
(340, 'products', 'updated', 'App\\Models\\Product', 'updated', 13, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ed\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:57:58', '2026-09-08 04:57:58'),
(341, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 35, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":13,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ed\",\"tally_name\":\"TG-206 - Gazi Smiss Gas Stove\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:57:58.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:57:58', '2026-09-08 04:57:58'),
(342, 'products', 'updated', 'App\\Models\\Product', 'updated', 14, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ee\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:57:58', '2026-09-08 04:57:58'),
(343, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 36, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":14,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ee\",\"tally_name\":\"GST-102C - Gazi Gas Stove\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:57:58.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:57:58', '2026-09-08 04:57:58'),
(344, 'products', 'updated', 'App\\Models\\Product', 'updated', 15, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ef\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:57:58', '2026-09-08 04:57:58'),
(345, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 37, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":15,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ef\",\"tally_name\":\"EG-732S - Gazi Smiss Gas Stove\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:57:58.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:57:58', '2026-09-08 04:57:58'),
(346, 'products', 'updated', 'App\\Models\\Product', 'updated', 16, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f0\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:57:59', '2026-09-08 04:57:59'),
(347, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 38, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":16,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f0\",\"tally_name\":\"TG-213S - Gazi Smiss Gas Stove\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:57:59.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:57:59', '2026-09-08 04:57:59'),
(348, 'products', 'updated', 'App\\Models\\Product', 'updated', 17, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f1\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:58:00', '2026-09-08 04:58:00'),
(349, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 39, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":17,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f1\",\"tally_name\":\"GH-8204M - Gazi Smiss Gas Stove\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:58:00.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:58:00', '2026-09-08 04:58:00'),
(350, 'products', 'updated', 'App\\Models\\Product', 'updated', 18, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f2\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:58:00', '2026-09-08 04:58:00'),
(351, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 40, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":18,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f2\",\"tally_name\":\"HY-716BV - Gazi Smiss Kitchen Hood\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:58:00.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:58:00', '2026-09-08 04:58:00'),
(352, 'products', 'updated', 'App\\Models\\Product', 'updated', 19, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f3\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:58:01', '2026-09-08 04:58:01'),
(353, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 41, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":19,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f3\",\"tally_name\":\"HY-712BT - Gazi Smiss Kitchen Hood\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:58:01.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:58:01', '2026-09-08 04:58:01'),
(354, 'products', 'updated', 'App\\Models\\Product', 'updated', 20, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f4\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:58:01', '2026-09-08 04:58:01'),
(355, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 42, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":20,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f4\",\"tally_name\":\"EG-750S - Gazi Smiss Kitchen Hood\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:58:01.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:58:01', '2026-09-08 04:58:01'),
(356, 'products', 'updated', 'App\\Models\\Product', 'updated', 21, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f5\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:58:01', '2026-09-08 04:58:01'),
(357, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 43, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":21,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f5\",\"tally_name\":\"HY-736BV - Gazi Smiss Kitchen Hood\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:58:01.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:58:01', '2026-09-08 04:58:01'),
(358, 'products', 'updated', 'App\\Models\\Product', 'updated', 22, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f6\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:58:02', '2026-09-08 04:58:02'),
(359, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 44, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":22,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f6\",\"tally_name\":\"HY-729CP - Gazi Smiss Kitchen Hood\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:58:02.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:58:02', '2026-09-08 04:58:02'),
(360, 'products', 'updated', 'App\\Models\\Product', 'updated', 23, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f7\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:58:02', '2026-09-08 04:58:02'),
(361, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 45, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":23,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f7\",\"tally_name\":\"IF-HL01 - Gazi Smiss Infrared Cooker\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:58:02.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:58:02', '2026-09-08 04:58:02'),
(362, 'products', 'updated', 'App\\Models\\Product', 'updated', 24, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f8\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:58:03', '2026-09-08 04:58:03'),
(363, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 46, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":24,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f8\",\"tally_name\":\"A-40G - Gazi Smiss Infrared Cooker\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:58:03.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:58:03', '2026-09-08 04:58:03'),
(364, 'products', 'updated', 'App\\Models\\Product', 'updated', 25, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f9\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:58:03', '2026-09-08 04:58:03'),
(365, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 47, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":25,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f9\",\"tally_name\":\"A-25S - Gazi Smiss Induction Cooker\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:58:03.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:58:03', '2026-09-08 04:58:03'),
(366, 'products', 'updated', 'App\\Models\\Product', 'updated', 26, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fa\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:58:04', '2026-09-08 04:58:04'),
(367, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 48, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":26,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fa\",\"tally_name\":\"A-37G - Gazi Smiss Infrared Cooker\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:58:04.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:58:04', '2026-09-08 04:58:04'),
(368, 'products', 'updated', 'App\\Models\\Product', 'updated', 27, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fb\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:58:04', '2026-09-08 04:58:04'),
(369, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 49, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":27,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fb\",\"tally_name\":\"E-720B - Gazi Smiss Induction & Infrared Cooker\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:58:04.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:58:04', '2026-09-08 04:58:04'),
(370, 'products', 'updated', 'App\\Models\\Product', 'updated', 28, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fc\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:58:05', '2026-09-08 04:58:05'),
(371, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 50, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":28,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fc\",\"tally_name\":\"A-01 - Gazi Smiss Infrared Cooker\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:58:05.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:58:05', '2026-09-08 04:58:05'),
(372, 'products', 'updated', 'App\\Models\\Product', 'updated', 29, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fd\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:58:05', '2026-09-08 04:58:05'),
(373, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 51, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":29,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fd\",\"tally_name\":\"GEO-03 - Gazi Smiss Electric Oven 30 Liter\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:58:05.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:58:05', '2026-09-08 04:58:05'),
(374, 'products', 'updated', 'App\\Models\\Product', 'updated', 30, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fe\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:58:06', '2026-09-08 04:58:06'),
(375, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 52, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":30,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fe\",\"tally_name\":\"GEO-04 - Gazi Smiss Electric Oven 40 Liter\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:58:06.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:58:06', '2026-09-08 04:58:06'),
(376, 'products', 'updated', 'App\\Models\\Product', 'updated', 31, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ff\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:58:06', '2026-09-08 04:58:06'),
(377, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 53, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":31,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ff\",\"tally_name\":\"GEO-05 - Gazi Smiss Electric Oven 50 Liter\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:58:06.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:58:06', '2026-09-08 04:58:06'),
(378, 'products', 'updated', 'App\\Models\\Product', 'updated', 32, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000100\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:58:07', '2026-09-08 04:58:07'),
(379, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 54, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":32,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000100\",\"tally_name\":\"GA-AF-23 - Gazi Smiss Air Fryer\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:58:07.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:58:07', '2026-09-08 04:58:07'),
(380, 'products', 'updated', 'App\\Models\\Product', 'updated', 33, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000101\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:58:07', '2026-09-08 04:58:07'),
(381, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 55, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":33,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000101\",\"tally_name\":\"GA-AF-25 - Gazi Smiss Air Fryer\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:58:07.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:58:07', '2026-09-08 04:58:07'),
(382, 'products', 'updated', 'App\\Models\\Product', 'updated', 34, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000102\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:58:08', '2026-09-08 04:58:08'),
(383, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 56, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":34,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000102\",\"tally_name\":\"GA-AF-27 - Gazi Smiss Air Fryer\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:58:08.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:58:08', '2026-09-08 04:58:08'),
(384, 'products', 'updated', 'App\\Models\\Product', 'updated', 35, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000103\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:58:08', '2026-09-08 04:58:08'),
(385, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 57, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":35,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000103\",\"tally_name\":\"15.0 HP Y2 Motor 950 RPM\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:58:08.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:58:08', '2026-09-08 04:58:08'),
(386, 'products', 'updated', 'App\\Models\\Product', 'updated', 36, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000104\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:58:09', '2026-09-08 04:58:09'),
(387, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 58, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":36,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000104\",\"tally_name\":\"10.0 HP Y2 Motor 2800 RPM\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:58:09.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:58:09', '2026-09-08 04:58:09'),
(388, 'products', 'updated', 'App\\Models\\Product', 'updated', 37, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000105\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:58:09', '2026-09-08 04:58:09'),
(389, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 59, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":37,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000105\",\"tally_name\":\"5.5 HP Y2 Motor 2800 RPM\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:58:09.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:58:09', '2026-09-08 04:58:09'),
(390, 'products', 'updated', 'App\\Models\\Product', 'updated', 38, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000106\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:58:10', '2026-09-08 04:58:10');
INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES
(391, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 60, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":38,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000106\",\"tally_name\":\"3 HP YC Motor 1450 RPM\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:58:10.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:58:10', '2026-09-08 04:58:10'),
(392, 'products', 'updated', 'App\\Models\\Product', 'updated', 39, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000107\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:58:10', '2026-09-08 04:58:10'),
(393, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 61, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":39,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000107\",\"tally_name\":\"1.0 HP Y2 Motor 2800 RPM\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:58:10.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:58:10', '2026-09-08 04:58:10'),
(394, 'products', 'updated', 'App\\Models\\Product', 'updated', 40, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000108\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 04:58:11', '2026-09-08 04:58:11'),
(395, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 62, NULL, NULL, '{\"attributes\":{\"entity_type\":\"product\",\"sfa_id\":40,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000108\",\"tally_name\":\"0.75 HP YC Motor 1450 RPM (Classic)\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T04:58:11.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 04:58:11', '2026-09-08 04:58:11'),
(396, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 20, 'App\\Models\\User', 1, '{\"attributes\":{\"dealer_code\":\"DLR-0006\",\"name\":\"Korim\",\"phone\":\"010000000\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true,\"tally_guid\":null,\"tally_ledger_name\":null}}', NULL, '2026-09-08 05:45:48', '2026-09-08 05:45:48'),
(397, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 21, NULL, NULL, '{\"attributes\":{\"dealer_code\":\"TLY-D-032746\",\"name\":\"Rohim Dealer\",\"phone\":\"0000000000\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":false,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000109\",\"tally_ledger_name\":null}}', NULL, '2026-09-08 05:57:22', '2026-09-08 05:57:22'),
(398, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 63, NULL, NULL, '{\"attributes\":{\"entity_type\":\"dealer\",\"sfa_id\":21,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000109\",\"tally_name\":\"Rohim Dealer\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T05:57:22.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 05:57:22', '2026-09-08 05:57:22'),
(399, 'dealers', 'updated', 'App\\Models\\Dealer', 'updated', 20, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010a\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 05:57:27', '2026-09-08 05:57:27'),
(400, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 64, NULL, NULL, '{\"attributes\":{\"entity_type\":\"dealer\",\"sfa_id\":20,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010a\",\"tally_name\":\"Korim\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T05:57:27.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 05:57:27', '2026-09-08 05:57:27'),
(401, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T05:58:11.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-07T05:19:59.000000Z\"}}', NULL, '2026-09-08 05:58:11', '2026-09-08 05:58:11'),
(402, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_successful_sync_at\":\"2026-09-08T05:58:12.000000Z\"},\"old\":{\"last_successful_sync_at\":\"2026-09-07T05:19:59.000000Z\"}}', NULL, '2026-09-08 05:58:12', '2026-09-08 05:58:12'),
(403, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T05:59:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T05:58:11.000000Z\"}}', NULL, '2026-09-08 05:59:12', '2026-09-08 05:59:12'),
(404, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:00:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T05:59:12.000000Z\"}}', NULL, '2026-09-08 06:00:12', '2026-09-08 06:00:12'),
(405, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:01:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:00:12.000000Z\"}}', NULL, '2026-09-08 06:01:12', '2026-09-08 06:01:12'),
(406, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:02:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:01:12.000000Z\"}}', NULL, '2026-09-08 06:02:12', '2026-09-08 06:02:12'),
(407, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:03:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:02:12.000000Z\"}}', NULL, '2026-09-08 06:03:12', '2026-09-08 06:03:12'),
(408, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:04:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:03:12.000000Z\"}}', NULL, '2026-09-08 06:04:12', '2026-09-08 06:04:12'),
(409, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:05:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:04:12.000000Z\"}}', NULL, '2026-09-08 06:05:12', '2026-09-08 06:05:12'),
(410, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:06:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:05:12.000000Z\"}}', NULL, '2026-09-08 06:06:12', '2026-09-08 06:06:12'),
(411, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:07:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:06:12.000000Z\"}}', NULL, '2026-09-08 06:07:12', '2026-09-08 06:07:12'),
(412, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:08:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:07:12.000000Z\"}}', NULL, '2026-09-08 06:08:12', '2026-09-08 06:08:12'),
(413, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:09:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:08:12.000000Z\"}}', NULL, '2026-09-08 06:09:12', '2026-09-08 06:09:12'),
(414, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:10:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:09:12.000000Z\"}}', NULL, '2026-09-08 06:10:12', '2026-09-08 06:10:12'),
(415, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:11:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:10:12.000000Z\"}}', NULL, '2026-09-08 06:11:12', '2026-09-08 06:11:12'),
(416, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:12:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:11:12.000000Z\"}}', NULL, '2026-09-08 06:12:12', '2026-09-08 06:12:12'),
(417, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:13:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:12:12.000000Z\"}}', NULL, '2026-09-08 06:13:12', '2026-09-08 06:13:12'),
(418, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_successful_sync_at\":\"2026-09-08T06:13:12.000000Z\"},\"old\":{\"last_successful_sync_at\":\"2026-09-08T05:58:12.000000Z\"}}', NULL, '2026-09-08 06:13:12', '2026-09-08 06:13:12'),
(419, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:14:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:13:12.000000Z\"}}', NULL, '2026-09-08 06:14:12', '2026-09-08 06:14:12'),
(420, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:15:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:14:12.000000Z\"}}', NULL, '2026-09-08 06:15:12', '2026-09-08 06:15:12'),
(421, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:16:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:15:12.000000Z\"}}', NULL, '2026-09-08 06:16:12', '2026-09-08 06:16:12'),
(422, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:17:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:16:12.000000Z\"}}', NULL, '2026-09-08 06:17:12', '2026-09-08 06:17:12'),
(423, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:18:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:17:12.000000Z\"}}', NULL, '2026-09-08 06:18:12', '2026-09-08 06:18:12'),
(424, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:19:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:18:12.000000Z\"}}', NULL, '2026-09-08 06:19:12', '2026-09-08 06:19:12'),
(425, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:20:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:19:12.000000Z\"}}', NULL, '2026-09-08 06:20:12', '2026-09-08 06:20:12'),
(426, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:21:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:20:12.000000Z\"}}', NULL, '2026-09-08 06:21:12', '2026-09-08 06:21:12'),
(427, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:22:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:21:12.000000Z\"}}', NULL, '2026-09-08 06:22:12', '2026-09-08 06:22:12'),
(428, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:23:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:22:12.000000Z\"}}', NULL, '2026-09-08 06:23:12', '2026-09-08 06:23:12'),
(429, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:24:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:23:12.000000Z\"}}', NULL, '2026-09-08 06:24:12', '2026-09-08 06:24:12'),
(430, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:25:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:24:12.000000Z\"}}', NULL, '2026-09-08 06:25:12', '2026-09-08 06:25:12'),
(431, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:26:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:25:12.000000Z\"}}', NULL, '2026-09-08 06:26:12', '2026-09-08 06:26:12'),
(432, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 22, 'App\\Models\\User', 1, '{\"attributes\":{\"dealer_code\":\"DLR-0007\",\"name\":\"Emran\",\"phone\":\"01756956465651\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true,\"tally_guid\":null,\"tally_ledger_name\":null}}', NULL, '2026-09-08 06:26:24', '2026-09-08 06:26:24'),
(433, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:26:52.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:26:12.000000Z\"}}', NULL, '2026-09-08 06:26:52', '2026-09-08 06:26:52'),
(434, 'dealers', 'updated', 'App\\Models\\Dealer', 'updated', 22, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010b\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 06:26:55', '2026-09-08 06:26:55'),
(435, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 65, NULL, NULL, '{\"attributes\":{\"entity_type\":\"dealer\",\"sfa_id\":22,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010b\",\"tally_name\":\"Emran\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T06:26:55.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 06:26:55', '2026-09-08 06:26:55'),
(436, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_successful_sync_at\":\"2026-09-08T06:26:55.000000Z\"},\"old\":{\"last_successful_sync_at\":\"2026-09-08T06:13:12.000000Z\"}}', NULL, '2026-09-08 06:26:55', '2026-09-08 06:26:55'),
(437, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:27:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:26:52.000000Z\"}}', NULL, '2026-09-08 06:27:12', '2026-09-08 06:27:12'),
(438, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:27:53.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:27:12.000000Z\"}}', NULL, '2026-09-08 06:27:53', '2026-09-08 06:27:53'),
(439, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_successful_sync_at\":\"2026-09-08T06:28:12.000000Z\"},\"old\":{\"last_successful_sync_at\":\"2026-09-08T06:26:55.000000Z\"}}', NULL, '2026-09-08 06:28:12', '2026-09-08 06:28:12'),
(440, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:28:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:27:53.000000Z\"}}', NULL, '2026-09-08 06:28:12', '2026-09-08 06:28:12'),
(441, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:28:53.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:28:12.000000Z\"}}', NULL, '2026-09-08 06:28:53', '2026-09-08 06:28:53'),
(442, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:29:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:28:53.000000Z\"}}', NULL, '2026-09-08 06:29:12', '2026-09-08 06:29:12'),
(443, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:29:53.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:29:12.000000Z\"}}', NULL, '2026-09-08 06:29:53', '2026-09-08 06:29:53'),
(444, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:30:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:29:53.000000Z\"}}', NULL, '2026-09-08 06:30:12', '2026-09-08 06:30:12'),
(445, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 23, NULL, NULL, '{\"attributes\":{\"dealer_code\":\"TLY-D-585664\",\"name\":\"Iftakher\",\"phone\":\"0000000000\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":false,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010c\",\"tally_ledger_name\":null}}', NULL, '2026-09-08 06:30:12', '2026-09-08 06:30:12'),
(446, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 66, NULL, NULL, '{\"attributes\":{\"entity_type\":\"dealer\",\"sfa_id\":23,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010c\",\"tally_name\":\"Iftakher\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T06:30:12.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 06:30:12', '2026-09-08 06:30:12'),
(447, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:30:53.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:30:12.000000Z\"}}', NULL, '2026-09-08 06:30:53', '2026-09-08 06:30:53'),
(448, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:31:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:30:53.000000Z\"}}', NULL, '2026-09-08 06:31:12', '2026-09-08 06:31:12'),
(449, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:31:53.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:31:12.000000Z\"}}', NULL, '2026-09-08 06:31:53', '2026-09-08 06:31:53'),
(450, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:32:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:31:53.000000Z\"}}', NULL, '2026-09-08 06:32:12', '2026-09-08 06:32:12'),
(451, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:32:53.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:32:12.000000Z\"}}', NULL, '2026-09-08 06:32:53', '2026-09-08 06:32:53'),
(452, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:33:12.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:32:53.000000Z\"}}', NULL, '2026-09-08 06:33:12', '2026-09-08 06:33:12'),
(453, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:33:53.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:33:12.000000Z\"}}', NULL, '2026-09-08 06:33:53', '2026-09-08 06:33:53'),
(454, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:34:09.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:33:53.000000Z\"}}', NULL, '2026-09-08 06:34:09', '2026-09-08 06:34:09'),
(455, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_successful_sync_at\":\"2026-09-08T06:34:10.000000Z\"},\"old\":{\"last_successful_sync_at\":\"2026-09-08T06:28:12.000000Z\"}}', NULL, '2026-09-08 06:34:10', '2026-09-08 06:34:10'),
(456, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:34:53.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:34:09.000000Z\"}}', NULL, '2026-09-08 06:34:53', '2026-09-08 06:34:53'),
(457, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:35:09.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:34:53.000000Z\"}}', NULL, '2026-09-08 06:35:09', '2026-09-08 06:35:09'),
(458, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:35:53.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:35:09.000000Z\"}}', NULL, '2026-09-08 06:35:53', '2026-09-08 06:35:53'),
(459, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:36:09.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:35:53.000000Z\"}}', NULL, '2026-09-08 06:36:09', '2026-09-08 06:36:09'),
(460, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:36:53.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:36:09.000000Z\"}}', NULL, '2026-09-08 06:36:53', '2026-09-08 06:36:53'),
(461, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:37:53.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:36:53.000000Z\"}}', NULL, '2026-09-08 06:37:53', '2026-09-08 06:37:53'),
(462, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:38:53.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:37:53.000000Z\"}}', NULL, '2026-09-08 06:38:53', '2026-09-08 06:38:53'),
(463, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:39:53.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:38:53.000000Z\"}}', NULL, '2026-09-08 06:39:53', '2026-09-08 06:39:53'),
(464, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:39:56.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:39:53.000000Z\"}}', NULL, '2026-09-08 06:39:56', '2026-09-08 06:39:56'),
(465, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_successful_sync_at\":\"2026-09-08T06:39:57.000000Z\"},\"old\":{\"last_successful_sync_at\":\"2026-09-08T06:34:10.000000Z\"}}', NULL, '2026-09-08 06:39:57', '2026-09-08 06:39:57'),
(466, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:40:53.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:39:56.000000Z\"}}', NULL, '2026-09-08 06:40:53', '2026-09-08 06:40:53'),
(467, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:40:56.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:40:53.000000Z\"}}', NULL, '2026-09-08 06:40:57', '2026-09-08 06:40:57'),
(468, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_successful_sync_at\":\"2026-09-08T06:41:53.000000Z\"},\"old\":{\"last_successful_sync_at\":\"2026-09-08T06:39:57.000000Z\"}}', NULL, '2026-09-08 06:41:53', '2026-09-08 06:41:53'),
(469, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:41:53.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:40:56.000000Z\"}}', NULL, '2026-09-08 06:41:53', '2026-09-08 06:41:53'),
(470, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:41:56.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:41:53.000000Z\"}}', NULL, '2026-09-08 06:41:56', '2026-09-08 06:41:56'),
(471, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:42:53.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:41:56.000000Z\"}}', NULL, '2026-09-08 06:42:53', '2026-09-08 06:42:53'),
(472, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:42:57.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:42:53.000000Z\"}}', NULL, '2026-09-08 06:42:57', '2026-09-08 06:42:57'),
(473, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:43:53.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:42:57.000000Z\"}}', NULL, '2026-09-08 06:43:53', '2026-09-08 06:43:53'),
(474, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:43:56.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:43:53.000000Z\"}}', NULL, '2026-09-08 06:43:56', '2026-09-08 06:43:56'),
(475, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:44:53.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:43:56.000000Z\"}}', NULL, '2026-09-08 06:44:53', '2026-09-08 06:44:53'),
(476, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:44:56.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:44:53.000000Z\"}}', NULL, '2026-09-08 06:44:56', '2026-09-08 06:44:56'),
(477, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:45:53.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:44:56.000000Z\"}}', NULL, '2026-09-08 06:45:53', '2026-09-08 06:45:53'),
(478, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:45:56.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:45:53.000000Z\"}}', NULL, '2026-09-08 06:45:56', '2026-09-08 06:45:56'),
(479, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:46:53.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:45:56.000000Z\"}}', NULL, '2026-09-08 06:46:53', '2026-09-08 06:46:53'),
(480, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:46:56.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:46:53.000000Z\"}}', NULL, '2026-09-08 06:46:56', '2026-09-08 06:46:56'),
(481, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:47:56.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:46:56.000000Z\"}}', NULL, '2026-09-08 06:47:56', '2026-09-08 06:47:56'),
(482, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:48:57.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:47:56.000000Z\"}}', NULL, '2026-09-08 06:48:57', '2026-09-08 06:48:57'),
(483, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:49:29.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:48:57.000000Z\"}}', NULL, '2026-09-08 06:49:29', '2026-09-08 06:49:29'),
(484, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_successful_sync_at\":\"2026-09-08T06:49:30.000000Z\"},\"old\":{\"last_successful_sync_at\":\"2026-09-08T06:41:53.000000Z\"}}', NULL, '2026-09-08 06:49:30', '2026-09-08 06:49:30'),
(485, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:49:59.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:49:29.000000Z\"}}', NULL, '2026-09-08 06:49:59', '2026-09-08 06:49:59'),
(486, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_successful_sync_at\":\"2026-09-08T06:49:59.000000Z\"},\"old\":{\"last_successful_sync_at\":\"2026-09-08T06:49:30.000000Z\"}}', NULL, '2026-09-08 06:49:59', '2026-09-08 06:49:59'),
(487, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:50:59.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:49:59.000000Z\"}}', NULL, '2026-09-08 06:50:59', '2026-09-08 06:50:59'),
(488, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:51:59.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:50:59.000000Z\"}}', NULL, '2026-09-08 06:51:59', '2026-09-08 06:51:59'),
(489, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:52:59.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:51:59.000000Z\"}}', NULL, '2026-09-08 06:52:59', '2026-09-08 06:52:59'),
(490, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:53:59.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:52:59.000000Z\"}}', NULL, '2026-09-08 06:53:59', '2026-09-08 06:53:59'),
(491, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:54:59.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:53:59.000000Z\"}}', NULL, '2026-09-08 06:54:59', '2026-09-08 06:54:59'),
(492, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:55:59.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:54:59.000000Z\"}}', NULL, '2026-09-08 06:55:59', '2026-09-08 06:55:59'),
(493, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:56:59.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:55:59.000000Z\"}}', NULL, '2026-09-08 06:56:59', '2026-09-08 06:56:59'),
(494, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:57:59.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:56:59.000000Z\"}}', NULL, '2026-09-08 06:57:59', '2026-09-08 06:57:59'),
(495, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:58:59.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:57:59.000000Z\"}}', NULL, '2026-09-08 06:58:59', '2026-09-08 06:58:59'),
(496, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 24, NULL, NULL, '{\"attributes\":{\"dealer_code\":\"TLY-D-867615\",\"name\":\"Iftakher\",\"phone\":\"0000000000\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":false,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010d\",\"tally_ledger_name\":null}}', NULL, '2026-09-08 06:58:59', '2026-09-08 06:58:59'),
(497, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 67, NULL, NULL, '{\"attributes\":{\"entity_type\":\"dealer\",\"sfa_id\":24,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010d\",\"tally_name\":\"Iftakher\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T06:58:59.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 06:58:59', '2026-09-08 06:58:59'),
(498, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T06:59:59.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:58:59.000000Z\"}}', NULL, '2026-09-08 06:59:59', '2026-09-08 06:59:59'),
(499, 'dealers', 'deleted', 'App\\Models\\Dealer', 'deleted', 22, 'App\\Models\\User', 1, '{\"old\":{\"dealer_code\":\"DLR-0007\",\"name\":\"Emran\",\"phone\":\"01756956465651\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010b\",\"tally_ledger_name\":null}}', NULL, '2026-09-08 07:02:13', '2026-09-08 07:02:13'),
(500, 'dealers', 'deleted', 'App\\Models\\Dealer', 'deleted', 20, 'App\\Models\\User', 1, '{\"old\":{\"dealer_code\":\"DLR-0006\",\"name\":\"Korim\",\"phone\":\"010000000\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010a\",\"tally_ledger_name\":null}}', NULL, '2026-09-08 07:02:31', '2026-09-08 07:02:31'),
(501, 'dealers', 'deleted', 'App\\Models\\Dealer', 'deleted', 21, 'App\\Models\\User', 1, '{\"old\":{\"dealer_code\":\"TLY-D-032746\",\"name\":\"Rohim Dealer\",\"phone\":\"0000000000\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":false,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000109\",\"tally_ledger_name\":null}}', NULL, '2026-09-08 07:02:35', '2026-09-08 07:02:35'),
(502, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 25, NULL, NULL, '{\"attributes\":{\"dealer_code\":\"ZZ-DELETE-TEST\",\"name\":\"ZZZ Delete Test\",\"phone\":\"0170000000\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true,\"tally_guid\":null,\"tally_ledger_name\":null}}', NULL, '2026-09-08 07:07:28', '2026-09-08 07:07:28'),
(503, 'dealers', 'deleted', 'App\\Models\\Dealer', 'deleted', 25, 'App\\Models\\User', 1, '{\"old\":{\"dealer_code\":\"ZZ-DELETE-TEST\",\"name\":\"ZZZ Delete Test\",\"phone\":\"0170000000\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true,\"tally_guid\":null,\"tally_ledger_name\":null}}', NULL, '2026-09-08 07:08:30', '2026-09-08 07:08:30'),
(504, 'dealers', 'deleted', 'App\\Models\\Dealer', 'deleted', 25, NULL, NULL, '{\"old\":{\"dealer_code\":\"ZZ-DELETE-TEST\",\"name\":\"ZZZ Delete Test\",\"phone\":\"0170000000\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true,\"tally_guid\":null,\"tally_ledger_name\":null}}', NULL, '2026-09-08 07:08:45', '2026-09-08 07:08:45'),
(505, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 26, NULL, NULL, '{\"attributes\":{\"dealer_code\":\"ZZ-BULK-1\",\"name\":\"ZZZ Bulk Test 1\",\"phone\":\"0170000000\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true,\"tally_guid\":null,\"tally_ledger_name\":null}}', NULL, '2026-09-08 07:13:14', '2026-09-08 07:13:14'),
(506, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 27, NULL, NULL, '{\"attributes\":{\"dealer_code\":\"ZZ-BULK-2\",\"name\":\"ZZZ Bulk Test 2\",\"phone\":\"0170000001\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true,\"tally_guid\":null,\"tally_ledger_name\":null}}', NULL, '2026-09-08 07:13:14', '2026-09-08 07:13:14'),
(507, 'dealers', 'deleted', 'App\\Models\\Dealer', 'deleted', 26, 'App\\Models\\User', 1, '{\"old\":{\"dealer_code\":\"ZZ-BULK-1\",\"name\":\"ZZZ Bulk Test 1\",\"phone\":\"0170000000\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true,\"tally_guid\":null,\"tally_ledger_name\":null}}', NULL, '2026-09-08 07:14:21', '2026-09-08 07:14:21'),
(508, 'dealers', 'deleted', 'App\\Models\\Dealer', 'deleted', 27, 'App\\Models\\User', 1, '{\"old\":{\"dealer_code\":\"ZZ-BULK-2\",\"name\":\"ZZZ Bulk Test 2\",\"phone\":\"0170000001\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true,\"tally_guid\":null,\"tally_ledger_name\":null}}', NULL, '2026-09-08 07:14:21', '2026-09-08 07:14:21'),
(509, 'dealers', 'deleted', 'App\\Models\\Dealer', 'deleted', 18, 'App\\Models\\User', 1, '{\"old\":{\"dealer_code\":\"TLY-D-537415\",\"name\":\"Dealer 1\",\"phone\":\"0000000000\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":false,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\",\"tally_ledger_name\":null}}', NULL, '2026-09-08 07:15:30', '2026-09-08 07:15:30'),
(510, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 28, 'App\\Models\\User', 1, '{\"attributes\":{\"dealer_code\":\"DLR-0006\",\"name\":\"Omar\",\"phone\":\"0123456789\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true,\"tally_guid\":null,\"tally_ledger_name\":null}}', NULL, '2026-09-08 07:27:40', '2026-09-08 07:27:40'),
(511, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:29:01.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T06:59:59.000000Z\"}}', NULL, '2026-09-08 07:29:01', '2026-09-08 07:29:01'),
(512, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 29, NULL, NULL, '{\"attributes\":{\"dealer_code\":\"TLY-D-352104\",\"name\":\"Jewel\",\"phone\":\"0000000000\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":false,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010e\",\"tally_ledger_name\":null}}', NULL, '2026-09-08 07:29:03', '2026-09-08 07:29:03'),
(513, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 68, NULL, NULL, '{\"attributes\":{\"entity_type\":\"dealer\",\"sfa_id\":29,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010e\",\"tally_name\":\"Jewel\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T07:29:03.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 07:29:03', '2026-09-08 07:29:03'),
(514, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_successful_sync_at\":\"2026-09-08T07:29:08.000000Z\"},\"old\":{\"last_successful_sync_at\":\"2026-09-08T06:49:59.000000Z\"}}', NULL, '2026-09-08 07:29:08', '2026-09-08 07:29:08'),
(515, 'dealers', 'deleted', 'App\\Models\\Dealer', 'deleted', 29, 'App\\Models\\User', 1, '{\"old\":{\"dealer_code\":\"TLY-D-352104\",\"name\":\"Jewel\",\"phone\":\"0000000000\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":false,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010e\",\"tally_ledger_name\":null}}', NULL, '2026-09-08 07:29:37', '2026-09-08 07:29:37'),
(516, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:30:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:29:01.000000Z\"}}', NULL, '2026-09-08 07:30:02', '2026-09-08 07:30:02'),
(517, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 30, NULL, NULL, '{\"attributes\":{\"dealer_code\":\"TLY-D-395767\",\"name\":\"Jewel\",\"phone\":\"0000000000\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":false,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010e\",\"tally_ledger_name\":null}}', NULL, '2026-09-08 07:30:02', '2026-09-08 07:30:02'),
(518, 'tally_mappings', 'updated', 'App\\Models\\TallyMapping', 'updated', 68, NULL, NULL, '{\"attributes\":{\"sfa_id\":30,\"last_synced_at\":\"2026-09-08T07:30:02.000000Z\"},\"old\":{\"sfa_id\":29,\"last_synced_at\":\"2026-09-08T07:29:03.000000Z\"}}', NULL, '2026-09-08 07:30:02', '2026-09-08 07:30:02'),
(519, 'dealers', 'updated', 'App\\Models\\Dealer', 'updated', 28, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010f\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 07:30:33', '2026-09-08 07:30:33'),
(520, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 69, NULL, NULL, '{\"attributes\":{\"entity_type\":\"dealer\",\"sfa_id\":28,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010f\",\"tally_name\":\"Omar\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T07:30:33.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 07:30:33', '2026-09-08 07:30:33'),
(521, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:31:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:30:02.000000Z\"}}', NULL, '2026-09-08 07:31:02', '2026-09-08 07:31:02'),
(522, 'dealers', 'deleted', 'App\\Models\\Dealer', 'deleted', 28, 'App\\Models\\User', 1, '{\"old\":{\"dealer_code\":\"DLR-0006\",\"name\":\"Omar\",\"phone\":\"0123456789\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010f\",\"tally_ledger_name\":null}}', NULL, '2026-09-08 07:31:07', '2026-09-08 07:31:07'),
(523, 'dealers', 'deleted', 'App\\Models\\Dealer', 'deleted', 30, 'App\\Models\\User', 1, '{\"old\":{\"dealer_code\":\"TLY-D-395767\",\"name\":\"Jewel\",\"phone\":\"0000000000\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":false,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010e\",\"tally_ledger_name\":null}}', NULL, '2026-09-08 07:31:10', '2026-09-08 07:31:10'),
(524, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 31, 'App\\Models\\User', 1, '{\"attributes\":{\"dealer_code\":\"13\",\"name\":\"Omar\",\"phone\":\"0123456789\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true,\"tally_guid\":null,\"tally_ledger_name\":null}}', NULL, '2026-09-08 07:31:44', '2026-09-08 07:31:44'),
(525, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:32:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:31:02.000000Z\"}}', NULL, '2026-09-08 07:32:02', '2026-09-08 07:32:02'),
(526, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 32, NULL, NULL, '{\"attributes\":{\"dealer_code\":\"TLY-D-763044\",\"name\":\"Jewel\",\"phone\":\"0000000000\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":false,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000110\",\"tally_ledger_name\":null}}', NULL, '2026-09-08 07:32:32', '2026-09-08 07:32:32'),
(527, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 70, NULL, NULL, '{\"attributes\":{\"entity_type\":\"dealer\",\"sfa_id\":32,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000110\",\"tally_name\":\"Jewel\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T07:32:32.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 07:32:32', '2026-09-08 07:32:32'),
(528, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:33:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:32:02.000000Z\"}}', NULL, '2026-09-08 07:33:02', '2026-09-08 07:33:02'),
(529, 'dealers', 'updated', 'App\\Models\\Dealer', 'updated', 31, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000111\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 07:33:03', '2026-09-08 07:33:03'),
(530, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 71, NULL, NULL, '{\"attributes\":{\"entity_type\":\"dealer\",\"sfa_id\":31,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000111\",\"tally_name\":\"Omar\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T07:33:03.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 07:33:03', '2026-09-08 07:33:03'),
(531, 'dealers', 'deleted', 'App\\Models\\Dealer', 'deleted', 31, 'App\\Models\\User', 1, '{\"old\":{\"dealer_code\":\"13\",\"name\":\"Omar\",\"phone\":\"0123456789\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000111\",\"tally_ledger_name\":null}}', NULL, '2026-09-08 07:33:21', '2026-09-08 07:33:21'),
(532, 'dealers', 'deleted', 'App\\Models\\Dealer', 'deleted', 32, 'App\\Models\\User', 1, '{\"old\":{\"dealer_code\":\"TLY-D-763044\",\"name\":\"Jewel\",\"phone\":\"0000000000\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":false,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000110\",\"tally_ledger_name\":null}}', NULL, '2026-09-08 07:33:24', '2026-09-08 07:33:24'),
(533, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:34:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:33:02.000000Z\"}}', NULL, '2026-09-08 07:34:02', '2026-09-08 07:34:02'),
(534, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:35:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:34:02.000000Z\"}}', NULL, '2026-09-08 07:35:02', '2026-09-08 07:35:02'),
(535, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:36:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:35:02.000000Z\"}}', NULL, '2026-09-08 07:36:02', '2026-09-08 07:36:02'),
(536, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:37:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:36:02.000000Z\"}}', NULL, '2026-09-08 07:37:02', '2026-09-08 07:37:02'),
(537, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:38:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:37:02.000000Z\"}}', NULL, '2026-09-08 07:38:02', '2026-09-08 07:38:02'),
(538, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:39:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:38:02.000000Z\"}}', NULL, '2026-09-08 07:39:02', '2026-09-08 07:39:02'),
(539, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:40:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:39:02.000000Z\"}}', NULL, '2026-09-08 07:40:02', '2026-09-08 07:40:02'),
(540, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:41:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:40:02.000000Z\"}}', NULL, '2026-09-08 07:41:02', '2026-09-08 07:41:02');
INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES
(541, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:42:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:41:02.000000Z\"}}', NULL, '2026-09-08 07:42:02', '2026-09-08 07:42:02'),
(542, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:43:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:42:02.000000Z\"}}', NULL, '2026-09-08 07:43:02', '2026-09-08 07:43:02'),
(543, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_successful_sync_at\":\"2026-09-08T07:44:02.000000Z\"},\"old\":{\"last_successful_sync_at\":\"2026-09-08T07:29:08.000000Z\"}}', NULL, '2026-09-08 07:44:02', '2026-09-08 07:44:02'),
(544, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:44:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:43:02.000000Z\"}}', NULL, '2026-09-08 07:44:02', '2026-09-08 07:44:02'),
(545, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:45:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:44:02.000000Z\"}}', NULL, '2026-09-08 07:45:02', '2026-09-08 07:45:02'),
(546, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:46:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:45:02.000000Z\"}}', NULL, '2026-09-08 07:46:02', '2026-09-08 07:46:02'),
(547, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:47:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:46:02.000000Z\"}}', NULL, '2026-09-08 07:47:02', '2026-09-08 07:47:02'),
(548, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:48:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:47:02.000000Z\"}}', NULL, '2026-09-08 07:48:02', '2026-09-08 07:48:02'),
(549, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:49:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:48:02.000000Z\"}}', NULL, '2026-09-08 07:49:02', '2026-09-08 07:49:02'),
(550, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:50:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:49:02.000000Z\"}}', NULL, '2026-09-08 07:50:02', '2026-09-08 07:50:02'),
(551, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:51:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:50:02.000000Z\"}}', NULL, '2026-09-08 07:51:02', '2026-09-08 07:51:02'),
(552, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:52:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:51:02.000000Z\"}}', NULL, '2026-09-08 07:52:02', '2026-09-08 07:52:02'),
(553, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:53:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:52:02.000000Z\"}}', NULL, '2026-09-08 07:53:02', '2026-09-08 07:53:02'),
(554, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:54:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:53:02.000000Z\"}}', NULL, '2026-09-08 07:54:02', '2026-09-08 07:54:02'),
(555, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:55:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:54:02.000000Z\"}}', NULL, '2026-09-08 07:55:02', '2026-09-08 07:55:02'),
(556, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:56:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:55:02.000000Z\"}}', NULL, '2026-09-08 07:56:02', '2026-09-08 07:56:02'),
(557, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:57:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:56:02.000000Z\"}}', NULL, '2026-09-08 07:57:02', '2026-09-08 07:57:02'),
(558, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:58:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:57:02.000000Z\"}}', NULL, '2026-09-08 07:58:02', '2026-09-08 07:58:02'),
(559, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_successful_sync_at\":\"2026-09-08T07:59:02.000000Z\"},\"old\":{\"last_successful_sync_at\":\"2026-09-08T07:44:02.000000Z\"}}', NULL, '2026-09-08 07:59:02', '2026-09-08 07:59:02'),
(560, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T07:59:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:58:02.000000Z\"}}', NULL, '2026-09-08 07:59:02', '2026-09-08 07:59:02'),
(561, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:00:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T07:59:02.000000Z\"}}', NULL, '2026-09-08 08:00:02', '2026-09-08 08:00:02'),
(562, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:01:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:00:02.000000Z\"}}', NULL, '2026-09-08 08:01:02', '2026-09-08 08:01:02'),
(563, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:02:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:01:02.000000Z\"}}', NULL, '2026-09-08 08:02:02', '2026-09-08 08:02:02'),
(564, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:03:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:02:02.000000Z\"}}', NULL, '2026-09-08 08:03:02', '2026-09-08 08:03:02'),
(565, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:04:03.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:03:02.000000Z\"}}', NULL, '2026-09-08 08:04:03', '2026-09-08 08:04:03'),
(566, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:05:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:04:03.000000Z\"}}', NULL, '2026-09-08 08:05:02', '2026-09-08 08:05:02'),
(567, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:06:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:05:02.000000Z\"}}', NULL, '2026-09-08 08:06:02', '2026-09-08 08:06:02'),
(568, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:07:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:06:02.000000Z\"}}', NULL, '2026-09-08 08:07:02', '2026-09-08 08:07:02'),
(569, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:08:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:07:02.000000Z\"}}', NULL, '2026-09-08 08:08:02', '2026-09-08 08:08:02'),
(570, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 33, 'App\\Models\\User', 1, '{\"attributes\":{\"dealer_code\":\"DLR-0006\",\"name\":\"Omar\",\"phone\":\"01723456789\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":true,\"tally_guid\":null,\"tally_ledger_name\":null}}', NULL, '2026-09-08 08:08:31', '2026-09-08 08:08:31'),
(571, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:09:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:08:02.000000Z\"}}', NULL, '2026-09-08 08:09:02', '2026-09-08 08:09:02'),
(572, 'dealers', 'created', 'App\\Models\\Dealer', 'created', 34, NULL, NULL, '{\"attributes\":{\"dealer_code\":\"TLY-D-277683\",\"name\":\"Jewel\",\"phone\":\"0000000000\",\"email\":null,\"address\":null,\"image\":null,\"gps_lat\":null,\"gps_lng\":null,\"division_id\":null,\"district_id\":null,\"thana_id\":null,\"territory_id\":null,\"status\":false,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000112\",\"tally_ledger_name\":null}}', NULL, '2026-09-08 08:09:33', '2026-09-08 08:09:33'),
(573, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 72, NULL, NULL, '{\"attributes\":{\"entity_type\":\"dealer\",\"sfa_id\":34,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000112\",\"tally_name\":\"Jewel\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T08:09:33.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 08:09:33', '2026-09-08 08:09:33'),
(574, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:10:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:09:02.000000Z\"}}', NULL, '2026-09-08 08:10:02', '2026-09-08 08:10:02'),
(575, 'dealers', 'updated', 'App\\Models\\Dealer', 'updated', 33, NULL, NULL, '{\"attributes\":{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000113\"},\"old\":{\"tally_guid\":null}}', NULL, '2026-09-08 08:10:04', '2026-09-08 08:10:04'),
(576, 'tally_mappings', 'created', 'App\\Models\\TallyMapping', 'created', 73, NULL, NULL, '{\"attributes\":{\"entity_type\":\"dealer\",\"sfa_id\":33,\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000113\",\"tally_name\":\"Omar\",\"tally_alter_id\":null,\"last_synced_at\":\"2026-09-08T08:10:04.000000Z\",\"sync_status\":\"pending\"}}', NULL, '2026-09-08 08:10:04', '2026-09-08 08:10:04'),
(577, 'dealers', 'updated', 'App\\Models\\Dealer', 'updated', 34, 'App\\Models\\User', 1, '{\"attributes\":{\"status\":true},\"old\":{\"status\":false}}', NULL, '2026-09-08 08:10:34', '2026-09-08 08:10:34'),
(578, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:11:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:10:02.000000Z\"}}', NULL, '2026-09-08 08:11:02', '2026-09-08 08:11:02'),
(579, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:12:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:11:02.000000Z\"}}', NULL, '2026-09-08 08:12:02', '2026-09-08 08:12:02'),
(580, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:13:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:12:02.000000Z\"}}', NULL, '2026-09-08 08:13:02', '2026-09-08 08:13:02'),
(581, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_successful_sync_at\":\"2026-09-08T08:14:02.000000Z\"},\"old\":{\"last_successful_sync_at\":\"2026-09-08T07:59:02.000000Z\"}}', NULL, '2026-09-08 08:14:02', '2026-09-08 08:14:02'),
(582, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:14:03.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:13:02.000000Z\"}}', NULL, '2026-09-08 08:14:03', '2026-09-08 08:14:03'),
(583, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:15:03.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:14:03.000000Z\"}}', NULL, '2026-09-08 08:15:03', '2026-09-08 08:15:03'),
(584, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:16:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:15:03.000000Z\"}}', NULL, '2026-09-08 08:16:02', '2026-09-08 08:16:02'),
(585, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:17:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:16:02.000000Z\"}}', NULL, '2026-09-08 08:17:02', '2026-09-08 08:17:02'),
(586, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:18:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:17:02.000000Z\"}}', NULL, '2026-09-08 08:18:02', '2026-09-08 08:18:02'),
(587, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:19:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:18:02.000000Z\"}}', NULL, '2026-09-08 08:19:02', '2026-09-08 08:19:02'),
(588, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:20:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:19:02.000000Z\"}}', NULL, '2026-09-08 08:20:02', '2026-09-08 08:20:02'),
(589, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:21:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:20:02.000000Z\"}}', NULL, '2026-09-08 08:21:02', '2026-09-08 08:21:02'),
(590, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:22:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:21:02.000000Z\"}}', NULL, '2026-09-08 08:22:02', '2026-09-08 08:22:02'),
(591, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:23:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:22:02.000000Z\"}}', NULL, '2026-09-08 08:23:02', '2026-09-08 08:23:02'),
(592, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:24:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:23:02.000000Z\"}}', NULL, '2026-09-08 08:24:02', '2026-09-08 08:24:02'),
(593, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:25:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:24:02.000000Z\"}}', NULL, '2026-09-08 08:25:02', '2026-09-08 08:25:02'),
(594, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:26:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:25:02.000000Z\"}}', NULL, '2026-09-08 08:26:02', '2026-09-08 08:26:02'),
(595, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:27:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:26:02.000000Z\"}}', NULL, '2026-09-08 08:27:02', '2026-09-08 08:27:02'),
(596, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:28:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:27:02.000000Z\"}}', NULL, '2026-09-08 08:28:02', '2026-09-08 08:28:02'),
(597, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_successful_sync_at\":\"2026-09-08T08:29:02.000000Z\"},\"old\":{\"last_successful_sync_at\":\"2026-09-08T08:14:02.000000Z\"}}', NULL, '2026-09-08 08:29:02', '2026-09-08 08:29:02'),
(598, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:29:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:28:02.000000Z\"}}', NULL, '2026-09-08 08:29:02', '2026-09-08 08:29:02'),
(599, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:30:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:29:02.000000Z\"}}', NULL, '2026-09-08 08:30:02', '2026-09-08 08:30:02'),
(600, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:31:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:30:02.000000Z\"}}', NULL, '2026-09-08 08:31:02', '2026-09-08 08:31:02'),
(601, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:32:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:31:02.000000Z\"}}', NULL, '2026-09-08 08:32:02', '2026-09-08 08:32:02'),
(602, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:33:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:32:02.000000Z\"}}', NULL, '2026-09-08 08:33:02', '2026-09-08 08:33:02'),
(603, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:34:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:33:02.000000Z\"}}', NULL, '2026-09-08 08:34:02', '2026-09-08 08:34:02'),
(604, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:35:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:34:02.000000Z\"}}', NULL, '2026-09-08 08:35:02', '2026-09-08 08:35:02'),
(605, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:36:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:35:02.000000Z\"}}', NULL, '2026-09-08 08:36:02', '2026-09-08 08:36:02'),
(606, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:37:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:36:02.000000Z\"}}', NULL, '2026-09-08 08:37:02', '2026-09-08 08:37:02'),
(607, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:38:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:37:02.000000Z\"}}', NULL, '2026-09-08 08:38:02', '2026-09-08 08:38:02'),
(608, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:39:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:38:02.000000Z\"}}', NULL, '2026-09-08 08:39:02', '2026-09-08 08:39:02'),
(609, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:40:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:39:02.000000Z\"}}', NULL, '2026-09-08 08:40:02', '2026-09-08 08:40:02'),
(610, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:41:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:40:02.000000Z\"}}', NULL, '2026-09-08 08:41:02', '2026-09-08 08:41:02'),
(611, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:42:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:41:02.000000Z\"}}', NULL, '2026-09-08 08:42:02', '2026-09-08 08:42:02'),
(612, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:43:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:42:02.000000Z\"}}', NULL, '2026-09-08 08:43:02', '2026-09-08 08:43:02'),
(613, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_successful_sync_at\":\"2026-09-08T08:44:02.000000Z\"},\"old\":{\"last_successful_sync_at\":\"2026-09-08T08:29:02.000000Z\"}}', NULL, '2026-09-08 08:44:02', '2026-09-08 08:44:02'),
(614, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:44:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:43:02.000000Z\"}}', NULL, '2026-09-08 08:44:02', '2026-09-08 08:44:02'),
(615, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:45:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:44:02.000000Z\"}}', NULL, '2026-09-08 08:45:02', '2026-09-08 08:45:02'),
(616, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:46:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:45:02.000000Z\"}}', NULL, '2026-09-08 08:46:02', '2026-09-08 08:46:02'),
(617, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:47:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:46:02.000000Z\"}}', NULL, '2026-09-08 08:47:02', '2026-09-08 08:47:02'),
(618, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:48:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:47:02.000000Z\"}}', NULL, '2026-09-08 08:48:02', '2026-09-08 08:48:02'),
(619, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:49:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:48:02.000000Z\"}}', NULL, '2026-09-08 08:49:02', '2026-09-08 08:49:02'),
(620, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:50:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:49:02.000000Z\"}}', NULL, '2026-09-08 08:50:02', '2026-09-08 08:50:02'),
(621, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:51:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:50:02.000000Z\"}}', NULL, '2026-09-08 08:51:02', '2026-09-08 08:51:02'),
(622, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:52:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:51:02.000000Z\"}}', NULL, '2026-09-08 08:52:02', '2026-09-08 08:52:02'),
(623, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:53:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:52:02.000000Z\"}}', NULL, '2026-09-08 08:53:02', '2026-09-08 08:53:02'),
(624, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T08:54:02.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:53:02.000000Z\"}}', NULL, '2026-09-08 08:54:02', '2026-09-08 08:54:02'),
(625, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T09:28:17.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T08:54:02.000000Z\"}}', NULL, '2026-09-08 09:28:17', '2026-09-08 09:28:17'),
(626, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_successful_sync_at\":\"2026-09-08T09:28:17.000000Z\"},\"old\":{\"last_successful_sync_at\":\"2026-09-08T08:44:02.000000Z\"}}', NULL, '2026-09-08 09:28:17', '2026-09-08 09:28:17'),
(627, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T09:29:15.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T09:28:17.000000Z\"}}', NULL, '2026-09-08 09:29:15', '2026-09-08 09:29:15'),
(628, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T09:30:15.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T09:29:15.000000Z\"}}', NULL, '2026-09-08 09:30:15', '2026-09-08 09:30:15'),
(629, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T09:31:15.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T09:30:15.000000Z\"}}', NULL, '2026-09-08 09:31:15', '2026-09-08 09:31:15'),
(630, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T09:32:15.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T09:31:15.000000Z\"}}', NULL, '2026-09-08 09:32:15', '2026-09-08 09:32:15'),
(631, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T09:33:15.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T09:32:15.000000Z\"}}', NULL, '2026-09-08 09:33:15', '2026-09-08 09:33:15'),
(632, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T09:34:15.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T09:33:15.000000Z\"}}', NULL, '2026-09-08 09:34:15', '2026-09-08 09:34:15'),
(633, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T09:35:15.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T09:34:15.000000Z\"}}', NULL, '2026-09-08 09:35:15', '2026-09-08 09:35:15'),
(634, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T09:36:15.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T09:35:15.000000Z\"}}', NULL, '2026-09-08 09:36:15', '2026-09-08 09:36:15'),
(635, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T09:37:15.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T09:36:15.000000Z\"}}', NULL, '2026-09-08 09:37:15', '2026-09-08 09:37:15'),
(636, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T09:38:15.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T09:37:15.000000Z\"}}', NULL, '2026-09-08 09:38:15', '2026-09-08 09:38:15'),
(637, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T09:39:15.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T09:38:15.000000Z\"}}', NULL, '2026-09-08 09:39:15', '2026-09-08 09:39:15'),
(638, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T09:40:15.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T09:39:15.000000Z\"}}', NULL, '2026-09-08 09:40:15', '2026-09-08 09:40:15'),
(639, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T09:41:15.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T09:40:15.000000Z\"}}', NULL, '2026-09-08 09:41:15', '2026-09-08 09:41:15'),
(640, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T09:42:15.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T09:41:15.000000Z\"}}', NULL, '2026-09-08 09:42:15', '2026-09-08 09:42:15'),
(641, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_successful_sync_at\":\"2026-09-08T09:43:15.000000Z\"},\"old\":{\"last_successful_sync_at\":\"2026-09-08T09:28:17.000000Z\"}}', NULL, '2026-09-08 09:43:15', '2026-09-08 09:43:15'),
(642, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-08T09:43:15.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T09:42:15.000000Z\"}}', NULL, '2026-09-08 09:43:15', '2026-09-08 09:43:15'),
(643, 'leave_types', 'created', 'App\\Models\\LeaveType', 'created', 1, NULL, NULL, '{\"attributes\":{\"name\":\"Casual Leave\",\"code\":\"CL\",\"description\":null,\"annual_quota\":10,\"is_paid\":true,\"status\":true}}', NULL, '2026-09-09 04:16:52', '2026-09-09 04:16:52'),
(644, 'leave_types', 'created', 'App\\Models\\LeaveType', 'created', 2, NULL, NULL, '{\"attributes\":{\"name\":\"Sick Leave\",\"code\":\"SL\",\"description\":null,\"annual_quota\":14,\"is_paid\":true,\"status\":true}}', NULL, '2026-09-09 04:16:52', '2026-09-09 04:16:52'),
(645, 'leave_types', 'created', 'App\\Models\\LeaveType', 'created', 3, NULL, NULL, '{\"attributes\":{\"name\":\"Annual Leave\",\"code\":\"AL\",\"description\":null,\"annual_quota\":20,\"is_paid\":true,\"status\":true}}', NULL, '2026-09-09 04:16:52', '2026-09-09 04:16:52'),
(646, 'leave_types', 'created', 'App\\Models\\LeaveType', 'created', 4, NULL, NULL, '{\"attributes\":{\"name\":\"Unpaid Leave\",\"code\":\"UL\",\"description\":null,\"annual_quota\":0,\"is_paid\":false,\"status\":true}}', NULL, '2026-09-09 04:16:52', '2026-09-09 04:16:52'),
(647, 'leave_requests', 'created', 'App\\Models\\LeaveRequest', 'created', 1, NULL, NULL, '{\"attributes\":{\"user_id\":80,\"leave_type_id\":1,\"from_date\":\"2026-09-14\",\"to_date\":\"2026-09-15\",\"days\":\"2.0\",\"is_half_day\":false,\"reason\":\"Demo request for UI check.\",\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null,\"decision_remarks\":null}}', NULL, '2026-09-09 04:16:52', '2026-09-09 04:16:52'),
(648, 'leave_requests', 'updated', 'App\\Models\\LeaveRequest', 'updated', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"status\":\"approved\",\"approved_by\":1,\"approved_at\":\"2026-09-09T04:17:50.000000Z\",\"decision_remarks\":\"Approved via UI check.\"},\"old\":{\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null,\"decision_remarks\":null}}', NULL, '2026-09-09 04:17:50', '2026-09-09 04:17:50'),
(649, 'attendances', 'created', 'App\\Models\\Attendance', 'created', 10, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":80,\"date\":\"2026-09-14\",\"check_in_at\":null,\"check_in_lat\":null,\"check_in_lng\":null,\"check_in_photo\":null,\"check_out_at\":null,\"check_out_lat\":null,\"check_out_lng\":null,\"check_out_photo\":null,\"status\":\"leave\",\"late_minutes\":0,\"remarks\":\"On approved Casual Leave leave (request #1).\"}}', NULL, '2026-09-09 04:17:50', '2026-09-09 04:17:50'),
(650, 'attendances', 'created', 'App\\Models\\Attendance', 'created', 11, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":80,\"date\":\"2026-09-15\",\"check_in_at\":null,\"check_in_lat\":null,\"check_in_lng\":null,\"check_in_photo\":null,\"check_out_at\":null,\"check_out_lat\":null,\"check_out_lng\":null,\"check_out_photo\":null,\"status\":\"leave\",\"late_minutes\":0,\"remarks\":\"On approved Casual Leave leave (request #1).\"}}', NULL, '2026-09-09 04:17:50', '2026-09-09 04:17:50'),
(651, 'leave_requests', 'deleted', 'App\\Models\\LeaveRequest', 'deleted', 1, NULL, NULL, '{\"old\":{\"user_id\":80,\"leave_type_id\":1,\"from_date\":\"2026-09-14\",\"to_date\":\"2026-09-15\",\"days\":\"2.0\",\"is_half_day\":false,\"reason\":\"Demo request for UI check.\",\"status\":\"approved\",\"approved_by\":1,\"approved_at\":\"2026-09-09T04:17:50.000000Z\",\"decision_remarks\":\"Approved via UI check.\"}}', NULL, '2026-09-09 04:19:05', '2026-09-09 04:19:05'),
(652, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 1, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":91,\"leave_type_id\":3,\"year\":2026,\"entitled_days\":\"20.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(653, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 2, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":91,\"leave_type_id\":1,\"year\":2026,\"entitled_days\":\"10.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(654, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 3, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":91,\"leave_type_id\":2,\"year\":2026,\"entitled_days\":\"14.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(655, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 4, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":91,\"leave_type_id\":4,\"year\":2026,\"entitled_days\":\"0.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(656, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 5, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":89,\"leave_type_id\":3,\"year\":2026,\"entitled_days\":\"20.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(657, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 6, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":89,\"leave_type_id\":1,\"year\":2026,\"entitled_days\":\"10.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(658, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 7, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":89,\"leave_type_id\":2,\"year\":2026,\"entitled_days\":\"14.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(659, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 8, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":89,\"leave_type_id\":4,\"year\":2026,\"entitled_days\":\"0.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(660, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 9, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":90,\"leave_type_id\":3,\"year\":2026,\"entitled_days\":\"20.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(661, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 10, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":90,\"leave_type_id\":1,\"year\":2026,\"entitled_days\":\"10.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(662, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 11, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":90,\"leave_type_id\":2,\"year\":2026,\"entitled_days\":\"14.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(663, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 12, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":90,\"leave_type_id\":4,\"year\":2026,\"entitled_days\":\"0.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(664, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 13, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":86,\"leave_type_id\":3,\"year\":2026,\"entitled_days\":\"20.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(665, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 14, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":86,\"leave_type_id\":1,\"year\":2026,\"entitled_days\":\"10.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(666, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 15, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":86,\"leave_type_id\":2,\"year\":2026,\"entitled_days\":\"14.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(667, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 16, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":86,\"leave_type_id\":4,\"year\":2026,\"entitled_days\":\"0.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(668, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 17, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":74,\"leave_type_id\":3,\"year\":2026,\"entitled_days\":\"20.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(669, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 18, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":74,\"leave_type_id\":1,\"year\":2026,\"entitled_days\":\"10.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(670, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 19, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":74,\"leave_type_id\":2,\"year\":2026,\"entitled_days\":\"14.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(671, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 20, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":74,\"leave_type_id\":4,\"year\":2026,\"entitled_days\":\"0.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(672, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 21, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":82,\"leave_type_id\":3,\"year\":2026,\"entitled_days\":\"20.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(673, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 22, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":82,\"leave_type_id\":1,\"year\":2026,\"entitled_days\":\"10.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(674, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 23, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":82,\"leave_type_id\":2,\"year\":2026,\"entitled_days\":\"14.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(675, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 24, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":82,\"leave_type_id\":4,\"year\":2026,\"entitled_days\":\"0.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(676, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 25, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":80,\"leave_type_id\":3,\"year\":2026,\"entitled_days\":\"20.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(677, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 26, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":80,\"leave_type_id\":1,\"year\":2026,\"entitled_days\":\"10.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(678, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 27, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":80,\"leave_type_id\":2,\"year\":2026,\"entitled_days\":\"14.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(679, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 28, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":80,\"leave_type_id\":4,\"year\":2026,\"entitled_days\":\"0.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(680, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 29, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":81,\"leave_type_id\":3,\"year\":2026,\"entitled_days\":\"20.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(681, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 30, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":81,\"leave_type_id\":1,\"year\":2026,\"entitled_days\":\"10.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(682, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 31, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":81,\"leave_type_id\":2,\"year\":2026,\"entitled_days\":\"14.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(683, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 32, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":81,\"leave_type_id\":4,\"year\":2026,\"entitled_days\":\"0.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(684, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 33, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":92,\"leave_type_id\":3,\"year\":2026,\"entitled_days\":\"20.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(685, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 34, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":92,\"leave_type_id\":1,\"year\":2026,\"entitled_days\":\"10.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(686, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 35, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":92,\"leave_type_id\":2,\"year\":2026,\"entitled_days\":\"14.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(687, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 36, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":92,\"leave_type_id\":4,\"year\":2026,\"entitled_days\":\"0.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(688, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 37, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"leave_type_id\":3,\"year\":2026,\"entitled_days\":\"20.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(689, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 38, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"leave_type_id\":1,\"year\":2026,\"entitled_days\":\"10.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(690, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 39, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"leave_type_id\":2,\"year\":2026,\"entitled_days\":\"14.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(691, 'leave_balances', 'created', 'App\\Models\\LeaveBalance', 'created', 40, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":1,\"leave_type_id\":4,\"year\":2026,\"entitled_days\":\"0.0\",\"carried_forward_days\":\"0.0\",\"remarks\":null}}', NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(692, 'leave_requests', 'created', 'App\\Models\\LeaveRequest', 'created', 2, 'App\\Models\\User', 80, '{\"attributes\":{\"user_id\":80,\"leave_type_id\":1,\"from_date\":\"2026-09-14\",\"to_date\":\"2026-09-15\",\"days\":\"2.0\",\"is_half_day\":false,\"reason\":\"Family wedding.\",\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null,\"decision_remarks\":null}}', NULL, '2026-09-09 07:07:08', '2026-09-09 07:07:08'),
(693, 'leave_requests', 'updated', 'App\\Models\\LeaveRequest', 'updated', 2, 'App\\Models\\User', 80, '{\"attributes\":{\"status\":\"cancelled\"},\"old\":{\"status\":\"pending\"}}', NULL, '2026-09-09 07:07:24', '2026-09-09 07:07:24'),
(694, 'leave_requests', 'created', 'App\\Models\\LeaveRequest', 'created', 3, 'App\\Models\\User', 80, '{\"attributes\":{\"user_id\":80,\"leave_type_id\":1,\"from_date\":\"2026-09-28\",\"to_date\":\"2026-09-29\",\"days\":\"2.0\",\"is_half_day\":false,\"reason\":\"Family function out of town.\",\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null,\"decision_remarks\":null}}', NULL, '2026-09-21 05:07:36', '2026-09-21 05:07:36'),
(695, 'leave_requests', 'updated', 'App\\Models\\LeaveRequest', 'updated', 3, 'App\\Models\\User', 1, '{\"attributes\":{\"status\":\"approved\",\"approved_by\":1,\"approved_at\":\"2026-09-21T05:09:47.000000Z\",\"decision_remarks\":\"Approved - cover arranged.\"},\"old\":{\"status\":\"pending\",\"approved_by\":null,\"approved_at\":null,\"decision_remarks\":null}}', NULL, '2026-09-21 05:09:47', '2026-09-21 05:09:47'),
(696, 'attendances', 'created', 'App\\Models\\Attendance', 'created', 12, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":80,\"date\":\"2026-09-28\",\"check_in_at\":null,\"check_in_lat\":null,\"check_in_lng\":null,\"check_in_photo\":null,\"check_out_at\":null,\"check_out_lat\":null,\"check_out_lng\":null,\"check_out_photo\":null,\"status\":\"leave\",\"late_minutes\":0,\"remarks\":\"On approved leave: Casual Leave (request #3).\"}}', NULL, '2026-09-21 05:09:47', '2026-09-21 05:09:47'),
(697, 'attendances', 'created', 'App\\Models\\Attendance', 'created', 13, 'App\\Models\\User', 1, '{\"attributes\":{\"user_id\":80,\"date\":\"2026-09-29\",\"check_in_at\":null,\"check_in_lat\":null,\"check_in_lng\":null,\"check_in_photo\":null,\"check_out_at\":null,\"check_out_lat\":null,\"check_out_lng\":null,\"check_out_photo\":null,\"status\":\"leave\",\"late_minutes\":0,\"remarks\":\"On approved leave: Casual Leave (request #3).\"}}', NULL, '2026-09-21 05:09:47', '2026-09-21 05:09:47'),
(698, 'leave_requests', 'deleted', 'App\\Models\\LeaveRequest', 'deleted', 3, NULL, NULL, '{\"old\":{\"user_id\":80,\"leave_type_id\":1,\"from_date\":\"2026-09-28\",\"to_date\":\"2026-09-29\",\"days\":\"2.0\",\"is_half_day\":false,\"reason\":\"Family function out of town.\",\"status\":\"approved\",\"approved_by\":1,\"approved_at\":\"2026-09-21T05:09:47.000000Z\",\"decision_remarks\":\"Approved - cover arranged.\"}}', NULL, '2026-09-21 05:10:05', '2026-09-21 05:10:05'),
(699, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-21T11:12:08.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-08T09:43:15.000000Z\"}}', NULL, '2026-09-21 11:12:08', '2026-09-21 11:12:08'),
(700, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_successful_sync_at\":\"2026-09-21T11:12:13.000000Z\"},\"old\":{\"last_successful_sync_at\":\"2026-09-08T09:43:15.000000Z\"}}', NULL, '2026-09-21 11:12:13', '2026-09-21 11:12:13'),
(701, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-21T11:13:08.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-21T11:12:08.000000Z\"}}', NULL, '2026-09-21 11:13:08', '2026-09-21 11:13:08'),
(702, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-21T11:14:08.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-21T11:13:08.000000Z\"}}', NULL, '2026-09-21 11:14:08', '2026-09-21 11:14:08'),
(703, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-21T11:15:09.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-21T11:14:08.000000Z\"}}', NULL, '2026-09-21 11:15:09', '2026-09-21 11:15:09');
INSERT INTO `activity_log` (`id`, `log_name`, `description`, `subject_type`, `event`, `subject_id`, `causer_type`, `causer_id`, `properties`, `batch_uuid`, `created_at`, `updated_at`) VALUES
(704, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-21T11:16:08.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-21T11:15:09.000000Z\"}}', NULL, '2026-09-21 11:16:08', '2026-09-21 11:16:08'),
(705, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-21T11:17:08.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-21T11:16:08.000000Z\"}}', NULL, '2026-09-21 11:17:08', '2026-09-21 11:17:08'),
(706, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-21T11:18:08.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-21T11:17:08.000000Z\"}}', NULL, '2026-09-21 11:18:08', '2026-09-21 11:18:08'),
(707, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-21T11:19:08.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-21T11:18:08.000000Z\"}}', NULL, '2026-09-21 11:19:08', '2026-09-21 11:19:08'),
(708, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-21T11:20:08.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-21T11:19:08.000000Z\"}}', NULL, '2026-09-21 11:20:09', '2026-09-21 11:20:09'),
(709, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-21T11:21:08.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-21T11:20:08.000000Z\"}}', NULL, '2026-09-21 11:21:08', '2026-09-21 11:21:08'),
(710, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-21T11:22:08.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-21T11:21:08.000000Z\"}}', NULL, '2026-09-21 11:22:08', '2026-09-21 11:22:08'),
(711, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-21T11:23:08.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-21T11:22:08.000000Z\"}}', NULL, '2026-09-21 11:23:08', '2026-09-21 11:23:08'),
(712, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-21T11:24:08.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-21T11:23:08.000000Z\"}}', NULL, '2026-09-21 11:24:08', '2026-09-21 11:24:08'),
(713, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-21T11:25:08.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-21T11:24:08.000000Z\"}}', NULL, '2026-09-21 11:25:08', '2026-09-21 11:25:08'),
(714, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-21T11:26:08.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-21T11:25:08.000000Z\"}}', NULL, '2026-09-21 11:26:08', '2026-09-21 11:26:08'),
(715, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_successful_sync_at\":\"2026-09-21T11:27:08.000000Z\"},\"old\":{\"last_successful_sync_at\":\"2026-09-21T11:12:13.000000Z\"}}', NULL, '2026-09-21 11:27:08', '2026-09-21 11:27:08'),
(716, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-21T11:27:09.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-21T11:26:08.000000Z\"}}', NULL, '2026-09-21 11:27:09', '2026-09-21 11:27:09'),
(717, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-21T11:28:08.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-21T11:27:09.000000Z\"}}', NULL, '2026-09-21 11:28:08', '2026-09-21 11:28:08'),
(718, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-21T11:29:08.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-21T11:28:08.000000Z\"}}', NULL, '2026-09-21 11:29:08', '2026-09-21 11:29:08'),
(719, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-21T11:30:08.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-21T11:29:08.000000Z\"}}', NULL, '2026-09-21 11:30:09', '2026-09-21 11:30:09'),
(720, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-21T11:31:09.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-21T11:30:08.000000Z\"}}', NULL, '2026-09-21 11:31:09', '2026-09-21 11:31:09'),
(721, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-21T11:32:09.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-21T11:31:09.000000Z\"}}', NULL, '2026-09-21 11:32:09', '2026-09-21 11:32:09'),
(722, 'tally_connections', 'updated', 'App\\Models\\TallyConnection', 'updated', 1, NULL, NULL, '{\"attributes\":{\"last_heartbeat_at\":\"2026-09-21T11:33:09.000000Z\"},\"old\":{\"last_heartbeat_at\":\"2026-09-21T11:32:09.000000Z\"}}', NULL, '2026-09-21 11:33:09', '2026-09-21 11:33:09');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `audience` varchar(255) NOT NULL,
  `audience_role` varchar(255) DEFAULT NULL,
  `audience_territory_id` bigint(20) UNSIGNED DEFAULT NULL,
  `audience_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `audience_dealer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sent_by` bigint(20) UNSIGNED NOT NULL,
  `recipient_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `check_in_at` datetime DEFAULT NULL,
  `check_in_lat` decimal(10,7) DEFAULT NULL,
  `check_in_lng` decimal(10,7) DEFAULT NULL,
  `check_in_photo` varchar(255) DEFAULT NULL,
  `check_out_at` datetime DEFAULT NULL,
  `check_out_lat` decimal(10,7) DEFAULT NULL,
  `check_out_lng` decimal(10,7) DEFAULT NULL,
  `check_out_photo` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `late_minutes` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`id`, `user_id`, `date`, `check_in_at`, `check_in_lat`, `check_in_lng`, `check_in_photo`, `check_out_at`, `check_out_lat`, `check_out_lng`, `check_out_photo`, `status`, `late_minutes`, `remarks`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 80, '2026-08-21', '2026-08-21 09:00:00', 23.7385030, 90.4641500, NULL, '2026-08-21 17:00:00', 23.7234210, 90.4784480, NULL, 'present', 0, NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:34:23', '2026-08-23 05:34:23'),
(2, 81, '2026-08-20', '2026-08-20 09:30:00', 23.7093470, 90.4177140, NULL, '2026-08-20 17:30:00', 23.8309230, 90.3299140, NULL, 'late', 30, NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:34:23', '2026-08-23 05:34:23'),
(3, 80, '2026-08-19', '2026-08-19 09:00:00', 23.7280050, 90.3437740, NULL, '2026-08-19 13:00:00', 23.7169730, 90.4464030, NULL, 'half_day', 0, NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:34:23', '2026-08-23 05:34:23'),
(4, 81, '2026-08-18', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'absent', 0, NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:34:23', '2026-08-23 05:34:23'),
(5, 80, '2026-08-17', '2026-08-17 09:00:00', 23.8366390, 90.4251380, NULL, '2026-08-17 17:00:00', 23.6009540, 90.3522290, NULL, 'present', 0, NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:34:23', '2026-08-23 05:34:23'),
(6, 1, '2026-08-27', '2026-08-27 07:48:44', 23.8103000, 90.4125000, 'attendance/9Xp8xnuXmswVuC0x8ibDIpWtsM54nLh47Os3QGaw.jpg', '2026-08-27 07:48:45', 23.8110000, 90.4130000, 'attendance/yQsWGbi0SywCGp8vIicLpEwNypkmUv43ykkCinuQ.jpg', 'half_day', 0, NULL, 1, 1, NULL, NULL, '2026-08-27 01:48:44', '2026-08-27 01:48:45'),
(7, 89, '2026-08-30', '2026-08-30 09:00:00', 23.8159530, 90.4380430, NULL, '2026-08-30 17:00:00', 23.8911820, 90.4200300, NULL, 'present', 0, NULL, NULL, NULL, NULL, NULL, '2026-08-30 04:52:19', '2026-08-30 04:52:19'),
(8, 90, '2026-08-30', '2026-08-30 09:00:00', 23.7901490, 90.4300580, NULL, '2026-08-30 17:00:00', 23.6703710, 90.3772890, NULL, 'present', 0, NULL, NULL, NULL, NULL, NULL, '2026-08-30 04:53:16', '2026-08-30 04:53:16'),
(9, 80, '2026-08-30', '2026-08-30 09:02:00', NULL, NULL, NULL, '2026-08-30 18:05:00', NULL, NULL, NULL, 'present', 0, NULL, NULL, NULL, NULL, NULL, '2026-08-30 04:55:32', '2026-08-30 04:55:32'),
(10, 80, '2026-09-14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'leave', 0, 'On approved Casual Leave leave (request #1).', 1, NULL, NULL, '2026-09-09 04:19:04', '2026-09-09 04:17:50', '2026-09-09 04:19:04'),
(11, 80, '2026-09-15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'leave', 0, 'On approved Casual Leave leave (request #1).', 1, NULL, NULL, '2026-09-09 04:19:04', '2026-09-09 04:17:50', '2026-09-09 04:19:04'),
(12, 80, '2026-09-28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'leave', 0, 'On approved leave: Casual Leave (request #3).', 1, NULL, NULL, '2026-09-21 05:10:05', '2026-09-21 05:09:47', '2026-09-21 05:10:05'),
(13, 80, '2026-09-29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'leave', 0, 'On approved leave: Casual Leave (request #3).', 1, NULL, NULL, '2026-09-21 05:10:05', '2026-09-21 05:09:47', '2026-09-21 05:10:05');

-- --------------------------------------------------------

--
-- Table structure for table `brochures`
--

CREATE TABLE `brochures` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('gazi-pump-sfa-cache-5c785c036466adea360111aa28563bfd556b5fba', 'i:2;', 1787822322),
('gazi-pump-sfa-cache-5c785c036466adea360111aa28563bfd556b5fba:timer', 'i:1787822322;', 1787822322),
('gazi-pump-sfa-cache-77a194dd605fb986af6290f739b82dc3', 'i:8;', 1788937677),
('gazi-pump-sfa-cache-77a194dd605fb986af6290f739b82dc3:timer', 'i:1788937677;', 1788937677),
('gazi-pump-sfa-cache-a75f3f172bfb296f2e10cbfc6dfc1883', 'i:1;', 1788846868),
('gazi-pump-sfa-cache-a75f3f172bfb296f2e10cbfc6dfc1883:timer', 'i:1788846868;', 1788846868),
('gazi-pump-sfa-cache-admin@example.com|127.0.0.1', 'i:1;', 1788149056),
('gazi-pump-sfa-cache-admin@example.com|127.0.0.1:timer', 'i:1788149056;', 1788149056),
('gazi-pump-sfa-cache-d189a3df25facd3522214377f5876bcb', 'i:3;', 1789990449),
('gazi-pump-sfa-cache-d189a3df25facd3522214377f5876bcb:timer', 'i:1789990449;', 1789990449),
('gazi-pump-sfa-cache-f1f70ec40aaa556905d4a030501c0ba4', 'i:2;', 1788846923),
('gazi-pump-sfa-cache-f1f70ec40aaa556905d4a030501c0ba4:timer', 'i:1788846923;', 1788846923),
('gazi-pump-sfa-cache-gm@gazipump.com|::1', 'i:1;', 1789967377),
('gazi-pump-sfa-cache-gm@gazipump.com|::1:timer', 'i:1789967377;', 1789967377),
('gazi-pump-sfa-cache-notifications.recent.6.user.1', 'O:55:\"Illuminate\\Notifications\\DatabaseNotificationCollection\":2:{s:8:\"\0*\0items\";a:1:{i:0;O:45:\"Illuminate\\Notifications\\DatabaseNotification\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:13:\"notifications\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:6:\"string\";s:12:\"incrementing\";b:0;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:8:{s:2:\"id\";s:36:\"dab3cdaa-dec6-44c6-a0d8-3f784666c003\";s:4:\"type\";s:42:\"App\\Notifications\\AnnouncementNotification\";s:15:\"notifiable_type\";s:15:\"App\\Models\\User\";s:13:\"notifiable_id\";i:1;s:4:\"data\";s:289:\"{\"type\":\"announcement\",\"title\":\"Postman verification\",\"message\":\"Et magnam quas autem architecto neque rem in eligendi. Tenetur praesentium dolorem a fuga ea. Fugiat voluptatem repudiandae soluta sed id qui. Facilis quia ut nesciunt.\",\"announcement_id\":null,\"sent_by\":\"Everett Kuvalis II\"}\";s:7:\"read_at\";s:19:\"2026-08-27 07:49:51\";s:10:\"created_at\";s:19:\"2026-08-27 07:49:25\";s:10:\"updated_at\";s:19:\"2026-08-27 07:49:51\";}s:11:\"\0*\0original\";a:8:{s:2:\"id\";s:36:\"dab3cdaa-dec6-44c6-a0d8-3f784666c003\";s:4:\"type\";s:42:\"App\\Notifications\\AnnouncementNotification\";s:15:\"notifiable_type\";s:15:\"App\\Models\\User\";s:13:\"notifiable_id\";i:1;s:4:\"data\";s:289:\"{\"type\":\"announcement\",\"title\":\"Postman verification\",\"message\":\"Et magnam quas autem architecto neque rem in eligendi. Tenetur praesentium dolorem a fuga ea. Fugiat voluptatem repudiandae soluta sed id qui. Facilis quia ut nesciunt.\",\"announcement_id\":null,\"sent_by\":\"Everett Kuvalis II\"}\";s:7:\"read_at\";s:19:\"2026-08-27 07:49:51\";s:10:\"created_at\";s:19:\"2026-08-27 07:49:25\";s:10:\"updated_at\";s:19:\"2026-08-27 07:49:51\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:2:{s:4:\"data\";s:5:\"array\";s:7:\"read_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:0:{}s:10:\"\0*\0guarded\";a:0:{}}}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}', 1789990938),
('gazi-pump-sfa-cache-notifications.recent.6.user.80', 'O:55:\"Illuminate\\Notifications\\DatabaseNotificationCollection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}', 1789988755),
('gazi-pump-sfa-cache-notifications.recent.6.user.87', 'O:55:\"Illuminate\\Notifications\\DatabaseNotificationCollection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}', 1788072086),
('gazi-pump-sfa-cache-notifications.recent.6.user.88', 'O:55:\"Illuminate\\Notifications\\DatabaseNotificationCollection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}', 1788074229),
('gazi-pump-sfa-cache-notifications.recent.6.user.93', 'O:55:\"Illuminate\\Notifications\\DatabaseNotificationCollection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}', 1788157233),
('gazi-pump-sfa-cache-notifications.unread_count.user.1', 'i:0;', 1789990938),
('gazi-pump-sfa-cache-notifications.unread_count.user.80', 'i:0;', 1789988755),
('gazi-pump-sfa-cache-notifications.unread_count.user.87', 'i:0;', 1788072086),
('gazi-pump-sfa-cache-notifications.unread_count.user.88', 'i:0;', 1788074229),
('gazi-pump-sfa-cache-notifications.unread_count.user.93', 'i:0;', 1788157233),
('gazi-pump-sfa-cache-portal.brochures.index', 'O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}', 2104211417),
('gazi-pump-sfa-cache-portal.service-centers.index', 'O:39:\"Illuminate\\Database\\Eloquent\\Collection\":2:{s:8:\"\0*\0items\";a:0:{}s:28:\"\0*\0escapeWhenCastingToString\";b:0;}', 2104211418),
('gazi-pump-sfa-cache-settings.current', 'O:18:\"App\\Models\\Setting\":36:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:8:\"settings\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:36:{s:2:\"id\";i:1;s:12:\"company_name\";s:14:\"Gazi Group SFA\";s:12:\"company_logo\";s:53:\"settings/vKmKYVjGFsZxNpZQN68KHSgQmp62nBks7YjjcW04.png\";s:15:\"company_favicon\";s:53:\"settings/EmqVOJsKAs9g0DDmRQ5XVpVY2MTO221U3wehG7qx.png\";s:15:\"company_address\";s:51:\"37/2, Pritom Zaman Tower, Purana Paltan, Dhaka-1000\";s:13:\"company_phone\";s:11:\"01958538607\";s:13:\"company_email\";s:17:\"info@gcart.com.bd\";s:28:\"attendance_office_start_time\";s:5:\"09:00\";s:26:\"attendance_office_end_time\";s:5:\"18:00\";s:29:\"attendance_late_grace_minutes\";i:15;s:23:\"attendance_weekend_days\";s:21:\"[\"Friday\",\"Saturday\"]\";s:23:\"visit_gps_radius_meters\";i:300;s:26:\"order_max_discount_percent\";s:5:\"20.00\";s:40:\"collection_overpayment_tolerance_percent\";s:5:\"10.00\";s:18:\"target_grade_a_min\";i:90;s:18:\"target_grade_b_min\";i:75;s:18:\"target_grade_c_min\";i:60;s:18:\"target_grade_d_min\";i:40;s:22:\"low_performance_grades\";s:9:\"[\"D\",\"F\"]\";s:37:\"target_reminder_days_before_month_end\";i:5;s:23:\"target_reminder_min_pct\";s:5:\"70.00\";s:28:\"live_gps_stale_after_minutes\";i:30;s:10:\"created_by\";N;s:10:\"updated_by\";i:1;s:10:\"deleted_by\";N;s:10:\"deleted_at\";N;s:10:\"created_at\";s:19:\"2026-08-06 10:39:49\";s:10:\"updated_at\";s:19:\"2026-08-24 06:32:30\";s:19:\"sms_gateway_enabled\";i:0;s:20:\"sms_gateway_provider\";N;s:19:\"sms_gateway_api_url\";N;s:19:\"sms_gateway_api_key\";N;s:21:\"sms_gateway_sender_id\";N;s:11:\"sms_channel\";s:3:\"sms\";s:29:\"collection_otp_expiry_minutes\";i:10;s:23:\"cash_daily_limit_amount\";N;}s:11:\"\0*\0original\";a:36:{s:2:\"id\";i:1;s:12:\"company_name\";s:14:\"Gazi Group SFA\";s:12:\"company_logo\";s:53:\"settings/vKmKYVjGFsZxNpZQN68KHSgQmp62nBks7YjjcW04.png\";s:15:\"company_favicon\";s:53:\"settings/EmqVOJsKAs9g0DDmRQ5XVpVY2MTO221U3wehG7qx.png\";s:15:\"company_address\";s:51:\"37/2, Pritom Zaman Tower, Purana Paltan, Dhaka-1000\";s:13:\"company_phone\";s:11:\"01958538607\";s:13:\"company_email\";s:17:\"info@gcart.com.bd\";s:28:\"attendance_office_start_time\";s:5:\"09:00\";s:26:\"attendance_office_end_time\";s:5:\"18:00\";s:29:\"attendance_late_grace_minutes\";i:15;s:23:\"attendance_weekend_days\";s:21:\"[\"Friday\",\"Saturday\"]\";s:23:\"visit_gps_radius_meters\";i:300;s:26:\"order_max_discount_percent\";s:5:\"20.00\";s:40:\"collection_overpayment_tolerance_percent\";s:5:\"10.00\";s:18:\"target_grade_a_min\";i:90;s:18:\"target_grade_b_min\";i:75;s:18:\"target_grade_c_min\";i:60;s:18:\"target_grade_d_min\";i:40;s:22:\"low_performance_grades\";s:9:\"[\"D\",\"F\"]\";s:37:\"target_reminder_days_before_month_end\";i:5;s:23:\"target_reminder_min_pct\";s:5:\"70.00\";s:28:\"live_gps_stale_after_minutes\";i:30;s:10:\"created_by\";N;s:10:\"updated_by\";i:1;s:10:\"deleted_by\";N;s:10:\"deleted_at\";N;s:10:\"created_at\";s:19:\"2026-08-06 10:39:49\";s:10:\"updated_at\";s:19:\"2026-08-24 06:32:30\";s:19:\"sms_gateway_enabled\";i:0;s:20:\"sms_gateway_provider\";N;s:19:\"sms_gateway_api_url\";N;s:19:\"sms_gateway_api_key\";N;s:21:\"sms_gateway_sender_id\";N;s:11:\"sms_channel\";s:3:\"sms\";s:29:\"collection_otp_expiry_minutes\";i:10;s:23:\"cash_daily_limit_amount\";N;}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:18:{s:29:\"attendance_late_grace_minutes\";s:7:\"integer\";s:23:\"attendance_weekend_days\";s:5:\"array\";s:23:\"visit_gps_radius_meters\";s:7:\"integer\";s:26:\"order_max_discount_percent\";s:9:\"decimal:2\";s:40:\"collection_overpayment_tolerance_percent\";s:9:\"decimal:2\";s:18:\"target_grade_a_min\";s:7:\"integer\";s:18:\"target_grade_b_min\";s:7:\"integer\";s:18:\"target_grade_c_min\";s:7:\"integer\";s:18:\"target_grade_d_min\";s:7:\"integer\";s:22:\"low_performance_grades\";s:5:\"array\";s:37:\"target_reminder_days_before_month_end\";s:7:\"integer\";s:23:\"target_reminder_min_pct\";s:9:\"decimal:2\";s:28:\"live_gps_stale_after_minutes\";s:7:\"integer\";s:19:\"sms_gateway_enabled\";s:7:\"boolean\";s:11:\"sms_channel\";s:20:\"App\\Enums\\SmsChannel\";s:29:\"collection_otp_expiry_minutes\";s:7:\"integer\";s:23:\"cash_daily_limit_amount\";s:9:\"decimal:2\";s:10:\"deleted_at\";s:8:\"datetime\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:29:{i:0;s:12:\"company_name\";i:1;s:12:\"company_logo\";i:2;s:15:\"company_favicon\";i:3;s:15:\"company_address\";i:4;s:13:\"company_phone\";i:5;s:13:\"company_email\";i:6;s:28:\"attendance_office_start_time\";i:7;s:26:\"attendance_office_end_time\";i:8;s:29:\"attendance_late_grace_minutes\";i:9;s:23:\"attendance_weekend_days\";i:10;s:23:\"visit_gps_radius_meters\";i:11;s:26:\"order_max_discount_percent\";i:12;s:40:\"collection_overpayment_tolerance_percent\";i:13;s:18:\"target_grade_a_min\";i:14;s:18:\"target_grade_b_min\";i:15;s:18:\"target_grade_c_min\";i:16;s:18:\"target_grade_d_min\";i:17;s:22:\"low_performance_grades\";i:18;s:37:\"target_reminder_days_before_month_end\";i:19;s:23:\"target_reminder_min_pct\";i:20;s:28:\"live_gps_stale_after_minutes\";i:21;s:19:\"sms_gateway_enabled\";i:22;s:20:\"sms_gateway_provider\";i:23;s:19:\"sms_gateway_api_url\";i:24;s:19:\"sms_gateway_api_key\";i:25;s:21:\"sms_gateway_sender_id\";i:26;s:11:\"sms_channel\";i:27;s:29:\"collection_otp_expiry_minutes\";i:28;s:23:\"cash_daily_limit_amount\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}s:16:\"\0*\0oldAttributes\";a:0:{}s:25:\"enableLoggingModelsEvents\";b:1;s:16:\"\0*\0forceDeleting\";b:0;}', 2103180956);
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('gazi-pump-sfa-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:486:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:14:\"menu.dashboard\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:10:\"menu.users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:10:\"users.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:9:\"users.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:10:\"users.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:12:\"users.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:13:\"users.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:12:\"users.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:11:\"users.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:12:\"users.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:13:\"users.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:14:\"api.users.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:10:\"menu.roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:10:\"roles.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:9:\"roles.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:10:\"roles.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:12:\"roles.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:13:\"roles.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:12:\"roles.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:11:\"roles.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:12:\"roles.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:21;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:13:\"roles.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:14:\"api.roles.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:23;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:16:\"menu.permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:24;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:16:\"permissions.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:25;a:4:{s:1:\"a\";i:26;s:1:\"b\";s:15:\"permissions.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:26;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:16:\"permissions.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:27;a:4:{s:1:\"a\";i:28;s:1:\"b\";s:18:\"permissions.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:28;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:19:\"permissions.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:29;a:4:{s:1:\"a\";i:30;s:1:\"b\";s:18:\"permissions.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:30;a:4:{s:1:\"a\";i:31;s:1:\"b\";s:17:\"permissions.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:31;a:4:{s:1:\"a\";i:32;s:1:\"b\";s:18:\"permissions.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:32;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:19:\"permissions.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:33;a:4:{s:1:\"a\";i:34;s:1:\"b\";s:16:\"menu.sales-teams\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:34;a:4:{s:1:\"a\";i:35;s:1:\"b\";s:16:\"sales-teams.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:35;a:4:{s:1:\"a\";i:36;s:1:\"b\";s:15:\"sales-teams.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:36;a:4:{s:1:\"a\";i:37;s:1:\"b\";s:16:\"sales-teams.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:37;a:4:{s:1:\"a\";i:38;s:1:\"b\";s:18:\"sales-teams.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:38;a:4:{s:1:\"a\";i:39;s:1:\"b\";s:19:\"sales-teams.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:39;a:4:{s:1:\"a\";i:40;s:1:\"b\";s:18:\"sales-teams.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:40;a:4:{s:1:\"a\";i:41;s:1:\"b\";s:17:\"sales-teams.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:41;a:4:{s:1:\"a\";i:42;s:1:\"b\";s:18:\"sales-teams.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:42;a:4:{s:1:\"a\";i:43;s:1:\"b\";s:19:\"sales-teams.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:43;a:4:{s:1:\"a\";i:44;s:1:\"b\";s:20:\"api.sales-teams.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:44;a:4:{s:1:\"a\";i:45;s:1:\"b\";s:16:\"menu.territories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:45;a:4:{s:1:\"a\";i:46;s:1:\"b\";s:16:\"territories.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:46;a:4:{s:1:\"a\";i:47;s:1:\"b\";s:15:\"territories.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:47;a:4:{s:1:\"a\";i:48;s:1:\"b\";s:16:\"territories.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:48;a:4:{s:1:\"a\";i:49;s:1:\"b\";s:18:\"territories.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:49;a:4:{s:1:\"a\";i:50;s:1:\"b\";s:19:\"territories.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:50;a:4:{s:1:\"a\";i:51;s:1:\"b\";s:18:\"territories.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:51;a:4:{s:1:\"a\";i:52;s:1:\"b\";s:17:\"territories.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:52;a:4:{s:1:\"a\";i:53;s:1:\"b\";s:18:\"territories.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:53;a:4:{s:1:\"a\";i:54;s:1:\"b\";s:19:\"territories.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:54;a:4:{s:1:\"a\";i:55;s:1:\"b\";s:20:\"api.territories.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:55;a:4:{s:1:\"a\";i:56;s:1:\"b\";s:12:\"menu.dealers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:56;a:4:{s:1:\"a\";i:57;s:1:\"b\";s:12:\"dealers.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:57;a:4:{s:1:\"a\";i:58;s:1:\"b\";s:11:\"dealers.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:58;a:4:{s:1:\"a\";i:59;s:1:\"b\";s:12:\"dealers.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:59;a:4:{s:1:\"a\";i:60;s:1:\"b\";s:14:\"dealers.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:60;a:4:{s:1:\"a\";i:61;s:1:\"b\";s:15:\"dealers.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:61;a:4:{s:1:\"a\";i:62;s:1:\"b\";s:14:\"dealers.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:62;a:4:{s:1:\"a\";i:63;s:1:\"b\";s:13:\"dealers.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:63;a:4:{s:1:\"a\";i:64;s:1:\"b\";s:14:\"dealers.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:64;a:4:{s:1:\"a\";i:65;s:1:\"b\";s:15:\"dealers.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:65;a:4:{s:1:\"a\";i:66;s:1:\"b\";s:16:\"api.dealers.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:66;a:4:{s:1:\"a\";i:67;s:1:\"b\";s:15:\"api.dealers.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:67;a:4:{s:1:\"a\";i:68;s:1:\"b\";s:23:\"menu.product-categories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:68;a:4:{s:1:\"a\";i:69;s:1:\"b\";s:23:\"product-categories.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:69;a:4:{s:1:\"a\";i:70;s:1:\"b\";s:22:\"product-categories.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:70;a:4:{s:1:\"a\";i:71;s:1:\"b\";s:23:\"product-categories.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:71;a:4:{s:1:\"a\";i:72;s:1:\"b\";s:25:\"product-categories.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:72;a:4:{s:1:\"a\";i:73;s:1:\"b\";s:26:\"product-categories.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:73;a:4:{s:1:\"a\";i:74;s:1:\"b\";s:25:\"product-categories.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:74;a:4:{s:1:\"a\";i:75;s:1:\"b\";s:24:\"product-categories.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:75;a:4:{s:1:\"a\";i:76;s:1:\"b\";s:25:\"product-categories.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:76;a:4:{s:1:\"a\";i:77;s:1:\"b\";s:26:\"product-categories.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:77;a:4:{s:1:\"a\";i:78;s:1:\"b\";s:27:\"api.product-categories.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:78;a:4:{s:1:\"a\";i:79;s:1:\"b\";s:13:\"menu.products\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:79;a:4:{s:1:\"a\";i:80;s:1:\"b\";s:13:\"products.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:80;a:4:{s:1:\"a\";i:81;s:1:\"b\";s:12:\"products.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:81;a:4:{s:1:\"a\";i:82;s:1:\"b\";s:13:\"products.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:82;a:4:{s:1:\"a\";i:83;s:1:\"b\";s:15:\"products.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:83;a:4:{s:1:\"a\";i:84;s:1:\"b\";s:16:\"products.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:84;a:4:{s:1:\"a\";i:85;s:1:\"b\";s:15:\"products.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:85;a:4:{s:1:\"a\";i:86;s:1:\"b\";s:14:\"products.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:86;a:4:{s:1:\"a\";i:87;s:1:\"b\";s:15:\"products.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:87;a:4:{s:1:\"a\";i:88;s:1:\"b\";s:16:\"products.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:88;a:4:{s:1:\"a\";i:89;s:1:\"b\";s:17:\"api.products.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:89;a:4:{s:1:\"a\";i:90;s:1:\"b\";s:15:\"menu.attendance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:90;a:4:{s:1:\"a\";i:91;s:1:\"b\";s:15:\"attendance.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:91;a:4:{s:1:\"a\";i:92;s:1:\"b\";s:14:\"attendance.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:92;a:4:{s:1:\"a\";i:93;s:1:\"b\";s:15:\"attendance.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:93;a:4:{s:1:\"a\";i:94;s:1:\"b\";s:17:\"attendance.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:94;a:4:{s:1:\"a\";i:95;s:1:\"b\";s:18:\"attendance.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:95;a:4:{s:1:\"a\";i:96;s:1:\"b\";s:17:\"attendance.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:96;a:4:{s:1:\"a\";i:97;s:1:\"b\";s:16:\"attendance.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:97;a:4:{s:1:\"a\";i:98;s:1:\"b\";s:17:\"attendance.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:98;a:4:{s:1:\"a\";i:99;s:1:\"b\";s:18:\"attendance.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:99;a:4:{s:1:\"a\";i:100;s:1:\"b\";s:19:\"api.attendance.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:100;a:4:{s:1:\"a\";i:101;s:1:\"b\";s:18:\"api.attendance.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:101;a:4:{s:1:\"a\";i:102;s:1:\"b\";s:13:\"menu.gps-logs\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:102;a:4:{s:1:\"a\";i:103;s:1:\"b\";s:13:\"gps-logs.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:103;a:4:{s:1:\"a\";i:104;s:1:\"b\";s:12:\"gps-logs.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:104;a:4:{s:1:\"a\";i:105;s:1:\"b\";s:13:\"gps-logs.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:105;a:4:{s:1:\"a\";i:106;s:1:\"b\";s:15:\"gps-logs.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:106;a:4:{s:1:\"a\";i:107;s:1:\"b\";s:16:\"gps-logs.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:107;a:4:{s:1:\"a\";i:108;s:1:\"b\";s:15:\"gps-logs.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:108;a:4:{s:1:\"a\";i:109;s:1:\"b\";s:14:\"gps-logs.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:109;a:4:{s:1:\"a\";i:110;s:1:\"b\";s:15:\"gps-logs.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:110;a:4:{s:1:\"a\";i:111;s:1:\"b\";s:16:\"gps-logs.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:111;a:4:{s:1:\"a\";i:112;s:1:\"b\";s:17:\"api.gps-logs.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:112;a:4:{s:1:\"a\";i:113;s:1:\"b\";s:16:\"api.gps-logs.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:113;a:4:{s:1:\"a\";i:114;s:1:\"b\";s:16:\"menu.visit-plans\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:114;a:4:{s:1:\"a\";i:115;s:1:\"b\";s:16:\"visit-plans.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:115;a:4:{s:1:\"a\";i:116;s:1:\"b\";s:15:\"visit-plans.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:116;a:4:{s:1:\"a\";i:117;s:1:\"b\";s:16:\"visit-plans.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:117;a:4:{s:1:\"a\";i:118;s:1:\"b\";s:18:\"visit-plans.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:118;a:4:{s:1:\"a\";i:119;s:1:\"b\";s:19:\"visit-plans.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:119;a:4:{s:1:\"a\";i:120;s:1:\"b\";s:18:\"visit-plans.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:120;a:4:{s:1:\"a\";i:121;s:1:\"b\";s:17:\"visit-plans.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:121;a:4:{s:1:\"a\";i:122;s:1:\"b\";s:18:\"visit-plans.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:122;a:4:{s:1:\"a\";i:123;s:1:\"b\";s:19:\"visit-plans.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:123;a:4:{s:1:\"a\";i:124;s:1:\"b\";s:20:\"api.visit-plans.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:124;a:4:{s:1:\"a\";i:125;s:1:\"b\";s:19:\"api.visit-plans.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:125;a:4:{s:1:\"a\";i:126;s:1:\"b\";s:11:\"menu.visits\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:126;a:4:{s:1:\"a\";i:127;s:1:\"b\";s:11:\"visits.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:127;a:4:{s:1:\"a\";i:128;s:1:\"b\";s:10:\"visits.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:128;a:4:{s:1:\"a\";i:129;s:1:\"b\";s:11:\"visits.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:129;a:4:{s:1:\"a\";i:130;s:1:\"b\";s:13:\"visits.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:130;a:4:{s:1:\"a\";i:131;s:1:\"b\";s:14:\"visits.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:131;a:4:{s:1:\"a\";i:132;s:1:\"b\";s:13:\"visits.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:132;a:4:{s:1:\"a\";i:133;s:1:\"b\";s:12:\"visits.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:133;a:4:{s:1:\"a\";i:134;s:1:\"b\";s:13:\"visits.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:134;a:4:{s:1:\"a\";i:135;s:1:\"b\";s:14:\"visits.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:135;a:4:{s:1:\"a\";i:136;s:1:\"b\";s:15:\"api.visits.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:136;a:4:{s:1:\"a\";i:137;s:1:\"b\";s:14:\"api.visits.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:137;a:4:{s:1:\"a\";i:138;s:1:\"b\";s:11:\"menu.orders\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:138;a:4:{s:1:\"a\";i:139;s:1:\"b\";s:11:\"orders.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:139;a:4:{s:1:\"a\";i:140;s:1:\"b\";s:10:\"orders.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:6;}}i:140;a:4:{s:1:\"a\";i:141;s:1:\"b\";s:11:\"orders.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:141;a:4:{s:1:\"a\";i:142;s:1:\"b\";s:13:\"orders.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:142;a:4:{s:1:\"a\";i:143;s:1:\"b\";s:14:\"orders.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:143;a:4:{s:1:\"a\";i:144;s:1:\"b\";s:13:\"orders.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:144;a:4:{s:1:\"a\";i:145;s:1:\"b\";s:12:\"orders.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:145;a:4:{s:1:\"a\";i:146;s:1:\"b\";s:13:\"orders.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:146;a:4:{s:1:\"a\";i:147;s:1:\"b\";s:14:\"orders.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:147;a:4:{s:1:\"a\";i:148;s:1:\"b\";s:15:\"api.orders.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:148;a:4:{s:1:\"a\";i:149;s:1:\"b\";s:14:\"api.orders.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:149;a:4:{s:1:\"a\";i:150;s:1:\"b\";s:23:\"menu.collection-entries\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:150;a:4:{s:1:\"a\";i:151;s:1:\"b\";s:23:\"collection-entries.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:151;a:4:{s:1:\"a\";i:152;s:1:\"b\";s:22:\"collection-entries.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:6;}}i:152;a:4:{s:1:\"a\";i:153;s:1:\"b\";s:23:\"collection-entries.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:153;a:4:{s:1:\"a\";i:154;s:1:\"b\";s:25:\"collection-entries.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:154;a:4:{s:1:\"a\";i:155;s:1:\"b\";s:26:\"collection-entries.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:155;a:4:{s:1:\"a\";i:156;s:1:\"b\";s:25:\"collection-entries.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:156;a:4:{s:1:\"a\";i:157;s:1:\"b\";s:24:\"collection-entries.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:157;a:4:{s:1:\"a\";i:158;s:1:\"b\";s:25:\"collection-entries.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:158;a:4:{s:1:\"a\";i:159;s:1:\"b\";s:26:\"collection-entries.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:159;a:4:{s:1:\"a\";i:160;s:1:\"b\";s:27:\"api.collection-entries.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:160;a:4:{s:1:\"a\";i:161;s:1:\"b\";s:26:\"api.collection-entries.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:161;a:4:{s:1:\"a\";i:162;s:1:\"b\";s:12:\"menu.targets\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:162;a:4:{s:1:\"a\";i:163;s:1:\"b\";s:12:\"targets.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:163;a:4:{s:1:\"a\";i:164;s:1:\"b\";s:11:\"targets.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:164;a:4:{s:1:\"a\";i:165;s:1:\"b\";s:12:\"targets.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:165;a:4:{s:1:\"a\";i:166;s:1:\"b\";s:14:\"targets.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:166;a:4:{s:1:\"a\";i:167;s:1:\"b\";s:15:\"targets.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:167;a:4:{s:1:\"a\";i:168;s:1:\"b\";s:14:\"targets.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:168;a:4:{s:1:\"a\";i:169;s:1:\"b\";s:13:\"targets.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:169;a:4:{s:1:\"a\";i:170;s:1:\"b\";s:14:\"targets.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:170;a:4:{s:1:\"a\";i:171;s:1:\"b\";s:15:\"targets.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:171;a:4:{s:1:\"a\";i:172;s:1:\"b\";s:16:\"api.targets.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:172;a:4:{s:1:\"a\";i:173;s:1:\"b\";s:17:\"report.attendance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:173;a:4:{s:1:\"a\";i:174;s:1:\"b\";s:13:\"report.visits\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:174;a:4:{s:1:\"a\";i:175;s:1:\"b\";s:24:\"report.order-performance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:175;a:4:{s:1:\"a\";i:176;s:1:\"b\";s:18:\"report.collections\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:176;a:4:{s:1:\"a\";i:177;s:1:\"b\";s:18:\"report.territories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:177;a:4:{s:1:\"a\";i:178;s:1:\"b\";s:25:\"report.target-achievement\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:178;a:4:{s:1:\"a\";i:179;s:1:\"b\";s:28:\"report.executive-performance\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:179;a:4:{s:1:\"a\";i:180;s:1:\"b\";s:22:\"report.dealer-coverage\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:180;a:4:{s:1:\"a\";i:181;s:1:\"b\";s:10:\"report.gps\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:181;a:4:{s:1:\"a\";i:182;s:1:\"b\";s:18:\"menu.notifications\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:182;a:4:{s:1:\"a\";i:183;s:1:\"b\";s:18:\"notifications.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:183;a:4:{s:1:\"a\";i:184;s:1:\"b\";s:17:\"notifications.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:184;a:4:{s:1:\"a\";i:185;s:1:\"b\";s:18:\"notifications.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:185;a:4:{s:1:\"a\";i:186;s:1:\"b\";s:20:\"notifications.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:186;a:4:{s:1:\"a\";i:187;s:1:\"b\";s:21:\"notifications.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:187;a:4:{s:1:\"a\";i:188;s:1:\"b\";s:20:\"notifications.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:188;a:4:{s:1:\"a\";i:189;s:1:\"b\";s:19:\"notifications.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:189;a:4:{s:1:\"a\";i:190;s:1:\"b\";s:20:\"notifications.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:190;a:4:{s:1:\"a\";i:191;s:1:\"b\";s:21:\"notifications.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:191;a:4:{s:1:\"a\";i:192;s:1:\"b\";s:22:\"api.notifications.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:192;a:4:{s:1:\"a\";i:193;s:1:\"b\";s:18:\"menu.announcements\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:193;a:4:{s:1:\"a\";i:194;s:1:\"b\";s:18:\"announcements.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:194;a:4:{s:1:\"a\";i:195;s:1:\"b\";s:17:\"announcements.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:195;a:4:{s:1:\"a\";i:196;s:1:\"b\";s:18:\"announcements.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:196;a:4:{s:1:\"a\";i:197;s:1:\"b\";s:20:\"announcements.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:197;a:4:{s:1:\"a\";i:198;s:1:\"b\";s:21:\"announcements.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:198;a:4:{s:1:\"a\";i:199;s:1:\"b\";s:20:\"announcements.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:199;a:4:{s:1:\"a\";i:200;s:1:\"b\";s:19:\"announcements.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:200;a:4:{s:1:\"a\";i:201;s:1:\"b\";s:20:\"announcements.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:201;a:4:{s:1:\"a\";i:202;s:1:\"b\";s:21:\"announcements.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:202;a:4:{s:1:\"a\";i:203;s:1:\"b\";s:17:\"menu.activity-log\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:203;a:4:{s:1:\"a\";i:204;s:1:\"b\";s:17:\"activity-log.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:204;a:4:{s:1:\"a\";i:205;s:1:\"b\";s:16:\"activity-log.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:205;a:4:{s:1:\"a\";i:206;s:1:\"b\";s:17:\"activity-log.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:206;a:4:{s:1:\"a\";i:207;s:1:\"b\";s:19:\"activity-log.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:207;a:4:{s:1:\"a\";i:208;s:1:\"b\";s:20:\"activity-log.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:208;a:4:{s:1:\"a\";i:209;s:1:\"b\";s:19:\"activity-log.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:209;a:4:{s:1:\"a\";i:210;s:1:\"b\";s:18:\"activity-log.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:210;a:4:{s:1:\"a\";i:211;s:1:\"b\";s:19:\"activity-log.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:211;a:4:{s:1:\"a\";i:212;s:1:\"b\";s:20:\"activity-log.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:212;a:4:{s:1:\"a\";i:213;s:1:\"b\";s:18:\"menu.territory-map\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:213;a:4:{s:1:\"a\";i:214;s:1:\"b\";s:18:\"territory-map.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:214;a:4:{s:1:\"a\";i:215;s:1:\"b\";s:17:\"territory-map.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:215;a:4:{s:1:\"a\";i:216;s:1:\"b\";s:18:\"territory-map.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:216;a:4:{s:1:\"a\";i:217;s:1:\"b\";s:20:\"territory-map.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:217;a:4:{s:1:\"a\";i:218;s:1:\"b\";s:21:\"territory-map.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:218;a:4:{s:1:\"a\";i:219;s:1:\"b\";s:20:\"territory-map.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:219;a:4:{s:1:\"a\";i:220;s:1:\"b\";s:19:\"territory-map.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:220;a:4:{s:1:\"a\";i:221;s:1:\"b\";s:20:\"territory-map.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:221;a:4:{s:1:\"a\";i:222;s:1:\"b\";s:21:\"territory-map.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:222;a:4:{s:1:\"a\";i:223;s:1:\"b\";s:13:\"menu.live-gps\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:223;a:4:{s:1:\"a\";i:224;s:1:\"b\";s:13:\"live-gps.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:224;a:4:{s:1:\"a\";i:225;s:1:\"b\";s:12:\"live-gps.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:225;a:4:{s:1:\"a\";i:226;s:1:\"b\";s:13:\"live-gps.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:226;a:4:{s:1:\"a\";i:227;s:1:\"b\";s:15:\"live-gps.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:227;a:4:{s:1:\"a\";i:228;s:1:\"b\";s:16:\"live-gps.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:228;a:4:{s:1:\"a\";i:229;s:1:\"b\";s:15:\"live-gps.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:229;a:4:{s:1:\"a\";i:230;s:1:\"b\";s:14:\"live-gps.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:230;a:4:{s:1:\"a\";i:231;s:1:\"b\";s:15:\"live-gps.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:231;a:4:{s:1:\"a\";i:232;s:1:\"b\";s:16:\"live-gps.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:232;a:4:{s:1:\"a\";i:233;s:1:\"b\";s:13:\"menu.settings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:233;a:4:{s:1:\"a\";i:234;s:1:\"b\";s:13:\"settings.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:234;a:4:{s:1:\"a\";i:235;s:1:\"b\";s:12:\"settings.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:235;a:4:{s:1:\"a\";i:236;s:1:\"b\";s:13:\"settings.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:236;a:4:{s:1:\"a\";i:237;s:1:\"b\";s:15:\"settings.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:237;a:4:{s:1:\"a\";i:238;s:1:\"b\";s:16:\"settings.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:238;a:4:{s:1:\"a\";i:239;s:1:\"b\";s:15:\"settings.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:239;a:4:{s:1:\"a\";i:240;s:1:\"b\";s:14:\"settings.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:240;a:4:{s:1:\"a\";i:241;s:1:\"b\";s:15:\"settings.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:241;a:4:{s:1:\"a\";i:242;s:1:\"b\";s:16:\"settings.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:242;a:4:{s:1:\"a\";i:243;s:1:\"b\";s:14:\"menu.inquiries\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:243;a:4:{s:1:\"a\";i:244;s:1:\"b\";s:14:\"inquiries.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:244;a:4:{s:1:\"a\";i:245;s:1:\"b\";s:13:\"inquiries.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:245;a:4:{s:1:\"a\";i:246;s:1:\"b\";s:14:\"inquiries.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:246;a:4:{s:1:\"a\";i:247;s:1:\"b\";s:16:\"inquiries.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:247;a:4:{s:1:\"a\";i:248;s:1:\"b\";s:17:\"inquiries.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:248;a:4:{s:1:\"a\";i:249;s:1:\"b\";s:16:\"inquiries.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:249;a:4:{s:1:\"a\";i:250;s:1:\"b\";s:15:\"inquiries.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:250;a:4:{s:1:\"a\";i:251;s:1:\"b\";s:16:\"inquiries.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:251;a:4:{s:1:\"a\";i:252;s:1:\"b\";s:17:\"inquiries.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:252;a:4:{s:1:\"a\";i:253;s:1:\"b\";s:19:\"menu.visit-requests\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:253;a:4:{s:1:\"a\";i:254;s:1:\"b\";s:19:\"visit-requests.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:254;a:4:{s:1:\"a\";i:255;s:1:\"b\";s:18:\"visit-requests.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:255;a:4:{s:1:\"a\";i:256;s:1:\"b\";s:19:\"visit-requests.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:256;a:4:{s:1:\"a\";i:257;s:1:\"b\";s:21:\"visit-requests.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:257;a:4:{s:1:\"a\";i:258;s:1:\"b\";s:22:\"visit-requests.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:258;a:4:{s:1:\"a\";i:259;s:1:\"b\";s:21:\"visit-requests.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:259;a:4:{s:1:\"a\";i:260;s:1:\"b\";s:20:\"visit-requests.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:260;a:4:{s:1:\"a\";i:261;s:1:\"b\";s:21:\"visit-requests.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:261;a:4:{s:1:\"a\";i:262;s:1:\"b\";s:22:\"visit-requests.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:262;a:4:{s:1:\"a\";i:263;s:1:\"b\";s:9:\"menu.news\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:263;a:4:{s:1:\"a\";i:264;s:1:\"b\";s:9:\"news.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:264;a:4:{s:1:\"a\";i:265;s:1:\"b\";s:8:\"news.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:265;a:4:{s:1:\"a\";i:266;s:1:\"b\";s:9:\"news.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:266;a:4:{s:1:\"a\";i:267;s:1:\"b\";s:11:\"news.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:267;a:4:{s:1:\"a\";i:268;s:1:\"b\";s:12:\"news.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:268;a:4:{s:1:\"a\";i:269;s:1:\"b\";s:11:\"news.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:269;a:4:{s:1:\"a\";i:270;s:1:\"b\";s:10:\"news.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:270;a:4:{s:1:\"a\";i:271;s:1:\"b\";s:11:\"news.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:271;a:4:{s:1:\"a\";i:272;s:1:\"b\";s:12:\"news.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:272;a:4:{s:1:\"a\";i:273;s:1:\"b\";s:15:\"menu.promotions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:273;a:4:{s:1:\"a\";i:274;s:1:\"b\";s:15:\"promotions.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:274;a:4:{s:1:\"a\";i:275;s:1:\"b\";s:14:\"promotions.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:275;a:4:{s:1:\"a\";i:276;s:1:\"b\";s:15:\"promotions.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:276;a:4:{s:1:\"a\";i:277;s:1:\"b\";s:17:\"promotions.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:277;a:4:{s:1:\"a\";i:278;s:1:\"b\";s:18:\"promotions.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:278;a:4:{s:1:\"a\";i:279;s:1:\"b\";s:17:\"promotions.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:279;a:4:{s:1:\"a\";i:280;s:1:\"b\";s:16:\"promotions.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:280;a:4:{s:1:\"a\";i:281;s:1:\"b\";s:17:\"promotions.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:281;a:4:{s:1:\"a\";i:282;s:1:\"b\";s:18:\"promotions.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:282;a:4:{s:1:\"a\";i:283;s:1:\"b\";s:9:\"menu.faqs\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:283;a:4:{s:1:\"a\";i:284;s:1:\"b\";s:9:\"faqs.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:284;a:4:{s:1:\"a\";i:285;s:1:\"b\";s:8:\"faqs.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:285;a:4:{s:1:\"a\";i:286;s:1:\"b\";s:9:\"faqs.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:286;a:4:{s:1:\"a\";i:287;s:1:\"b\";s:11:\"faqs.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:287;a:4:{s:1:\"a\";i:288;s:1:\"b\";s:12:\"faqs.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:288;a:4:{s:1:\"a\";i:289;s:1:\"b\";s:11:\"faqs.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:289;a:4:{s:1:\"a\";i:290;s:1:\"b\";s:10:\"faqs.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:290;a:4:{s:1:\"a\";i:291;s:1:\"b\";s:11:\"faqs.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:291;a:4:{s:1:\"a\";i:292;s:1:\"b\";s:12:\"faqs.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:292;a:4:{s:1:\"a\";i:293;s:1:\"b\";s:20:\"menu.service-centers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:293;a:4:{s:1:\"a\";i:294;s:1:\"b\";s:20:\"service-centers.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:294;a:4:{s:1:\"a\";i:295;s:1:\"b\";s:19:\"service-centers.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:295;a:4:{s:1:\"a\";i:296;s:1:\"b\";s:20:\"service-centers.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:296;a:4:{s:1:\"a\";i:297;s:1:\"b\";s:22:\"service-centers.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:297;a:4:{s:1:\"a\";i:298;s:1:\"b\";s:23:\"service-centers.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:298;a:4:{s:1:\"a\";i:299;s:1:\"b\";s:22:\"service-centers.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:299;a:4:{s:1:\"a\";i:300;s:1:\"b\";s:21:\"service-centers.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:300;a:4:{s:1:\"a\";i:301;s:1:\"b\";s:22:\"service-centers.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:301;a:4:{s:1:\"a\";i:302;s:1:\"b\";s:23:\"service-centers.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:302;a:4:{s:1:\"a\";i:303;s:1:\"b\";s:14:\"menu.brochures\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:303;a:4:{s:1:\"a\";i:304;s:1:\"b\";s:14:\"brochures.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:304;a:4:{s:1:\"a\";i:305;s:1:\"b\";s:13:\"brochures.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:305;a:4:{s:1:\"a\";i:306;s:1:\"b\";s:14:\"brochures.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:306;a:4:{s:1:\"a\";i:307;s:1:\"b\";s:16:\"brochures.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:307;a:4:{s:1:\"a\";i:308;s:1:\"b\";s:17:\"brochures.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:308;a:4:{s:1:\"a\";i:309;s:1:\"b\";s:16:\"brochures.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:309;a:4:{s:1:\"a\";i:310;s:1:\"b\";s:15:\"brochures.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:310;a:4:{s:1:\"a\";i:311;s:1:\"b\";s:16:\"brochures.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:311;a:4:{s:1:\"a\";i:312;s:1:\"b\";s:17:\"brochures.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:312;a:4:{s:1:\"a\";i:313;s:1:\"b\";s:14:\"menu.divisions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:313;a:4:{s:1:\"a\";i:314;s:1:\"b\";s:14:\"divisions.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:314;a:4:{s:1:\"a\";i:315;s:1:\"b\";s:13:\"divisions.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:315;a:4:{s:1:\"a\";i:316;s:1:\"b\";s:14:\"divisions.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:316;a:4:{s:1:\"a\";i:317;s:1:\"b\";s:16:\"divisions.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:317;a:4:{s:1:\"a\";i:318;s:1:\"b\";s:17:\"divisions.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:318;a:4:{s:1:\"a\";i:319;s:1:\"b\";s:16:\"divisions.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:319;a:4:{s:1:\"a\";i:320;s:1:\"b\";s:15:\"divisions.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:320;a:4:{s:1:\"a\";i:321;s:1:\"b\";s:16:\"divisions.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:321;a:4:{s:1:\"a\";i:322;s:1:\"b\";s:17:\"divisions.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:322;a:4:{s:1:\"a\";i:323;s:1:\"b\";s:18:\"api.divisions.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:323;a:4:{s:1:\"a\";i:324;s:1:\"b\";s:14:\"menu.districts\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:324;a:4:{s:1:\"a\";i:325;s:1:\"b\";s:14:\"districts.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:325;a:4:{s:1:\"a\";i:326;s:1:\"b\";s:13:\"districts.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:326;a:4:{s:1:\"a\";i:327;s:1:\"b\";s:14:\"districts.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:327;a:4:{s:1:\"a\";i:328;s:1:\"b\";s:16:\"districts.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:328;a:4:{s:1:\"a\";i:329;s:1:\"b\";s:17:\"districts.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:329;a:4:{s:1:\"a\";i:330;s:1:\"b\";s:16:\"districts.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:330;a:4:{s:1:\"a\";i:331;s:1:\"b\";s:15:\"districts.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:331;a:4:{s:1:\"a\";i:332;s:1:\"b\";s:16:\"districts.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:332;a:4:{s:1:\"a\";i:333;s:1:\"b\";s:17:\"districts.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:333;a:4:{s:1:\"a\";i:334;s:1:\"b\";s:18:\"api.districts.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:334;a:4:{s:1:\"a\";i:335;s:1:\"b\";s:11:\"menu.thanas\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:335;a:4:{s:1:\"a\";i:336;s:1:\"b\";s:11:\"thanas.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:336;a:4:{s:1:\"a\";i:337;s:1:\"b\";s:10:\"thanas.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:337;a:4:{s:1:\"a\";i:338;s:1:\"b\";s:11:\"thanas.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:338;a:4:{s:1:\"a\";i:339;s:1:\"b\";s:13:\"thanas.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:339;a:4:{s:1:\"a\";i:340;s:1:\"b\";s:14:\"thanas.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:340;a:4:{s:1:\"a\";i:341;s:1:\"b\";s:13:\"thanas.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:341;a:4:{s:1:\"a\";i:342;s:1:\"b\";s:12:\"thanas.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:342;a:4:{s:1:\"a\";i:343;s:1:\"b\";s:13:\"thanas.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:343;a:4:{s:1:\"a\";i:344;s:1:\"b\";s:14:\"thanas.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:344;a:4:{s:1:\"a\";i:345;s:1:\"b\";s:15:\"api.thanas.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:345;a:4:{s:1:\"a\";i:346;s:1:\"b\";s:20:\"report.dealer-ledger\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:346;a:4:{s:1:\"a\";i:347;s:1:\"b\";s:13:\"menu.holidays\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:347;a:4:{s:1:\"a\";i:348;s:1:\"b\";s:13:\"holidays.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:348;a:4:{s:1:\"a\";i:349;s:1:\"b\";s:12:\"holidays.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:349;a:4:{s:1:\"a\";i:350;s:1:\"b\";s:13:\"holidays.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:350;a:4:{s:1:\"a\";i:351;s:1:\"b\";s:15:\"holidays.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:351;a:4:{s:1:\"a\";i:352;s:1:\"b\";s:16:\"holidays.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:352;a:4:{s:1:\"a\";i:353;s:1:\"b\";s:15:\"holidays.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:353;a:4:{s:1:\"a\";i:354;s:1:\"b\";s:14:\"holidays.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:354;a:4:{s:1:\"a\";i:355;s:1:\"b\";s:15:\"holidays.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:355;a:4:{s:1:\"a\";i:356;s:1:\"b\";s:16:\"holidays.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:356;a:4:{s:1:\"a\";i:357;s:1:\"b\";s:14:\"menu.retailers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:357;a:4:{s:1:\"a\";i:358;s:1:\"b\";s:14:\"retailers.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:358;a:4:{s:1:\"a\";i:359;s:1:\"b\";s:13:\"retailers.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:359;a:4:{s:1:\"a\";i:360;s:1:\"b\";s:14:\"retailers.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:360;a:4:{s:1:\"a\";i:361;s:1:\"b\";s:16:\"retailers.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:361;a:4:{s:1:\"a\";i:362;s:1:\"b\";s:17:\"retailers.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:362;a:4:{s:1:\"a\";i:363;s:1:\"b\";s:16:\"retailers.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:363;a:4:{s:1:\"a\";i:364;s:1:\"b\";s:15:\"retailers.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:364;a:4:{s:1:\"a\";i:365;s:1:\"b\";s:16:\"retailers.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:365;a:4:{s:1:\"a\";i:366;s:1:\"b\";s:17:\"retailers.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:366;a:4:{s:1:\"a\";i:367;s:1:\"b\";s:19:\"menu.cash-handovers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:367;a:4:{s:1:\"a\";i:368;s:1:\"b\";s:19:\"cash-handovers.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:368;a:4:{s:1:\"a\";i:369;s:1:\"b\";s:18:\"cash-handovers.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:369;a:4:{s:1:\"a\";i:370;s:1:\"b\";s:19:\"cash-handovers.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:370;a:4:{s:1:\"a\";i:371;s:1:\"b\";s:21:\"cash-handovers.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:371;a:4:{s:1:\"a\";i:372;s:1:\"b\";s:22:\"cash-handovers.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:372;a:4:{s:1:\"a\";i:373;s:1:\"b\";s:21:\"cash-handovers.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:373;a:4:{s:1:\"a\";i:374;s:1:\"b\";s:20:\"cash-handovers.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:374;a:4:{s:1:\"a\";i:375;s:1:\"b\";s:21:\"cash-handovers.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:375;a:4:{s:1:\"a\";i:376;s:1:\"b\";s:22:\"cash-handovers.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:376;a:4:{s:1:\"a\";i:377;s:1:\"b\";s:18:\"api.retailers.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:377;a:4:{s:1:\"a\";i:378;s:1:\"b\";s:23:\"report.movement-summary\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:378;a:4:{s:1:\"a\";i:379;s:1:\"b\";s:17:\"menu.achievements\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:379;a:4:{s:1:\"a\";i:380;s:1:\"b\";s:17:\"achievements.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:380;a:4:{s:1:\"a\";i:381;s:1:\"b\";s:16:\"achievements.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:6;}}i:381;a:4:{s:1:\"a\";i:382;s:1:\"b\";s:17:\"achievements.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:382;a:4:{s:1:\"a\";i:383;s:1:\"b\";s:19:\"achievements.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:383;a:4:{s:1:\"a\";i:384;s:1:\"b\";s:20:\"achievements.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:384;a:4:{s:1:\"a\";i:385;s:1:\"b\";s:19:\"achievements.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:385;a:4:{s:1:\"a\";i:386;s:1:\"b\";s:18:\"achievements.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:386;a:4:{s:1:\"a\";i:387;s:1:\"b\";s:19:\"achievements.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:387;a:4:{s:1:\"a\";i:388;s:1:\"b\";s:20:\"achievements.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:388;a:4:{s:1:\"a\";i:389;s:1:\"b\";s:21:\"api.achievements.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:389;a:4:{s:1:\"a\";i:390;s:1:\"b\";s:20:\"api.achievements.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:390;a:4:{s:1:\"a\";i:391;s:1:\"b\";s:26:\"report.achievement-summary\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:391;a:4:{s:1:\"a\";i:392;s:1:\"b\";s:22:\"menu.tally-integration\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:392;a:4:{s:1:\"a\";i:393;s:1:\"b\";s:22:\"tally-integration.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:393;a:4:{s:1:\"a\";i:394;s:1:\"b\";s:27:\"tally-integration.configure\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:394;a:4:{s:1:\"a\";i:395;s:1:\"b\";s:22:\"tally-integration.sync\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:395;a:4:{s:1:\"a\";i:396;s:1:\"b\";s:23:\"tally-integration.retry\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:396;a:4:{s:1:\"a\";i:397;s:1:\"b\";s:27:\"tally-integration.reconcile\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:397;a:4:{s:1:\"a\";i:398;s:1:\"b\";s:11:\"menu.depots\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:398;a:4:{s:1:\"a\";i:399;s:1:\"b\";s:11:\"depots.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:399;a:4:{s:1:\"a\";i:400;s:1:\"b\";s:10:\"depots.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:400;a:4:{s:1:\"a\";i:401;s:1:\"b\";s:11:\"depots.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:401;a:4:{s:1:\"a\";i:402;s:1:\"b\";s:13:\"depots.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:402;a:4:{s:1:\"a\";i:403;s:1:\"b\";s:14:\"depots.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:403;a:4:{s:1:\"a\";i:404;s:1:\"b\";s:13:\"depots.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:404;a:4:{s:1:\"a\";i:405;s:1:\"b\";s:12:\"depots.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:405;a:4:{s:1:\"a\";i:406;s:1:\"b\";s:13:\"depots.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:406;a:4:{s:1:\"a\";i:407;s:1:\"b\";s:14:\"depots.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:407;a:4:{s:1:\"a\";i:408;s:1:\"b\";s:15:\"api.depots.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:408;a:4:{s:1:\"a\";i:409;s:1:\"b\";s:13:\"menu.vehicles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:409;a:4:{s:1:\"a\";i:410;s:1:\"b\";s:13:\"vehicles.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:410;a:4:{s:1:\"a\";i:411;s:1:\"b\";s:12:\"vehicles.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:411;a:4:{s:1:\"a\";i:412;s:1:\"b\";s:13:\"vehicles.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:412;a:4:{s:1:\"a\";i:413;s:1:\"b\";s:15:\"vehicles.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:413;a:4:{s:1:\"a\";i:414;s:1:\"b\";s:16:\"vehicles.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:414;a:4:{s:1:\"a\";i:415;s:1:\"b\";s:15:\"vehicles.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:415;a:4:{s:1:\"a\";i:416;s:1:\"b\";s:14:\"vehicles.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:416;a:4:{s:1:\"a\";i:417;s:1:\"b\";s:15:\"vehicles.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:417;a:4:{s:1:\"a\";i:418;s:1:\"b\";s:16:\"vehicles.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:418;a:4:{s:1:\"a\";i:419;s:1:\"b\";s:17:\"api.vehicles.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:419;a:4:{s:1:\"a\";i:420;s:1:\"b\";s:12:\"menu.drivers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:420;a:4:{s:1:\"a\";i:421;s:1:\"b\";s:12:\"drivers.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:421;a:4:{s:1:\"a\";i:422;s:1:\"b\";s:11:\"drivers.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:422;a:4:{s:1:\"a\";i:423;s:1:\"b\";s:12:\"drivers.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:423;a:4:{s:1:\"a\";i:424;s:1:\"b\";s:14:\"drivers.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:424;a:4:{s:1:\"a\";i:425;s:1:\"b\";s:15:\"drivers.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:425;a:4:{s:1:\"a\";i:426;s:1:\"b\";s:14:\"drivers.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:426;a:4:{s:1:\"a\";i:427;s:1:\"b\";s:13:\"drivers.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:427;a:4:{s:1:\"a\";i:428;s:1:\"b\";s:14:\"drivers.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:428;a:4:{s:1:\"a\";i:429;s:1:\"b\";s:15:\"drivers.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:429;a:4:{s:1:\"a\";i:430;s:1:\"b\";s:16:\"api.drivers.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:430;a:4:{s:1:\"a\";i:431;s:1:\"b\";s:15:\"menu.deliveries\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:431;a:4:{s:1:\"a\";i:432;s:1:\"b\";s:15:\"deliveries.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:432;a:4:{s:1:\"a\";i:433;s:1:\"b\";s:14:\"deliveries.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:433;a:4:{s:1:\"a\";i:434;s:1:\"b\";s:15:\"deliveries.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:434;a:4:{s:1:\"a\";i:435;s:1:\"b\";s:17:\"deliveries.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:435;a:4:{s:1:\"a\";i:436;s:1:\"b\";s:18:\"deliveries.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:436;a:4:{s:1:\"a\";i:437;s:1:\"b\";s:17:\"deliveries.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:437;a:4:{s:1:\"a\";i:438;s:1:\"b\";s:16:\"deliveries.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:438;a:4:{s:1:\"a\";i:439;s:1:\"b\";s:17:\"deliveries.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:439;a:4:{s:1:\"a\";i:440;s:1:\"b\";s:18:\"deliveries.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:440;a:4:{s:1:\"a\";i:441;s:1:\"b\";s:18:\"menu.sales-returns\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:441;a:4:{s:1:\"a\";i:442;s:1:\"b\";s:18:\"sales-returns.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:442;a:4:{s:1:\"a\";i:443;s:1:\"b\";s:17:\"sales-returns.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:6;}}i:443;a:4:{s:1:\"a\";i:444;s:1:\"b\";s:18:\"sales-returns.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:444;a:4:{s:1:\"a\";i:445;s:1:\"b\";s:20:\"sales-returns.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:445;a:4:{s:1:\"a\";i:446;s:1:\"b\";s:21:\"sales-returns.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:446;a:4:{s:1:\"a\";i:447;s:1:\"b\";s:20:\"sales-returns.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:447;a:4:{s:1:\"a\";i:448;s:1:\"b\";s:19:\"sales-returns.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:448;a:4:{s:1:\"a\";i:449;s:1:\"b\";s:20:\"sales-returns.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:449;a:4:{s:1:\"a\";i:450;s:1:\"b\";s:21:\"sales-returns.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:450;a:4:{s:1:\"a\";i:451;s:1:\"b\";s:22:\"api.sales-returns.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:451;a:4:{s:1:\"a\";i:452;s:1:\"b\";s:21:\"api.sales-returns.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:452;a:4:{s:1:\"a\";i:453;s:1:\"b\";s:17:\"api.retailers.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:453;a:4:{s:1:\"a\";i:454;s:1:\"b\";s:27:\"report.sales-return-summary\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:454;a:4:{s:1:\"a\";i:455;s:1:\"b\";s:16:\"menu.leave-types\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:455;a:4:{s:1:\"a\";i:456;s:1:\"b\";s:16:\"leave-types.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:456;a:4:{s:1:\"a\";i:457;s:1:\"b\";s:15:\"leave-types.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:457;a:4:{s:1:\"a\";i:458;s:1:\"b\";s:16:\"leave-types.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:458;a:4:{s:1:\"a\";i:459;s:1:\"b\";s:18:\"leave-types.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:459;a:4:{s:1:\"a\";i:460;s:1:\"b\";s:19:\"leave-types.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:460;a:4:{s:1:\"a\";i:461;s:1:\"b\";s:18:\"leave-types.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:461;a:4:{s:1:\"a\";i:462;s:1:\"b\";s:17:\"leave-types.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:462;a:4:{s:1:\"a\";i:463;s:1:\"b\";s:18:\"leave-types.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:463;a:4:{s:1:\"a\";i:464;s:1:\"b\";s:19:\"leave-types.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:464;a:4:{s:1:\"a\";i:465;s:1:\"b\";s:19:\"menu.leave-requests\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:465;a:4:{s:1:\"a\";i:466;s:1:\"b\";s:19:\"leave-requests.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:466;a:4:{s:1:\"a\";i:467;s:1:\"b\";s:18:\"leave-requests.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:467;a:4:{s:1:\"a\";i:468;s:1:\"b\";s:19:\"leave-requests.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:468;a:4:{s:1:\"a\";i:469;s:1:\"b\";s:21:\"leave-requests.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:469;a:4:{s:1:\"a\";i:470;s:1:\"b\";s:22:\"leave-requests.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:470;a:4:{s:1:\"a\";i:471;s:1:\"b\";s:21:\"leave-requests.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:471;a:4:{s:1:\"a\";i:472;s:1:\"b\";s:20:\"leave-requests.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:472;a:4:{s:1:\"a\";i:473;s:1:\"b\";s:21:\"leave-requests.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:473;a:4:{s:1:\"a\";i:474;s:1:\"b\";s:22:\"leave-requests.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}i:474;a:4:{s:1:\"a\";i:475;s:1:\"b\";s:23:\"api.leave-requests.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;}}i:475;a:4:{s:1:\"a\";i:476;s:1:\"b\";s:22:\"api.leave-requests.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:6;}}i:476;a:4:{s:1:\"a\";i:477;s:1:\"b\";s:19:\"menu.leave-balances\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:477;a:4:{s:1:\"a\";i:478;s:1:\"b\";s:19:\"leave-balances.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:478;a:4:{s:1:\"a\";i:479;s:1:\"b\";s:18:\"leave-balances.add\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:479;a:4:{s:1:\"a\";i:480;s:1:\"b\";s:19:\"leave-balances.edit\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:480;a:4:{s:1:\"a\";i:481;s:1:\"b\";s:21:\"leave-balances.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:481;a:4:{s:1:\"a\";i:482;s:1:\"b\";s:22:\"leave-balances.restore\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:482;a:4:{s:1:\"a\";i:483;s:1:\"b\";s:21:\"leave-balances.export\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:483;a:4:{s:1:\"a\";i:484;s:1:\"b\";s:20:\"leave-balances.print\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:484;a:4:{s:1:\"a\";i:485;s:1:\"b\";s:21:\"leave-balances.import\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:485;a:4:{s:1:\"a\";i:486;s:1:\"b\";s:22:\"leave-balances.approve\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}}s:5:\"roles\";a:6:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:11:\"Super Admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:15:\"General Manager\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:13:\"Sales Manager\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:12:\"Area Manager\";s:1:\"c\";s:3:\"web\";}i:4;a:3:{s:1:\"a\";i:5;s:1:\"b\";s:17:\"Territory Manager\";s:1:\"c\";s:3:\"web\";}i:5;a:3:{s:1:\"a\";i:6;s:1:\"b\";s:15:\"Sales Executive\";s:1:\"c\";s:3:\"web\";}}}', 1790054552);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cash_handovers`
--

CREATE TABLE `cash_handovers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `handover_date` date NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `confirmed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cash_handovers`
--

INSERT INTO `cash_handovers` (`id`, `user_id`, `amount`, `handover_date`, `status`, `confirmed_by`, `confirmed_at`, `remarks`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 80, 1500.00, '2026-08-27', 'confirmed', 1, '2026-08-27 00:42:08', NULL, 1, 1, NULL, NULL, '2026-08-27 00:41:57', '2026-08-27 00:42:08');

-- --------------------------------------------------------

--
-- Table structure for table `collection_entries`
--

CREATE TABLE `collection_entries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `dealer_id` bigint(20) UNSIGNED NOT NULL,
  `collection_date` date NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(255) NOT NULL DEFAULT 'cash',
  `reference_no` varchar(255) DEFAULT NULL,
  `cheque_image` varchar(255) DEFAULT NULL,
  `cheque_status` varchar(255) DEFAULT NULL,
  `otp_verified_at` timestamp NULL DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `external_reference` varchar(255) DEFAULT NULL,
  `tally_guid` varchar(255) DEFAULT NULL,
  `tally_voucher_number` varchar(255) DEFAULT NULL,
  `sync_status` varchar(255) NOT NULL DEFAULT 'not_synced',
  `sync_error` text DEFAULT NULL,
  `synced_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `collection_entries`
--

INSERT INTO `collection_entries` (`id`, `user_id`, `dealer_id`, `collection_date`, `amount`, `payment_method`, `reference_no`, `cheque_image`, `cheque_status`, `otp_verified_at`, `remarks`, `status`, `external_reference`, `tally_guid`, `tally_voucher_number`, `sync_status`, `sync_error`, `synced_at`, `approved_by`, `approved_at`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 80, 1, '2026-08-23', 1629325.28, 'cheque', 'REF-00329VW', 'collection-entries/24Z6UTp3dkL3EdI4osm1pp7u4pOZwKyLC6XfQLCC.png', NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 'not_synced', NULL, NULL, 1, '2026-08-27 01:17:55', NULL, 1, NULL, NULL, '2026-08-23 05:34:26', '2026-08-27 01:17:55'),
(2, 81, 2, '2026-08-22', 235405.62, 'bank_transfer', 'REF-56506JU', NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'not_synced', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:34:26', '2026-08-23 05:34:26'),
(3, 80, 3, '2026-08-21', 252866.88, 'cheque', 'REF-11154PU', NULL, NULL, NULL, 'Quis ad est asperiores qui architecto ut tenetur.', 'pending', NULL, NULL, NULL, 'not_synced', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:34:26', '2026-08-23 05:34:26'),
(4, 81, 4, '2026-08-20', 77069.10, 'mobile_banking', 'REF-82410BJ', NULL, NULL, NULL, 'Tempora non quidem quis enim error sint minima.', 'pending', NULL, NULL, NULL, 'not_synced', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:34:26', '2026-08-23 05:34:26'),
(5, 80, 5, '2026-08-19', 100852.29, 'cheque', 'REF-62367NZ', NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'not_synced', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:34:26', '2026-08-23 05:34:26'),
(10, 80, 1, '2026-08-27', 4000.00, 'cash', 'REF-25485NQ', NULL, NULL, NULL, NULL, 'approved', NULL, NULL, NULL, 'not_synced', NULL, NULL, 1, '2026-08-29 23:48:34', NULL, 1, NULL, NULL, '2026-08-27 00:41:37', '2026-08-29 23:48:34'),
(11, 1, 1, '2026-08-27', 600.00, 'cash', NULL, 'collection-entries/nZxkmnDxZbHoDmWbV1zuxgJjf3qoGdOyypAH4wOP.jpg', NULL, NULL, 'Collected during routine visit.', 'rejected', NULL, NULL, NULL, 'not_synced', NULL, NULL, 1, '2026-08-29 23:48:39', 1, 1, NULL, NULL, '2026-08-27 01:48:51', '2026-08-29 23:48:39'),
(12, 1, 1, '2026-08-27', 600.00, 'cash', NULL, 'collection-entries/23zmotjetcZ5RAhRA0jfzw0hZb7A2v7nwoROypUU.jpg', NULL, NULL, 'Collected during routine visit.', 'pending', NULL, NULL, NULL, 'not_synced', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-08-27 01:49:49', '2026-08-27 01:49:49'),
(13, 1, 1, '2026-08-27', 600.00, 'cash', NULL, 'collection-entries/HPc7Ui9qi30pV3uFhKREbbtNsooBYPpIpQytiAEy.jpg', NULL, NULL, 'Collected during routine visit.', 'pending', NULL, NULL, NULL, 'not_synced', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-08-27 01:50:19', '2026-08-27 01:50:19'),
(14, 1, 1, '2026-08-27', 600.00, 'cash', NULL, 'collection-entries/vRXJ0ADKVTDfXBfESg7l5yZL2sUGDkyurgwyMFEi.jpg', NULL, NULL, 'Collected during routine visit.', 'pending', NULL, NULL, NULL, 'not_synced', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-08-27 02:21:59', '2026-08-27 02:21:59'),
(15, 1, 1, '2026-08-27', 600.00, 'cash', NULL, 'collection-entries/KPfZNaObQgkn6jl1QNg30hXg0DUsQUod5NJSZ0KH.jpg', NULL, NULL, 'Collected during routine visit.', 'pending', NULL, NULL, NULL, 'not_synced', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-08-27 02:22:34', '2026-08-27 02:22:34'),
(16, 80, 5, '2026-08-27', 18.00, 'cash', NULL, NULL, NULL, NULL, NULL, 'pending', NULL, NULL, NULL, 'not_synced', NULL, NULL, NULL, NULL, 1, NULL, 1, '2026-08-27 02:44:45', '2026-08-27 02:44:21', '2026-08-27 02:44:45'),
(17, 1, 1, '2026-08-27', 600.00, 'cash', NULL, 'collection-entries/HYOiNVpOT3MyDEMLWFomPL2hcsu6cFtqH2y3jory.jpg', NULL, '2026-08-27 02:52:04', 'Collected during routine visit.', 'pending', NULL, NULL, NULL, 'not_synced', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-08-27 02:52:04', '2026-08-27 02:52:04'),
(18, 80, 1, '2026-08-27', 500.00, 'cash', NULL, NULL, NULL, '2026-08-27 02:57:01', NULL, 'pending', NULL, NULL, NULL, 'not_synced', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-08-27 02:57:01', '2026-08-27 02:57:01'),
(19, 80, 1, '2026-08-27', 750.00, 'cash', NULL, NULL, NULL, '2026-08-27 03:12:04', NULL, 'pending', NULL, NULL, NULL, 'not_synced', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-08-27 03:12:04', '2026-08-27 03:12:04'),
(20, 80, 1, '2026-08-27', 850.00, 'cash', NULL, NULL, NULL, '2026-08-27 03:18:03', NULL, 'pending', NULL, NULL, NULL, 'not_synced', NULL, NULL, NULL, NULL, 80, NULL, NULL, NULL, '2026-08-27 03:18:03', '2026-08-27 03:18:03'),
(21, 1, 1, '2026-08-27', 600.00, 'cash', NULL, 'collection-entries/IWbyhMXcTOy4QKXiFKizzhu393UCktjTsFObPKS8.jpg', NULL, '2026-08-27 03:18:54', 'Collected during routine visit.', 'pending', NULL, NULL, NULL, 'not_synced', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-08-27 03:18:54', '2026-08-27 03:18:54');

-- --------------------------------------------------------

--
-- Table structure for table `collection_otps`
--

CREATE TABLE `collection_otps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dealer_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `code_hash` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `verified_at` timestamp NULL DEFAULT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `collection_otps`
--

INSERT INTO `collection_otps` (`id`, `dealer_id`, `user_id`, `amount`, `payment_method`, `code_hash`, `expires_at`, `verified_at`, `attempts`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 500.00, 'cash', '$2y$12$hAqAKoFvVMysbDlm70gziO41bSfeDqCa0sOqgpqcwn2XMNqTEldVy', '2026-08-27 00:24:53', NULL, 0, '2026-08-27 00:24:53', '2026-08-27 00:24:53'),
(2, 1, 1, 500.00, 'cash', '$2y$12$.ARoG28ve7FgU9aAuYu69e4dJHUGK7oI1fPfJy0FjjfntmjO25q3O', '2026-08-27 00:25:56', NULL, 0, '2026-08-27 00:25:56', '2026-08-27 00:25:56'),
(3, 1, 1, 500.00, 'cash', '$2y$12$NyPdLU.ezVnnmED2X1IXbeZTGouFBwuCwC64VCxYGb.cY8cK7/ClG', '2026-08-27 00:28:13', NULL, 0, '2026-08-27 00:28:13', '2026-08-27 00:28:13'),
(4, 1, 1, 500.00, 'cash', '$2y$12$Kcux9mtMuzTMlpb9kTSZ5ONV6547sN.v8TzrqsLnkfgIUYc/K9kcW', '2026-08-27 00:30:08', NULL, 0, '2026-08-27 00:30:08', '2026-08-27 00:30:08'),
(5, 1, 1, 600.00, 'cash', '$2y$12$1zy/GMUQQxwO57ClipURlOuyeU/9mkYrjGC7SSMc2widqB00XeQoq', '2026-08-27 01:58:51', NULL, 0, '2026-08-27 01:48:51', '2026-08-27 01:48:51'),
(6, 1, 1, 600.00, 'cash', '$2y$12$yzQ5yr0GHxE5Ahlx5n2CV.dckypRQ65iYilzz6xp3UcSgScJG8FCO', '2026-08-27 01:59:48', NULL, 0, '2026-08-27 01:49:48', '2026-08-27 01:49:48'),
(7, 1, 1, 600.00, 'cash', '$2y$12$kJ9GLncXud1RYfHX0jdXBOVA9Udb1zhVWJmlyeCiyMuz6csnmd3Vm', '2026-08-27 02:00:18', NULL, 0, '2026-08-27 01:50:18', '2026-08-27 01:50:18'),
(8, 1, 1, 600.00, 'cash', '$2y$12$xpAdbeifiZt5vWh2Qvh51uIIfIwon6npyyFo27odbwGzuiz/xyBOi', '2026-08-27 02:31:58', NULL, 0, '2026-08-27 02:21:58', '2026-08-27 02:21:58'),
(9, 1, 1, 600.00, 'cash', '$2y$12$iPlmIjRLEnXuVWKNvzhs4.t.06/IsX2gGPKqLbU/3VvRu6C5QFaqC', '2026-08-27 02:32:33', NULL, 0, '2026-08-27 02:22:33', '2026-08-27 02:22:33'),
(10, 1, 1, 600.00, 'cash', '$2y$12$GpCy0k7ruTpv8I3RnVFhouyrQ6tO/qdnWER77CS6UXcI//bOIwC4u', '2026-08-27 08:52:04', '2026-08-27 02:52:04', 0, '2026-08-27 02:52:03', '2026-08-27 02:52:04'),
(11, 1, 1, 500.00, 'cash', '$2y$12$k.5qx28.yPEo5bYJ6dlspO/rw86uZWUmtFnzQfLjVSt3vaR7XpjQm', '2026-08-27 08:57:01', '2026-08-27 02:57:01', 0, '2026-08-27 02:57:00', '2026-08-27 02:57:01'),
(12, 3, 1, 45.00, 'cash', '$2y$12$hVeZb4.B6ZV99Sh7UwPUJOzpyEbl9K26Y4H2xsM.SIugSN4VNGwjW', '2026-08-27 03:18:38', NULL, 0, '2026-08-27 03:08:38', '2026-08-27 03:08:38'),
(13, 1, 1, 750.00, 'cash', '$2y$12$rjD0JtMQ1d6yZ4uSnskL4.Lp7HbGNWImWnkUnw5MRFBGl6Q.FYTiK', '2026-08-27 03:21:24', NULL, 0, '2026-08-27 03:11:24', '2026-08-27 03:11:24'),
(14, 1, 1, 750.00, 'cash', '$2y$12$ZmzqZwZ5q7VPnzVSF/RzE.5MWXYzVK10yArDwKzJiv93ctRiV75pm', '2026-08-27 09:12:04', '2026-08-27 03:12:04', 0, '2026-08-27 03:11:50', '2026-08-27 03:12:04'),
(15, 3, 1, 45.00, 'cash', '$2y$12$OdfHh02IRpJMHUyWUYQAfebJzOjjWTtuRQ2rukZc0EkUY5pKpHedC', '2026-08-27 03:23:40', NULL, 0, '2026-08-27 03:13:40', '2026-08-27 03:13:40'),
(16, 1, 80, 850.00, 'cash', '$2y$12$l6ypsrbCgh0a/b8pcKh5.uWZNqh8NuQMywbxsQSXiDVGzEJQOvZIS', '2026-08-27 09:18:03', '2026-08-27 03:18:03', 0, '2026-08-27 03:17:56', '2026-08-27 03:18:03'),
(17, 1, 80, 300.00, 'cash', '$2y$12$7sye107fXk3ikpmmbvemFukig7htp1UZfeKRwusJMHEty4GCQVcyu', '2026-08-27 09:18:28', NULL, 1, '2026-08-27 03:18:27', '2026-08-27 03:18:28'),
(18, 1, 1, 600.00, 'cash', '$2y$12$uwexjrdeuYbRRXpJgKzx/enjYID4KYMriM.1qo6O3euUIYfOjvDjy', '2026-08-27 09:18:54', '2026-08-27 03:18:54', 0, '2026-08-27 03:18:53', '2026-08-27 03:18:54'),
(19, 4, 1, 100.00, 'cash', '$2y$12$4IlyNDSxfPyqY6FuBS7VFuDrU2ATWlLEAQRw8glH5/V9OHVFO/1CC', '2026-09-08 11:09:14', NULL, 0, '2026-09-08 10:59:14', '2026-09-08 10:59:14');

-- --------------------------------------------------------

--
-- Table structure for table `customer_accounts`
--

CREATE TABLE `customer_accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dealer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_accounts`
--

INSERT INTO `customer_accounts` (`id`, `dealer_id`, `name`, `email`, `phone`, `password`, `email_verified_at`, `remember_token`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Savar Pump House', 'savarpump@gmail.com', '01821404477', '$2y$12$L5YJe5DwnzPGOsYfnWfehuoh8ozg5pLRJboOGqi0/qrcmeOU47nCy', NULL, NULL, NULL, '2026-08-25 03:15:01', '2026-08-25 05:27:03');

-- --------------------------------------------------------

--
-- Table structure for table `customer_password_reset_tokens`
--

CREATE TABLE `customer_password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dealers`
--

CREATE TABLE `dealers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `division_id` bigint(20) UNSIGNED DEFAULT NULL,
  `district_id` bigint(20) UNSIGNED DEFAULT NULL,
  `thana_id` bigint(20) UNSIGNED DEFAULT NULL,
  `dealer_code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `gps_lat` decimal(10,7) DEFAULT NULL,
  `gps_lng` decimal(10,7) DEFAULT NULL,
  `territory_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `tally_guid` varchar(255) DEFAULT NULL,
  `tally_ledger_name` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `dealers`
--

INSERT INTO `dealers` (`id`, `division_id`, `district_id`, `thana_id`, `dealer_code`, `name`, `phone`, `email`, `address`, `image`, `gps_lat`, `gps_lng`, `territory_id`, `status`, `tally_guid`, `tally_ledger_name`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 6, 47, 365, 'DLR-0001', 'Savar Pump House', '01821404477', 'savarpump@gmail.com', 'Savar Bazar Road, Savar, Dhaka', 'dealers/gazi-showroom-1.jpg', 23.8490746, 90.2578354, 1, 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000db', NULL, NULL, 1, NULL, NULL, '2026-08-23 05:22:02', '2026-09-08 04:36:51'),
(2, 6, 47, 367, 'DLR-0002', 'Keraniganj Hardware & Motors', '01953131136', NULL, 'Aganagar, Keraniganj, Dhaka', 'dealers/gazi-showroom-2.jpg', 23.7000808, 90.3724086, 2, 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000dc', NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:22:02', '2026-09-08 04:57:49'),
(3, 6, 47, 366, 'DLR-0003', 'Dhamrai Water Solutions', '01997886631', NULL, 'Dhamrai Bus Stand, Dhamrai, Dhaka', 'dealers/gazi-showroom-3.jpg', 23.9181673, 90.2115083, 3, 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000dd', NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:22:02', '2026-09-08 04:57:50'),
(4, 6, 47, 365, 'DLR-0004', 'Gazi Appliance Corner', '01707842562', NULL, 'Savar New Market, Savar, Dhaka', 'dealers/gazi-showroom-1.jpg', 23.8276516, 90.2581358, 1, 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000de', NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:22:02', '2026-09-08 04:57:50'),
(5, 6, 47, 367, 'DLR-0005', 'Buriganga Distribution House', '01852953026', NULL, 'Zinzira, Keraniganj, Dhaka', 'dealers/gazi-showroom-2.jpg', 23.7024951, 90.3890115, 2, 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000df', NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:22:02', '2026-09-08 04:57:50'),
(6, NULL, NULL, NULL, 'DLR-1787816922', 'Karim Traders', '01712345678', NULL, NULL, NULL, 23.8103000, 90.4125000, NULL, 1, NULL, NULL, 1, NULL, 1, '2026-09-07 10:29:56', '2026-08-27 01:48:42', '2026-09-08 03:53:04'),
(7, NULL, NULL, NULL, 'DLR-1787816980', 'Karim Traders', '01712345678', NULL, NULL, NULL, 23.8103000, 90.4125000, NULL, 1, NULL, NULL, 1, NULL, 1, '2026-09-07 10:29:53', '2026-08-27 01:49:40', '2026-09-08 03:53:04'),
(8, NULL, NULL, NULL, 'DLR-1787817010', 'Karim Traders', '01712345678', NULL, NULL, NULL, 23.8103000, 90.4125000, NULL, 1, NULL, NULL, 1, NULL, 1, '2026-09-07 10:30:04', '2026-08-27 01:50:10', '2026-09-08 03:53:04'),
(9, NULL, NULL, NULL, 'DLR-1787818909', 'Karim Traders', '01712345678', NULL, NULL, NULL, 23.8103000, 90.4125000, NULL, 1, NULL, NULL, 1, NULL, 1, '2026-09-07 10:29:49', '2026-08-27 02:21:49', '2026-09-08 03:53:04'),
(10, NULL, NULL, NULL, 'DLR-1787818944', 'Karim Traders', '01712345678', NULL, NULL, NULL, 23.8103000, 90.4125000, NULL, 1, NULL, NULL, 1, NULL, 1, '2026-09-07 10:29:46', '2026-08-27 02:22:24', '2026-09-08 03:53:04'),
(11, NULL, NULL, NULL, 'DLR-1787820714', 'Karim Traders', '01712345678', NULL, NULL, NULL, 23.8103000, 90.4125000, NULL, 1, NULL, NULL, 1, NULL, 1, '2026-09-07 10:26:01', '2026-08-27 02:51:54', '2026-09-08 03:53:04'),
(12, NULL, NULL, NULL, 'DLR-1787822324', 'Karim Traders', '01712345678', NULL, NULL, NULL, 23.8103000, 90.4125000, NULL, 1, NULL, NULL, 1, NULL, 1, '2026-09-07 10:29:42', '2026-08-27 03:18:44', '2026-09-08 03:53:04'),
(13, NULL, NULL, NULL, 'CUST-85241', 'Tangail Hardware', '01166526307', 'dominic67@rutherford.biz', '337 Stamm Port Apt. 131\nSouth Keshaunport, RI 18925', NULL, 22.0545930, 91.0388790, NULL, 1, NULL, NULL, NULL, NULL, 1, '2026-09-07 10:28:16', '2026-08-30 04:52:19', '2026-09-08 03:53:04'),
(14, NULL, NULL, NULL, 'CUST-13532', 'Tangail Hardware', '01575829863', 'gerard.wiegand@balistreri.com', '88225 Lynch Brooks Suite 251\nNew Jody, CA 76381', NULL, 23.0175250, 91.7119790, NULL, 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000e0', NULL, NULL, NULL, NULL, NULL, '2026-08-30 04:53:16', '2026-09-08 04:57:51'),
(18, NULL, NULL, NULL, 'TLY-D-537415', 'Dealer 1', '0000000000', NULL, NULL, NULL, NULL, NULL, NULL, 0, 'd530416d-350a-4df9-bc3c-06943927816a-000000d5', NULL, NULL, NULL, 1, NULL, '2026-09-08 03:53:13', '2026-09-08 07:15:30'),
(19, NULL, NULL, NULL, 'TLY-D-199449', 'Dealer 2', '0000000000', NULL, NULL, NULL, NULL, NULL, NULL, 0, 'd530416d-350a-4df9-bc3c-06943927816a-000000d6', NULL, NULL, NULL, NULL, NULL, '2026-09-08 03:53:13', '2026-09-08 03:53:13'),
(33, NULL, NULL, NULL, 'DLR-0006', 'Omar', '01723456789', NULL, NULL, NULL, NULL, NULL, NULL, 1, 'd530416d-350a-4df9-bc3c-06943927816a-00000113', NULL, 1, NULL, NULL, NULL, '2026-09-08 08:08:31', '2026-09-08 08:10:04'),
(34, NULL, NULL, NULL, 'TLY-D-277683', 'Jewel', '0000000000', NULL, NULL, NULL, NULL, NULL, NULL, 1, 'd530416d-350a-4df9-bc3c-06943927816a-00000112', NULL, NULL, 1, NULL, NULL, '2026-09-08 08:09:33', '2026-09-08 08:10:34');

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `vehicle_id` bigint(20) UNSIGNED DEFAULT NULL,
  `driver_id` bigint(20) UNSIGNED DEFAULT NULL,
  `delivery_date` date NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'dispatched',
  `external_reference` varchar(255) DEFAULT NULL,
  `tally_guid` varchar(255) DEFAULT NULL,
  `tally_delivery_number` varchar(255) DEFAULT NULL,
  `sync_status` varchar(255) NOT NULL DEFAULT 'not_synced',
  `sync_error` text DEFAULT NULL,
  `synced_at` timestamp NULL DEFAULT NULL,
  `dispatched_by` bigint(20) UNSIGNED DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `depots`
--

CREATE TABLE `depots` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `territory_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tally_guid` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `depots`
--

INSERT INTO `depots` (`id`, `name`, `code`, `address`, `territory_id`, `tally_guid`, `status`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Main Depot', 'DEP-MAIN', NULL, NULL, 'd530416d-350a-4df9-bc3c-06943927816a-000000da', 1, NULL, NULL, NULL, NULL, '2026-09-07 11:23:44', '2026-09-08 04:36:51'),
(4, 'Main Location', 'TLY-DEP-943921', NULL, NULL, 'd530416d-350a-4df9-bc3c-06943927816a-00000063', 0, NULL, NULL, NULL, NULL, '2026-09-08 03:53:14', '2026-09-08 03:53:14'),
(5, 'Warehouse', 'TLY-DEP-927918', NULL, NULL, 'd530416d-350a-4df9-bc3c-06943927816a-000000d4', 0, NULL, NULL, NULL, NULL, '2026-09-08 03:53:14', '2026-09-08 03:53:14');

-- --------------------------------------------------------

--
-- Table structure for table `depot_allocations`
--

CREATE TABLE `depot_allocations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_item_id` bigint(20) UNSIGNED NOT NULL,
  `depot_id` bigint(20) UNSIGNED NOT NULL,
  `requested_qty` decimal(12,2) NOT NULL,
  `allocated_qty` decimal(12,2) NOT NULL DEFAULT 0.00,
  `allocation_status` varchar(255) NOT NULL DEFAULT 'pending',
  `is_alternative_depot` tinyint(1) NOT NULL DEFAULT 0,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `districts`
--

CREATE TABLE `districts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `division_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `name_bn` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `districts`
--

INSERT INTO `districts` (`id`, `division_id`, `name`, `name_bn`, `status`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Comilla', 'কুমিল্লা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(2, 1, 'Feni', 'ফেনী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(3, 1, 'Brahmanbaria', 'ব্রাহ্মণবাড়িয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(4, 1, 'Rangamati', 'রাঙ্গামাটি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(5, 1, 'Noakhali', 'নোয়াখালী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(6, 1, 'Chandpur', 'চাঁদপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(7, 1, 'Lakshmipur', 'লক্ষ্মীপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(8, 1, 'Chattogram', 'চট্টগ্রাম', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(9, 1, 'Coxsbazar', 'কক্সবাজার', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(10, 1, 'Khagrachhari', 'খাগড়াছড়ি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(11, 1, 'Bandarban', 'বান্দরবান', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(12, 2, 'Sirajganj', 'সিরাজগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(13, 2, 'Pabna', 'পাবনা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(14, 2, 'Bogura', 'বগুড়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(15, 2, 'Rajshahi', 'রাজশাহী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(16, 2, 'Natore', 'নাটোর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(17, 2, 'Joypurhat', 'জয়পুরহাট', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(18, 2, 'Chapainawabganj', 'চাঁপাইনবাবগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(19, 2, 'Naogaon', 'নওগাঁ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(20, 3, 'Jashore', 'যশোর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(21, 3, 'Satkhira', 'সাতক্ষীরা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(22, 3, 'Meherpur', 'মেহেরপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(23, 3, 'Narail', 'নড়াইল', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(24, 3, 'Chuadanga', 'চুয়াডাঙ্গা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(25, 3, 'Kushtia', 'কুষ্টিয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(26, 3, 'Magura', 'মাগুরা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(27, 3, 'Khulna', 'খুলনা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(28, 3, 'Bagerhat', 'বাগেরহাট', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(29, 3, 'Jhenaidah', 'ঝিনাইদহ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(30, 4, 'Jhalakathi', 'ঝালকাঠি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(31, 4, 'Patuakhali', 'পটুয়াখালী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(32, 4, 'Pirojpur', 'পিরোজপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(33, 4, 'Barisal', 'বরিশাল', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(34, 4, 'Bhola', 'ভোলা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(35, 4, 'Barguna', 'বরগুনা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(36, 5, 'Sylhet', 'সিলেট', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(37, 5, 'Moulvibazar', 'মৌলভীবাজার', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(38, 5, 'Habiganj', 'হবিগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(39, 5, 'Sunamganj', 'সুনামগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(40, 6, 'Narsingdi', 'নরসিংদী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(41, 6, 'Gazipur', 'গাজীপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(42, 6, 'Shariatpur', 'শরীয়তপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(43, 6, 'Narayanganj', 'নারায়ণগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(44, 6, 'Tangail', 'টাঙ্গাইল', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(45, 6, 'Kishoreganj', 'কিশোরগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(46, 6, 'Manikganj', 'মানিকগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(47, 6, 'Dhaka', 'ঢাকা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(48, 6, 'Munshiganj', 'মুন্সিগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(49, 6, 'Rajbari', 'রাজবাড়ী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(50, 6, 'Madaripur', 'মাদারীপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(51, 6, 'Gopalganj', 'গোপালগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(52, 6, 'Faridpur', 'ফরিদপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(53, 7, 'Panchagarh', 'পঞ্চগড়', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(54, 7, 'Dinajpur', 'দিনাজপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(55, 7, 'Lalmonirhat', 'লালমনিরহাট', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(56, 7, 'Nilphamari', 'নীলফামারী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(57, 7, 'Gaibandha', 'গাইবান্ধা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(58, 7, 'Thakurgaon', 'ঠাকুরগাঁও', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(59, 7, 'Rangpur', 'রংপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(60, 7, 'Kurigram', 'কুড়িগ্রাম', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(61, 8, 'Sherpur', 'শেরপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(62, 8, 'Mymensingh', 'ময়মনসিংহ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(63, 8, 'Jamalpur', 'জামালপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(64, 8, 'Netrokona', 'নেত্রকোণা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09');

-- --------------------------------------------------------

--
-- Table structure for table `divisions`
--

CREATE TABLE `divisions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `name_bn` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `divisions`
--

INSERT INTO `divisions` (`id`, `name`, `name_bn`, `status`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Chattagram', 'চট্টগ্রাম', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(2, 'Rajshahi', 'রাজশাহী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(3, 'Khulna', 'খুলনা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(4, 'Barisal', 'বরিশাল', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(5, 'Sylhet', 'সিলেট', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(6, 'Dhaka', 'ঢাকা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(7, 'Rangpur', 'রংপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(8, 'Mymensingh', 'ময়মনসিংহ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09');

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `license_number` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gps_logs`
--

CREATE TABLE `gps_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `lat` decimal(10,7) NOT NULL,
  `lng` decimal(10,7) NOT NULL,
  `recorded_at` datetime NOT NULL,
  `accuracy` decimal(6,2) DEFAULT NULL,
  `speed` decimal(6,2) DEFAULT NULL,
  `battery_level` tinyint(3) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gps_logs`
--

INSERT INTO `gps_logs` (`id`, `user_id`, `lat`, `lng`, `recorded_at`, `accuracy`, `speed`, `battery_level`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 80, 23.7067420, 90.4238680, '2026-08-24 00:00:00', 49.16, 1.60, 26, 80, NULL, NULL, NULL, '2026-08-23 21:37:52', '2026-08-23 21:37:52'),
(2, 80, 23.7054540, 90.4241470, '2026-08-24 00:20:00', 11.98, 42.27, 58, 80, NULL, NULL, NULL, '2026-08-23 21:37:52', '2026-08-23 21:37:52'),
(3, 80, 23.7044590, 90.4259120, '2026-08-24 00:40:00', 29.83, 45.42, 31, 80, NULL, NULL, NULL, '2026-08-23 21:37:52', '2026-08-23 21:37:52'),
(4, 80, 23.7042960, 90.4248850, '2026-08-24 01:00:00', 26.63, 37.99, 48, 80, NULL, NULL, NULL, '2026-08-23 21:37:52', '2026-08-23 21:37:52'),
(5, 80, 23.7066350, 90.4227150, '2026-08-24 01:20:00', 17.98, 56.60, 80, 80, NULL, NULL, NULL, '2026-08-23 21:37:52', '2026-08-23 21:37:52'),
(6, 80, 23.7041980, 90.4221690, '2026-08-24 01:40:00', 49.44, 36.82, 35, 80, NULL, NULL, NULL, '2026-08-23 21:37:52', '2026-08-23 21:37:52'),
(7, 80, 23.7061090, 90.4261210, '2026-08-24 02:00:00', 5.99, 21.44, 42, 80, NULL, NULL, NULL, '2026-08-23 21:37:52', '2026-08-23 21:37:52'),
(8, 80, 23.7085530, 90.4259930, '2026-08-24 02:20:00', 11.05, 41.55, 50, 80, NULL, NULL, NULL, '2026-08-23 21:37:52', '2026-08-23 21:37:52'),
(9, 80, 23.7123290, 90.4251800, '2026-08-24 02:40:00', 41.56, 19.32, 39, 80, NULL, NULL, NULL, '2026-08-23 21:37:52', '2026-08-23 21:37:52'),
(10, 80, 23.7153850, 90.4276500, '2026-08-24 03:00:00', 7.61, 20.43, 74, 80, NULL, NULL, NULL, '2026-08-23 21:37:52', '2026-08-23 21:37:52'),
(11, 80, 23.7153150, 90.4304700, '2026-08-24 03:20:00', 45.63, 33.16, 99, 80, NULL, NULL, NULL, '2026-08-23 21:37:52', '2026-08-23 21:37:52'),
(12, 80, 23.7133440, 90.4297680, '2026-08-24 03:37:52', 36.39, 51.59, 49, 80, NULL, NULL, NULL, '2026-08-23 21:37:52', '2026-08-23 21:37:52'),
(13, 81, 23.7408360, 90.3876050, '2026-08-24 00:00:00', 28.43, 20.47, 25, 81, NULL, NULL, NULL, '2026-08-23 21:37:52', '2026-08-23 21:37:52'),
(14, 81, 23.7439940, 90.3885960, '2026-08-24 00:20:00', 23.84, 16.98, 72, 81, NULL, NULL, NULL, '2026-08-23 21:37:52', '2026-08-23 21:37:52'),
(15, 81, 23.7459870, 90.3872360, '2026-08-24 00:40:00', 48.52, 31.90, 43, 81, NULL, NULL, NULL, '2026-08-23 21:37:52', '2026-08-23 21:37:52'),
(16, 81, 23.7498190, 90.3868000, '2026-08-24 01:00:00', 25.01, 35.81, 26, 81, NULL, NULL, NULL, '2026-08-23 21:37:52', '2026-08-23 21:37:52'),
(17, 81, 23.7499060, 90.3886280, '2026-08-24 01:20:00', 31.99, 8.52, 63, 81, NULL, NULL, NULL, '2026-08-23 21:37:52', '2026-08-23 21:37:52'),
(18, 81, 23.7471540, 90.3919660, '2026-08-24 01:40:00', 20.61, 48.66, 70, 81, NULL, NULL, NULL, '2026-08-23 21:37:52', '2026-08-23 21:37:52'),
(19, 81, 23.7488950, 90.3920080, '2026-08-24 02:00:00', 27.12, 17.62, 96, 81, NULL, NULL, NULL, '2026-08-23 21:37:52', '2026-08-23 21:37:52'),
(20, 81, 23.7521110, 90.3931600, '2026-08-24 02:20:00', 36.37, 59.61, 43, 81, NULL, NULL, NULL, '2026-08-23 21:37:52', '2026-08-23 21:37:52'),
(21, 81, 23.7510950, 90.3905410, '2026-08-24 02:40:00', 47.78, 22.58, 99, 81, NULL, NULL, NULL, '2026-08-23 21:37:52', '2026-08-23 21:37:52'),
(22, 81, 23.7536770, 90.3894180, '2026-08-24 03:00:00', 39.28, 7.99, 84, 81, NULL, NULL, NULL, '2026-08-23 21:37:52', '2026-08-23 21:37:52'),
(23, 81, 23.7501310, 90.3925650, '2026-08-24 03:20:00', 37.40, 40.66, 19, 81, NULL, NULL, NULL, '2026-08-23 21:37:52', '2026-08-23 21:37:52'),
(24, 81, 23.7499450, 90.3959900, '2026-08-24 03:37:52', 38.60, 50.53, 51, 81, NULL, NULL, NULL, '2026-08-23 21:37:52', '2026-08-23 21:37:52'),
(25, 1, 23.8103000, 90.4125000, '2026-08-27 09:20:00', 12.50, 8.20, 87, 1, NULL, NULL, NULL, '2026-08-27 01:48:46', '2026-08-27 01:48:46'),
(26, 1, 23.8110000, 90.4130000, '2026-08-27 09:40:00', 10.00, 5.00, 85, 1, NULL, NULL, NULL, '2026-08-27 01:48:46', '2026-08-27 01:48:46'),
(27, 1, 23.8103000, 90.4125000, '2026-08-27 09:20:00', 12.50, 8.20, 87, 1, NULL, NULL, NULL, '2026-08-27 01:49:44', '2026-08-27 01:49:44'),
(28, 1, 23.8110000, 90.4130000, '2026-08-27 09:40:00', 10.00, 5.00, 85, 1, NULL, NULL, NULL, '2026-08-27 01:49:44', '2026-08-27 01:49:44'),
(29, 1, 23.8103000, 90.4125000, '2026-08-27 09:20:00', 12.50, 8.20, 87, 1, NULL, NULL, NULL, '2026-08-27 01:50:13', '2026-08-27 01:50:13'),
(30, 1, 23.8110000, 90.4130000, '2026-08-27 09:40:00', 10.00, 5.00, 85, 1, NULL, NULL, NULL, '2026-08-27 01:50:13', '2026-08-27 01:50:13'),
(31, 1, 23.8103000, 90.4125000, '2026-08-27 09:20:00', 12.50, 8.20, 87, 1, NULL, NULL, NULL, '2026-08-27 02:21:53', '2026-08-27 02:21:53'),
(32, 1, 23.8110000, 90.4130000, '2026-08-27 09:40:00', 10.00, 5.00, 85, 1, NULL, NULL, NULL, '2026-08-27 02:21:53', '2026-08-27 02:21:53'),
(33, 1, 23.8103000, 90.4125000, '2026-08-27 09:20:00', 12.50, 8.20, 87, 1, NULL, NULL, NULL, '2026-08-27 02:22:29', '2026-08-27 02:22:29'),
(34, 1, 23.8110000, 90.4130000, '2026-08-27 09:40:00', 10.00, 5.00, 85, 1, NULL, NULL, NULL, '2026-08-27 02:22:29', '2026-08-27 02:22:29'),
(35, 1, 23.8103000, 90.4125000, '2026-08-27 09:20:00', 12.50, 8.20, 87, 1, NULL, NULL, NULL, '2026-08-27 02:51:58', '2026-08-27 02:51:58'),
(36, 1, 23.8110000, 90.4130000, '2026-08-27 09:40:00', 10.00, 5.00, 85, 1, NULL, NULL, NULL, '2026-08-27 02:51:58', '2026-08-27 02:51:58'),
(37, 1, 23.8103000, 90.4125000, '2026-08-27 09:20:00', 12.50, 8.20, 87, 1, NULL, NULL, NULL, '2026-08-27 03:18:48', '2026-08-27 03:18:48'),
(38, 1, 23.8110000, 90.4130000, '2026-08-27 09:40:00', 10.00, 5.00, 85, 1, NULL, NULL, NULL, '2026-08-27 03:18:48', '2026-08-27 03:18:48'),
(39, 89, 23.7371540, 90.4314500, '2026-08-30 09:00:00', 20.37, 20.00, 66, NULL, NULL, NULL, NULL, '2026-08-30 04:52:19', '2026-08-30 04:52:19'),
(40, 89, 23.7797260, 90.4390090, '2026-08-30 09:30:00', 12.41, 0.00, 84, NULL, NULL, NULL, NULL, '2026-08-30 04:52:19', '2026-08-30 04:52:19'),
(41, 89, 23.7771350, 90.3800410, '2026-08-30 10:00:00', 40.85, 20.00, 97, NULL, NULL, NULL, NULL, '2026-08-30 04:52:19', '2026-08-30 04:52:19'),
(42, 90, 23.8046750, 90.3801170, '2026-08-30 09:00:00', 32.47, 20.00, 81, NULL, NULL, NULL, NULL, '2026-08-30 04:53:16', '2026-08-30 04:53:16'),
(43, 90, 23.7058990, 90.3898940, '2026-08-30 09:30:00', 33.76, 0.00, 57, NULL, NULL, NULL, NULL, '2026-08-30 04:53:16', '2026-08-30 04:53:16'),
(44, 90, 23.7856380, 90.4402430, '2026-08-30 10:00:00', 29.85, 20.00, 27, NULL, NULL, NULL, NULL, '2026-08-30 04:53:16', '2026-08-30 04:53:16'),
(45, 80, 23.8419530, 90.3926870, '2026-08-30 09:02:00', 7.59, 15.00, 63, NULL, NULL, NULL, NULL, '2026-08-30 04:55:32', '2026-08-30 04:55:32'),
(46, 80, 23.7070810, 90.4304300, '2026-08-30 09:20:00', 19.49, 0.00, 50, NULL, NULL, NULL, NULL, '2026-08-30 04:55:32', '2026-08-30 04:55:32'),
(47, 80, 23.8220910, 90.3871020, '2026-08-30 09:45:00', 41.83, 18.00, 53, NULL, NULL, NULL, NULL, '2026-08-30 04:55:32', '2026-08-30 04:55:32'),
(48, 80, 23.8311610, 90.4272310, '2026-08-30 10:10:00', 12.32, 0.00, 48, NULL, NULL, NULL, NULL, '2026-08-30 04:55:32', '2026-08-30 04:55:32'),
(49, 80, 23.7389290, 90.3616330, '2026-08-30 10:40:00', 19.47, 22.00, 49, NULL, NULL, NULL, NULL, '2026-08-30 04:55:32', '2026-08-30 04:55:32'),
(50, 80, 23.8440640, 90.4069720, '2026-08-30 11:15:00', 46.21, 0.00, 88, NULL, NULL, NULL, NULL, '2026-08-30 04:55:32', '2026-08-30 04:55:32'),
(51, 80, 23.8451440, 90.3968940, '2026-08-30 12:00:00', 41.71, 0.00, 66, NULL, NULL, NULL, NULL, '2026-08-30 04:55:32', '2026-08-30 04:55:32'),
(52, 80, 23.8117670, 90.3950140, '2026-08-30 13:30:00', 10.96, 25.00, 35, NULL, NULL, NULL, NULL, '2026-08-30 04:55:32', '2026-08-30 04:55:32'),
(53, 80, 23.7152510, 90.4086210, '2026-08-30 14:15:00', 5.17, 0.00, 53, NULL, NULL, NULL, NULL, '2026-08-30 04:55:32', '2026-08-30 04:55:32'),
(54, 80, 23.7616710, 90.4000400, '2026-08-30 15:00:00', 42.00, 20.00, 49, NULL, NULL, NULL, NULL, '2026-08-30 04:55:32', '2026-08-30 04:55:32'),
(55, 80, 23.7237980, 90.4175080, '2026-08-30 16:20:00', 3.66, 0.00, 86, NULL, NULL, NULL, NULL, '2026-08-30 04:55:32', '2026-08-30 04:55:32'),
(56, 80, 23.7649060, 90.3752500, '2026-08-30 17:30:00', 40.73, 12.00, 44, NULL, NULL, NULL, NULL, '2026-08-30 04:55:32', '2026-08-30 04:55:32'),
(57, 80, 23.7089110, 90.4390580, '2026-08-30 18:05:00', 21.35, 0.00, 31, NULL, NULL, NULL, NULL, '2026-08-30 04:55:32', '2026-08-30 04:55:32');

-- --------------------------------------------------------

--
-- Table structure for table `holidays`
--

CREATE TABLE `holidays` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

CREATE TABLE `inquiries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'new',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

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
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_balances`
--

CREATE TABLE `leave_balances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `leave_type_id` bigint(20) UNSIGNED NOT NULL,
  `year` smallint(5) UNSIGNED NOT NULL,
  `entitled_days` decimal(5,1) NOT NULL DEFAULT 0.0,
  `carried_forward_days` decimal(5,1) NOT NULL DEFAULT 0.0,
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_balances`
--

INSERT INTO `leave_balances` (`id`, `user_id`, `leave_type_id`, `year`, `entitled_days`, `carried_forward_days`, `remarks`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 91, 3, 2026, 20.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(2, 91, 1, 2026, 10.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(3, 91, 2, 2026, 14.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(4, 91, 4, 2026, 0.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(5, 89, 3, 2026, 20.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(6, 89, 1, 2026, 10.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(7, 89, 2, 2026, 14.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(8, 89, 4, 2026, 0.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(9, 90, 3, 2026, 20.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(10, 90, 1, 2026, 10.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(11, 90, 2, 2026, 14.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(12, 90, 4, 2026, 0.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(13, 86, 3, 2026, 20.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(14, 86, 1, 2026, 10.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(15, 86, 2, 2026, 14.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(16, 86, 4, 2026, 0.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(17, 74, 3, 2026, 20.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(18, 74, 1, 2026, 10.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(19, 74, 2, 2026, 14.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(20, 74, 4, 2026, 0.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(21, 82, 3, 2026, 20.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(22, 82, 1, 2026, 10.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(23, 82, 2, 2026, 14.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(24, 82, 4, 2026, 0.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(25, 80, 3, 2026, 20.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(26, 80, 1, 2026, 10.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(27, 80, 2, 2026, 14.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(28, 80, 4, 2026, 0.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(29, 81, 3, 2026, 20.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(30, 81, 1, 2026, 10.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(31, 81, 2, 2026, 14.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(32, 81, 4, 2026, 0.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(33, 92, 3, 2026, 20.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(34, 92, 1, 2026, 10.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(35, 92, 2, 2026, 14.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(36, 92, 4, 2026, 0.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(37, 1, 3, 2026, 20.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(38, 1, 1, 2026, 10.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(39, 1, 2, 2026, 14.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13'),
(40, 1, 4, 2026, 0.0, 0.0, NULL, 1, NULL, NULL, NULL, '2026-09-09 06:19:13', '2026-09-09 06:19:13');

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `leave_type_id` bigint(20) UNSIGNED NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `days` decimal(5,1) NOT NULL,
  `is_half_day` tinyint(1) NOT NULL DEFAULT 0,
  `reason` text NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `decision_remarks` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `annual_quota` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_paid` tinyint(1) NOT NULL DEFAULT 1,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_types`
--

INSERT INTO `leave_types` (`id`, `name`, `code`, `description`, `annual_quota`, `is_paid`, `status`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Casual Leave', 'CL', NULL, 10, 1, 1, NULL, NULL, NULL, NULL, '2026-09-09 04:16:52', '2026-09-09 04:16:52'),
(2, 'Sick Leave', 'SL', NULL, 14, 1, 1, NULL, NULL, NULL, NULL, '2026-09-09 04:16:52', '2026-09-09 04:16:52'),
(3, 'Annual Leave', 'AL', NULL, 20, 1, 1, NULL, NULL, NULL, NULL, '2026-09-09 04:16:52', '2026-09-09 04:16:52'),
(4, 'Unpaid Leave', 'UL', NULL, 0, 0, 1, NULL, NULL, NULL, NULL, '2026-09-09 04:16:52', '2026-09-09 04:16:52');

-- --------------------------------------------------------

--
-- Table structure for table `ledger_entries`
--

CREATE TABLE `ledger_entries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dealer_id` bigint(20) UNSIGNED NOT NULL,
  `tally_guid` varchar(255) NOT NULL,
  `voucher_date` date NOT NULL,
  `voucher_type` varchar(255) NOT NULL,
  `voucher_number` varchar(255) DEFAULT NULL,
  `debit_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `credit_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `narration` text DEFAULT NULL,
  `synced_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ledger_entries`
--

INSERT INTO `ledger_entries` (`id`, `dealer_id`, `tally_guid`, `voucher_date`, `voucher_type`, `voucher_number`, `debit_amount`, `credit_amount`, `narration`, `synced_at`, `created_at`, `updated_at`) VALUES
(1, 18, 'd530416d-350a-4df9-bc3c-06943927816a-00000003-0', '2026-09-01', 'Sales', '1', 125000.00, 0.00, NULL, '2026-09-21 11:13:39', '2026-09-08 05:57:26', '2026-09-21 11:13:39'),
(2, 19, 'd530416d-350a-4df9-bc3c-06943927816a-00000001-1', '2026-09-01', 'Journal', '1', 0.00, 50.00, NULL, '2026-09-21 11:13:39', '2026-09-08 05:57:26', '2026-09-21 11:13:39');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_08_02_113814_create_personal_access_tokens_table', 1),
(5, '2026_08_02_113819_create_permission_tables', 1),
(6, '2026_08_02_113821_create_activity_log_table', 1),
(7, '2026_08_02_113822_add_event_column_to_activity_log_table', 1),
(8, '2026_08_02_113823_add_batch_uuid_column_to_activity_log_table', 1),
(9, '2026_08_03_033356_add_sfa_fields_to_users_table', 1),
(10, '2026_08_03_042553_create_sales_teams_table', 1),
(11, '2026_08_03_042556_create_territories_table', 1),
(12, '2026_08_03_042560_add_org_structure_fields_to_users_table', 1),
(13, '2026_08_03_050105_create_customers_table', 1),
(14, '2026_08_03_052745_create_product_categories_table', 1),
(15, '2026_08_03_052746_create_products_table', 1),
(16, '2026_08_03_060000_create_attendances_table', 1),
(17, '2026_08_03_070000_create_gps_logs_table', 1),
(18, '2026_08_03_080000_create_visit_plans_table', 1),
(19, '2026_08_03_080100_create_visits_table', 1),
(20, '2026_08_04_090000_create_sales_entries_table', 1),
(21, '2026_08_04_090100_create_sales_entry_items_table', 1),
(22, '2026_08_04_100000_create_collection_entries_table', 1),
(23, '2026_08_04_110000_create_targets_table', 1),
(24, '2026_08_04_110100_create_achievements_table', 1),
(25, '2026_08_04_120000_create_notifications_table', 1),
(26, '2026_08_04_120100_create_announcements_table', 1),
(27, '2026_08_04_120200_add_date_of_birth_to_users_table', 1),
(28, '2026_08_04_130000_create_settings_table', 1),
(29, '2026_08_04_140000_create_news_table', 1),
(30, '2026_08_04_140100_create_promotions_table', 1),
(31, '2026_08_04_140200_create_faqs_table', 1),
(32, '2026_08_04_140300_create_service_centers_table', 1),
(33, '2026_08_04_140400_create_brochures_table', 1),
(34, '2026_08_04_140500_create_customer_accounts_table', 1),
(35, '2026_08_04_140600_create_inquiries_table', 1),
(36, '2026_08_04_140700_create_visit_requests_table', 1),
(37, '2026_08_06_150000_add_hardening_indexes', 1),
(38, '2026_08_06_160000_create_customer_password_reset_tokens_table', 1),
(39, '2026_08_13_092042_make_employee_id_nullable_on_users_table', 2),
(40, '2026_08_22_100000_rename_customers_table_to_dealers', 3),
(41, '2026_08_22_100100_rename_customer_id_to_dealer_id_on_dependents', 4),
(42, '2026_08_22_100200_rename_sales_entries_table_to_orders', 5),
(43, '2026_08_22_100300_rename_sales_entry_items_table_to_order_items', 6),
(44, '2026_08_22_100400_rename_customer_and_sales_entry_permissions', 7),
(45, '2026_08_22_100500_rename_sale_date_to_order_date_on_orders', 8),
(46, '2026_08_22_100600_rename_activity_log_names_for_dealer_and_order', 9),
(47, '2026_08_22_100700_rename_sales_columns_to_order_on_targets_and_achievements', 10),
(48, '2026_08_22_100800_rename_sales_max_discount_percent_on_settings', 11),
(49, '2026_08_22_150000_create_divisions_table', 12),
(50, '2026_08_22_150100_create_districts_table', 13),
(51, '2026_08_22_150200_create_thanas_table', 14),
(52, '2026_08_22_150300_add_geo_hierarchy_to_territories_table', 15),
(53, '2026_08_22_150400_add_geo_hierarchy_to_dealers_table', 16),
(54, '2026_08_22_160000_create_territory_user_table', 17),
(55, '2026_08_22_169000_drop_territory_id_from_users_table', 18),
(56, '2026_08_23_043203_add_territory_id_to_visit_plans_table', 18),
(57, '2026_08_23_074508_add_office_end_time_and_weekend_days_to_settings_table', 19),
(58, '2026_08_23_104011_add_company_favicon_to_settings_table', 20),
(59, '2026_08_23_111829_add_image_to_dealers_table', 21),
(60, '2026_08_25_081627_add_cheque_image_to_collection_entries_table', 22),
(61, '2026_08_25_094930_add_sales_team_id_to_products_table', 23),
(62, '2026_08_25_104507_create_holidays_table', 24),
(63, '2026_08_27_035120_add_audience_dealer_id_to_announcements_table', 25),
(64, '2026_08_27_045000_create_retailers_table', 26),
(65, '2026_08_27_045012_add_retailer_id_to_orders_table', 26),
(66, '2026_08_27_045021_create_target_items_table', 26),
(67, '2026_08_27_053212_drop_type_from_dealers_table', 27),
(68, '2026_08_27_060806_add_cheque_status_to_collection_entries_table', 28),
(69, '2026_08_27_061139_add_sms_gateway_settings_to_settings_table', 29),
(70, '2026_08_27_061345_create_collection_otps_table', 30),
(71, '2026_08_27_061420_add_otp_verified_at_to_collection_entries_table', 30),
(72, '2026_08_27_070000_create_cash_handovers_table', 31),
(73, '2026_08_27_070010_add_cash_daily_limit_amount_to_settings_table', 31),
(74, '2026_08_27_090000_add_approval_status_to_orders_table', 32),
(75, '2026_08_27_090010_add_approval_status_to_collection_entries_table', 32),
(76, '2026_08_27_104040_add_parent_id_to_product_categories_table', 33),
(77, '2026_08_31_090000_create_achievement_entries_table', 34),
(79, '2026_08_31_090010_create_achievement_items_table', 35),
(80, '2026_09_07_090000_create_tally_connections_table', 36),
(81, '2026_09_07_090010_create_tally_mappings_table', 37),
(82, '2026_09_07_090020_create_sync_queues_table', 38),
(83, '2026_09_07_090030_create_sync_logs_table', 39),
(84, '2026_09_07_090040_add_tally_guid_to_dealers_table', 40),
(85, '2026_09_07_090050_add_tally_guid_to_retailers_table', 41),
(86, '2026_09_07_090060_add_tally_guid_to_products_table', 42),
(87, '2026_09_07_100000_add_tally_sync_fields_to_orders_table', 43),
(88, '2026_09_07_100010_add_tally_sync_fields_to_collection_entries_table', 44),
(89, '2026_09_07_100020_add_tally_ledger_name_to_dealers_table', 45),
(90, '2026_09_07_110000_create_depots_table', 46),
(91, '2026_09_07_110010_create_product_stocks_table', 47),
(92, '2026_09_07_110020_create_depot_allocations_table', 48),
(93, '2026_09_07_131259_create_vehicles_table', 49),
(94, '2026_09_07_131300_create_drivers_table', 49),
(96, '2026_09_07_131345_create_deliveries_table', 50),
(97, '2026_09_07_133500_create_ledger_entries_table', 51),
(98, '2026_09_07_135345_create_sales_returns_table', 52),
(99, '2026_09_07_135346_create_sales_return_items_table', 52),
(100, '2026_09_09_100000_create_leave_types_table', 53),
(101, '2026_09_09_100001_create_leave_requests_table', 53),
(102, '2026_09_09_100002_create_leave_balances_table', 53);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(1, 'App\\Models\\User', 74),
(2, 'App\\Models\\User', 93),
(6, 'App\\Models\\User', 80),
(6, 'App\\Models\\User', 81),
(6, 'App\\Models\\User', 89),
(6, 'App\\Models\\User', 90),
(6, 'App\\Models\\User', 91);

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `excerpt` varchar(255) DEFAULT NULL,
  `body` longtext NOT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `published_at` datetime DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('dab3cdaa-dec6-44c6-a0d8-3f784666c003', 'App\\Notifications\\AnnouncementNotification', 'App\\Models\\User', 1, '{\"type\":\"announcement\",\"title\":\"Postman verification\",\"message\":\"Et magnam quas autem architecto neque rem in eligendi. Tenetur praesentium dolorem a fuga ea. Fugiat voluptatem repudiandae soluta sed id qui. Facilis quia ut nesciunt.\",\"announcement_id\":null,\"sent_by\":\"Everett Kuvalis II\"}', '2026-08-27 01:49:51', '2026-08-27 01:49:25', '2026-08-27 01:49:51');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `dealer_id` bigint(20) UNSIGNED NOT NULL,
  `retailer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `order_date` date NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `remarks` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `external_reference` varchar(255) DEFAULT NULL,
  `tally_guid` varchar(255) DEFAULT NULL,
  `tally_order_number` varchar(255) DEFAULT NULL,
  `sync_status` varchar(255) NOT NULL DEFAULT 'not_synced',
  `sync_error` text DEFAULT NULL,
  `synced_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `dealer_id`, `retailer_id`, `order_date`, `total_amount`, `remarks`, `status`, `external_reference`, `tally_guid`, `tally_order_number`, `sync_status`, `sync_error`, `synced_at`, `approved_by`, `approved_at`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 80, 1, NULL, '2026-08-23', 2627944.00, NULL, 'approved', NULL, NULL, NULL, 'not_synced', NULL, NULL, 1, '2026-08-27 01:11:56', NULL, 1, NULL, NULL, '2026-08-23 05:34:25', '2026-08-27 01:11:56'),
(2, 81, 2, NULL, '2026-08-22', 405871.76, NULL, 'rejected', NULL, NULL, NULL, 'not_synced', NULL, NULL, 1, '2026-08-27 01:12:04', NULL, 1, NULL, NULL, '2026-08-23 05:34:25', '2026-08-27 01:12:04'),
(3, 80, 3, NULL, '2026-08-21', 341712.00, NULL, 'pending', NULL, NULL, NULL, 'not_synced', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:34:25', '2026-08-23 05:34:25'),
(4, 81, 4, NULL, '2026-08-20', 130625.60, NULL, 'pending', NULL, NULL, NULL, 'not_synced', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:34:25', '2026-08-23 05:34:25'),
(5, 80, 5, NULL, '2026-08-19', 101871.00, NULL, 'pending', NULL, NULL, NULL, 'not_synced', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:34:25', '2026-08-23 05:34:25'),
(6, 1, 1, NULL, '2026-08-27', 42400.00, 'Restocked shelf display.', 'approved', NULL, NULL, NULL, 'not_synced', NULL, NULL, 1, '2026-08-29 23:04:31', 1, 1, NULL, NULL, '2026-08-27 01:48:50', '2026-08-29 23:04:31'),
(7, 1, 1, NULL, '2026-08-27', 42400.00, 'Restocked shelf display.', 'rejected', NULL, NULL, NULL, 'not_synced', NULL, NULL, 1, '2026-08-29 23:06:08', 1, 1, NULL, NULL, '2026-08-27 01:49:47', '2026-08-29 23:06:08'),
(8, 1, 1, NULL, '2026-08-27', 42400.00, 'Restocked shelf display.', 'approved', NULL, NULL, NULL, 'not_synced', NULL, NULL, 1, '2026-08-29 23:48:58', 1, 1, NULL, NULL, '2026-08-27 01:50:17', '2026-08-29 23:48:58'),
(9, 1, 1, NULL, '2026-08-27', 42400.00, 'Restocked shelf display.', 'rejected', NULL, NULL, NULL, 'not_synced', NULL, NULL, 1, '2026-08-29 23:49:02', 1, 1, NULL, NULL, '2026-08-27 02:21:57', '2026-08-29 23:49:02'),
(10, 1, 1, NULL, '2026-08-27', 42400.00, 'Restocked shelf display.', 'pending', NULL, NULL, NULL, 'not_synced', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-08-27 02:22:32', '2026-08-27 02:22:32'),
(11, 1, 1, NULL, '2026-08-27', 42400.00, 'Restocked shelf display.', 'pending', NULL, NULL, NULL, 'not_synced', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-08-27 02:52:02', '2026-08-27 02:52:02'),
(12, 1, 1, NULL, '2026-08-27', 42400.00, 'Restocked shelf display.', 'pending', NULL, NULL, NULL, 'not_synced', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-08-27 03:18:52', '2026-08-27 03:18:52'),
(13, 80, 1, NULL, '2026-06-12', 3600.00, NULL, 'pending', NULL, NULL, NULL, 'not_synced', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-27 03:30:45', '2026-08-27 03:30:45');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `unit_price`, `discount_amount`, `total_amount`, `created_at`, `updated_at`) VALUES
(1, 1, 9, 14, 185000.00, 0.00, 2590000.00, '2026-08-23 05:34:25', '2026-08-23 05:34:25'),
(2, 1, 26, 10, 4080.00, 2856.00, 37944.00, '2026-08-23 05:34:25', '2026-08-23 05:34:25'),
(3, 2, 16, 16, 11220.00, 0.00, 179520.00, '2026-08-23 05:34:25', '2026-08-23 05:34:25'),
(4, 2, 28, 9, 3672.00, 4296.24, 28751.76, '2026-08-23 05:34:25', '2026-08-23 05:34:25'),
(5, 2, 37, 8, 24700.00, 0.00, 197600.00, '2026-08-23 05:34:25', '2026-08-23 05:34:25'),
(6, 3, 32, 17, 11088.00, 0.00, 188496.00, '2026-08-23 05:34:25', '2026-08-23 05:34:25'),
(7, 3, 40, 19, 8400.00, 6384.00, 153216.00, '2026-08-23 05:34:25', '2026-08-23 05:34:25'),
(8, 4, 2, 12, 8200.00, 3936.00, 94464.00, '2026-08-23 05:34:25', '2026-08-23 05:34:25'),
(9, 4, 14, 20, 1864.00, 1118.40, 36161.60, '2026-08-23 05:34:25', '2026-08-23 05:34:25'),
(10, 5, 39, 11, 9450.00, 2079.00, 101871.00, '2026-08-23 05:34:25', '2026-08-23 05:34:25'),
(11, 6, 1, 4, 6500.00, 0.00, 26000.00, '2026-08-27 01:48:50', '2026-08-27 01:48:50'),
(12, 6, 2, 2, 8200.00, 0.00, 16400.00, '2026-08-27 01:48:50', '2026-08-27 01:48:50'),
(13, 7, 1, 4, 6500.00, 0.00, 26000.00, '2026-08-27 01:49:47', '2026-08-27 01:49:47'),
(14, 7, 2, 2, 8200.00, 0.00, 16400.00, '2026-08-27 01:49:47', '2026-08-27 01:49:47'),
(15, 8, 1, 4, 6500.00, 0.00, 26000.00, '2026-08-27 01:50:17', '2026-08-27 01:50:17'),
(16, 8, 2, 2, 8200.00, 0.00, 16400.00, '2026-08-27 01:50:17', '2026-08-27 01:50:17'),
(17, 9, 1, 4, 6500.00, 0.00, 26000.00, '2026-08-27 02:21:57', '2026-08-27 02:21:57'),
(18, 9, 2, 2, 8200.00, 0.00, 16400.00, '2026-08-27 02:21:57', '2026-08-27 02:21:57'),
(19, 10, 1, 4, 6500.00, 0.00, 26000.00, '2026-08-27 02:22:32', '2026-08-27 02:22:32'),
(20, 10, 2, 2, 8200.00, 0.00, 16400.00, '2026-08-27 02:22:32', '2026-08-27 02:22:32'),
(21, 11, 1, 4, 6500.00, 0.00, 26000.00, '2026-08-27 02:52:02', '2026-08-27 02:52:02'),
(22, 11, 2, 2, 8200.00, 0.00, 16400.00, '2026-08-27 02:52:02', '2026-08-27 02:52:02'),
(23, 12, 1, 4, 6500.00, 0.00, 26000.00, '2026-08-27 03:18:52', '2026-08-27 03:18:52'),
(24, 12, 2, 2, 8200.00, 0.00, 16400.00, '2026-08-27 03:18:52', '2026-08-27 03:18:52'),
(25, 13, 1, 12, 200.00, 0.00, 2400.00, '2026-08-27 03:30:45', '2026-08-27 03:30:45'),
(26, 13, 2, 6, 200.00, 0.00, 1200.00, '2026-08-27 03:30:45', '2026-08-27 03:30:45');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'menu.dashboard', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(2, 'menu.users', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(3, 'users.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(4, 'users.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(5, 'users.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(6, 'users.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(7, 'users.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(8, 'users.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(9, 'users.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(10, 'users.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(11, 'users.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(12, 'api.users.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(13, 'menu.roles', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(14, 'roles.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(15, 'roles.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(16, 'roles.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(17, 'roles.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(18, 'roles.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(19, 'roles.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(20, 'roles.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(21, 'roles.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(22, 'roles.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(23, 'api.roles.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(24, 'menu.permissions', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(25, 'permissions.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(26, 'permissions.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(27, 'permissions.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(28, 'permissions.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(29, 'permissions.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(30, 'permissions.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(31, 'permissions.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(32, 'permissions.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(33, 'permissions.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(34, 'menu.sales-teams', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(35, 'sales-teams.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(36, 'sales-teams.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(37, 'sales-teams.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(38, 'sales-teams.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(39, 'sales-teams.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(40, 'sales-teams.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(41, 'sales-teams.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(42, 'sales-teams.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(43, 'sales-teams.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(44, 'api.sales-teams.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(45, 'menu.territories', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(46, 'territories.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(47, 'territories.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(48, 'territories.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(49, 'territories.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(50, 'territories.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(51, 'territories.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(52, 'territories.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(53, 'territories.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(54, 'territories.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(55, 'api.territories.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(56, 'menu.dealers', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(57, 'dealers.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(58, 'dealers.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(59, 'dealers.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(60, 'dealers.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(61, 'dealers.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(62, 'dealers.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(63, 'dealers.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(64, 'dealers.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(65, 'dealers.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(66, 'api.dealers.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(67, 'api.dealers.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(68, 'menu.product-categories', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(69, 'product-categories.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(70, 'product-categories.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(71, 'product-categories.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(72, 'product-categories.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(73, 'product-categories.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(74, 'product-categories.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(75, 'product-categories.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(76, 'product-categories.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(77, 'product-categories.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(78, 'api.product-categories.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(79, 'menu.products', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(80, 'products.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(81, 'products.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(82, 'products.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(83, 'products.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(84, 'products.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(85, 'products.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(86, 'products.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(87, 'products.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(88, 'products.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(89, 'api.products.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(90, 'menu.attendance', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(91, 'attendance.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(92, 'attendance.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(93, 'attendance.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(94, 'attendance.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(95, 'attendance.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(96, 'attendance.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(97, 'attendance.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(98, 'attendance.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(99, 'attendance.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(100, 'api.attendance.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(101, 'api.attendance.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(102, 'menu.gps-logs', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(103, 'gps-logs.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(104, 'gps-logs.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(105, 'gps-logs.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(106, 'gps-logs.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(107, 'gps-logs.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(108, 'gps-logs.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(109, 'gps-logs.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(110, 'gps-logs.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(111, 'gps-logs.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(112, 'api.gps-logs.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(113, 'api.gps-logs.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(114, 'menu.visit-plans', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(115, 'visit-plans.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(116, 'visit-plans.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(117, 'visit-plans.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(118, 'visit-plans.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(119, 'visit-plans.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(120, 'visit-plans.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(121, 'visit-plans.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(122, 'visit-plans.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(123, 'visit-plans.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(124, 'api.visit-plans.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(125, 'api.visit-plans.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(126, 'menu.visits', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(127, 'visits.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(128, 'visits.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(129, 'visits.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(130, 'visits.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(131, 'visits.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(132, 'visits.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(133, 'visits.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(134, 'visits.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(135, 'visits.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(136, 'api.visits.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(137, 'api.visits.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(138, 'menu.orders', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(139, 'orders.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(140, 'orders.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(141, 'orders.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(142, 'orders.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(143, 'orders.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(144, 'orders.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(145, 'orders.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(146, 'orders.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(147, 'orders.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(148, 'api.orders.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(149, 'api.orders.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(150, 'menu.collection-entries', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(151, 'collection-entries.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(152, 'collection-entries.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(153, 'collection-entries.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(154, 'collection-entries.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(155, 'collection-entries.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(156, 'collection-entries.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(157, 'collection-entries.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(158, 'collection-entries.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(159, 'collection-entries.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(160, 'api.collection-entries.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(161, 'api.collection-entries.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(162, 'menu.targets', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(163, 'targets.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(164, 'targets.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(165, 'targets.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(166, 'targets.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(167, 'targets.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(168, 'targets.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(169, 'targets.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(170, 'targets.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(171, 'targets.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(172, 'api.targets.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(173, 'report.attendance', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(174, 'report.visits', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(175, 'report.order-performance', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(176, 'report.collections', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(177, 'report.territories', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(178, 'report.target-achievement', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(179, 'report.executive-performance', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(180, 'report.dealer-coverage', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(181, 'report.gps', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(182, 'menu.notifications', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(183, 'notifications.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(184, 'notifications.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(185, 'notifications.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(186, 'notifications.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(187, 'notifications.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(188, 'notifications.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(189, 'notifications.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(190, 'notifications.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(191, 'notifications.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(192, 'api.notifications.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(193, 'menu.announcements', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(194, 'announcements.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(195, 'announcements.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(196, 'announcements.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(197, 'announcements.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(198, 'announcements.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(199, 'announcements.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(200, 'announcements.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(201, 'announcements.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(202, 'announcements.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(203, 'menu.activity-log', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(204, 'activity-log.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(205, 'activity-log.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(206, 'activity-log.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(207, 'activity-log.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(208, 'activity-log.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(209, 'activity-log.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(210, 'activity-log.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(211, 'activity-log.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(212, 'activity-log.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(213, 'menu.territory-map', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(214, 'territory-map.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(215, 'territory-map.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(216, 'territory-map.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(217, 'territory-map.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(218, 'territory-map.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(219, 'territory-map.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(220, 'territory-map.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(221, 'territory-map.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(222, 'territory-map.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(223, 'menu.live-gps', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(224, 'live-gps.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(225, 'live-gps.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(226, 'live-gps.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(227, 'live-gps.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(228, 'live-gps.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(229, 'live-gps.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(230, 'live-gps.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(231, 'live-gps.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(232, 'live-gps.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(233, 'menu.settings', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(234, 'settings.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(235, 'settings.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(236, 'settings.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(237, 'settings.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(238, 'settings.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(239, 'settings.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(240, 'settings.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(241, 'settings.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(242, 'settings.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(243, 'menu.inquiries', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(244, 'inquiries.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(245, 'inquiries.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(246, 'inquiries.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(247, 'inquiries.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(248, 'inquiries.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(249, 'inquiries.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(250, 'inquiries.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(251, 'inquiries.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(252, 'inquiries.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(253, 'menu.visit-requests', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(254, 'visit-requests.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(255, 'visit-requests.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(256, 'visit-requests.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(257, 'visit-requests.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(258, 'visit-requests.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(259, 'visit-requests.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(260, 'visit-requests.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(261, 'visit-requests.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(262, 'visit-requests.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(263, 'menu.news', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(264, 'news.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(265, 'news.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(266, 'news.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(267, 'news.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(268, 'news.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(269, 'news.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(270, 'news.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(271, 'news.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(272, 'news.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(273, 'menu.promotions', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(274, 'promotions.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(275, 'promotions.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(276, 'promotions.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(277, 'promotions.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(278, 'promotions.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(279, 'promotions.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(280, 'promotions.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(281, 'promotions.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(282, 'promotions.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(283, 'menu.faqs', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(284, 'faqs.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(285, 'faqs.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(286, 'faqs.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(287, 'faqs.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(288, 'faqs.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(289, 'faqs.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(290, 'faqs.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(291, 'faqs.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(292, 'faqs.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(293, 'menu.service-centers', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(294, 'service-centers.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(295, 'service-centers.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(296, 'service-centers.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(297, 'service-centers.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(298, 'service-centers.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(299, 'service-centers.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(300, 'service-centers.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(301, 'service-centers.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(302, 'service-centers.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(303, 'menu.brochures', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(304, 'brochures.view', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(305, 'brochures.add', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(306, 'brochures.edit', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(307, 'brochures.delete', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(308, 'brochures.restore', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(309, 'brochures.export', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(310, 'brochures.print', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(311, 'brochures.import', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(312, 'brochures.approve', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(313, 'menu.divisions', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(314, 'divisions.view', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(315, 'divisions.add', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(316, 'divisions.edit', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(317, 'divisions.delete', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(318, 'divisions.restore', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(319, 'divisions.export', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(320, 'divisions.print', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(321, 'divisions.import', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(322, 'divisions.approve', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(323, 'api.divisions.view', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(324, 'menu.districts', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(325, 'districts.view', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(326, 'districts.add', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(327, 'districts.edit', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(328, 'districts.delete', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(329, 'districts.restore', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(330, 'districts.export', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(331, 'districts.print', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(332, 'districts.import', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(333, 'districts.approve', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(334, 'api.districts.view', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(335, 'menu.thanas', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(336, 'thanas.view', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(337, 'thanas.add', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(338, 'thanas.edit', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(339, 'thanas.delete', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(340, 'thanas.restore', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(341, 'thanas.export', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(342, 'thanas.print', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(343, 'thanas.import', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(344, 'thanas.approve', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(345, 'api.thanas.view', 'web', '2026-08-22 05:53:13', '2026-08-22 05:53:13'),
(346, 'report.dealer-ledger', 'web', '2026-08-25 03:36:14', '2026-08-25 03:36:14'),
(347, 'menu.holidays', 'web', '2026-08-25 04:49:12', '2026-08-25 04:49:12'),
(348, 'holidays.view', 'web', '2026-08-25 04:49:12', '2026-08-25 04:49:12'),
(349, 'holidays.add', 'web', '2026-08-25 04:49:12', '2026-08-25 04:49:12'),
(350, 'holidays.edit', 'web', '2026-08-25 04:49:12', '2026-08-25 04:49:12'),
(351, 'holidays.delete', 'web', '2026-08-25 04:49:12', '2026-08-25 04:49:12'),
(352, 'holidays.restore', 'web', '2026-08-25 04:49:12', '2026-08-25 04:49:12'),
(353, 'holidays.export', 'web', '2026-08-25 04:49:12', '2026-08-25 04:49:12'),
(354, 'holidays.print', 'web', '2026-08-25 04:49:12', '2026-08-25 04:49:12'),
(355, 'holidays.import', 'web', '2026-08-25 04:49:12', '2026-08-25 04:49:12'),
(356, 'holidays.approve', 'web', '2026-08-25 04:49:12', '2026-08-25 04:49:12'),
(357, 'menu.retailers', 'web', '2026-08-26 22:54:45', '2026-08-26 22:54:45'),
(358, 'retailers.view', 'web', '2026-08-26 22:54:45', '2026-08-26 22:54:45'),
(359, 'retailers.add', 'web', '2026-08-26 22:54:45', '2026-08-26 22:54:45'),
(360, 'retailers.edit', 'web', '2026-08-26 22:54:45', '2026-08-26 22:54:45'),
(361, 'retailers.delete', 'web', '2026-08-26 22:54:45', '2026-08-26 22:54:45'),
(362, 'retailers.restore', 'web', '2026-08-26 22:54:45', '2026-08-26 22:54:45'),
(363, 'retailers.export', 'web', '2026-08-26 22:54:45', '2026-08-26 22:54:45'),
(364, 'retailers.print', 'web', '2026-08-26 22:54:45', '2026-08-26 22:54:45'),
(365, 'retailers.import', 'web', '2026-08-26 22:54:45', '2026-08-26 22:54:45'),
(366, 'retailers.approve', 'web', '2026-08-26 22:54:45', '2026-08-26 22:54:45'),
(367, 'menu.cash-handovers', 'web', '2026-08-27 00:41:17', '2026-08-27 00:41:17'),
(368, 'cash-handovers.view', 'web', '2026-08-27 00:41:17', '2026-08-27 00:41:17'),
(369, 'cash-handovers.add', 'web', '2026-08-27 00:41:17', '2026-08-27 00:41:17'),
(370, 'cash-handovers.edit', 'web', '2026-08-27 00:41:17', '2026-08-27 00:41:17'),
(371, 'cash-handovers.delete', 'web', '2026-08-27 00:41:17', '2026-08-27 00:41:17'),
(372, 'cash-handovers.restore', 'web', '2026-08-27 00:41:17', '2026-08-27 00:41:17'),
(373, 'cash-handovers.export', 'web', '2026-08-27 00:41:17', '2026-08-27 00:41:17'),
(374, 'cash-handovers.print', 'web', '2026-08-27 00:41:17', '2026-08-27 00:41:17'),
(375, 'cash-handovers.import', 'web', '2026-08-27 00:41:17', '2026-08-27 00:41:17'),
(376, 'cash-handovers.approve', 'web', '2026-08-27 00:41:17', '2026-08-27 00:41:17'),
(377, 'api.retailers.view', 'web', '2026-08-27 02:20:45', '2026-08-27 02:20:45'),
(378, 'report.movement-summary', 'web', '2026-08-30 04:55:16', '2026-08-30 04:55:16'),
(379, 'menu.achievements', 'web', '2026-08-30 23:32:59', '2026-08-30 23:32:59'),
(380, 'achievements.view', 'web', '2026-08-30 23:32:59', '2026-08-30 23:32:59'),
(381, 'achievements.add', 'web', '2026-08-30 23:32:59', '2026-08-30 23:32:59'),
(382, 'achievements.edit', 'web', '2026-08-30 23:32:59', '2026-08-30 23:32:59'),
(383, 'achievements.delete', 'web', '2026-08-30 23:32:59', '2026-08-30 23:32:59'),
(384, 'achievements.restore', 'web', '2026-08-30 23:32:59', '2026-08-30 23:32:59'),
(385, 'achievements.export', 'web', '2026-08-30 23:32:59', '2026-08-30 23:32:59'),
(386, 'achievements.print', 'web', '2026-08-30 23:32:59', '2026-08-30 23:32:59'),
(387, 'achievements.import', 'web', '2026-08-30 23:32:59', '2026-08-30 23:32:59'),
(388, 'achievements.approve', 'web', '2026-08-30 23:32:59', '2026-08-30 23:32:59'),
(389, 'api.achievements.view', 'web', '2026-08-30 23:32:59', '2026-08-30 23:32:59'),
(390, 'api.achievements.add', 'web', '2026-08-30 23:32:59', '2026-08-30 23:32:59'),
(391, 'report.achievement-summary', 'web', '2026-08-30 23:32:59', '2026-08-30 23:32:59'),
(392, 'menu.tally-integration', 'web', '2026-09-07 05:16:28', '2026-09-07 05:16:28'),
(393, 'tally-integration.view', 'web', '2026-09-07 05:16:28', '2026-09-07 05:16:28'),
(394, 'tally-integration.configure', 'web', '2026-09-07 05:16:28', '2026-09-07 05:16:28'),
(395, 'tally-integration.sync', 'web', '2026-09-07 05:16:28', '2026-09-07 05:16:28'),
(396, 'tally-integration.retry', 'web', '2026-09-07 05:16:28', '2026-09-07 05:16:28'),
(397, 'tally-integration.reconcile', 'web', '2026-09-07 05:16:28', '2026-09-07 05:16:28'),
(398, 'menu.depots', 'web', '2026-09-07 06:41:16', '2026-09-07 06:41:16'),
(399, 'depots.view', 'web', '2026-09-07 06:41:16', '2026-09-07 06:41:16'),
(400, 'depots.add', 'web', '2026-09-07 06:41:16', '2026-09-07 06:41:16'),
(401, 'depots.edit', 'web', '2026-09-07 06:41:16', '2026-09-07 06:41:16'),
(402, 'depots.delete', 'web', '2026-09-07 06:41:16', '2026-09-07 06:41:16'),
(403, 'depots.restore', 'web', '2026-09-07 06:41:16', '2026-09-07 06:41:16'),
(404, 'depots.export', 'web', '2026-09-07 06:41:16', '2026-09-07 06:41:16'),
(405, 'depots.print', 'web', '2026-09-07 06:41:16', '2026-09-07 06:41:16'),
(406, 'depots.import', 'web', '2026-09-07 06:41:16', '2026-09-07 06:41:16'),
(407, 'depots.approve', 'web', '2026-09-07 06:41:16', '2026-09-07 06:41:16'),
(408, 'api.depots.view', 'web', '2026-09-07 06:41:16', '2026-09-07 06:41:16'),
(409, 'menu.vehicles', 'web', '2026-09-07 07:16:39', '2026-09-07 07:16:39'),
(410, 'vehicles.view', 'web', '2026-09-07 07:16:39', '2026-09-07 07:16:39'),
(411, 'vehicles.add', 'web', '2026-09-07 07:16:39', '2026-09-07 07:16:39'),
(412, 'vehicles.edit', 'web', '2026-09-07 07:16:39', '2026-09-07 07:16:39'),
(413, 'vehicles.delete', 'web', '2026-09-07 07:16:39', '2026-09-07 07:16:39'),
(414, 'vehicles.restore', 'web', '2026-09-07 07:16:39', '2026-09-07 07:16:39'),
(415, 'vehicles.export', 'web', '2026-09-07 07:16:39', '2026-09-07 07:16:39'),
(416, 'vehicles.print', 'web', '2026-09-07 07:16:39', '2026-09-07 07:16:39'),
(417, 'vehicles.import', 'web', '2026-09-07 07:16:39', '2026-09-07 07:16:39'),
(418, 'vehicles.approve', 'web', '2026-09-07 07:16:39', '2026-09-07 07:16:39'),
(419, 'api.vehicles.view', 'web', '2026-09-07 07:16:39', '2026-09-07 07:16:39'),
(420, 'menu.drivers', 'web', '2026-09-07 07:16:39', '2026-09-07 07:16:39'),
(421, 'drivers.view', 'web', '2026-09-07 07:16:39', '2026-09-07 07:16:39'),
(422, 'drivers.add', 'web', '2026-09-07 07:16:39', '2026-09-07 07:16:39'),
(423, 'drivers.edit', 'web', '2026-09-07 07:16:39', '2026-09-07 07:16:39'),
(424, 'drivers.delete', 'web', '2026-09-07 07:16:39', '2026-09-07 07:16:39'),
(425, 'drivers.restore', 'web', '2026-09-07 07:16:39', '2026-09-07 07:16:39'),
(426, 'drivers.export', 'web', '2026-09-07 07:16:39', '2026-09-07 07:16:39'),
(427, 'drivers.print', 'web', '2026-09-07 07:16:39', '2026-09-07 07:16:39'),
(428, 'drivers.import', 'web', '2026-09-07 07:16:39', '2026-09-07 07:16:39'),
(429, 'drivers.approve', 'web', '2026-09-07 07:16:39', '2026-09-07 07:16:39'),
(430, 'api.drivers.view', 'web', '2026-09-07 07:16:39', '2026-09-07 07:16:39'),
(431, 'menu.deliveries', 'web', '2026-09-07 07:20:34', '2026-09-07 07:20:34'),
(432, 'deliveries.view', 'web', '2026-09-07 07:20:34', '2026-09-07 07:20:34'),
(433, 'deliveries.add', 'web', '2026-09-07 07:20:34', '2026-09-07 07:20:34'),
(434, 'deliveries.edit', 'web', '2026-09-07 07:20:34', '2026-09-07 07:20:34'),
(435, 'deliveries.delete', 'web', '2026-09-07 07:20:34', '2026-09-07 07:20:34'),
(436, 'deliveries.restore', 'web', '2026-09-07 07:20:34', '2026-09-07 07:20:34'),
(437, 'deliveries.export', 'web', '2026-09-07 07:20:34', '2026-09-07 07:20:34'),
(438, 'deliveries.print', 'web', '2026-09-07 07:20:34', '2026-09-07 07:20:34'),
(439, 'deliveries.import', 'web', '2026-09-07 07:20:34', '2026-09-07 07:20:34'),
(440, 'deliveries.approve', 'web', '2026-09-07 07:20:34', '2026-09-07 07:20:34'),
(441, 'menu.sales-returns', 'web', '2026-09-07 08:01:13', '2026-09-07 08:01:13'),
(442, 'sales-returns.view', 'web', '2026-09-07 08:01:13', '2026-09-07 08:01:13'),
(443, 'sales-returns.add', 'web', '2026-09-07 08:01:13', '2026-09-07 08:01:13'),
(444, 'sales-returns.edit', 'web', '2026-09-07 08:01:13', '2026-09-07 08:01:13'),
(445, 'sales-returns.delete', 'web', '2026-09-07 08:01:13', '2026-09-07 08:01:13'),
(446, 'sales-returns.restore', 'web', '2026-09-07 08:01:13', '2026-09-07 08:01:13'),
(447, 'sales-returns.export', 'web', '2026-09-07 08:01:13', '2026-09-07 08:01:13'),
(448, 'sales-returns.print', 'web', '2026-09-07 08:01:13', '2026-09-07 08:01:13'),
(449, 'sales-returns.import', 'web', '2026-09-07 08:01:13', '2026-09-07 08:01:13'),
(450, 'sales-returns.approve', 'web', '2026-09-07 08:01:13', '2026-09-07 08:01:13'),
(451, 'api.sales-returns.view', 'web', '2026-09-07 08:01:13', '2026-09-07 08:01:13'),
(452, 'api.sales-returns.add', 'web', '2026-09-07 08:01:13', '2026-09-07 08:01:13'),
(453, 'api.retailers.add', 'web', '2026-09-07 08:07:19', '2026-09-07 08:07:19'),
(454, 'report.sales-return-summary', 'web', '2026-09-07 08:17:52', '2026-09-07 08:17:52'),
(455, 'menu.leave-types', 'web', '2026-09-09 04:06:21', '2026-09-09 04:06:21'),
(456, 'leave-types.view', 'web', '2026-09-09 04:06:21', '2026-09-09 04:06:21'),
(457, 'leave-types.add', 'web', '2026-09-09 04:06:21', '2026-09-09 04:06:21'),
(458, 'leave-types.edit', 'web', '2026-09-09 04:06:21', '2026-09-09 04:06:21'),
(459, 'leave-types.delete', 'web', '2026-09-09 04:06:21', '2026-09-09 04:06:21'),
(460, 'leave-types.restore', 'web', '2026-09-09 04:06:21', '2026-09-09 04:06:21'),
(461, 'leave-types.export', 'web', '2026-09-09 04:06:21', '2026-09-09 04:06:21'),
(462, 'leave-types.print', 'web', '2026-09-09 04:06:21', '2026-09-09 04:06:21'),
(463, 'leave-types.import', 'web', '2026-09-09 04:06:21', '2026-09-09 04:06:21'),
(464, 'leave-types.approve', 'web', '2026-09-09 04:06:21', '2026-09-09 04:06:21'),
(465, 'menu.leave-requests', 'web', '2026-09-09 04:06:21', '2026-09-09 04:06:21'),
(466, 'leave-requests.view', 'web', '2026-09-09 04:06:21', '2026-09-09 04:06:21'),
(467, 'leave-requests.add', 'web', '2026-09-09 04:06:21', '2026-09-09 04:06:21'),
(468, 'leave-requests.edit', 'web', '2026-09-09 04:06:21', '2026-09-09 04:06:21'),
(469, 'leave-requests.delete', 'web', '2026-09-09 04:06:21', '2026-09-09 04:06:21'),
(470, 'leave-requests.restore', 'web', '2026-09-09 04:06:21', '2026-09-09 04:06:21'),
(471, 'leave-requests.export', 'web', '2026-09-09 04:06:21', '2026-09-09 04:06:21'),
(472, 'leave-requests.print', 'web', '2026-09-09 04:06:21', '2026-09-09 04:06:21'),
(473, 'leave-requests.import', 'web', '2026-09-09 04:06:21', '2026-09-09 04:06:21'),
(474, 'leave-requests.approve', 'web', '2026-09-09 04:06:21', '2026-09-09 04:06:21'),
(475, 'api.leave-requests.view', 'web', '2026-09-09 04:06:21', '2026-09-09 04:06:21'),
(476, 'api.leave-requests.add', 'web', '2026-09-09 04:06:43', '2026-09-09 04:06:43'),
(477, 'menu.leave-balances', 'web', '2026-09-09 06:14:45', '2026-09-09 06:14:45'),
(478, 'leave-balances.view', 'web', '2026-09-09 06:14:45', '2026-09-09 06:14:45'),
(479, 'leave-balances.add', 'web', '2026-09-09 06:14:45', '2026-09-09 06:14:45'),
(480, 'leave-balances.edit', 'web', '2026-09-09 06:14:45', '2026-09-09 06:14:45'),
(481, 'leave-balances.delete', 'web', '2026-09-09 06:14:45', '2026-09-09 06:14:45'),
(482, 'leave-balances.restore', 'web', '2026-09-09 06:14:45', '2026-09-09 06:14:45'),
(483, 'leave-balances.export', 'web', '2026-09-09 06:14:45', '2026-09-09 06:14:45'),
(484, 'leave-balances.print', 'web', '2026-09-09 06:14:45', '2026-09-09 06:14:45'),
(485, 'leave-balances.import', 'web', '2026-09-09 06:14:45', '2026-09-09 06:14:45'),
(486, 'leave-balances.approve', 'web', '2026-09-09 06:14:45', '2026-09-09 06:14:45');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `sales_team_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(255) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `tally_guid` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `sales_team_id`, `name`, `sku`, `price`, `description`, `image`, `status`, `tally_guid`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Gazi Self-Priming Jet Pump', 'SKU-00001', 6500.00, 'Gazi self-priming jet pump for household and light commercial water supply.', 'products/gazi-self-priming-jet-pump.jpg', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000e1', NULL, 1, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:57:51'),
(2, 1, 1, 'Gazi Standardized Centrifugal Pump', 'SKU-00002', 8200.00, 'Gazi standardized centrifugal pump for general-purpose water transfer.', 'products/gazi-standardized-centrifugal-pump.jpg', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000e2', NULL, 1, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:57:52'),
(3, 1, NULL, 'Pentax Centrifugal Pump', 'SKU-00003', 7800.00, 'Pentax (Pentex) centrifugal pump, distributed by Gazi Pumps & Motors.', 'products/pentax-centrifugal-pump.jpg', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000e3', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:57:52'),
(4, 1, NULL, 'Pentax Submersible Pump', 'SKU-00004', 9500.00, 'Pentax (Pentex) submersible pump for deep-well water extraction.', 'products/pentax-submersible-pump.jpg', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000e4', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:57:53'),
(5, 1, NULL, 'Eifel EA Series Pump', 'SKU-00005', 8800.00, 'Eifel EA Series pump, distributed by Gazi Pumps & Motors.', 'products/eifel-ea-series-pump.jpg', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000e5', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:57:53'),
(6, 1, NULL, 'Eifel EAD Series Pump', 'SKU-00006', 9200.00, 'Eifel EAD Series pump, distributed by Gazi Pumps & Motors.', 'products/eifel-ead-series-pump.jpg', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000e6', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:57:54'),
(7, 1, NULL, 'CNP CDLF Series Pump', 'SKU-00007', 15500.00, 'CNP CDLF Series vertical multistage pump, distributed by Gazi Pumps & Motors.', 'products/cnp-cdlf-series-pump.jpg', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000e7', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:57:55'),
(8, 1, NULL, 'CNP SZ Series Pump', 'SKU-00008', 14200.00, 'CNP SZ Series pump, distributed by Gazi Pumps & Motors.', 'products/cnp-sz-series-pump.jpg', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000e8', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:57:55'),
(9, 2, NULL, 'Gazi Fire Fighting Pump Complete Set', 'SKU-00009', 185000.00, 'Gazi fire fighting pump complete set for industrial fire-safety installations.', 'products/gazi-fire-fighting-pump-set.jpg', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000e9', NULL, 1, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:57:56'),
(10, 2, NULL, 'Gazi Motors YC Series', 'SKU-00010', 8400.00, 'Gazi Motors YC Series electric motor for industrial and pump applications.', 'products/gazi-motors-yc-series.jpg', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000ea', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:57:56'),
(11, 2, NULL, 'Gazi Gas Stove (Industrial Line)', 'SKU-00011', 3200.00, 'Gazi gas stove from the Gazi Pumps & Motors industrial product line.', 'products/gazi-gas-stove-industrial.jpg', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000eb', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:57:56'),
(12, 2, NULL, 'Gazi Tubewell', 'SKU-00012', 4500.00, 'Gazi tubewell equipment for groundwater extraction.', 'products/gazi-tubewell.jpg', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000ec', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:57:57'),
(13, 3, NULL, 'TG-206 - Gazi Smiss Gas Stove', 'SKU-00013', 8466.00, 'Gazi Smiss TG-206 double burner gas stove.', 'products/gazi-smiss-gas-stove-tg-206.png', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000ed', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:57:58'),
(14, 3, NULL, 'GST-102C - Gazi Gas Stove', 'SKU-00014', 1864.00, 'Gazi GST-102C single burner gas stove.', 'products/gazi-gas-stove-gst-102c.png', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000ee', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:57:58'),
(15, 3, NULL, 'EG-732S - Gazi Smiss Gas Stove', 'SKU-00015', 14448.00, 'Gazi Smiss EG-732S glass-top gas stove.', 'products/gazi-smiss-gas-stove-eg-732s.webp', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000ef', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:57:58'),
(16, 3, NULL, 'TG-213S - Gazi Smiss Gas Stove', 'SKU-00016', 11220.00, 'Gazi Smiss TG-213S double burner gas stove.', 'products/gazi-smiss-gas-stove-tg-213s.png', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000f0', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:57:59'),
(17, 3, NULL, 'GH-8204M - Gazi Smiss Gas Stove', 'SKU-00017', 16128.00, 'Gazi Smiss GH-8204M gas stove.', 'products/gazi-smiss-gas-stove-gh-8204m.webp', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000f1', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:58:00'),
(18, 4, NULL, 'HY-716BV - Gazi Smiss Kitchen Hood', 'SKU-00018', 22176.00, 'Gazi Smiss HY-716BV kitchen hood.', 'products/gazi-smiss-kitchen-hood-hy-716bv.png', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000f2', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:58:00'),
(19, 4, NULL, 'HY-712BT - Gazi Smiss Kitchen Hood', 'SKU-00019', 14616.00, 'Gazi Smiss HY-712BT kitchen hood.', 'products/gazi-smiss-kitchen-hood-hy-712bt.webp', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000f3', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:58:01'),
(20, 4, NULL, 'EG-750S - Gazi Smiss Kitchen Hood', 'SKU-00020', 8856.00, 'Gazi Smiss EG-750S kitchen hood.', 'products/gazi-smiss-kitchen-hood-eg-750s.png', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000f4', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:58:01'),
(21, 4, NULL, 'HY-736BV - Gazi Smiss Kitchen Hood', 'SKU-00021', 23184.00, 'Gazi Smiss HY-736BV kitchen hood.', 'products/gazi-smiss-kitchen-hood-hy-736bv.webp', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000f5', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:58:01'),
(22, 4, NULL, 'HY-729CP - Gazi Smiss Kitchen Hood', 'SKU-00022', 16320.00, 'Gazi Smiss HY-729CP kitchen hood.', 'products/gazi-smiss-kitchen-hood-hy-729cp.webp', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000f6', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:58:02'),
(23, 5, NULL, 'IF-HL01 - Gazi Smiss Infrared Cooker', 'SKU-00023', 5952.00, 'Gazi Smiss IF-HL01 infrared cooker.', 'products/gazi-smiss-infrared-cooker-if-hl01.webp', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000f7', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:58:02'),
(24, 5, NULL, 'A-40G - Gazi Smiss Infrared Cooker', 'SKU-00024', 4080.00, 'Gazi Smiss A-40G infrared cooker.', 'products/gazi-smiss-infrared-cooker-a-40g.webp', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000f8', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:58:03'),
(25, 5, NULL, 'A-25S - Gazi Smiss Induction Cooker', 'SKU-00025', 4080.00, 'Gazi Smiss A-25S induction cooker.', 'products/gazi-smiss-induction-cooker-a-25s.webp', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000f9', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:58:03'),
(26, 5, NULL, 'A-37G - Gazi Smiss Infrared Cooker', 'SKU-00026', 4080.00, 'Gazi Smiss A-37G infrared cooker.', 'products/gazi-smiss-infrared-cooker-a-37g.png', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000fa', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:58:04'),
(27, 5, NULL, 'E-720B - Gazi Smiss Induction & Infrared Cooker', 'SKU-00027', 12960.00, 'Gazi Smiss E-720B combined induction and infrared cooker.', 'products/gazi-smiss-induction-infrared-cooker-e-720b.png', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000fb', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:58:04'),
(28, 5, NULL, 'A-01 - Gazi Smiss Infrared Cooker', 'SKU-00028', 3672.00, 'Gazi Smiss A-01 infrared cooker.', 'products/gazi-smiss-infrared-cooker-a-01.png', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000fc', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:58:05'),
(29, 6, NULL, 'GEO-03 - Gazi Smiss Electric Oven 30 Liter', 'SKU-00029', 15600.00, 'Gazi Smiss GEO-03 electric oven, 30 liter capacity.', 'products/gazi-smiss-electric-oven-30l-geo-03.png', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000fd', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:58:05'),
(30, 6, NULL, 'GEO-04 - Gazi Smiss Electric Oven 40 Liter', 'SKU-00030', 16800.00, 'Gazi Smiss GEO-04 electric oven, 40 liter capacity.', 'products/gazi-smiss-electric-oven-40l-geo-04.png', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000fe', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:58:06'),
(31, 6, NULL, 'GEO-05 - Gazi Smiss Electric Oven 50 Liter', 'SKU-00031', 19200.00, 'Gazi Smiss GEO-05 electric oven, 50 liter capacity.', 'products/gazi-smiss-electric-oven-50l-geo-05.png', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000ff', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:58:06'),
(32, 7, NULL, 'GA-AF-23 - Gazi Smiss Air Fryer', 'SKU-00032', 11088.00, 'Gazi Smiss GA-AF-23 air fryer.', 'products/gazi-smiss-air-fryer-ga-af-23.png', 1, 'd530416d-350a-4df9-bc3c-06943927816a-00000100', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:58:07'),
(33, 7, NULL, 'GA-AF-25 - Gazi Smiss Air Fryer', 'SKU-00033', 10836.00, 'Gazi Smiss GA-AF-25 air fryer.', 'products/gazi-smiss-air-fryer-ga-af-25.png', 1, 'd530416d-350a-4df9-bc3c-06943927816a-00000101', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:58:07'),
(34, 7, NULL, 'GA-AF-27 - Gazi Smiss Air Fryer', 'SKU-00034', 8976.00, 'Gazi Smiss GA-AF-27 air fryer.', 'products/gazi-smiss-air-fryer-ga-af-27.png', 1, 'd530416d-350a-4df9-bc3c-06943927816a-00000102', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:58:08'),
(35, 8, NULL, '15.0 HP Y2 Motor 950 RPM', 'SKU-00035', 87675.00, 'Gazi Smiss Y2 series electric motor, 15.0 HP, 950 RPM.', 'products/gazi-smiss-motor-15hp-y2.png', 1, 'd530416d-350a-4df9-bc3c-06943927816a-00000103', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:58:08'),
(36, 8, NULL, '10.0 HP Y2 Motor 2800 RPM', 'SKU-00036', 34650.00, 'Gazi Smiss Y2 series electric motor, 10.0 HP, 2800 RPM.', 'products/gazi-smiss-motor-10hp-y2.png', 1, 'd530416d-350a-4df9-bc3c-06943927816a-00000104', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:58:09'),
(37, 8, NULL, '5.5 HP Y2 Motor 2800 RPM', 'SKU-00037', 24700.00, 'Gazi Smiss Y2 series electric motor, 5.5 HP, 2800 RPM.', 'products/gazi-smiss-motor-5-5hp-y2.png', 1, 'd530416d-350a-4df9-bc3c-06943927816a-00000105', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:58:09'),
(38, 8, NULL, '3 HP YC Motor 1450 RPM', 'SKU-00038', 21000.00, 'Gazi Smiss YC series electric motor, 3 HP, 1450 RPM.', 'products/gazi-smiss-motor-3hp-yc.png', 1, 'd530416d-350a-4df9-bc3c-06943927816a-00000106', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:58:10'),
(39, 8, NULL, '1.0 HP Y2 Motor 2800 RPM', 'SKU-00039', 9450.00, 'Gazi Smiss Y2 series electric motor, 1.0 HP, 2800 RPM.', 'products/gazi-smiss-motor-1hp-y2.png', 1, 'd530416d-350a-4df9-bc3c-06943927816a-00000107', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:58:10'),
(40, 8, NULL, '0.75 HP YC Motor 1450 RPM (Classic)', 'SKU-00040', 8400.00, 'Gazi Smiss YC series electric motor (Classic), 0.75 HP, 1450 RPM.', 'products/gazi-smiss-motor-0-75hp-yc-classic.png', 1, 'd530416d-350a-4df9-bc3c-06943927816a-00000108', NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-09-08 04:58:11'),
(44, 1, NULL, 'Pump Model 1', 'TLY-P-725592', 0.00, NULL, NULL, 0, 'd530416d-350a-4df9-bc3c-06943927816a-000000d2', NULL, NULL, NULL, NULL, '2026-09-08 03:53:14', '2026-09-08 03:53:14'),
(45, 1, NULL, 'TV Model 1', 'TLY-P-206227', 0.00, NULL, NULL, 0, 'd530416d-350a-4df9-bc3c-06943927816a-000000d3', NULL, NULL, NULL, NULL, '2026-09-08 03:53:14', '2026-09-08 03:53:14');

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`id`, `name`, `code`, `parent_id`, `description`, `status`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Industrial Pumps', 'CAT-001', NULL, 'Self-priming, centrifugal, and submersible pumps from the Gazi, Pentax, Eifel, and CNP lines (gazipumps.com).', 1, NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(2, 'Industrial Motors & Equipment', 'CAT-002', NULL, 'Fire-fighting pump sets, motors, gas stoves, and tubewells from the Gazi Pumps & Motors industrial line (gazipumps.com).', 1, NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(3, 'Gazi Smiss Gas Stoves', 'CAT-003', NULL, 'Gazi Smiss and Gazi branded gas stoves from the gcart.com.bd storefront.', 1, NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(4, 'Gazi Smiss Kitchen Hoods', 'CAT-004', NULL, 'Gazi Smiss kitchen hood/chimney models from the gcart.com.bd storefront.', 1, NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(5, 'Gazi Smiss Induction & Infrared Cookers', 'CAT-005', NULL, 'Gazi Smiss induction and infrared cooktops from the gcart.com.bd storefront.', 1, NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(6, 'Gazi Smiss Electric Ovens', 'CAT-006', NULL, 'Gazi Smiss electric oven models from the gcart.com.bd storefront.', 1, NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(7, 'Gazi Smiss Air Fryers', 'CAT-007', NULL, 'Gazi Smiss air fryer models from the gcart.com.bd storefront.', 1, NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30'),
(8, 'Gazi Smiss Motors', 'CAT-008', NULL, 'Gazi Smiss Y2/YC series electric motors from the gcart.com.bd storefront.', 1, NULL, NULL, NULL, NULL, '2026-08-23 04:54:30', '2026-08-23 04:54:30');

-- --------------------------------------------------------

--
-- Table structure for table `product_stocks`
--

CREATE TABLE `product_stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `depot_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `opening_qty` decimal(12,2) NOT NULL DEFAULT 0.00,
  `in_qty` decimal(12,2) NOT NULL DEFAULT 0.00,
  `out_qty` decimal(12,2) NOT NULL DEFAULT 0.00,
  `closing_qty` decimal(12,2) NOT NULL DEFAULT 0.00,
  `available_qty` decimal(12,2) NOT NULL DEFAULT 0.00,
  `reserved_qty` decimal(12,2) NOT NULL DEFAULT 0.00,
  `allocated_qty` decimal(12,2) NOT NULL DEFAULT 0.00,
  `last_synced_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_stocks`
--

INSERT INTO `product_stocks` (`id`, `depot_id`, `product_id`, `opening_qty`, `in_qty`, `out_qty`, `closing_qty`, `available_qty`, `reserved_qty`, `allocated_qty`, `last_synced_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 0.00, 0.00, 0.00, 45.00, 45.00, 0.00, 0.00, '2026-09-07 11:24:23', '2026-09-07 11:24:04', '2026-09-07 11:25:51'),
(2, 1, 44, 0.00, 0.00, 0.00, 45.00, 45.00, 0.00, 0.00, '2026-09-21 11:27:08', '2026-09-08 05:58:12', '2026-09-21 11:27:08');

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `starts_at` date DEFAULT NULL,
  `ends_at` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `retailers`
--

CREATE TABLE `retailers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dealer_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `shipping_address` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `tally_guid` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `retailers`
--

INSERT INTO `retailers` (`id`, `dealer_id`, `name`, `phone`, `email`, `image`, `shipping_address`, `status`, `tally_guid`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(2, 1, 'Postman Test Retailer', '01711111111', NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, '2026-09-08 04:57:21', '2026-08-27 02:22:48', '2026-09-08 04:57:21');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'web', '2026-08-06 04:39:49', '2026-08-06 04:39:49'),
(2, 'General Manager', 'web', '2026-08-06 04:39:49', '2026-08-06 04:39:49'),
(3, 'Sales Manager', 'web', '2026-08-06 04:39:49', '2026-08-06 04:39:49'),
(4, 'Area Manager', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(5, 'Territory Manager', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50'),
(6, 'Sales Executive', 'web', '2026-08-06 04:39:50', '2026-08-06 04:39:50');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(3, 5),
(4, 1),
(4, 2),
(5, 1),
(5, 2),
(6, 1),
(7, 1),
(8, 1),
(8, 2),
(9, 1),
(9, 2),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(13, 2),
(14, 1),
(14, 2),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(24, 2),
(25, 1),
(25, 2),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(34, 2),
(34, 3),
(34, 4),
(34, 5),
(35, 1),
(35, 2),
(35, 3),
(35, 4),
(35, 5),
(36, 1),
(36, 2),
(37, 1),
(37, 2),
(38, 1),
(39, 1),
(40, 1),
(40, 2),
(41, 1),
(41, 2),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(45, 2),
(45, 3),
(45, 4),
(45, 5),
(46, 1),
(46, 2),
(46, 3),
(46, 4),
(46, 5),
(47, 1),
(47, 2),
(48, 1),
(48, 2),
(49, 1),
(50, 1),
(51, 1),
(51, 2),
(52, 1),
(52, 2),
(53, 1),
(54, 1),
(55, 1),
(56, 1),
(56, 2),
(56, 3),
(56, 4),
(56, 5),
(56, 6),
(57, 1),
(57, 2),
(57, 3),
(57, 4),
(57, 5),
(57, 6),
(58, 1),
(58, 2),
(58, 3),
(58, 4),
(58, 5),
(58, 6),
(59, 1),
(59, 2),
(59, 3),
(59, 4),
(59, 5),
(60, 1),
(61, 1),
(62, 1),
(62, 2),
(63, 1),
(63, 2),
(64, 1),
(65, 1),
(66, 1),
(66, 6),
(67, 1),
(67, 6),
(68, 1),
(68, 2),
(68, 3),
(68, 4),
(68, 5),
(68, 6),
(69, 1),
(69, 2),
(69, 3),
(69, 4),
(69, 5),
(69, 6),
(70, 1),
(70, 2),
(71, 1),
(71, 2),
(72, 1),
(73, 1),
(74, 1),
(74, 2),
(75, 1),
(75, 2),
(76, 1),
(77, 1),
(78, 1),
(78, 6),
(79, 1),
(79, 2),
(79, 3),
(79, 4),
(79, 5),
(79, 6),
(80, 1),
(80, 2),
(80, 3),
(80, 4),
(80, 5),
(80, 6),
(81, 1),
(81, 2),
(82, 1),
(82, 2),
(83, 1),
(84, 1),
(85, 1),
(85, 2),
(86, 1),
(86, 2),
(87, 1),
(88, 1),
(89, 1),
(89, 6),
(90, 1),
(90, 2),
(90, 3),
(90, 4),
(90, 5),
(91, 1),
(91, 2),
(91, 3),
(91, 4),
(91, 5),
(92, 1),
(92, 2),
(93, 1),
(93, 2),
(94, 1),
(95, 1),
(96, 1),
(96, 2),
(96, 3),
(96, 4),
(96, 5),
(97, 1),
(97, 2),
(97, 3),
(97, 4),
(97, 5),
(98, 1),
(99, 1),
(100, 1),
(100, 6),
(101, 1),
(101, 6),
(102, 1),
(102, 2),
(102, 3),
(102, 4),
(102, 5),
(103, 1),
(103, 2),
(103, 3),
(103, 4),
(103, 5),
(104, 1),
(105, 1),
(106, 1),
(107, 1),
(108, 1),
(108, 2),
(108, 3),
(108, 4),
(108, 5),
(109, 1),
(109, 2),
(109, 3),
(109, 4),
(109, 5),
(110, 1),
(111, 1),
(112, 1),
(112, 6),
(113, 1),
(113, 6),
(114, 1),
(114, 2),
(114, 3),
(114, 4),
(114, 5),
(115, 1),
(115, 2),
(115, 3),
(115, 4),
(115, 5),
(116, 1),
(116, 2),
(116, 3),
(116, 4),
(116, 5),
(117, 1),
(117, 2),
(117, 3),
(117, 4),
(117, 5),
(118, 1),
(119, 1),
(120, 1),
(120, 2),
(120, 3),
(120, 4),
(120, 5),
(121, 1),
(121, 2),
(121, 3),
(121, 4),
(121, 5),
(122, 1),
(123, 1),
(124, 1),
(124, 6),
(125, 1),
(125, 6),
(126, 1),
(126, 2),
(126, 3),
(126, 4),
(126, 5),
(127, 1),
(127, 2),
(127, 3),
(127, 4),
(127, 5),
(128, 1),
(128, 2),
(129, 1),
(129, 2),
(130, 1),
(131, 1),
(132, 1),
(132, 2),
(132, 3),
(132, 4),
(132, 5),
(133, 1),
(133, 2),
(133, 3),
(133, 4),
(133, 5),
(134, 1),
(135, 1),
(136, 1),
(136, 6),
(137, 1),
(137, 6),
(138, 1),
(138, 2),
(138, 3),
(138, 4),
(138, 5),
(138, 6),
(139, 1),
(139, 2),
(139, 3),
(139, 4),
(139, 5),
(139, 6),
(140, 1),
(140, 2),
(140, 6),
(141, 1),
(141, 2),
(142, 1),
(143, 1),
(144, 1),
(144, 2),
(144, 3),
(144, 4),
(144, 5),
(144, 6),
(145, 1),
(145, 2),
(145, 3),
(145, 4),
(145, 5),
(145, 6),
(146, 1),
(147, 1),
(147, 2),
(148, 1),
(148, 6),
(149, 1),
(149, 6),
(150, 1),
(150, 2),
(150, 3),
(150, 4),
(150, 5),
(150, 6),
(151, 1),
(151, 2),
(151, 3),
(151, 4),
(151, 5),
(151, 6),
(152, 1),
(152, 2),
(152, 6),
(153, 1),
(153, 2),
(154, 1),
(155, 1),
(156, 1),
(156, 2),
(156, 3),
(156, 4),
(156, 5),
(156, 6),
(157, 1),
(157, 2),
(157, 3),
(157, 4),
(157, 5),
(157, 6),
(158, 1),
(159, 1),
(159, 2),
(160, 1),
(160, 6),
(161, 1),
(161, 6),
(162, 1),
(162, 2),
(162, 3),
(162, 4),
(162, 5),
(162, 6),
(163, 1),
(163, 2),
(163, 3),
(163, 4),
(163, 5),
(163, 6),
(164, 1),
(164, 2),
(164, 3),
(164, 4),
(164, 5),
(165, 1),
(165, 2),
(165, 3),
(165, 4),
(165, 5),
(166, 1),
(167, 1),
(168, 1),
(168, 2),
(168, 3),
(168, 4),
(168, 5),
(168, 6),
(169, 1),
(169, 2),
(169, 3),
(169, 4),
(169, 5),
(169, 6),
(170, 1),
(171, 1),
(172, 1),
(172, 6),
(173, 1),
(173, 2),
(173, 3),
(173, 4),
(173, 5),
(173, 6),
(174, 1),
(174, 2),
(174, 3),
(174, 4),
(174, 5),
(174, 6),
(175, 1),
(175, 2),
(175, 3),
(175, 4),
(175, 5),
(175, 6),
(176, 1),
(176, 2),
(176, 3),
(176, 4),
(176, 5),
(176, 6),
(177, 1),
(177, 2),
(177, 3),
(177, 4),
(177, 5),
(178, 1),
(178, 2),
(178, 3),
(178, 4),
(178, 5),
(178, 6),
(179, 1),
(179, 2),
(179, 3),
(179, 4),
(179, 5),
(180, 1),
(180, 2),
(180, 3),
(180, 4),
(180, 5),
(181, 1),
(181, 2),
(181, 3),
(181, 4),
(181, 5),
(181, 6),
(182, 1),
(182, 2),
(182, 3),
(182, 4),
(182, 5),
(182, 6),
(183, 1),
(183, 2),
(183, 3),
(183, 4),
(183, 5),
(183, 6),
(184, 1),
(185, 1),
(186, 1),
(187, 1),
(188, 1),
(189, 1),
(190, 1),
(191, 1),
(192, 1),
(192, 6),
(193, 1),
(193, 2),
(194, 1),
(194, 2),
(195, 1),
(195, 2),
(196, 1),
(197, 1),
(197, 2),
(198, 1),
(198, 2),
(199, 1),
(200, 1),
(201, 1),
(202, 1),
(203, 1),
(203, 2),
(204, 1),
(204, 2),
(205, 1),
(206, 1),
(207, 1),
(208, 1),
(209, 1),
(209, 2),
(210, 1),
(210, 2),
(211, 1),
(212, 1),
(213, 1),
(213, 2),
(213, 3),
(213, 4),
(213, 5),
(214, 1),
(214, 2),
(214, 3),
(214, 4),
(214, 5),
(215, 1),
(216, 1),
(217, 1),
(218, 1),
(219, 1),
(220, 1),
(221, 1),
(222, 1),
(223, 1),
(223, 2),
(223, 3),
(223, 4),
(223, 5),
(224, 1),
(224, 2),
(224, 3),
(224, 4),
(224, 5),
(225, 1),
(226, 1),
(227, 1),
(228, 1),
(229, 1),
(230, 1),
(231, 1),
(232, 1),
(233, 1),
(234, 1),
(235, 1),
(236, 1),
(237, 1),
(238, 1),
(239, 1),
(240, 1),
(241, 1),
(242, 1),
(243, 1),
(243, 2),
(243, 3),
(243, 4),
(243, 5),
(244, 1),
(244, 2),
(244, 3),
(244, 4),
(244, 5),
(245, 1),
(246, 1),
(246, 2),
(247, 1),
(248, 1),
(249, 1),
(250, 1),
(251, 1),
(252, 1),
(253, 1),
(253, 2),
(253, 3),
(253, 4),
(253, 5),
(254, 1),
(254, 2),
(254, 3),
(254, 4),
(254, 5),
(255, 1),
(256, 1),
(256, 2),
(257, 1),
(258, 1),
(259, 1),
(260, 1),
(261, 1),
(262, 1),
(263, 1),
(263, 2),
(264, 1),
(264, 2),
(265, 1),
(265, 2),
(266, 1),
(266, 2),
(267, 1),
(267, 2),
(268, 1),
(268, 2),
(269, 1),
(270, 1),
(271, 1),
(272, 1),
(273, 1),
(273, 2),
(274, 1),
(274, 2),
(275, 1),
(275, 2),
(276, 1),
(276, 2),
(277, 1),
(277, 2),
(278, 1),
(278, 2),
(279, 1),
(280, 1),
(281, 1),
(282, 1),
(283, 1),
(283, 2),
(284, 1),
(284, 2),
(285, 1),
(285, 2),
(286, 1),
(286, 2),
(287, 1),
(287, 2),
(288, 1),
(288, 2),
(289, 1),
(290, 1),
(291, 1),
(292, 1),
(293, 1),
(293, 2),
(294, 1),
(294, 2),
(295, 1),
(295, 2),
(296, 1),
(296, 2),
(297, 1),
(297, 2),
(298, 1),
(298, 2),
(299, 1),
(300, 1),
(301, 1),
(302, 1),
(303, 1),
(303, 2),
(304, 1),
(304, 2),
(305, 1),
(305, 2),
(306, 1),
(306, 2),
(307, 1),
(307, 2),
(308, 1),
(308, 2),
(309, 1),
(310, 1),
(311, 1),
(312, 1),
(313, 1),
(313, 2),
(313, 3),
(313, 4),
(313, 5),
(314, 1),
(314, 2),
(314, 3),
(314, 4),
(314, 5),
(315, 1),
(315, 2),
(316, 1),
(316, 2),
(317, 1),
(318, 1),
(319, 1),
(319, 2),
(320, 1),
(320, 2),
(321, 1),
(322, 1),
(323, 1),
(324, 1),
(324, 2),
(324, 3),
(324, 4),
(324, 5),
(325, 1),
(325, 2),
(325, 3),
(325, 4),
(325, 5),
(326, 1),
(326, 2),
(327, 1),
(327, 2),
(328, 1),
(329, 1),
(330, 1),
(330, 2),
(331, 1),
(331, 2),
(332, 1),
(333, 1),
(334, 1),
(335, 1),
(335, 2),
(335, 3),
(335, 4),
(335, 5),
(336, 1),
(336, 2),
(336, 3),
(336, 4),
(336, 5),
(337, 1),
(337, 2),
(338, 1),
(338, 2),
(339, 1),
(340, 1),
(341, 1),
(341, 2),
(342, 1),
(342, 2),
(343, 1),
(344, 1),
(345, 1),
(346, 1),
(346, 2),
(346, 3),
(346, 4),
(346, 5),
(347, 1),
(347, 2),
(347, 3),
(347, 4),
(347, 5),
(348, 1),
(348, 2),
(348, 3),
(348, 4),
(348, 5),
(349, 1),
(349, 2),
(350, 1),
(350, 2),
(351, 1),
(352, 1),
(353, 1),
(353, 2),
(354, 1),
(354, 2),
(355, 1),
(356, 1),
(357, 1),
(357, 2),
(357, 3),
(357, 4),
(357, 5),
(357, 6),
(358, 1),
(358, 2),
(358, 3),
(358, 4),
(358, 5),
(358, 6),
(359, 1),
(359, 2),
(359, 3),
(359, 4),
(359, 5),
(359, 6),
(360, 1),
(360, 2),
(360, 3),
(360, 4),
(360, 5),
(361, 1),
(362, 1),
(363, 1),
(363, 2),
(364, 1),
(364, 2),
(365, 1),
(366, 1),
(367, 1),
(368, 1),
(369, 1),
(370, 1),
(371, 1),
(372, 1),
(373, 1),
(374, 1),
(375, 1),
(376, 1),
(377, 1),
(377, 6),
(378, 1),
(378, 2),
(378, 3),
(378, 4),
(378, 5),
(378, 6),
(379, 1),
(379, 2),
(379, 3),
(379, 4),
(379, 5),
(379, 6),
(380, 1),
(380, 2),
(380, 3),
(380, 4),
(380, 5),
(380, 6),
(381, 1),
(381, 2),
(381, 6),
(382, 1),
(382, 2),
(383, 1),
(384, 1),
(385, 1),
(385, 2),
(385, 3),
(385, 4),
(385, 5),
(385, 6),
(386, 1),
(386, 2),
(386, 3),
(386, 4),
(386, 5),
(386, 6),
(387, 1),
(388, 1),
(388, 2),
(389, 1),
(389, 6),
(390, 1),
(390, 6),
(391, 1),
(391, 2),
(391, 3),
(391, 4),
(391, 5),
(391, 6),
(392, 1),
(392, 2),
(393, 1),
(393, 2),
(394, 1),
(394, 2),
(395, 1),
(395, 2),
(396, 1),
(396, 2),
(397, 1),
(397, 2),
(398, 1),
(398, 2),
(398, 3),
(398, 4),
(398, 5),
(399, 1),
(399, 2),
(399, 3),
(399, 4),
(399, 5),
(400, 1),
(400, 2),
(401, 1),
(401, 2),
(402, 1),
(403, 1),
(404, 1),
(404, 2),
(405, 1),
(405, 2),
(406, 1),
(407, 1),
(408, 1),
(409, 1),
(409, 2),
(409, 3),
(409, 4),
(409, 5),
(410, 1),
(410, 2),
(410, 3),
(410, 4),
(410, 5),
(411, 1),
(411, 2),
(412, 1),
(412, 2),
(413, 1),
(414, 1),
(415, 1),
(415, 2),
(416, 1),
(416, 2),
(417, 1),
(418, 1),
(419, 1),
(420, 1),
(420, 2),
(420, 3),
(420, 4),
(420, 5),
(421, 1),
(421, 2),
(421, 3),
(421, 4),
(421, 5),
(422, 1),
(422, 2),
(423, 1),
(423, 2),
(424, 1),
(425, 1),
(426, 1),
(426, 2),
(427, 1),
(427, 2),
(428, 1),
(429, 1),
(430, 1),
(431, 1),
(431, 2),
(431, 3),
(431, 4),
(431, 5),
(431, 6),
(432, 1),
(432, 2),
(432, 3),
(432, 4),
(432, 5),
(432, 6),
(433, 1),
(433, 2),
(433, 3),
(433, 4),
(433, 5),
(434, 1),
(435, 1),
(436, 1),
(437, 1),
(438, 1),
(439, 1),
(440, 1),
(441, 1),
(441, 2),
(441, 3),
(441, 4),
(441, 5),
(441, 6),
(442, 1),
(442, 2),
(442, 3),
(442, 4),
(442, 5),
(442, 6),
(443, 1),
(443, 2),
(443, 6),
(444, 1),
(444, 2),
(444, 3),
(444, 4),
(444, 5),
(445, 1),
(446, 1),
(447, 1),
(447, 2),
(447, 3),
(447, 4),
(447, 5),
(448, 1),
(448, 2),
(448, 3),
(448, 4),
(448, 5),
(449, 1),
(450, 1),
(450, 2),
(450, 3),
(450, 4),
(450, 5),
(451, 1),
(451, 6),
(452, 1),
(452, 6),
(453, 1),
(453, 6),
(454, 1),
(454, 2),
(454, 3),
(454, 4),
(454, 5),
(454, 6),
(455, 1),
(455, 2),
(455, 3),
(455, 4),
(455, 5),
(456, 1),
(456, 2),
(456, 3),
(456, 4),
(456, 5),
(457, 1),
(457, 2),
(458, 1),
(458, 2),
(459, 1),
(460, 1),
(461, 1),
(461, 2),
(462, 1),
(462, 2),
(463, 1),
(464, 1),
(465, 1),
(465, 2),
(465, 3),
(465, 4),
(465, 5),
(465, 6),
(466, 1),
(466, 2),
(466, 3),
(466, 4),
(466, 5),
(466, 6),
(467, 1),
(467, 2),
(467, 3),
(467, 4),
(467, 5),
(467, 6),
(468, 1),
(468, 2),
(469, 1),
(470, 1),
(471, 1),
(471, 2),
(471, 3),
(471, 4),
(471, 5),
(472, 1),
(472, 2),
(472, 3),
(472, 4),
(472, 5),
(473, 1),
(474, 1),
(474, 2),
(474, 3),
(474, 4),
(474, 5),
(475, 1),
(475, 2),
(475, 3),
(475, 4),
(475, 5),
(475, 6),
(476, 1),
(476, 6),
(477, 1),
(477, 2),
(478, 1),
(478, 2),
(479, 1),
(479, 2),
(480, 1),
(480, 2),
(481, 1),
(481, 2),
(482, 1),
(483, 1),
(483, 2),
(484, 1),
(484, 2),
(485, 1),
(486, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sales_returns`
--

CREATE TABLE `sales_returns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `dealer_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'requested',
  `reason` text DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `vehicle_id` bigint(20) UNSIGNED DEFAULT NULL,
  `driver_id` bigint(20) UNSIGNED DEFAULT NULL,
  `dispatched_at` timestamp NULL DEFAULT NULL,
  `receiving_depot_id` bigint(20) UNSIGNED DEFAULT NULL,
  `received_by` bigint(20) UNSIGNED DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `external_reference` varchar(255) DEFAULT NULL,
  `tally_guid` varchar(255) DEFAULT NULL,
  `tally_credit_note_number` varchar(255) DEFAULT NULL,
  `sync_status` varchar(255) NOT NULL DEFAULT 'not_synced',
  `sync_error` text DEFAULT NULL,
  `synced_at` timestamp NULL DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_return_items`
--

CREATE TABLE `sales_return_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sales_return_id` bigint(20) UNSIGNED NOT NULL,
  `order_item_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `requested_qty` decimal(12,2) NOT NULL,
  `received_qty` decimal(12,2) DEFAULT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_teams`
--

CREATE TABLE `sales_teams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales_teams`
--

INSERT INTO `sales_teams` (`id`, `name`, `code`, `description`, `status`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'Sales Team', 'TEAM-1', 'Sales Team 1 covering its assigned executives nationwide.', 1, NULL, 1, NULL, NULL, '2026-08-06 04:39:52', '2026-08-22 07:44:59'),
(2, 'Collection Team', 'TEAM-2', 'Sales Team 2 covering its assigned executives nationwide.', 1, NULL, 1, NULL, NULL, '2026-08-06 04:39:52', '2026-08-22 07:45:14'),
(3, 'Service Team', 'TEAM-3', 'Sales Team 3 covering its assigned executives nationwide.', 1, NULL, 1, NULL, NULL, '2026-08-06 04:39:52', '2026-08-22 07:45:28');

-- --------------------------------------------------------

--
-- Table structure for table `service_centers`
--

CREATE TABLE `service_centers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('7dz97flVbBeTtoT1cPkxdrvmvQAcjCTCNC3ocyjW', 80, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Claude/2.2553.1 Chrome/152.0.7977.76 Safari/537.36 MSIX', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiTUdiZzVFcXZpbnptN0N3SHJ5MlFwNnBRRFhQenNPUkpZWVFkYlFLcCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDA6Imh0dHA6Ly9sb2NhbGhvc3QvZ2F6aV9wdW1wL2d1aWRlP3Y9Y2xlYW4iO3M6NToicm91dGUiO3M6MTE6Imd1aWRlLmluZGV4Ijt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6ODA7fQ==', 1789988738),
('BhQ9YLCSYdyCRTXbGrRezfuR8PvcRDPDCxySPXIQ', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:156.0) Gecko/20100101 Firefox/156.0', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiVHdsZ1VoS21DOHlaUjg2YldkSzRBT1o1QnJySjV0YUlOWk9WdEJNZyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjM2OiJodHRwczovL2xvY2FsaG9zdC9nYXppX3B1bXAvc2V0dGluZ3MiO3M6NToicm91dGUiO3M6MTQ6InNldHRpbmdzLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1789990904);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `company_logo` varchar(255) DEFAULT NULL,
  `company_favicon` varchar(255) DEFAULT NULL,
  `company_address` text DEFAULT NULL,
  `company_phone` varchar(255) DEFAULT NULL,
  `company_email` varchar(255) DEFAULT NULL,
  `attendance_office_start_time` varchar(255) NOT NULL,
  `attendance_office_end_time` varchar(255) NOT NULL,
  `attendance_late_grace_minutes` int(10) UNSIGNED NOT NULL,
  `attendance_weekend_days` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`attendance_weekend_days`)),
  `visit_gps_radius_meters` int(10) UNSIGNED NOT NULL,
  `order_max_discount_percent` decimal(5,2) NOT NULL,
  `collection_overpayment_tolerance_percent` decimal(5,2) NOT NULL,
  `target_grade_a_min` tinyint(3) UNSIGNED NOT NULL,
  `target_grade_b_min` tinyint(3) UNSIGNED NOT NULL,
  `target_grade_c_min` tinyint(3) UNSIGNED NOT NULL,
  `target_grade_d_min` tinyint(3) UNSIGNED NOT NULL,
  `low_performance_grades` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`low_performance_grades`)),
  `target_reminder_days_before_month_end` int(10) UNSIGNED NOT NULL,
  `target_reminder_min_pct` decimal(5,2) NOT NULL,
  `live_gps_stale_after_minutes` int(10) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sms_gateway_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `sms_gateway_provider` varchar(255) DEFAULT NULL,
  `sms_gateway_api_url` varchar(255) DEFAULT NULL,
  `sms_gateway_api_key` varchar(255) DEFAULT NULL,
  `sms_gateway_sender_id` varchar(255) DEFAULT NULL,
  `sms_channel` varchar(255) NOT NULL DEFAULT 'sms',
  `collection_otp_expiry_minutes` smallint(5) UNSIGNED NOT NULL DEFAULT 10,
  `cash_daily_limit_amount` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `company_name`, `company_logo`, `company_favicon`, `company_address`, `company_phone`, `company_email`, `attendance_office_start_time`, `attendance_office_end_time`, `attendance_late_grace_minutes`, `attendance_weekend_days`, `visit_gps_radius_meters`, `order_max_discount_percent`, `collection_overpayment_tolerance_percent`, `target_grade_a_min`, `target_grade_b_min`, `target_grade_c_min`, `target_grade_d_min`, `low_performance_grades`, `target_reminder_days_before_month_end`, `target_reminder_min_pct`, `live_gps_stale_after_minutes`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`, `sms_gateway_enabled`, `sms_gateway_provider`, `sms_gateway_api_url`, `sms_gateway_api_key`, `sms_gateway_sender_id`, `sms_channel`, `collection_otp_expiry_minutes`, `cash_daily_limit_amount`) VALUES
(1, 'Gazi Group SFA', 'settings/vKmKYVjGFsZxNpZQN68KHSgQmp62nBks7YjjcW04.png', 'settings/EmqVOJsKAs9g0DDmRQ5XVpVY2MTO221U3wehG7qx.png', '37/2, Pritom Zaman Tower, Purana Paltan, Dhaka-1000', '01958538607', 'info@gcart.com.bd', '09:00', '18:00', 15, '[\"Friday\",\"Saturday\"]', 300, 20.00, 10.00, 90, 75, 60, 40, '[\"D\",\"F\"]', 5, 70.00, 30, NULL, 1, NULL, NULL, '2026-08-06 04:39:49', '2026-08-24 00:32:30', 0, NULL, NULL, NULL, NULL, 'sms', 10, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sync_logs`
--

CREATE TABLE `sync_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `entity_type` varchar(255) NOT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `direction` varchar(255) NOT NULL,
  `request_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `response_time` timestamp NULL DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `external_reference` varchar(255) DEFAULT NULL,
  `tally_guid` varchar(255) DEFAULT NULL,
  `tally_voucher_number` varchar(255) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sync_logs`
--

INSERT INTO `sync_logs` (`id`, `entity_type`, `entity_id`, `direction`, `request_time`, `response_time`, `status`, `external_reference`, `tally_guid`, `tally_voucher_number`, `error_message`, `user_id`, `created_at`) VALUES
(1, 'dealer', NULL, 'pull_from_tally', '2026-09-07 12:00:16', '2026-09-07 12:00:16', 'success', 'SFA-PULL-dealer-20260907175828', NULL, NULL, NULL, NULL, '2026-09-07 12:00:16'),
(2, 'retailer', NULL, 'pull_from_tally', '2026-09-07 12:00:16', '2026-09-07 12:00:17', 'success', 'SFA-PULL-retailer-20260907175828', NULL, NULL, NULL, NULL, '2026-09-07 12:00:17'),
(3, 'product', NULL, 'pull_from_tally', '2026-09-07 12:00:16', '2026-09-07 12:00:17', 'success', 'SFA-PULL-product-20260907175828', NULL, NULL, NULL, NULL, '2026-09-07 12:00:17'),
(4, 'depot', NULL, 'pull_from_tally', '2026-09-07 12:00:16', '2026-09-07 12:00:17', 'success', 'SFA-PULL-depot-20260907175828', NULL, NULL, NULL, NULL, '2026-09-07 12:00:17'),
(5, 'dealer', NULL, 'pull_from_tally', '2026-09-08 03:53:12', '2026-09-08 03:53:13', 'success', 'SFA-PULL-dealer-20260908095304', NULL, NULL, NULL, NULL, '2026-09-08 03:53:13'),
(6, 'retailer', NULL, 'pull_from_tally', '2026-09-08 03:53:12', '2026-09-08 03:53:13', 'success', 'SFA-PULL-retailer-20260908095304', NULL, NULL, NULL, NULL, '2026-09-08 03:53:13'),
(7, 'product', NULL, 'pull_from_tally', '2026-09-08 03:53:12', '2026-09-08 03:53:14', 'success', 'SFA-PULL-product-20260908095304', NULL, NULL, NULL, NULL, '2026-09-08 03:53:14'),
(8, 'depot', NULL, 'pull_from_tally', '2026-09-08 03:53:12', '2026-09-08 03:53:14', 'success', 'SFA-PULL-depot-20260908095304', NULL, NULL, NULL, NULL, '2026-09-08 03:53:14'),
(9, 'depot', 1, 'push_to_tally', '2026-09-08 04:29:41', '2026-09-08 04:29:41', 'failed', 'SFA-MPUSH-depot-1', NULL, NULL, 'Tally created 0 master(s) and reported 0 error(s) for \"Main Depot\". A name that already exists in Tally is reported this way — map it instead of pushing it.', NULL, '2026-09-08 04:29:41'),
(10, 'depot', 1, 'push_to_tally', '2026-09-08 04:33:54', '2026-09-08 04:33:54', 'failed', 'SFA-MPUSH-depot-1', NULL, NULL, 'Tally did not create \"Main Depot\": Godown \'Primary\' does not exist!', NULL, '2026-09-08 04:33:54'),
(11, 'depot', NULL, 'pull_from_tally', '2026-09-08 04:36:50', '2026-09-08 04:36:51', 'success', 'SFA-PULL-depot-20260908103642', NULL, NULL, NULL, NULL, '2026-09-08 04:36:51'),
(12, 'dealer', 1, 'push_to_tally', '2026-09-08 04:36:51', '2026-09-08 04:36:51', 'success', 'SFA-MPUSH-dealer-1', 'd530416d-350a-4df9-bc3c-06943927816a-000000db', NULL, NULL, NULL, '2026-09-08 04:36:51'),
(13, 'dealer', 2, 'push_to_tally', '2026-09-08 04:57:49', '2026-09-08 04:57:49', 'success', 'SFA-MPUSH-dealer-2', 'd530416d-350a-4df9-bc3c-06943927816a-000000dc', NULL, NULL, NULL, '2026-09-08 04:57:49'),
(14, 'dealer', 3, 'push_to_tally', '2026-09-08 04:57:49', '2026-09-08 04:57:50', 'success', 'SFA-MPUSH-dealer-3', 'd530416d-350a-4df9-bc3c-06943927816a-000000dd', NULL, NULL, NULL, '2026-09-08 04:57:50'),
(15, 'dealer', 4, 'push_to_tally', '2026-09-08 04:57:49', '2026-09-08 04:57:50', 'success', 'SFA-MPUSH-dealer-4', 'd530416d-350a-4df9-bc3c-06943927816a-000000de', NULL, NULL, NULL, '2026-09-08 04:57:50'),
(16, 'dealer', 5, 'push_to_tally', '2026-09-08 04:57:49', '2026-09-08 04:57:50', 'success', 'SFA-MPUSH-dealer-5', 'd530416d-350a-4df9-bc3c-06943927816a-000000df', NULL, NULL, NULL, '2026-09-08 04:57:50'),
(17, 'dealer', 14, 'push_to_tally', '2026-09-08 04:57:49', '2026-09-08 04:57:51', 'success', 'SFA-MPUSH-dealer-14', 'd530416d-350a-4df9-bc3c-06943927816a-000000e0', NULL, NULL, NULL, '2026-09-08 04:57:51'),
(18, 'product', 1, 'push_to_tally', '2026-09-08 04:57:49', '2026-09-08 04:57:51', 'success', 'SFA-MPUSH-product-1', 'd530416d-350a-4df9-bc3c-06943927816a-000000e1', NULL, NULL, NULL, '2026-09-08 04:57:51'),
(19, 'product', 2, 'push_to_tally', '2026-09-08 04:57:49', '2026-09-08 04:57:52', 'success', 'SFA-MPUSH-product-2', 'd530416d-350a-4df9-bc3c-06943927816a-000000e2', NULL, NULL, NULL, '2026-09-08 04:57:52'),
(20, 'product', 3, 'push_to_tally', '2026-09-08 04:57:49', '2026-09-08 04:57:52', 'success', 'SFA-MPUSH-product-3', 'd530416d-350a-4df9-bc3c-06943927816a-000000e3', NULL, NULL, NULL, '2026-09-08 04:57:52'),
(21, 'product', 4, 'push_to_tally', '2026-09-08 04:57:49', '2026-09-08 04:57:53', 'success', 'SFA-MPUSH-product-4', 'd530416d-350a-4df9-bc3c-06943927816a-000000e4', NULL, NULL, NULL, '2026-09-08 04:57:53'),
(22, 'product', 5, 'push_to_tally', '2026-09-08 04:57:49', '2026-09-08 04:57:53', 'success', 'SFA-MPUSH-product-5', 'd530416d-350a-4df9-bc3c-06943927816a-000000e5', NULL, NULL, NULL, '2026-09-08 04:57:53'),
(23, 'product', 6, 'push_to_tally', '2026-09-08 04:57:54', '2026-09-08 04:57:54', 'success', 'SFA-MPUSH-product-6', 'd530416d-350a-4df9-bc3c-06943927816a-000000e6', NULL, NULL, NULL, '2026-09-08 04:57:54'),
(24, 'product', 7, 'push_to_tally', '2026-09-08 04:57:54', '2026-09-08 04:57:55', 'success', 'SFA-MPUSH-product-7', 'd530416d-350a-4df9-bc3c-06943927816a-000000e7', NULL, NULL, NULL, '2026-09-08 04:57:55'),
(25, 'product', 8, 'push_to_tally', '2026-09-08 04:57:54', '2026-09-08 04:57:55', 'success', 'SFA-MPUSH-product-8', 'd530416d-350a-4df9-bc3c-06943927816a-000000e8', NULL, NULL, NULL, '2026-09-08 04:57:55'),
(26, 'product', 9, 'push_to_tally', '2026-09-08 04:57:54', '2026-09-08 04:57:56', 'success', 'SFA-MPUSH-product-9', 'd530416d-350a-4df9-bc3c-06943927816a-000000e9', NULL, NULL, NULL, '2026-09-08 04:57:56'),
(27, 'product', 10, 'push_to_tally', '2026-09-08 04:57:54', '2026-09-08 04:57:56', 'success', 'SFA-MPUSH-product-10', 'd530416d-350a-4df9-bc3c-06943927816a-000000ea', NULL, NULL, NULL, '2026-09-08 04:57:56'),
(28, 'product', 11, 'push_to_tally', '2026-09-08 04:57:54', '2026-09-08 04:57:57', 'success', 'SFA-MPUSH-product-11', 'd530416d-350a-4df9-bc3c-06943927816a-000000eb', NULL, NULL, NULL, '2026-09-08 04:57:57'),
(29, 'product', 12, 'push_to_tally', '2026-09-08 04:57:54', '2026-09-08 04:57:57', 'success', 'SFA-MPUSH-product-12', 'd530416d-350a-4df9-bc3c-06943927816a-000000ec', NULL, NULL, NULL, '2026-09-08 04:57:57'),
(30, 'product', 13, 'push_to_tally', '2026-09-08 04:57:54', '2026-09-08 04:57:58', 'success', 'SFA-MPUSH-product-13', 'd530416d-350a-4df9-bc3c-06943927816a-000000ed', NULL, NULL, NULL, '2026-09-08 04:57:58'),
(31, 'product', 14, 'push_to_tally', '2026-09-08 04:57:54', '2026-09-08 04:57:58', 'success', 'SFA-MPUSH-product-14', 'd530416d-350a-4df9-bc3c-06943927816a-000000ee', NULL, NULL, NULL, '2026-09-08 04:57:58'),
(32, 'product', 15, 'push_to_tally', '2026-09-08 04:57:54', '2026-09-08 04:57:58', 'success', 'SFA-MPUSH-product-15', 'd530416d-350a-4df9-bc3c-06943927816a-000000ef', NULL, NULL, NULL, '2026-09-08 04:57:58'),
(33, 'product', 16, 'push_to_tally', '2026-09-08 04:57:59', '2026-09-08 04:57:59', 'success', 'SFA-MPUSH-product-16', 'd530416d-350a-4df9-bc3c-06943927816a-000000f0', NULL, NULL, NULL, '2026-09-08 04:57:59'),
(34, 'product', 17, 'push_to_tally', '2026-09-08 04:57:59', '2026-09-08 04:58:00', 'success', 'SFA-MPUSH-product-17', 'd530416d-350a-4df9-bc3c-06943927816a-000000f1', NULL, NULL, NULL, '2026-09-08 04:58:00'),
(35, 'product', 18, 'push_to_tally', '2026-09-08 04:57:59', '2026-09-08 04:58:00', 'success', 'SFA-MPUSH-product-18', 'd530416d-350a-4df9-bc3c-06943927816a-000000f2', NULL, NULL, NULL, '2026-09-08 04:58:00'),
(36, 'product', 19, 'push_to_tally', '2026-09-08 04:57:59', '2026-09-08 04:58:01', 'success', 'SFA-MPUSH-product-19', 'd530416d-350a-4df9-bc3c-06943927816a-000000f3', NULL, NULL, NULL, '2026-09-08 04:58:01'),
(37, 'product', 20, 'push_to_tally', '2026-09-08 04:57:59', '2026-09-08 04:58:01', 'success', 'SFA-MPUSH-product-20', 'd530416d-350a-4df9-bc3c-06943927816a-000000f4', NULL, NULL, NULL, '2026-09-08 04:58:01'),
(38, 'product', 21, 'push_to_tally', '2026-09-08 04:57:59', '2026-09-08 04:58:01', 'success', 'SFA-MPUSH-product-21', 'd530416d-350a-4df9-bc3c-06943927816a-000000f5', NULL, NULL, NULL, '2026-09-08 04:58:01'),
(39, 'product', 22, 'push_to_tally', '2026-09-08 04:57:59', '2026-09-08 04:58:02', 'success', 'SFA-MPUSH-product-22', 'd530416d-350a-4df9-bc3c-06943927816a-000000f6', NULL, NULL, NULL, '2026-09-08 04:58:02'),
(40, 'product', 23, 'push_to_tally', '2026-09-08 04:57:59', '2026-09-08 04:58:02', 'success', 'SFA-MPUSH-product-23', 'd530416d-350a-4df9-bc3c-06943927816a-000000f7', NULL, NULL, NULL, '2026-09-08 04:58:02'),
(41, 'product', 24, 'push_to_tally', '2026-09-08 04:57:59', '2026-09-08 04:58:03', 'success', 'SFA-MPUSH-product-24', 'd530416d-350a-4df9-bc3c-06943927816a-000000f8', NULL, NULL, NULL, '2026-09-08 04:58:03'),
(42, 'product', 25, 'push_to_tally', '2026-09-08 04:57:59', '2026-09-08 04:58:03', 'success', 'SFA-MPUSH-product-25', 'd530416d-350a-4df9-bc3c-06943927816a-000000f9', NULL, NULL, NULL, '2026-09-08 04:58:03'),
(43, 'product', 26, 'push_to_tally', '2026-09-08 04:58:03', '2026-09-08 04:58:04', 'success', 'SFA-MPUSH-product-26', 'd530416d-350a-4df9-bc3c-06943927816a-000000fa', NULL, NULL, NULL, '2026-09-08 04:58:04'),
(44, 'product', 27, 'push_to_tally', '2026-09-08 04:58:03', '2026-09-08 04:58:04', 'success', 'SFA-MPUSH-product-27', 'd530416d-350a-4df9-bc3c-06943927816a-000000fb', NULL, NULL, NULL, '2026-09-08 04:58:04'),
(45, 'product', 28, 'push_to_tally', '2026-09-08 04:58:03', '2026-09-08 04:58:05', 'success', 'SFA-MPUSH-product-28', 'd530416d-350a-4df9-bc3c-06943927816a-000000fc', NULL, NULL, NULL, '2026-09-08 04:58:05'),
(46, 'product', 29, 'push_to_tally', '2026-09-08 04:58:03', '2026-09-08 04:58:05', 'success', 'SFA-MPUSH-product-29', 'd530416d-350a-4df9-bc3c-06943927816a-000000fd', NULL, NULL, NULL, '2026-09-08 04:58:05'),
(47, 'product', 30, 'push_to_tally', '2026-09-08 04:58:03', '2026-09-08 04:58:06', 'success', 'SFA-MPUSH-product-30', 'd530416d-350a-4df9-bc3c-06943927816a-000000fe', NULL, NULL, NULL, '2026-09-08 04:58:06'),
(48, 'product', 31, 'push_to_tally', '2026-09-08 04:58:03', '2026-09-08 04:58:06', 'success', 'SFA-MPUSH-product-31', 'd530416d-350a-4df9-bc3c-06943927816a-000000ff', NULL, NULL, NULL, '2026-09-08 04:58:06'),
(49, 'product', 32, 'push_to_tally', '2026-09-08 04:58:03', '2026-09-08 04:58:07', 'success', 'SFA-MPUSH-product-32', 'd530416d-350a-4df9-bc3c-06943927816a-00000100', NULL, NULL, NULL, '2026-09-08 04:58:07'),
(50, 'product', 33, 'push_to_tally', '2026-09-08 04:58:03', '2026-09-08 04:58:07', 'success', 'SFA-MPUSH-product-33', 'd530416d-350a-4df9-bc3c-06943927816a-00000101', NULL, NULL, NULL, '2026-09-08 04:58:07'),
(51, 'product', 34, 'push_to_tally', '2026-09-08 04:58:03', '2026-09-08 04:58:08', 'success', 'SFA-MPUSH-product-34', 'd530416d-350a-4df9-bc3c-06943927816a-00000102', NULL, NULL, NULL, '2026-09-08 04:58:08'),
(52, 'product', 35, 'push_to_tally', '2026-09-08 04:58:03', '2026-09-08 04:58:08', 'success', 'SFA-MPUSH-product-35', 'd530416d-350a-4df9-bc3c-06943927816a-00000103', NULL, NULL, NULL, '2026-09-08 04:58:08'),
(53, 'product', 36, 'push_to_tally', '2026-09-08 04:58:09', '2026-09-08 04:58:09', 'success', 'SFA-MPUSH-product-36', 'd530416d-350a-4df9-bc3c-06943927816a-00000104', NULL, NULL, NULL, '2026-09-08 04:58:09'),
(54, 'product', 37, 'push_to_tally', '2026-09-08 04:58:09', '2026-09-08 04:58:09', 'success', 'SFA-MPUSH-product-37', 'd530416d-350a-4df9-bc3c-06943927816a-00000105', NULL, NULL, NULL, '2026-09-08 04:58:09'),
(55, 'product', 38, 'push_to_tally', '2026-09-08 04:58:09', '2026-09-08 04:58:10', 'success', 'SFA-MPUSH-product-38', 'd530416d-350a-4df9-bc3c-06943927816a-00000106', NULL, NULL, NULL, '2026-09-08 04:58:10'),
(56, 'product', 39, 'push_to_tally', '2026-09-08 04:58:09', '2026-09-08 04:58:10', 'success', 'SFA-MPUSH-product-39', 'd530416d-350a-4df9-bc3c-06943927816a-00000107', NULL, NULL, NULL, '2026-09-08 04:58:10'),
(57, 'product', 40, 'push_to_tally', '2026-09-08 04:58:09', '2026-09-08 04:58:11', 'success', 'SFA-MPUSH-product-40', 'd530416d-350a-4df9-bc3c-06943927816a-00000108', NULL, NULL, NULL, '2026-09-08 04:58:11'),
(58, 'dealer', NULL, 'pull_from_tally', '2026-09-08 04:59:21', '2026-09-08 04:59:21', 'success', 'SFA-PULL-dealer-20260908105920', NULL, NULL, NULL, NULL, '2026-09-08 04:59:21'),
(59, 'retailer', NULL, 'pull_from_tally', '2026-09-08 04:59:21', '2026-09-08 04:59:22', 'success', 'SFA-PULL-retailer-20260908105920', NULL, NULL, NULL, NULL, '2026-09-08 04:59:22'),
(60, 'product', NULL, 'pull_from_tally', '2026-09-08 04:59:21', '2026-09-08 04:59:22', 'success', 'SFA-PULL-product-20260908105920', NULL, NULL, NULL, NULL, '2026-09-08 04:59:22'),
(61, 'depot', NULL, 'pull_from_tally', '2026-09-08 04:59:21', '2026-09-08 04:59:22', 'success', 'SFA-PULL-depot-20260908105920', NULL, NULL, NULL, NULL, '2026-09-08 04:59:22'),
(62, 'dealer', NULL, 'pull_from_tally', '2026-09-08 05:57:21', '2026-09-08 05:57:22', 'success', 'SFA-PULL-dealer-20260908114753', NULL, NULL, NULL, NULL, '2026-09-08 05:57:22'),
(63, 'retailer', NULL, 'pull_from_tally', '2026-09-08 05:57:21', '2026-09-08 05:57:22', 'success', 'SFA-PULL-retailer-20260908114753', NULL, NULL, NULL, NULL, '2026-09-08 05:57:22'),
(64, 'product', NULL, 'pull_from_tally', '2026-09-08 05:57:21', '2026-09-08 05:57:23', 'success', 'SFA-PULL-product-20260908114753', NULL, NULL, NULL, NULL, '2026-09-08 05:57:23'),
(65, 'depot', NULL, 'pull_from_tally', '2026-09-08 05:57:21', '2026-09-08 05:57:23', 'success', 'SFA-PULL-depot-20260908114753', NULL, NULL, NULL, NULL, '2026-09-08 05:57:23'),
(66, 'ledger', 1, 'pull_from_tally', '2026-09-08 05:57:22', '2026-09-08 05:57:23', 'success', 'SFA-LED-PULL-1-20260908114753', NULL, NULL, NULL, NULL, '2026-09-08 05:57:23'),
(67, 'ledger', 2, 'pull_from_tally', '2026-09-08 05:57:22', '2026-09-08 05:57:24', 'success', 'SFA-LED-PULL-2-20260908114753', NULL, NULL, NULL, NULL, '2026-09-08 05:57:24'),
(68, 'ledger', 3, 'pull_from_tally', '2026-09-08 05:57:22', '2026-09-08 05:57:24', 'success', 'SFA-LED-PULL-3-20260908114753', NULL, NULL, NULL, NULL, '2026-09-08 05:57:24'),
(69, 'ledger', 4, 'pull_from_tally', '2026-09-08 05:57:22', '2026-09-08 05:57:24', 'success', 'SFA-LED-PULL-4-20260908114753', NULL, NULL, NULL, NULL, '2026-09-08 05:57:24'),
(70, 'ledger', 5, 'pull_from_tally', '2026-09-08 05:57:22', '2026-09-08 05:57:25', 'success', 'SFA-LED-PULL-5-20260908114753', NULL, NULL, NULL, NULL, '2026-09-08 05:57:25'),
(71, 'ledger', 14, 'pull_from_tally', '2026-09-08 05:57:22', '2026-09-08 05:57:25', 'success', 'SFA-LED-PULL-14-20260908114753', NULL, NULL, NULL, NULL, '2026-09-08 05:57:25'),
(72, 'ledger', 18, 'pull_from_tally', '2026-09-08 05:57:25', '2026-09-08 05:57:26', 'success', 'SFA-LED-PULL-18-20260908114753', NULL, NULL, NULL, NULL, '2026-09-08 05:57:26'),
(73, 'ledger', 19, 'pull_from_tally', '2026-09-08 05:57:25', '2026-09-08 05:57:26', 'success', 'SFA-LED-PULL-19-20260908114753', NULL, NULL, NULL, NULL, '2026-09-08 05:57:26'),
(74, 'dealer', 20, 'push_to_tally', '2026-09-08 05:57:25', '2026-09-08 05:57:27', 'success', 'SFA-MPUSH-dealer-20', 'd530416d-350a-4df9-bc3c-06943927816a-0000010a', NULL, NULL, NULL, '2026-09-08 05:57:27'),
(75, 'dealer', NULL, 'pull_from_tally', '2026-09-08 06:26:42', '2026-09-08 06:26:42', 'success', 'SFA-PULL-dealer-20260908122634', NULL, NULL, NULL, NULL, '2026-09-08 06:26:42'),
(76, 'retailer', NULL, 'pull_from_tally', '2026-09-08 06:26:42', '2026-09-08 06:26:43', 'success', 'SFA-PULL-retailer-20260908122634', NULL, NULL, NULL, NULL, '2026-09-08 06:26:43'),
(77, 'product', NULL, 'pull_from_tally', '2026-09-08 06:26:42', '2026-09-08 06:26:43', 'success', 'SFA-PULL-product-20260908122634', NULL, NULL, NULL, NULL, '2026-09-08 06:26:43'),
(78, 'depot', NULL, 'pull_from_tally', '2026-09-08 06:26:42', '2026-09-08 06:26:44', 'success', 'SFA-PULL-depot-20260908122634', NULL, NULL, NULL, NULL, '2026-09-08 06:26:44'),
(79, 'ledger', 1, 'pull_from_tally', '2026-09-08 06:26:42', '2026-09-08 06:26:44', 'success', 'SFA-LED-PULL-1-20260908122634', NULL, NULL, NULL, NULL, '2026-09-08 06:26:44'),
(80, 'ledger', 2, 'pull_from_tally', '2026-09-08 06:26:42', '2026-09-08 06:26:44', 'success', 'SFA-LED-PULL-2-20260908122634', NULL, NULL, NULL, NULL, '2026-09-08 06:26:44'),
(81, 'ledger', 3, 'pull_from_tally', '2026-09-08 06:26:42', '2026-09-08 06:26:45', 'success', 'SFA-LED-PULL-3-20260908122634', NULL, NULL, NULL, NULL, '2026-09-08 06:26:45'),
(82, 'ledger', 4, 'pull_from_tally', '2026-09-08 06:26:42', '2026-09-08 06:26:45', 'success', 'SFA-LED-PULL-4-20260908122634', NULL, NULL, NULL, NULL, '2026-09-08 06:26:45'),
(83, 'ledger', 5, 'pull_from_tally', '2026-09-08 06:26:42', '2026-09-08 06:26:46', 'success', 'SFA-LED-PULL-5-20260908122634', NULL, NULL, NULL, NULL, '2026-09-08 06:26:46'),
(84, 'ledger', 14, 'pull_from_tally', '2026-09-08 06:26:42', '2026-09-08 06:26:46', 'success', 'SFA-LED-PULL-14-20260908122634', NULL, NULL, NULL, NULL, '2026-09-08 06:26:46'),
(85, 'ledger', 18, 'pull_from_tally', '2026-09-08 06:26:53', '2026-09-08 06:26:53', 'success', 'SFA-LED-PULL-18-20260908122634', NULL, NULL, NULL, NULL, '2026-09-08 06:26:53'),
(86, 'ledger', 19, 'pull_from_tally', '2026-09-08 06:26:53', '2026-09-08 06:26:54', 'success', 'SFA-LED-PULL-19-20260908122634', NULL, NULL, NULL, NULL, '2026-09-08 06:26:54'),
(87, 'ledger', 20, 'pull_from_tally', '2026-09-08 06:26:53', '2026-09-08 06:26:54', 'success', 'SFA-LED-PULL-20-20260908122634', NULL, NULL, NULL, NULL, '2026-09-08 06:26:54'),
(88, 'ledger', 21, 'pull_from_tally', '2026-09-08 06:26:53', '2026-09-08 06:26:54', 'success', 'SFA-LED-PULL-21-20260908122634', NULL, NULL, NULL, NULL, '2026-09-08 06:26:54'),
(89, 'dealer', 22, 'push_to_tally', '2026-09-08 06:26:53', '2026-09-08 06:26:55', 'success', 'SFA-MPUSH-dealer-22', 'd530416d-350a-4df9-bc3c-06943927816a-0000010b', NULL, NULL, NULL, '2026-09-08 06:26:55'),
(90, 'dealer', NULL, 'pull_from_tally', '2026-09-08 06:27:12', '2026-09-08 06:27:12', 'success', 'SFA-PULL-dealer-20260908122658', NULL, NULL, NULL, NULL, '2026-09-08 06:27:12'),
(91, 'retailer', NULL, 'pull_from_tally', '2026-09-08 06:27:12', '2026-09-08 06:27:13', 'success', 'SFA-PULL-retailer-20260908122658', NULL, NULL, NULL, NULL, '2026-09-08 06:27:13'),
(92, 'product', NULL, 'pull_from_tally', '2026-09-08 06:27:12', '2026-09-08 06:27:13', 'success', 'SFA-PULL-product-20260908122658', NULL, NULL, NULL, NULL, '2026-09-08 06:27:13'),
(93, 'depot', NULL, 'pull_from_tally', '2026-09-08 06:27:12', '2026-09-08 06:27:13', 'success', 'SFA-PULL-depot-20260908122658', NULL, NULL, NULL, NULL, '2026-09-08 06:27:13'),
(94, 'ledger', 1, 'pull_from_tally', '2026-09-08 06:27:12', '2026-09-08 06:27:14', 'success', 'SFA-LED-PULL-1-20260908122658', NULL, NULL, NULL, NULL, '2026-09-08 06:27:14'),
(95, 'ledger', 2, 'pull_from_tally', '2026-09-08 06:27:12', '2026-09-08 06:27:14', 'success', 'SFA-LED-PULL-2-20260908122658', NULL, NULL, NULL, NULL, '2026-09-08 06:27:14'),
(96, 'ledger', 3, 'pull_from_tally', '2026-09-08 06:27:12', '2026-09-08 06:27:14', 'success', 'SFA-LED-PULL-3-20260908122658', NULL, NULL, NULL, NULL, '2026-09-08 06:27:14'),
(97, 'ledger', 4, 'pull_from_tally', '2026-09-08 06:27:12', '2026-09-08 06:27:15', 'success', 'SFA-LED-PULL-4-20260908122658', NULL, NULL, NULL, NULL, '2026-09-08 06:27:15'),
(98, 'ledger', 5, 'pull_from_tally', '2026-09-08 06:27:12', '2026-09-08 06:27:15', 'success', 'SFA-LED-PULL-5-20260908122658', NULL, NULL, NULL, NULL, '2026-09-08 06:27:15'),
(99, 'ledger', 14, 'pull_from_tally', '2026-09-08 06:27:12', '2026-09-08 06:27:15', 'success', 'SFA-LED-PULL-14-20260908122658', NULL, NULL, NULL, NULL, '2026-09-08 06:27:15'),
(100, 'ledger', 18, 'pull_from_tally', '2026-09-08 06:27:23', '2026-09-08 06:27:23', 'success', 'SFA-LED-PULL-18-20260908122658', NULL, NULL, NULL, NULL, '2026-09-08 06:27:23'),
(101, 'ledger', 19, 'pull_from_tally', '2026-09-08 06:27:23', '2026-09-08 06:27:23', 'success', 'SFA-LED-PULL-19-20260908122658', NULL, NULL, NULL, NULL, '2026-09-08 06:27:23'),
(102, 'ledger', 20, 'pull_from_tally', '2026-09-08 06:27:23', '2026-09-08 06:27:24', 'success', 'SFA-LED-PULL-20-20260908122658', NULL, NULL, NULL, NULL, '2026-09-08 06:27:24'),
(103, 'ledger', 21, 'pull_from_tally', '2026-09-08 06:27:23', '2026-09-08 06:27:24', 'success', 'SFA-LED-PULL-21-20260908122658', NULL, NULL, NULL, NULL, '2026-09-08 06:27:24'),
(104, 'ledger', 22, 'pull_from_tally', '2026-09-08 06:27:23', '2026-09-08 06:27:24', 'success', 'SFA-LED-PULL-22-20260908122658', NULL, NULL, NULL, NULL, '2026-09-08 06:27:24'),
(105, 'dealer', NULL, 'pull_from_tally', '2026-09-08 06:30:12', '2026-09-08 06:30:12', 'success', 'SFA-PULL-dealer-20260908122955', NULL, NULL, NULL, NULL, '2026-09-08 06:30:12'),
(106, 'retailer', NULL, 'pull_from_tally', '2026-09-08 06:30:12', '2026-09-08 06:30:13', 'success', 'SFA-PULL-retailer-20260908122955', NULL, NULL, NULL, NULL, '2026-09-08 06:30:13'),
(107, 'product', NULL, 'pull_from_tally', '2026-09-08 06:30:12', '2026-09-08 06:30:13', 'success', 'SFA-PULL-product-20260908122955', NULL, NULL, NULL, NULL, '2026-09-08 06:30:13'),
(108, 'depot', NULL, 'pull_from_tally', '2026-09-08 06:30:12', '2026-09-08 06:30:13', 'success', 'SFA-PULL-depot-20260908122955', NULL, NULL, NULL, NULL, '2026-09-08 06:30:13'),
(109, 'ledger', 1, 'pull_from_tally', '2026-09-08 06:30:12', '2026-09-08 06:30:14', 'success', 'SFA-LED-PULL-1-20260908122955', NULL, NULL, NULL, NULL, '2026-09-08 06:30:14'),
(110, 'ledger', 2, 'pull_from_tally', '2026-09-08 06:30:12', '2026-09-08 06:30:14', 'success', 'SFA-LED-PULL-2-20260908122955', NULL, NULL, NULL, NULL, '2026-09-08 06:30:14'),
(111, 'ledger', 3, 'pull_from_tally', '2026-09-08 06:30:12', '2026-09-08 06:30:14', 'success', 'SFA-LED-PULL-3-20260908122955', NULL, NULL, NULL, NULL, '2026-09-08 06:30:14'),
(112, 'ledger', 4, 'pull_from_tally', '2026-09-08 06:30:12', '2026-09-08 06:30:15', 'success', 'SFA-LED-PULL-4-20260908122955', NULL, NULL, NULL, NULL, '2026-09-08 06:30:15'),
(113, 'ledger', 5, 'pull_from_tally', '2026-09-08 06:30:12', '2026-09-08 06:30:15', 'success', 'SFA-LED-PULL-5-20260908122955', NULL, NULL, NULL, NULL, '2026-09-08 06:30:15'),
(114, 'ledger', 14, 'pull_from_tally', '2026-09-08 06:30:12', '2026-09-08 06:30:15', 'success', 'SFA-LED-PULL-14-20260908122955', NULL, NULL, NULL, NULL, '2026-09-08 06:30:15'),
(115, 'ledger', 18, 'pull_from_tally', '2026-09-08 06:30:23', '2026-09-08 06:30:23', 'success', 'SFA-LED-PULL-18-20260908122955', NULL, NULL, NULL, NULL, '2026-09-08 06:30:23'),
(116, 'ledger', 19, 'pull_from_tally', '2026-09-08 06:30:23', '2026-09-08 06:30:23', 'success', 'SFA-LED-PULL-19-20260908122955', NULL, NULL, NULL, NULL, '2026-09-08 06:30:23'),
(117, 'ledger', 20, 'pull_from_tally', '2026-09-08 06:30:23', '2026-09-08 06:30:24', 'success', 'SFA-LED-PULL-20-20260908122955', NULL, NULL, NULL, NULL, '2026-09-08 06:30:24'),
(118, 'ledger', 21, 'pull_from_tally', '2026-09-08 06:30:23', '2026-09-08 06:30:24', 'success', 'SFA-LED-PULL-21-20260908122955', NULL, NULL, NULL, NULL, '2026-09-08 06:30:24'),
(119, 'ledger', 22, 'pull_from_tally', '2026-09-08 06:30:23', '2026-09-08 06:30:24', 'success', 'SFA-LED-PULL-22-20260908122955', NULL, NULL, NULL, NULL, '2026-09-08 06:30:24'),
(120, 'dealer', NULL, 'pull_from_tally', '2026-09-08 06:31:53', '2026-09-08 06:31:54', 'success', 'SFA-PULL-dealer-20260908123142', NULL, NULL, NULL, NULL, '2026-09-08 06:31:54'),
(121, 'retailer', NULL, 'pull_from_tally', '2026-09-08 06:31:53', '2026-09-08 06:31:54', 'success', 'SFA-PULL-retailer-20260908123142', NULL, NULL, NULL, NULL, '2026-09-08 06:31:54'),
(122, 'product', NULL, 'pull_from_tally', '2026-09-08 06:31:53', '2026-09-08 06:31:54', 'success', 'SFA-PULL-product-20260908123142', NULL, NULL, NULL, NULL, '2026-09-08 06:31:54'),
(123, 'depot', NULL, 'pull_from_tally', '2026-09-08 06:31:53', '2026-09-08 06:31:55', 'success', 'SFA-PULL-depot-20260908123142', NULL, NULL, NULL, NULL, '2026-09-08 06:31:55'),
(124, 'ledger', 1, 'pull_from_tally', '2026-09-08 06:31:53', '2026-09-08 06:31:55', 'success', 'SFA-LED-PULL-1-20260908123143', NULL, NULL, NULL, NULL, '2026-09-08 06:31:55'),
(125, 'ledger', 2, 'pull_from_tally', '2026-09-08 06:31:53', '2026-09-08 06:31:55', 'success', 'SFA-LED-PULL-2-20260908123143', NULL, NULL, NULL, NULL, '2026-09-08 06:31:55'),
(126, 'ledger', 3, 'pull_from_tally', '2026-09-08 06:31:53', '2026-09-08 06:31:56', 'success', 'SFA-LED-PULL-3-20260908123143', NULL, NULL, NULL, NULL, '2026-09-08 06:31:56'),
(127, 'ledger', 4, 'pull_from_tally', '2026-09-08 06:31:53', '2026-09-08 06:31:56', 'success', 'SFA-LED-PULL-4-20260908123143', NULL, NULL, NULL, NULL, '2026-09-08 06:31:56'),
(128, 'ledger', 5, 'pull_from_tally', '2026-09-08 06:31:53', '2026-09-08 06:31:57', 'success', 'SFA-LED-PULL-5-20260908123143', NULL, NULL, NULL, NULL, '2026-09-08 06:31:57'),
(129, 'ledger', 14, 'pull_from_tally', '2026-09-08 06:31:53', '2026-09-08 06:31:57', 'success', 'SFA-LED-PULL-14-20260908123143', NULL, NULL, NULL, NULL, '2026-09-08 06:31:57'),
(130, 'ledger', 18, 'pull_from_tally', '2026-09-08 06:32:12', '2026-09-08 06:32:13', 'success', 'SFA-LED-PULL-18-20260908123143', NULL, NULL, NULL, NULL, '2026-09-08 06:32:13'),
(131, 'ledger', 19, 'pull_from_tally', '2026-09-08 06:32:12', '2026-09-08 06:32:13', 'success', 'SFA-LED-PULL-19-20260908123143', NULL, NULL, NULL, NULL, '2026-09-08 06:32:13'),
(132, 'ledger', 20, 'pull_from_tally', '2026-09-08 06:32:12', '2026-09-08 06:32:13', 'success', 'SFA-LED-PULL-20-20260908123143', NULL, NULL, NULL, NULL, '2026-09-08 06:32:13'),
(133, 'ledger', 21, 'pull_from_tally', '2026-09-08 06:32:12', '2026-09-08 06:32:14', 'success', 'SFA-LED-PULL-21-20260908123143', NULL, NULL, NULL, NULL, '2026-09-08 06:32:14'),
(134, 'ledger', 22, 'pull_from_tally', '2026-09-08 06:32:12', '2026-09-08 06:32:14', 'success', 'SFA-LED-PULL-22-20260908123143', NULL, NULL, NULL, NULL, '2026-09-08 06:32:14'),
(135, 'ledger', 23, 'pull_from_tally', '2026-09-08 06:32:12', '2026-09-08 06:32:14', 'success', 'SFA-LED-PULL-23-20260908123143', NULL, NULL, NULL, NULL, '2026-09-08 06:32:14'),
(136, 'dealer', NULL, 'pull_from_tally', '2026-09-08 06:40:23', '2026-09-08 06:40:23', 'success', 'SFA-PULL-dealer-20260908124009', NULL, NULL, NULL, NULL, '2026-09-08 06:40:23'),
(137, 'depot', NULL, 'pull_from_tally', '2026-09-08 06:41:53', '2026-09-08 06:41:53', 'success', 'SFA-PULL-depot-20260908124132', NULL, NULL, NULL, NULL, '2026-09-08 06:41:53'),
(138, 'product', NULL, 'pull_from_tally', '2026-09-08 06:50:29', '2026-09-08 06:50:29', 'success', 'SFA-PULL-product-20260908125013', NULL, NULL, NULL, NULL, '2026-09-08 06:50:29'),
(139, 'dealer', NULL, 'pull_from_tally', '2026-09-08 06:58:59', '2026-09-08 06:58:59', 'success', 'SFA-PULL-dealer-20260908125835', NULL, NULL, NULL, NULL, '2026-09-08 06:58:59'),
(140, 'retailer', NULL, 'pull_from_tally', '2026-09-08 06:58:59', '2026-09-08 06:59:00', 'success', 'SFA-PULL-retailer-20260908125835', NULL, NULL, NULL, NULL, '2026-09-08 06:59:00'),
(141, 'product', NULL, 'pull_from_tally', '2026-09-08 06:58:59', '2026-09-08 06:59:00', 'success', 'SFA-PULL-product-20260908125835', NULL, NULL, NULL, NULL, '2026-09-08 06:59:00'),
(142, 'depot', NULL, 'pull_from_tally', '2026-09-08 06:58:59', '2026-09-08 06:59:00', 'success', 'SFA-PULL-depot-20260908125835', NULL, NULL, NULL, NULL, '2026-09-08 06:59:00'),
(143, 'ledger', 1, 'pull_from_tally', '2026-09-08 06:58:59', '2026-09-08 06:59:01', 'success', 'SFA-LED-PULL-1-20260908125835', NULL, NULL, NULL, NULL, '2026-09-08 06:59:01'),
(144, 'ledger', 2, 'pull_from_tally', '2026-09-08 06:58:59', '2026-09-08 06:59:01', 'success', 'SFA-LED-PULL-2-20260908125835', NULL, NULL, NULL, NULL, '2026-09-08 06:59:01'),
(145, 'ledger', 3, 'pull_from_tally', '2026-09-08 06:58:59', '2026-09-08 06:59:02', 'success', 'SFA-LED-PULL-3-20260908125835', NULL, NULL, NULL, NULL, '2026-09-08 06:59:02'),
(146, 'ledger', 4, 'pull_from_tally', '2026-09-08 06:58:59', '2026-09-08 06:59:02', 'success', 'SFA-LED-PULL-4-20260908125835', NULL, NULL, NULL, NULL, '2026-09-08 06:59:02'),
(147, 'ledger', 5, 'pull_from_tally', '2026-09-08 06:58:59', '2026-09-08 06:59:02', 'success', 'SFA-LED-PULL-5-20260908125835', NULL, NULL, NULL, NULL, '2026-09-08 06:59:02'),
(148, 'ledger', 14, 'pull_from_tally', '2026-09-08 06:58:59', '2026-09-08 06:59:03', 'success', 'SFA-LED-PULL-14-20260908125835', NULL, NULL, NULL, NULL, '2026-09-08 06:59:03'),
(149, 'ledger', 18, 'pull_from_tally', '2026-09-08 06:59:29', '2026-09-08 06:59:29', 'success', 'SFA-LED-PULL-18-20260908125835', NULL, NULL, NULL, NULL, '2026-09-08 06:59:29'),
(150, 'ledger', 19, 'pull_from_tally', '2026-09-08 06:59:29', '2026-09-08 06:59:30', 'success', 'SFA-LED-PULL-19-20260908125835', NULL, NULL, NULL, NULL, '2026-09-08 06:59:30'),
(151, 'ledger', 20, 'pull_from_tally', '2026-09-08 06:59:29', '2026-09-08 06:59:30', 'success', 'SFA-LED-PULL-20-20260908125835', NULL, NULL, NULL, NULL, '2026-09-08 06:59:30'),
(152, 'ledger', 21, 'pull_from_tally', '2026-09-08 06:59:29', '2026-09-08 06:59:31', 'success', 'SFA-LED-PULL-21-20260908125835', NULL, NULL, NULL, NULL, '2026-09-08 06:59:31'),
(153, 'ledger', 22, 'pull_from_tally', '2026-09-08 06:59:29', '2026-09-08 06:59:31', 'success', 'SFA-LED-PULL-22-20260908125835', NULL, NULL, NULL, NULL, '2026-09-08 06:59:31'),
(154, 'dealer', NULL, 'pull_from_tally', '2026-09-08 07:29:02', '2026-09-08 07:29:03', 'success', 'SFA-PULL-dealer-20260908130120', NULL, NULL, NULL, NULL, '2026-09-08 07:29:03'),
(155, 'retailer', NULL, 'pull_from_tally', '2026-09-08 07:29:02', '2026-09-08 07:29:03', 'success', 'SFA-PULL-retailer-20260908130120', NULL, NULL, NULL, NULL, '2026-09-08 07:29:03'),
(156, 'product', NULL, 'pull_from_tally', '2026-09-08 07:29:02', '2026-09-08 07:29:04', 'success', 'SFA-PULL-product-20260908130120', NULL, NULL, NULL, NULL, '2026-09-08 07:29:04'),
(157, 'depot', NULL, 'pull_from_tally', '2026-09-08 07:29:02', '2026-09-08 07:29:04', 'success', 'SFA-PULL-depot-20260908130120', NULL, NULL, NULL, NULL, '2026-09-08 07:29:04'),
(158, 'ledger', 1, 'pull_from_tally', '2026-09-08 07:29:02', '2026-09-08 07:29:05', 'success', 'SFA-LED-PULL-1-20260908130120', NULL, NULL, NULL, NULL, '2026-09-08 07:29:05'),
(159, 'ledger', 2, 'pull_from_tally', '2026-09-08 07:29:02', '2026-09-08 07:29:05', 'success', 'SFA-LED-PULL-2-20260908130120', NULL, NULL, NULL, NULL, '2026-09-08 07:29:05'),
(160, 'ledger', 3, 'pull_from_tally', '2026-09-08 07:29:02', '2026-09-08 07:29:06', 'success', 'SFA-LED-PULL-3-20260908130120', NULL, NULL, NULL, NULL, '2026-09-08 07:29:06'),
(161, 'ledger', 4, 'pull_from_tally', '2026-09-08 07:29:02', '2026-09-08 07:29:06', 'success', 'SFA-LED-PULL-4-20260908130120', NULL, NULL, NULL, NULL, '2026-09-08 07:29:06'),
(162, 'ledger', 5, 'pull_from_tally', '2026-09-08 07:29:02', '2026-09-08 07:29:07', 'success', 'SFA-LED-PULL-5-20260908130120', NULL, NULL, NULL, NULL, '2026-09-08 07:29:07'),
(163, 'ledger', 14, 'pull_from_tally', '2026-09-08 07:29:02', '2026-09-08 07:29:07', 'success', 'SFA-LED-PULL-14-20260908130120', NULL, NULL, NULL, NULL, '2026-09-08 07:29:07'),
(164, 'ledger', 18, 'pull_from_tally', '2026-09-08 07:29:32', '2026-09-08 07:29:32', 'success', 'SFA-LED-PULL-18-20260908130120', NULL, NULL, NULL, NULL, '2026-09-08 07:29:32'),
(165, 'ledger', 19, 'pull_from_tally', '2026-09-08 07:29:32', '2026-09-08 07:29:33', 'success', 'SFA-LED-PULL-19-20260908130120', NULL, NULL, NULL, NULL, '2026-09-08 07:29:33'),
(166, 'ledger', 20, 'pull_from_tally', '2026-09-08 07:29:32', '2026-09-08 07:29:33', 'success', 'SFA-LED-PULL-20-20260908130120', NULL, NULL, NULL, NULL, '2026-09-08 07:29:33'),
(167, 'ledger', 21, 'pull_from_tally', '2026-09-08 07:29:32', '2026-09-08 07:29:33', 'success', 'SFA-LED-PULL-21-20260908130120', NULL, NULL, NULL, NULL, '2026-09-08 07:29:33'),
(168, 'ledger', 22, 'pull_from_tally', '2026-09-08 07:29:32', '2026-09-08 07:29:34', 'success', 'SFA-LED-PULL-22-20260908130120', NULL, NULL, NULL, NULL, '2026-09-08 07:29:34'),
(169, 'ledger', 24, 'pull_from_tally', '2026-09-08 07:29:32', '2026-09-08 07:29:34', 'success', 'SFA-LED-PULL-24-20260908130120', NULL, NULL, NULL, NULL, '2026-09-08 07:29:34'),
(170, 'dealer', NULL, 'pull_from_tally', '2026-09-08 07:30:02', '2026-09-08 07:30:02', 'success', 'SFA-PULL-dealer-20260908132953', NULL, NULL, NULL, NULL, '2026-09-08 07:30:02'),
(171, 'retailer', NULL, 'pull_from_tally', '2026-09-08 07:30:02', '2026-09-08 07:30:03', 'success', 'SFA-PULL-retailer-20260908132953', NULL, NULL, NULL, NULL, '2026-09-08 07:30:03'),
(172, 'product', NULL, 'pull_from_tally', '2026-09-08 07:30:02', '2026-09-08 07:30:03', 'success', 'SFA-PULL-product-20260908132953', NULL, NULL, NULL, NULL, '2026-09-08 07:30:03'),
(173, 'depot', NULL, 'pull_from_tally', '2026-09-08 07:30:02', '2026-09-08 07:30:03', 'success', 'SFA-PULL-depot-20260908132953', NULL, NULL, NULL, NULL, '2026-09-08 07:30:03'),
(174, 'ledger', 1, 'pull_from_tally', '2026-09-08 07:30:02', '2026-09-08 07:30:04', 'success', 'SFA-LED-PULL-1-20260908132953', NULL, NULL, NULL, NULL, '2026-09-08 07:30:04'),
(175, 'ledger', 2, 'pull_from_tally', '2026-09-08 07:30:02', '2026-09-08 07:30:04', 'success', 'SFA-LED-PULL-2-20260908132953', NULL, NULL, NULL, NULL, '2026-09-08 07:30:04'),
(176, 'ledger', 3, 'pull_from_tally', '2026-09-08 07:30:02', '2026-09-08 07:30:04', 'success', 'SFA-LED-PULL-3-20260908132953', NULL, NULL, NULL, NULL, '2026-09-08 07:30:04'),
(177, 'ledger', 4, 'pull_from_tally', '2026-09-08 07:30:02', '2026-09-08 07:30:05', 'success', 'SFA-LED-PULL-4-20260908132953', NULL, NULL, NULL, NULL, '2026-09-08 07:30:05'),
(178, 'ledger', 5, 'pull_from_tally', '2026-09-08 07:30:02', '2026-09-08 07:30:05', 'success', 'SFA-LED-PULL-5-20260908132953', NULL, NULL, NULL, NULL, '2026-09-08 07:30:05'),
(179, 'ledger', 14, 'pull_from_tally', '2026-09-08 07:30:02', '2026-09-08 07:30:05', 'success', 'SFA-LED-PULL-14-20260908132953', NULL, NULL, NULL, NULL, '2026-09-08 07:30:05'),
(180, 'ledger', 18, 'pull_from_tally', '2026-09-08 07:30:32', '2026-09-08 07:30:32', 'success', 'SFA-LED-PULL-18-20260908132953', NULL, NULL, NULL, NULL, '2026-09-08 07:30:32'),
(181, 'ledger', 19, 'pull_from_tally', '2026-09-08 07:30:32', '2026-09-08 07:30:33', 'success', 'SFA-LED-PULL-19-20260908132953', NULL, NULL, NULL, NULL, '2026-09-08 07:30:33'),
(182, 'dealer', 28, 'push_to_tally', '2026-09-08 07:30:32', '2026-09-08 07:30:33', 'success', 'SFA-MPUSH-dealer-28', 'd530416d-350a-4df9-bc3c-06943927816a-0000010f', NULL, NULL, NULL, '2026-09-08 07:30:33'),
(183, 'dealer', NULL, 'pull_from_tally', '2026-09-08 07:32:32', '2026-09-08 07:32:32', 'success', 'SFA-PULL-dealer-20260908133231', NULL, NULL, NULL, NULL, '2026-09-08 07:32:32'),
(184, 'retailer', NULL, 'pull_from_tally', '2026-09-08 07:32:32', '2026-09-08 07:32:33', 'success', 'SFA-PULL-retailer-20260908133231', NULL, NULL, NULL, NULL, '2026-09-08 07:32:33'),
(185, 'product', NULL, 'pull_from_tally', '2026-09-08 07:32:32', '2026-09-08 07:32:33', 'success', 'SFA-PULL-product-20260908133231', NULL, NULL, NULL, NULL, '2026-09-08 07:32:33'),
(186, 'depot', NULL, 'pull_from_tally', '2026-09-08 07:32:32', '2026-09-08 07:32:33', 'success', 'SFA-PULL-depot-20260908133231', NULL, NULL, NULL, NULL, '2026-09-08 07:32:33'),
(187, 'ledger', 1, 'pull_from_tally', '2026-09-08 07:32:32', '2026-09-08 07:32:34', 'success', 'SFA-LED-PULL-1-20260908133231', NULL, NULL, NULL, NULL, '2026-09-08 07:32:34'),
(188, 'ledger', 2, 'pull_from_tally', '2026-09-08 07:32:32', '2026-09-08 07:32:34', 'success', 'SFA-LED-PULL-2-20260908133231', NULL, NULL, NULL, NULL, '2026-09-08 07:32:34'),
(189, 'ledger', 3, 'pull_from_tally', '2026-09-08 07:32:32', '2026-09-08 07:32:34', 'success', 'SFA-LED-PULL-3-20260908133231', NULL, NULL, NULL, NULL, '2026-09-08 07:32:34'),
(190, 'ledger', 4, 'pull_from_tally', '2026-09-08 07:32:32', '2026-09-08 07:32:35', 'success', 'SFA-LED-PULL-4-20260908133231', NULL, NULL, NULL, NULL, '2026-09-08 07:32:35'),
(191, 'ledger', 5, 'pull_from_tally', '2026-09-08 07:32:32', '2026-09-08 07:32:35', 'success', 'SFA-LED-PULL-5-20260908133231', NULL, NULL, NULL, NULL, '2026-09-08 07:32:35'),
(192, 'ledger', 14, 'pull_from_tally', '2026-09-08 07:32:32', '2026-09-08 07:32:35', 'success', 'SFA-LED-PULL-14-20260908133231', NULL, NULL, NULL, NULL, '2026-09-08 07:32:35'),
(193, 'ledger', 18, 'pull_from_tally', '2026-09-08 07:33:02', '2026-09-08 07:33:02', 'success', 'SFA-LED-PULL-18-20260908133231', NULL, NULL, NULL, NULL, '2026-09-08 07:33:02'),
(194, 'ledger', 19, 'pull_from_tally', '2026-09-08 07:33:02', '2026-09-08 07:33:03', 'success', 'SFA-LED-PULL-19-20260908133231', NULL, NULL, NULL, NULL, '2026-09-08 07:33:03'),
(195, 'dealer', 31, 'push_to_tally', '2026-09-08 07:33:02', '2026-09-08 07:33:03', 'success', 'SFA-MPUSH-dealer-31', 'd530416d-350a-4df9-bc3c-06943927816a-00000111', NULL, NULL, NULL, '2026-09-08 07:33:03'),
(196, 'dealer', NULL, 'pull_from_tally', '2026-09-08 08:09:32', '2026-09-08 08:09:33', 'success', 'SFA-PULL-dealer-20260908140930', NULL, NULL, NULL, NULL, '2026-09-08 08:09:33'),
(197, 'retailer', NULL, 'pull_from_tally', '2026-09-08 08:09:32', '2026-09-08 08:09:33', 'success', 'SFA-PULL-retailer-20260908140930', NULL, NULL, NULL, NULL, '2026-09-08 08:09:33'),
(198, 'product', NULL, 'pull_from_tally', '2026-09-08 08:09:32', '2026-09-08 08:09:34', 'success', 'SFA-PULL-product-20260908140930', NULL, NULL, NULL, NULL, '2026-09-08 08:09:34'),
(199, 'depot', NULL, 'pull_from_tally', '2026-09-08 08:09:32', '2026-09-08 08:09:34', 'success', 'SFA-PULL-depot-20260908140930', NULL, NULL, NULL, NULL, '2026-09-08 08:09:34'),
(200, 'ledger', 1, 'pull_from_tally', '2026-09-08 08:09:32', '2026-09-08 08:09:35', 'success', 'SFA-LED-PULL-1-20260908140930', NULL, NULL, NULL, NULL, '2026-09-08 08:09:35'),
(201, 'ledger', 2, 'pull_from_tally', '2026-09-08 08:09:32', '2026-09-08 08:09:35', 'success', 'SFA-LED-PULL-2-20260908140930', NULL, NULL, NULL, NULL, '2026-09-08 08:09:35'),
(202, 'ledger', 3, 'pull_from_tally', '2026-09-08 08:09:32', '2026-09-08 08:09:36', 'success', 'SFA-LED-PULL-3-20260908140930', NULL, NULL, NULL, NULL, '2026-09-08 08:09:36'),
(203, 'ledger', 4, 'pull_from_tally', '2026-09-08 08:09:32', '2026-09-08 08:09:36', 'success', 'SFA-LED-PULL-4-20260908140930', NULL, NULL, NULL, NULL, '2026-09-08 08:09:36'),
(204, 'ledger', 5, 'pull_from_tally', '2026-09-08 08:09:32', '2026-09-08 08:09:36', 'success', 'SFA-LED-PULL-5-20260908140930', NULL, NULL, NULL, NULL, '2026-09-08 08:09:36'),
(205, 'ledger', 14, 'pull_from_tally', '2026-09-08 08:09:32', '2026-09-08 08:09:37', 'success', 'SFA-LED-PULL-14-20260908140930', NULL, NULL, NULL, NULL, '2026-09-08 08:09:37'),
(206, 'ledger', 18, 'pull_from_tally', '2026-09-08 08:10:03', '2026-09-08 08:10:03', 'success', 'SFA-LED-PULL-18-20260908140930', NULL, NULL, NULL, NULL, '2026-09-08 08:10:03'),
(207, 'ledger', 19, 'pull_from_tally', '2026-09-08 08:10:03', '2026-09-08 08:10:04', 'success', 'SFA-LED-PULL-19-20260908140930', NULL, NULL, NULL, NULL, '2026-09-08 08:10:04'),
(208, 'dealer', 33, 'push_to_tally', '2026-09-08 08:10:03', '2026-09-08 08:10:04', 'success', 'SFA-MPUSH-dealer-33', 'd530416d-350a-4df9-bc3c-06943927816a-00000113', NULL, NULL, NULL, '2026-09-08 08:10:04'),
(209, 'dealer', NULL, 'pull_from_tally', '2026-09-21 11:12:08', '2026-09-21 11:12:09', 'success', 'SFA-PULL-dealer-20260921170524', NULL, NULL, NULL, NULL, '2026-09-21 11:12:09'),
(210, 'retailer', NULL, 'pull_from_tally', '2026-09-21 11:12:08', '2026-09-21 11:12:09', 'success', 'SFA-PULL-retailer-20260921170524', NULL, NULL, NULL, NULL, '2026-09-21 11:12:09'),
(211, 'product', NULL, 'pull_from_tally', '2026-09-21 11:12:08', '2026-09-21 11:12:09', 'success', 'SFA-PULL-product-20260921170524', NULL, NULL, NULL, NULL, '2026-09-21 11:12:10'),
(212, 'depot', NULL, 'pull_from_tally', '2026-09-21 11:12:08', '2026-09-21 11:12:10', 'success', 'SFA-PULL-depot-20260921170524', NULL, NULL, NULL, NULL, '2026-09-21 11:12:10'),
(213, 'ledger', 1, 'pull_from_tally', '2026-09-21 11:12:08', '2026-09-21 11:12:10', 'success', 'SFA-LED-PULL-1-20260921170524', NULL, NULL, NULL, NULL, '2026-09-21 11:12:10'),
(214, 'ledger', 2, 'pull_from_tally', '2026-09-21 11:12:08', '2026-09-21 11:12:11', 'success', 'SFA-LED-PULL-2-20260921170524', NULL, NULL, NULL, NULL, '2026-09-21 11:12:11'),
(215, 'ledger', 3, 'pull_from_tally', '2026-09-21 11:12:08', '2026-09-21 11:12:11', 'success', 'SFA-LED-PULL-3-20260921170524', NULL, NULL, NULL, NULL, '2026-09-21 11:12:11'),
(216, 'ledger', 4, 'pull_from_tally', '2026-09-21 11:12:08', '2026-09-21 11:12:11', 'success', 'SFA-LED-PULL-4-20260921170524', NULL, NULL, NULL, NULL, '2026-09-21 11:12:12'),
(217, 'ledger', 5, 'pull_from_tally', '2026-09-21 11:12:08', '2026-09-21 11:12:12', 'success', 'SFA-LED-PULL-5-20260921170524', NULL, NULL, NULL, NULL, '2026-09-21 11:12:12'),
(218, 'ledger', 14, 'pull_from_tally', '2026-09-21 11:12:08', '2026-09-21 11:12:12', 'success', 'SFA-LED-PULL-14-20260921170524', NULL, NULL, NULL, NULL, '2026-09-21 11:12:12'),
(219, 'ledger', 18, 'pull_from_tally', '2026-09-21 11:12:38', '2026-09-21 11:12:39', 'success', 'SFA-LED-PULL-18-20260921170524', NULL, NULL, NULL, NULL, '2026-09-21 11:12:39'),
(220, 'ledger', 19, 'pull_from_tally', '2026-09-21 11:12:38', '2026-09-21 11:12:39', 'success', 'SFA-LED-PULL-19-20260921170524', NULL, NULL, NULL, NULL, '2026-09-21 11:12:39'),
(221, 'ledger', 33, 'pull_from_tally', '2026-09-21 11:12:38', '2026-09-21 11:12:40', 'success', 'SFA-LED-PULL-33-20260921170524', NULL, NULL, NULL, NULL, '2026-09-21 11:12:40'),
(222, 'ledger', 34, 'pull_from_tally', '2026-09-21 11:12:38', '2026-09-21 11:12:40', 'success', 'SFA-LED-PULL-34-20260921170524', NULL, NULL, NULL, NULL, '2026-09-21 11:12:40'),
(223, 'dealer', NULL, 'pull_from_tally', '2026-09-21 11:13:08', '2026-09-21 11:13:09', 'success', 'SFA-PULL-dealer-20260921171245', NULL, NULL, NULL, NULL, '2026-09-21 11:13:09'),
(224, 'retailer', NULL, 'pull_from_tally', '2026-09-21 11:13:08', '2026-09-21 11:13:09', 'success', 'SFA-PULL-retailer-20260921171245', NULL, NULL, NULL, NULL, '2026-09-21 11:13:09'),
(225, 'product', NULL, 'pull_from_tally', '2026-09-21 11:13:08', '2026-09-21 11:13:10', 'success', 'SFA-PULL-product-20260921171245', NULL, NULL, NULL, NULL, '2026-09-21 11:13:10'),
(226, 'depot', NULL, 'pull_from_tally', '2026-09-21 11:13:08', '2026-09-21 11:13:10', 'success', 'SFA-PULL-depot-20260921171245', NULL, NULL, NULL, NULL, '2026-09-21 11:13:10'),
(227, 'ledger', 1, 'pull_from_tally', '2026-09-21 11:13:08', '2026-09-21 11:13:10', 'success', 'SFA-LED-PULL-1-20260921171245', NULL, NULL, NULL, NULL, '2026-09-21 11:13:10'),
(228, 'ledger', 2, 'pull_from_tally', '2026-09-21 11:13:08', '2026-09-21 11:13:11', 'success', 'SFA-LED-PULL-2-20260921171245', NULL, NULL, NULL, NULL, '2026-09-21 11:13:11'),
(229, 'ledger', 3, 'pull_from_tally', '2026-09-21 11:13:08', '2026-09-21 11:13:11', 'success', 'SFA-LED-PULL-3-20260921171245', NULL, NULL, NULL, NULL, '2026-09-21 11:13:11'),
(230, 'ledger', 4, 'pull_from_tally', '2026-09-21 11:13:08', '2026-09-21 11:13:12', 'success', 'SFA-LED-PULL-4-20260921171245', NULL, NULL, NULL, NULL, '2026-09-21 11:13:12'),
(231, 'ledger', 5, 'pull_from_tally', '2026-09-21 11:13:08', '2026-09-21 11:13:12', 'success', 'SFA-LED-PULL-5-20260921171245', NULL, NULL, NULL, NULL, '2026-09-21 11:13:12'),
(232, 'ledger', 14, 'pull_from_tally', '2026-09-21 11:13:08', '2026-09-21 11:13:12', 'success', 'SFA-LED-PULL-14-20260921171245', NULL, NULL, NULL, NULL, '2026-09-21 11:13:12'),
(233, 'ledger', 18, 'pull_from_tally', '2026-09-21 11:13:38', '2026-09-21 11:13:39', 'success', 'SFA-LED-PULL-18-20260921171245', NULL, NULL, NULL, NULL, '2026-09-21 11:13:39'),
(234, 'ledger', 19, 'pull_from_tally', '2026-09-21 11:13:38', '2026-09-21 11:13:39', 'success', 'SFA-LED-PULL-19-20260921171245', NULL, NULL, NULL, NULL, '2026-09-21 11:13:39'),
(235, 'ledger', 33, 'pull_from_tally', '2026-09-21 11:13:38', '2026-09-21 11:13:39', 'success', 'SFA-LED-PULL-33-20260921171245', NULL, NULL, NULL, NULL, '2026-09-21 11:13:40'),
(236, 'ledger', 34, 'pull_from_tally', '2026-09-21 11:13:38', '2026-09-21 11:13:40', 'success', 'SFA-LED-PULL-34-20260921171245', NULL, NULL, NULL, NULL, '2026-09-21 11:13:40');

-- --------------------------------------------------------

--
-- Table structure for table `sync_queues`
--

CREATE TABLE `sync_queues` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `entity_type` varchar(255) NOT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `direction` varchar(255) NOT NULL,
  `external_reference` varchar(255) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `attempt_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `last_attempt_at` timestamp NULL DEFAULT NULL,
  `next_attempt_at` timestamp NULL DEFAULT NULL,
  `response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response`)),
  `error_code` varchar(255) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sync_queues`
--

INSERT INTO `sync_queues` (`id`, `entity_type`, `entity_id`, `direction`, `external_reference`, `payload`, `status`, `attempt_count`, `last_attempt_at`, `next_attempt_at`, `response`, `error_code`, `error_message`, `created_at`, `updated_at`, `completed_at`) VALUES
(21, 'dealer', NULL, 'pull_from_tally', 'SFA-PULL-dealer-20260908095304', '[]', 'success', 1, '2026-09-08 03:53:12', NULL, '{\"rows\":[{\"name\":\"Dealer 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\"},{\"name\":\"Dealer 2\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\"}],\"group\":\"Sundry Debtors\",\"applied\":{\"linked\":0,\"already\":0,\"imported\":[\"Dealer 1\",\"Dealer 2\"],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 03:53:04', '2026-09-08 03:53:13', '2026-09-08 03:53:13'),
(22, 'retailer', NULL, 'pull_from_tally', 'SFA-PULL-retailer-20260908095304', '[]', 'success', 1, '2026-09-08 03:53:12', NULL, '{\"rows\":[],\"skipped\":\"TALLY_RETAILER_LEDGER_GROUP is not set to a group of its own, so retailer ledgers cannot be told apart from dealer ledgers. Set it to the group the customer files retailers under (it must differ from the dealers\' group).\",\"applied\":{\"linked\":0,\"already\":0,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 03:53:04', '2026-09-08 03:53:13', '2026-09-08 03:53:13'),
(23, 'product', NULL, 'pull_from_tally', 'SFA-PULL-product-20260908095304', '[]', 'success', 1, '2026-09-08 03:53:12', NULL, '{\"rows\":[{\"name\":\"Pump Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d2\"},{\"name\":\"TV Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d3\"}],\"applied\":{\"linked\":0,\"already\":0,\"imported\":[\"Pump Model 1\",\"TV Model 1\"],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 03:53:04', '2026-09-08 03:53:14', '2026-09-08 03:53:14'),
(24, 'depot', NULL, 'pull_from_tally', 'SFA-PULL-depot-20260908095304', '[]', 'success', 1, '2026-09-08 03:53:12', NULL, '{\"rows\":[{\"name\":\"Main Location\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000063\"},{\"name\":\"Warehouse\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d4\"}],\"applied\":{\"linked\":0,\"already\":0,\"imported\":[\"Main Location\",\"Warehouse\"],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 03:53:04', '2026-09-08 03:53:14', '2026-09-08 03:53:14'),
(26, 'depot', 1, 'push_to_tally', 'SFA-MPUSH-depot-1', '{\"name\":\"Main Depot\",\"address\":null}', 'cancelled', 1, '2026-09-08 04:33:54', NULL, '{\"RESPONSE\":{\"LINEERROR\":\"Godown \'Primary\' does not exist!\",\"CREATED\":0,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":1}}', 'TALLY_INVALID_VOUCHER', 'Tally did not create \"Main Depot\": Godown \'Primary\' does not exist! [Superseded: Main Depot was later linked to the existing Tally godown by a Tally to SFA pull. Both failures remain in Sync Logs.]', '2026-09-08 04:33:45', '2026-09-08 05:01:35', '2026-09-08 05:01:35'),
(27, 'depot', NULL, 'pull_from_tally', 'SFA-PULL-depot-20260908103642', '[]', 'success', 1, '2026-09-08 04:36:50', NULL, '{\"rows\":[{\"name\":\"Main Depot\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000da\"},{\"name\":\"Main Location\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000063\"},{\"name\":\"Warehouse\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d4\"}],\"applied\":{\"linked\":1,\"already\":2,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 04:36:42', '2026-09-08 04:36:51', '2026-09-08 04:36:51'),
(28, 'dealer', 1, 'push_to_tally', 'SFA-MPUSH-dealer-1', '{\"name\":\"Savar Pump House\",\"group\":\"Sundry Debtors\",\"phone\":\"01821404477\",\"email\":\"savarpump@gmail.com\",\"address\":\"Savar Bazar Road, Savar, Dhaka\"}', 'success', 1, '2026-09-08 04:36:51', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:36:42', '2026-09-08 04:36:51', '2026-09-08 04:36:51'),
(29, 'dealer', 2, 'push_to_tally', 'SFA-MPUSH-dealer-2', '{\"name\":\"Keraniganj Hardware & Motors\",\"group\":\"Sundry Debtors\",\"phone\":\"01953131136\",\"email\":null,\"address\":\"Aganagar, Keraniganj, Dhaka\"}', 'success', 1, '2026-09-08 04:57:49', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:57:49', '2026-09-08 04:57:49'),
(30, 'dealer', 3, 'push_to_tally', 'SFA-MPUSH-dealer-3', '{\"name\":\"Dhamrai Water Solutions\",\"group\":\"Sundry Debtors\",\"phone\":\"01997886631\",\"email\":null,\"address\":\"Dhamrai Bus Stand, Dhamrai, Dhaka\"}', 'success', 1, '2026-09-08 04:57:49', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:57:50', '2026-09-08 04:57:50'),
(31, 'dealer', 4, 'push_to_tally', 'SFA-MPUSH-dealer-4', '{\"name\":\"Gazi Appliance Corner\",\"group\":\"Sundry Debtors\",\"phone\":\"01707842562\",\"email\":null,\"address\":\"Savar New Market, Savar, Dhaka\"}', 'success', 1, '2026-09-08 04:57:49', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:57:50', '2026-09-08 04:57:50'),
(32, 'dealer', 5, 'push_to_tally', 'SFA-MPUSH-dealer-5', '{\"name\":\"Buriganga Distribution House\",\"group\":\"Sundry Debtors\",\"phone\":\"01852953026\",\"email\":null,\"address\":\"Zinzira, Keraniganj, Dhaka\"}', 'success', 1, '2026-09-08 04:57:49', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:57:50', '2026-09-08 04:57:50'),
(33, 'dealer', 14, 'push_to_tally', 'SFA-MPUSH-dealer-14', '{\"name\":\"Tangail Hardware\",\"group\":\"Sundry Debtors\",\"phone\":\"01575829863\",\"email\":\"gerard.wiegand@balistreri.com\",\"address\":\"88225 Lynch Brooks Suite 251\\nNew Jody, CA 76381\"}', 'success', 1, '2026-09-08 04:57:49', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:57:51', '2026-09-08 04:57:51'),
(34, 'product', 1, 'push_to_tally', 'SFA-MPUSH-product-1', '{\"name\":\"Gazi Self-Priming Jet Pump\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:49', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:57:51', '2026-09-08 04:57:51'),
(35, 'product', 2, 'push_to_tally', 'SFA-MPUSH-product-2', '{\"name\":\"Gazi Standardized Centrifugal Pump\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:49', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:57:52', '2026-09-08 04:57:52'),
(36, 'product', 3, 'push_to_tally', 'SFA-MPUSH-product-3', '{\"name\":\"Pentax Centrifugal Pump\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:49', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:57:52', '2026-09-08 04:57:52'),
(37, 'product', 4, 'push_to_tally', 'SFA-MPUSH-product-4', '{\"name\":\"Pentax Submersible Pump\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:49', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:57:53', '2026-09-08 04:57:53'),
(38, 'product', 5, 'push_to_tally', 'SFA-MPUSH-product-5', '{\"name\":\"Eifel EA Series Pump\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:49', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:57:53', '2026-09-08 04:57:53'),
(39, 'product', 6, 'push_to_tally', 'SFA-MPUSH-product-6', '{\"name\":\"Eifel EAD Series Pump\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:54', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:57:54', '2026-09-08 04:57:54'),
(40, 'product', 7, 'push_to_tally', 'SFA-MPUSH-product-7', '{\"name\":\"CNP CDLF Series Pump\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:54', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:57:55', '2026-09-08 04:57:55'),
(41, 'product', 8, 'push_to_tally', 'SFA-MPUSH-product-8', '{\"name\":\"CNP SZ Series Pump\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:54', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:57:55', '2026-09-08 04:57:55'),
(42, 'product', 9, 'push_to_tally', 'SFA-MPUSH-product-9', '{\"name\":\"Gazi Fire Fighting Pump Complete Set\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:54', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:57:56', '2026-09-08 04:57:56'),
(43, 'product', 10, 'push_to_tally', 'SFA-MPUSH-product-10', '{\"name\":\"Gazi Motors YC Series\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:54', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:57:56', '2026-09-08 04:57:56'),
(44, 'product', 11, 'push_to_tally', 'SFA-MPUSH-product-11', '{\"name\":\"Gazi Gas Stove (Industrial Line)\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:54', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:57:57', '2026-09-08 04:57:57'),
(45, 'product', 12, 'push_to_tally', 'SFA-MPUSH-product-12', '{\"name\":\"Gazi Tubewell\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:54', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:57:57', '2026-09-08 04:57:57'),
(46, 'product', 13, 'push_to_tally', 'SFA-MPUSH-product-13', '{\"name\":\"TG-206 - Gazi Smiss Gas Stove\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:54', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:57:58', '2026-09-08 04:57:58'),
(47, 'product', 14, 'push_to_tally', 'SFA-MPUSH-product-14', '{\"name\":\"GST-102C - Gazi Gas Stove\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:54', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:57:58', '2026-09-08 04:57:58'),
(48, 'product', 15, 'push_to_tally', 'SFA-MPUSH-product-15', '{\"name\":\"EG-732S - Gazi Smiss Gas Stove\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:54', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:57:58', '2026-09-08 04:57:58'),
(49, 'product', 16, 'push_to_tally', 'SFA-MPUSH-product-16', '{\"name\":\"TG-213S - Gazi Smiss Gas Stove\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:59', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:57:59', '2026-09-08 04:57:59'),
(50, 'product', 17, 'push_to_tally', 'SFA-MPUSH-product-17', '{\"name\":\"GH-8204M - Gazi Smiss Gas Stove\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:59', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:58:00', '2026-09-08 04:58:00'),
(51, 'product', 18, 'push_to_tally', 'SFA-MPUSH-product-18', '{\"name\":\"HY-716BV - Gazi Smiss Kitchen Hood\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:59', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:58:00', '2026-09-08 04:58:00'),
(52, 'product', 19, 'push_to_tally', 'SFA-MPUSH-product-19', '{\"name\":\"HY-712BT - Gazi Smiss Kitchen Hood\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:59', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:58:01', '2026-09-08 04:58:01'),
(53, 'product', 20, 'push_to_tally', 'SFA-MPUSH-product-20', '{\"name\":\"EG-750S - Gazi Smiss Kitchen Hood\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:59', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:58:01', '2026-09-08 04:58:01'),
(54, 'product', 21, 'push_to_tally', 'SFA-MPUSH-product-21', '{\"name\":\"HY-736BV - Gazi Smiss Kitchen Hood\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:59', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:58:01', '2026-09-08 04:58:01'),
(55, 'product', 22, 'push_to_tally', 'SFA-MPUSH-product-22', '{\"name\":\"HY-729CP - Gazi Smiss Kitchen Hood\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:59', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:58:02', '2026-09-08 04:58:02'),
(56, 'product', 23, 'push_to_tally', 'SFA-MPUSH-product-23', '{\"name\":\"IF-HL01 - Gazi Smiss Infrared Cooker\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:59', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:58:02', '2026-09-08 04:58:02'),
(57, 'product', 24, 'push_to_tally', 'SFA-MPUSH-product-24', '{\"name\":\"A-40G - Gazi Smiss Infrared Cooker\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:59', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:58:03', '2026-09-08 04:58:03'),
(58, 'product', 25, 'push_to_tally', 'SFA-MPUSH-product-25', '{\"name\":\"A-25S - Gazi Smiss Induction Cooker\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:57:59', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:58:03', '2026-09-08 04:58:03'),
(59, 'product', 26, 'push_to_tally', 'SFA-MPUSH-product-26', '{\"name\":\"A-37G - Gazi Smiss Infrared Cooker\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:58:03', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:58:04', '2026-09-08 04:58:04'),
(60, 'product', 27, 'push_to_tally', 'SFA-MPUSH-product-27', '{\"name\":\"E-720B - Gazi Smiss Induction & Infrared Cooker\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:58:03', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:58:04', '2026-09-08 04:58:04'),
(61, 'product', 28, 'push_to_tally', 'SFA-MPUSH-product-28', '{\"name\":\"A-01 - Gazi Smiss Infrared Cooker\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:58:03', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:58:05', '2026-09-08 04:58:05'),
(62, 'product', 29, 'push_to_tally', 'SFA-MPUSH-product-29', '{\"name\":\"GEO-03 - Gazi Smiss Electric Oven 30 Liter\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:58:03', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:58:05', '2026-09-08 04:58:05'),
(63, 'product', 30, 'push_to_tally', 'SFA-MPUSH-product-30', '{\"name\":\"GEO-04 - Gazi Smiss Electric Oven 40 Liter\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:58:03', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:58:06', '2026-09-08 04:58:06'),
(64, 'product', 31, 'push_to_tally', 'SFA-MPUSH-product-31', '{\"name\":\"GEO-05 - Gazi Smiss Electric Oven 50 Liter\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:58:03', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:58:06', '2026-09-08 04:58:06'),
(65, 'product', 32, 'push_to_tally', 'SFA-MPUSH-product-32', '{\"name\":\"GA-AF-23 - Gazi Smiss Air Fryer\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:58:03', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:58:07', '2026-09-08 04:58:07'),
(66, 'product', 33, 'push_to_tally', 'SFA-MPUSH-product-33', '{\"name\":\"GA-AF-25 - Gazi Smiss Air Fryer\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:58:03', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:58:07', '2026-09-08 04:58:07'),
(67, 'product', 34, 'push_to_tally', 'SFA-MPUSH-product-34', '{\"name\":\"GA-AF-27 - Gazi Smiss Air Fryer\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:58:03', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:58:08', '2026-09-08 04:58:08'),
(68, 'product', 35, 'push_to_tally', 'SFA-MPUSH-product-35', '{\"name\":\"15.0 HP Y2 Motor 950 RPM\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:58:03', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:58:08', '2026-09-08 04:58:08'),
(69, 'product', 36, 'push_to_tally', 'SFA-MPUSH-product-36', '{\"name\":\"10.0 HP Y2 Motor 2800 RPM\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:58:09', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:58:09', '2026-09-08 04:58:09'),
(70, 'product', 37, 'push_to_tally', 'SFA-MPUSH-product-37', '{\"name\":\"5.5 HP Y2 Motor 2800 RPM\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:58:09', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:58:09', '2026-09-08 04:58:09'),
(71, 'product', 38, 'push_to_tally', 'SFA-MPUSH-product-38', '{\"name\":\"3 HP YC Motor 1450 RPM\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:58:09', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:58:10', '2026-09-08 04:58:10'),
(72, 'product', 39, 'push_to_tally', 'SFA-MPUSH-product-39', '{\"name\":\"1.0 HP Y2 Motor 2800 RPM\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:58:09', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:58:10', '2026-09-08 04:58:10'),
(73, 'product', 40, 'push_to_tally', 'SFA-MPUSH-product-40', '{\"name\":\"0.75 HP YC Motor 1450 RPM (Classic)\",\"group\":\"\",\"unit\":\"PCS\"}', 'success', 1, '2026-09-08 04:58:09', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 04:57:31', '2026-09-08 04:58:11', '2026-09-08 04:58:11'),
(74, 'dealer', NULL, 'pull_from_tally', 'SFA-PULL-dealer-20260908105920', '[]', 'success', 1, '2026-09-08 04:59:21', NULL, '{\"rows\":[{\"name\":\"Buriganga Distribution House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\"},{\"name\":\"Dealer 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\"},{\"name\":\"Dealer 2\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\"},{\"name\":\"Dhamrai Water Solutions\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\"},{\"name\":\"Gazi Appliance Corner\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\"},{\"name\":\"Keraniganj Hardware & Motors\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\"},{\"name\":\"Savar Pump House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\"},{\"name\":\"Tangail Hardware\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\"}],\"group\":\"Sundry Debtors\",\"applied\":{\"linked\":0,\"already\":8,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 04:59:20', '2026-09-08 04:59:21', '2026-09-08 04:59:21'),
(75, 'retailer', NULL, 'pull_from_tally', 'SFA-PULL-retailer-20260908105920', '[]', 'success', 1, '2026-09-08 04:59:21', NULL, '{\"rows\":[],\"skipped\":\"TALLY_RETAILER_LEDGER_GROUP is not set to a group of its own, so retailer ledgers cannot be told apart from dealer ledgers. Set it to the group the customer files retailers under (it must differ from the dealers\' group).\",\"applied\":{\"linked\":0,\"already\":0,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 04:59:20', '2026-09-08 04:59:22', '2026-09-08 04:59:22'),
(76, 'product', NULL, 'pull_from_tally', 'SFA-PULL-product-20260908105920', '[]', 'success', 1, '2026-09-08 04:59:21', NULL, '{\"rows\":[{\"name\":\"0.75 HP YC Motor 1450 RPM (Classic)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000108\"},{\"name\":\"10.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000104\"},{\"name\":\"1.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000107\"},{\"name\":\"15.0 HP Y2 Motor 950 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000103\"},{\"name\":\"3 HP YC Motor 1450 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000106\"},{\"name\":\"5.5 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000105\"},{\"name\":\"A-01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fc\"},{\"name\":\"A-25S - Gazi Smiss Induction Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f9\"},{\"name\":\"A-37G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fa\"},{\"name\":\"A-40G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f8\"},{\"name\":\"CNP CDLF Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e7\"},{\"name\":\"CNP SZ Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e8\"},{\"name\":\"E-720B - Gazi Smiss Induction & Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fb\"},{\"name\":\"EG-732S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ef\"},{\"name\":\"EG-750S - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f4\"},{\"name\":\"Eifel EAD Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e6\"},{\"name\":\"Eifel EA Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e5\"},{\"name\":\"GA-AF-23 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000100\"},{\"name\":\"GA-AF-25 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000101\"},{\"name\":\"GA-AF-27 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000102\"},{\"name\":\"Gazi Fire Fighting Pump Complete Set\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e9\"},{\"name\":\"Gazi Gas Stove (Industrial Line)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000eb\"},{\"name\":\"Gazi Motors YC Series\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ea\"},{\"name\":\"Gazi Self-Priming Jet Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e1\"},{\"name\":\"Gazi Standardized Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e2\"},{\"name\":\"Gazi Tubewell\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ec\"},{\"name\":\"GEO-03 - Gazi Smiss Electric Oven 30 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fd\"},{\"name\":\"GEO-04 - Gazi Smiss Electric Oven 40 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fe\"},{\"name\":\"GEO-05 - Gazi Smiss Electric Oven 50 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ff\"},{\"name\":\"GH-8204M - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f1\"},{\"name\":\"GST-102C - Gazi Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ee\"},{\"name\":\"HY-712BT - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f3\"},{\"name\":\"HY-716BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f2\"},{\"name\":\"HY-729CP - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f6\"},{\"name\":\"HY-736BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f5\"},{\"name\":\"IF-HL01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f7\"},{\"name\":\"Pentax Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e3\"},{\"name\":\"Pentax Submersible Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e4\"},{\"name\":\"Pump Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d2\"},{\"name\":\"TG-206 - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ed\"},{\"name\":\"TG-213S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f0\"},{\"name\":\"TV Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d3\"}],\"applied\":{\"linked\":0,\"already\":42,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 04:59:20', '2026-09-08 04:59:22', '2026-09-08 04:59:22'),
(77, 'depot', NULL, 'pull_from_tally', 'SFA-PULL-depot-20260908105920', '[]', 'success', 1, '2026-09-08 04:59:21', NULL, '{\"rows\":[{\"name\":\"Main Depot\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000da\"},{\"name\":\"Main Location\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000063\"},{\"name\":\"Warehouse\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d4\"}],\"applied\":{\"linked\":0,\"already\":3,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 04:59:20', '2026-09-08 04:59:22', '2026-09-08 04:59:22'),
(78, 'dealer', NULL, 'pull_from_tally', 'SFA-PULL-dealer-20260908114753', '[]', 'success', 1, '2026-09-08 05:57:21', NULL, '{\"rows\":[{\"name\":\"Buriganga Distribution House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\"},{\"name\":\"Dealer 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\"},{\"name\":\"Dealer 2\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\"},{\"name\":\"Dhamrai Water Solutions\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\"},{\"name\":\"Gazi Appliance Corner\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\"},{\"name\":\"Keraniganj Hardware & Motors\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\"},{\"name\":\"Rohim Dealer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000109\"},{\"name\":\"Savar Pump House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\"},{\"name\":\"Tangail Hardware\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\"}],\"group\":\"Sundry Debtors\",\"applied\":{\"linked\":0,\"already\":8,\"imported\":[\"Rohim Dealer\"],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 05:47:53', '2026-09-08 05:57:22', '2026-09-08 05:57:22'),
(79, 'retailer', NULL, 'pull_from_tally', 'SFA-PULL-retailer-20260908114753', '[]', 'success', 1, '2026-09-08 05:57:21', NULL, '{\"rows\":[],\"skipped\":\"TALLY_RETAILER_LEDGER_GROUP is not set to a group of its own, so retailer ledgers cannot be told apart from dealer ledgers. Set it to the group the customer files retailers under (it must differ from the dealers\' group).\",\"applied\":{\"linked\":0,\"already\":0,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 05:47:53', '2026-09-08 05:57:22', '2026-09-08 05:57:22'),
(80, 'product', NULL, 'pull_from_tally', 'SFA-PULL-product-20260908114753', '[]', 'success', 1, '2026-09-08 05:57:21', NULL, '{\"rows\":[{\"name\":\"0.75 HP YC Motor 1450 RPM (Classic)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000108\"},{\"name\":\"10.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000104\"},{\"name\":\"1.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000107\"},{\"name\":\"15.0 HP Y2 Motor 950 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000103\"},{\"name\":\"3 HP YC Motor 1450 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000106\"},{\"name\":\"5.5 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000105\"},{\"name\":\"A-01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fc\"},{\"name\":\"A-25S - Gazi Smiss Induction Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f9\"},{\"name\":\"A-37G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fa\"},{\"name\":\"A-40G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f8\"},{\"name\":\"CNP CDLF Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e7\"},{\"name\":\"CNP SZ Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e8\"},{\"name\":\"E-720B - Gazi Smiss Induction & Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fb\"},{\"name\":\"EG-732S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ef\"},{\"name\":\"EG-750S - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f4\"},{\"name\":\"Eifel EAD Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e6\"},{\"name\":\"Eifel EA Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e5\"},{\"name\":\"GA-AF-23 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000100\"},{\"name\":\"GA-AF-25 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000101\"},{\"name\":\"GA-AF-27 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000102\"},{\"name\":\"Gazi Fire Fighting Pump Complete Set\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e9\"},{\"name\":\"Gazi Gas Stove (Industrial Line)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000eb\"},{\"name\":\"Gazi Motors YC Series\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ea\"},{\"name\":\"Gazi Self-Priming Jet Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e1\"},{\"name\":\"Gazi Standardized Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e2\"},{\"name\":\"Gazi Tubewell\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ec\"},{\"name\":\"GEO-03 - Gazi Smiss Electric Oven 30 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fd\"},{\"name\":\"GEO-04 - Gazi Smiss Electric Oven 40 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fe\"},{\"name\":\"GEO-05 - Gazi Smiss Electric Oven 50 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ff\"},{\"name\":\"GH-8204M - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f1\"},{\"name\":\"GST-102C - Gazi Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ee\"},{\"name\":\"HY-712BT - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f3\"},{\"name\":\"HY-716BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f2\"},{\"name\":\"HY-729CP - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f6\"},{\"name\":\"HY-736BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f5\"},{\"name\":\"IF-HL01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f7\"},{\"name\":\"Pentax Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e3\"},{\"name\":\"Pentax Submersible Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e4\"},{\"name\":\"Pump Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d2\"},{\"name\":\"TG-206 - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ed\"},{\"name\":\"TG-213S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f0\"},{\"name\":\"TV Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d3\"}],\"applied\":{\"linked\":0,\"already\":42,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 05:47:53', '2026-09-08 05:57:23', '2026-09-08 05:57:23'),
(81, 'depot', NULL, 'pull_from_tally', 'SFA-PULL-depot-20260908114753', '[]', 'success', 1, '2026-09-08 05:57:21', NULL, '{\"rows\":[{\"name\":\"Main Depot\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000da\"},{\"name\":\"Main Location\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000063\"},{\"name\":\"Warehouse\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d4\"}],\"applied\":{\"linked\":0,\"already\":3,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 05:47:53', '2026-09-08 05:57:23', '2026-09-08 05:57:23'),
(82, 'ledger', 1, 'pull_from_tally', 'SFA-LED-PULL-1-20260908114753', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\",\"dealer_tally_name\":\"Savar Pump House\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 05:57:22', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 05:47:53', '2026-09-08 05:57:23', '2026-09-08 05:57:23'),
(83, 'ledger', 2, 'pull_from_tally', 'SFA-LED-PULL-2-20260908114753', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\",\"dealer_tally_name\":\"Keraniganj Hardware & Motors\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 05:57:22', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 05:47:53', '2026-09-08 05:57:24', '2026-09-08 05:57:24'),
(84, 'ledger', 3, 'pull_from_tally', 'SFA-LED-PULL-3-20260908114753', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\",\"dealer_tally_name\":\"Dhamrai Water Solutions\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 05:57:22', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 05:47:53', '2026-09-08 05:57:24', '2026-09-08 05:57:24'),
(85, 'ledger', 4, 'pull_from_tally', 'SFA-LED-PULL-4-20260908114753', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\",\"dealer_tally_name\":\"Gazi Appliance Corner\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 05:57:22', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 05:47:53', '2026-09-08 05:57:24', '2026-09-08 05:57:24'),
(86, 'ledger', 5, 'pull_from_tally', 'SFA-LED-PULL-5-20260908114753', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\",\"dealer_tally_name\":\"Buriganga Distribution House\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 05:57:22', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 05:47:53', '2026-09-08 05:57:25', '2026-09-08 05:57:25'),
(87, 'ledger', 14, 'pull_from_tally', 'SFA-LED-PULL-14-20260908114753', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\",\"dealer_tally_name\":\"Tangail Hardware\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 05:57:22', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 05:47:53', '2026-09-08 05:57:25', '2026-09-08 05:57:25'),
(88, 'ledger', 18, 'pull_from_tally', 'SFA-LED-PULL-18-20260908114753', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\",\"dealer_tally_name\":\"Dealer 1\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 05:57:25', NULL, '{\"rows\":[{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000003-0\",\"ledger_name\":\"Dealer 1\",\"voucher_date\":\"2026-09-01\",\"voucher_type\":\"Sales\",\"voucher_number\":\"1\",\"narration\":null,\"debit_amount\":125000,\"credit_amount\":0}]}', NULL, NULL, '2026-09-08 05:47:53', '2026-09-08 05:57:26', '2026-09-08 05:57:26'),
(89, 'ledger', 19, 'pull_from_tally', 'SFA-LED-PULL-19-20260908114753', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\",\"dealer_tally_name\":\"Dealer 2\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 05:57:25', NULL, '{\"rows\":[{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000001-1\",\"ledger_name\":\"Dealer 2\",\"voucher_date\":\"2026-09-01\",\"voucher_type\":\"Journal\",\"voucher_number\":\"1\",\"narration\":null,\"debit_amount\":0,\"credit_amount\":50}]}', NULL, NULL, '2026-09-08 05:47:53', '2026-09-08 05:57:26', '2026-09-08 05:57:26'),
(90, 'dealer', 20, 'push_to_tally', 'SFA-MPUSH-dealer-20', '{\"name\":\"Korim\",\"group\":\"Sundry Debtors\",\"phone\":\"010000000\",\"email\":null,\"address\":null}', 'success', 1, '2026-09-08 05:57:25', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 05:47:53', '2026-09-08 05:57:27', '2026-09-08 05:57:27'),
(91, 'dealer', NULL, 'pull_from_tally', 'SFA-PULL-dealer-20260908122634', '[]', 'success', 1, '2026-09-08 06:26:42', NULL, '{\"rows\":[{\"name\":\"Buriganga Distribution House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\"},{\"name\":\"Dealer 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\"},{\"name\":\"Dealer 2\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\"},{\"name\":\"Dhamrai Water Solutions\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\"},{\"name\":\"Gazi Appliance Corner\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\"},{\"name\":\"Keraniganj Hardware & Motors\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\"},{\"name\":\"Korim\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010a\"},{\"name\":\"Rohim Dealer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000109\"},{\"name\":\"Savar Pump House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\"},{\"name\":\"Tangail Hardware\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\"}],\"group\":\"Sundry Debtors\",\"applied\":{\"linked\":0,\"already\":10,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 06:26:34', '2026-09-08 06:26:42', '2026-09-08 06:26:42'),
(92, 'retailer', NULL, 'pull_from_tally', 'SFA-PULL-retailer-20260908122634', '[]', 'success', 1, '2026-09-08 06:26:42', NULL, '{\"rows\":[],\"skipped\":\"TALLY_RETAILER_LEDGER_GROUP is not set to a group of its own, so retailer ledgers cannot be told apart from dealer ledgers. Set it to the group the customer files retailers under (it must differ from the dealers\' group).\",\"applied\":{\"linked\":0,\"already\":0,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 06:26:34', '2026-09-08 06:26:43', '2026-09-08 06:26:43'),
(93, 'product', NULL, 'pull_from_tally', 'SFA-PULL-product-20260908122634', '[]', 'success', 1, '2026-09-08 06:26:42', NULL, '{\"rows\":[{\"name\":\"0.75 HP YC Motor 1450 RPM (Classic)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000108\"},{\"name\":\"10.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000104\"},{\"name\":\"1.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000107\"},{\"name\":\"15.0 HP Y2 Motor 950 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000103\"},{\"name\":\"3 HP YC Motor 1450 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000106\"},{\"name\":\"5.5 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000105\"},{\"name\":\"A-01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fc\"},{\"name\":\"A-25S - Gazi Smiss Induction Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f9\"},{\"name\":\"A-37G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fa\"},{\"name\":\"A-40G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f8\"},{\"name\":\"CNP CDLF Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e7\"},{\"name\":\"CNP SZ Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e8\"},{\"name\":\"E-720B - Gazi Smiss Induction & Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fb\"},{\"name\":\"EG-732S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ef\"},{\"name\":\"EG-750S - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f4\"},{\"name\":\"Eifel EAD Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e6\"},{\"name\":\"Eifel EA Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e5\"},{\"name\":\"GA-AF-23 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000100\"},{\"name\":\"GA-AF-25 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000101\"},{\"name\":\"GA-AF-27 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000102\"},{\"name\":\"Gazi Fire Fighting Pump Complete Set\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e9\"},{\"name\":\"Gazi Gas Stove (Industrial Line)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000eb\"},{\"name\":\"Gazi Motors YC Series\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ea\"},{\"name\":\"Gazi Self-Priming Jet Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e1\"},{\"name\":\"Gazi Standardized Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e2\"},{\"name\":\"Gazi Tubewell\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ec\"},{\"name\":\"GEO-03 - Gazi Smiss Electric Oven 30 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fd\"},{\"name\":\"GEO-04 - Gazi Smiss Electric Oven 40 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fe\"},{\"name\":\"GEO-05 - Gazi Smiss Electric Oven 50 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ff\"},{\"name\":\"GH-8204M - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f1\"},{\"name\":\"GST-102C - Gazi Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ee\"},{\"name\":\"HY-712BT - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f3\"},{\"name\":\"HY-716BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f2\"},{\"name\":\"HY-729CP - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f6\"},{\"name\":\"HY-736BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f5\"},{\"name\":\"IF-HL01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f7\"},{\"name\":\"Pentax Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e3\"},{\"name\":\"Pentax Submersible Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e4\"},{\"name\":\"Pump Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d2\"},{\"name\":\"TG-206 - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ed\"},{\"name\":\"TG-213S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f0\"},{\"name\":\"TV Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d3\"}],\"applied\":{\"linked\":0,\"already\":42,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 06:26:34', '2026-09-08 06:26:43', '2026-09-08 06:26:43');
INSERT INTO `sync_queues` (`id`, `entity_type`, `entity_id`, `direction`, `external_reference`, `payload`, `status`, `attempt_count`, `last_attempt_at`, `next_attempt_at`, `response`, `error_code`, `error_message`, `created_at`, `updated_at`, `completed_at`) VALUES
(94, 'depot', NULL, 'pull_from_tally', 'SFA-PULL-depot-20260908122634', '[]', 'success', 1, '2026-09-08 06:26:42', NULL, '{\"rows\":[{\"name\":\"Main Depot\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000da\"},{\"name\":\"Main Location\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000063\"},{\"name\":\"Warehouse\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d4\"}],\"applied\":{\"linked\":0,\"already\":3,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 06:26:34', '2026-09-08 06:26:44', '2026-09-08 06:26:44'),
(95, 'ledger', 1, 'pull_from_tally', 'SFA-LED-PULL-1-20260908122634', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\",\"dealer_tally_name\":\"Savar Pump House\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:26:42', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:26:34', '2026-09-08 06:26:44', '2026-09-08 06:26:44'),
(96, 'ledger', 2, 'pull_from_tally', 'SFA-LED-PULL-2-20260908122634', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\",\"dealer_tally_name\":\"Keraniganj Hardware & Motors\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:26:42', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:26:34', '2026-09-08 06:26:44', '2026-09-08 06:26:44'),
(97, 'ledger', 3, 'pull_from_tally', 'SFA-LED-PULL-3-20260908122634', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\",\"dealer_tally_name\":\"Dhamrai Water Solutions\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:26:42', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:26:34', '2026-09-08 06:26:45', '2026-09-08 06:26:45'),
(98, 'ledger', 4, 'pull_from_tally', 'SFA-LED-PULL-4-20260908122634', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\",\"dealer_tally_name\":\"Gazi Appliance Corner\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:26:42', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:26:34', '2026-09-08 06:26:45', '2026-09-08 06:26:45'),
(99, 'ledger', 5, 'pull_from_tally', 'SFA-LED-PULL-5-20260908122634', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\",\"dealer_tally_name\":\"Buriganga Distribution House\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:26:42', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:26:34', '2026-09-08 06:26:46', '2026-09-08 06:26:46'),
(100, 'ledger', 14, 'pull_from_tally', 'SFA-LED-PULL-14-20260908122634', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\",\"dealer_tally_name\":\"Tangail Hardware\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:26:42', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:26:34', '2026-09-08 06:26:46', '2026-09-08 06:26:46'),
(101, 'ledger', 18, 'pull_from_tally', 'SFA-LED-PULL-18-20260908122634', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\",\"dealer_tally_name\":\"Dealer 1\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:26:53', NULL, '{\"rows\":[{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000003-0\",\"ledger_name\":\"Dealer 1\",\"voucher_date\":\"2026-09-01\",\"voucher_type\":\"Sales\",\"voucher_number\":\"1\",\"narration\":null,\"debit_amount\":125000,\"credit_amount\":0}]}', NULL, NULL, '2026-09-08 06:26:34', '2026-09-08 06:26:53', '2026-09-08 06:26:53'),
(102, 'ledger', 19, 'pull_from_tally', 'SFA-LED-PULL-19-20260908122634', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\",\"dealer_tally_name\":\"Dealer 2\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:26:53', NULL, '{\"rows\":[{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000001-1\",\"ledger_name\":\"Dealer 2\",\"voucher_date\":\"2026-09-01\",\"voucher_type\":\"Journal\",\"voucher_number\":\"1\",\"narration\":null,\"debit_amount\":0,\"credit_amount\":50}]}', NULL, NULL, '2026-09-08 06:26:34', '2026-09-08 06:26:54', '2026-09-08 06:26:54'),
(103, 'ledger', 20, 'pull_from_tally', 'SFA-LED-PULL-20-20260908122634', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010a\",\"dealer_tally_name\":\"Korim\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:26:53', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:26:34', '2026-09-08 06:26:54', '2026-09-08 06:26:54'),
(104, 'ledger', 21, 'pull_from_tally', 'SFA-LED-PULL-21-20260908122634', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000109\",\"dealer_tally_name\":\"Rohim Dealer\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:26:53', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:26:34', '2026-09-08 06:26:54', '2026-09-08 06:26:54'),
(105, 'dealer', 22, 'push_to_tally', 'SFA-MPUSH-dealer-22', '{\"name\":\"Emran\",\"group\":\"Sundry Debtors\",\"phone\":\"01756956465651\",\"email\":null,\"address\":null}', 'success', 1, '2026-09-08 06:26:53', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 06:26:34', '2026-09-08 06:26:55', '2026-09-08 06:26:55'),
(106, 'dealer', NULL, 'pull_from_tally', 'SFA-PULL-dealer-20260908122658', '[]', 'success', 1, '2026-09-08 06:27:12', NULL, '{\"rows\":[{\"name\":\"Buriganga Distribution House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\"},{\"name\":\"Dealer 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\"},{\"name\":\"Dealer 2\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\"},{\"name\":\"Dhamrai Water Solutions\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\"},{\"name\":\"Emran\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010b\"},{\"name\":\"Gazi Appliance Corner\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\"},{\"name\":\"Keraniganj Hardware & Motors\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\"},{\"name\":\"Korim\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010a\"},{\"name\":\"Rohim Dealer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000109\"},{\"name\":\"Savar Pump House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\"},{\"name\":\"Tangail Hardware\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\"}],\"group\":\"Sundry Debtors\",\"applied\":{\"linked\":0,\"already\":11,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 06:26:58', '2026-09-08 06:27:12', '2026-09-08 06:27:12'),
(107, 'retailer', NULL, 'pull_from_tally', 'SFA-PULL-retailer-20260908122658', '[]', 'success', 1, '2026-09-08 06:27:12', NULL, '{\"rows\":[],\"skipped\":\"TALLY_RETAILER_LEDGER_GROUP is not set to a group of its own, so retailer ledgers cannot be told apart from dealer ledgers. Set it to the group the customer files retailers under (it must differ from the dealers\' group).\",\"applied\":{\"linked\":0,\"already\":0,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 06:26:58', '2026-09-08 06:27:13', '2026-09-08 06:27:13'),
(108, 'product', NULL, 'pull_from_tally', 'SFA-PULL-product-20260908122658', '[]', 'success', 1, '2026-09-08 06:27:12', NULL, '{\"rows\":[{\"name\":\"0.75 HP YC Motor 1450 RPM (Classic)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000108\"},{\"name\":\"10.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000104\"},{\"name\":\"1.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000107\"},{\"name\":\"15.0 HP Y2 Motor 950 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000103\"},{\"name\":\"3 HP YC Motor 1450 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000106\"},{\"name\":\"5.5 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000105\"},{\"name\":\"A-01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fc\"},{\"name\":\"A-25S - Gazi Smiss Induction Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f9\"},{\"name\":\"A-37G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fa\"},{\"name\":\"A-40G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f8\"},{\"name\":\"CNP CDLF Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e7\"},{\"name\":\"CNP SZ Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e8\"},{\"name\":\"E-720B - Gazi Smiss Induction & Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fb\"},{\"name\":\"EG-732S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ef\"},{\"name\":\"EG-750S - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f4\"},{\"name\":\"Eifel EAD Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e6\"},{\"name\":\"Eifel EA Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e5\"},{\"name\":\"GA-AF-23 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000100\"},{\"name\":\"GA-AF-25 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000101\"},{\"name\":\"GA-AF-27 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000102\"},{\"name\":\"Gazi Fire Fighting Pump Complete Set\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e9\"},{\"name\":\"Gazi Gas Stove (Industrial Line)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000eb\"},{\"name\":\"Gazi Motors YC Series\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ea\"},{\"name\":\"Gazi Self-Priming Jet Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e1\"},{\"name\":\"Gazi Standardized Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e2\"},{\"name\":\"Gazi Tubewell\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ec\"},{\"name\":\"GEO-03 - Gazi Smiss Electric Oven 30 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fd\"},{\"name\":\"GEO-04 - Gazi Smiss Electric Oven 40 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fe\"},{\"name\":\"GEO-05 - Gazi Smiss Electric Oven 50 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ff\"},{\"name\":\"GH-8204M - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f1\"},{\"name\":\"GST-102C - Gazi Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ee\"},{\"name\":\"HY-712BT - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f3\"},{\"name\":\"HY-716BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f2\"},{\"name\":\"HY-729CP - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f6\"},{\"name\":\"HY-736BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f5\"},{\"name\":\"IF-HL01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f7\"},{\"name\":\"Pentax Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e3\"},{\"name\":\"Pentax Submersible Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e4\"},{\"name\":\"Pump Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d2\"},{\"name\":\"TG-206 - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ed\"},{\"name\":\"TG-213S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f0\"},{\"name\":\"TV Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d3\"}],\"applied\":{\"linked\":0,\"already\":42,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 06:26:58', '2026-09-08 06:27:13', '2026-09-08 06:27:13'),
(109, 'depot', NULL, 'pull_from_tally', 'SFA-PULL-depot-20260908122658', '[]', 'success', 1, '2026-09-08 06:27:12', NULL, '{\"rows\":[{\"name\":\"Main Depot\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000da\"},{\"name\":\"Main Location\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000063\"},{\"name\":\"Warehouse\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d4\"}],\"applied\":{\"linked\":0,\"already\":3,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 06:26:58', '2026-09-08 06:27:13', '2026-09-08 06:27:13'),
(110, 'ledger', 1, 'pull_from_tally', 'SFA-LED-PULL-1-20260908122658', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\",\"dealer_tally_name\":\"Savar Pump House\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:27:12', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:26:58', '2026-09-08 06:27:14', '2026-09-08 06:27:14'),
(111, 'ledger', 2, 'pull_from_tally', 'SFA-LED-PULL-2-20260908122658', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\",\"dealer_tally_name\":\"Keraniganj Hardware & Motors\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:27:12', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:26:58', '2026-09-08 06:27:14', '2026-09-08 06:27:14'),
(112, 'ledger', 3, 'pull_from_tally', 'SFA-LED-PULL-3-20260908122658', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\",\"dealer_tally_name\":\"Dhamrai Water Solutions\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:27:12', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:26:58', '2026-09-08 06:27:14', '2026-09-08 06:27:14'),
(113, 'ledger', 4, 'pull_from_tally', 'SFA-LED-PULL-4-20260908122658', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\",\"dealer_tally_name\":\"Gazi Appliance Corner\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:27:12', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:26:58', '2026-09-08 06:27:15', '2026-09-08 06:27:15'),
(114, 'ledger', 5, 'pull_from_tally', 'SFA-LED-PULL-5-20260908122658', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\",\"dealer_tally_name\":\"Buriganga Distribution House\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:27:12', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:26:58', '2026-09-08 06:27:15', '2026-09-08 06:27:15'),
(115, 'ledger', 14, 'pull_from_tally', 'SFA-LED-PULL-14-20260908122658', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\",\"dealer_tally_name\":\"Tangail Hardware\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:27:12', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:26:58', '2026-09-08 06:27:15', '2026-09-08 06:27:15'),
(116, 'ledger', 18, 'pull_from_tally', 'SFA-LED-PULL-18-20260908122658', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\",\"dealer_tally_name\":\"Dealer 1\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:27:23', NULL, '{\"rows\":[{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000003-0\",\"ledger_name\":\"Dealer 1\",\"voucher_date\":\"2026-09-01\",\"voucher_type\":\"Sales\",\"voucher_number\":\"1\",\"narration\":null,\"debit_amount\":125000,\"credit_amount\":0}]}', NULL, NULL, '2026-09-08 06:26:58', '2026-09-08 06:27:23', '2026-09-08 06:27:23'),
(117, 'ledger', 19, 'pull_from_tally', 'SFA-LED-PULL-19-20260908122658', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\",\"dealer_tally_name\":\"Dealer 2\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:27:23', NULL, '{\"rows\":[{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000001-1\",\"ledger_name\":\"Dealer 2\",\"voucher_date\":\"2026-09-01\",\"voucher_type\":\"Journal\",\"voucher_number\":\"1\",\"narration\":null,\"debit_amount\":0,\"credit_amount\":50}]}', NULL, NULL, '2026-09-08 06:26:58', '2026-09-08 06:27:23', '2026-09-08 06:27:23'),
(118, 'ledger', 20, 'pull_from_tally', 'SFA-LED-PULL-20-20260908122658', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010a\",\"dealer_tally_name\":\"Korim\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:27:23', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:26:58', '2026-09-08 06:27:24', '2026-09-08 06:27:24'),
(119, 'ledger', 21, 'pull_from_tally', 'SFA-LED-PULL-21-20260908122658', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000109\",\"dealer_tally_name\":\"Rohim Dealer\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:27:23', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:26:58', '2026-09-08 06:27:24', '2026-09-08 06:27:24'),
(120, 'ledger', 22, 'pull_from_tally', 'SFA-LED-PULL-22-20260908122658', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010b\",\"dealer_tally_name\":\"Emran\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:27:23', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:26:58', '2026-09-08 06:27:24', '2026-09-08 06:27:24'),
(121, 'dealer', NULL, 'pull_from_tally', 'SFA-PULL-dealer-20260908122955', '[]', 'success', 1, '2026-09-08 06:30:12', NULL, '{\"rows\":[{\"name\":\"Buriganga Distribution House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\"},{\"name\":\"Dealer 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\"},{\"name\":\"Dealer 2\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\"},{\"name\":\"Dhamrai Water Solutions\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\"},{\"name\":\"Emran\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010b\"},{\"name\":\"Gazi Appliance Corner\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\"},{\"name\":\"Iftakher\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010c\"},{\"name\":\"Keraniganj Hardware & Motors\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\"},{\"name\":\"Korim\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010a\"},{\"name\":\"Rohim Dealer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000109\"},{\"name\":\"Savar Pump House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\"},{\"name\":\"Tangail Hardware\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\"}],\"group\":\"Sundry Debtors\",\"applied\":{\"linked\":0,\"already\":11,\"imported\":[\"Iftakher\"],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 06:29:55', '2026-09-08 06:30:12', '2026-09-08 06:30:12'),
(122, 'retailer', NULL, 'pull_from_tally', 'SFA-PULL-retailer-20260908122955', '[]', 'success', 1, '2026-09-08 06:30:12', NULL, '{\"rows\":[],\"skipped\":\"TALLY_RETAILER_LEDGER_GROUP is not set to a group of its own, so retailer ledgers cannot be told apart from dealer ledgers. Set it to the group the customer files retailers under (it must differ from the dealers\' group).\",\"applied\":{\"linked\":0,\"already\":0,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 06:29:55', '2026-09-08 06:30:13', '2026-09-08 06:30:13'),
(123, 'product', NULL, 'pull_from_tally', 'SFA-PULL-product-20260908122955', '[]', 'success', 1, '2026-09-08 06:30:12', NULL, '{\"rows\":[{\"name\":\"0.75 HP YC Motor 1450 RPM (Classic)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000108\"},{\"name\":\"10.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000104\"},{\"name\":\"1.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000107\"},{\"name\":\"15.0 HP Y2 Motor 950 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000103\"},{\"name\":\"3 HP YC Motor 1450 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000106\"},{\"name\":\"5.5 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000105\"},{\"name\":\"A-01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fc\"},{\"name\":\"A-25S - Gazi Smiss Induction Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f9\"},{\"name\":\"A-37G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fa\"},{\"name\":\"A-40G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f8\"},{\"name\":\"CNP CDLF Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e7\"},{\"name\":\"CNP SZ Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e8\"},{\"name\":\"E-720B - Gazi Smiss Induction & Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fb\"},{\"name\":\"EG-732S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ef\"},{\"name\":\"EG-750S - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f4\"},{\"name\":\"Eifel EAD Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e6\"},{\"name\":\"Eifel EA Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e5\"},{\"name\":\"GA-AF-23 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000100\"},{\"name\":\"GA-AF-25 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000101\"},{\"name\":\"GA-AF-27 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000102\"},{\"name\":\"Gazi Fire Fighting Pump Complete Set\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e9\"},{\"name\":\"Gazi Gas Stove (Industrial Line)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000eb\"},{\"name\":\"Gazi Motors YC Series\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ea\"},{\"name\":\"Gazi Self-Priming Jet Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e1\"},{\"name\":\"Gazi Standardized Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e2\"},{\"name\":\"Gazi Tubewell\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ec\"},{\"name\":\"GEO-03 - Gazi Smiss Electric Oven 30 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fd\"},{\"name\":\"GEO-04 - Gazi Smiss Electric Oven 40 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fe\"},{\"name\":\"GEO-05 - Gazi Smiss Electric Oven 50 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ff\"},{\"name\":\"GH-8204M - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f1\"},{\"name\":\"GST-102C - Gazi Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ee\"},{\"name\":\"HY-712BT - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f3\"},{\"name\":\"HY-716BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f2\"},{\"name\":\"HY-729CP - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f6\"},{\"name\":\"HY-736BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f5\"},{\"name\":\"IF-HL01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f7\"},{\"name\":\"Pentax Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e3\"},{\"name\":\"Pentax Submersible Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e4\"},{\"name\":\"Pump Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d2\"},{\"name\":\"TG-206 - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ed\"},{\"name\":\"TG-213S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f0\"},{\"name\":\"TV Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d3\"}],\"applied\":{\"linked\":0,\"already\":42,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 06:29:55', '2026-09-08 06:30:13', '2026-09-08 06:30:13'),
(124, 'depot', NULL, 'pull_from_tally', 'SFA-PULL-depot-20260908122955', '[]', 'success', 1, '2026-09-08 06:30:12', NULL, '{\"rows\":[{\"name\":\"Main Depot\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000da\"},{\"name\":\"Main Location\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000063\"},{\"name\":\"Warehouse\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d4\"}],\"applied\":{\"linked\":0,\"already\":3,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 06:29:55', '2026-09-08 06:30:13', '2026-09-08 06:30:13'),
(125, 'ledger', 1, 'pull_from_tally', 'SFA-LED-PULL-1-20260908122955', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\",\"dealer_tally_name\":\"Savar Pump House\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:30:12', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:29:55', '2026-09-08 06:30:14', '2026-09-08 06:30:14'),
(126, 'ledger', 2, 'pull_from_tally', 'SFA-LED-PULL-2-20260908122955', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\",\"dealer_tally_name\":\"Keraniganj Hardware & Motors\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:30:12', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:29:55', '2026-09-08 06:30:14', '2026-09-08 06:30:14'),
(127, 'ledger', 3, 'pull_from_tally', 'SFA-LED-PULL-3-20260908122955', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\",\"dealer_tally_name\":\"Dhamrai Water Solutions\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:30:12', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:29:55', '2026-09-08 06:30:14', '2026-09-08 06:30:14'),
(128, 'ledger', 4, 'pull_from_tally', 'SFA-LED-PULL-4-20260908122955', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\",\"dealer_tally_name\":\"Gazi Appliance Corner\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:30:12', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:29:55', '2026-09-08 06:30:15', '2026-09-08 06:30:15'),
(129, 'ledger', 5, 'pull_from_tally', 'SFA-LED-PULL-5-20260908122955', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\",\"dealer_tally_name\":\"Buriganga Distribution House\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:30:12', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:29:55', '2026-09-08 06:30:15', '2026-09-08 06:30:15'),
(130, 'ledger', 14, 'pull_from_tally', 'SFA-LED-PULL-14-20260908122955', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\",\"dealer_tally_name\":\"Tangail Hardware\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:30:12', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:29:55', '2026-09-08 06:30:15', '2026-09-08 06:30:15'),
(131, 'ledger', 18, 'pull_from_tally', 'SFA-LED-PULL-18-20260908122955', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\",\"dealer_tally_name\":\"Dealer 1\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:30:23', NULL, '{\"rows\":[{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000003-0\",\"ledger_name\":\"Dealer 1\",\"voucher_date\":\"2026-09-01\",\"voucher_type\":\"Sales\",\"voucher_number\":\"1\",\"narration\":null,\"debit_amount\":125000,\"credit_amount\":0}]}', NULL, NULL, '2026-09-08 06:29:55', '2026-09-08 06:30:23', '2026-09-08 06:30:23'),
(132, 'ledger', 19, 'pull_from_tally', 'SFA-LED-PULL-19-20260908122955', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\",\"dealer_tally_name\":\"Dealer 2\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:30:23', NULL, '{\"rows\":[{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000001-1\",\"ledger_name\":\"Dealer 2\",\"voucher_date\":\"2026-09-01\",\"voucher_type\":\"Journal\",\"voucher_number\":\"1\",\"narration\":null,\"debit_amount\":0,\"credit_amount\":50}]}', NULL, NULL, '2026-09-08 06:29:55', '2026-09-08 06:30:23', '2026-09-08 06:30:23'),
(133, 'ledger', 20, 'pull_from_tally', 'SFA-LED-PULL-20-20260908122955', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010a\",\"dealer_tally_name\":\"Korim\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:30:23', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:29:55', '2026-09-08 06:30:24', '2026-09-08 06:30:24'),
(134, 'ledger', 21, 'pull_from_tally', 'SFA-LED-PULL-21-20260908122955', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000109\",\"dealer_tally_name\":\"Rohim Dealer\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:30:23', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:29:55', '2026-09-08 06:30:24', '2026-09-08 06:30:24'),
(135, 'ledger', 22, 'pull_from_tally', 'SFA-LED-PULL-22-20260908122955', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010b\",\"dealer_tally_name\":\"Emran\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:30:23', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:29:55', '2026-09-08 06:30:24', '2026-09-08 06:30:24'),
(136, 'dealer', NULL, 'pull_from_tally', 'SFA-PULL-dealer-20260908123142', '[]', 'success', 1, '2026-09-08 06:31:53', NULL, '{\"rows\":[{\"name\":\"Buriganga Distribution House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\"},{\"name\":\"Dealer 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\"},{\"name\":\"Dealer 2\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\"},{\"name\":\"Dhamrai Water Solutions\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\"},{\"name\":\"Emran\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010b\"},{\"name\":\"Gazi Appliance Corner\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\"},{\"name\":\"Iftakher\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010c\"},{\"name\":\"Keraniganj Hardware & Motors\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\"},{\"name\":\"Korim\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010a\"},{\"name\":\"Rohim Dealer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000109\"},{\"name\":\"Savar Pump House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\"},{\"name\":\"Tangail Hardware\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\"}],\"group\":\"Sundry Debtors\",\"applied\":{\"linked\":0,\"already\":12,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 06:31:42', '2026-09-08 06:31:54', '2026-09-08 06:31:54'),
(137, 'retailer', NULL, 'pull_from_tally', 'SFA-PULL-retailer-20260908123142', '[]', 'success', 1, '2026-09-08 06:31:53', NULL, '{\"rows\":[],\"skipped\":\"TALLY_RETAILER_LEDGER_GROUP is not set to a group of its own, so retailer ledgers cannot be told apart from dealer ledgers. Set it to the group the customer files retailers under (it must differ from the dealers\' group).\",\"applied\":{\"linked\":0,\"already\":0,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 06:31:42', '2026-09-08 06:31:54', '2026-09-08 06:31:54'),
(138, 'product', NULL, 'pull_from_tally', 'SFA-PULL-product-20260908123142', '[]', 'success', 1, '2026-09-08 06:31:53', NULL, '{\"rows\":[{\"name\":\"0.75 HP YC Motor 1450 RPM (Classic)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000108\"},{\"name\":\"10.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000104\"},{\"name\":\"1.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000107\"},{\"name\":\"15.0 HP Y2 Motor 950 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000103\"},{\"name\":\"3 HP YC Motor 1450 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000106\"},{\"name\":\"5.5 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000105\"},{\"name\":\"A-01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fc\"},{\"name\":\"A-25S - Gazi Smiss Induction Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f9\"},{\"name\":\"A-37G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fa\"},{\"name\":\"A-40G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f8\"},{\"name\":\"CNP CDLF Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e7\"},{\"name\":\"CNP SZ Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e8\"},{\"name\":\"E-720B - Gazi Smiss Induction & Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fb\"},{\"name\":\"EG-732S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ef\"},{\"name\":\"EG-750S - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f4\"},{\"name\":\"Eifel EAD Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e6\"},{\"name\":\"Eifel EA Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e5\"},{\"name\":\"GA-AF-23 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000100\"},{\"name\":\"GA-AF-25 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000101\"},{\"name\":\"GA-AF-27 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000102\"},{\"name\":\"Gazi Fire Fighting Pump Complete Set\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e9\"},{\"name\":\"Gazi Gas Stove (Industrial Line)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000eb\"},{\"name\":\"Gazi Motors YC Series\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ea\"},{\"name\":\"Gazi Self-Priming Jet Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e1\"},{\"name\":\"Gazi Standardized Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e2\"},{\"name\":\"Gazi Tubewell\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ec\"},{\"name\":\"GEO-03 - Gazi Smiss Electric Oven 30 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fd\"},{\"name\":\"GEO-04 - Gazi Smiss Electric Oven 40 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fe\"},{\"name\":\"GEO-05 - Gazi Smiss Electric Oven 50 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ff\"},{\"name\":\"GH-8204M - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f1\"},{\"name\":\"GST-102C - Gazi Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ee\"},{\"name\":\"HY-712BT - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f3\"},{\"name\":\"HY-716BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f2\"},{\"name\":\"HY-729CP - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f6\"},{\"name\":\"HY-736BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f5\"},{\"name\":\"IF-HL01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f7\"},{\"name\":\"Pentax Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e3\"},{\"name\":\"Pentax Submersible Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e4\"},{\"name\":\"Pump Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d2\"},{\"name\":\"TG-206 - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ed\"},{\"name\":\"TG-213S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f0\"},{\"name\":\"TV Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d3\"}],\"applied\":{\"linked\":0,\"already\":42,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 06:31:42', '2026-09-08 06:31:54', '2026-09-08 06:31:54'),
(139, 'depot', NULL, 'pull_from_tally', 'SFA-PULL-depot-20260908123142', '[]', 'success', 1, '2026-09-08 06:31:53', NULL, '{\"rows\":[{\"name\":\"Main Depot\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000da\"},{\"name\":\"Main Location\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000063\"},{\"name\":\"Warehouse\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d4\"}],\"applied\":{\"linked\":0,\"already\":3,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 06:31:42', '2026-09-08 06:31:55', '2026-09-08 06:31:55'),
(140, 'ledger', 1, 'pull_from_tally', 'SFA-LED-PULL-1-20260908123143', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\",\"dealer_tally_name\":\"Savar Pump House\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:31:53', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:31:43', '2026-09-08 06:31:55', '2026-09-08 06:31:55'),
(141, 'ledger', 2, 'pull_from_tally', 'SFA-LED-PULL-2-20260908123143', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\",\"dealer_tally_name\":\"Keraniganj Hardware & Motors\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:31:53', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:31:43', '2026-09-08 06:31:55', '2026-09-08 06:31:55'),
(142, 'ledger', 3, 'pull_from_tally', 'SFA-LED-PULL-3-20260908123143', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\",\"dealer_tally_name\":\"Dhamrai Water Solutions\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:31:53', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:31:43', '2026-09-08 06:31:56', '2026-09-08 06:31:56'),
(143, 'ledger', 4, 'pull_from_tally', 'SFA-LED-PULL-4-20260908123143', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\",\"dealer_tally_name\":\"Gazi Appliance Corner\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:31:53', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:31:43', '2026-09-08 06:31:56', '2026-09-08 06:31:56'),
(144, 'ledger', 5, 'pull_from_tally', 'SFA-LED-PULL-5-20260908123143', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\",\"dealer_tally_name\":\"Buriganga Distribution House\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:31:53', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:31:43', '2026-09-08 06:31:57', '2026-09-08 06:31:57'),
(145, 'ledger', 14, 'pull_from_tally', 'SFA-LED-PULL-14-20260908123143', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\",\"dealer_tally_name\":\"Tangail Hardware\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:31:53', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:31:43', '2026-09-08 06:31:57', '2026-09-08 06:31:57'),
(146, 'ledger', 18, 'pull_from_tally', 'SFA-LED-PULL-18-20260908123143', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\",\"dealer_tally_name\":\"Dealer 1\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:32:12', NULL, '{\"rows\":[{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000003-0\",\"ledger_name\":\"Dealer 1\",\"voucher_date\":\"2026-09-01\",\"voucher_type\":\"Sales\",\"voucher_number\":\"1\",\"narration\":null,\"debit_amount\":125000,\"credit_amount\":0}]}', NULL, NULL, '2026-09-08 06:31:43', '2026-09-08 06:32:13', '2026-09-08 06:32:13'),
(147, 'ledger', 19, 'pull_from_tally', 'SFA-LED-PULL-19-20260908123143', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\",\"dealer_tally_name\":\"Dealer 2\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:32:12', NULL, '{\"rows\":[{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000001-1\",\"ledger_name\":\"Dealer 2\",\"voucher_date\":\"2026-09-01\",\"voucher_type\":\"Journal\",\"voucher_number\":\"1\",\"narration\":null,\"debit_amount\":0,\"credit_amount\":50}]}', NULL, NULL, '2026-09-08 06:31:43', '2026-09-08 06:32:13', '2026-09-08 06:32:13'),
(148, 'ledger', 20, 'pull_from_tally', 'SFA-LED-PULL-20-20260908123143', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010a\",\"dealer_tally_name\":\"Korim\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:32:12', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:31:43', '2026-09-08 06:32:13', '2026-09-08 06:32:13'),
(149, 'ledger', 21, 'pull_from_tally', 'SFA-LED-PULL-21-20260908123143', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000109\",\"dealer_tally_name\":\"Rohim Dealer\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:32:12', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:31:43', '2026-09-08 06:32:14', '2026-09-08 06:32:14'),
(150, 'ledger', 22, 'pull_from_tally', 'SFA-LED-PULL-22-20260908123143', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010b\",\"dealer_tally_name\":\"Emran\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:32:12', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:31:43', '2026-09-08 06:32:14', '2026-09-08 06:32:14'),
(151, 'ledger', 23, 'pull_from_tally', 'SFA-LED-PULL-23-20260908123143', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010c\",\"dealer_tally_name\":\"Iftakher\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:32:12', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:31:43', '2026-09-08 06:32:14', '2026-09-08 06:32:14'),
(152, 'dealer', NULL, 'pull_from_tally', 'SFA-PULL-dealer-20260908124009', '[]', 'success', 1, '2026-09-08 06:40:23', NULL, '{\"rows\":[{\"name\":\"Buriganga Distribution House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\"},{\"name\":\"Dealer 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\"},{\"name\":\"Dealer 2\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\"},{\"name\":\"Dhamrai Water Solutions\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\"},{\"name\":\"Emran\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010b\"},{\"name\":\"Gazi Appliance Corner\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\"},{\"name\":\"Iftakher\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010c\"},{\"name\":\"Keraniganj Hardware & Motors\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\"},{\"name\":\"Korim\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010a\"},{\"name\":\"Rohim Dealer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000109\"},{\"name\":\"Savar Pump House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\"},{\"name\":\"Tangail Hardware\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\"}],\"group\":\"Sundry Debtors\",\"applied\":{\"linked\":0,\"already\":12,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 06:40:09', '2026-09-08 06:40:23', '2026-09-08 06:40:23'),
(153, 'depot', NULL, 'pull_from_tally', 'SFA-PULL-depot-20260908124132', '[]', 'success', 1, '2026-09-08 06:41:53', NULL, '{\"rows\":[{\"name\":\"Main Depot\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000da\"},{\"name\":\"Main Location\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000063\"},{\"name\":\"Warehouse\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d4\"}],\"applied\":{\"linked\":0,\"already\":3,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 06:41:32', '2026-09-08 06:41:53', '2026-09-08 06:41:53'),
(154, 'product', NULL, 'pull_from_tally', 'SFA-PULL-product-20260908125013', '[]', 'success', 1, '2026-09-08 06:50:29', NULL, '{\"rows\":[{\"name\":\"0.75 HP YC Motor 1450 RPM (Classic)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000108\"},{\"name\":\"10.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000104\"},{\"name\":\"1.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000107\"},{\"name\":\"15.0 HP Y2 Motor 950 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000103\"},{\"name\":\"3 HP YC Motor 1450 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000106\"},{\"name\":\"5.5 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000105\"},{\"name\":\"A-01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fc\"},{\"name\":\"A-25S - Gazi Smiss Induction Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f9\"},{\"name\":\"A-37G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fa\"},{\"name\":\"A-40G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f8\"},{\"name\":\"CNP CDLF Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e7\"},{\"name\":\"CNP SZ Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e8\"},{\"name\":\"E-720B - Gazi Smiss Induction & Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fb\"},{\"name\":\"EG-732S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ef\"},{\"name\":\"EG-750S - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f4\"},{\"name\":\"Eifel EAD Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e6\"},{\"name\":\"Eifel EA Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e5\"},{\"name\":\"GA-AF-23 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000100\"},{\"name\":\"GA-AF-25 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000101\"},{\"name\":\"GA-AF-27 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000102\"},{\"name\":\"Gazi Fire Fighting Pump Complete Set\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e9\"},{\"name\":\"Gazi Gas Stove (Industrial Line)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000eb\"},{\"name\":\"Gazi Motors YC Series\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ea\"},{\"name\":\"Gazi Self-Priming Jet Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e1\"},{\"name\":\"Gazi Standardized Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e2\"},{\"name\":\"Gazi Tubewell\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ec\"},{\"name\":\"GEO-03 - Gazi Smiss Electric Oven 30 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fd\"},{\"name\":\"GEO-04 - Gazi Smiss Electric Oven 40 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fe\"},{\"name\":\"GEO-05 - Gazi Smiss Electric Oven 50 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ff\"},{\"name\":\"GH-8204M - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f1\"},{\"name\":\"GST-102C - Gazi Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ee\"},{\"name\":\"HY-712BT - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f3\"},{\"name\":\"HY-716BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f2\"},{\"name\":\"HY-729CP - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f6\"},{\"name\":\"HY-736BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f5\"},{\"name\":\"IF-HL01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f7\"},{\"name\":\"Pentax Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e3\"},{\"name\":\"Pentax Submersible Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e4\"},{\"name\":\"Pump Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d2\"},{\"name\":\"TG-206 - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ed\"},{\"name\":\"TG-213S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f0\"},{\"name\":\"TV Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d3\"}],\"applied\":{\"linked\":0,\"already\":42,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 06:50:13', '2026-09-08 06:50:29', '2026-09-08 06:50:29');
INSERT INTO `sync_queues` (`id`, `entity_type`, `entity_id`, `direction`, `external_reference`, `payload`, `status`, `attempt_count`, `last_attempt_at`, `next_attempt_at`, `response`, `error_code`, `error_message`, `created_at`, `updated_at`, `completed_at`) VALUES
(155, 'dealer', NULL, 'pull_from_tally', 'SFA-PULL-dealer-20260908125835', '[]', 'success', 1, '2026-09-08 06:58:59', NULL, '{\"rows\":[{\"name\":\"Buriganga Distribution House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\"},{\"name\":\"Dealer 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\"},{\"name\":\"Dealer 2\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\"},{\"name\":\"Dhamrai Water Solutions\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\"},{\"name\":\"Emran\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010b\"},{\"name\":\"Gazi Appliance Corner\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\"},{\"name\":\"Iftakher\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010d\"},{\"name\":\"Keraniganj Hardware & Motors\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\"},{\"name\":\"Korim\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010a\"},{\"name\":\"Rohim Dealer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000109\"},{\"name\":\"Savar Pump House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\"},{\"name\":\"Tangail Hardware\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\"}],\"group\":\"Sundry Debtors\",\"applied\":{\"linked\":0,\"already\":11,\"imported\":[\"Iftakher\"],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 06:58:35', '2026-09-08 06:58:59', '2026-09-08 06:58:59'),
(156, 'retailer', NULL, 'pull_from_tally', 'SFA-PULL-retailer-20260908125835', '[]', 'success', 1, '2026-09-08 06:58:59', NULL, '{\"rows\":[],\"skipped\":\"TALLY_RETAILER_LEDGER_GROUP is not set to a group of its own, so retailer ledgers cannot be told apart from dealer ledgers. Set it to the group the customer files retailers under (it must differ from the dealers\' group).\",\"applied\":{\"linked\":0,\"already\":0,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 06:58:35', '2026-09-08 06:59:00', '2026-09-08 06:59:00'),
(157, 'product', NULL, 'pull_from_tally', 'SFA-PULL-product-20260908125835', '[]', 'success', 1, '2026-09-08 06:58:59', NULL, '{\"rows\":[{\"name\":\"0.75 HP YC Motor 1450 RPM (Classic)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000108\"},{\"name\":\"10.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000104\"},{\"name\":\"1.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000107\"},{\"name\":\"15.0 HP Y2 Motor 950 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000103\"},{\"name\":\"3 HP YC Motor 1450 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000106\"},{\"name\":\"5.5 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000105\"},{\"name\":\"A-01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fc\"},{\"name\":\"A-25S - Gazi Smiss Induction Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f9\"},{\"name\":\"A-37G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fa\"},{\"name\":\"A-40G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f8\"},{\"name\":\"CNP CDLF Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e7\"},{\"name\":\"CNP SZ Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e8\"},{\"name\":\"E-720B - Gazi Smiss Induction & Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fb\"},{\"name\":\"EG-732S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ef\"},{\"name\":\"EG-750S - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f4\"},{\"name\":\"Eifel EAD Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e6\"},{\"name\":\"Eifel EA Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e5\"},{\"name\":\"GA-AF-23 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000100\"},{\"name\":\"GA-AF-25 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000101\"},{\"name\":\"GA-AF-27 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000102\"},{\"name\":\"Gazi Fire Fighting Pump Complete Set\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e9\"},{\"name\":\"Gazi Gas Stove (Industrial Line)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000eb\"},{\"name\":\"Gazi Motors YC Series\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ea\"},{\"name\":\"Gazi Self-Priming Jet Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e1\"},{\"name\":\"Gazi Standardized Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e2\"},{\"name\":\"Gazi Tubewell\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ec\"},{\"name\":\"GEO-03 - Gazi Smiss Electric Oven 30 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fd\"},{\"name\":\"GEO-04 - Gazi Smiss Electric Oven 40 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fe\"},{\"name\":\"GEO-05 - Gazi Smiss Electric Oven 50 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ff\"},{\"name\":\"GH-8204M - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f1\"},{\"name\":\"GST-102C - Gazi Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ee\"},{\"name\":\"HY-712BT - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f3\"},{\"name\":\"HY-716BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f2\"},{\"name\":\"HY-729CP - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f6\"},{\"name\":\"HY-736BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f5\"},{\"name\":\"IF-HL01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f7\"},{\"name\":\"Pentax Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e3\"},{\"name\":\"Pentax Submersible Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e4\"},{\"name\":\"Pump Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d2\"},{\"name\":\"TG-206 - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ed\"},{\"name\":\"TG-213S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f0\"},{\"name\":\"TV Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d3\"}],\"applied\":{\"linked\":0,\"already\":42,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 06:58:35', '2026-09-08 06:59:00', '2026-09-08 06:59:00'),
(158, 'depot', NULL, 'pull_from_tally', 'SFA-PULL-depot-20260908125835', '[]', 'success', 1, '2026-09-08 06:58:59', NULL, '{\"rows\":[{\"name\":\"Main Depot\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000da\"},{\"name\":\"Main Location\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000063\"},{\"name\":\"Warehouse\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d4\"}],\"applied\":{\"linked\":0,\"already\":3,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 06:58:35', '2026-09-08 06:59:00', '2026-09-08 06:59:00'),
(159, 'ledger', 1, 'pull_from_tally', 'SFA-LED-PULL-1-20260908125835', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\",\"dealer_tally_name\":\"Savar Pump House\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:58:59', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:58:35', '2026-09-08 06:59:01', '2026-09-08 06:59:01'),
(160, 'ledger', 2, 'pull_from_tally', 'SFA-LED-PULL-2-20260908125835', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\",\"dealer_tally_name\":\"Keraniganj Hardware & Motors\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:58:59', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:58:35', '2026-09-08 06:59:01', '2026-09-08 06:59:01'),
(161, 'ledger', 3, 'pull_from_tally', 'SFA-LED-PULL-3-20260908125835', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\",\"dealer_tally_name\":\"Dhamrai Water Solutions\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:58:59', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:58:35', '2026-09-08 06:59:02', '2026-09-08 06:59:02'),
(162, 'ledger', 4, 'pull_from_tally', 'SFA-LED-PULL-4-20260908125835', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\",\"dealer_tally_name\":\"Gazi Appliance Corner\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:58:59', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:58:35', '2026-09-08 06:59:02', '2026-09-08 06:59:02'),
(163, 'ledger', 5, 'pull_from_tally', 'SFA-LED-PULL-5-20260908125835', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\",\"dealer_tally_name\":\"Buriganga Distribution House\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:58:59', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:58:35', '2026-09-08 06:59:02', '2026-09-08 06:59:02'),
(164, 'ledger', 14, 'pull_from_tally', 'SFA-LED-PULL-14-20260908125835', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\",\"dealer_tally_name\":\"Tangail Hardware\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:58:59', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:58:35', '2026-09-08 06:59:03', '2026-09-08 06:59:03'),
(165, 'ledger', 18, 'pull_from_tally', 'SFA-LED-PULL-18-20260908125835', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\",\"dealer_tally_name\":\"Dealer 1\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:59:29', NULL, '{\"rows\":[{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000003-0\",\"ledger_name\":\"Dealer 1\",\"voucher_date\":\"2026-09-01\",\"voucher_type\":\"Sales\",\"voucher_number\":\"1\",\"narration\":null,\"debit_amount\":125000,\"credit_amount\":0}]}', NULL, NULL, '2026-09-08 06:58:35', '2026-09-08 06:59:29', '2026-09-08 06:59:29'),
(166, 'ledger', 19, 'pull_from_tally', 'SFA-LED-PULL-19-20260908125835', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\",\"dealer_tally_name\":\"Dealer 2\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:59:29', NULL, '{\"rows\":[{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000001-1\",\"ledger_name\":\"Dealer 2\",\"voucher_date\":\"2026-09-01\",\"voucher_type\":\"Journal\",\"voucher_number\":\"1\",\"narration\":null,\"debit_amount\":0,\"credit_amount\":50}]}', NULL, NULL, '2026-09-08 06:58:35', '2026-09-08 06:59:30', '2026-09-08 06:59:30'),
(167, 'ledger', 20, 'pull_from_tally', 'SFA-LED-PULL-20-20260908125835', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010a\",\"dealer_tally_name\":\"Korim\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:59:29', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:58:35', '2026-09-08 06:59:30', '2026-09-08 06:59:30'),
(168, 'ledger', 21, 'pull_from_tally', 'SFA-LED-PULL-21-20260908125835', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000109\",\"dealer_tally_name\":\"Rohim Dealer\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:59:29', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:58:35', '2026-09-08 06:59:31', '2026-09-08 06:59:31'),
(169, 'ledger', 22, 'pull_from_tally', 'SFA-LED-PULL-22-20260908125835', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010b\",\"dealer_tally_name\":\"Emran\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 06:59:29', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 06:58:35', '2026-09-08 06:59:31', '2026-09-08 06:59:31'),
(170, 'dealer', NULL, 'pull_from_tally', 'SFA-PULL-dealer-20260908130120', '[]', 'success', 1, '2026-09-08 07:29:02', NULL, '{\"rows\":[{\"name\":\"Buriganga Distribution House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\"},{\"name\":\"Dealer 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\"},{\"name\":\"Dealer 2\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\"},{\"name\":\"Dhamrai Water Solutions\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\"},{\"name\":\"Gazi Appliance Corner\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\"},{\"name\":\"Jewel\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010e\"},{\"name\":\"Keraniganj Hardware & Motors\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\"},{\"name\":\"Savar Pump House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\"},{\"name\":\"Tangail Hardware\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\"}],\"group\":\"Sundry Debtors\",\"applied\":{\"linked\":0,\"already\":8,\"imported\":[\"Jewel\"],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 07:01:20', '2026-09-08 07:29:03', '2026-09-08 07:29:03'),
(171, 'retailer', NULL, 'pull_from_tally', 'SFA-PULL-retailer-20260908130120', '[]', 'success', 1, '2026-09-08 07:29:02', NULL, '{\"rows\":[],\"skipped\":\"TALLY_RETAILER_LEDGER_GROUP is not set to a group of its own, so retailer ledgers cannot be told apart from dealer ledgers. Set it to the group the customer files retailers under (it must differ from the dealers\' group).\",\"applied\":{\"linked\":0,\"already\":0,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 07:01:20', '2026-09-08 07:29:03', '2026-09-08 07:29:03'),
(172, 'product', NULL, 'pull_from_tally', 'SFA-PULL-product-20260908130120', '[]', 'success', 1, '2026-09-08 07:29:02', NULL, '{\"rows\":[{\"name\":\"0.75 HP YC Motor 1450 RPM (Classic)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000108\"},{\"name\":\"10.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000104\"},{\"name\":\"1.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000107\"},{\"name\":\"15.0 HP Y2 Motor 950 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000103\"},{\"name\":\"3 HP YC Motor 1450 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000106\"},{\"name\":\"5.5 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000105\"},{\"name\":\"A-01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fc\"},{\"name\":\"A-25S - Gazi Smiss Induction Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f9\"},{\"name\":\"A-37G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fa\"},{\"name\":\"A-40G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f8\"},{\"name\":\"CNP CDLF Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e7\"},{\"name\":\"CNP SZ Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e8\"},{\"name\":\"E-720B - Gazi Smiss Induction & Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fb\"},{\"name\":\"EG-732S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ef\"},{\"name\":\"EG-750S - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f4\"},{\"name\":\"Eifel EAD Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e6\"},{\"name\":\"Eifel EA Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e5\"},{\"name\":\"GA-AF-23 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000100\"},{\"name\":\"GA-AF-25 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000101\"},{\"name\":\"GA-AF-27 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000102\"},{\"name\":\"Gazi Fire Fighting Pump Complete Set\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e9\"},{\"name\":\"Gazi Gas Stove (Industrial Line)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000eb\"},{\"name\":\"Gazi Motors YC Series\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ea\"},{\"name\":\"Gazi Self-Priming Jet Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e1\"},{\"name\":\"Gazi Standardized Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e2\"},{\"name\":\"Gazi Tubewell\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ec\"},{\"name\":\"GEO-03 - Gazi Smiss Electric Oven 30 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fd\"},{\"name\":\"GEO-04 - Gazi Smiss Electric Oven 40 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fe\"},{\"name\":\"GEO-05 - Gazi Smiss Electric Oven 50 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ff\"},{\"name\":\"GH-8204M - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f1\"},{\"name\":\"GST-102C - Gazi Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ee\"},{\"name\":\"HY-712BT - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f3\"},{\"name\":\"HY-716BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f2\"},{\"name\":\"HY-729CP - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f6\"},{\"name\":\"HY-736BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f5\"},{\"name\":\"IF-HL01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f7\"},{\"name\":\"Pentax Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e3\"},{\"name\":\"Pentax Submersible Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e4\"},{\"name\":\"Pump Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d2\"},{\"name\":\"TG-206 - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ed\"},{\"name\":\"TG-213S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f0\"},{\"name\":\"TV Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d3\"}],\"applied\":{\"linked\":0,\"already\":42,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 07:01:20', '2026-09-08 07:29:04', '2026-09-08 07:29:04'),
(173, 'depot', NULL, 'pull_from_tally', 'SFA-PULL-depot-20260908130120', '[]', 'success', 1, '2026-09-08 07:29:02', NULL, '{\"rows\":[{\"name\":\"Main Depot\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000da\"},{\"name\":\"Main Location\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000063\"},{\"name\":\"Warehouse\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d4\"}],\"applied\":{\"linked\":0,\"already\":3,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 07:01:20', '2026-09-08 07:29:04', '2026-09-08 07:29:04'),
(174, 'ledger', 1, 'pull_from_tally', 'SFA-LED-PULL-1-20260908130120', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\",\"dealer_tally_name\":\"Savar Pump House\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:29:02', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 07:01:20', '2026-09-08 07:29:05', '2026-09-08 07:29:05'),
(175, 'ledger', 2, 'pull_from_tally', 'SFA-LED-PULL-2-20260908130120', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\",\"dealer_tally_name\":\"Keraniganj Hardware & Motors\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:29:02', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 07:01:20', '2026-09-08 07:29:05', '2026-09-08 07:29:05'),
(176, 'ledger', 3, 'pull_from_tally', 'SFA-LED-PULL-3-20260908130120', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\",\"dealer_tally_name\":\"Dhamrai Water Solutions\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:29:02', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 07:01:20', '2026-09-08 07:29:06', '2026-09-08 07:29:06'),
(177, 'ledger', 4, 'pull_from_tally', 'SFA-LED-PULL-4-20260908130120', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\",\"dealer_tally_name\":\"Gazi Appliance Corner\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:29:02', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 07:01:20', '2026-09-08 07:29:06', '2026-09-08 07:29:06'),
(178, 'ledger', 5, 'pull_from_tally', 'SFA-LED-PULL-5-20260908130120', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\",\"dealer_tally_name\":\"Buriganga Distribution House\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:29:02', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 07:01:20', '2026-09-08 07:29:07', '2026-09-08 07:29:07'),
(179, 'ledger', 14, 'pull_from_tally', 'SFA-LED-PULL-14-20260908130120', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\",\"dealer_tally_name\":\"Tangail Hardware\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:29:02', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 07:01:20', '2026-09-08 07:29:07', '2026-09-08 07:29:07'),
(180, 'ledger', 18, 'pull_from_tally', 'SFA-LED-PULL-18-20260908130120', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\",\"dealer_tally_name\":\"Dealer 1\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:29:32', NULL, '{\"rows\":[{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000003-0\",\"ledger_name\":\"Dealer 1\",\"voucher_date\":\"2026-09-01\",\"voucher_type\":\"Sales\",\"voucher_number\":\"1\",\"narration\":null,\"debit_amount\":125000,\"credit_amount\":0}]}', NULL, NULL, '2026-09-08 07:01:20', '2026-09-08 07:29:32', '2026-09-08 07:29:32'),
(181, 'ledger', 19, 'pull_from_tally', 'SFA-LED-PULL-19-20260908130120', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\",\"dealer_tally_name\":\"Dealer 2\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:29:32', NULL, '{\"rows\":[{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000001-1\",\"ledger_name\":\"Dealer 2\",\"voucher_date\":\"2026-09-01\",\"voucher_type\":\"Journal\",\"voucher_number\":\"1\",\"narration\":null,\"debit_amount\":0,\"credit_amount\":50}]}', NULL, NULL, '2026-09-08 07:01:20', '2026-09-08 07:29:33', '2026-09-08 07:29:33'),
(182, 'ledger', 20, 'pull_from_tally', 'SFA-LED-PULL-20-20260908130120', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010a\",\"dealer_tally_name\":\"Korim\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:29:32', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 07:01:20', '2026-09-08 07:29:33', '2026-09-08 07:29:33'),
(183, 'ledger', 21, 'pull_from_tally', 'SFA-LED-PULL-21-20260908130120', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000109\",\"dealer_tally_name\":\"Rohim Dealer\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:29:32', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 07:01:20', '2026-09-08 07:29:33', '2026-09-08 07:29:33'),
(184, 'ledger', 22, 'pull_from_tally', 'SFA-LED-PULL-22-20260908130120', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010b\",\"dealer_tally_name\":\"Emran\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:29:32', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 07:01:20', '2026-09-08 07:29:34', '2026-09-08 07:29:34'),
(185, 'ledger', 24, 'pull_from_tally', 'SFA-LED-PULL-24-20260908130120', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010d\",\"dealer_tally_name\":\"Iftakher\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:29:32', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 07:01:20', '2026-09-08 07:29:34', '2026-09-08 07:29:34'),
(186, 'dealer', NULL, 'pull_from_tally', 'SFA-PULL-dealer-20260908132953', '[]', 'success', 1, '2026-09-08 07:30:02', NULL, '{\"rows\":[{\"name\":\"Buriganga Distribution House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\"},{\"name\":\"Dealer 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\"},{\"name\":\"Dealer 2\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\"},{\"name\":\"Dhamrai Water Solutions\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\"},{\"name\":\"Gazi Appliance Corner\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\"},{\"name\":\"Jewel\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-0000010e\"},{\"name\":\"Keraniganj Hardware & Motors\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\"},{\"name\":\"Savar Pump House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\"},{\"name\":\"Tangail Hardware\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\"}],\"group\":\"Sundry Debtors\",\"applied\":{\"linked\":0,\"already\":8,\"imported\":[\"Jewel\"],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 07:29:53', '2026-09-08 07:30:02', '2026-09-08 07:30:02'),
(187, 'retailer', NULL, 'pull_from_tally', 'SFA-PULL-retailer-20260908132953', '[]', 'success', 1, '2026-09-08 07:30:02', NULL, '{\"rows\":[],\"skipped\":\"TALLY_RETAILER_LEDGER_GROUP is not set to a group of its own, so retailer ledgers cannot be told apart from dealer ledgers. Set it to the group the customer files retailers under (it must differ from the dealers\' group).\",\"applied\":{\"linked\":0,\"already\":0,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 07:29:53', '2026-09-08 07:30:03', '2026-09-08 07:30:03'),
(188, 'product', NULL, 'pull_from_tally', 'SFA-PULL-product-20260908132953', '[]', 'success', 1, '2026-09-08 07:30:02', NULL, '{\"rows\":[{\"name\":\"0.75 HP YC Motor 1450 RPM (Classic)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000108\"},{\"name\":\"10.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000104\"},{\"name\":\"1.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000107\"},{\"name\":\"15.0 HP Y2 Motor 950 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000103\"},{\"name\":\"3 HP YC Motor 1450 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000106\"},{\"name\":\"5.5 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000105\"},{\"name\":\"A-01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fc\"},{\"name\":\"A-25S - Gazi Smiss Induction Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f9\"},{\"name\":\"A-37G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fa\"},{\"name\":\"A-40G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f8\"},{\"name\":\"CNP CDLF Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e7\"},{\"name\":\"CNP SZ Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e8\"},{\"name\":\"E-720B - Gazi Smiss Induction & Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fb\"},{\"name\":\"EG-732S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ef\"},{\"name\":\"EG-750S - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f4\"},{\"name\":\"Eifel EAD Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e6\"},{\"name\":\"Eifel EA Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e5\"},{\"name\":\"GA-AF-23 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000100\"},{\"name\":\"GA-AF-25 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000101\"},{\"name\":\"GA-AF-27 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000102\"},{\"name\":\"Gazi Fire Fighting Pump Complete Set\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e9\"},{\"name\":\"Gazi Gas Stove (Industrial Line)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000eb\"},{\"name\":\"Gazi Motors YC Series\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ea\"},{\"name\":\"Gazi Self-Priming Jet Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e1\"},{\"name\":\"Gazi Standardized Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e2\"},{\"name\":\"Gazi Tubewell\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ec\"},{\"name\":\"GEO-03 - Gazi Smiss Electric Oven 30 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fd\"},{\"name\":\"GEO-04 - Gazi Smiss Electric Oven 40 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fe\"},{\"name\":\"GEO-05 - Gazi Smiss Electric Oven 50 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ff\"},{\"name\":\"GH-8204M - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f1\"},{\"name\":\"GST-102C - Gazi Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ee\"},{\"name\":\"HY-712BT - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f3\"},{\"name\":\"HY-716BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f2\"},{\"name\":\"HY-729CP - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f6\"},{\"name\":\"HY-736BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f5\"},{\"name\":\"IF-HL01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f7\"},{\"name\":\"Pentax Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e3\"},{\"name\":\"Pentax Submersible Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e4\"},{\"name\":\"Pump Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d2\"},{\"name\":\"TG-206 - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ed\"},{\"name\":\"TG-213S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f0\"},{\"name\":\"TV Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d3\"}],\"applied\":{\"linked\":0,\"already\":42,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 07:29:53', '2026-09-08 07:30:03', '2026-09-08 07:30:03'),
(189, 'depot', NULL, 'pull_from_tally', 'SFA-PULL-depot-20260908132953', '[]', 'success', 1, '2026-09-08 07:30:02', NULL, '{\"rows\":[{\"name\":\"Main Depot\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000da\"},{\"name\":\"Main Location\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000063\"},{\"name\":\"Warehouse\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d4\"}],\"applied\":{\"linked\":0,\"already\":3,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 07:29:53', '2026-09-08 07:30:03', '2026-09-08 07:30:03'),
(190, 'ledger', 1, 'pull_from_tally', 'SFA-LED-PULL-1-20260908132953', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\",\"dealer_tally_name\":\"Savar Pump House\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:30:02', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 07:29:53', '2026-09-08 07:30:04', '2026-09-08 07:30:04'),
(191, 'ledger', 2, 'pull_from_tally', 'SFA-LED-PULL-2-20260908132953', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\",\"dealer_tally_name\":\"Keraniganj Hardware & Motors\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:30:02', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 07:29:53', '2026-09-08 07:30:04', '2026-09-08 07:30:04'),
(192, 'ledger', 3, 'pull_from_tally', 'SFA-LED-PULL-3-20260908132953', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\",\"dealer_tally_name\":\"Dhamrai Water Solutions\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:30:02', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 07:29:53', '2026-09-08 07:30:04', '2026-09-08 07:30:04'),
(193, 'ledger', 4, 'pull_from_tally', 'SFA-LED-PULL-4-20260908132953', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\",\"dealer_tally_name\":\"Gazi Appliance Corner\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:30:02', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 07:29:53', '2026-09-08 07:30:05', '2026-09-08 07:30:05'),
(194, 'ledger', 5, 'pull_from_tally', 'SFA-LED-PULL-5-20260908132953', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\",\"dealer_tally_name\":\"Buriganga Distribution House\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:30:02', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 07:29:53', '2026-09-08 07:30:05', '2026-09-08 07:30:05'),
(195, 'ledger', 14, 'pull_from_tally', 'SFA-LED-PULL-14-20260908132953', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\",\"dealer_tally_name\":\"Tangail Hardware\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:30:02', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 07:29:53', '2026-09-08 07:30:05', '2026-09-08 07:30:05'),
(196, 'ledger', 18, 'pull_from_tally', 'SFA-LED-PULL-18-20260908132953', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\",\"dealer_tally_name\":\"Dealer 1\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:30:32', NULL, '{\"rows\":[{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000003-0\",\"ledger_name\":\"Dealer 1\",\"voucher_date\":\"2026-09-01\",\"voucher_type\":\"Sales\",\"voucher_number\":\"1\",\"narration\":null,\"debit_amount\":125000,\"credit_amount\":0}]}', NULL, NULL, '2026-09-08 07:29:53', '2026-09-08 07:30:32', '2026-09-08 07:30:32'),
(197, 'ledger', 19, 'pull_from_tally', 'SFA-LED-PULL-19-20260908132953', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\",\"dealer_tally_name\":\"Dealer 2\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:30:32', NULL, '{\"rows\":[{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000001-1\",\"ledger_name\":\"Dealer 2\",\"voucher_date\":\"2026-09-01\",\"voucher_type\":\"Journal\",\"voucher_number\":\"1\",\"narration\":null,\"debit_amount\":0,\"credit_amount\":50}]}', NULL, NULL, '2026-09-08 07:29:53', '2026-09-08 07:30:33', '2026-09-08 07:30:33'),
(198, 'dealer', 28, 'push_to_tally', 'SFA-MPUSH-dealer-28', '{\"name\":\"Omar\",\"group\":\"Sundry Debtors\",\"phone\":\"0123456789\",\"email\":null,\"address\":null}', 'success', 1, '2026-09-08 07:30:32', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 07:29:53', '2026-09-08 07:30:33', '2026-09-08 07:30:33'),
(199, 'dealer', NULL, 'pull_from_tally', 'SFA-PULL-dealer-20260908133231', '[]', 'success', 1, '2026-09-08 07:32:32', NULL, '{\"rows\":[{\"name\":\"Buriganga Distribution House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\"},{\"name\":\"Dealer 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\"},{\"name\":\"Dealer 2\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\"},{\"name\":\"Dhamrai Water Solutions\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\"},{\"name\":\"Gazi Appliance Corner\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\"},{\"name\":\"Jewel\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000110\"},{\"name\":\"Keraniganj Hardware & Motors\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\"},{\"name\":\"Savar Pump House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\"},{\"name\":\"Tangail Hardware\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\"}],\"group\":\"Sundry Debtors\",\"applied\":{\"linked\":0,\"already\":8,\"imported\":[\"Jewel\"],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 07:32:31', '2026-09-08 07:32:32', '2026-09-08 07:32:32'),
(200, 'retailer', NULL, 'pull_from_tally', 'SFA-PULL-retailer-20260908133231', '[]', 'success', 1, '2026-09-08 07:32:32', NULL, '{\"rows\":[],\"skipped\":\"TALLY_RETAILER_LEDGER_GROUP is not set to a group of its own, so retailer ledgers cannot be told apart from dealer ledgers. Set it to the group the customer files retailers under (it must differ from the dealers\' group).\",\"applied\":{\"linked\":0,\"already\":0,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 07:32:31', '2026-09-08 07:32:33', '2026-09-08 07:32:33'),
(201, 'product', NULL, 'pull_from_tally', 'SFA-PULL-product-20260908133231', '[]', 'success', 1, '2026-09-08 07:32:32', NULL, '{\"rows\":[{\"name\":\"0.75 HP YC Motor 1450 RPM (Classic)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000108\"},{\"name\":\"10.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000104\"},{\"name\":\"1.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000107\"},{\"name\":\"15.0 HP Y2 Motor 950 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000103\"},{\"name\":\"3 HP YC Motor 1450 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000106\"},{\"name\":\"5.5 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000105\"},{\"name\":\"A-01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fc\"},{\"name\":\"A-25S - Gazi Smiss Induction Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f9\"},{\"name\":\"A-37G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fa\"},{\"name\":\"A-40G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f8\"},{\"name\":\"CNP CDLF Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e7\"},{\"name\":\"CNP SZ Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e8\"},{\"name\":\"E-720B - Gazi Smiss Induction & Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fb\"},{\"name\":\"EG-732S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ef\"},{\"name\":\"EG-750S - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f4\"},{\"name\":\"Eifel EAD Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e6\"},{\"name\":\"Eifel EA Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e5\"},{\"name\":\"GA-AF-23 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000100\"},{\"name\":\"GA-AF-25 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000101\"},{\"name\":\"GA-AF-27 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000102\"},{\"name\":\"Gazi Fire Fighting Pump Complete Set\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e9\"},{\"name\":\"Gazi Gas Stove (Industrial Line)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000eb\"},{\"name\":\"Gazi Motors YC Series\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ea\"},{\"name\":\"Gazi Self-Priming Jet Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e1\"},{\"name\":\"Gazi Standardized Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e2\"},{\"name\":\"Gazi Tubewell\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ec\"},{\"name\":\"GEO-03 - Gazi Smiss Electric Oven 30 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fd\"},{\"name\":\"GEO-04 - Gazi Smiss Electric Oven 40 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fe\"},{\"name\":\"GEO-05 - Gazi Smiss Electric Oven 50 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ff\"},{\"name\":\"GH-8204M - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f1\"},{\"name\":\"GST-102C - Gazi Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ee\"},{\"name\":\"HY-712BT - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f3\"},{\"name\":\"HY-716BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f2\"},{\"name\":\"HY-729CP - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f6\"},{\"name\":\"HY-736BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f5\"},{\"name\":\"IF-HL01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f7\"},{\"name\":\"Pentax Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e3\"},{\"name\":\"Pentax Submersible Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e4\"},{\"name\":\"Pump Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d2\"},{\"name\":\"TG-206 - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ed\"},{\"name\":\"TG-213S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f0\"},{\"name\":\"TV Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d3\"}],\"applied\":{\"linked\":0,\"already\":42,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 07:32:31', '2026-09-08 07:32:33', '2026-09-08 07:32:33'),
(202, 'depot', NULL, 'pull_from_tally', 'SFA-PULL-depot-20260908133231', '[]', 'success', 1, '2026-09-08 07:32:32', NULL, '{\"rows\":[{\"name\":\"Main Depot\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000da\"},{\"name\":\"Main Location\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000063\"},{\"name\":\"Warehouse\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d4\"}],\"applied\":{\"linked\":0,\"already\":3,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 07:32:31', '2026-09-08 07:32:33', '2026-09-08 07:32:33'),
(203, 'ledger', 1, 'pull_from_tally', 'SFA-LED-PULL-1-20260908133231', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\",\"dealer_tally_name\":\"Savar Pump House\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:32:32', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 07:32:31', '2026-09-08 07:32:34', '2026-09-08 07:32:34'),
(204, 'ledger', 2, 'pull_from_tally', 'SFA-LED-PULL-2-20260908133231', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\",\"dealer_tally_name\":\"Keraniganj Hardware & Motors\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:32:32', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 07:32:31', '2026-09-08 07:32:34', '2026-09-08 07:32:34'),
(205, 'ledger', 3, 'pull_from_tally', 'SFA-LED-PULL-3-20260908133231', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\",\"dealer_tally_name\":\"Dhamrai Water Solutions\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:32:32', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 07:32:31', '2026-09-08 07:32:34', '2026-09-08 07:32:34'),
(206, 'ledger', 4, 'pull_from_tally', 'SFA-LED-PULL-4-20260908133231', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\",\"dealer_tally_name\":\"Gazi Appliance Corner\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:32:32', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 07:32:31', '2026-09-08 07:32:35', '2026-09-08 07:32:35'),
(207, 'ledger', 5, 'pull_from_tally', 'SFA-LED-PULL-5-20260908133231', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\",\"dealer_tally_name\":\"Buriganga Distribution House\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:32:32', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 07:32:31', '2026-09-08 07:32:35', '2026-09-08 07:32:35'),
(208, 'ledger', 14, 'pull_from_tally', 'SFA-LED-PULL-14-20260908133231', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\",\"dealer_tally_name\":\"Tangail Hardware\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:32:32', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 07:32:31', '2026-09-08 07:32:35', '2026-09-08 07:32:35'),
(209, 'ledger', 18, 'pull_from_tally', 'SFA-LED-PULL-18-20260908133231', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\",\"dealer_tally_name\":\"Dealer 1\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:33:02', NULL, '{\"rows\":[{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000003-0\",\"ledger_name\":\"Dealer 1\",\"voucher_date\":\"2026-09-01\",\"voucher_type\":\"Sales\",\"voucher_number\":\"1\",\"narration\":null,\"debit_amount\":125000,\"credit_amount\":0}]}', NULL, NULL, '2026-09-08 07:32:31', '2026-09-08 07:33:02', '2026-09-08 07:33:02'),
(210, 'ledger', 19, 'pull_from_tally', 'SFA-LED-PULL-19-20260908133231', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\",\"dealer_tally_name\":\"Dealer 2\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 07:33:02', NULL, '{\"rows\":[{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000001-1\",\"ledger_name\":\"Dealer 2\",\"voucher_date\":\"2026-09-01\",\"voucher_type\":\"Journal\",\"voucher_number\":\"1\",\"narration\":null,\"debit_amount\":0,\"credit_amount\":50}]}', NULL, NULL, '2026-09-08 07:32:31', '2026-09-08 07:33:03', '2026-09-08 07:33:03'),
(211, 'dealer', 31, 'push_to_tally', 'SFA-MPUSH-dealer-31', '{\"name\":\"Omar\",\"group\":\"Sundry Debtors\",\"phone\":\"0123456789\",\"email\":null,\"address\":null}', 'success', 1, '2026-09-08 07:33:02', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 07:32:31', '2026-09-08 07:33:03', '2026-09-08 07:33:03'),
(212, 'dealer', NULL, 'pull_from_tally', 'SFA-PULL-dealer-20260908140930', '[]', 'success', 1, '2026-09-08 08:09:32', NULL, '{\"rows\":[{\"name\":\"Buriganga Distribution House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\"},{\"name\":\"Dealer 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\"},{\"name\":\"Dealer 2\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\"},{\"name\":\"Dhamrai Water Solutions\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\"},{\"name\":\"Gazi Appliance Corner\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\"},{\"name\":\"Jewel\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000112\"},{\"name\":\"Keraniganj Hardware & Motors\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\"},{\"name\":\"Savar Pump House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\"},{\"name\":\"Tangail Hardware\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\"}],\"group\":\"Sundry Debtors\",\"applied\":{\"linked\":0,\"already\":8,\"imported\":[\"Jewel\"],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 08:09:30', '2026-09-08 08:09:33', '2026-09-08 08:09:33'),
(213, 'retailer', NULL, 'pull_from_tally', 'SFA-PULL-retailer-20260908140930', '[]', 'success', 1, '2026-09-08 08:09:32', NULL, '{\"rows\":[],\"skipped\":\"TALLY_RETAILER_LEDGER_GROUP is not set to a group of its own, so retailer ledgers cannot be told apart from dealer ledgers. Set it to the group the customer files retailers under (it must differ from the dealers\' group).\",\"applied\":{\"linked\":0,\"already\":0,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 08:09:30', '2026-09-08 08:09:33', '2026-09-08 08:09:33');
INSERT INTO `sync_queues` (`id`, `entity_type`, `entity_id`, `direction`, `external_reference`, `payload`, `status`, `attempt_count`, `last_attempt_at`, `next_attempt_at`, `response`, `error_code`, `error_message`, `created_at`, `updated_at`, `completed_at`) VALUES
(214, 'product', NULL, 'pull_from_tally', 'SFA-PULL-product-20260908140930', '[]', 'success', 1, '2026-09-08 08:09:32', NULL, '{\"rows\":[{\"name\":\"0.75 HP YC Motor 1450 RPM (Classic)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000108\"},{\"name\":\"10.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000104\"},{\"name\":\"1.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000107\"},{\"name\":\"15.0 HP Y2 Motor 950 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000103\"},{\"name\":\"3 HP YC Motor 1450 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000106\"},{\"name\":\"5.5 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000105\"},{\"name\":\"A-01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fc\"},{\"name\":\"A-25S - Gazi Smiss Induction Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f9\"},{\"name\":\"A-37G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fa\"},{\"name\":\"A-40G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f8\"},{\"name\":\"CNP CDLF Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e7\"},{\"name\":\"CNP SZ Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e8\"},{\"name\":\"E-720B - Gazi Smiss Induction & Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fb\"},{\"name\":\"EG-732S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ef\"},{\"name\":\"EG-750S - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f4\"},{\"name\":\"Eifel EAD Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e6\"},{\"name\":\"Eifel EA Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e5\"},{\"name\":\"GA-AF-23 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000100\"},{\"name\":\"GA-AF-25 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000101\"},{\"name\":\"GA-AF-27 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000102\"},{\"name\":\"Gazi Fire Fighting Pump Complete Set\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e9\"},{\"name\":\"Gazi Gas Stove (Industrial Line)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000eb\"},{\"name\":\"Gazi Motors YC Series\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ea\"},{\"name\":\"Gazi Self-Priming Jet Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e1\"},{\"name\":\"Gazi Standardized Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e2\"},{\"name\":\"Gazi Tubewell\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ec\"},{\"name\":\"GEO-03 - Gazi Smiss Electric Oven 30 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fd\"},{\"name\":\"GEO-04 - Gazi Smiss Electric Oven 40 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fe\"},{\"name\":\"GEO-05 - Gazi Smiss Electric Oven 50 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ff\"},{\"name\":\"GH-8204M - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f1\"},{\"name\":\"GST-102C - Gazi Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ee\"},{\"name\":\"HY-712BT - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f3\"},{\"name\":\"HY-716BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f2\"},{\"name\":\"HY-729CP - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f6\"},{\"name\":\"HY-736BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f5\"},{\"name\":\"IF-HL01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f7\"},{\"name\":\"Pentax Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e3\"},{\"name\":\"Pentax Submersible Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e4\"},{\"name\":\"Pump Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d2\"},{\"name\":\"TG-206 - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ed\"},{\"name\":\"TG-213S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f0\"},{\"name\":\"TV Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d3\"}],\"applied\":{\"linked\":0,\"already\":42,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 08:09:30', '2026-09-08 08:09:34', '2026-09-08 08:09:34'),
(215, 'depot', NULL, 'pull_from_tally', 'SFA-PULL-depot-20260908140930', '[]', 'success', 1, '2026-09-08 08:09:32', NULL, '{\"rows\":[{\"name\":\"Main Depot\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000da\"},{\"name\":\"Main Location\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000063\"},{\"name\":\"Warehouse\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d4\"}],\"applied\":{\"linked\":0,\"already\":3,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-08 08:09:30', '2026-09-08 08:09:34', '2026-09-08 08:09:34'),
(216, 'ledger', 1, 'pull_from_tally', 'SFA-LED-PULL-1-20260908140930', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\",\"dealer_tally_name\":\"Savar Pump House\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 08:09:32', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 08:09:30', '2026-09-08 08:09:35', '2026-09-08 08:09:35'),
(217, 'ledger', 2, 'pull_from_tally', 'SFA-LED-PULL-2-20260908140930', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\",\"dealer_tally_name\":\"Keraniganj Hardware & Motors\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 08:09:32', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 08:09:30', '2026-09-08 08:09:35', '2026-09-08 08:09:35'),
(218, 'ledger', 3, 'pull_from_tally', 'SFA-LED-PULL-3-20260908140930', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\",\"dealer_tally_name\":\"Dhamrai Water Solutions\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 08:09:32', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 08:09:30', '2026-09-08 08:09:36', '2026-09-08 08:09:36'),
(219, 'ledger', 4, 'pull_from_tally', 'SFA-LED-PULL-4-20260908140930', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\",\"dealer_tally_name\":\"Gazi Appliance Corner\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 08:09:32', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 08:09:30', '2026-09-08 08:09:36', '2026-09-08 08:09:36'),
(220, 'ledger', 5, 'pull_from_tally', 'SFA-LED-PULL-5-20260908140930', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\",\"dealer_tally_name\":\"Buriganga Distribution House\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 08:09:32', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 08:09:30', '2026-09-08 08:09:36', '2026-09-08 08:09:36'),
(221, 'ledger', 14, 'pull_from_tally', 'SFA-LED-PULL-14-20260908140930', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\",\"dealer_tally_name\":\"Tangail Hardware\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 08:09:32', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-08 08:09:30', '2026-09-08 08:09:37', '2026-09-08 08:09:37'),
(222, 'ledger', 18, 'pull_from_tally', 'SFA-LED-PULL-18-20260908140930', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\",\"dealer_tally_name\":\"Dealer 1\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 08:10:03', NULL, '{\"rows\":[{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000003-0\",\"ledger_name\":\"Dealer 1\",\"voucher_date\":\"2026-09-01\",\"voucher_type\":\"Sales\",\"voucher_number\":\"1\",\"narration\":null,\"debit_amount\":125000,\"credit_amount\":0}]}', NULL, NULL, '2026-09-08 08:09:30', '2026-09-08 08:10:03', '2026-09-08 08:10:03'),
(223, 'ledger', 19, 'pull_from_tally', 'SFA-LED-PULL-19-20260908140930', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\",\"dealer_tally_name\":\"Dealer 2\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-08\"}', 'success', 1, '2026-09-08 08:10:03', NULL, '{\"rows\":[{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000001-1\",\"ledger_name\":\"Dealer 2\",\"voucher_date\":\"2026-09-01\",\"voucher_type\":\"Journal\",\"voucher_number\":\"1\",\"narration\":null,\"debit_amount\":0,\"credit_amount\":50}]}', NULL, NULL, '2026-09-08 08:09:30', '2026-09-08 08:10:04', '2026-09-08 08:10:04'),
(224, 'dealer', 33, 'push_to_tally', 'SFA-MPUSH-dealer-33', '{\"name\":\"Omar\",\"group\":\"Sundry Debtors\",\"phone\":\"01723456789\",\"email\":null,\"address\":null}', 'success', 1, '2026-09-08 08:10:03', NULL, '{\"RESPONSE\":{\"CREATED\":1,\"ALTERED\":0,\"DELETED\":0,\"LASTVCHID\":0,\"LASTMID\":0,\"COMBINED\":0,\"IGNORED\":0,\"ERRORS\":0,\"CANCELLED\":0,\"EXCEPTIONS\":0},\"mapped\":true}', NULL, NULL, '2026-09-08 08:09:30', '2026-09-08 08:10:04', '2026-09-08 08:10:04'),
(225, 'dealer', NULL, 'pull_from_tally', 'SFA-PULL-dealer-20260921170524', '[]', 'success', 1, '2026-09-21 11:12:08', NULL, '{\"rows\":[{\"name\":\"Buriganga Distribution House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\"},{\"name\":\"Dealer 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\"},{\"name\":\"Dealer 2\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\"},{\"name\":\"Dhamrai Water Solutions\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\"},{\"name\":\"Gazi Appliance Corner\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\"},{\"name\":\"Jewel\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000112\"},{\"name\":\"Keraniganj Hardware & Motors\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\"},{\"name\":\"Omar\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000113\"},{\"name\":\"Savar Pump House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\"},{\"name\":\"Tangail Hardware\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\"}],\"group\":\"Sundry Debtors\",\"applied\":{\"linked\":0,\"already\":10,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-21 11:05:24', '2026-09-21 11:12:09', '2026-09-21 11:12:09'),
(226, 'retailer', NULL, 'pull_from_tally', 'SFA-PULL-retailer-20260921170524', '[]', 'success', 1, '2026-09-21 11:12:08', NULL, '{\"rows\":[],\"skipped\":\"TALLY_RETAILER_LEDGER_GROUP is not set to a group of its own, so retailer ledgers cannot be told apart from dealer ledgers. Set it to the group the customer files retailers under (it must differ from the dealers\' group).\",\"applied\":{\"linked\":0,\"already\":0,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-21 11:05:24', '2026-09-21 11:12:09', '2026-09-21 11:12:09'),
(227, 'product', NULL, 'pull_from_tally', 'SFA-PULL-product-20260921170524', '[]', 'success', 1, '2026-09-21 11:12:08', NULL, '{\"rows\":[{\"name\":\"0.75 HP YC Motor 1450 RPM (Classic)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000108\"},{\"name\":\"10.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000104\"},{\"name\":\"1.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000107\"},{\"name\":\"15.0 HP Y2 Motor 950 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000103\"},{\"name\":\"3 HP YC Motor 1450 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000106\"},{\"name\":\"5.5 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000105\"},{\"name\":\"A-01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fc\"},{\"name\":\"A-25S - Gazi Smiss Induction Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f9\"},{\"name\":\"A-37G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fa\"},{\"name\":\"A-40G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f8\"},{\"name\":\"CNP CDLF Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e7\"},{\"name\":\"CNP SZ Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e8\"},{\"name\":\"E-720B - Gazi Smiss Induction & Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fb\"},{\"name\":\"EG-732S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ef\"},{\"name\":\"EG-750S - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f4\"},{\"name\":\"Eifel EAD Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e6\"},{\"name\":\"Eifel EA Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e5\"},{\"name\":\"GA-AF-23 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000100\"},{\"name\":\"GA-AF-25 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000101\"},{\"name\":\"GA-AF-27 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000102\"},{\"name\":\"Gazi Fire Fighting Pump Complete Set\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e9\"},{\"name\":\"Gazi Gas Stove (Industrial Line)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000eb\"},{\"name\":\"Gazi Motors YC Series\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ea\"},{\"name\":\"Gazi Self-Priming Jet Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e1\"},{\"name\":\"Gazi Standardized Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e2\"},{\"name\":\"Gazi Tubewell\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ec\"},{\"name\":\"GEO-03 - Gazi Smiss Electric Oven 30 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fd\"},{\"name\":\"GEO-04 - Gazi Smiss Electric Oven 40 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fe\"},{\"name\":\"GEO-05 - Gazi Smiss Electric Oven 50 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ff\"},{\"name\":\"GH-8204M - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f1\"},{\"name\":\"GST-102C - Gazi Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ee\"},{\"name\":\"HY-712BT - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f3\"},{\"name\":\"HY-716BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f2\"},{\"name\":\"HY-729CP - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f6\"},{\"name\":\"HY-736BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f5\"},{\"name\":\"IF-HL01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f7\"},{\"name\":\"Pentax Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e3\"},{\"name\":\"Pentax Submersible Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e4\"},{\"name\":\"Pump Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d2\"},{\"name\":\"TG-206 - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ed\"},{\"name\":\"TG-213S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f0\"},{\"name\":\"TV Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d3\"}],\"applied\":{\"linked\":0,\"already\":42,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-21 11:05:24', '2026-09-21 11:12:09', '2026-09-21 11:12:09'),
(228, 'depot', NULL, 'pull_from_tally', 'SFA-PULL-depot-20260921170524', '[]', 'success', 1, '2026-09-21 11:12:08', NULL, '{\"rows\":[{\"name\":\"Main Depot\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000da\"},{\"name\":\"Main Location\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000063\"},{\"name\":\"Warehouse\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d4\"}],\"applied\":{\"linked\":0,\"already\":3,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-21 11:05:24', '2026-09-21 11:12:10', '2026-09-21 11:12:10'),
(229, 'ledger', 1, 'pull_from_tally', 'SFA-LED-PULL-1-20260921170524', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\",\"dealer_tally_name\":\"Savar Pump House\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-21\"}', 'success', 1, '2026-09-21 11:12:08', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-21 11:05:24', '2026-09-21 11:12:10', '2026-09-21 11:12:10'),
(230, 'ledger', 2, 'pull_from_tally', 'SFA-LED-PULL-2-20260921170524', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\",\"dealer_tally_name\":\"Keraniganj Hardware & Motors\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-21\"}', 'success', 1, '2026-09-21 11:12:08', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-21 11:05:24', '2026-09-21 11:12:11', '2026-09-21 11:12:11'),
(231, 'ledger', 3, 'pull_from_tally', 'SFA-LED-PULL-3-20260921170524', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\",\"dealer_tally_name\":\"Dhamrai Water Solutions\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-21\"}', 'success', 1, '2026-09-21 11:12:08', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-21 11:05:24', '2026-09-21 11:12:11', '2026-09-21 11:12:11'),
(232, 'ledger', 4, 'pull_from_tally', 'SFA-LED-PULL-4-20260921170524', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\",\"dealer_tally_name\":\"Gazi Appliance Corner\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-21\"}', 'success', 1, '2026-09-21 11:12:08', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-21 11:05:24', '2026-09-21 11:12:11', '2026-09-21 11:12:11'),
(233, 'ledger', 5, 'pull_from_tally', 'SFA-LED-PULL-5-20260921170524', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\",\"dealer_tally_name\":\"Buriganga Distribution House\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-21\"}', 'success', 1, '2026-09-21 11:12:08', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-21 11:05:24', '2026-09-21 11:12:12', '2026-09-21 11:12:12'),
(234, 'ledger', 14, 'pull_from_tally', 'SFA-LED-PULL-14-20260921170524', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\",\"dealer_tally_name\":\"Tangail Hardware\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-21\"}', 'success', 1, '2026-09-21 11:12:08', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-21 11:05:24', '2026-09-21 11:12:12', '2026-09-21 11:12:12'),
(235, 'ledger', 18, 'pull_from_tally', 'SFA-LED-PULL-18-20260921170524', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\",\"dealer_tally_name\":\"Dealer 1\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-21\"}', 'success', 1, '2026-09-21 11:12:38', NULL, '{\"rows\":[{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000003-0\",\"ledger_name\":\"Dealer 1\",\"voucher_date\":\"2026-09-01\",\"voucher_type\":\"Sales\",\"voucher_number\":\"1\",\"narration\":null,\"debit_amount\":125000,\"credit_amount\":0}]}', NULL, NULL, '2026-09-21 11:05:24', '2026-09-21 11:12:39', '2026-09-21 11:12:39'),
(236, 'ledger', 19, 'pull_from_tally', 'SFA-LED-PULL-19-20260921170524', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\",\"dealer_tally_name\":\"Dealer 2\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-21\"}', 'success', 1, '2026-09-21 11:12:38', NULL, '{\"rows\":[{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000001-1\",\"ledger_name\":\"Dealer 2\",\"voucher_date\":\"2026-09-01\",\"voucher_type\":\"Journal\",\"voucher_number\":\"1\",\"narration\":null,\"debit_amount\":0,\"credit_amount\":50}]}', NULL, NULL, '2026-09-21 11:05:24', '2026-09-21 11:12:39', '2026-09-21 11:12:39'),
(237, 'ledger', 33, 'pull_from_tally', 'SFA-LED-PULL-33-20260921170524', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000113\",\"dealer_tally_name\":\"Omar\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-21\"}', 'success', 1, '2026-09-21 11:12:38', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-21 11:05:24', '2026-09-21 11:12:40', '2026-09-21 11:12:40'),
(238, 'ledger', 34, 'pull_from_tally', 'SFA-LED-PULL-34-20260921170524', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000112\",\"dealer_tally_name\":\"Jewel\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-21\"}', 'success', 1, '2026-09-21 11:12:38', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-21 11:05:24', '2026-09-21 11:12:40', '2026-09-21 11:12:40'),
(239, 'dealer', NULL, 'pull_from_tally', 'SFA-PULL-dealer-20260921171245', '[]', 'success', 1, '2026-09-21 11:13:08', NULL, '{\"rows\":[{\"name\":\"Buriganga Distribution House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\"},{\"name\":\"Dealer 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\"},{\"name\":\"Dealer 2\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\"},{\"name\":\"Dhamrai Water Solutions\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\"},{\"name\":\"Gazi Appliance Corner\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\"},{\"name\":\"Jewel\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000112\"},{\"name\":\"Keraniganj Hardware & Motors\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\"},{\"name\":\"Omar\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000113\"},{\"name\":\"Savar Pump House\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\"},{\"name\":\"Tangail Hardware\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\"}],\"group\":\"Sundry Debtors\",\"applied\":{\"linked\":0,\"already\":10,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-21 11:12:45', '2026-09-21 11:13:09', '2026-09-21 11:13:09'),
(240, 'retailer', NULL, 'pull_from_tally', 'SFA-PULL-retailer-20260921171245', '[]', 'success', 1, '2026-09-21 11:13:08', NULL, '{\"rows\":[],\"skipped\":\"TALLY_RETAILER_LEDGER_GROUP is not set to a group of its own, so retailer ledgers cannot be told apart from dealer ledgers. Set it to the group the customer files retailers under (it must differ from the dealers\' group).\",\"applied\":{\"linked\":0,\"already\":0,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-21 11:12:45', '2026-09-21 11:13:09', '2026-09-21 11:13:09'),
(241, 'product', NULL, 'pull_from_tally', 'SFA-PULL-product-20260921171245', '[]', 'success', 1, '2026-09-21 11:13:08', NULL, '{\"rows\":[{\"name\":\"0.75 HP YC Motor 1450 RPM (Classic)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000108\"},{\"name\":\"10.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000104\"},{\"name\":\"1.0 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000107\"},{\"name\":\"15.0 HP Y2 Motor 950 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000103\"},{\"name\":\"3 HP YC Motor 1450 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000106\"},{\"name\":\"5.5 HP Y2 Motor 2800 RPM\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000105\"},{\"name\":\"A-01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fc\"},{\"name\":\"A-25S - Gazi Smiss Induction Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f9\"},{\"name\":\"A-37G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fa\"},{\"name\":\"A-40G - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f8\"},{\"name\":\"CNP CDLF Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e7\"},{\"name\":\"CNP SZ Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e8\"},{\"name\":\"E-720B - Gazi Smiss Induction & Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fb\"},{\"name\":\"EG-732S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ef\"},{\"name\":\"EG-750S - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f4\"},{\"name\":\"Eifel EAD Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e6\"},{\"name\":\"Eifel EA Series Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e5\"},{\"name\":\"GA-AF-23 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000100\"},{\"name\":\"GA-AF-25 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000101\"},{\"name\":\"GA-AF-27 - Gazi Smiss Air Fryer\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000102\"},{\"name\":\"Gazi Fire Fighting Pump Complete Set\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e9\"},{\"name\":\"Gazi Gas Stove (Industrial Line)\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000eb\"},{\"name\":\"Gazi Motors YC Series\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ea\"},{\"name\":\"Gazi Self-Priming Jet Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e1\"},{\"name\":\"Gazi Standardized Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e2\"},{\"name\":\"Gazi Tubewell\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ec\"},{\"name\":\"GEO-03 - Gazi Smiss Electric Oven 30 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fd\"},{\"name\":\"GEO-04 - Gazi Smiss Electric Oven 40 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000fe\"},{\"name\":\"GEO-05 - Gazi Smiss Electric Oven 50 Liter\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ff\"},{\"name\":\"GH-8204M - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f1\"},{\"name\":\"GST-102C - Gazi Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ee\"},{\"name\":\"HY-712BT - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f3\"},{\"name\":\"HY-716BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f2\"},{\"name\":\"HY-729CP - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f6\"},{\"name\":\"HY-736BV - Gazi Smiss Kitchen Hood\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f5\"},{\"name\":\"IF-HL01 - Gazi Smiss Infrared Cooker\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f7\"},{\"name\":\"Pentax Centrifugal Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e3\"},{\"name\":\"Pentax Submersible Pump\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e4\"},{\"name\":\"Pump Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d2\"},{\"name\":\"TG-206 - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000ed\"},{\"name\":\"TG-213S - Gazi Smiss Gas Stove\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000f0\"},{\"name\":\"TV Model 1\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d3\"}],\"applied\":{\"linked\":0,\"already\":42,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-21 11:12:45', '2026-09-21 11:13:10', '2026-09-21 11:13:10'),
(242, 'depot', NULL, 'pull_from_tally', 'SFA-PULL-depot-20260921171245', '[]', 'success', 1, '2026-09-21 11:13:08', NULL, '{\"rows\":[{\"name\":\"Main Depot\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000da\"},{\"name\":\"Main Location\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000063\"},{\"name\":\"Warehouse\",\"guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d4\"}],\"applied\":{\"linked\":0,\"already\":3,\"imported\":[],\"conflicts\":[],\"unmatched\":[],\"deleted_here\":[]}}', NULL, NULL, '2026-09-21 11:12:45', '2026-09-21 11:13:10', '2026-09-21 11:13:10'),
(243, 'ledger', 1, 'pull_from_tally', 'SFA-LED-PULL-1-20260921171245', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000db\",\"dealer_tally_name\":\"Savar Pump House\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-21\"}', 'success', 1, '2026-09-21 11:13:08', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-21 11:12:45', '2026-09-21 11:13:10', '2026-09-21 11:13:10'),
(244, 'ledger', 2, 'pull_from_tally', 'SFA-LED-PULL-2-20260921171245', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dc\",\"dealer_tally_name\":\"Keraniganj Hardware & Motors\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-21\"}', 'success', 1, '2026-09-21 11:13:08', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-21 11:12:45', '2026-09-21 11:13:11', '2026-09-21 11:13:11'),
(245, 'ledger', 3, 'pull_from_tally', 'SFA-LED-PULL-3-20260921171245', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000dd\",\"dealer_tally_name\":\"Dhamrai Water Solutions\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-21\"}', 'success', 1, '2026-09-21 11:13:08', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-21 11:12:45', '2026-09-21 11:13:11', '2026-09-21 11:13:11'),
(246, 'ledger', 4, 'pull_from_tally', 'SFA-LED-PULL-4-20260921171245', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000de\",\"dealer_tally_name\":\"Gazi Appliance Corner\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-21\"}', 'success', 1, '2026-09-21 11:13:08', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-21 11:12:45', '2026-09-21 11:13:12', '2026-09-21 11:13:12'),
(247, 'ledger', 5, 'pull_from_tally', 'SFA-LED-PULL-5-20260921171245', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000df\",\"dealer_tally_name\":\"Buriganga Distribution House\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-21\"}', 'success', 1, '2026-09-21 11:13:08', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-21 11:12:45', '2026-09-21 11:13:12', '2026-09-21 11:13:12'),
(248, 'ledger', 14, 'pull_from_tally', 'SFA-LED-PULL-14-20260921171245', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000e0\",\"dealer_tally_name\":\"Tangail Hardware\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-21\"}', 'success', 1, '2026-09-21 11:13:08', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-21 11:12:45', '2026-09-21 11:13:12', '2026-09-21 11:13:12'),
(249, 'ledger', 18, 'pull_from_tally', 'SFA-LED-PULL-18-20260921171245', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d5\",\"dealer_tally_name\":\"Dealer 1\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-21\"}', 'success', 1, '2026-09-21 11:13:38', NULL, '{\"rows\":[{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000003-0\",\"ledger_name\":\"Dealer 1\",\"voucher_date\":\"2026-09-01\",\"voucher_type\":\"Sales\",\"voucher_number\":\"1\",\"narration\":null,\"debit_amount\":125000,\"credit_amount\":0}]}', NULL, NULL, '2026-09-21 11:12:45', '2026-09-21 11:13:39', '2026-09-21 11:13:39'),
(250, 'ledger', 19, 'pull_from_tally', 'SFA-LED-PULL-19-20260921171245', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-000000d6\",\"dealer_tally_name\":\"Dealer 2\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-21\"}', 'success', 1, '2026-09-21 11:13:38', NULL, '{\"rows\":[{\"tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000001-1\",\"ledger_name\":\"Dealer 2\",\"voucher_date\":\"2026-09-01\",\"voucher_type\":\"Journal\",\"voucher_number\":\"1\",\"narration\":null,\"debit_amount\":0,\"credit_amount\":50}]}', NULL, NULL, '2026-09-21 11:12:45', '2026-09-21 11:13:39', '2026-09-21 11:13:39'),
(251, 'ledger', 33, 'pull_from_tally', 'SFA-LED-PULL-33-20260921171245', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000113\",\"dealer_tally_name\":\"Omar\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-21\"}', 'success', 1, '2026-09-21 11:13:38', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-21 11:12:45', '2026-09-21 11:13:39', '2026-09-21 11:13:39'),
(252, 'ledger', 34, 'pull_from_tally', 'SFA-LED-PULL-34-20260921171245', '{\"dealer_tally_guid\":\"d530416d-350a-4df9-bc3c-06943927816a-00000112\",\"dealer_tally_name\":\"Jewel\",\"from_date\":\"2026-09-01\",\"to_date\":\"2026-09-21\"}', 'success', 1, '2026-09-21 11:13:38', NULL, '{\"rows\":[]}', NULL, NULL, '2026-09-21 11:12:45', '2026-09-21 11:13:40', '2026-09-21 11:13:40');

-- --------------------------------------------------------

--
-- Table structure for table `tally_connections`
--

CREATE TABLE `tally_connections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `connection_name` varchar(255) NOT NULL,
  `tally_company_name` varchar(255) NOT NULL,
  `tally_company_guid` varchar(255) DEFAULT NULL,
  `host` varchar(255) NOT NULL,
  `port` int(10) UNSIGNED NOT NULL DEFAULT 9000,
  `protocol` varchar(255) NOT NULL DEFAULT 'http',
  `api_format` varchar(255) NOT NULL DEFAULT 'xml',
  `sync_agent_id` varchar(255) DEFAULT NULL,
  `sync_agent_token` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_heartbeat_at` timestamp NULL DEFAULT NULL,
  `last_successful_sync_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tally_connections`
--

INSERT INTO `tally_connections` (`id`, `connection_name`, `tally_company_name`, `tally_company_guid`, `host`, `port`, `protocol`, `api_format`, `sync_agent_id`, `sync_agent_token`, `is_active`, `last_heartbeat_at`, `last_successful_sync_at`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 'GDN Tally (Primary)', 'GDN Tally', NULL, 'localhost', 9000, 'http', 'xml', '69844c04-b0ea-4b45-9a2f-338aac290762', '8cb8400885d35d850bc4fe78cc41fe59a69228e94f1329511faa8f26ac893373', 1, '2026-09-21 11:33:09', '2026-09-21 11:27:08', 1, 1, NULL, NULL, '2026-09-07 05:19:46', '2026-09-21 11:33:09');

-- --------------------------------------------------------

--
-- Table structure for table `tally_mappings`
--

CREATE TABLE `tally_mappings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `entity_type` varchar(255) NOT NULL,
  `sfa_id` bigint(20) UNSIGNED NOT NULL,
  `tally_guid` varchar(255) DEFAULT NULL,
  `tally_name` varchar(255) DEFAULT NULL,
  `tally_alter_id` varchar(255) DEFAULT NULL,
  `last_synced_at` timestamp NULL DEFAULT NULL,
  `sync_status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tally_mappings`
--

INSERT INTO `tally_mappings` (`id`, `entity_type`, `sfa_id`, `tally_guid`, `tally_name`, `tally_alter_id`, `last_synced_at`, `sync_status`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(10, 'dealer', 18, 'd530416d-350a-4df9-bc3c-06943927816a-000000d5', 'Dealer 1', NULL, '2026-09-08 03:53:13', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 03:53:13', '2026-09-08 03:53:13'),
(11, 'dealer', 19, 'd530416d-350a-4df9-bc3c-06943927816a-000000d6', 'Dealer 2', NULL, '2026-09-08 03:53:13', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 03:53:13', '2026-09-08 03:53:13'),
(12, 'product', 44, 'd530416d-350a-4df9-bc3c-06943927816a-000000d2', 'Pump Model 1', NULL, '2026-09-08 03:53:14', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 03:53:14', '2026-09-08 03:53:14'),
(13, 'product', 45, 'd530416d-350a-4df9-bc3c-06943927816a-000000d3', 'TV Model 1', NULL, '2026-09-08 03:53:14', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 03:53:14', '2026-09-08 03:53:14'),
(14, 'depot', 4, 'd530416d-350a-4df9-bc3c-06943927816a-00000063', 'Main Location', NULL, '2026-09-08 03:53:14', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 03:53:14', '2026-09-08 03:53:14'),
(15, 'depot', 5, 'd530416d-350a-4df9-bc3c-06943927816a-000000d4', 'Warehouse', NULL, '2026-09-08 03:53:14', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 03:53:14', '2026-09-08 03:53:14'),
(16, 'depot', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000da', 'Main Depot', NULL, '2026-09-08 04:36:51', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:36:51', '2026-09-08 04:36:51'),
(17, 'dealer', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000db', 'Savar Pump House', NULL, '2026-09-08 04:36:51', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:36:51', '2026-09-08 04:36:51'),
(18, 'dealer', 2, 'd530416d-350a-4df9-bc3c-06943927816a-000000dc', 'Keraniganj Hardware & Motors', NULL, '2026-09-08 04:57:49', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:57:49', '2026-09-08 04:57:49'),
(19, 'dealer', 3, 'd530416d-350a-4df9-bc3c-06943927816a-000000dd', 'Dhamrai Water Solutions', NULL, '2026-09-08 04:57:50', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:57:50', '2026-09-08 04:57:50'),
(20, 'dealer', 4, 'd530416d-350a-4df9-bc3c-06943927816a-000000de', 'Gazi Appliance Corner', NULL, '2026-09-08 04:57:50', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:57:50', '2026-09-08 04:57:50'),
(21, 'dealer', 5, 'd530416d-350a-4df9-bc3c-06943927816a-000000df', 'Buriganga Distribution House', NULL, '2026-09-08 04:57:50', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:57:50', '2026-09-08 04:57:50'),
(22, 'dealer', 14, 'd530416d-350a-4df9-bc3c-06943927816a-000000e0', 'Tangail Hardware', NULL, '2026-09-08 04:57:51', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:57:51', '2026-09-08 04:57:51'),
(23, 'product', 1, 'd530416d-350a-4df9-bc3c-06943927816a-000000e1', 'Gazi Self-Priming Jet Pump', NULL, '2026-09-08 04:57:51', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:57:51', '2026-09-08 04:57:51'),
(24, 'product', 2, 'd530416d-350a-4df9-bc3c-06943927816a-000000e2', 'Gazi Standardized Centrifugal Pump', NULL, '2026-09-08 04:57:52', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:57:52', '2026-09-08 04:57:52'),
(25, 'product', 3, 'd530416d-350a-4df9-bc3c-06943927816a-000000e3', 'Pentax Centrifugal Pump', NULL, '2026-09-08 04:57:52', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:57:52', '2026-09-08 04:57:52'),
(26, 'product', 4, 'd530416d-350a-4df9-bc3c-06943927816a-000000e4', 'Pentax Submersible Pump', NULL, '2026-09-08 04:57:53', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:57:53', '2026-09-08 04:57:53'),
(27, 'product', 5, 'd530416d-350a-4df9-bc3c-06943927816a-000000e5', 'Eifel EA Series Pump', NULL, '2026-09-08 04:57:53', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:57:53', '2026-09-08 04:57:53'),
(28, 'product', 6, 'd530416d-350a-4df9-bc3c-06943927816a-000000e6', 'Eifel EAD Series Pump', NULL, '2026-09-08 04:57:54', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:57:54', '2026-09-08 04:57:54'),
(29, 'product', 7, 'd530416d-350a-4df9-bc3c-06943927816a-000000e7', 'CNP CDLF Series Pump', NULL, '2026-09-08 04:57:55', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:57:55', '2026-09-08 04:57:55'),
(30, 'product', 8, 'd530416d-350a-4df9-bc3c-06943927816a-000000e8', 'CNP SZ Series Pump', NULL, '2026-09-08 04:57:55', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:57:55', '2026-09-08 04:57:55'),
(31, 'product', 9, 'd530416d-350a-4df9-bc3c-06943927816a-000000e9', 'Gazi Fire Fighting Pump Complete Set', NULL, '2026-09-08 04:57:56', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:57:56', '2026-09-08 04:57:56'),
(32, 'product', 10, 'd530416d-350a-4df9-bc3c-06943927816a-000000ea', 'Gazi Motors YC Series', NULL, '2026-09-08 04:57:56', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:57:56', '2026-09-08 04:57:56'),
(33, 'product', 11, 'd530416d-350a-4df9-bc3c-06943927816a-000000eb', 'Gazi Gas Stove (Industrial Line)', NULL, '2026-09-08 04:57:57', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:57:57', '2026-09-08 04:57:57'),
(34, 'product', 12, 'd530416d-350a-4df9-bc3c-06943927816a-000000ec', 'Gazi Tubewell', NULL, '2026-09-08 04:57:57', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:57:57', '2026-09-08 04:57:57'),
(35, 'product', 13, 'd530416d-350a-4df9-bc3c-06943927816a-000000ed', 'TG-206 - Gazi Smiss Gas Stove', NULL, '2026-09-08 04:57:58', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:57:58', '2026-09-08 04:57:58'),
(36, 'product', 14, 'd530416d-350a-4df9-bc3c-06943927816a-000000ee', 'GST-102C - Gazi Gas Stove', NULL, '2026-09-08 04:57:58', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:57:58', '2026-09-08 04:57:58'),
(37, 'product', 15, 'd530416d-350a-4df9-bc3c-06943927816a-000000ef', 'EG-732S - Gazi Smiss Gas Stove', NULL, '2026-09-08 04:57:58', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:57:58', '2026-09-08 04:57:58'),
(38, 'product', 16, 'd530416d-350a-4df9-bc3c-06943927816a-000000f0', 'TG-213S - Gazi Smiss Gas Stove', NULL, '2026-09-08 04:57:59', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:57:59', '2026-09-08 04:57:59'),
(39, 'product', 17, 'd530416d-350a-4df9-bc3c-06943927816a-000000f1', 'GH-8204M - Gazi Smiss Gas Stove', NULL, '2026-09-08 04:58:00', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:58:00', '2026-09-08 04:58:00'),
(40, 'product', 18, 'd530416d-350a-4df9-bc3c-06943927816a-000000f2', 'HY-716BV - Gazi Smiss Kitchen Hood', NULL, '2026-09-08 04:58:00', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:58:00', '2026-09-08 04:58:00'),
(41, 'product', 19, 'd530416d-350a-4df9-bc3c-06943927816a-000000f3', 'HY-712BT - Gazi Smiss Kitchen Hood', NULL, '2026-09-08 04:58:01', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:58:01', '2026-09-08 04:58:01'),
(42, 'product', 20, 'd530416d-350a-4df9-bc3c-06943927816a-000000f4', 'EG-750S - Gazi Smiss Kitchen Hood', NULL, '2026-09-08 04:58:01', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:58:01', '2026-09-08 04:58:01'),
(43, 'product', 21, 'd530416d-350a-4df9-bc3c-06943927816a-000000f5', 'HY-736BV - Gazi Smiss Kitchen Hood', NULL, '2026-09-08 04:58:01', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:58:01', '2026-09-08 04:58:01'),
(44, 'product', 22, 'd530416d-350a-4df9-bc3c-06943927816a-000000f6', 'HY-729CP - Gazi Smiss Kitchen Hood', NULL, '2026-09-08 04:58:02', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:58:02', '2026-09-08 04:58:02'),
(45, 'product', 23, 'd530416d-350a-4df9-bc3c-06943927816a-000000f7', 'IF-HL01 - Gazi Smiss Infrared Cooker', NULL, '2026-09-08 04:58:02', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:58:02', '2026-09-08 04:58:02'),
(46, 'product', 24, 'd530416d-350a-4df9-bc3c-06943927816a-000000f8', 'A-40G - Gazi Smiss Infrared Cooker', NULL, '2026-09-08 04:58:03', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:58:03', '2026-09-08 04:58:03'),
(47, 'product', 25, 'd530416d-350a-4df9-bc3c-06943927816a-000000f9', 'A-25S - Gazi Smiss Induction Cooker', NULL, '2026-09-08 04:58:03', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:58:03', '2026-09-08 04:58:03'),
(48, 'product', 26, 'd530416d-350a-4df9-bc3c-06943927816a-000000fa', 'A-37G - Gazi Smiss Infrared Cooker', NULL, '2026-09-08 04:58:04', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:58:04', '2026-09-08 04:58:04'),
(49, 'product', 27, 'd530416d-350a-4df9-bc3c-06943927816a-000000fb', 'E-720B - Gazi Smiss Induction & Infrared Cooker', NULL, '2026-09-08 04:58:04', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:58:04', '2026-09-08 04:58:04'),
(50, 'product', 28, 'd530416d-350a-4df9-bc3c-06943927816a-000000fc', 'A-01 - Gazi Smiss Infrared Cooker', NULL, '2026-09-08 04:58:05', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:58:05', '2026-09-08 04:58:05'),
(51, 'product', 29, 'd530416d-350a-4df9-bc3c-06943927816a-000000fd', 'GEO-03 - Gazi Smiss Electric Oven 30 Liter', NULL, '2026-09-08 04:58:05', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:58:05', '2026-09-08 04:58:05'),
(52, 'product', 30, 'd530416d-350a-4df9-bc3c-06943927816a-000000fe', 'GEO-04 - Gazi Smiss Electric Oven 40 Liter', NULL, '2026-09-08 04:58:06', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:58:06', '2026-09-08 04:58:06'),
(53, 'product', 31, 'd530416d-350a-4df9-bc3c-06943927816a-000000ff', 'GEO-05 - Gazi Smiss Electric Oven 50 Liter', NULL, '2026-09-08 04:58:06', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:58:06', '2026-09-08 04:58:06'),
(54, 'product', 32, 'd530416d-350a-4df9-bc3c-06943927816a-00000100', 'GA-AF-23 - Gazi Smiss Air Fryer', NULL, '2026-09-08 04:58:07', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:58:07', '2026-09-08 04:58:07'),
(55, 'product', 33, 'd530416d-350a-4df9-bc3c-06943927816a-00000101', 'GA-AF-25 - Gazi Smiss Air Fryer', NULL, '2026-09-08 04:58:07', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:58:07', '2026-09-08 04:58:07'),
(56, 'product', 34, 'd530416d-350a-4df9-bc3c-06943927816a-00000102', 'GA-AF-27 - Gazi Smiss Air Fryer', NULL, '2026-09-08 04:58:08', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:58:08', '2026-09-08 04:58:08'),
(57, 'product', 35, 'd530416d-350a-4df9-bc3c-06943927816a-00000103', '15.0 HP Y2 Motor 950 RPM', NULL, '2026-09-08 04:58:08', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:58:08', '2026-09-08 04:58:08'),
(58, 'product', 36, 'd530416d-350a-4df9-bc3c-06943927816a-00000104', '10.0 HP Y2 Motor 2800 RPM', NULL, '2026-09-08 04:58:09', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:58:09', '2026-09-08 04:58:09'),
(59, 'product', 37, 'd530416d-350a-4df9-bc3c-06943927816a-00000105', '5.5 HP Y2 Motor 2800 RPM', NULL, '2026-09-08 04:58:09', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:58:09', '2026-09-08 04:58:09'),
(60, 'product', 38, 'd530416d-350a-4df9-bc3c-06943927816a-00000106', '3 HP YC Motor 1450 RPM', NULL, '2026-09-08 04:58:10', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:58:10', '2026-09-08 04:58:10'),
(61, 'product', 39, 'd530416d-350a-4df9-bc3c-06943927816a-00000107', '1.0 HP Y2 Motor 2800 RPM', NULL, '2026-09-08 04:58:10', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:58:10', '2026-09-08 04:58:10'),
(62, 'product', 40, 'd530416d-350a-4df9-bc3c-06943927816a-00000108', '0.75 HP YC Motor 1450 RPM (Classic)', NULL, '2026-09-08 04:58:11', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 04:58:11', '2026-09-08 04:58:11'),
(63, 'dealer', 21, 'd530416d-350a-4df9-bc3c-06943927816a-00000109', 'Rohim Dealer', NULL, '2026-09-08 05:57:22', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 05:57:22', '2026-09-08 05:57:22'),
(64, 'dealer', 20, 'd530416d-350a-4df9-bc3c-06943927816a-0000010a', 'Korim', NULL, '2026-09-08 05:57:27', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 05:57:27', '2026-09-08 05:57:27'),
(65, 'dealer', 22, 'd530416d-350a-4df9-bc3c-06943927816a-0000010b', 'Emran', NULL, '2026-09-08 06:26:55', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 06:26:55', '2026-09-08 06:26:55'),
(66, 'dealer', 23, 'd530416d-350a-4df9-bc3c-06943927816a-0000010c', 'Iftakher', NULL, '2026-09-08 06:30:12', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 06:30:12', '2026-09-08 06:30:12'),
(67, 'dealer', 24, 'd530416d-350a-4df9-bc3c-06943927816a-0000010d', 'Iftakher', NULL, '2026-09-08 06:58:59', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 06:58:59', '2026-09-08 06:58:59'),
(68, 'dealer', 30, 'd530416d-350a-4df9-bc3c-06943927816a-0000010e', 'Jewel', NULL, '2026-09-08 07:30:02', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 07:29:03', '2026-09-08 07:30:02'),
(69, 'dealer', 28, 'd530416d-350a-4df9-bc3c-06943927816a-0000010f', 'Omar', NULL, '2026-09-08 07:30:33', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 07:30:33', '2026-09-08 07:30:33'),
(70, 'dealer', 32, 'd530416d-350a-4df9-bc3c-06943927816a-00000110', 'Jewel', NULL, '2026-09-08 07:32:32', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 07:32:32', '2026-09-08 07:32:32'),
(71, 'dealer', 31, 'd530416d-350a-4df9-bc3c-06943927816a-00000111', 'Omar', NULL, '2026-09-08 07:33:03', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 07:33:03', '2026-09-08 07:33:03'),
(72, 'dealer', 34, 'd530416d-350a-4df9-bc3c-06943927816a-00000112', 'Jewel', NULL, '2026-09-08 08:09:33', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 08:09:33', '2026-09-08 08:09:33'),
(73, 'dealer', 33, 'd530416d-350a-4df9-bc3c-06943927816a-00000113', 'Omar', NULL, '2026-09-08 08:10:04', 'pending', NULL, NULL, NULL, NULL, '2026-09-08 08:10:04', '2026-09-08 08:10:04');

-- --------------------------------------------------------

--
-- Table structure for table `targets`
--

CREATE TABLE `targets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `month` tinyint(3) UNSIGNED NOT NULL,
  `year` smallint(5) UNSIGNED NOT NULL,
  `order_value_target` decimal(12,2) NOT NULL,
  `collection_target` decimal(12,2) NOT NULL,
  `quantity_target` int(10) UNSIGNED NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `targets`
--

INSERT INTO `targets` (`id`, `user_id`, `month`, `year`, `order_value_target`, `collection_target`, `quantity_target`, `notes`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 80, 8, 2026, 2856520.11, 1844231.34, 66, NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:36:10', '2026-08-23 05:36:10'),
(2, 80, 7, 2026, 129235.75, 139414.16, 80, NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:36:10', '2026-08-23 05:36:10'),
(3, 80, 6, 2026, 9000.00, 51690.77, 35, NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:36:10', '2026-08-27 03:30:45'),
(4, 81, 8, 2026, 380913.13, 221857.05, 46, NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:36:10', '2026-08-23 05:36:10'),
(5, 81, 7, 2026, 187078.77, 51401.96, 35, NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:36:10', '2026-08-23 05:36:10');

-- --------------------------------------------------------

--
-- Table structure for table `target_items`
--

CREATE TABLE `target_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `target_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `order_target` decimal(12,2) NOT NULL DEFAULT 0.00,
  `collection_target` decimal(12,2) NOT NULL DEFAULT 0.00,
  `quantity_target` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `target_items`
--

INSERT INTO `target_items` (`id`, `target_id`, `product_id`, `order_target`, `collection_target`, `quantity_target`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 5000.00, 1500.00, 20, '2026-08-27 03:30:45', '2026-08-27 03:30:45'),
(2, 3, 2, 4000.00, 1500.00, 15, '2026-08-27 03:30:45', '2026-08-27 03:30:45');

-- --------------------------------------------------------

--
-- Table structure for table `territories`
--

CREATE TABLE `territories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `division_id` bigint(20) UNSIGNED DEFAULT NULL,
  `district_id` bigint(20) UNSIGNED DEFAULT NULL,
  `thana_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `manager_id` bigint(20) UNSIGNED DEFAULT NULL,
  `center_lat` decimal(10,7) DEFAULT NULL,
  `center_lng` decimal(10,7) DEFAULT NULL,
  `boundary` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`boundary`)),
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `territories`
--

INSERT INTO `territories` (`id`, `division_id`, `district_id`, `thana_id`, `name`, `code`, `manager_id`, `center_lat`, `center_lng`, `boundary`, `status`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 6, 47, 365, 'Dhaka - Savar', 'TER-DHK-001', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, '2026-08-23 05:22:02', '2026-08-23 05:22:02'),
(2, 6, 47, 367, 'Dhaka - Keraniganj', 'TER-DHK-002', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, '2026-08-23 05:22:02', '2026-08-23 05:22:02'),
(3, 6, 47, 366, 'Dhaka - Dhamrai', 'TER-DHK-003', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, '2026-08-23 05:22:02', '2026-08-23 05:22:02');

-- --------------------------------------------------------

--
-- Table structure for table `territory_user`
--

CREATE TABLE `territory_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `territory_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `territory_user`
--

INSERT INTO `territory_user` (`id`, `user_id`, `territory_id`, `created_at`, `updated_at`) VALUES
(1, 80, 1, NULL, NULL),
(2, 81, 2, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `thanas`
--

CREATE TABLE `thanas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `district_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `name_bn` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `thanas`
--

INSERT INTO `thanas` (`id`, `district_id`, `name`, `name_bn`, `status`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Debidwar', 'দেবিদ্বার', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(2, 1, 'Barura', 'বরুড়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(3, 1, 'Brahmanpara', 'ব্রাহ্মণপাড়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(4, 1, 'Chandina', 'চান্দিনা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(5, 1, 'Chauddagram', 'চৌদ্দগ্রাম', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(6, 1, 'Daudkandi', 'দাউদকান্দি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(7, 1, 'Homna', 'হোমনা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(8, 1, 'Laksam', 'লাকসাম', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(9, 1, 'Muradnagar', 'মুরাদনগর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(10, 1, 'Nangalkot', 'নাঙ্গলকোট', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(11, 1, 'Comilla Sadar', 'কুমিল্লা সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(12, 1, 'Meghna', 'মেঘনা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(13, 1, 'Monohargonj', 'মনোহরগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(14, 1, 'Sadarsouth', 'সদর দক্ষিণ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(15, 1, 'Titas', 'তিতাস', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(16, 1, 'Burichang', 'বুড়িচং', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(17, 1, 'Lalmai', 'লালমাই', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(18, 2, 'Chhagalnaiya', 'ছাগলনাইয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(19, 2, 'Feni Sadar', 'ফেনী সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(20, 2, 'Sonagazi', 'সোনাগাজী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(21, 2, 'Fulgazi', 'ফুলগাজী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(22, 2, 'Parshuram', 'পরশুরাম', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(23, 2, 'Daganbhuiyan', 'দাগনভূঞা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(24, 3, 'Brahmanbaria Sadar', 'ব্রাহ্মণবাড়িয়া সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(25, 3, 'Kasba', 'কসবা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(26, 3, 'Nasirnagar', 'নাসিরনগর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(27, 3, 'Sarail', 'সরাইল', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(28, 3, 'Ashuganj', 'আশুগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(29, 3, 'Akhaura', 'আখাউড়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(30, 3, 'Nabinagar', 'নবীনগর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(31, 3, 'Bancharampur', 'বাঞ্ছারামপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(32, 3, 'Bijoynagar', 'বিজয়নগর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(33, 4, 'Rangamati Sadar', 'রাঙ্গামাটি সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(34, 4, 'Kaptai', 'কাপ্তাই', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(35, 4, 'Kawkhali', 'কাউখালী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(36, 4, 'Baghaichari', 'বাঘাইছড়ি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(37, 4, 'Barkal', 'বরকল', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(38, 4, 'Langadu', 'লংগদু', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(39, 4, 'Rajasthali', 'রাজস্থলী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(40, 4, 'Belaichari', 'বিলাইছড়ি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(41, 4, 'Juraichari', 'জুরাছড়ি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(42, 4, 'Naniarchar', 'নানিয়ারচর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(43, 5, 'Noakhali Sadar', 'নোয়াখালী সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(44, 5, 'Companiganj', 'কোম্পানীগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(45, 5, 'Begumganj', 'বেগমগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(46, 5, 'Hatia', 'হাতিয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(47, 5, 'Subarnachar', 'সুবর্ণচর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(48, 5, 'Kabirhat', 'কবিরহাট', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(49, 5, 'Senbug', 'সেনবাগ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(50, 5, 'Chatkhil', 'চাটখিল', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(51, 5, 'Sonaimori', 'সোনাইমুড়ী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(52, 6, 'Haimchar', 'হাইমচর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(53, 6, 'Kachua', 'কচুয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(54, 6, 'Shahrasti', 'শাহরাস্তি	', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(55, 6, 'Chandpur Sadar', 'চাঁদপুর সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(56, 6, 'Matlab South', 'মতলব দক্ষিণ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(57, 6, 'Hajiganj', 'হাজীগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(58, 6, 'Matlab North', 'মতলব উত্তর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(59, 6, 'Faridgonj', 'ফরিদগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(60, 7, 'Lakshmipur Sadar', 'লক্ষ্মীপুর সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(61, 7, 'Kamalnagar', 'কমলনগর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(62, 7, 'Raipur', 'রায়পুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(63, 7, 'Ramgati', 'রামগতি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(64, 7, 'Ramganj', 'রামগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(65, 8, 'Rangunia', 'রাঙ্গুনিয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(66, 8, 'Sitakunda', 'সীতাকুন্ড', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(67, 8, 'Mirsharai', 'মীরসরাই', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(68, 8, 'Patiya', 'পটিয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(69, 8, 'Sandwip', 'সন্দ্বীপ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(70, 8, 'Banshkhali', 'বাঁশখালী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(71, 8, 'Boalkhali', 'বোয়ালখালী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(72, 8, 'Anwara', 'আনোয়ারা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(73, 8, 'Chandanaish', 'চন্দনাইশ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(74, 8, 'Satkania', 'সাতকানিয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(75, 8, 'Lohagara', 'লোহাগাড়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(76, 8, 'Hathazari', 'হাটহাজারী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(77, 8, 'Fatikchhari', 'ফটিকছড়ি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(78, 8, 'Raozan', 'রাউজান', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(79, 8, 'Karnafuli', 'কর্ণফুলী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(80, 9, 'Coxsbazar Sadar', 'কক্সবাজার সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(81, 9, 'Chakaria', 'চকরিয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(82, 9, 'Kutubdia', 'কুতুবদিয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(83, 9, 'Ukhiya', 'উখিয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(84, 9, 'Moheshkhali', 'মহেশখালী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(85, 9, 'Pekua', 'পেকুয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(86, 9, 'Ramu', 'রামু', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(87, 9, 'Teknaf', 'টেকনাফ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(88, 10, 'Khagrachhari Sadar', 'খাগড়াছড়ি সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(89, 10, 'Dighinala', 'দিঘীনালা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(90, 10, 'Panchari', 'পানছড়ি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(91, 10, 'Laxmichhari', 'লক্ষীছড়ি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(92, 10, 'Mohalchari', 'মহালছড়ি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(93, 10, 'Manikchari', 'মানিকছড়ি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(94, 10, 'Ramgarh', 'রামগড়', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(95, 10, 'Matiranga', 'মাটিরাঙ্গা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(96, 10, 'Guimara', 'গুইমারা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(97, 11, 'Bandarban Sadar', 'বান্দরবান সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(98, 11, 'Alikadam', 'আলীকদম', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(99, 11, 'Naikhongchhari', 'নাইক্ষ্যংছড়ি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(100, 11, 'Rowangchhari', 'রোয়াংছড়ি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(101, 11, 'Lama', 'লামা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(102, 11, 'Ruma', 'রুমা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(103, 11, 'Thanchi', 'থানচি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(104, 12, 'Belkuchi', 'বেলকুচি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(105, 12, 'Chauhali', 'চৌহালি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(106, 12, 'Kamarkhand', 'কামারখন্দ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(107, 12, 'Kazipur', 'কাজীপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(108, 12, 'Raigonj', 'রায়গঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:09', '2026-08-22 03:16:09'),
(109, 12, 'Shahjadpur', 'শাহজাদপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(110, 12, 'Sirajganj Sadar', 'সিরাজগঞ্জ সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(111, 12, 'Tarash', 'তাড়াশ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(112, 12, 'Ullapara', 'উল্লাপাড়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(113, 13, 'Sujanagar', 'সুজানগর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(114, 13, 'Ishurdi', 'ঈশ্বরদী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(115, 13, 'Bhangura', 'ভাঙ্গুড়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(116, 13, 'Pabna Sadar', 'পাবনা সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(117, 13, 'Bera', 'বেড়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(118, 13, 'Atghoria', 'আটঘরিয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(119, 13, 'Chatmohar', 'চাটমোহর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(120, 13, 'Santhia', 'সাঁথিয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(121, 13, 'Faridpur', 'ফরিদপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(122, 14, 'Kahaloo', 'কাহালু', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(123, 14, 'Bogra Sadar', 'বগুড়া সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(124, 14, 'Shariakandi', 'সারিয়াকান্দি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(125, 14, 'Shajahanpur', 'শাজাহানপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(126, 14, 'Dupchanchia', 'দুপচাচিঁয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(127, 14, 'Adamdighi', 'আদমদিঘি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(128, 14, 'Nondigram', 'নন্দিগ্রাম', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(129, 14, 'Sonatala', 'সোনাতলা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(130, 14, 'Dhunot', 'ধুনট', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(131, 14, 'Gabtali', 'গাবতলী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(132, 14, 'Sherpur', 'শেরপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(133, 14, 'Shibganj', 'শিবগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(134, 15, 'Paba', 'পবা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(135, 15, 'Durgapur', 'দুর্গাপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(136, 15, 'Mohonpur', 'মোহনপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(137, 15, 'Charghat', 'চারঘাট', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(138, 15, 'Puthia', 'পুঠিয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(139, 15, 'Bagha', 'বাঘা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(140, 15, 'Godagari', 'গোদাগাড়ী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(141, 15, 'Tanore', 'তানোর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(142, 15, 'Bagmara', 'বাগমারা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(143, 16, 'Natore Sadar', 'নাটোর সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(144, 16, 'Singra', 'সিংড়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(145, 16, 'Baraigram', 'বড়াইগ্রাম', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(146, 16, 'Bagatipara', 'বাগাতিপাড়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(147, 16, 'Lalpur', 'লালপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(148, 16, 'Gurudaspur', 'গুরুদাসপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(149, 16, 'Naldanga', 'নলডাঙ্গা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(150, 17, 'Akkelpur', 'আক্কেলপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(151, 17, 'Kalai', 'কালাই', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(152, 17, 'Khetlal', 'ক্ষেতলাল', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(153, 17, 'Panchbibi', 'পাঁচবিবি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(154, 17, 'Joypurhat Sadar', 'জয়পুরহাট সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(155, 18, 'Chapainawabganj Sadar', 'চাঁপাইনবাবগঞ্জ সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(156, 18, 'Gomostapur', 'গোমস্তাপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(157, 18, 'Nachol', 'নাচোল', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(158, 18, 'Bholahat', 'ভোলাহাট', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(159, 18, 'Shibganj', 'শিবগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(160, 19, 'Mohadevpur', 'মহাদেবপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(161, 19, 'Badalgachi', 'বদলগাছী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(162, 19, 'Patnitala', 'পত্নিতলা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(163, 19, 'Dhamoirhat', 'ধামইরহাট', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(164, 19, 'Niamatpur', 'নিয়ামতপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(165, 19, 'Manda', 'মান্দা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(166, 19, 'Atrai', 'আত্রাই', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(167, 19, 'Raninagar', 'রাণীনগর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(168, 19, 'Naogaon Sadar', 'নওগাঁ সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(169, 19, 'Porsha', 'পোরশা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(170, 19, 'Sapahar', 'সাপাহার', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(171, 20, 'Manirampur', 'মণিরামপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(172, 20, 'Abhaynagar', 'অভয়নগর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(173, 20, 'Bagherpara', 'বাঘারপাড়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(174, 20, 'Chougachha', 'চৌগাছা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(175, 20, 'Jhikargacha', 'ঝিকরগাছা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(176, 20, 'Keshabpur', 'কেশবপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(177, 20, 'Jessore Sadar', 'যশোর সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(178, 20, 'Sharsha', 'শার্শা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(179, 21, 'Assasuni', 'আশাশুনি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(180, 21, 'Debhata', 'দেবহাটা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(181, 21, 'Kalaroa', 'কলারোয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(182, 21, 'Satkhira Sadar', 'সাতক্ষীরা সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(183, 21, 'Shyamnagar', 'শ্যামনগর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(184, 21, 'Tala', 'তালা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(185, 21, 'Kaliganj', 'কালিগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(186, 22, 'Mujibnagar', 'মুজিবনগর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(187, 22, 'Meherpur Sadar', 'মেহেরপুর সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(188, 22, 'Gangni', 'গাংনী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(189, 23, 'Narail Sadar', 'নড়াইল সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(190, 23, 'Lohagara', 'লোহাগড়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(191, 23, 'Kalia', 'কালিয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(192, 24, 'Chuadanga Sadar', 'চুয়াডাঙ্গা সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(193, 24, 'Alamdanga', 'আলমডাঙ্গা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(194, 24, 'Damurhuda', 'দামুড়হুদা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(195, 24, 'Jibannagar', 'জীবননগর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(196, 25, 'Kushtia Sadar', 'কুষ্টিয়া সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(197, 25, 'Kumarkhali', 'কুমারখালী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(198, 25, 'Khoksa', 'খোকসা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(199, 25, 'Mirpur', 'মিরপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(200, 25, 'Daulatpur', 'দৌলতপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(201, 25, 'Bheramara', 'ভেড়ামারা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(202, 26, 'Shalikha', 'শালিখা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(203, 26, 'Sreepur', 'শ্রীপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(204, 26, 'Magura Sadar', 'মাগুরা সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(205, 26, 'Mohammadpur', 'মহম্মদপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(206, 27, 'Paikgasa', 'পাইকগাছা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(207, 27, 'Fultola', 'ফুলতলা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(208, 27, 'Digholia', 'দিঘলিয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(209, 27, 'Rupsha', 'রূপসা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(210, 27, 'Terokhada', 'তেরখাদা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(211, 27, 'Dumuria', 'ডুমুরিয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(212, 27, 'Botiaghata', 'বটিয়াঘাটা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(213, 27, 'Dakop', 'দাকোপ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(214, 27, 'Koyra', 'কয়রা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(215, 28, 'Fakirhat', 'ফকিরহাট', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(216, 28, 'Bagerhat Sadar', 'বাগেরহাট সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(217, 28, 'Mollahat', 'মোল্লাহাট', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(218, 28, 'Sarankhola', 'শরণখোলা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(219, 28, 'Rampal', 'রামপাল', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(220, 28, 'Morrelganj', 'মোড়েলগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(221, 28, 'Kachua', 'কচুয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(222, 28, 'Mongla', 'মোংলা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(223, 28, 'Chitalmari', 'চিতলমারী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(224, 29, 'Jhenaidah Sadar', 'ঝিনাইদহ সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(225, 29, 'Shailkupa', 'শৈলকুপা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(226, 29, 'Harinakundu', 'হরিণাকুন্ডু', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(227, 29, 'Kaliganj', 'কালীগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(228, 29, 'Kotchandpur', 'কোটচাঁদপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(229, 29, 'Moheshpur', 'মহেশপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(230, 30, 'Jhalakathi Sadar', 'ঝালকাঠি সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(231, 30, 'Kathalia', 'কাঠালিয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(232, 30, 'Nalchity', 'নলছিটি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(233, 30, 'Rajapur', 'রাজাপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(234, 31, 'Bauphal', 'বাউফল', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(235, 31, 'Patuakhali Sadar', 'পটুয়াখালী সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(236, 31, 'Dumki', 'দুমকি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(237, 31, 'Dashmina', 'দশমিনা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(238, 31, 'Kalapara', 'কলাপাড়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(239, 31, 'Mirzaganj', 'মির্জাগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(240, 31, 'Galachipa', 'গলাচিপা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(241, 31, 'Rangabali', 'রাঙ্গাবালী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(242, 32, 'Pirojpur Sadar', 'পিরোজপুর সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(243, 32, 'Nazirpur', 'নাজিরপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(244, 32, 'Kawkhali', 'কাউখালী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(245, 32, 'Zianagar', 'জিয়ানগর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(246, 32, 'Bhandaria', 'ভান্ডারিয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(247, 32, 'Mathbaria', 'মঠবাড়ীয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(248, 32, 'Nesarabad', 'নেছারাবাদ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(249, 33, 'Barisal Sadar', 'বরিশাল সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(250, 33, 'Bakerganj', 'বাকেরগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(251, 33, 'Babuganj', 'বাবুগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(252, 33, 'Wazirpur', 'উজিরপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(253, 33, 'Banaripara', 'বানারীপাড়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(254, 33, 'Gournadi', 'গৌরনদী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(255, 33, 'Agailjhara', 'আগৈলঝাড়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(256, 33, 'Mehendiganj', 'মেহেন্দিগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(257, 33, 'Muladi', 'মুলাদী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(258, 33, 'Hizla', 'হিজলা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(259, 34, 'Bhola Sadar', 'ভোলা সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(260, 34, 'Borhan Sddin', 'বোরহান উদ্দিন', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(261, 34, 'Charfesson', 'চরফ্যাশন', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(262, 34, 'Doulatkhan', 'দৌলতখান', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(263, 34, 'Monpura', 'মনপুরা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(264, 34, 'Tazumuddin', 'তজুমদ্দিন', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(265, 34, 'Lalmohan', 'লালমোহন', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(266, 35, 'Amtali', 'আমতলী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(267, 35, 'Barguna Sadar', 'বরগুনা সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(268, 35, 'Betagi', 'বেতাগী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(269, 35, 'Bamna', 'বামনা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(270, 35, 'Pathorghata', 'পাথরঘাটা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(271, 35, 'Taltali', 'তালতলি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(272, 36, 'Balaganj', 'বালাগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(273, 36, 'Beanibazar', 'বিয়ানীবাজার', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(274, 36, 'Bishwanath', 'বিশ্বনাথ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(275, 36, 'Companiganj', 'কোম্পানীগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(276, 36, 'Fenchuganj', 'ফেঞ্চুগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(277, 36, 'Golapganj', 'গোলাপগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(278, 36, 'Gowainghat', 'গোয়াইনঘাট', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(279, 36, 'Jaintiapur', 'জৈন্তাপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(280, 36, 'Kanaighat', 'কানাইঘাট', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(281, 36, 'Sylhet Sadar', 'সিলেট সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(282, 36, 'Zakiganj', 'জকিগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(283, 36, 'Dakshinsurma', 'দক্ষিণ সুরমা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(284, 36, 'Osmaninagar', 'ওসমানী নগর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(285, 37, 'Barlekha', 'বড়লেখা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(286, 37, 'Kamolganj', 'কমলগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(287, 37, 'Kulaura', 'কুলাউড়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(288, 37, 'Moulvibazar Sadar', 'মৌলভীবাজার সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(289, 37, 'Rajnagar', 'রাজনগর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(290, 37, 'Sreemangal', 'শ্রীমঙ্গল', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(291, 37, 'Juri', 'জুড়ী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(292, 38, 'Nabiganj', 'নবীগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(293, 38, 'Bahubal', 'বাহুবল', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(294, 38, 'Ajmiriganj', 'আজমিরীগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(295, 38, 'Baniachong', 'বানিয়াচং', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(296, 38, 'Lakhai', 'লাখাই', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(297, 38, 'Chunarughat', 'চুনারুঘাট', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(298, 38, 'Habiganj Sadar', 'হবিগঞ্জ সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(299, 38, 'Madhabpur', 'মাধবপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(300, 39, 'Sunamganj Sadar', 'সুনামগঞ্জ সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(301, 39, 'South Sunamganj', 'দক্ষিণ সুনামগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(302, 39, 'Bishwambarpur', 'বিশ্বম্ভরপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(303, 39, 'Chhatak', 'ছাতক', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(304, 39, 'Jagannathpur', 'জগন্নাথপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(305, 39, 'Dowarabazar', 'দোয়ারাবাজার', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(306, 39, 'Tahirpur', 'তাহিরপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(307, 39, 'Dharmapasha', 'ধর্মপাশা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(308, 39, 'Jamalganj', 'জামালগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(309, 39, 'Shalla', 'শাল্লা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(310, 39, 'Derai', 'দিরাই', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(311, 40, 'Belabo', 'বেলাবো', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(312, 40, 'Monohardi', 'মনোহরদী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(313, 40, 'Narsingdi Sadar', 'নরসিংদী সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(314, 40, 'Palash', 'পলাশ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(315, 40, 'Raipura', 'রায়পুরা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(316, 40, 'Shibpur', 'শিবপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(317, 41, 'Kaliganj', 'কালীগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(318, 41, 'Kaliakair', 'কালিয়াকৈর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(319, 41, 'Kapasia', 'কাপাসিয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(320, 41, 'Gazipur Sadar', 'গাজীপুর সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(321, 41, 'Sreepur', 'শ্রীপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(322, 42, 'Shariatpur Sadar', 'শরিয়তপুর সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(323, 42, 'Naria', 'নড়িয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(324, 42, 'Zajira', 'জাজিরা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(325, 42, 'Gosairhat', 'গোসাইরহাট', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(326, 42, 'Bhedarganj', 'ভেদরগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(327, 42, 'Damudya', 'ডামুড্যা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(328, 43, 'Araihazar', 'আড়াইহাজার', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(329, 43, 'Bandar', 'বন্দর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(330, 43, 'Narayanganj Sadar', 'নারায়নগঞ্জ সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(331, 43, 'Rupganj', 'রূপগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(332, 43, 'Sonargaon', 'সোনারগাঁ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(333, 44, 'Basail', 'বাসাইল', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(334, 44, 'Bhuapur', 'ভুয়াপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(335, 44, 'Delduar', 'দেলদুয়ার', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(336, 44, 'Ghatail', 'ঘাটাইল', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(337, 44, 'Gopalpur', 'গোপালপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(338, 44, 'Madhupur', 'মধুপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(339, 44, 'Mirzapur', 'মির্জাপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(340, 44, 'Nagarpur', 'নাগরপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(341, 44, 'Sakhipur', 'সখিপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(342, 44, 'Tangail Sadar', 'টাঙ্গাইল সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(343, 44, 'Kalihati', 'কালিহাতী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(344, 44, 'Dhanbari', 'ধনবাড়ী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(345, 45, 'Itna', 'ইটনা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(346, 45, 'Katiadi', 'কটিয়াদী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(347, 45, 'Bhairab', 'ভৈরব', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(348, 45, 'Tarail', 'তাড়াইল', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(349, 45, 'Hossainpur', 'হোসেনপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(350, 45, 'Pakundia', 'পাকুন্দিয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(351, 45, 'Kuliarchar', 'কুলিয়ারচর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(352, 45, 'Kishoreganj Sadar', 'কিশোরগঞ্জ সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(353, 45, 'Karimgonj', 'করিমগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(354, 45, 'Bajitpur', 'বাজিতপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(355, 45, 'Austagram', 'অষ্টগ্রাম', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(356, 45, 'Mithamoin', 'মিঠামইন', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(357, 45, 'Nikli', 'নিকলী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(358, 46, 'Harirampur', 'হরিরামপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(359, 46, 'Saturia', 'সাটুরিয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(360, 46, 'Manikganj Sadar', 'মানিকগঞ্জ সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(361, 46, 'Gior', 'ঘিওর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(362, 46, 'Shibaloy', 'শিবালয়', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(363, 46, 'Doulatpur', 'দৌলতপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(364, 46, 'Singiar', 'সিংগাইর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(365, 47, 'Savar', 'সাভার', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(366, 47, 'Dhamrai', 'ধামরাই', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(367, 47, 'Keraniganj', 'কেরাণীগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(368, 47, 'Nawabganj', 'নবাবগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(369, 47, 'Dohar', 'দোহার', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(370, 48, 'Munshiganj Sadar', 'মুন্সিগঞ্জ সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(371, 48, 'Sreenagar', 'শ্রীনগর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(372, 48, 'Sirajdikhan', 'সিরাজদিখান', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(373, 48, 'Louhajanj', 'লৌহজং', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(374, 48, 'Gajaria', 'গজারিয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(375, 48, 'Tongibari', 'টংগীবাড়ি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(376, 49, 'Rajbari Sadar', 'রাজবাড়ী সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(377, 49, 'Goalanda', 'গোয়ালন্দ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(378, 49, 'Pangsa', 'পাংশা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(379, 49, 'Baliakandi', 'বালিয়াকান্দি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(380, 49, 'Kalukhali', 'কালুখালী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(381, 50, 'Madaripur Sadar', 'মাদারীপুর সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(382, 50, 'Shibchar', 'শিবচর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(383, 50, 'Kalkini', 'কালকিনি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(384, 50, 'Rajoir', 'রাজৈর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(385, 51, 'Gopalganj Sadar', 'গোপালগঞ্জ সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(386, 51, 'Kashiani', 'কাশিয়ানী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(387, 51, 'Tungipara', 'টুংগীপাড়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(388, 51, 'Kotalipara', 'কোটালীপাড়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(389, 51, 'Muksudpur', 'মুকসুদপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(390, 52, 'Faridpur Sadar', 'ফরিদপুর সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(391, 52, 'Alfadanga', 'আলফাডাঙ্গা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(392, 52, 'Boalmari', 'বোয়ালমারী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(393, 52, 'Sadarpur', 'সদরপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(394, 52, 'Nagarkanda', 'নগরকান্দা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(395, 52, 'Bhanga', 'ভাঙ্গা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(396, 52, 'Charbhadrasan', 'চরভদ্রাসন', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(397, 52, 'Madhukhali', 'মধুখালী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(398, 52, 'Saltha', 'সালথা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(399, 53, 'Panchagarh Sadar', 'পঞ্চগড় সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(400, 53, 'Debiganj', 'দেবীগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(401, 53, 'Boda', 'বোদা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(402, 53, 'Atwari', 'আটোয়ারী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(403, 53, 'Tetulia', 'তেতুলিয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(404, 54, 'Nawabganj', 'নবাবগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(405, 54, 'Birganj', 'বীরগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(406, 54, 'Ghoraghat', 'ঘোড়াঘাট', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(407, 54, 'Birampur', 'বিরামপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(408, 54, 'Parbatipur', 'পার্বতীপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(409, 54, 'Bochaganj', 'বোচাগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(410, 54, 'Kaharol', 'কাহারোল', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(411, 54, 'Fulbari', 'ফুলবাড়ী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(412, 54, 'Dinajpur Sadar', 'দিনাজপুর সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(413, 54, 'Hakimpur', 'হাকিমপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(414, 54, 'Khansama', 'খানসামা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(415, 54, 'Birol', 'বিরল', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(416, 54, 'Chirirbandar', 'চিরিরবন্দর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(417, 55, 'Lalmonirhat Sadar', 'লালমনিরহাট সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(418, 55, 'Kaliganj', 'কালীগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(419, 55, 'Hatibandha', 'হাতীবান্ধা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(420, 55, 'Patgram', 'পাটগ্রাম', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(421, 55, 'Aditmari', 'আদিতমারী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(422, 56, 'Syedpur', 'সৈয়দপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(423, 56, 'Domar', 'ডোমার', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(424, 56, 'Dimla', 'ডিমলা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(425, 56, 'Jaldhaka', 'জলঢাকা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(426, 56, 'Kishorganj', 'কিশোরগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(427, 56, 'Nilphamari Sadar', 'নীলফামারী সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(428, 57, 'Sadullapur', 'সাদুল্লাপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(429, 57, 'Gaibandha Sadar', 'গাইবান্ধা সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(430, 57, 'Palashbari', 'পলাশবাড়ী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(431, 57, 'Saghata', 'সাঘাটা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(432, 57, 'Gobindaganj', 'গোবিন্দগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(433, 57, 'Sundarganj', 'সুন্দরগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(434, 57, 'Phulchari', 'ফুলছড়ি', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(435, 58, 'Thakurgaon Sadar', 'ঠাকুরগাঁও সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(436, 58, 'Pirganj', 'পীরগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(437, 58, 'Ranisankail', 'রাণীশংকৈল', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(438, 58, 'Haripur', 'হরিপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(439, 58, 'Baliadangi', 'বালিয়াডাঙ্গী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(440, 59, 'Rangpur Sadar', 'রংপুর সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(441, 59, 'Gangachara', 'গংগাচড়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(442, 59, 'Taragonj', 'তারাগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(443, 59, 'Badargonj', 'বদরগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(444, 59, 'Mithapukur', 'মিঠাপুকুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(445, 59, 'Pirgonj', 'পীরগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(446, 59, 'Kaunia', 'কাউনিয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(447, 59, 'Pirgacha', 'পীরগাছা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(448, 60, 'Kurigram Sadar', 'কুড়িগ্রাম সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(449, 60, 'Nageshwari', 'নাগেশ্বরী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(450, 60, 'Bhurungamari', 'ভুরুঙ্গামারী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(451, 60, 'Phulbari', 'ফুলবাড়ী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(452, 60, 'Rajarhat', 'রাজারহাট', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(453, 60, 'Ulipur', 'উলিপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(454, 60, 'Chilmari', 'চিলমারী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(455, 60, 'Rowmari', 'রৌমারী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(456, 60, 'Charrajibpur', 'চর রাজিবপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(457, 61, 'Sherpur Sadar', 'শেরপুর সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(458, 61, 'Nalitabari', 'নালিতাবাড়ী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(459, 61, 'Sreebordi', 'শ্রীবরদী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(460, 61, 'Nokla', 'নকলা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(461, 61, 'Jhenaigati', 'ঝিনাইগাতী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(462, 62, 'Fulbaria', 'ফুলবাড়ীয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(463, 62, 'Trishal', 'ত্রিশাল', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(464, 62, 'Bhaluka', 'ভালুকা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(465, 62, 'Muktagacha', 'মুক্তাগাছা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(466, 62, 'Mymensingh Sadar', 'ময়মনসিংহ সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(467, 62, 'Dhobaura', 'ধোবাউড়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10');
INSERT INTO `thanas` (`id`, `district_id`, `name`, `name_bn`, `status`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(468, 62, 'Phulpur', 'ফুলপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(469, 62, 'Haluaghat', 'হালুয়াঘাট', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(470, 62, 'Gouripur', 'গৌরীপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(471, 62, 'Gafargaon', 'গফরগাঁও', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(472, 62, 'Iswarganj', 'ঈশ্বরগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(473, 62, 'Nandail', 'নান্দাইল', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(474, 62, 'Tarakanda', 'তারাকান্দা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(475, 63, 'Jamalpur Sadar', 'জামালপুর সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(476, 63, 'Melandah', 'মেলান্দহ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(477, 63, 'Islampur', 'ইসলামপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(478, 63, 'Dewangonj', 'দেওয়ানগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(479, 63, 'Sarishabari', 'সরিষাবাড়ী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(480, 63, 'Madarganj', 'মাদারগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(481, 63, 'Bokshiganj', 'বকশীগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(482, 64, 'Barhatta', 'বারহাট্টা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(483, 64, 'Durgapur', 'দুর্গাপুর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(484, 64, 'Kendua', 'কেন্দুয়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(485, 64, 'Atpara', 'আটপাড়া', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(486, 64, 'Madan', 'মদন', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(487, 64, 'Khaliajuri', 'খালিয়াজুরী', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(488, 64, 'Kalmakanda', 'কলমাকান্দা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(489, 64, 'Mohongonj', 'মোহনগঞ্জ', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(490, 64, 'Purbadhala', 'পূর্বধলা', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(491, 64, 'Netrokona Sadar', 'নেত্রকোণা সদর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(492, 9, 'Eidgaon', 'ঈদগাঁও', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(493, 39, 'Madhyanagar', 'মধ্যনগর', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10'),
(494, 50, 'Dasar', 'ডাসার', 1, NULL, NULL, NULL, NULL, '2026-08-22 03:16:10', '2026-08-22 03:16:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `designation` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `sales_team_id` bigint(20) UNSIGNED DEFAULT NULL,
  `manager_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `employee_id`, `name`, `email`, `phone`, `photo`, `designation`, `date_of_birth`, `sales_team_id`, `manager_id`, `status`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'EMP-00001', 'System Administrator', 'admin@gazipump.com', '01173784260', NULL, 'Super Admin', '1972-01-24', NULL, NULL, 1, NULL, 1, NULL, NULL, '2026-08-06 04:39:51', '$2y$12$SS5jB8vCBqAKdtN9v835dOVWYAkwyldeWVpdBgfFc1qxPp0d6aFi2', 'X6QVGmURDWwQNfR2aOnn1tFW2zoVDtQbvk45zeQNW63eYAjsfa6A6H97ThD0', '2026-08-06 04:39:51', '2026-08-27 00:27:43'),
(74, '123456', 'nayem', 'softnayem169@gmail.com', '01684191999', NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, NULL, NULL, '$2y$12$o9X3YZ1fno7396lCkjj51Ojw5m1DoiHEevsB5Iylz31aBrRnrGBLK', NULL, '2026-08-13 03:00:56', '2026-08-15 22:29:56'),
(80, 'EMP-10001', 'Rafiqul Islam', 'rafiqul.islam@gazipump.com', '01711000001', NULL, 'Sales Executive', '1983-01-12', 1, NULL, 1, NULL, 80, NULL, NULL, '2026-08-23 05:32:44', '$2y$12$0PDo6N45znvgavPfkeINwO6kLbp3Xw6ZsSuCzkw47xiyqGDpYDGmS', 'yMpxtaezEdV01uXRftC0ymOxqLk9qvDM5lOHhQCP5lHNt7lKRWzensYBfQlK', '2026-08-23 05:32:44', '2026-08-30 00:55:48'),
(81, 'EMP-10002', 'Shirin Akter', 'shirin.akter@gazipump.com', '01711000002', NULL, 'Sales Executive', '2001-12-06', NULL, NULL, 1, NULL, 81, NULL, NULL, '2026-08-23 05:32:44', '$2y$12$hnMF5XzSUmqBoT.X5CFLp.P8BwGlSz8tydRtJDmH6OUqXluxX6VWC', 'F6XiL3ymv3uSR2PEiX8yrURyXv3WXcsLNdWUPuc2wyAUq4IiDWqRRaopoOdr', '2026-08-23 05:32:44', '2026-08-23 05:32:44'),
(82, 'EMP-04600', 'Prof. Korbin Stracke V', 'ahuels@example.com', '01999018857', NULL, 'Sales Executive', '1990-08-31', NULL, NULL, 1, NULL, 82, NULL, NULL, '2026-08-25 03:20:09', '$2y$12$WrV3fpqhVhGhpRnmaEy7NeElnKtFpIbNLZholPvlet7HNAScS49mK', '7YmOxrro7uoW4S8pGEAlJGj7cv4LDVNLvWHELA9L8o14k4gOSppCZzpX6A88', '2026-08-25 03:20:10', '2026-08-25 03:20:10'),
(86, 'EMP-24764', 'Everett Kuvalis II', 'herman56@example.net', '01638329305', NULL, 'Sales Executive', '1984-10-31', NULL, NULL, 1, NULL, 86, NULL, NULL, '2026-08-27 01:49:25', '$2y$12$KiNuFZn1.9BcH6uDfYFAceuEWBALfk6KBrxji3iM.ROodpAPePVG.', 'M6LFZq4e1sDBYbGqeWwMIKjo7mMbPv7icsTtswko3AC8mvFCFludk7424APP', '2026-08-27 01:49:25', '2026-08-29 23:10:51'),
(89, 'EMP-69451', 'Demond Little', 'theodora.goyette@example.net', '01093105911', NULL, 'Sales Executive', '1995-02-19', NULL, NULL, 1, NULL, 89, NULL, NULL, '2026-08-30 04:52:18', '$2y$12$WPXIL61cWtrAx7cD8JYxS..SKhlSyoyLvNtByV9ZhnDiVRKH1IVxO', 'qATn9GPvsva8uizmVq5DqVoufGGPxTB8Nq0N8RtiKcwjoiOvK8VDnkbcDj2Q', '2026-08-30 04:52:18', '2026-08-30 04:52:18'),
(90, 'EMP-96116', 'Domingo Bergnaum', 'cole.noemy@example.net', '01001579230', NULL, 'Sales Executive', '1988-01-01', NULL, NULL, 1, NULL, 90, NULL, NULL, '2026-08-30 04:53:15', '$2y$12$I4Zu.3uJmXMubdO4Qb0MI.n.ngr2W8mILMbW16udwq2/vfdwutRaC', '3ZbFyxyBWRYAyAKtqpEbSKib2ww3NmN5ufoxBspiSdH6nzsU1qHr7KhYeYSN', '2026-08-30 04:53:15', '2026-08-30 04:53:15'),
(91, 'EMP-87966', 'Darrel Weissnat', 'nasir51@example.com', '01536437306', NULL, 'Sales Executive', '2004-08-09', NULL, NULL, 1, NULL, 91, NULL, NULL, '2026-08-30 23:52:34', '$2y$12$3EzB4sz6qQYTtCUFGxNFDelrRdUwqaK4CSEH9hgJx5yHT8RxZfuzy', 'OvJbK56tr2oHkAtSq36fD66DzgWDkhgrhIxCf7cqxsWD4U32Dw4VqOeEQvvT', '2026-08-30 23:52:34', '2026-08-30 23:52:34'),
(92, 'EMP-48811', 'Sydni Herman', 'vhammes@example.net', '01972962789', NULL, 'Sales Executive', '1999-01-13', NULL, NULL, 1, NULL, 92, NULL, NULL, '2026-08-30 23:52:34', '$2y$12$3EzB4sz6qQYTtCUFGxNFDelrRdUwqaK4CSEH9hgJx5yHT8RxZfuzy', 'RyYViCmillayLC44RnREIJQ60eB5VHfW9dogJoPoKWBZwC4VodsIs774J3NZ', '2026-08-30 23:52:34', '2026-08-30 23:52:34');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `registration_number` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `capacity` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `visits`
--

CREATE TABLE `visits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `visit_plan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `dealer_id` bigint(20) UNSIGNED NOT NULL,
  `check_in_at` datetime NOT NULL,
  `check_in_lat` decimal(10,7) DEFAULT NULL,
  `check_in_lng` decimal(10,7) DEFAULT NULL,
  `check_in_photo` varchar(255) DEFAULT NULL,
  `check_out_at` datetime DEFAULT NULL,
  `check_out_lat` decimal(10,7) DEFAULT NULL,
  `check_out_lng` decimal(10,7) DEFAULT NULL,
  `check_out_photo` varchar(255) DEFAULT NULL,
  `is_gps_verified` tinyint(1) DEFAULT NULL,
  `distance_from_dealer_meters` decimal(8,2) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `visits`
--

INSERT INTO `visits` (`id`, `visit_plan_id`, `user_id`, `dealer_id`, `check_in_at`, `check_in_lat`, `check_in_lng`, `check_in_photo`, `check_out_at`, `check_out_lat`, `check_out_lng`, `check_out_photo`, `is_gps_verified`, `distance_from_dealer_meters`, `feedback`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 80, 1, '2026-08-21 12:06:00', 23.7834950, 90.3694400, NULL, '2026-08-21 12:24:00', 23.7834950, 90.3694400, NULL, NULL, NULL, 'Consequatur nesciunt ullam sit porro repellat.', NULL, NULL, NULL, NULL, '2026-08-23 05:34:24', '2026-08-23 05:34:24'),
(2, 2, 81, 2, '2026-08-20 10:13:00', 23.7281630, 90.3903100, NULL, '2026-08-20 10:59:00', 23.7281630, 90.3903100, NULL, NULL, NULL, 'Est quia ut iure minima voluptate est.', NULL, NULL, NULL, NULL, '2026-08-23 05:34:24', '2026-08-23 05:34:24'),
(3, 3, 80, 3, '2026-08-19 11:39:00', 23.7858560, 90.3871860, NULL, '2026-08-19 12:11:00', 23.7858560, 90.3871860, NULL, NULL, NULL, 'Recusandae vel consequatur dolores maiores aut cumque necessitatibus.', NULL, NULL, NULL, NULL, '2026-08-23 05:34:24', '2026-08-23 05:34:24'),
(4, 4, 81, 4, '2026-08-18 11:25:00', 23.8456870, 90.4240890, NULL, '2026-08-18 12:23:00', 23.8456870, 90.4240890, NULL, NULL, NULL, 'Corrupti quasi eum voluptatem eligendi ducimus quo.', NULL, NULL, NULL, NULL, '2026-08-23 05:34:24', '2026-08-23 05:34:24'),
(5, 5, 80, 5, '2026-08-17 13:34:00', 23.8114280, 90.3568150, NULL, '2026-08-17 14:25:00', 23.8114280, 90.3568150, NULL, NULL, NULL, 'Quis eum delectus quia excepturi ut atque nam.', NULL, NULL, NULL, NULL, '2026-08-23 05:34:24', '2026-08-23 05:34:24'),
(6, NULL, 1, 1, '2026-08-27 07:48:48', 23.8103000, 90.4125000, 'visits/URwOZV5U7581YTcg6vaJqPRKjTVPm2Wrncw6RMl6.jpg', '2026-08-27 07:48:48', 23.8110000, 90.4130000, 'visits/cGW6GNQzm4HCUWatWYIIFH6pYd7rvvJzgx8g9uZ5.jpg', 0, 16311.93, 'Placed a repeat order.', 1, 1, NULL, NULL, '2026-08-27 01:48:48', '2026-08-27 01:48:48'),
(7, NULL, 1, 1, '2026-08-27 07:49:45', 23.8103000, 90.4125000, 'visits/CyhpQUFedLqcYcSsBKifPYsbZdEOoCTsW4tHkzK7.jpg', '2026-08-27 07:49:46', 23.8110000, 90.4130000, 'visits/4Ccf9r2idnlp9Ydov3S7I6pqNCsfyo66H7HRGKF5.jpg', 0, 16311.93, 'Placed a repeat order.', 1, 1, NULL, NULL, '2026-08-27 01:49:45', '2026-08-27 01:49:46'),
(8, NULL, 1, 1, '2026-08-27 07:50:15', 23.8103000, 90.4125000, 'visits/4qnEgByoNYZttwM02FoCPNz8a0eI4APG1OrprfoU.jpg', '2026-08-27 07:50:16', 23.8110000, 90.4130000, 'visits/vSy1LxzFRLfYRbwPqaWRcrN0o3VT6GjlnJr7il45.jpg', 0, 16311.93, 'Placed a repeat order.', 1, 1, NULL, NULL, '2026-08-27 01:50:15', '2026-08-27 01:50:16'),
(9, NULL, 1, 1, '2026-08-27 08:21:55', 23.8103000, 90.4125000, 'visits/MuQcVDS91HLy34IWw1QFHZHfDQ5SXQ34qGWsBESY.jpg', '2026-08-27 08:21:56', 23.8110000, 90.4130000, 'visits/vcZDBY3dxpDZPNZHj39aeFOVfNGefGA08kU7Qd1F.jpg', 0, 16311.93, 'Placed a repeat order.', 1, 1, NULL, NULL, '2026-08-27 02:21:55', '2026-08-27 02:21:56'),
(10, NULL, 1, 1, '2026-08-27 08:22:30', 23.8103000, 90.4125000, 'visits/d6ErNxreWFwgrDvVFD90sck5thAisZCLsi6j9zKH.jpg', '2026-08-27 08:22:31', 23.8110000, 90.4130000, 'visits/kGmZbC6j2iSCssuUwYsTo4wyyz6TUreX1Er6VwXx.jpg', 0, 16311.93, 'Placed a repeat order.', 1, 1, NULL, NULL, '2026-08-27 02:22:30', '2026-08-27 02:22:31'),
(11, NULL, 1, 1, '2026-08-27 08:52:00', 23.8103000, 90.4125000, 'visits/Zs14BKdhfRf9ZmzIqxWWDkA7ISsAfXpBWu8Tpp2m.jpg', '2026-08-27 08:52:01', 23.8110000, 90.4130000, 'visits/FsEuXqvyzvvJRRITONdG1tvzSljqzy0yweMHzFZ8.jpg', 0, 16311.93, 'Placed a repeat order.', 1, 1, NULL, NULL, '2026-08-27 02:52:00', '2026-08-27 02:52:01'),
(12, NULL, 1, 1, '2026-08-27 09:18:50', 23.8103000, 90.4125000, 'visits/1MSw6ZscRb76bz9OClvA8pC61oX8AOvwUWszodZD.jpg', '2026-08-27 09:18:51', 23.8110000, 90.4130000, 'visits/gBiz6B61YT1akYFKYLsfmbQHKw768dSe0ndJ1hgF.jpg', 0, 16311.93, 'Placed a repeat order.', 1, 1, NULL, NULL, '2026-08-27 03:18:50', '2026-08-27 03:18:51'),
(13, NULL, 89, 13, '2026-08-30 09:15:00', 23.7018870, 90.3615190, NULL, '2026-08-30 09:45:00', 23.8360040, 90.4476040, NULL, 1, 25.86, 'Nihil quas aut impedit adipisci.', NULL, NULL, NULL, NULL, '2026-08-30 04:52:19', '2026-08-30 04:52:19'),
(14, NULL, 90, 14, '2026-08-30 09:15:00', 23.8475340, 90.3982480, NULL, '2026-08-30 09:45:00', 23.7104620, 90.3528880, NULL, 1, 51.23, 'Qui totam voluptas in et assumenda sint soluta.', NULL, NULL, NULL, NULL, '2026-08-30 04:53:16', '2026-08-30 04:53:16'),
(15, NULL, 80, 1, '2026-08-30 09:45:00', 23.7018120, 90.4319180, NULL, '2026-08-30 10:10:00', 23.7624060, 90.4285720, NULL, 1, 20.96, 'Nesciunt voluptas voluptas amet ratione in.', NULL, NULL, NULL, NULL, '2026-08-30 04:55:32', '2026-08-30 04:55:32'),
(16, NULL, 80, 1, '2026-08-30 15:00:00', 23.8069810, 90.3788490, NULL, '2026-08-30 16:20:00', 23.7055080, 90.4403640, NULL, 1, 139.60, 'Ut est ipsam ut atque.', NULL, NULL, NULL, NULL, '2026-08-30 04:55:32', '2026-08-30 04:55:32'),
(17, NULL, 80, 4, '2026-08-31 23:05:00', NULL, NULL, NULL, '2026-08-31 23:08:00', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-09-01 05:05:44', '2026-09-01 05:05:44'),
(18, NULL, 80, 4, '2026-09-01 15:15:00', NULL, NULL, NULL, '2026-09-01 15:18:00', NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, '2026-09-01 09:15:10', '2026-09-01 09:15:10');

-- --------------------------------------------------------

--
-- Table structure for table `visit_plans`
--

CREATE TABLE `visit_plans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `dealer_id` bigint(20) UNSIGNED NOT NULL,
  `territory_id` bigint(20) UNSIGNED DEFAULT NULL,
  `planned_date` date NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'planned',
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `visit_plans`
--

INSERT INTO `visit_plans` (`id`, `user_id`, `dealer_id`, `territory_id`, `planned_date`, `status`, `notes`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 80, 1, 1, '2026-08-21', 'completed', NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:34:24', '2026-08-23 05:34:24'),
(2, 81, 2, 2, '2026-08-20', 'completed', NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:34:24', '2026-08-23 05:34:24'),
(3, 80, 3, 3, '2026-08-19', 'completed', NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:34:24', '2026-08-23 05:34:24'),
(4, 81, 4, 1, '2026-08-18', 'completed', NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:34:24', '2026-08-23 05:34:24'),
(5, 80, 5, 2, '2026-08-17', 'completed', NULL, NULL, NULL, NULL, NULL, '2026-08-23 05:34:24', '2026-08-23 05:34:24'),
(6, 1, 1, NULL, '2026-08-28', 'planned', 'Follow up on last order.', 1, NULL, NULL, NULL, '2026-08-27 01:48:47', '2026-08-27 01:48:47'),
(7, 1, 1, NULL, '2026-08-28', 'planned', 'Follow up on last order.', 1, NULL, NULL, NULL, '2026-08-27 01:49:44', '2026-08-27 01:49:44'),
(8, 1, 1, NULL, '2026-08-28', 'planned', 'Follow up on last order.', 1, NULL, NULL, NULL, '2026-08-27 01:50:14', '2026-08-27 01:50:14'),
(9, 1, 1, NULL, '2026-08-28', 'planned', 'Follow up on last order.', 1, NULL, NULL, NULL, '2026-08-27 02:21:54', '2026-08-27 02:21:54'),
(10, 1, 1, NULL, '2026-08-28', 'planned', 'Follow up on last order.', 1, NULL, NULL, NULL, '2026-08-27 02:22:29', '2026-08-27 02:22:29'),
(11, 1, 1, NULL, '2026-08-28', 'planned', 'Follow up on last order.', 1, NULL, NULL, NULL, '2026-08-27 02:51:59', '2026-08-27 02:51:59'),
(12, 1, 1, NULL, '2026-08-28', 'planned', 'Follow up on last order.', 1, NULL, NULL, NULL, '2026-08-27 03:18:49', '2026-08-27 03:18:49'),
(13, 80, 4, 1, '2026-08-31', 'planned', NULL, 1, 1, 1, NULL, '2026-09-01 05:04:22', '2026-09-01 07:01:03'),
(14, 80, 4, 1, '2026-09-01', 'planned', NULL, 1, 1, 1, '2026-09-01 09:00:00', '2026-09-01 05:36:02', '2026-09-01 09:00:00'),
(15, 80, 4, 1, '2026-09-01', 'completed', NULL, 1, NULL, NULL, NULL, '2026-09-01 09:01:17', '2026-09-01 09:15:10');

-- --------------------------------------------------------

--
-- Table structure for table `visit_requests`
--

CREATE TABLE `visit_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_account_id` bigint(20) UNSIGNED NOT NULL,
  `preferred_date` date NOT NULL,
  `address` text NOT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `achievements_target_id_unique` (`target_id`),
  ADD KEY `achievements_grade_index` (`grade`);

--
-- Indexes for table `achievement_entries`
--
ALTER TABLE `achievement_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `achievement_entries_user_id_entry_date_unique` (`user_id`,`entry_date`),
  ADD KEY `achievement_entries_approved_by_foreign` (`approved_by`),
  ADD KEY `achievement_entries_created_by_foreign` (`created_by`),
  ADD KEY `achievement_entries_updated_by_foreign` (`updated_by`),
  ADD KEY `achievement_entries_deleted_by_foreign` (`deleted_by`),
  ADD KEY `achievement_entries_entry_date_index` (`entry_date`);

--
-- Indexes for table `achievement_items`
--
ALTER TABLE `achievement_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `achievement_items_achievement_entry_id_index` (`achievement_entry_id`),
  ADD KEY `achievement_items_product_id_index` (`product_id`);

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject` (`subject_type`,`subject_id`),
  ADD KEY `causer` (`causer_type`,`causer_id`),
  ADD KEY `activity_log_log_name_index` (`log_name`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcements_audience_territory_id_foreign` (`audience_territory_id`),
  ADD KEY `announcements_audience_user_id_foreign` (`audience_user_id`),
  ADD KEY `announcements_sent_by_foreign` (`sent_by`),
  ADD KEY `announcements_created_by_foreign` (`created_by`),
  ADD KEY `announcements_updated_by_foreign` (`updated_by`),
  ADD KEY `announcements_deleted_by_foreign` (`deleted_by`),
  ADD KEY `announcements_audience_created_at_index` (`audience`,`created_at`),
  ADD KEY `announcements_audience_dealer_id_foreign` (`audience_dealer_id`);

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendances_user_id_date_unique` (`user_id`,`date`),
  ADD KEY `attendances_created_by_foreign` (`created_by`),
  ADD KEY `attendances_updated_by_foreign` (`updated_by`),
  ADD KEY `attendances_deleted_by_foreign` (`deleted_by`),
  ADD KEY `attendances_date_index` (`date`),
  ADD KEY `attendances_status_index` (`status`);

--
-- Indexes for table `brochures`
--
ALTER TABLE `brochures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brochures_created_by_foreign` (`created_by`),
  ADD KEY `brochures_updated_by_foreign` (`updated_by`),
  ADD KEY `brochures_deleted_by_foreign` (`deleted_by`),
  ADD KEY `brochures_is_published_index` (`is_published`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `cash_handovers`
--
ALTER TABLE `cash_handovers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cash_handovers_confirmed_by_foreign` (`confirmed_by`),
  ADD KEY `cash_handovers_created_by_foreign` (`created_by`),
  ADD KEY `cash_handovers_updated_by_foreign` (`updated_by`),
  ADD KEY `cash_handovers_deleted_by_foreign` (`deleted_by`),
  ADD KEY `cash_handovers_user_id_handover_date_index` (`user_id`,`handover_date`);

--
-- Indexes for table `collection_entries`
--
ALTER TABLE `collection_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `collection_entries_external_reference_unique` (`external_reference`),
  ADD KEY `collection_entries_created_by_foreign` (`created_by`),
  ADD KEY `collection_entries_updated_by_foreign` (`updated_by`),
  ADD KEY `collection_entries_deleted_by_foreign` (`deleted_by`),
  ADD KEY `collection_entries_user_id_collection_date_index` (`user_id`,`collection_date`),
  ADD KEY `collection_entries_customer_id_index` (`dealer_id`),
  ADD KEY `collection_entries_collection_date_index` (`collection_date`),
  ADD KEY `collection_entries_approved_by_foreign` (`approved_by`),
  ADD KEY `collection_entries_sync_status_index` (`sync_status`);

--
-- Indexes for table `collection_otps`
--
ALTER TABLE `collection_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `collection_otps_user_id_foreign` (`user_id`),
  ADD KEY `collection_otps_dealer_id_user_id_index` (`dealer_id`,`user_id`);

--
-- Indexes for table `customer_accounts`
--
ALTER TABLE `customer_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_accounts_email_unique` (`email`),
  ADD KEY `customer_accounts_customer_id_foreign` (`dealer_id`);

--
-- Indexes for table `customer_password_reset_tokens`
--
ALTER TABLE `customer_password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `dealers`
--
ALTER TABLE `dealers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customers_customer_code_unique` (`dealer_code`),
  ADD KEY `customers_territory_id_foreign` (`territory_id`),
  ADD KEY `customers_created_by_foreign` (`created_by`),
  ADD KEY `customers_updated_by_foreign` (`updated_by`),
  ADD KEY `customers_deleted_by_foreign` (`deleted_by`),
  ADD KEY `customers_status_index` (`status`),
  ADD KEY `dealers_division_id_foreign` (`division_id`),
  ADD KEY `dealers_district_id_foreign` (`district_id`),
  ADD KEY `dealers_thana_id_foreign` (`thana_id`),
  ADD KEY `dealers_tally_guid_index` (`tally_guid`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `deliveries_external_reference_unique` (`external_reference`),
  ADD KEY `deliveries_order_id_foreign` (`order_id`),
  ADD KEY `deliveries_vehicle_id_foreign` (`vehicle_id`),
  ADD KEY `deliveries_driver_id_foreign` (`driver_id`),
  ADD KEY `deliveries_dispatched_by_foreign` (`dispatched_by`),
  ADD KEY `deliveries_status_index` (`status`),
  ADD KEY `deliveries_sync_status_index` (`sync_status`),
  ADD KEY `deliveries_tally_guid_index` (`tally_guid`);

--
-- Indexes for table `depots`
--
ALTER TABLE `depots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `depots_code_unique` (`code`),
  ADD KEY `depots_territory_id_foreign` (`territory_id`),
  ADD KEY `depots_created_by_foreign` (`created_by`),
  ADD KEY `depots_updated_by_foreign` (`updated_by`),
  ADD KEY `depots_deleted_by_foreign` (`deleted_by`),
  ADD KEY `depots_tally_guid_index` (`tally_guid`);

--
-- Indexes for table `depot_allocations`
--
ALTER TABLE `depot_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `depot_allocations_order_item_id_foreign` (`order_item_id`),
  ADD KEY `depot_allocations_depot_id_foreign` (`depot_id`),
  ADD KEY `depot_allocations_approved_by_foreign` (`approved_by`),
  ADD KEY `depot_allocations_created_by_foreign` (`created_by`),
  ADD KEY `depot_allocations_updated_by_foreign` (`updated_by`),
  ADD KEY `depot_allocations_allocation_status_index` (`allocation_status`);

--
-- Indexes for table `districts`
--
ALTER TABLE `districts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `districts_division_id_name_unique` (`division_id`,`name`),
  ADD KEY `districts_created_by_foreign` (`created_by`),
  ADD KEY `districts_updated_by_foreign` (`updated_by`),
  ADD KEY `districts_deleted_by_foreign` (`deleted_by`),
  ADD KEY `districts_status_index` (`status`);

--
-- Indexes for table `divisions`
--
ALTER TABLE `divisions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `divisions_name_unique` (`name`),
  ADD KEY `divisions_created_by_foreign` (`created_by`),
  ADD KEY `divisions_updated_by_foreign` (`updated_by`),
  ADD KEY `divisions_deleted_by_foreign` (`deleted_by`),
  ADD KEY `divisions_status_index` (`status`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `drivers_license_number_unique` (`license_number`),
  ADD KEY `drivers_created_by_foreign` (`created_by`),
  ADD KEY `drivers_updated_by_foreign` (`updated_by`),
  ADD KEY `drivers_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `faqs_created_by_foreign` (`created_by`),
  ADD KEY `faqs_updated_by_foreign` (`updated_by`),
  ADD KEY `faqs_deleted_by_foreign` (`deleted_by`),
  ADD KEY `faqs_is_published_index` (`is_published`);

--
-- Indexes for table `gps_logs`
--
ALTER TABLE `gps_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `gps_logs_created_by_foreign` (`created_by`),
  ADD KEY `gps_logs_updated_by_foreign` (`updated_by`),
  ADD KEY `gps_logs_deleted_by_foreign` (`deleted_by`),
  ADD KEY `gps_logs_user_id_recorded_at_index` (`user_id`,`recorded_at`);

--
-- Indexes for table `holidays`
--
ALTER TABLE `holidays`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `holidays_date_unique` (`date`),
  ADD KEY `holidays_created_by_foreign` (`created_by`),
  ADD KEY `holidays_updated_by_foreign` (`updated_by`),
  ADD KEY `holidays_deleted_by_foreign` (`deleted_by`),
  ADD KEY `holidays_status_index` (`status`);

--
-- Indexes for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inquiries_customer_account_id_foreign` (`customer_account_id`),
  ADD KEY `inquiries_product_id_foreign` (`product_id`),
  ADD KEY `inquiries_created_by_foreign` (`created_by`),
  ADD KEY `inquiries_updated_by_foreign` (`updated_by`),
  ADD KEY `inquiries_deleted_by_foreign` (`deleted_by`),
  ADD KEY `inquiries_status_index` (`status`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leave_balances`
--
ALTER TABLE `leave_balances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `leave_balances_user_id_leave_type_id_year_unique` (`user_id`,`leave_type_id`,`year`),
  ADD KEY `leave_balances_leave_type_id_foreign` (`leave_type_id`),
  ADD KEY `leave_balances_created_by_foreign` (`created_by`),
  ADD KEY `leave_balances_updated_by_foreign` (`updated_by`),
  ADD KEY `leave_balances_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leave_requests_leave_type_id_foreign` (`leave_type_id`),
  ADD KEY `leave_requests_approved_by_foreign` (`approved_by`),
  ADD KEY `leave_requests_created_by_foreign` (`created_by`),
  ADD KEY `leave_requests_updated_by_foreign` (`updated_by`),
  ADD KEY `leave_requests_deleted_by_foreign` (`deleted_by`),
  ADD KEY `leave_requests_status_index` (`status`),
  ADD KEY `leave_requests_user_id_from_date_to_date_index` (`user_id`,`from_date`,`to_date`);

--
-- Indexes for table `leave_types`
--
ALTER TABLE `leave_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `leave_types_code_unique` (`code`),
  ADD KEY `leave_types_created_by_foreign` (`created_by`),
  ADD KEY `leave_types_updated_by_foreign` (`updated_by`),
  ADD KEY `leave_types_deleted_by_foreign` (`deleted_by`),
  ADD KEY `leave_types_status_index` (`status`);

--
-- Indexes for table `ledger_entries`
--
ALTER TABLE `ledger_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ledger_entries_tally_guid_unique` (`tally_guid`),
  ADD KEY `ledger_entries_dealer_id_voucher_date_index` (`dealer_id`,`voucher_date`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD KEY `news_created_by_foreign` (`created_by`),
  ADD KEY `news_updated_by_foreign` (`updated_by`),
  ADD KEY `news_deleted_by_foreign` (`deleted_by`),
  ADD KEY `news_is_published_published_at_index` (`is_published`,`published_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_external_reference_unique` (`external_reference`),
  ADD KEY `sales_entries_created_by_foreign` (`created_by`),
  ADD KEY `sales_entries_updated_by_foreign` (`updated_by`),
  ADD KEY `sales_entries_deleted_by_foreign` (`deleted_by`),
  ADD KEY `sales_entries_user_id_sale_date_index` (`user_id`,`order_date`),
  ADD KEY `sales_entries_customer_id_index` (`dealer_id`),
  ADD KEY `sales_entries_sale_date_index` (`order_date`),
  ADD KEY `orders_retailer_id_foreign` (`retailer_id`),
  ADD KEY `orders_approved_by_foreign` (`approved_by`),
  ADD KEY `orders_sync_status_index` (`sync_status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sales_entry_items_sales_entry_id_index` (`order_id`),
  ADD KEY `sales_entry_items_product_id_index` (`product_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_sku_unique` (`sku`),
  ADD KEY `products_category_id_foreign` (`category_id`),
  ADD KEY `products_created_by_foreign` (`created_by`),
  ADD KEY `products_updated_by_foreign` (`updated_by`),
  ADD KEY `products_deleted_by_foreign` (`deleted_by`),
  ADD KEY `products_status_index` (`status`),
  ADD KEY `products_sales_team_id_foreign` (`sales_team_id`),
  ADD KEY `products_tally_guid_index` (`tally_guid`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_categories_code_unique` (`code`),
  ADD KEY `product_categories_created_by_foreign` (`created_by`),
  ADD KEY `product_categories_updated_by_foreign` (`updated_by`),
  ADD KEY `product_categories_deleted_by_foreign` (`deleted_by`),
  ADD KEY `product_categories_status_index` (`status`),
  ADD KEY `product_categories_parent_id_foreign` (`parent_id`);

--
-- Indexes for table `product_stocks`
--
ALTER TABLE `product_stocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_stocks_depot_id_product_id_unique` (`depot_id`,`product_id`),
  ADD KEY `product_stocks_product_id_foreign` (`product_id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `promotions_created_by_foreign` (`created_by`),
  ADD KEY `promotions_updated_by_foreign` (`updated_by`),
  ADD KEY `promotions_deleted_by_foreign` (`deleted_by`),
  ADD KEY `promotions_is_active_index` (`is_active`);

--
-- Indexes for table `retailers`
--
ALTER TABLE `retailers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `retailers_created_by_foreign` (`created_by`),
  ADD KEY `retailers_updated_by_foreign` (`updated_by`),
  ADD KEY `retailers_deleted_by_foreign` (`deleted_by`),
  ADD KEY `retailers_dealer_id_index` (`dealer_id`),
  ADD KEY `retailers_status_index` (`status`),
  ADD KEY `retailers_tally_guid_index` (`tally_guid`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sales_returns`
--
ALTER TABLE `sales_returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sales_returns_external_reference_unique` (`external_reference`),
  ADD KEY `sales_returns_order_id_foreign` (`order_id`),
  ADD KEY `sales_returns_dealer_id_foreign` (`dealer_id`),
  ADD KEY `sales_returns_user_id_foreign` (`user_id`),
  ADD KEY `sales_returns_approved_by_foreign` (`approved_by`),
  ADD KEY `sales_returns_vehicle_id_foreign` (`vehicle_id`),
  ADD KEY `sales_returns_driver_id_foreign` (`driver_id`),
  ADD KEY `sales_returns_receiving_depot_id_foreign` (`receiving_depot_id`),
  ADD KEY `sales_returns_received_by_foreign` (`received_by`),
  ADD KEY `sales_returns_created_by_foreign` (`created_by`),
  ADD KEY `sales_returns_updated_by_foreign` (`updated_by`),
  ADD KEY `sales_returns_status_index` (`status`),
  ADD KEY `sales_returns_sync_status_index` (`sync_status`),
  ADD KEY `sales_returns_tally_guid_index` (`tally_guid`);

--
-- Indexes for table `sales_return_items`
--
ALTER TABLE `sales_return_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sales_return_items_sales_return_id_foreign` (`sales_return_id`),
  ADD KEY `sales_return_items_order_item_id_foreign` (`order_item_id`),
  ADD KEY `sales_return_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `sales_teams`
--
ALTER TABLE `sales_teams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sales_teams_code_unique` (`code`),
  ADD KEY `sales_teams_created_by_foreign` (`created_by`),
  ADD KEY `sales_teams_updated_by_foreign` (`updated_by`),
  ADD KEY `sales_teams_deleted_by_foreign` (`deleted_by`),
  ADD KEY `sales_teams_status_index` (`status`);

--
-- Indexes for table `service_centers`
--
ALTER TABLE `service_centers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_centers_created_by_foreign` (`created_by`),
  ADD KEY `service_centers_updated_by_foreign` (`updated_by`),
  ADD KEY `service_centers_deleted_by_foreign` (`deleted_by`),
  ADD KEY `service_centers_is_active_index` (`is_active`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `settings_created_by_foreign` (`created_by`),
  ADD KEY `settings_updated_by_foreign` (`updated_by`),
  ADD KEY `settings_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `sync_logs`
--
ALTER TABLE `sync_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sync_logs_user_id_foreign` (`user_id`),
  ADD KEY `sync_logs_entity_type_entity_id_index` (`entity_type`,`entity_id`),
  ADD KEY `sync_logs_external_reference_index` (`external_reference`),
  ADD KEY `sync_logs_tally_guid_index` (`tally_guid`),
  ADD KEY `sync_logs_status_index` (`status`);

--
-- Indexes for table `sync_queues`
--
ALTER TABLE `sync_queues`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sync_queues_external_reference_unique` (`external_reference`),
  ADD KEY `sync_queues_status_index` (`status`),
  ADD KEY `sync_queues_next_attempt_at_index` (`next_attempt_at`),
  ADD KEY `sync_queues_entity_type_entity_id_index` (`entity_type`,`entity_id`);

--
-- Indexes for table `tally_connections`
--
ALTER TABLE `tally_connections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tally_connections_sync_agent_id_unique` (`sync_agent_id`),
  ADD KEY `tally_connections_created_by_foreign` (`created_by`),
  ADD KEY `tally_connections_updated_by_foreign` (`updated_by`),
  ADD KEY `tally_connections_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `tally_mappings`
--
ALTER TABLE `tally_mappings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tally_mappings_entity_type_sfa_id_unique` (`entity_type`,`sfa_id`),
  ADD UNIQUE KEY `tally_mappings_entity_type_tally_guid_unique` (`entity_type`,`tally_guid`),
  ADD KEY `tally_mappings_created_by_foreign` (`created_by`),
  ADD KEY `tally_mappings_updated_by_foreign` (`updated_by`),
  ADD KEY `tally_mappings_deleted_by_foreign` (`deleted_by`),
  ADD KEY `tally_mappings_sync_status_index` (`sync_status`),
  ADD KEY `tally_mappings_tally_alter_id_index` (`tally_alter_id`);

--
-- Indexes for table `targets`
--
ALTER TABLE `targets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `targets_user_id_month_year_unique` (`user_id`,`month`,`year`),
  ADD KEY `targets_created_by_foreign` (`created_by`),
  ADD KEY `targets_updated_by_foreign` (`updated_by`),
  ADD KEY `targets_deleted_by_foreign` (`deleted_by`),
  ADD KEY `targets_month_year_index` (`month`,`year`);

--
-- Indexes for table `target_items`
--
ALTER TABLE `target_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `target_items_target_id_index` (`target_id`),
  ADD KEY `target_items_product_id_index` (`product_id`);

--
-- Indexes for table `territories`
--
ALTER TABLE `territories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `territories_code_unique` (`code`),
  ADD KEY `territories_manager_id_foreign` (`manager_id`),
  ADD KEY `territories_created_by_foreign` (`created_by`),
  ADD KEY `territories_updated_by_foreign` (`updated_by`),
  ADD KEY `territories_deleted_by_foreign` (`deleted_by`),
  ADD KEY `territories_status_index` (`status`),
  ADD KEY `territories_division_id_foreign` (`division_id`),
  ADD KEY `territories_district_id_foreign` (`district_id`),
  ADD KEY `territories_thana_id_foreign` (`thana_id`);

--
-- Indexes for table `territory_user`
--
ALTER TABLE `territory_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `territory_user_user_id_territory_id_unique` (`user_id`,`territory_id`),
  ADD KEY `territory_user_territory_id_foreign` (`territory_id`);

--
-- Indexes for table `thanas`
--
ALTER TABLE `thanas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `thanas_district_id_name_unique` (`district_id`,`name`),
  ADD KEY `thanas_created_by_foreign` (`created_by`),
  ADD KEY `thanas_updated_by_foreign` (`updated_by`),
  ADD KEY `thanas_deleted_by_foreign` (`deleted_by`),
  ADD KEY `thanas_status_index` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_employee_id_unique` (`employee_id`),
  ADD KEY `users_manager_id_foreign` (`manager_id`),
  ADD KEY `users_created_by_foreign` (`created_by`),
  ADD KEY `users_updated_by_foreign` (`updated_by`),
  ADD KEY `users_deleted_by_foreign` (`deleted_by`),
  ADD KEY `users_status_index` (`status`),
  ADD KEY `users_sales_team_id_foreign` (`sales_team_id`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vehicles_registration_number_unique` (`registration_number`),
  ADD KEY `vehicles_created_by_foreign` (`created_by`),
  ADD KEY `vehicles_updated_by_foreign` (`updated_by`),
  ADD KEY `vehicles_deleted_by_foreign` (`deleted_by`);

--
-- Indexes for table `visits`
--
ALTER TABLE `visits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `visits_visit_plan_id_foreign` (`visit_plan_id`),
  ADD KEY `visits_created_by_foreign` (`created_by`),
  ADD KEY `visits_updated_by_foreign` (`updated_by`),
  ADD KEY `visits_deleted_by_foreign` (`deleted_by`),
  ADD KEY `visits_user_id_check_in_at_index` (`user_id`,`check_in_at`),
  ADD KEY `visits_customer_id_index` (`dealer_id`),
  ADD KEY `visits_check_in_at_index` (`check_in_at`);

--
-- Indexes for table `visit_plans`
--
ALTER TABLE `visit_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `visit_plans_customer_id_foreign` (`dealer_id`),
  ADD KEY `visit_plans_created_by_foreign` (`created_by`),
  ADD KEY `visit_plans_updated_by_foreign` (`updated_by`),
  ADD KEY `visit_plans_deleted_by_foreign` (`deleted_by`),
  ADD KEY `visit_plans_user_id_planned_date_index` (`user_id`,`planned_date`),
  ADD KEY `visit_plans_status_index` (`status`),
  ADD KEY `visit_plans_planned_date_index` (`planned_date`),
  ADD KEY `visit_plans_territory_id_foreign` (`territory_id`);

--
-- Indexes for table `visit_requests`
--
ALTER TABLE `visit_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `visit_requests_customer_account_id_foreign` (`customer_account_id`),
  ADD KEY `visit_requests_created_by_foreign` (`created_by`),
  ADD KEY `visit_requests_updated_by_foreign` (`updated_by`),
  ADD KEY `visit_requests_deleted_by_foreign` (`deleted_by`),
  ADD KEY `visit_requests_status_index` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `achievements`
--
ALTER TABLE `achievements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `achievement_entries`
--
ALTER TABLE `achievement_entries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `achievement_items`
--
ALTER TABLE `achievement_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=723;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `brochures`
--
ALTER TABLE `brochures`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cash_handovers`
--
ALTER TABLE `cash_handovers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `collection_entries`
--
ALTER TABLE `collection_entries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `collection_otps`
--
ALTER TABLE `collection_otps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `customer_accounts`
--
ALTER TABLE `customer_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `dealers`
--
ALTER TABLE `dealers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `depots`
--
ALTER TABLE `depots`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `depot_allocations`
--
ALTER TABLE `depot_allocations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `districts`
--
ALTER TABLE `districts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `divisions`
--
ALTER TABLE `divisions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gps_logs`
--
ALTER TABLE `gps_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inquiries`
--
ALTER TABLE `inquiries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_balances`
--
ALTER TABLE `leave_balances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ledger_entries`
--
ALTER TABLE `ledger_entries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=487;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `product_stocks`
--
ALTER TABLE `product_stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `retailers`
--
ALTER TABLE `retailers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sales_returns`
--
ALTER TABLE `sales_returns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_return_items`
--
ALTER TABLE `sales_return_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_teams`
--
ALTER TABLE `sales_teams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `service_centers`
--
ALTER TABLE `service_centers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sync_logs`
--
ALTER TABLE `sync_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=237;

--
-- AUTO_INCREMENT for table `sync_queues`
--
ALTER TABLE `sync_queues`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=253;

--
-- AUTO_INCREMENT for table `tally_connections`
--
ALTER TABLE `tally_connections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tally_mappings`
--
ALTER TABLE `tally_mappings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `targets`
--
ALTER TABLE `targets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `target_items`
--
ALTER TABLE `target_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `territories`
--
ALTER TABLE `territories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `territory_user`
--
ALTER TABLE `territory_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `thanas`
--
ALTER TABLE `thanas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=495;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `visits`
--
ALTER TABLE `visits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `visit_plans`
--
ALTER TABLE `visit_plans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `visit_requests`
--
ALTER TABLE `visit_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `achievements`
--
ALTER TABLE `achievements`
  ADD CONSTRAINT `achievements_target_id_foreign` FOREIGN KEY (`target_id`) REFERENCES `targets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `achievement_entries`
--
ALTER TABLE `achievement_entries`
  ADD CONSTRAINT `achievement_entries_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `achievement_entries_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `achievement_entries_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `achievement_entries_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `achievement_entries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `achievement_items`
--
ALTER TABLE `achievement_items`
  ADD CONSTRAINT `achievement_items_achievement_entry_id_foreign` FOREIGN KEY (`achievement_entry_id`) REFERENCES `achievement_entries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `achievement_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_audience_dealer_id_foreign` FOREIGN KEY (`audience_dealer_id`) REFERENCES `dealers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `announcements_audience_territory_id_foreign` FOREIGN KEY (`audience_territory_id`) REFERENCES `territories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `announcements_audience_user_id_foreign` FOREIGN KEY (`audience_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `announcements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `announcements_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `announcements_sent_by_foreign` FOREIGN KEY (`sent_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `announcements_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `attendances_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `attendances_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `attendances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `brochures`
--
ALTER TABLE `brochures`
  ADD CONSTRAINT `brochures_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `brochures_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `brochures_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cash_handovers`
--
ALTER TABLE `cash_handovers`
  ADD CONSTRAINT `cash_handovers_confirmed_by_foreign` FOREIGN KEY (`confirmed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cash_handovers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cash_handovers_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cash_handovers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cash_handovers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `collection_entries`
--
ALTER TABLE `collection_entries`
  ADD CONSTRAINT `collection_entries_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `collection_entries_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `collection_entries_customer_id_foreign` FOREIGN KEY (`dealer_id`) REFERENCES `dealers` (`id`),
  ADD CONSTRAINT `collection_entries_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `collection_entries_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `collection_entries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `collection_otps`
--
ALTER TABLE `collection_otps`
  ADD CONSTRAINT `collection_otps_dealer_id_foreign` FOREIGN KEY (`dealer_id`) REFERENCES `dealers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `collection_otps_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `customer_accounts`
--
ALTER TABLE `customer_accounts`
  ADD CONSTRAINT `customer_accounts_customer_id_foreign` FOREIGN KEY (`dealer_id`) REFERENCES `dealers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `dealers`
--
ALTER TABLE `dealers`
  ADD CONSTRAINT `customers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customers_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customers_territory_id_foreign` FOREIGN KEY (`territory_id`) REFERENCES `territories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dealers_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dealers_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dealers_thana_id_foreign` FOREIGN KEY (`thana_id`) REFERENCES `thanas` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_dispatched_by_foreign` FOREIGN KEY (`dispatched_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deliveries_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deliveries_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deliveries_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `depots`
--
ALTER TABLE `depots`
  ADD CONSTRAINT `depots_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `depots_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `depots_territory_id_foreign` FOREIGN KEY (`territory_id`) REFERENCES `territories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `depots_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `depot_allocations`
--
ALTER TABLE `depot_allocations`
  ADD CONSTRAINT `depot_allocations_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `depot_allocations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `depot_allocations_depot_id_foreign` FOREIGN KEY (`depot_id`) REFERENCES `depots` (`id`),
  ADD CONSTRAINT `depot_allocations_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `depot_allocations_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `districts`
--
ALTER TABLE `districts`
  ADD CONSTRAINT `districts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `districts_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `districts_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`),
  ADD CONSTRAINT `districts_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `divisions`
--
ALTER TABLE `divisions`
  ADD CONSTRAINT `divisions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `divisions_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `divisions_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `drivers`
--
ALTER TABLE `drivers`
  ADD CONSTRAINT `drivers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `drivers_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `drivers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `faqs`
--
ALTER TABLE `faqs`
  ADD CONSTRAINT `faqs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `faqs_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `faqs_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `gps_logs`
--
ALTER TABLE `gps_logs`
  ADD CONSTRAINT `gps_logs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `gps_logs_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `gps_logs_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `gps_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `holidays`
--
ALTER TABLE `holidays`
  ADD CONSTRAINT `holidays_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `holidays_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `holidays_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD CONSTRAINT `inquiries_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inquiries_customer_account_id_foreign` FOREIGN KEY (`customer_account_id`) REFERENCES `customer_accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inquiries_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inquiries_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inquiries_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `leave_balances`
--
ALTER TABLE `leave_balances`
  ADD CONSTRAINT `leave_balances_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leave_balances_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leave_balances_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_balances_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leave_balances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leave_requests_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leave_requests_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leave_requests_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`),
  ADD CONSTRAINT `leave_requests_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leave_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_types`
--
ALTER TABLE `leave_types`
  ADD CONSTRAINT `leave_types_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leave_types_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leave_types_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ledger_entries`
--
ALTER TABLE `ledger_entries`
  ADD CONSTRAINT `ledger_entries_dealer_id_foreign` FOREIGN KEY (`dealer_id`) REFERENCES `dealers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `news_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `news_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_retailer_id_foreign` FOREIGN KEY (`retailer_id`) REFERENCES `retailers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_entries_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_entries_customer_id_foreign` FOREIGN KEY (`dealer_id`) REFERENCES `dealers` (`id`),
  ADD CONSTRAINT `sales_entries_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_entries_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_entries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `sales_entry_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `sales_entry_items_sales_entry_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`),
  ADD CONSTRAINT `products_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_sales_team_id_foreign` FOREIGN KEY (`sales_team_id`) REFERENCES `sales_teams` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD CONSTRAINT `product_categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_categories_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `product_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_categories_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_stocks`
--
ALTER TABLE `product_stocks`
  ADD CONSTRAINT `product_stocks_depot_id_foreign` FOREIGN KEY (`depot_id`) REFERENCES `depots` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_stocks_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `promotions`
--
ALTER TABLE `promotions`
  ADD CONSTRAINT `promotions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `promotions_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `promotions_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `retailers`
--
ALTER TABLE `retailers`
  ADD CONSTRAINT `retailers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `retailers_dealer_id_foreign` FOREIGN KEY (`dealer_id`) REFERENCES `dealers` (`id`),
  ADD CONSTRAINT `retailers_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `retailers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_returns`
--
ALTER TABLE `sales_returns`
  ADD CONSTRAINT `sales_returns_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_returns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_returns_dealer_id_foreign` FOREIGN KEY (`dealer_id`) REFERENCES `dealers` (`id`),
  ADD CONSTRAINT `sales_returns_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_returns_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `sales_returns_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_returns_receiving_depot_id_foreign` FOREIGN KEY (`receiving_depot_id`) REFERENCES `depots` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_returns_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_returns_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `sales_returns_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sales_return_items`
--
ALTER TABLE `sales_return_items`
  ADD CONSTRAINT `sales_return_items_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`),
  ADD CONSTRAINT `sales_return_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `sales_return_items_sales_return_id_foreign` FOREIGN KEY (`sales_return_id`) REFERENCES `sales_returns` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_teams`
--
ALTER TABLE `sales_teams`
  ADD CONSTRAINT `sales_teams_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_teams_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_teams_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `service_centers`
--
ALTER TABLE `service_centers`
  ADD CONSTRAINT `service_centers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `service_centers_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `service_centers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `settings`
--
ALTER TABLE `settings`
  ADD CONSTRAINT `settings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `settings_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `settings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sync_logs`
--
ALTER TABLE `sync_logs`
  ADD CONSTRAINT `sync_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tally_connections`
--
ALTER TABLE `tally_connections`
  ADD CONSTRAINT `tally_connections_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tally_connections_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tally_connections_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tally_mappings`
--
ALTER TABLE `tally_mappings`
  ADD CONSTRAINT `tally_mappings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tally_mappings_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tally_mappings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `targets`
--
ALTER TABLE `targets`
  ADD CONSTRAINT `targets_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `targets_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `targets_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `targets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `target_items`
--
ALTER TABLE `target_items`
  ADD CONSTRAINT `target_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `target_items_target_id_foreign` FOREIGN KEY (`target_id`) REFERENCES `targets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `territories`
--
ALTER TABLE `territories`
  ADD CONSTRAINT `territories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `territories_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `territories_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `territories_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `territories_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `territories_thana_id_foreign` FOREIGN KEY (`thana_id`) REFERENCES `thanas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `territories_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `territory_user`
--
ALTER TABLE `territory_user`
  ADD CONSTRAINT `territory_user_territory_id_foreign` FOREIGN KEY (`territory_id`) REFERENCES `territories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `territory_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `thanas`
--
ALTER TABLE `thanas`
  ADD CONSTRAINT `thanas_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `thanas_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `thanas_district_id_foreign` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`),
  ADD CONSTRAINT `thanas_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_sales_team_id_foreign` FOREIGN KEY (`sales_team_id`) REFERENCES `sales_teams` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `vehicles_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `vehicles_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `visits`
--
ALTER TABLE `visits`
  ADD CONSTRAINT `visits_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `visits_customer_id_foreign` FOREIGN KEY (`dealer_id`) REFERENCES `dealers` (`id`),
  ADD CONSTRAINT `visits_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `visits_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `visits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `visits_visit_plan_id_foreign` FOREIGN KEY (`visit_plan_id`) REFERENCES `visit_plans` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `visit_plans`
--
ALTER TABLE `visit_plans`
  ADD CONSTRAINT `visit_plans_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `visit_plans_customer_id_foreign` FOREIGN KEY (`dealer_id`) REFERENCES `dealers` (`id`),
  ADD CONSTRAINT `visit_plans_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `visit_plans_territory_id_foreign` FOREIGN KEY (`territory_id`) REFERENCES `territories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `visit_plans_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `visit_plans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `visit_requests`
--
ALTER TABLE `visit_requests`
  ADD CONSTRAINT `visit_requests_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `visit_requests_customer_account_id_foreign` FOREIGN KEY (`customer_account_id`) REFERENCES `customer_accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `visit_requests_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `visit_requests_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
