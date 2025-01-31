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
					Datenbank Konstanten definieren
\*--------------------------------------------------------------------------------------------------------------------------------------------*/

define('TOUR_SAISON', $wpdb->prefix . "tour_saison");        // Saison

define('TOURTERMINE', $wpdb->prefix . 'termine');        // Auftrittstermine
define('TOURDATUM', $wpdb->prefix . 'datum');                // Daten
define('TOURTAGE', $wpdb->prefix . 'tage');                // TageMapping
define('TOURGRUPPEN', $wpdb->prefix . 'tage_gruppen');    // Tage - Gruppen Mapping
define('TOURTRANSPORT', $wpdb->prefix . 'transport');        // TransportMapping

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
function tour_overview()
{
    include_once(plugin_dir_path(__FILE__) . "functions/backend/tour_overview.php");
}

function tour_add_event()
{
    include_once(plugin_dir_path(__FILE__) . "functions/backend/tour_new.php");
}

function tour_settings()
{
    include_once(plugin_dir_path(__FILE__) . "functions/backend/tour_settings.php");
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
    $sql = "CREATE TABLE " . TOUR_SAISON . " (
      id int NOT NULL AUTO_INCREMENT,
      name varchar(9) NOT NULL,
      start date DEFAULT '0000-00-00 00:00:00' NOT NULL,
      end date DEFAULT '0000-00-00 00:00:00' NOT NULL,
      active tinyint(1) DEFAULT '0' NOT NULL,
      PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

}

function tour_plugin_activation()
{
    tour_create_database_tables();
}

register_activation_hook(__FILE__, 'tour_plugin_activation');


function tourdaten_shortcode()
{
    // Fetch the API response using wp_remote_get for better handling of HTTP requests in WordPress
    $response = wp_remote_get('https://trbapi.flind.ch/api/v1/tour/?format=json');

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
 * @return string|null Post title for the latest,  * or null if none.
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


