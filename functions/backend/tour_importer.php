<?php
/**
 * Handles the Google Sheet import functionality.
 */

echo '<div class="wrap"><h1>Google Sheet Importer</h1>';

	$action = $_POST['action'] ?? 'default';

	// A simple router for the importer steps
	switch ( $action ) {
		case 'preview':
			handle_preview_step();
			break;
		case 'import':
			handle_import_step();
			break;
		default:
			display_initial_form();
			break;
	}

	echo '</div>'; // close wrap

// Step 1: Display the initial form for the Google Sheet URL
function display_initial_form() {
	?>
    <p>Füge hier den Google Sheet Link ein um die Tourdaten zu importieren.</p>
    <div class="postbox">
        <div class="postbox-header"><h2>Schritt 1: Google Sheet URL einfügen</h2></div>
        <div class="inside">
            <form method="post" action="<?php echo admin_url( 'admin.php?page=tour_importer' ); ?>">
				<?php wp_nonce_field( 'tour_import_preview', 'tour_importer_nonce' ); ?>
                <input type="hidden" name="action" value="preview">
                <table class="form-table">
                    <tr>
                        <th><label for="sheet_url">Google Sheet URL</label></th>
                        <td>
                            <input type="url" name="sheet_url" id="sheet_url" class="large-text"
                                   placeholder="https://docs.google.com/spreadsheets/d/..." required>
                            <p class="description">
                                Stelle sicher, dass der Link für "Jeder, der über den Link verfügt" freigegeben ist.
                            </p>
                        </td>
                    </tr>
                </table>
				<?php submit_button( 'Vorschau anzeigen' ); ?>
            </form>
        </div>
    </div>
	<?php
}

// Step 2: Handle fetching, parsing, and displaying the preview
function handle_preview_step() {
	if ( ! isset( $_POST['tour_importer_nonce'] ) || ! wp_verify_nonce( $_POST['tour_importer_nonce'], 'tour_import_preview' ) ) {
		wp_die( 'Nonce verification failed!' );
	}

	$sheet_url = esc_url_raw( $_POST['sheet_url'] );
	preg_match( '/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $sheet_url, $matches );

	if ( ! isset( $matches[1] ) ) {
		echo '<div class="notice notice-error"><p>Ungültige Google Sheet URL. Konnte keine Sheet ID finden.</p></div>';
		display_initial_form();
		return;
	}
	$sheet_id = $matches[1];
	$csv_url = "https://docs.google.com/spreadsheets/d/{$sheet_id}/export?format=csv&gid=0";

	$response = wp_remote_get( $csv_url, [ 'timeout' => 30 ] );

	if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
		echo '<div class="notice notice-error"><p>Fehler beim Abrufen des Google Sheets. Stelle sicher, dass der Link korrekt und öffentlich ist.</p></div>';
		display_initial_form();
		return;
	}

    // Check content type to ensure it's a CSV, not an Excel file
    $content_type = wp_remote_retrieve_header( $response, 'content-type' );
    if ( strpos( $content_type, 'text/csv' ) === false ) {
        echo '<div class="notice notice-error"><p><strong>Fehler:</strong> Der Link scheint zu einer Excel-Datei (.xlsx) zu führen, nicht zu einem nativen Google Sheet.</p>';
        echo '<p>Bitte öffne die Datei in Google Drive, klicke auf <strong>"Datei" &rarr; "Als Google Tabellen speichern"</strong> und verwende den Freigabe-Link der neuen Datei.</p></div>';
        display_initial_form();
        return;
    }

	$csv_data = wp_remote_retrieve_body( $response );
	$events = parse_sheet_data( $csv_data );

	if ( empty( $events ) ) {
		echo '<div class="notice notice-warning"><p>Keine gültigen Daten in der Tabelle gefunden. Überprüfe, ob die Spalte "Name" ausgefüllt ist.</p></div>';
		display_initial_form();
		return;
	}

	// Append location to event name
	foreach ($events as &$event) {
		if (!empty($event['location'])) {
			$event['name'] .= ' ' . $event['location'];
		}
	}
	unset($event); // Unset reference

	// 1. Group events and find min/max dates for each category
	$grouped_events = [];
	$category_dates = [];
	foreach ($events as $event) {
		$category_name = trim($event['category_name'] ?? 'Uncategorized');
		$grouped_events[$category_name][] = $event;

		if ($category_name !== 'Uncategorized') {
			$event_date = strtotime(str_replace('.', '-', $event['date'] ?? ''));
			if ($event_date) {
				if (!isset($category_dates[$category_name])) {
					$category_dates[$category_name] = ['min' => $event_date, 'max' => $event_date];
				} else {
					if ($event_date < $category_dates[$category_name]['min']) $category_dates[$category_name]['min'] = $event_date;
					if ($event_date > $category_dates[$category_name]['max']) $category_dates[$category_name]['max'] = $event_date;
				}
			}
		}
	}

	// 2. Sort categories by min date
	uasort($category_dates, function($a, $b) { return $a['min'] <=> $b['min']; });

	// 3. Create a map of category data (sort, start_date, end_date)
	$category_data_map = [];
	$category_sort_number = 1;
	foreach ($category_dates as $category_name => $dates) {
		$category_data_map[$category_name] = [
			'sort'       => $category_sort_number++,
			'date_start' => date('Y-m-d', $dates['min']),
			'date_end'   => date('Y-m-d', $dates['max']),
		];
	}

	// 4. Sort events within groups, assign sort numbers, and add all data
	$final_events = [];
	// Iterate through the sorted categories to maintain order
	foreach (array_keys($category_data_map) as $category_name) {
		if (!isset($grouped_events[$category_name])) continue;

		$events_in_group = &$grouped_events[$category_name];

		// Sort events by date and play time
		usort($events_in_group, function($a, $b) {
			$date_a = strtotime(str_replace('.', '-', $a['date'] ?? ''));
			$date_b = strtotime(str_replace('.', '-', $b['date'] ?? ''));
			if ($date_a !== $date_b) {
				return $date_a <=> $date_b;
			}
			$play_a = strtotime($a['play'] ?? '');
			$play_b = strtotime($b['play'] ?? '');
			return $play_a <=> $play_b;
		});

		// Assign data to each event
		$event_sort_number = 1;
		foreach ($events_in_group as &$event) {
			$event['sort'] = $event_sort_number++;
			$event['category_sort'] = $category_data_map[$category_name]['sort'];
			$event['category_date_start'] = $category_data_map[$category_name]['date_start'];
			$event['category_date_end'] = $category_data_map[$category_name]['date_end'];
		}

		$final_events = array_merge($final_events, $events_in_group);
	}
	unset($event, $events_in_group);

	// Add any 'Uncategorized' events at the end
	if (isset($grouped_events['Uncategorized'])) {
		$final_events = array_merge($final_events, $grouped_events['Uncategorized']);
	}

	// Temporarily store parsed data for the next step
	set_transient( 'tour_import_data', $final_events, HOUR_IN_SECONDS );

	display_preview_table_and_options( $final_events );
}

