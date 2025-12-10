-- Migration: Enforce NOT NULL on season_id in wp_tour_categories
-- Date: 2025-12-10
-- Description: Ensure all categories belong to a specific season (remove NULL season_id)
--              This restores the strict relationship between categories and seasons

-- Step 1: Update any categories with NULL season_id to the active season
-- Note: This query assigns NULL season categories to the active season
UPDATE `wp_tour_categories`
SET `season_id` = (
    SELECT `id` FROM `wp_tour_seasons` WHERE `active` = 1 LIMIT 1
)
WHERE `season_id` IS NULL;

-- Step 2: If there are still NULL values (no active season), assign to the most recent season
UPDATE `wp_tour_categories`
SET `season_id` = (
    SELECT `id` FROM `wp_tour_seasons` ORDER BY `start_date` DESC LIMIT 1
)
WHERE `season_id` IS NULL;

-- Step 3: Alter the table to enforce NOT NULL constraint
ALTER TABLE `wp_tour_categories`
MODIFY COLUMN `season_id` BIGINT(20) UNSIGNED NOT NULL;

-- Verification query (run this after migration to check the structure)
-- DESCRIBE `wp_tour_categories`;
-- SELECT c.*, s.name as season_name FROM `wp_tour_categories` c LEFT JOIN `wp_tour_seasons` s ON c.season_id = s.id;
