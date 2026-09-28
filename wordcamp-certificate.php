<?php
/**
 * Plugin Name: WordCamp Certificate Generator
 * Plugin URI:  https://github.com/WordCamp-Philippines/wordcamp-certificate
 * Description: Generates a beautiful Certificate of Attendance for WordCamp attendees. Attendees enter their name and email to receive a downloadable certificate.
 * Version:     1.2.2
 * Author:      WordPress Philippines
 * License:     GPL-2.0-or-later
 * Text Domain: wordcamp-certificate
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WCC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WCC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WCC_VERSION', '1.2.2' );

require_once WCC_PLUGIN_DIR . 'includes/design-settings.php';
require_once WCC_PLUGIN_DIR . 'includes/attendees.php';

// Activation: create DB table
register_activation_hook( __FILE__, 'wcc_activate' );
function wcc_activate() {
	global $wpdb;
	$table = $wpdb->prefix . 'wordcamp_certificates';
	$charset_collate = $wpdb->get_charset_collate();
	$sql = "CREATE TABLE IF NOT EXISTS {$table} (
		id           BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		full_name    VARCHAR(255) NOT NULL,
		email        VARCHAR(255) NOT NULL,
		issued_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
		cert_token   VARCHAR(64) NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY   cert_token (cert_token),
		KEY          email (email)
	) {$charset_collate};";
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	wcc_create_attendees_table();
}

// Admin menu
add_action( 'admin_menu', 'wcc_admin_menu' );
function wcc_admin_menu() {
	add_menu_page(
		__( 'WordCamp Certificates', 'wordcamp-certificate' ),
		__( 'WC Certificates', 'wordcamp-certificate' ),
		'manage_options',
		'wordcamp-certificates',
		'wcc_admin_page',
		'dashicons-awards',
		30
	);
	add_submenu_page(
		'wordcamp-certificates',
		__( 'Attendees', 'wordcamp-certificate' ),
		__( 'Attendees', 'wordcamp-certificate' ),
		'manage_options',
		'wordcamp-certificate-attendees',
		'wcc_attendees_page'
	);
	add_submenu_page(
		'wordcamp-certificates',
		__( 'Settings', 'wordcamp-certificate' ),
		__( 'Settings', 'wordcamp-certificate' ),
		'manage_options',
		'wordcamp-certificate-settings',
		'wcc_settings_page'
	);
}

// Enqueue admin styles
add_action( 'admin_enqueue_scripts', 'wcc_admin_scripts' );
function wcc_admin_scripts( $hook ) {
	if ( strpos( $hook, 'wordcamp-certificate' ) === false ) return;
	wp_enqueue_style( 'wcc-admin', WCC_PLUGIN_URL . 'assets/admin.css', [], WCC_VERSION );
	if ( strpos( $hook, 'wordcamp-certificate-settings' ) !== false ) {
		wp_enqueue_media();
	}
}

// Enqueue frontend scripts/styles
add_action( 'wp_enqueue_scripts', 'wcc_frontend_scripts' );
function wcc_frontend_scripts() {
	wp_enqueue_style( 'wcc-frontend', WCC_PLUGIN_URL . 'assets/frontend.css', [], WCC_VERSION );
	wp_enqueue_script( 'wcc-frontend', WCC_PLUGIN_URL . 'assets/frontend.js', [ 'jquery' ], WCC_VERSION, true );
	wp_localize_script( 'wcc-frontend', 'wccData', [
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'wcc_generate_nonce' ),
	] );
}

// Shortcode
add_shortcode( 'wordcamp_certificate', 'wcc_shortcode' );
function wcc_shortcode( $atts ) {
	$atts = shortcode_atts( [
		'event_name' => get_option( 'wcc_event_name', 'WordCamp' ),
		'event_date' => get_option( 'wcc_event_date', date( 'F j, Y' ) ),
	], $atts );
	ob_start();
	include WCC_PLUGIN_DIR . 'templates/form.php';
	return ob_get_clean();
}

// AJAX: generate certificate
add_action( 'wp_ajax_wcc_generate', 'wcc_ajax_generate' );
add_action( 'wp_ajax_nopriv_wcc_generate', 'wcc_ajax_generate' );
function wcc_ajax_generate() {
	check_ajax_referer( 'wcc_generate_nonce', 'nonce' );

	$full_name = sanitize_text_field( wp_unslash( $_POST['full_name'] ?? '' ) );
	$email     = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );

	if ( empty( $full_name ) || empty( $email ) ) {
		wp_send_json_error( [ 'message' => __( 'Please provide your full name and email address.', 'wordcamp-certificate' ) ] );
	}

	if ( ! is_email( $email ) ) {
		wp_send_json_error( [ 'message' => __( 'Please enter a valid email address.', 'wordcamp-certificate' ) ] );
	}

	$verified = wcc_verify_attendee( $full_name, $email );
	if ( is_wp_error( $verified ) ) {
		wp_send_json_error( [ 'message' => $verified->get_error_message() ] );
	}

	// Use the official full name from the imported attendee list, not whatever
	// was typed into the form (which may be a nickname/display name variant).
	$canonical_name = trim( $verified->first_name . ' ' . $verified->last_name );

	global $wpdb;
	$table = $wpdb->prefix . 'wordcamp_certificates';

	// Check if already issued
	$existing = $wpdb->get_row( $wpdb->prepare(
		"SELECT cert_token FROM {$table} WHERE email = %s",
		$email
	) );

	if ( $existing ) {
		$token = $existing->cert_token;
	} else {
		$token = wp_generate_password( 32, false );
		$wpdb->insert( $table, [
			'full_name'  => $canonical_name,
			'email'      => $email,
			'issued_at'  => current_time( 'mysql' ),
			'cert_token' => $token,
		] );
	}

	$cert_url = add_query_arg( [
		'wcc_cert'  => '1',
		'token'     => $token,
	], home_url( '/' ) );

	wp_send_json_success( [ 'cert_url' => $cert_url, 'name' => $canonical_name ] );
}

// Certificate render endpoint
add_action( 'template_redirect', 'wcc_render_certificate' );
function wcc_render_certificate() {
	if ( ! isset( $_GET['wcc_cert'] ) || ! isset( $_GET['token'] ) ) return;

	$token = sanitize_text_field( wp_unslash( $_GET['token'] ) );
	if ( empty( $token ) ) {
		wp_die( esc_html__( 'Invalid certificate token.', 'wordcamp-certificate' ) );
	}

	global $wpdb;
	$table = $wpdb->prefix . 'wordcamp_certificates';
	$cert  = $wpdb->get_row( $wpdb->prepare(
		"SELECT * FROM {$table} WHERE cert_token = %s",
		$token
	) );

	if ( ! $cert ) {
		wp_die( esc_html__( 'Certificate not found.', 'wordcamp-certificate' ) );
	}

	$event = [
		'event_name'     => get_option( 'wcc_event_name', 'WordCamp' ),
		'event_date'     => get_option( 'wcc_event_date', date( 'F j, Y' ) ),
		'event_location' => get_option( 'wcc_event_location', '' ),
		'organizer_name' => get_option( 'wcc_organizer_name', get_bloginfo( 'name' ) ),
	];
	$logo_id  = (int) get_option( 'wcc_event_logo_id', 0 );
	$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'medium' ) : '';
	$design   = wcc_get_design_settings();
	$fonts    = wcc_get_font_choices();
	$body_html = wcc_render_certificate_body( $cert, $event );
	extract( $event ); // event_name, event_date, event_location, organizer_name for the template.

	include WCC_PLUGIN_DIR . 'templates/certificate.php';
	exit;
}

// Admin page: list all issued certificates
function wcc_admin_page() {
	global $wpdb;
	$table = $wpdb->prefix . 'wordcamp_certificates';
	$certs = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY issued_at DESC" );
	include WCC_PLUGIN_DIR . 'templates/admin.php';
}

// Attendees page: import CampTix export, view current list status
function wcc_attendees_page() {
	$import_result = null;
	if ( isset( $_POST['wcc_import_attendees'] ) && check_admin_referer( 'wcc_import_attendees' ) ) {
		if ( ! empty( $_FILES['wcc_attendee_csv']['tmp_name'] ) && UPLOAD_ERR_OK === $_FILES['wcc_attendee_csv']['error'] ) {
			$import_result = wcc_import_attendees_csv( $_FILES['wcc_attendee_csv']['tmp_name'] );
		} else {
			$import_result = new WP_Error( 'wcc_no_file', __( 'Please choose a CSV file to upload.', 'wordcamp-certificate' ) );
		}
	}

	$attendee_count = wcc_get_attendee_count();
	$last_import    = wcc_get_last_import_time();

	$search  = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
	$paged   = isset( $_GET['paged'] ) ? max( 1, (int) $_GET['paged'] ) : 1;
	$listing = wcc_get_attendees( [ 'search' => $search, 'paged' => $paged, 'per_page' => 20 ] );

	include WCC_PLUGIN_DIR . 'templates/attendees.php';
}

// Settings page
function wcc_settings_page() {
	if ( isset( $_POST['wcc_save_settings'] ) && check_admin_referer( 'wcc_settings' ) ) {
		update_option( 'wcc_event_name',     sanitize_text_field( $_POST['wcc_event_name'] ) );
		update_option( 'wcc_event_date',     sanitize_text_field( $_POST['wcc_event_date'] ) );
		update_option( 'wcc_event_location', sanitize_text_field( $_POST['wcc_event_location'] ) );
		update_option( 'wcc_organizer_name', sanitize_text_field( $_POST['wcc_organizer_name'] ) );
		update_option( 'wcc_event_logo_id',  absint( $_POST['wcc_event_logo_id'] ?? 0 ) );
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved.', 'wordcamp-certificate' ) . '</p></div>';
	}
	include WCC_PLUGIN_DIR . 'templates/settings.php';
}

// Register a dedicated block category so certificate blocks group together in the inserter.
add_filter( 'block_categories_all', 'wcc_block_categories' );
function wcc_block_categories( $categories ) {
	return array_merge( [ [ 'slug' => 'wordcamp-certificate', 'title' => __( 'WordCamp Certificate', 'wordcamp-certificate' ) ] ], $categories );
}

// Register blocks
add_action( 'init', 'wcc_register_block' );
function wcc_register_block() {
	if ( ! function_exists( 'register_block_type' ) ) return;

	register_block_type( 'wordcamp-certificate/form', [
		'editor_script'   => 'wcc-block-editor',
		'render_callback' => 'wcc_block_render',
		'attributes'      => [
			'eventName' => [ 'type' => 'string', 'default' => '' ],
			'eventDate' => [ 'type' => 'string', 'default' => '' ],
		],
	] );
	wp_register_script(
		'wcc-block-editor',
		WCC_PLUGIN_URL . 'assets/block.js',
		[ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components' ],
		WCC_VERSION,
		true
	);

	// Certificate Design block: full visual editor for the certificate look & feel.
	wp_register_script(
		'wcc-design-block-editor',
		WCC_PLUGIN_URL . 'assets/design-block.js',
		[ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render' ],
		WCC_VERSION,
		true
	);
	wp_register_style(
		'wcc-design-block-editor',
		WCC_PLUGIN_URL . 'assets/design-block-editor.css',
		[],
		WCC_VERSION
	);
	wp_register_style(
		'wcc-cert-canvas',
		WCC_PLUGIN_URL . 'assets/cert-canvas.css',
		[],
		WCC_VERSION
	);
	register_block_type( 'wordcamp-certificate/design', [
		'editor_script' => 'wcc-design-block-editor',
		'editor_style'  => 'wcc-design-block-editor',
		'style'         => 'wcc-cert-canvas',
		'attributes'    => wcc_design_block_attributes(),
	] );

	// Certificate Field block: a movable token for dynamic per-attendee/event data.
	wp_register_script(
		'wcc-field-block',
		WCC_PLUGIN_URL . 'assets/field-block.js',
		[ 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ],
		WCC_VERSION,
		true
	);
	register_block_type( 'wordcamp-certificate/field', [
		'editor_script' => 'wcc-field-block',
		'style'         => 'wcc-cert-canvas',
		'attributes'    => [
			'field'         => [ 'type' => 'string', 'default' => 'full_name' ],
			'fontFamily'    => [ 'type' => 'string', 'default' => '' ],
			'fontSize'      => [ 'type' => 'number', 'default' => 0 ],
			'fontWeight'    => [ 'type' => 'string', 'default' => '' ],
			'fontStyle'     => [ 'type' => 'string', 'default' => '' ],
			'textTransform' => [ 'type' => 'string', 'default' => 'none' ],
			'letterSpacing' => [ 'type' => 'number', 'default' => 0 ],
			'color'         => [ 'type' => 'string', 'default' => '' ],
		],
	] );
}

/** Attribute schema for the design block, shared by PHP registration and used as a reference by design-block.js. */
function wcc_design_block_attributes() {
	$defaults = wcc_get_design_defaults();
	return [
		'primaryColor' => [ 'type' => 'string', 'default' => $defaults['primary_color'] ],
		'darkColor'    => [ 'type' => 'string', 'default' => $defaults['dark_color'] ],
		'goldColor'    => [ 'type' => 'string', 'default' => $defaults['gold_color'] ],
		'lightColor'   => [ 'type' => 'string', 'default' => $defaults['light_color'] ],
		'borderColor'  => [ 'type' => 'string', 'default' => $defaults['border_color'] ],
		'headingFont'  => [ 'type' => 'string', 'default' => $defaults['heading_font'] ],
		'bodyFont'     => [ 'type' => 'string', 'default' => $defaults['body_font'] ],
		'showLogo'     => [ 'type' => 'boolean', 'default' => $defaults['show_logo'] ],
	];
}

function wcc_block_render( $attributes ) {
	$shortcode_atts = '';
	if ( ! empty( $attributes['eventName'] ) ) {
		$shortcode_atts .= ' event_name="' . esc_attr( $attributes['eventName'] ) . '"';
	}
	if ( ! empty( $attributes['eventDate'] ) ) {
		$shortcode_atts .= ' event_date="' . esc_attr( $attributes['eventDate'] ) . '"';
	}
	return do_shortcode( '[wordcamp_certificate' . $shortcode_atts . ']' );
}