// Step 3: Handle the actual import process
function handle_import_step() {
	if ( ! isset( $_POST['tour_importer_nonce'] ) || ! wp_verify_nonce( $_POST['tour_importer_nonce'], 'tour_import_run' ) ) {
		wp_die( 'Nonce verification failed!' );
	}

	global $wpdb;

	$events = get_transient( 'tour_import_data' );
	if ( ! $events ) {
		echo '<div class="notice notice-error"><p>Importdaten nicht gefunden oder abgelaufen. Bitte starte den Prozess erneut.</p></div>';
		display_initial_form();
		return;
	}

	$season_id = intval( $_POST['season_id'] );
	$new_season_name = sanitize_text_field( $_POST['new_season_name'] );

	// Create a new season if requested
	if ( $season_id === 0 && ! empty( $new_season_name ) ) {
		// Simple date range for the new season, can be improved
		$start_date = date( 'Y-01-01' );
		$end_date   = date( 'Y-12-31' );
		$wpdb->insert( TOUR_SEASONS, [
			'uuid'       => tour_generate_uuid(),
			'name'       => $new_season_name,
			'start_date' => $start_date,
			'end_date'   => $end_date,
		], [ '%s', '%s', '%s', '%s' ] );
		$season_id = $wpdb->insert_id;
	}

	if ( ! $season_id ) {
		echo '<div class="notice notice-error"><p>Keine Saison ausgewählt oder erstellt. Der Import wurde abgebrochen.</p></div>';
		display_preview_table_and_options( $events );
		return;
	}

	// Get existing categories for the season to avoid duplicates
	$existing_categories_query = $wpdb->prepare("SELECT title, id FROM " . TOUR_CATEGORIES . " WHERE season_id = %d", $season_id);
	$existing_categories_results = $wpdb->get_results($existing_categories_query, ARRAY_A);
	$category_map = array_column($existing_categories_results, 'id', 'title');

	$imported_count = 0;
	$created_categories_count = 0;
	$transports = get_transports_map();
    $types_map = [ 'auftritt' => 0, 'infos' => 1, 'gv' => 2, 'anderes' => 3 ];


	foreach ($events as $event_data) {
        $category_id = null;
        $category_name = trim($event_data['category_name'] ?? '');

        if (!empty($category_name)) {
            if (isset($category_map[$category_name])) {
                $category_id = $category_map[$category_name];
            } else {
                // Category does not exist, create it
                $wpdb->insert(TOUR_CATEGORIES, [
                    'uuid'       => tour_generate_uuid(),
                    'title'      => $category_name,
                    'date_start' => $event_data['category_date_start'],
                    'date_end'   => $event_data['category_date_end'],
                    'season_id'  => $season_id,
                    'sort'       => $event_data['category_sort'] ?? 0,
                ], ['%s', '%s', '%s', '%s', '%d', '%d']);
                $category_id = $wpdb->insert_id;
                $category_map[$category_name] = $category_id; // Cache it
                $created_categories_count++;
            }
        }

        if (empty($category_id)) {
            continue;
        }

        $transport_id = null;
        if (!empty($event_data['transport_name'])) {
            $transport_name_lower = strtolower(trim($event_data['transport_name']));
            if (isset($transports[$transport_name_lower])) {
                $transport_id = $transports[$transport_name_lower];
            }
        }

        // Prepare data for insertion
        $insert_data = [
            'uuid'         => tour_generate_uuid(),
            'name'         => $event_data['name'],
            'category_id'  => $category_id,
            'transport_id' => $transport_id,
            'date'         => !empty($event_data['date'] ?? null) ? date('Y-m-d', strtotime(str_replace('.', '-', $event_data['date']))) : null,
            'day'          => $event_data['day'],
            'sort'         => $event_data['sort'],
            'type'         => $types_map[strtolower($event_data['type'] ?? '')] ?? 0,
            'organizer'    => $event_data['organizer'] ?? null,
            'location'     => $event_data['location'] ?? null,
            'maps_url'     => !empty($event_data['location'] ?? null) ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($event_data['location']) : null,
            'play'         => !empty($event_data['play'] ?? null) ? date('H:i:s', strtotime($event_data['play'])) : null,
            'gathering'    => !empty($event_data['gathering'] ?? null) ? date('H:i:s', strtotime($event_data['gathering'])) : null,
            'makeup'       => !empty($event_data['makeup'] ?? null) ? date('H:i:s', strtotime($event_data['makeup'])) : null,
            'warehouse'    => !empty($event_data['warehouse'] ?? null) ? date('H:i:s', strtotime($event_data['warehouse'])) : null,
            'sun'          => !empty($event_data['sun'] ?? null) ? date('H:i:s', strtotime($event_data['sun'])) : null,
            'trailer'      => $event_data['trailer'] ?? null,
            'fix'          => strtolower($event_data['fix'] ?? '') === 'x' ? 1 : 0,
            'public'       => strtolower($event_data['public'] ?? '') === 'x' ? 1 : 0,
            'info'         => $event_data['info'] ?? null,
        ];

        // Filter out null values to let DB defaults apply
        $insert_data = array_filter($insert_data, function($value) {
            return $value !== null && $value !== '';
        });

		if ($wpdb->insert(TOUR_EVENTS, $insert_data)) {
			$imported_count++;
		}
	}

	// Clean up the transient
	delete_transient( 'tour_import_data' );

	echo "<div class='notice notice-success'><p>Erfolgreich {$imported_count} von " . count($events) . " Auftritten importiert. Dabei wurden {$created_categories_count} neue Kategorien erstellt.</p></div>";
	echo '<a href="' . admin_url( 'admin.php?page=tour_events' ) . '" class="button button-primary">Zur Auftrittsliste</a>';
}

