/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` varchar(255) NOT NULL,
  `activity_name` varchar(255) NOT NULL,
  `activity_description` text NOT NULL,
  `activity_type` varchar(255) NOT NULL,
  `activity_date` date NOT NULL,
  `activity_start_time` time NOT NULL,
  `activity_end_time` time NOT NULL,
  `activity_location` varchar(255) NOT NULL,
  `max_participants` int(11) NOT NULL DEFAULT 20,
  `current_participants` int(11) NOT NULL DEFAULT 0,
  `activity_goals` text DEFAULT NULL,
  `activity_outcomes` text DEFAULT NULL,
  `activity_image` varchar(255) DEFAULT NULL,
  `required_resources` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`required_resources`)),
  `activity_status` enum('scheduled','ongoing','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `centre_id` varchar(255) NOT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `instructor_id` bigint(20) unsigned DEFAULT NULL,
  `times_conducted` int(11) NOT NULL DEFAULT 0,
  `average_rating` decimal(3,2) DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `min_participants` int(11) NOT NULL DEFAULT 1,
  `difficulty_level` enum('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',
  `age_group` enum('children','adolescents','adults','elderly','all_ages') NOT NULL DEFAULT 'all_ages',
  `activity_period` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `activities_activity_id_unique` (`activity_id`),
  KEY `activities_activity_id_index` (`activity_id`),
  KEY `activities_centre_id_index` (`centre_id`),
  KEY `activities_activity_status_index` (`activity_status`),
  KEY `activities_activity_date_index` (`activity_date`),
  KEY `activities_category_id_index` (`category_id`),
  KEY `activities_instructor_id_index` (`instructor_id`),
  KEY `activities_difficulty_level_index` (`difficulty_level`),
  KEY `activities_is_active_index` (`is_active`),
  KEY `activities_created_by_foreign` (`created_by`),
  CONSTRAINT `activities_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `activities_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activities_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_enrollments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` bigint(20) unsigned NOT NULL,
  `trainee_id` bigint(20) unsigned NOT NULL,
  `enrollment_date` date NOT NULL,
  `enrollment_status` enum('enrolled','withdrawn','completed','suspended') NOT NULL DEFAULT 'enrolled',
  `enrollment_notes` text DEFAULT NULL,
  `individual_goals` text DEFAULT NULL,
  `progress_notes` text DEFAULT NULL,
  `sessions_attended` int(11) NOT NULL DEFAULT 0,
  `total_sessions` int(11) NOT NULL DEFAULT 0,
  `attendance_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `overall_progress` decimal(5,2) NOT NULL DEFAULT 0.00,
  `enrolled_by` bigint(20) unsigned NOT NULL,
  `centre_id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `activity_enrollments_activity_id_trainee_id_unique` (`activity_id`,`trainee_id`),
  KEY `activity_enrollments_activity_id_index` (`activity_id`),
  KEY `activity_enrollments_trainee_id_index` (`trainee_id`),
  KEY `activity_enrollments_enrollment_status_index` (`enrollment_status`),
  KEY `activity_enrollments_enrollment_date_index` (`enrollment_date`),
  KEY `activity_enrollments_centre_id_index` (`centre_id`),
  KEY `activity_enrollments_enrolled_by_foreign` (`enrolled_by`),
  CONSTRAINT `activity_enrollments_activity_id_foreign` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activity_enrollments_enrolled_by_foreign` FOREIGN KEY (`enrolled_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activity_enrollments_trainee_id_foreign` FOREIGN KEY (`trainee_id`) REFERENCES `trainees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_schedules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` bigint(20) unsigned NOT NULL,
  `frequency` enum('daily','weekly','monthly','custom') NOT NULL DEFAULT 'weekly',
  `days_of_week` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`days_of_week`)),
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `venue` varchar(255) NOT NULL,
  `max_capacity` int(11) NOT NULL DEFAULT 20,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_schedules_activity_id_index` (`activity_id`),
  KEY `activity_schedules_frequency_index` (`frequency`),
  KEY `activity_schedules_start_date_end_date_index` (`start_date`,`end_date`),
  KEY `activity_schedules_is_active_index` (`is_active`),
  KEY `activity_schedules_created_by_foreign` (`created_by`),
  CONSTRAINT `activity_schedules_activity_id_foreign` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activity_schedules_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` bigint(20) unsigned NOT NULL,
  `session_date` date NOT NULL,
  `scheduled_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `venue` varchar(255) NOT NULL,
  `room_number` varchar(255) DEFAULT NULL,
  `instructor_id` bigint(20) unsigned NOT NULL,
  `teacher_id` bigint(20) unsigned NOT NULL,
  `max_capacity` int(11) NOT NULL DEFAULT 20,
  `max_participants` int(11) NOT NULL DEFAULT 20,
  `current_enrollment` int(11) NOT NULL DEFAULT 0,
  `current_participants` int(11) NOT NULL DEFAULT 0,
  `session_status` enum('scheduled','ongoing','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `status` enum('scheduled','ongoing','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `attendance_marked` tinyint(1) NOT NULL DEFAULT 0,
  `session_notes` text DEFAULT NULL,
  `materials_used` text DEFAULT NULL,
  `session_rating` decimal(3,2) DEFAULT NULL,
  `centre_id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_sessions_activity_id_index` (`activity_id`),
  KEY `activity_sessions_session_date_index` (`session_date`),
  KEY `activity_sessions_scheduled_date_index` (`scheduled_date`),
  KEY `activity_sessions_instructor_id_index` (`instructor_id`),
  KEY `activity_sessions_session_status_index` (`session_status`),
  KEY `activity_sessions_centre_id_index` (`centre_id`),
  CONSTRAINT `activity_sessions_activity_id_foreign` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activity_sessions_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `asset_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `depreciation_rate` decimal(5,2) NOT NULL DEFAULT 20.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_categories_code_unique` (`code`),
  KEY `asset_categories_parent_id_index` (`parent_id`),
  KEY `asset_categories_is_active_index` (`is_active`),
  KEY `asset_categories_code_index` (`code`),
  CONSTRAINT `asset_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `asset_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `asset_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_locations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `location_name` varchar(255) NOT NULL,
  `location_code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `centre_id` varchar(255) NOT NULL,
  `building` varchar(255) DEFAULT NULL,
  `floor` varchar(255) DEFAULT NULL,
  `room` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `responsible_person` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_locations_location_code_unique` (`location_code`),
  KEY `asset_locations_centre_id_index` (`centre_id`),
  KEY `asset_locations_is_active_index` (`is_active`),
  KEY `asset_locations_responsible_person_index` (`responsible_person`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `asset_maintenance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_maintenance` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` bigint(20) unsigned NOT NULL,
  `maintenance_type` varchar(255) NOT NULL DEFAULT 'scheduled',
  `description` text NOT NULL,
  `scheduled_date` date NOT NULL,
  `completed_date` date DEFAULT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `priority` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `estimated_cost` decimal(10,2) DEFAULT NULL,
  `actual_cost` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `centre_id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_maintenance_asset_id_index` (`asset_id`),
  KEY `asset_maintenance_scheduled_date_index` (`scheduled_date`),
  KEY `asset_maintenance_status_index` (`status`),
  KEY `asset_maintenance_priority_index` (`priority`),
  KEY `asset_maintenance_centre_id_index` (`centre_id`),
  KEY `asset_maintenance_assigned_to_index` (`assigned_to`),
  KEY `asset_maintenance_created_by_index` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `asset_maintenance_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_maintenance_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `maintenance_id` bigint(20) unsigned NOT NULL,
  `action` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `performed_by` bigint(20) unsigned NOT NULL,
  `centre_id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_maintenance_history_maintenance_id_index` (`maintenance_id`),
  KEY `asset_maintenance_history_action_index` (`action`),
  KEY `asset_maintenance_history_performed_by_index` (`performed_by`),
  KEY `asset_maintenance_history_centre_id_index` (`centre_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `asset_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` bigint(20) unsigned NOT NULL,
  `type` enum('assignment','transfer','return','disposal','maintenance') NOT NULL DEFAULT 'assignment',
  `from_user` bigint(20) unsigned DEFAULT NULL,
  `to_user` bigint(20) unsigned DEFAULT NULL,
  `from_location` varchar(255) DEFAULT NULL,
  `to_location` varchar(255) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `performed_by` bigint(20) unsigned NOT NULL,
  `movement_date` datetime NOT NULL,
  `status` enum('pending','approved','completed','cancelled') NOT NULL DEFAULT 'completed',
  `notes` text DEFAULT NULL,
  `centre_id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_movements_asset_id_index` (`asset_id`),
  KEY `asset_movements_type_index` (`type`),
  KEY `asset_movements_movement_date_index` (`movement_date`),
  KEY `asset_movements_from_user_index` (`from_user`),
  KEY `asset_movements_to_user_index` (`to_user`),
  KEY `asset_movements_performed_by_index` (`performed_by`),
  KEY `asset_movements_centre_id_index` (`centre_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `asset_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_types` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type_name` varchar(255) NOT NULL,
  `type_description` text DEFAULT NULL,
  `type_category` varchar(255) NOT NULL,
  `requires_maintenance` tinyint(1) NOT NULL DEFAULT 0,
  `default_maintenance_interval_days` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_types_type_category_index` (`type_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `asset_code` varchar(255) DEFAULT NULL,
  `asset_id` varchar(255) NOT NULL,
  `asset_name` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `asset_description` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `asset_type_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `manufacturer` varchar(255) DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_price` decimal(10,2) DEFAULT NULL,
  `current_value` decimal(10,2) DEFAULT NULL,
  `depreciation_rate` decimal(5,2) NOT NULL DEFAULT 20.00,
  `specifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specifications`)),
  `location` varchar(255) NOT NULL,
  `condition` enum('excellent','good','fair','poor','damaged') NOT NULL DEFAULT 'good',
  `status` enum('active','inactive','maintenance','disposed') NOT NULL DEFAULT 'active',
  `centre_id` varchar(255) NOT NULL,
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `assigned_date` date DEFAULT NULL,
  `maintenance_notes` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `last_maintenance_date` date DEFAULT NULL,
  `next_maintenance_date` date DEFAULT NULL,
  `warranty_expiry` varchar(255) DEFAULT NULL,
  `warranty_months` int(11) NOT NULL DEFAULT 12,
  `asset_image` varchar(255) DEFAULT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `qr_code` varchar(255) DEFAULT NULL,
  `rfid_tag` varchar(255) DEFAULT NULL,
  `priority` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `assets_asset_id_unique` (`asset_id`),
  UNIQUE KEY `assets_asset_code_unique` (`asset_code`),
  KEY `assets_asset_id_index` (`asset_id`),
  KEY `assets_centre_id_index` (`centre_id`),
  KEY `assets_status_index` (`status`),
  KEY `assets_condition_index` (`condition`),
  KEY `assets_asset_type_id_index` (`asset_type_id`),
  KEY `assets_assigned_to_index` (`assigned_to`),
  KEY `assets_category_id_foreign` (`category_id`),
  CONSTRAINT `assets_asset_type_id_foreign` FOREIGN KEY (`asset_type_id`) REFERENCES `asset_types` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assets_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assets_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `asset_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `trainee_id` bigint(20) unsigned NOT NULL,
  `activity_id` bigint(20) unsigned DEFAULT NULL,
  `session_id` bigint(20) unsigned DEFAULT NULL,
  `class_id` bigint(20) unsigned DEFAULT NULL,
  `attendance_date` date NOT NULL,
  `check_in_time` time DEFAULT NULL,
  `check_out_time` time DEFAULT NULL,
  `attendance_status` enum('present','absent','late','excused','partial') NOT NULL DEFAULT 'present',
  `attendance_notes` text DEFAULT NULL,
  `participation_level` int(11) DEFAULT NULL,
  `progress_observation` text DEFAULT NULL,
  `recorded_by` bigint(20) unsigned NOT NULL,
  `centre_id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_trainee_id_index` (`trainee_id`),
  KEY `attendance_activity_id_index` (`activity_id`),
  KEY `attendance_session_id_index` (`session_id`),
  KEY `attendance_class_id_index` (`class_id`),
  KEY `attendance_attendance_date_index` (`attendance_date`),
  KEY `attendance_attendance_status_index` (`attendance_status`),
  KEY `attendance_centre_id_index` (`centre_id`),
  KEY `attendance_recorded_by_foreign` (`recorded_by`),
  CONSTRAINT `attendance_activity_id_foreign` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_session_id_foreign` FOREIGN KEY (`session_id`) REFERENCES `activity_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_trainee_id_foreign` FOREIGN KEY (`trainee_id`) REFERENCES `trainees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `attendances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `trainee_id` bigint(20) unsigned NOT NULL,
  `activity_id` bigint(20) unsigned DEFAULT NULL,
  `session_id` bigint(20) unsigned DEFAULT NULL,
  `date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` enum('present','absent','late','excused','sick') NOT NULL DEFAULT 'present',
  `notes` text DEFAULT NULL,
  `mood_rating` int(11) DEFAULT NULL,
  `engagement_level` int(11) DEFAULT NULL,
  `achievements` text DEFAULT NULL,
  `challenges` text DEFAULT NULL,
  `goals_progress` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`goals_progress`)),
  `recorded_by` bigint(20) unsigned NOT NULL,
  `centre_id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendances_trainee_id_date_index` (`trainee_id`,`date`),
  KEY `attendances_activity_id_index` (`activity_id`),
  KEY `attendances_session_id_index` (`session_id`),
  KEY `attendances_status_index` (`status`),
  KEY `attendances_centre_id_index` (`centre_id`),
  KEY `attendances_recorded_by_foreign` (`recorded_by`),
  CONSTRAINT `attendances_activity_id_foreign` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendances_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendances_session_id_foreign` FOREIGN KEY (`session_id`) REFERENCES `activity_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendances_trainee_id_foreign` FOREIGN KEY (`trainee_id`) REFERENCES `trainees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  `category_description` text DEFAULT NULL,
  `category_icon` varchar(255) DEFAULT NULL,
  `category_color` varchar(255) DEFAULT NULL,
  `category_type` enum('rehabilitation','academic','recreational','faith') NOT NULL DEFAULT 'rehabilitation',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categories_category_type_index` (`category_type`),
  KEY `categories_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `centres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `centres` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `centre_id` varchar(255) NOT NULL,
  `centre_name` varchar(255) NOT NULL,
  `centre_address` text NOT NULL,
  `centre_phone` varchar(255) NOT NULL,
  `centre_email` varchar(255) NOT NULL,
  `centre_capacity` varchar(255) NOT NULL,
  `centre_manager` varchar(255) NOT NULL,
  `centre_manager_contact` varchar(255) NOT NULL,
  `centre_status` enum('active','inactive','maintenance') NOT NULL DEFAULT 'active',
  `centre_description` text DEFAULT NULL,
  `centre_facilities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`centre_facilities`)),
  `centre_image` varchar(255) DEFAULT NULL,
  `centre_latitude` decimal(10,8) DEFAULT NULL,
  `centre_longitude` decimal(11,8) DEFAULT NULL,
  `opening_time` time DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `centres_centre_id_unique` (`centre_id`),
  KEY `centres_centre_id_index` (`centre_id`),
  KEY `centres_centre_status_index` (`centre_status`),
  KEY `centres_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `class_name` varchar(255) NOT NULL,
  `class_description` text DEFAULT NULL,
  `instructor_id` bigint(20) unsigned NOT NULL,
  `course_id` bigint(20) unsigned DEFAULT NULL,
  `class_schedule` varchar(255) DEFAULT NULL,
  `max_students` int(11) NOT NULL DEFAULT 25,
  `current_enrollment` int(11) NOT NULL DEFAULT 0,
  `class_status` enum('active','inactive','completed') NOT NULL DEFAULT 'active',
  `centre_id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `classes_instructor_id_index` (`instructor_id`),
  KEY `classes_course_id_index` (`course_id`),
  KEY `classes_centre_id_index` (`centre_id`),
  KEY `classes_class_status_index` (`class_status`),
  CONSTRAINT `classes_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  CONSTRAINT `classes_instructor_id_foreign` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `inquiry_type` enum('general','enrollment','volunteer','feedback','support') NOT NULL DEFAULT 'general',
  `status` enum('new','in_progress','resolved','closed') NOT NULL DEFAULT 'new',
  `admin_notes` text DEFAULT NULL,
  `replied_by` bigint(20) unsigned DEFAULT NULL,
  `replied_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `centre_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_messages_status_index` (`status`),
  KEY `contact_messages_inquiry_type_index` (`inquiry_type`),
  KEY `contact_messages_email_index` (`email`),
  KEY `contact_messages_centre_id_index` (`centre_id`),
  KEY `contact_messages_replied_by_foreign` (`replied_by`),
  CONSTRAINT `contact_messages_replied_by_foreign` FOREIGN KEY (`replied_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `courses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` varchar(255) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `course_description` text NOT NULL,
  `course_duration_weeks` int(11) NOT NULL,
  `max_participants` int(11) NOT NULL,
  `course_objectives` text DEFAULT NULL,
  `required_skills` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`required_skills`)),
  `difficulty_level` enum('beginner','intermediate','advanced') NOT NULL DEFAULT 'beginner',
  `course_status` enum('active','inactive','draft','archived') NOT NULL DEFAULT 'active',
  `centre_id` varchar(255) NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `courses_course_id_unique` (`course_id`),
  KEY `courses_course_id_index` (`course_id`),
  KEY `courses_centre_id_index` (`centre_id`),
  KEY `courses_course_status_index` (`course_status`),
  KEY `courses_difficulty_level_index` (`difficulty_level`),
  KEY `courses_created_by_foreign` (`created_by`),
  CONSTRAINT `courses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_name` varchar(255) NOT NULL,
  `event_description` text DEFAULT NULL,
  `event_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `event_location` varchar(255) NOT NULL,
  `max_attendees` int(11) DEFAULT NULL,
  `event_type` enum('workshop','seminar','celebration','meeting','other') NOT NULL DEFAULT 'other',
  `event_status` enum('planned','ongoing','completed','cancelled') NOT NULL DEFAULT 'planned',
  `organizer_id` bigint(20) unsigned NOT NULL,
  `centre_id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `events_event_date_index` (`event_date`),
  KEY `events_event_status_index` (`event_status`),
  KEY `events_organizer_id_index` (`organizer_id`),
  KEY `events_centre_id_index` (`centre_id`),
  CONSTRAINT `events_organizer_id_foreign` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `letter_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `letter_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `template_name` varchar(255) NOT NULL,
  `template_description` text DEFAULT NULL,
  `template_type` enum('recommendation','completion_certificate','progress_report','invitation','official_letter','assessment_report','custom') NOT NULL DEFAULT 'custom',
  `template_content` longtext NOT NULL,
  `template_variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`template_variables`)),
  `template_header_image` varchar(255) DEFAULT NULL,
  `template_footer_text` varchar(255) DEFAULT NULL,
  `template_styling` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`template_styling`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `centre_id` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `letter_templates_template_type_index` (`template_type`),
  KEY `letter_templates_is_active_index` (`is_active`),
  KEY `letter_templates_centre_id_index` (`centre_id`),
  KEY `letter_templates_created_by_foreign` (`created_by`),
  CONSTRAINT `letter_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `letters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `letters` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `letter_reference_number` varchar(255) NOT NULL,
  `letter_name` varchar(255) DEFAULT NULL,
  `template_id` bigint(20) unsigned DEFAULT NULL,
  `letter_title` varchar(255) NOT NULL,
  `letter_description` text DEFAULT NULL,
  `letter_type` enum('recommendation','completion_certificate','progress_report','invitation','official_letter','assessment_report','custom') NOT NULL DEFAULT 'custom',
  `letter_content` longtext NOT NULL,
  `letter_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`letter_data`)),
  `recipient_name` varchar(255) NOT NULL,
  `recipient_email` varchar(255) DEFAULT NULL,
  `recipient_address` varchar(255) DEFAULT NULL,
  `letter_status` enum('draft','generated','sent','archived') NOT NULL DEFAULT 'generated',
  `pdf_filename` varchar(255) DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `pdf_file_size` int(11) DEFAULT NULL,
  `generated_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivery_notes` text DEFAULT NULL,
  `centre_id` varchar(255) NOT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `letters_letter_reference_number_unique` (`letter_reference_number`),
  KEY `letters_letter_reference_number_index` (`letter_reference_number`),
  KEY `letters_letter_type_index` (`letter_type`),
  KEY `letters_letter_status_index` (`letter_status`),
  KEY `letters_centre_id_index` (`centre_id`),
  KEY `letters_created_by_index` (`created_by`),
  KEY `letters_generated_at_index` (`generated_at`),
  KEY `letters_template_id_foreign` (`template_id`),
  CONSTRAINT `letters_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `letters_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `letter_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` bigint(20) unsigned NOT NULL,
  `recipient_id` bigint(20) unsigned NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `priority` enum('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `is_starred` tinyint(1) NOT NULL DEFAULT 0,
  `sender_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `recipient_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `reply_to` bigint(20) unsigned DEFAULT NULL,
  `message_thread_id` varchar(255) DEFAULT NULL,
  `centre_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `messages_sender_id_index` (`sender_id`),
  KEY `messages_recipient_id_index` (`recipient_id`),
  KEY `messages_is_read_index` (`is_read`),
  KEY `messages_priority_index` (`priority`),
  KEY `messages_message_thread_id_index` (`message_thread_id`),
  KEY `messages_centre_id_index` (`centre_id`),
  KEY `messages_reply_to_foreign` (`reply_to`),
  CONSTRAINT `messages_recipient_id_foreign` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_reply_to_foreign` FOREIGN KEY (`reply_to`) REFERENCES `messages` (`id`) ON DELETE SET NULL,
  CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','error','reminder') NOT NULL DEFAULT 'info',
  `priority` enum('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `action_url` varchar(255) DEFAULT NULL,
  `action_text` varchar(255) DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `centre_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_index` (`user_id`),
  KEY `notifications_is_read_index` (`is_read`),
  KEY `notifications_type_index` (`type`),
  KEY `notifications_priority_index` (`priority`),
  KEY `notifications_centre_id_index` (`centre_id`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `session_attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `session_attendance` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `session_id` bigint(20) unsigned NOT NULL,
  `trainee_id` bigint(20) unsigned NOT NULL,
  `attendance_status` enum('present','absent','late','excused') NOT NULL DEFAULT 'present',
  `check_in_time` time DEFAULT NULL,
  `check_out_time` time DEFAULT NULL,
  `participation_score` int(11) DEFAULT NULL,
  `session_progress_notes` text DEFAULT NULL,
  `behavioral_notes` text DEFAULT NULL,
  `goals_achieved` tinyint(1) NOT NULL DEFAULT 0,
  `recorded_by` bigint(20) unsigned NOT NULL,
  `centre_id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_attendance_session_id_trainee_id_unique` (`session_id`,`trainee_id`),
  KEY `session_attendance_session_id_index` (`session_id`),
  KEY `session_attendance_trainee_id_index` (`trainee_id`),
  KEY `session_attendance_attendance_status_index` (`attendance_status`),
  KEY `session_attendance_centre_id_index` (`centre_id`),
  KEY `session_attendance_recorded_by_foreign` (`recorded_by`),
  CONSTRAINT `session_attendance_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `session_attendance_session_id_foreign` FOREIGN KEY (`session_id`) REFERENCES `activity_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `session_attendance_trainee_id_foreign` FOREIGN KEY (`trainee_id`) REFERENCES `trainees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `session_enrollments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `session_enrollments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `session_id` bigint(20) unsigned NOT NULL,
  `trainee_id` bigint(20) unsigned NOT NULL,
  `enrollment_status` enum('enrolled','waitlist','cancelled') NOT NULL DEFAULT 'enrolled',
  `enrollment_date` date NOT NULL,
  `special_requirements` text DEFAULT NULL,
  `enrolled_by` bigint(20) unsigned NOT NULL,
  `centre_id` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_enrollments_session_id_trainee_id_unique` (`session_id`,`trainee_id`),
  KEY `session_enrollments_session_id_index` (`session_id`),
  KEY `session_enrollments_trainee_id_index` (`trainee_id`),
  KEY `session_enrollments_enrollment_status_index` (`enrollment_status`),
  KEY `session_enrollments_centre_id_index` (`centre_id`),
  KEY `session_enrollments_enrolled_by_foreign` (`enrolled_by`),
  CONSTRAINT `session_enrollments_enrolled_by_foreign` FOREIGN KEY (`enrolled_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `session_enrollments_session_id_foreign` FOREIGN KEY (`session_id`) REFERENCES `activity_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `session_enrollments_trainee_id_foreign` FOREIGN KEY (`trainee_id`) REFERENCES `trainees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
DROP TABLE IF EXISTS `staff_attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_attendance` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `attendance_date` date NOT NULL,
  `check_in_time` time DEFAULT NULL,
  `check_out_time` time DEFAULT NULL,
  `status` enum('present','absent','late','half_day','sick_leave','annual_leave') NOT NULL DEFAULT 'present',
  `notes` text DEFAULT NULL,
  `centre_id` varchar(255) NOT NULL,
  `recorded_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staff_attendance_user_id_attendance_date_index` (`user_id`,`attendance_date`),
  KEY `staff_attendance_centre_id_index` (`centre_id`),
  KEY `staff_attendance_attendance_date_index` (`attendance_date`),
  KEY `staff_attendance_status_index` (`status`),
  KEY `staff_attendance_recorded_by_foreign` (`recorded_by`),
  CONSTRAINT `staff_attendance_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `staff_attendance_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `staff_attendances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_attendances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `staff_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `date` date NOT NULL,
  `attendance_date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `attendance_time` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` enum('present','absent','late','half_day') NOT NULL DEFAULT 'present',
  `attendance_type` varchar(255) NOT NULL DEFAULT 'check_in',
  `remarks` text DEFAULT NULL,
  `centre_id` varchar(255) NOT NULL,
  `recorded_by` bigint(20) unsigned DEFAULT NULL,
  `marked_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `marked_by_email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staff_attendances_staff_id_date_index` (`staff_id`,`date`),
  KEY `staff_attendances_centre_id_index` (`centre_id`),
  KEY `staff_attendances_date_index` (`date`),
  KEY `staff_attendances_recorded_by_foreign` (`recorded_by`),
  CONSTRAINT `staff_attendances_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `staff_attendances_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trainees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trainees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `trainee_id` varchar(255) NOT NULL,
  `trainee_first_name` varchar(255) NOT NULL,
  `trainee_last_name` varchar(255) NOT NULL,
  `trainee_email` varchar(255) NOT NULL,
  `ic_number` varchar(255) NOT NULL,
  `trainee_date_of_birth` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `trainee_phone_number` varchar(255) DEFAULT NULL,
  `trainee_address` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `trainee_condition` varchar(255) DEFAULT NULL,
  `centre_name` varchar(255) NOT NULL,
  `medical_history` text DEFAULT NULL,
  `additional_notes` text DEFAULT NULL,
  `photo_consent` tinyint(1) NOT NULL DEFAULT 0,
  `services_consent` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('active','inactive','suspended','graduated') NOT NULL DEFAULT 'active',
  `centre_id` varchar(255) NOT NULL,
  `course_id` bigint(20) unsigned DEFAULT NULL,
  `guardian_name` varchar(255) DEFAULT NULL,
  `guardian_phone` varchar(255) DEFAULT NULL,
  `guardian_email` varchar(255) DEFAULT NULL,
  `guardian_relationship` varchar(255) DEFAULT NULL,
  `guardian_address` text DEFAULT NULL,
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `emergency_contact_phone` varchar(255) DEFAULT NULL,
  `emergency_contact_relationship` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `trainees_trainee_id_unique` (`trainee_id`),
  UNIQUE KEY `trainees_trainee_email_unique` (`trainee_email`),
  UNIQUE KEY `trainees_ic_number_unique` (`ic_number`),
  KEY `trainees_trainee_id_index` (`trainee_id`),
  KEY `trainees_centre_id_index` (`centre_id`),
  KEY `trainees_status_index` (`status`),
  KEY `trainees_ic_number_index` (`ic_number`),
  KEY `trainees_trainee_condition_index` (`trainee_condition`),
  KEY `trainees_course_id_foreign` (`course_id`),
  CONSTRAINT `trainees_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `iium_id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `education_level` varchar(255) DEFAULT NULL,
  `education_specialization` varchar(255) DEFAULT NULL,
  `teaching_specialization` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `about` text DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `role` enum('admin','supervisor','teacher','ajk') NOT NULL DEFAULT 'teacher',
  `status` enum('active','inactive','pending','suspended') NOT NULL DEFAULT 'pending',
  `centre_id` varchar(255) DEFAULT NULL,
  `centre_location` varchar(255) DEFAULT NULL,
  `user_last_accessed_at` timestamp NULL DEFAULT NULL,
  `review` text DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_iium_id_unique` (`iium_id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_status_index` (`role`,`status`),
  KEY `users_centre_id_index` (`centre_id`),
  KEY `users_iium_id_index` (`iium_id`),
  KEY `users_email_index` (`email`),
  KEY `users_updated_by_foreign` (`updated_by`),
  CONSTRAINT `users_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `volunteers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `volunteers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `volunteer_name` varchar(255) NOT NULL,
  `volunteer_email` varchar(255) NOT NULL,
  `volunteer_phone` varchar(255) NOT NULL,
  `volunteer_address` text NOT NULL,
  `volunteer_birth_date` date NOT NULL,
  `volunteer_gender` enum('Male','Female','Other') NOT NULL,
  `volunteer_skills` text DEFAULT NULL,
  `volunteer_experience` text DEFAULT NULL,
  `volunteer_availability` varchar(255) DEFAULT NULL,
  `volunteer_status` enum('pending','active','inactive') NOT NULL DEFAULT 'pending',
  `volunteer_start_date` date DEFAULT NULL,
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `emergency_contact_phone` varchar(255) DEFAULT NULL,
  `centre_id` varchar(255) DEFAULT NULL,
  `approved_by` bigint(20) unsigned DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `status_updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `volunteers_volunteer_email_unique` (`volunteer_email`),
  KEY `volunteers_volunteer_status_index` (`volunteer_status`),
  KEY `volunteers_volunteer_email_index` (`volunteer_email`),
  KEY `volunteers_centre_id_index` (`centre_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'001_create_core_laravel_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'002_create_centres_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'003_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'004_create_trainees_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'005_create_activities_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'006_create_activity_sessions_enrollments',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'007_create_attendance_tracking',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'008_create_communication_system',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'009_create_letter_generation_system',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'010_create_foreign_key_constraints',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'011_fix_database_schema_mismatches',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'012_create_missing_asset_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'013_fix_assets_table_schema',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2019_12_14_000001_create_personal_access_tokens_table',1);
