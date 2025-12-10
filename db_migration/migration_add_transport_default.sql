-- Migration: Add default field to transports
-- Date: 2025-12-10
-- Description: Add a 'default' column to allow marking one transport as default
--              When creating new events, the default transport will be preselected

-- Step 1: Add default column
ALTER TABLE `wp_tour_transports`
ADD COLUMN `default` TINYINT(1) DEFAULT 0 NOT NULL AFTER `name`;

-- Step 2: Add index for default field
ALTER TABLE `wp_tour_transports`
ADD INDEX `default` (`default`);

-- Note: Only one transport should be marked as default at a time
-- This is enforced in the application logic, not at the database level
