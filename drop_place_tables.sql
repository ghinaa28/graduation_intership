-- Drop all tables related to post_place.php
-- This script removes all place-related tables and their dependencies

-- Drop tables in order (respecting foreign key constraints)

-- Drop junction/linking tables first
DROP TABLE IF EXISTS `place_equipment`;
DROP TABLE IF EXISTS `place_packages`;
DROP TABLE IF EXISTS `package_items`;
DROP TABLE IF EXISTS `instructor_place_applications`;

-- Drop related functional tables
DROP TABLE IF EXISTS `place_bookings`;
DROP TABLE IF EXISTS `place_reviews`;
DROP TABLE IF EXISTS `place_evaluations`;
DROP TABLE IF EXISTS `place_availability`;

-- Drop equipment-related tables (used exclusively by places)
DROP TABLE IF EXISTS `equipment_items`;
DROP TABLE IF EXISTS `equipment_packages`;
DROP TABLE IF EXISTS `equipment_categories`;

-- Drop main places table last
DROP TABLE IF EXISTS `places`;

-- Verify tables are dropped
SELECT 'Place-related tables have been dropped successfully!' AS message;

