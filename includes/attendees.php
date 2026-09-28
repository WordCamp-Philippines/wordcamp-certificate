<?php
/**
 * Attendee list: table schema, CampTix CSV import, and the email+name
 * verification gate used before a certificate is issued.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Create (or upgrade) the attendees table. Safe to call repeatedly via dbDelta. */
function wcc_create_attendees_table() {
	global $wpdb;
	$table            = $wpdb->prefix . 'wordcamp_attendees';
	$charset_collate  = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE IF NOT EXISTS {$table} (
		id           BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		attendee_id  VARCHAR(64) NOT NULL DEFAULT '',
		first_name   VARCHAR(255) NOT NULL DEFAULT '',
		last_name    VARCHAR(255) NOT NULL DEFAULT '',
		email        VARCHAR(255) NOT NULL DEFAULT '',
		ticket_type  VARCHAR(100) NOT NULL DEFAULT '',
		imported_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id),
		KEY          email (email)
	) {$charset_collate};";
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}

/** Total number of attendees currently imported. */
function wcc_get_attendee_count() {
	global $wpdb;
	$table = $wpdb->prefix . 'wordcamp_attendees';
	return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
}

/** Timestamp of the most recent import, or empty string if none. */
function wcc_get_last_import_time() {
	global $wpdb;
	$table = $wpdb->prefix . 'wordcamp_attendees';
	return (string) $wpdb->get_var( "SELECT MAX(imported_at) FROM {$table}" );
}

/**
 * Fetch a page of imported attendees, optionally filtered by a search term
 * matched against first name, last name, email, and attendee ID.
 *
 * @param array $args {
 *     @type string $search   Search term. Default ''.
 *     @type int    $paged    Current page (1-indexed). Default 1.
 *     @type int    $per_page Rows per page. Default 20.
 * }
 * @return array{rows:array,total:int,per_page:int,paged:int}
 */
function wcc_get_attendees( $args = [] ) {
	global $wpdb;
	$table = $wpdb->prefix . 'wordcamp_attendees';

	$search   = isset( $args['search'] ) ? trim( (string) $args['search'] ) : '';
	$paged    = isset( $args['paged'] ) ? max( 1, (int) $args['paged'] ) : 1;
	$per_page = isset( $args['per_page'] ) ? max( 1, (int) $args['per_page'] ) : 20;
	$offset   = ( $paged - 1 ) * $per_page;

	$where = '';
	$params = [];
	if ( '' !== $search ) {
		$like   = '%' . $wpdb->esc_like( $search ) . '%';
		$where  = 'WHERE first_name LIKE %s OR last_name LIKE %s OR email LIKE %s OR attendee_id LIKE %s';
		$params = [ $like, $like, $like, $like ];
	}

	$total_sql = "SELECT COUNT(*) FROM {$table} {$where}";
	$total     = (int) ( $params ? $wpdb->get_var( $wpdb->prepare( $total_sql, $params ) ) : $wpdb->get_var( $total_sql ) );

	$rows_sql = "SELECT * FROM {$table} {$where} ORDER BY last_name ASC, first_name ASC LIMIT %d OFFSET %d";
	$rows     = $wpdb->get_results( $wpdb->prepare( $rows_sql, array_merge( $params, [ $per_page, $offset ] ) ) );

	return [
		'rows'     => $rows,
		'total'    => $total,
		'per_page' => $per_page,
		'paged'    => $paged,
	];
}

/** Normalize a name for comparison: strip accents/punctuation, collapse whitespace, lowercase. */
function wcc_normalize_name( $name ) {
	$name = remove_accents( (string) $name );
	$name = strtolower( $name );
	$name = preg_replace( '/[^a-z0-9]+/', ' ', $name );
	$name = trim( preg_replace( '/\s+/', ' ', $name ) );
	return $name;
}

/**
 * Verify a submitted full name + email against the imported attendee list.
 *
 * Returns the matched attendee row on a match, or a WP_Error describing why
 * verification failed. Requires BOTH the email and the name to match an
 * imported record.
 */
function wcc_verify_attendee( $full_name, $email ) {
	global $wpdb;
	$table = $wpdb->prefix . 'wordcamp_attendees';

	if ( 0 === wcc_get_attendee_count() ) {
		return new WP_Error(
			'wcc_no_attendee_list',
			__( 'The attendee list has not been imported yet. Please contact the event organizer.', 'wordcamp-certificate' )
		);
	}

	$rows = $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM {$table} WHERE email = %s",
		$email
	) );

	if ( empty( $rows ) ) {
		return new WP_Error(
			'wcc_email_not_found',
			__( "We couldn't find this email in our attendee list. Please double-check the email you registered with, or contact the organizer.", 'wordcamp-certificate' )
		);
	}

	$submitted = wcc_normalize_name( $full_name );

	foreach ( $rows as $row ) {
		$forward = wcc_normalize_name( trim( $row->first_name . ' ' . $row->last_name ) );
		$reverse = wcc_normalize_name( trim( $row->last_name . ' ' . $row->first_name ) );
		if ( $submitted === $forward || $submitted === $reverse ) {
			return $row;
		}
	}

	return new WP_Error(
		'wcc_name_mismatch',
		__( "The name doesn't match our attendee record for this email. Please enter your name exactly as used during registration.", 'wordcamp-certificate' )
	);
}

