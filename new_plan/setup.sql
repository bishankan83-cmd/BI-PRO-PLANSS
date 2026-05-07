-- ============================================================
-- TIRE PRODUCTION PLANNING SYSTEM - COMPLETE DATABASE SETUP
-- Run this file ONCE to create the planning table
-- ============================================================

-- Create the production_plan table to store scheduled tire runs
CREATE TABLE IF NOT EXISTS `production_plan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `icode` int(11) NOT NULL COMMENT 'Tire item code from tire table',
  `tire_description` varchar(255) NOT NULL,
  `mold_id` varchar(100) NOT NULL COMMENT 'Mold assigned',
  `press_id` int(11) NOT NULL COMMENT 'Press assigned',
  `cavity_id` int(11) NOT NULL COMMENT 'Cavity assigned',
  `cavity_name` varchar(20) DEFAULT NULL,
  `planned_start` datetime NOT NULL COMMENT 'Calculated start time (after mold + cavity available)',
  `planned_end` datetime NOT NULL COMMENT 'Calculated end time (start + time_taken minutes)',
  `time_taken` int(11) NOT NULL COMMENT 'Duration in minutes from tire table',
  `status` enum('planned','in_progress','completed','cancelled') DEFAULT 'planned',
  `created_at` datetime DEFAULT NOW(),
  `notes` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `icode` (`icode`),
  KEY `mold_id` (`mold_id`),
  KEY `press_id` (`press_id`),
  KEY `planned_start` (`planned_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Stores all planned tire production runs';

-- Create a press table (derived from mold_press and press_cavity data)
-- You may already have this table; adjust if needed
CREATE TABLE IF NOT EXISTS `press` (
  `press_id` int(11) NOT NULL AUTO_INCREMENT,
  `press_name` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`press_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Populate press names if table is empty
INSERT IGNORE INTO `press` (`press_id`, `press_name`)
SELECT DISTINCT press_id, CONCAT('Press-', press_id)
FROM `press_cavity`
WHERE press_id > 0
ORDER BY press_id;