// Helper to parse CSV data and map to database fields
function parse_sheet_data( $csv_data ) {
	$rows = explode( "\n", trim( $csv_data ) );
	$header_row = str_getcsv( array_shift( $rows ) );

	$header_map = [
		'Was' => 'name', 'Datum' => 'date', 'Art' => 'type', 'Ort' => 'location',
		'Veranstalter' => 'organizer', 'Zeit' => 'play', 'Besammlung' => 'gathering', 'Schminken' => 'makeup',
		'Abfahrt Magazin' => 'warehouse', 'Sonne' => 'sun', 'Trailer' => 'trailer', 'Transport' => 'transport_name',
		'Fix' => 'fix', 'Public' => 'public', 'Info' => 'info', 'Tagesinfo' => 'category_name',
	];
	$events = [];
	$col_indexes = [];

	foreach ( $header_map as $sheet_header => $db_col ) {
		$index = array_search( $sheet_header, $header_row );
		if ( $index !== false ) {
			$col_indexes[ $db_col ] = $index;
		}
	}

    if ( !isset($col_indexes['name']) ) {
        return []; // 'Anlass' column is mandatory
    }

	foreach ( $rows as $row ) {
		if ( empty( trim( $row ) ) ) continue;
		$row_data = str_getcsv( $row );
		if ( empty( trim( $row_data[ $col_indexes['name'] ] ?? '' ) ) ) continue;

		$event = [];
		foreach ( $col_indexes as $db_col => $index ) {
			$event[ $db_col ] = trim( $row_data[ $index ] ?? '' );
		}

        // Derive 'day' from 'date' if not explicitly provided or empty
        if ( empty($event['day']) && !empty($event['date']) ) {
            $datetime = DateTime::createFromFormat('d.m.Y', $event['date']);
            if ($datetime) {
                $php_day_num = (int)$datetime->format('w'); // 0 (Sunday) through 6 (Saturday)
                $our_system_day_num = ($php_day_num === 0) ? 6 : $php_day_num - 1; // 0 (Monday) through 6 (Sunday)
                $event['day'] = $our_system_day_num;
            } else {
                $event['day'] = 0; // Default to Monday if date parsing fails
            }
        } elseif (!isset($event['day'])) { // If 'day' was never set, set default to Monday
            $event['day'] = 0;
        }

		$events[] = $event;
	}
	return $events;
}

