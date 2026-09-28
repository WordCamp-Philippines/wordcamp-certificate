<?php
/**
 * Certificate design settings: colors/fonts defaults, font choices, the
 * save_post sync that turns the "Certificate Design" block (a real
 * drag-and-drop Gutenberg canvas) into the live site-wide design, and the
 * per-attendee field substitution used when a certificate is issued.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Default color/font/logo design values. Content itself lives in the block's InnerBlocks, not here. */
function wcc_get_design_defaults() {
	return [
		'primary_color' => '#21759b',
		'dark_color'    => '#0a2a40',
		'gold_color'    => '#c9a84c',
		'light_color'   => '#f0f7fb',
		'border_color'  => '#d4e8f3',
		'heading_font'  => 'playfair',
		'body_font'     => 'inter',
		'show_logo'     => true,
	];
}

/**
 * Font choices available in the design block. `google` holds the family
 * segment used in the combined Google Fonts URL; system fonts leave it empty.
 */
function wcc_get_font_choices() {
	return [
		'playfair'    => [ 'label' => 'Playfair Display', 'family' => "'Playfair Display', serif", 'google' => 'Playfair+Display:wght@400;600;700' ],
		'merriweather'=> [ 'label' => 'Merriweather', 'family' => "'Merriweather', serif", 'google' => 'Merriweather:wght@400;700' ],
		'georgia'     => [ 'label' => 'Georgia (system)', 'family' => 'Georgia, serif', 'google' => '' ],
		'inter'       => [ 'label' => 'Inter', 'family' => "'Inter', sans-serif", 'google' => 'Inter:wght@300;400;500;600' ],
		'roboto'      => [ 'label' => 'Roboto', 'family' => "'Roboto', sans-serif", 'google' => 'Roboto:wght@300;400;500' ],
		'arial'       => [ 'label' => 'Arial (system)', 'family' => 'Arial, sans-serif', 'google' => '' ],
	];
}

/** Build the combined Google Fonts stylesheet URL for all supported fonts. */
function wcc_get_google_fonts_url() {
	$families = [];
	foreach ( wcc_get_font_choices() as $font ) {
		if ( ! empty( $font['google'] ) ) {
			$families[] = 'family=' . $font['google'];
		}
	}
	return 'https://fonts.googleapis.com/css2?' . implode( '&', $families ) . '&display=swap';
}

/** Get the saved design (colors/fonts/logo) merged over the defaults. */
function wcc_get_design_settings() {
	$saved = get_option( 'wcc_certificate_design', [] );
	if ( ! is_array( $saved ) ) {
		$saved = [];
	}
	return wp_parse_args( $saved, wcc_get_design_defaults() );
}

/**
 * The default certificate body, used the first time the "Certificate Design"
 * page is opened (before the user has customized/saved anything) and as the
 * fallback if no design has ever been saved. Mirrors what the block's own
 * InnerBlocks template inserts, kept in sync manually.
 */
function wcc_get_default_body_html() {
	return '<span class="wcc-cert-field cert-event-name" data-wcc-field="event_name">WordCamp</span>'
		. '<hr class="wp-block-separator cert-divider-block"/>'
		. '<p class="cert-subtitle">This is to certify that</p>'
		. '<h1 class="cert-title">Certificate of Attendance</h1>'
		. '<p class="cert-presented">proudly presented to</p>'
		. '<span class="wcc-cert-field cert-name" data-wcc-field="full_name">Jane Doe</span>'
		. '<p class="cert-desc">has successfully attended and participated in {event_name}, a community-driven WordPress conference dedicated to sharing knowledge, fostering collaboration, and celebrating the open web.</p>'
		. '<div class="cert-details">'
		. '<span class="wcc-cert-field cert-detail-item" data-wcc-field="event_date">📅 March 12, 2026</span>'
		. '<span class="wcc-cert-field cert-detail-item" data-wcc-field="event_location">📍 Manila, Philippines</span>'
		. '</div>'
		. '<hr class="wp-block-separator cert-divider-block"/>'
		. '<div class="cert-footer">'
		. '<div class="cert-sig"><p class="cert-sig-label">Organizer</p><span class="wcc-cert-field cert-sig-name" data-wcc-field="organizer_name">Event Organizer</span></div>'
		. '<div class="cert-seal"><span class="wcc-cert-field cert-id" data-wcc-field="cert_id">ABC12345</span></div>'
		. '<div class="cert-sig"><p class="cert-sig-label">Date Issued</p><span class="wcc-cert-field cert-sig-name" data-wcc-field="date_issued">March 12, 2026</span></div>'
		. '</div>';
}

/** Get the saved certificate body HTML (from the design block's InnerBlocks), or the default. */
function wcc_get_body_html() {
	$html = get_option( 'wcc_certificate_body_html', '' );
	return $html ? $html : wcc_get_default_body_html();
}

/**
 * Render the certificate body for one attendee: takes the saved design's
 * static HTML and substitutes each "Certificate Field" token's placeholder
 * content with the real value for this certificate/event.
 *
 * @param object $cert           Row from the certificates table (full_name, cert_token, issued_at).
 * @param array  $event          [event_name, event_date, event_location, organizer_name].
 * @return string
 */