/**
 * Import a CampTix attendee export CSV. Only the essential fields are kept:
 * Attendee ID, First Name, Last Name, E-mail Address, Ticket Type, Status.
 * Replaces the current attendee list with the contents of the file.
 *
 * @param string $file_path Path to the uploaded CSV (a temp upload path).
 * @return array{imported:int,skipped:int,total:int}|WP_Error
 */
function wcc_import_attendees_csv( $file_path ) {
	if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
		return new WP_Error( 'wcc_csv_unreadable', __( 'The uploaded file could not be read.', 'wordcamp-certificate' ) );
	}

	$handle = fopen( $file_path, 'r' );
	if ( ! $handle ) {
		return new WP_Error( 'wcc_csv_open_failed', __( 'The uploaded file could not be opened.', 'wordcamp-certificate' ) );
	}

	$header = fgetcsv( $handle, 0, ',', '"', '\\' );
	if ( ! $header ) {
		fclose( $handle );
		return new WP_Error( 'wcc_csv_empty', __( 'The CSV file appears to be empty.', 'wordcamp-certificate' ) );
	}

	// Build a case-insensitive column index. CampTix exports repeat some
	// header names (e.g. multiple "Where did you find us?" columns) — we
	// only look up the specific columns we need, all of which are unique.
	$columns = [];
	foreach ( $header as $index => $label ) {
		$columns[ strtolower( trim( $label ) ) ] = $index;
	}

	$required = [ 'attendee id', 'first name', 'last name', 'e-mail address' ];
	foreach ( $required as $key ) {
		if ( ! isset( $columns[ $key ] ) ) {
			fclose( $handle );
			return new WP_Error(
				'wcc_csv_missing_columns',
				sprintf(
					/* translators: %s: missing column name */
					__( 'This doesn\'t look like a CampTix attendee export — missing column "%s".', 'wordcamp-certificate' ),
					$key
				)
			);
		}
	}

	$idx_id     = $columns['attendee id'];
	$idx_first  = $columns['first name'];
	$idx_last   = $columns['last name'];
	$idx_email  = $columns['e-mail address'];
	$idx_type   = $columns['ticket type'] ?? null;
	$idx_status = $columns['status'] ?? null;

	$rows = [];
	while ( ( $data = fgetcsv( $handle, 0, ',', '"', '\\' ) ) !== false ) {
		if ( count( $data ) === 1 && trim( (string) $data[0] ) === '' ) {
			continue; // skip blank lines
		}
		$status = null !== $idx_status && isset( $data[ $idx_status ] ) ? trim( $data[ $idx_status ] ) : 'Publish';
		$email  = isset( $data[ $idx_email ] ) ? sanitize_email( trim( $data[ $idx_email ] ) ) : '';

		// Only import valid, published tickets with a usable email address.
		if ( '' === $email || ! is_email( $email ) || 0 !== strcasecmp( $status, 'Publish' ) ) {
			continue;
		}

		$rows[] = [
			'attendee_id' => isset( $data[ $idx_id ] ) ? sanitize_text_field( $data[ $idx_id ] ) : '',
			'first_name'  => isset( $data[ $idx_first ] ) ? sanitize_text_field( $data[ $idx_first ] ) : '',
			'last_name'   => isset( $data[ $idx_last ] ) ? sanitize_text_field( $data[ $idx_last ] ) : '',
			'email'       => $email,
			'ticket_type' => null !== $idx_type && isset( $data[ $idx_type ] ) ? sanitize_text_field( $data[ $idx_type ] ) : '',
		];
	}
	fclose( $handle );

	$total = count( $rows );
	if ( 0 === $total ) {
		return new WP_Error( 'wcc_csv_no_rows', __( 'No valid attendee rows were found in this file.', 'wordcamp-certificate' ) );
	}

	global $wpdb;
	$table = $wpdb->prefix . 'wordcamp_attendees';

	// A CampTix export is a full snapshot — replace the current list.
	$wpdb->query( "TRUNCATE TABLE {$table}" );

	$imported = 0;
	foreach ( $rows as $row ) {
		$row['imported_at'] = current_time( 'mysql' );
		$inserted = $wpdb->insert( $table, $row );
		if ( $inserted ) {
			$imported++;
		}
	}

	return [
		'imported' => $imported,
		'skipped'  => 0,
		'total'    => $imported,
	];
}
