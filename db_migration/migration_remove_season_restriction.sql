-- Migration: Remove season restriction from categories
-- Date: 2025-12-10
-- Description: Make categories accessible by all seasons by making season_id nullable
--              This allows categories to be used across multiple seasons

-- Step 1: Modify season_id column to allow NULL values
ALTER TABLE `wp_tour_categories`
MODIFY COLUMN `season_id` BIGINT(20) UNSIGNED DEFAULT NULL;

-- Step 2: Update indexes to handle NULL values (recreate the index)
-- Drop existing index
ALTER TABLE `wp_tour_categories`
DROP INDEX `season_id`;

-- Recreate index that handles NULL values
ALTER TABLE `wp_tour_categories`
ADD INDEX `season_id` (`season_id`);

-- Verification query (run this after migration to check the structure)
-- DESCRIBE `wp_tour_categories`;
