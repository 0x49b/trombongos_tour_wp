<?php

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
