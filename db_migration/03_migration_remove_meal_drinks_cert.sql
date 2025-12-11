-- Migration: Remove meal, drinks, and cert fields from wp_tour_events table
-- Date: 2025-12-10
-- Description: Drop meal, drinks, and cert columns as they are no longer needed

-- Drop columns
ALTER TABLE `wp_tour_events`
DROP
COLUMN `meal`,
DROP
COLUMN `drinks`,
DROP
COLUMN `cert`;

-- Verification query (run this after migration to check the structure)
-- DESCRIBE `wp_tour_events`;
