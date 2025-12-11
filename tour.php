<?php
/*
Plugin Name: Trombongos Tour Plugin
Plugin URI: http://www.trombongos.ch
Description: Dieses Plugin stellt die Tourdaten zur Verfügung. Diese können über das Backend bearbeitet werden. Zudem werden diese unter der url tour.trombongos.ch den Mitgliedern zur Verfügung gestellt.
Author: Florian Thiévent
Version: 3.0
Author URI: https://www.thievent.org
*/


global $wpdb;

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Datenbank Konstanten definieren
\*--------------------------------------------------------------------------------------------------------------------------------------------*/

define( 'TOUR_SEASONS', $wpdb->prefix . 'tour_seasons' );
define( 'TOUR_CATEGORIES', $wpdb->prefix . 'tour_categories' );
define( 'TOUR_TRANSPORTS', $wpdb->prefix . 'tour_transports' );
define( 'TOUR_EVENTS', $wpdb->prefix . 'tour_events' );

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Plugin Scripts & Styles (Backend)
\*--------------------------------------------------------------------------------------------------------------------------------------------*/

function tour_scripts_backend() {
	// Enqueue admin styles
	wp_enqueue_style(
		'tour-admin-styles',
		plugins_url( 'assets/css/admin.css', __FILE__ ),
		array(),
		'1.3.0'
	);
}

add_action( 'admin_enqueue_scripts', 'tour_scripts_backend' );
/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Admin Seite einrichten (im Backend in der linken Spalte)
\*--------------------------------------------------------------------------------------------------------------------------------------------*/

add_action( "admin_menu", "setup_theme_admin_menus" );


function setup_theme_admin_menus() {
	add_menu_page( 'Trombongos Tour', 'Trombongos Tour', 'manage_options',
		'trb_tour', 'tour_overview', 'dashicons-calendar-alt' );

	add_submenu_page( 'trb_tour',
		'Übersicht', 'Übersicht', 'manage_options',
		'trb_tour', 'tour_overview' );

	add_submenu_page( 'trb_tour',
		'Auftritte', 'Auftritte', 'manage_options',
		'tour_events', 'tour_events_page' );

	add_submenu_page( 'trb_tour',
		'Kategorien', 'Kategorien', 'manage_options',
		'tour_categories', 'tour_categories_page' );

	add_submenu_page( 'trb_tour',
		'Saisons', 'Saisons', 'manage_options',
		'tour_seasons', 'tour_seasons_page' );

	add_submenu_page( 'trb_tour',
		'Transport', 'Transport', 'manage_options',
		'tour_transports', 'tour_transports_page' );
}

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Backend Functions inkludieren
\*--------------------------------------------------------------------------------------------------------------------------------------------*/
function tour_overview() {
	include_once( plugin_dir_path( __FILE__ ) . "functions/backend/tour_overview.php" );
}

function tour_events_page() {
	include_once( plugin_dir_path( __FILE__ ) . "functions/backend/tour_events.php" );
}

function tour_categories_page() {
	include_once( plugin_dir_path( __FILE__ ) . "functions/backend/tour_categories.php" );
}

function tour_seasons_page() {
	include_once( plugin_dir_path( __FILE__ ) . "functions/backend/tour_seasons.php" );
}

function tour_transports_page() {
	include_once( plugin_dir_path( __FILE__ ) . "functions/backend/tour_transports.php" );
}

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Alle Klassen aus dem Ordner <class> inkludieren
\*--------------------------------------------------------------------------------------------------------------------------------------------*/
include_once( plugin_dir_path( __FILE__ ) . "class/class-tour-list-tables.php" );

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					REST API inkludieren
\*--------------------------------------------------------------------------------------------------------------------------------------------*/
include_once( plugin_dir_path( __FILE__ ) . "functions/api/tour-api.php" );

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Plugin Activation Hook
                    1. Datenbanktabellen anlegen
