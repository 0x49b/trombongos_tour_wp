<?php
/*
Plugin Name: Trombongos Tour Plugin
Plugin URI: http://www.trombongos.ch
Description: Dieses Plugin stellt die Tourdaten zur Verfügung. Diese werden von der API geladen.
Author: Florian Thiévent
Version: 2.1
Author URI: https://www.thievent.org
*/

defined('WPINC') || die();

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

function do_shortcode_tourplan() {
   $html = '';
   if (is_admin() || wp_doing_ajax()) {
      // ...
   } else {
      // Parse the shortcode's options.
      $url = 'https://trbapi.thievent.org/api/v1/tour/?format=json';
      // Create a Curl Handle.
      $ch = curl_init();
      // Standard PHP/Curl options for a simple GET request.
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      // Execute the request.
      $json_response = curl_exec($ch);
      $response_code = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
      // Close the Curl Handle.
      curl_close($ch);
      if ($response_code != 200) {
         $html .= sprintf('<p><strong>ERROR:</strong> Got HTTP response %d from the server.</p>', $response_code);
      } elseif (empty($json_response)) {
         // No data returned from the server.
         $html .= sprintf('<p><strong>ERROR:</strong> Empty respone from the server.</p>');
      }  elseif (empty($ipsum_paragraphs = json_decode($json_response, true))) {
         $html .= sprintf(
            '<p><strong>ERROR:</strong> Failed to process the JSON response.</p><pre>%s</pre>',
            esc_html($json_response)
         );
      } else {

         $oldtitle= NULL;
         $olddate = NULL;
         $oldevent = NULL;
         $i = 0;

         $total_evenings = 0;
         foreach ($ipsum_paragraphs['data'] as $date) {
            if( $date['public']){
               $total_evenings += $date['evening_count'];
            }
         }
        
         if($total_evenings < 1){
            $html .= sprintf('<p>Unsere Tour %s ist leider vorbei. Wir freuen uns dich in der neuen Saison begrüssen zu dürfen.</p>', $ipsum_paragraphs['season']);
         } else {

         $html .= sprintf('
                  <div>
                  <h3>Unsere Tour %s</h3>
                  <table style="border-collapse: collapse;">
                     <tbody>
                     <tr style="text-align: left;">
                        <th>Datum</th>
                        <th style="padding-left: 10px;">Anlass</th>
                        <th style="padding-left: 10px;">Auftrittszeit</th>
                     </tr>', $ipsum_paragraphs['season']);

         foreach ($ipsum_paragraphs['data'] as $date) {
            if ($date['evening_count'] > 0 && $date['public']) {
                  foreach ($date['evenings'] as $evening) {
                     if ($date['title'] != $oldtitle) {
                  
                        $html .= sprintf('
                        <tr>
                     <td colspan="3" style="background-color: #eaeaea;">%s</td>
                     </tr>',$date['title']);

                     
                  }
                     foreach ($evening as $event) {
                        if ($evening["public"] == 1) {
                              if ($evening['name'] != $oldevent && $evening['fix']) {
                                 $html .= '<tr style="border-bottom: 1px solid black;"><td class="col-3">';
                                 if ($evening['date'] != $olddate) {
                                       $html .= $evening['date'];
                                 }
                                 $html .= sprintf('</td><td style="padding-left: 10px;">%s</td><td style="padding-left: 10px;">%s</td></tr>',$evening['name'], $evening['play']);         
                              }
                              $olddate  = $evening['date'];
                              $oldevent = $evening['name'];
                        }
                     }
                     $oldtitle = $date['title'];
                  }
            }
            $i++;
         }

         $html .= "</tbody></table></div>";

            }
         }

      }
      return $html;
}
add_shortcode('tourplan', 'do_shortcode_tourplan');


/*--------------------------------------------------------------------------------------------------------------------------------------------*\
					Alle Klassen aus dem Ordner <class> inkludieren
\*--------------------------------------------------------------------------------------------------------------------------------------------*/
include_once(plugin_dir_path( __FILE__ )."class/class-tour-list-tables.php");

