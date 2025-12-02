<?php
/**
 * Tour REST API Endpoint
 * Replicates the Django API output format exactly
 */

// Register REST API route
add_action('rest_api_init', function () {
    register_rest_route('tour/v1', '/tour', array(
        'methods' => 'GET',
        'callback' => 'tour_api_get_tour_data',
        'permission_callback' => '__return_true', // Public endpoint
    ));
});

/**
 * Generate UUID v4
 */
function tour_generate_uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

/**
 * Format date to DD.MM.YYYY
 */
function tour_format_date($date) {
    if (empty($date)) return null;
    $dt = new DateTime($date);
    return $dt->format('d.m.Y');
}

/**
 * Format time to HH:MM
 */
function tour_format_time($time) {
    if (empty($time)) return null;
    $dt = new DateTime($time);
    return $dt->format('H:i');
}

/**
 * Get day name from number
 */
function tour_get_day_name($day_num) {
    $days = ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'];
    return isset($days[$day_num]) ? $days[$day_num] : '';
}

/**
 * Get type name from number
 */
function tour_get_type_name($type_num) {
    $types = ['Auftritt', 'Infos', 'GV', 'Anderes'];
    return isset($types[$type_num]) ? $types[$type_num] : '';
}

/**
 * Get cert name from number
 */
function tour_get_cert_name($cert_num) {
    if ($cert_num === null) return null;
    $certs = ['2G+', '2G', '3G', '3G+'];
    return isset($certs[$cert_num]) ? $certs[$cert_num] : null;
}

/**
 * Main API callback function
 */
function tour_api_get_tour_data($request) {
    global $wpdb;

    // Get the active season
    $active_season = $wpdb->get_row(
        "SELECT * FROM " . TOUR_SEASONS . " WHERE active = 1 LIMIT 1",
        ARRAY_A
    );

    if (!$active_season) {
        return new WP_Error('no_active_season', 'No active season found', array('status' => 404));
    }

    // Get all categories for the active season, ordered by sort
    $categories = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM " . TOUR_CATEGORIES . " WHERE season_id = %d ORDER BY sort ASC",
        $active_season['id']
    ), ARRAY_A);

    $data = array();

    foreach ($categories as $category) {
        // Get events for this category where date >= NOW() and fix = 1
        $events = $wpdb->get_results($wpdb->prepare(
            "SELECT e.*, t.name as transport_name
            FROM " . TOUR_EVENTS . " e
            LEFT JOIN " . TOUR_TRANSPORTS . " t ON e.transport_id = t.id
            WHERE e.category_id = %d
            AND e.date >= CURDATE()
            AND e.fix = 1
            ORDER BY e.date ASC, e.sort ASC",
            $category['id']
        ), ARRAY_A);

        // Transform events data
        $evenings = array();
        $previous_date = null;

        foreach ($events as $event) {
            $event_data = array(
                'id' => (int) $event['id'],
                'uuid' => $event['uuid'],
                'name' => $event['name'],
                'date' => tour_format_date($event['date']),
                'day' => tour_get_day_name($event['day']),
                'sort' => (int) $event['sort'],
                'type' => tour_get_type_name($event['type']),
                'organizer' => $event['organizer'],
                'location' => $event['location'],
                'play' => tour_format_time($event['play']),
                'gathering' => tour_format_time($event['gathering']),
                'makeup' => tour_format_time($event['makeup']),
                'warehouse' => tour_format_time($event['warehouse']),
                'sun' => tour_format_time($event['sun']),
                'meal' => (bool) $event['meal'],
                'drinks' => (bool) $event['drinks'],
                'trailer' => $event['trailer'],
                'cert' => tour_get_cert_name($event['cert']),
                'transport' => $event['transport_name'],
                'fix' => (bool) $event['fix'],
                'public' => (bool) $event['public'],
                'info' => $event['info'],
            );

            // Calculate firstOnDay
            if ($previous_date === $event['date']) {
                $event_data['firstOnDay'] = false;
            } else {
                $event_data['firstOnDay'] = true;
                $previous_date = $event['date'];
            }

            $evenings[] = $event_data;
        }

        // Build category data
        $category_data = array(
            'title' => $category['title'],
            'date_start' => tour_format_date($category['date_start']),
            'date_end' => tour_format_date($category['date_end']),
            'public' => (bool) $category['public'],
            'evening_count' => count($evenings),
            'evenings' => $evenings
        );

        $data[] = $category_data;
    }

    // Get current request URL and time
    $request_url = home_url(add_query_arg(array(), $request->get_route()));
    $request_time = current_time('d.m.Y H:i');

    // Build final response
    $response = array(
        'season' => $active_season['name'],
        'requestURL' => $request_url,
        'requestTime' => $request_time,
        'data' => $data
    );

    return rest_ensure_response($response);
}
