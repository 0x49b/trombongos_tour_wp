-- Migration: Update time fields in wp_tour_events table
-- Date: 2025-12-02
-- Description: Replace old time fields (assembly, loadup, departure, soundcheck, dinner, ending)
--              with new time fields (gathering, makeup, warehouse, sun)

-- Step 1: Add new columns
ALTER TABLE `wp_tour_events`
ADD COLUMN `gathering` TIME DEFAULT NULL AFTER `play`,
ADD COLUMN `makeup` TIME DEFAULT NULL AFTER `gathering`,
ADD COLUMN `warehouse` TIME DEFAULT NULL AFTER `makeup`,
ADD COLUMN `sun` TIME DEFAULT NULL AFTER `warehouse`;

-- Step 2: Migrate existing data (if needed)
-- Rename assembly to gathering
UPDATE `wp_tour_events` SET `gathering` = `assembly` WHERE `assembly` IS NOT NULL;

-- Step 3: Drop old columns
ALTER TABLE `wp_tour_events`
DROP COLUMN `assembly`,
DROP COLUMN `loadup`,
DROP COLUMN `departure`,
DROP COLUMN `soundcheck`,
DROP COLUMN `dinner`,
DROP COLUMN `ending`;

-- Verification query (run this after migration to check the structure)
-- DESCRIBE `wp_tour_events`;
