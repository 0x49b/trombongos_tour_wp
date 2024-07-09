<?php
/*
Plugin Name: Trombongos Tour Plugin
Plugin URI: http://www.trombongos.ch
Description: Dieses Plugin stellt die Tourdaten zur Verfügung. Diese können über das Backend bearbeitet werden. Zudem werden diese unter der url tour.trombongos.ch den Mitgliedern zur Verfügung gestellt.
Author: Florian Thiévent
Version: 2.0
Author URI: https://www.thievent.org
*/

global $wpdb;

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Datenbank Konstanten definieren
\*--------------------------------------------------------------------------------------------------------------------------------------------*/

define('TOURTERMINE', $wpdb->prefix.'termine' );  		// Auftrittstermine
define('TOURDATUM', $wpdb->prefix.'datum');				// Daten
define('TOURTAGE', $wpdb->prefix.'tage'); 				// TageMapping
define('TOURGRUPPEN', $wpdb->prefix.'tage_gruppen');	// Tage - Gruppen Mapping
define('TOURTRANSPORT', $wpdb->prefix.'transport');		// TransportMapping
/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Plugin Scripts & Styles (Backend)
\*--------------------------------------------------------------------------------------------------------------------------------------------*/

		function tour_scripts_backend() {

			// Tour Script
			//wp_register_script( 'tour-script', plugins_url('/tour/functions/backend/js/backend.js'), false, '1.0', false );
			//wp_enqueue_script( 'tour-script');

			//wp_enqueue_script( 'jquery-ui-datepicker' );
		}

		add_action( 'init', 'tour_scripts_backend' );
/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Admin Seite einrichten (im Backend in der linken Spalte)
\*--------------------------------------------------------------------------------------------------------------------------------------------*/

 add_action("admin_menu", "setup_theme_admin_menus");


 function setup_theme_admin_menus() {

    add_menu_page('Trombongos Tour', 'Trombongos Tour', 'manage_options',
        'trb_tour', 'tour_overview', 'dashicons-calendar-alt');

    
	/*
	add_submenu_page('trb_tour',
        'Auftritt hinzufügen', 'Neuer Auftritt', 'manage_options',
        'add_event', 'tour_add_event');


    add_submenu_page('trb_tour',
        'Einstellungen', 'Einstellungen', 'manage_options',
        'settings', 'tour_settings');
	*/
}

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Backend Functions inkludieren
\*--------------------------------------------------------------------------------------------------------------------------------------------*/
function tour_overview() {	  include_once(plugin_dir_path( __FILE__ )."functions/backend/tour_overview.php");}
function tour_add_event() {	  include_once(plugin_dir_path( __FILE__ )."functions/backend/tour_new.php");}
function tour_settings() {	  include_once(plugin_dir_path( __FILE__ )."functions/backend/tour_settings.php");}

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Frontend Functions inkludieren
\*--------------------------------------------------------------------------------------------------------------------------------------------*/
function tour_tourplan_front() {	include_once(plugin_dir_path( __FILE__ )."functions/frontend/tour_tourplan_front.php");}
add_shortcode('tourplan', 'tour_tourplan_front');

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Alle Klassen aus dem Ordner <class> inkludieren
\*--------------------------------------------------------------------------------------------------------------------------------------------*/
include_once(plugin_dir_path( __FILE__ )."class/class-tour-list-tables.php");