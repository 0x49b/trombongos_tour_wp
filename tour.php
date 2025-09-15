<?php
/*
Plugin Name: Trombongos Tour Plugin
Plugin URI: http://www.trombongos.ch
Description: Dieses Plugin stellt die Tourdaten zur Verfügung. Diese können über das Backend bearbeitet werden. Zudem werden diese unter der url tour.trombongos.ch den Mitgliedern zur Verfügung gestellt.
Author: Florian Thiévent
Version: 2.0
Author URI: https://www.thievent.org
*/

/**
 * https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
 */

global $wpdb;

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Konfigurations Konstanten
\*--------------------------------------------------------------------------------------------------------------------------------------------*/

define('TOUR_MENU_CAPABILITY', 'manage_options');
define('TOUR_DATA_SHORTCODE', 'tourdaten');

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Datenbank Konstanten definieren
\*--------------------------------------------------------------------------------------------------------------------------------------------*/

define('TOUR_SAISON', $wpdb->prefix . "tour_saison");       // Saison

define('TOURTERMINE', $wpdb->prefix . 'termine');           // Auftrittstermine
define('TOURDATUM', $wpdb->prefix . 'datum');               // Daten
define('TOURTAGE', $wpdb->prefix . 'tage');                 // TageMapping
define('TOURGRUPPEN', $wpdb->prefix . 'tage_gruppen');      // Tage - Gruppen Mapping
define('TOURTRANSPORT', $wpdb->prefix . 'transport');       // TransportMapping

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Plugin Activation Hook
                    1. Datenbanktabellen anlegen
\*--------------------------------------------------------------------------------------------------------------------------------------------*/
function tour_create_database_tables()
{

    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    /**
     * Create Saison Table
     */
    $sql = "CREATE TABLE `" . TOUR_SAISON . "` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(9) NOT NULL,
    `start` DATE NULL DEFAULT NULL,
    `end`   DATE NULL DEFAULT NULL,
    `active` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

}

function tour_plugin_activation()
{
    tour_create_database_tables();
}

register_activation_hook(__FILE__, 'tour_plugin_activation');

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Plugin Scripts & Styles (Backend)
\*--------------------------------------------------------------------------------------------------------------------------------------------*/

function tour_scripts_backend()
{

    // Tour Script
    //wp_register_script( 'tour-script', plugins_url('/tour/functions/backend/js/backend.js'), false, '1.0', false );
    //wp_enqueue_script( 'tour-script');

    //wp_enqueue_script( 'jquery-ui-datepicker' );
}

add_action('init', 'tour_scripts_backend');
/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Admin Seite einrichten (im Backend in der linken Spalte)
\*--------------------------------------------------------------------------------------------------------------------------------------------*/

function tour_setup_admin_menus()
{

    add_menu_page(
        'Trombongos Tour',
        'Trombongos Tour',
        TOUR_MENU_CAPABILITY,
        'trb_tour',
        'tour_overview',
        'dashicons-megaphone'
    );

    add_submenu_page(
        'trb_tour',
        'Neue Saison',
        'Saisons',
        TOUR_MENU_CAPABILITY,
        'add_season',
        'tour_season_view'
    );

}

add_action("admin_menu", "tour_setup_admin_menus");

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Admin Functions inkludieren
\*--------------------------------------------------------------------------------------------------------------------------------------------*/
function tour_overview()
{
    if (is_admin()) {
        include_once(plugin_dir_path(__FILE__) . "admin/tour_overview.php");
    }
}

function tour_season_view()
{
    if (is_admin()) {
        include_once __DIR__ . "/admin/tour_season_view.php";
    }
}

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
                    Shortcodes inkludieren
\*--------------------------------------------------------------------------------------------------------------------------------------------*/

include_once(plugin_dir_path(__FILE__) . "public/tour_shortcode_tourdaten.php");
// Register the shortcode
add_shortcode(TOUR_DATA_SHORTCODE, 'tourdaten_shortcode');


/*--------------------------------------------------------------------------------------------------------------------------------------------*\
                    REST erweitern /trombongos/v1
\*--------------------------------------------------------------------------------------------------------------------------------------------*/
/**
 * Grab latest post by author
 * @param array $data Options for the function.
 * @return string|null Post title for the latest, * or null if none.
 */
function my_awesome_func($data)
{

    $posts = get_posts(array(
        'author' => $data['id'],
    ));

    if (empty($posts)) {
        return [];
    }

    return [
        'ID'    => $posts[0]->ID,
        'title' => get_the_title($posts[0]),
        'link'  => get_permalink($posts[0]),
        ];
}

add_action('rest_api_init', function () {
    register_rest_route('trombongos/v1', '/author/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'my_awesome_func',
    ));
});


