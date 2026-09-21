-- Migration: Add is_default field to transports
-- Date: 2025-12-10
-- Description: Add an 'is_default' column to allow marking one transport as default
--              When creating new events, the default transport will be preselected
--              Named 'is_default' (not 'default') because DEFAULT is a reserved SQL keyword

-- Step 1: Add is_default column
ALTER TABLE `wp_tour_transports`
    ADD COLUMN `is_default` TINYINT(1) DEFAULT 0 NOT NULL AFTER `name`;

-- Step 2: Add index for is_default field
ALTER TABLE `wp_tour_transports`
    ADD INDEX `is_default` (`is_default`);

-- Note: Only one transport should be marked as default at a time
-- This is enforced in the application logic, not at the database level
