-- Migration: Add maps_url field to wp_tour_events table
-- Date: 2025-12-10
-- Description: Add maps_url field to store Google Maps search link based on location

-- Add maps_url column after location
ALTER TABLE `wp_tour_events`
    ADD COLUMN `maps_url` VARCHAR(500) DEFAULT NULL AFTER `location`;

-- Verification query (run this after migration to check the structure)
-- DESCRIBE `wp_tour_events`;