\*--------------------------------------------------------------------------------------------------------------------------------------------*/
function tour_create_database_tables() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	// Create Seasons Table
	$sql_seasons = "CREATE TABLE " . TOUR_SEASONS . " (
      id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      uuid CHAR(36) NOT NULL,
      name VARCHAR(9) NOT NULL,
      start_date DATE NOT NULL,
      end_date DATE NOT NULL,
      active TINYINT(1) DEFAULT 0 NOT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
      updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
      PRIMARY KEY (id),
      UNIQUE KEY uuid (uuid),
      KEY active (active)
    ) $charset_collate;";
	dbDelta( $sql_seasons );

	// Create Transports Table
	$sql_transports = "CREATE TABLE " . TOUR_TRANSPORTS . " (
      id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      uuid CHAR(36) NOT NULL,
      name VARCHAR(255) NOT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
      updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
      PRIMARY KEY (id),
      UNIQUE KEY uuid (uuid)
    ) $charset_collate;";
	dbDelta( $sql_transports );

	// Create Categories Table
	$sql_categories = "CREATE TABLE " . TOUR_CATEGORIES . " (
      id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      uuid CHAR(36) NOT NULL,
      title VARCHAR(255) NOT NULL,
      date_start DATE NOT NULL,
      date_end DATE NOT NULL,
      public TINYINT(1) DEFAULT 1 NOT NULL,
      sort INT DEFAULT 0 NOT NULL,
      season_id BIGINT(20) UNSIGNED NOT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
      updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
      PRIMARY KEY (id),
      UNIQUE KEY uuid (uuid),
      KEY season_id (season_id),
      KEY sort (sort)
    ) $charset_collate;";
	dbDelta( $sql_categories );

	// Create Events Table
	$sql_events = "CREATE TABLE " . TOUR_EVENTS . " (
      id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      uuid CHAR(36) NOT NULL,
      name VARCHAR(255) NOT NULL,
      category_id BIGINT(20) UNSIGNED NOT NULL,
      transport_id BIGINT(20) UNSIGNED DEFAULT NULL,
      date DATE NOT NULL,
      day TINYINT NOT NULL,
      sort INT DEFAULT 0 NOT NULL,
      type TINYINT DEFAULT 0 NOT NULL,
      organizer VARCHAR(255) DEFAULT NULL,
      location VARCHAR(255) DEFAULT NULL,
      maps_url VARCHAR(500) DEFAULT NULL,
      play TIME NOT NULL,
      gathering TIME DEFAULT NULL,
      makeup TIME DEFAULT NULL,
      warehouse TIME DEFAULT NULL,
      sun TIME DEFAULT NULL,
      trailer VARCHAR(255) DEFAULT NULL,
      fix TINYINT(1) DEFAULT 0 NOT NULL,
      public TINYINT(1) DEFAULT 0 NOT NULL,
      info TEXT DEFAULT NULL,
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
      updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
      PRIMARY KEY (id),
      UNIQUE KEY uuid (uuid),
      KEY category_id (category_id),
      KEY transport_id (transport_id),
      KEY date (date),
      KEY event_query (public, fix, date)
    ) $charset_collate;";
	dbDelta( $sql_events );
}

function tour_plugin_activation() {
	tour_create_database_tables();
	tour_run_migrations();
}

register_activation_hook( __FILE__, 'tour_plugin_activation' );

/**
 * Run database migrations
 */
function tour_run_migrations() {
	global $wpdb;

	// Get the current plugin version
	$current_version = get_option( 'tour_plugin_version', '0.0' );
	$new_version     = '3.0';

	// Only run migrations if version has changed
	if ( version_compare( $current_version, $new_version, '<' ) ) {
		$migration_dir   = plugin_dir_path( __FILE__ ) . 'db_migration/';
		$migration_files = glob( $migration_dir . '*.sql' );

		if ( $migration_files ) {
			foreach ( $migration_files as $migration_file ) {
				$migration_name = basename( $migration_file, '.sql' );

				// Check if this migration has already been run
				$migration_key = 'tour_migration_' . $migration_name;
				$migration_run = get_option( $migration_key, false );

				if ( ! $migration_run ) {
					// Read the SQL file
					$sql = file_get_contents( $migration_file );

					if ( $sql ) {
						// Split by semicolons to execute multiple statements
						$statements = array_filter(
							array_map( 'trim', explode( ';', $sql ) ),
							function ( $statement ) {
								// Filter out empty statements and comments
								return ! empty( $statement ) && strpos( trim( $statement ), '--' ) !== 0;
							}
						);

						// Execute each statement
						foreach ( $statements as $statement ) {
							if ( ! empty( $statement ) ) {
								$wpdb->query( $statement );
							}
						}

						// Mark migration as completed
						update_option( $migration_key, true );
					}
				}
			}
		}

		// Update plugin version
		update_option( 'tour_plugin_version', $new_version );
	}
}

// Run migrations on plugin update
add_action( 'plugins_loaded', 'tour_run_migrations' );

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Shortcode Definition
\*--------------------------------------------------------------------------------------------------------------------------------------------*/

include_once( plugin_dir_path( __FILE__ ) . "functions/frontend/tourdaten_shortcode.php" );

// Register the shortcode
add_shortcode( 'tourdaten', 'tourdaten_shortcode' );