function wcc_render_certificate_body( $cert, $event ) {
	$html = wcc_get_body_html();

	$values = [
		'full_name'      => $cert->full_name,
		'event_name'     => $event['event_name'],
		'event_date'     => $event['event_date'],
		'event_location' => $event['event_location'],
		'organizer_name' => $event['organizer_name'],
		'cert_id'        => strtoupper( substr( $cert->cert_token, 0, 8 ) ),
		'date_issued'    => date_i18n( 'F j, Y', strtotime( $cert->issued_at ) ),
	];

	// Substitute each field token's content: <span ... data-wcc-field="key" ...>SAMPLE</span> → real value.
	// Icons (📅/📍) that precede the sample text in event_date/event_location are preserved.
	foreach ( $values as $key => $value ) {
		$html = preg_replace_callback(
			'/(<[^>]*data-wcc-field="' . preg_quote( $key, '/' ) . '"[^>]*>)(.*?)(<\/[a-z]+>)/s',
			function ( $m ) use ( $value ) {
				$prefix = '';
				if ( preg_match( '/^([^\w<]{0,4})/u', $m[2], $icon_match ) && trim( $icon_match[1] ) !== '' ) {
					$prefix = $icon_match[1];
				}
				return $m[1] . $prefix . esc_html( $value ) . $m[3];
			},
			$html
		);
	}

	// Backward-compatible plain-text placeholder support inside typed paragraphs.
	$html = str_replace( '{event_name}', esc_html( $event['event_name'] ), $html );

	return $html;
}

/**
 * Recursively search parsed blocks for the first instance of a block name.
 */
function wcc_find_block( $blocks, $block_name ) {
	foreach ( $blocks as $block ) {
		if ( isset( $block['blockName'] ) && $block_name === $block['blockName'] ) {
			return $block;
		}
		if ( ! empty( $block['innerBlocks'] ) ) {
			$found = wcc_find_block( $block['innerBlocks'], $block_name );
			if ( $found ) {
				return $found;
			}
		}
	}
	return null;
}

/**
 * On every post/page save, look for the "Certificate Design" block and, if
 * present, sync its colors/fonts into wcc_certificate_design and its
 * rendered InnerBlocks markup into wcc_certificate_body_html, so the live
 * certificate output picks up both the design and the drag-and-drop content.
 */
add_action( 'save_post', 'wcc_sync_design_from_post', 20, 2 );
function wcc_sync_design_from_post( $post_id, $post ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	if ( ! in_array( $post->post_status, [ 'publish', 'draft', 'pending', 'private' ], true ) ) {
		return;
	}
	if ( ! has_block( 'wordcamp-certificate/design', $post ) ) {
		return;
	}

	$blocks = parse_blocks( $post->post_content );
	$block  = wcc_find_block( $blocks, 'wordcamp-certificate/design' );
	if ( ! $block ) {
		return;
	}

	// parse_blocks() only returns attributes explicitly present in the block
	// comment; merge in the registered defaults so untouched attributes aren't lost.
	$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'wordcamp-certificate/design' );
	$registered = [];
	if ( $block_type ) {
		foreach ( (array) $block_type->attributes as $attr_key => $attr_schema ) {
			if ( is_array( $attr_schema ) && array_key_exists( 'default', $attr_schema ) ) {
				$registered[ $attr_key ] = $attr_schema['default'];
			}
		}
	}
	$a      = wp_parse_args( $block['attrs'] ?? [], $registered );
	$fonts  = array_keys( wcc_get_font_choices() );
	$design = [
		'primary_color' => sanitize_hex_color( $a['primaryColor'] ?? '' ) ?: wcc_get_design_defaults()['primary_color'],
		'dark_color'    => sanitize_hex_color( $a['darkColor'] ?? '' ) ?: wcc_get_design_defaults()['dark_color'],
		'gold_color'    => sanitize_hex_color( $a['goldColor'] ?? '' ) ?: wcc_get_design_defaults()['gold_color'],
		'light_color'   => sanitize_hex_color( $a['lightColor'] ?? '' ) ?: wcc_get_design_defaults()['light_color'],
		'border_color'  => sanitize_hex_color( $a['borderColor'] ?? '' ) ?: wcc_get_design_defaults()['border_color'],
		'heading_font'  => in_array( $a['headingFont'] ?? '', $fonts, true ) ? $a['headingFont'] : 'playfair',
		'body_font'     => in_array( $a['bodyFont'] ?? '', $fonts, true ) ? $a['bodyFont'] : 'inter',
		'show_logo'     => ! empty( $a['showLogo'] ),
	];
	update_option( 'wcc_certificate_design', $design );

	// Render just the design block's InnerBlocks (the drag-and-drop content,
	// not the block's own outer cert-wrap/cert/cert-inner chrome, which the
	// certificate template supplies itself) and store the result; field
	// tokens keep their sample text until substituted at certificate-issue
	// time by wcc_render_certificate_body().
	if ( ! empty( $block['innerBlocks'] ) ) {
		$body_html = '';
		foreach ( $block['innerBlocks'] as $inner_block ) {
			$body_html .= render_block( $inner_block );
		}
		update_option( 'wcc_certificate_body_html', $body_html );
	}
}