// Helper to display the preview table and season options
function display_preview_table_and_options( $events ) {
	global $wpdb;
	$seasons = $wpdb->get_results( "SELECT id, name FROM " . TOUR_SEASONS . " ORDER BY start_date DESC" );
	?>
    <h2>Schritt 2: Vorschau und Saison auswählen</h2>
    <p>Überprüfe die importierten Daten. Leere Felder werden mit den Standardwerten der Datenbank gefüllt.</p>

    <div style="max-height: 400px; overflow-y: auto; margin-bottom: 20px; border: 1px solid #c3c4c7;">
        <table class="wp-list-table widefat fixed striped">
            <thead>
            <tr>
                <th>Sort</th><th>Name</th><th>Kategorie</th><th>Datum</th><th>Ort</th><th>Typ</th><th>Transport</th><th>Fix</th><th>Öffentlich</th>
            </tr>
            </thead>
            <tbody>
			<?php foreach ( $events as $event ): ?>
                <tr>
                    <td><?php echo esc_html( $event['sort'] ?? '' ); ?></td>
                    <td><?php echo esc_html( $event['name'] ?? '' ); ?></td>
                    <td><?php echo esc_html( $event['category_name'] ?? '' ); ?></td>
                    <td><?php echo esc_html( $event['date'] ?? '' ); ?></td>
                    <td><?php echo esc_html( $event['location'] ?? '' ); ?></td>
                    <td><?php echo esc_html( $event['type'] ?? '' ); ?></td>
                    <td><?php echo esc_html( $event['transport_name'] ?? '' ); ?></td>
                    <td><?php echo (isset($event['fix']) && strtolower($event['fix']) === 'x') ? 'Ja' : 'Nein'; ?></td>
                    <td><?php echo (isset($event['public']) && strtolower($event['public']) === 'x') ? 'Ja' : 'Nein'; ?></td>
                </tr>
			<?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="postbox">
        <div class="postbox-header"><h2>Schritt 3: Saison Zuweisen und Importieren</h2></div>
        <div class="inside">
            <form method="post" action="<?php echo admin_url( 'admin.php?page=tour_importer' ); ?>">
				<?php wp_nonce_field( 'tour_import_run', 'tour_importer_nonce' ); ?>
                <input type="hidden" name="action" value="import">
                <table class="form-table">
                    <tr>
                        <th><label for="season_id">Saison auswählen</label></th>
                        <td>
                            <select name="season_id" id="season_id" onchange="document.getElementById('new_season_wrapper').style.display = this.value == '0' ? 'block' : 'none';">
                                <option value="">--- Bestehende Saison ---</option>
								<?php foreach ( $seasons as $season ): ?>
                                    <option value="<?php echo esc_attr( $season->id ); ?>"><?php echo esc_html( $season->name ); ?></option>
								<?php endforeach; ?>
                                <option value="0">--- Neue Saison erstellen ---</option>
                            </select>
                        </td>
                    </tr>
                    <tr id="new_season_wrapper" style="display:none;">
                        <th><label for="new_season_name">Name der neuen Saison</label></th>
                        <td><input type="text" name="new_season_name" id="new_season_name" class="regular-text"></td>
                    </tr>
                </table>
				<?php submit_button( 'Importieren' ); ?>
            </form>
        </div>
    </div>
	<?php
}

// Helper to get a map of transport names to IDs
function get_transports_map() {
	global $wpdb;
	$results = $wpdb->get_results( "SELECT id, name FROM " . TOUR_TRANSPORTS, ARRAY_A );
	$map = [];
	foreach ( $results as $result ) {
		$map[ strtolower( $result['name'] ) ] = $result['id'];
	}
	return $map;
}