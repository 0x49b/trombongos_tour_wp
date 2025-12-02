<?php
/*
Plugin Name: Trombongos Tour Plugin
Plugin URI: http://www.trombongos.ch
Description: Dieses Plugin stellt die Tourdaten zur Verfügung. Diese können über das Backend bearbeitet werden. Zudem werden diese unter der url tour.trombongos.ch den Mitgliedern zur Verfügung gestellt.
Author: Florian Thiévent
Version: 3.0
Author URI: https://www.thievent.org
*/

/**
 * https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
 */

global $wpdb;

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Datenbank Konstanten definieren
\*--------------------------------------------------------------------------------------------------------------------------------------------*/

define('TOUR_SEASONS', $wpdb->prefix . 'tour_seasons');
define('TOUR_CATEGORIES', $wpdb->prefix . 'tour_categories');
define('TOUR_TRANSPORTS', $wpdb->prefix . 'tour_transports');
define('TOUR_EVENTS', $wpdb->prefix . 'tour_events');

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

add_action("admin_menu", "setup_theme_admin_menus");


function setup_theme_admin_menus()
{
    add_menu_page('Trombongos Tour', 'Trombongos Tour', 'manage_options',
        'trb_tour', 'tour_overview', 'dashicons-calendar-alt');

    add_submenu_page('trb_tour',
        'Übersicht', 'Übersicht', 'manage_options',
        'trb_tour', 'tour_overview');

    add_submenu_page('trb_tour',
        'Auftritte', 'Auftritte', 'manage_options',
        'tour_events', 'tour_events_page');

    add_submenu_page('trb_tour',
        'Kategorien', 'Kategorien', 'manage_options',
        'tour_categories', 'tour_categories_page');

    add_submenu_page('trb_tour',
        'Saisons', 'Saisons', 'manage_options',
        'tour_seasons', 'tour_seasons_page');

    add_submenu_page('trb_tour',
        'Transport', 'Transport', 'manage_options',
        'tour_transports', 'tour_transports_page');
}

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Backend Functions inkludieren
\*--------------------------------------------------------------------------------------------------------------------------------------------*/
function tour_overview()
{
    include_once(plugin_dir_path(__FILE__) . "functions/backend/tour_overview.php");
}

function tour_events_page()
{
    include_once(plugin_dir_path(__FILE__) . "functions/backend/tour_events.php");
}

function tour_categories_page()
{
    include_once(plugin_dir_path(__FILE__) . "functions/backend/tour_categories.php");
}

function tour_seasons_page()
{
    include_once(plugin_dir_path(__FILE__) . "functions/backend/tour_seasons.php");
}

function tour_transports_page()
{
    include_once(plugin_dir_path(__FILE__) . "functions/backend/tour_transports.php");
}

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Frontend Functions inkludieren
\*--------------------------------------------------------------------------------------------------------------------------------------------*/
function tour_tourplan_front()
{
    include_once(plugin_dir_path(__FILE__) . "functions/frontend/tour_tourplan_front.php");
}

add_shortcode('tourplan', 'tour_tourplan_front');

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Alle Klassen aus dem Ordner <class> inkludieren
\*--------------------------------------------------------------------------------------------------------------------------------------------*/
include_once(plugin_dir_path(__FILE__) . "class/class-tour-list-tables.php");

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					REST API inkludieren
\*--------------------------------------------------------------------------------------------------------------------------------------------*/
include_once(plugin_dir_path(__FILE__) . "functions/api/tour-api.php");

/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Plugin Activation Hook
                    1. Datenbanktabellen anlegen
\*--------------------------------------------------------------------------------------------------------------------------------------------*/
function tour_create_database_tables()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

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
    dbDelta($sql_seasons);

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
    dbDelta($sql_transports);

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
    dbDelta($sql_categories);

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
      play TIME NOT NULL,
      assembly TIME DEFAULT NULL,
      loadup TIME DEFAULT NULL,
      departure TIME DEFAULT NULL,
      soundcheck TIME DEFAULT NULL,
      dinner TIME DEFAULT NULL,
      ending TIME DEFAULT NULL,
      meal TINYINT(1) DEFAULT 0 NOT NULL,
      drinks TINYINT(1) DEFAULT 0 NOT NULL,
      trailer VARCHAR(255) DEFAULT NULL,
      cert TINYINT DEFAULT NULL,
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
    dbDelta($sql_events);
}

function tour_plugin_activation()
{
    tour_create_database_tables();
}

register_activation_hook(__FILE__, 'tour_plugin_activation');


function tourdaten_shortcode()
{
    // Fetch the API response from local WordPress REST API
    $response = wp_remote_get(rest_url('tour/v1/tour'));

    // Check if the request was successful
    if (is_wp_error($response)) {
        return 'Failed to retrieve data.';
    }

    $body = wp_remote_retrieve_body($response);
    $res = json_decode($body, true);

    // Check if the response contains valid JSON
    if ($res === null || !isset($res['data'])) {
        return 'Invalid response format.';
    }

    $dates = $res['data'];
    $oldtitle = NULL;
    $olddate = NULL;
    $oldevent = NULL;
    $i = 0;

    // To prevent multiple appearances of "1. Wochenende"
    $weekend_shown = false;

    // Start building the HTML content as a string
    $output = "
    <div class=\"col-md-12\">
    <h3 class=\"wp-block-heading\">Tourdaten " . esc_html($res['season']) . "</h3>
    <table class=\"table table-sm table-responsive\">
        <tbody>
        <tr>
            <th class=\"col-3\" style=\"border-top: 1px solid black\">Datum</th>
            <th class=\"col-8\" style=\"border-top: 1px solid black\">Anlass</th>
            <th class=\"col-1\" style=\"border-top: 1px solid black\">Auftrittszeit</th>
        </tr>";

    // Loop through dates
    foreach ($dates as $date) {
        if ($date['evening_count'] > 0 && $date['public']) {
            foreach ($date['evenings'] as $evening) {
                if (isset($dates[$i]['title']) && $dates[$i]['title'] != $oldtitle) {
                    $output .= "<tr class=\"bg-secondary text-light\">";
                    $output .= "<td colspan=\"3\" style=\"background-color: #d1d1d1\" class=\"col-sm-12 col-12 bg-secondary text-light\">" . esc_html($dates[$i]['title']) . "</td>";
                    $output .= "</tr>";
                    $oldtitle = $dates[$i]['title'];
                }

                if ($evening["public"] == 1 && isset($evening['fix']) && $evening['fix']) {
                    $output .= "<tr>
                        <td class=\"col-3\">";
                    if (isset($evening['date']) && $evening['date'] != $olddate) {
                        $output .= esc_html($evening['date']);
                    }
                    $output .= "</td>
                        <td class=\"col-8 \" style=\"padding-left: 1em;\">" . esc_html($evening['name']) . "</td>
                        <td class=\"col-1\" style=\"padding-left: 1em;\">" . esc_html($evening['play']) . "</td>
                    </tr>";

                }
                $olddate = $evening['date'] ?? null;
                $oldevent = $evening['name'] ?? null;
            }
            $oldtitle = $dates[$i]['title'];
        }
        $i++;
    }

    $output .= "
        </tbody>
    </table>
    </div>";

    // Return the generated HTML content
    return $output;
}

// Register the shortcode
add_shortcode('tourdaten', 'tourdaten_shortcode');

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
        return null;
    }

    return $posts[0];
}

add_action('rest_api_init', function () {
    register_rest_route('tour/v1', '/author/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'my_awesome_func',
    ));
});


