# Trombongos Tour Plugin v3.0 - Migration Guide

## Overview

This guide helps you migrate from the Django API to the new WordPress-native implementation.

## What's New in v3.0

### Complete WordPress Integration
- **4 New Database Tables**: Seasons, Categories, Transports, Events
- **REST API Endpoint**: `/wp-json/tour/v1/tour` - 100% compatible with Django format
- **Full Admin UI**: Comprehensive management interface for all tour data
- **Local Data Storage**: No more dependency on external Django API

### New Admin Pages

1. **Übersicht (Overview)** - Dashboard with statistics and quick links
2. **Auftritte (Events)** - Comprehensive event management with filtering
3. **Kategorien (Categories)** - Organize events into periods (e.g., "Fastnacht 2025")
4. **Saisons (Seasons)** - Manage tour seasons (only one can be active)
5. **Transport (Transports)** - Manage transportation methods

## Installation Steps

### 1. Activate the Plugin

When you activate the plugin, it will automatically create 4 new database tables:
- `wp_tour_seasons`
- `wp_tour_categories`
- `wp_tour_transports`
- `wp_tour_events`

### 2. Create Your First Season

1. Go to **Trombongos Tour > Saisons**
2. Click "Neu hinzufügen"
3. Fill in:
   - **Name**: Format YYYY/YYYY (e.g., `2025/2026`)
   - **Startdatum**: Season start date
   - **Enddatum**: Season end date
   - **Aktiv**: Check this box (only one season can be active)
4. Click "Hinzufügen"

### 3. Add Transport Methods

1. Go to **Trombongos Tour > Transport**
2. Add transport options like:
   - Car
   - Bus
   - Individual
   - etc.

### 4. Create Categories

1. Go to **Trombongos Tour > Kategorien**
2. For each period in your season, create a category:
   - **Titel**: e.g., "Fastnacht 2025", "Sommer 2025"
   - **Saison**: Select the active season
   - **Startdatum/Enddatum**: Date range for this period
   - **Sortierung**: Order (lower numbers appear first)
   - **Öffentlich**: Check if visible on public website

### 5. Add Events

1. Go to **Trombongos Tour > Auftritte**
2. Click "Neu hinzufügen"
3. Fill in all event details:
   - **Basic Info**: Name, Category, Date, Day, Type
   - **Location**: Organizer, Location
   - **Timing**: All times (only "Auftrittszeit" is required)
   - **Logistics**: Transport, Trailer, COVID cert, Meal/Drinks
   - **Status**: Fix (confirmed), Public (visible on website)
   - **Notes**: Additional information

## Data Migration from Django

### Option 1: Manual Entry
Use the admin interface to manually enter all events.

### Option 2: SQL Import (Recommended)
If you have access to the Django database, you can export and import the data:

1. Export data from Django:
```bash
python manage.py dumpdata api.season api.category api.transport api.event --indent 2 > tour_data.json
```

2. Create a custom import script or use WP-CLI to import the data

### Option 3: API Migration Script
Fetch data from the existing Django API and import it:

```php
<?php
// Run this once in WordPress admin or via WP-CLI
$response = wp_remote_get('https://trbapi.flind.ch/api/v1/tour/?format=json');
$data = json_decode(wp_remote_retrieve_body($response), true);

// Process and import $data into new tables
// (Contact developer for full migration script)
```

## Testing the API

### Test the REST Endpoint

Visit: `https://your-site.com/wp-json/tour/v1/tour`

The output should match this format exactly:

```json
{
  "season": "2025/2026",
  "requestURL": "https://your-site.com/wp-json/tour/v1/tour",
  "requestTime": "01.12.2025 14:30",
  "data": [
    {
      "title": "Fastnacht 2025",
      "date_start": "15.01.2025",
      "date_end": "05.03.2025",
      "public": true,
      "evening_count": 2,
      "evenings": [
        {
          "id": 1,
          "uuid": "abc-123-def",
          "name": "Auftritt Windisch",
          "date": "25.01.2025",
          "day": "Samstag",
          "sort": 0,
          "type": "Auftritt",
          "organizer": "Gemeinde",
          "location": "Dorfplatz",
          "play": "20:00",
          "assembly": "18:00",
          "loadup": null,
          "departure": "17:30",
          "soundcheck": null,
          "dinner": "19:00",
          "ending": "22:00",
          "meal": true,
          "drinks": true,
          "trailer": "Hans",
          "cert": null,
          "transport": "Car",
          "fix": true,
          "public": true,
          "info": "Parkplatz hinter Kirche",
          "firstOnDay": true
        }
      ]
    }
  ]
}
```

### Test the Frontend Shortcode

1. Create or edit a page
2. Add the shortcode: `[tourdaten]`
3. View the page - it should display the tour calendar

## Key Features

### Only Confirmed Future Events in API
The API endpoint only returns events where:
- `fix = 1` (confirmed)
- `date >= TODAY`
- Belong to categories in the active season

### firstOnDay Logic
When multiple events occur on the same date, only the first one has `firstOnDay: true`. This prevents duplicate date labels in the frontend.

### Active Season System
- Only ONE season can be active at a time
- Activating a season automatically deactivates all others
- The API only returns data from the active season

### Event Status Workflow
1. **Create Event** → `fix = 0`, `public = 0`
2. **Confirm Event** → Set `fix = 1`
3. **Make Public** → Set `public = 1`

## Shortcodes

### [tourdaten]
Displays the public tour calendar. Now fetches from local WordPress API instead of external Django API.

### [tourplan]
Original shortcode (still available for backward compatibility).

## Admin Capabilities

All admin pages require the `manage_options` capability (typically Administrator role).

## Database Schema

### Seasons Table
- Manages tour seasons (e.g., "2025/2026")
- Only one can be active
- Cannot delete active season
- Cannot delete season with categories

### Categories Table
- Groups events into periods
- Belongs to a season
- Has date range and sort order
- Cannot delete category with events

### Transports Table
- Simple list of transport methods
- Cannot delete if used by events

### Events Table
- Main data table with 20+ fields
- Links to category and transport
- Timestamps and UUID for each event

## Troubleshooting

### API Returns Empty Data
- Check if a season is active (Saisons page)
- Check if events are marked as "fix" (confirmed)
- Check if event dates are in the future

### Events Not Appearing
- Ensure the event's category belongs to the active season
- Ensure `fix = 1` for the event
- Ensure `date >= today`

### Database Tables Not Created
- Deactivate and reactivate the plugin
- Check WordPress debug log for errors

## Support

For issues or questions:
- Check the plugin overview dashboard for statistics
- Review the MIGRATION_GUIDE.md (this file)
- Contact the plugin developer

## Version History

### v3.0 (December 2025)
- Complete rewrite with WordPress-native implementation
- Added 4 database tables for local data storage
- Added REST API endpoint matching Django format
- Added comprehensive admin UI
- Removed dependency on external Django API

### v2.0 (Previous)
- Used external Django API
- Basic shortcode implementation
