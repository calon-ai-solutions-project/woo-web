<?php
/**
 * Brand tokens applied as Kadence defaults.
 *
 * Colours and fonts come from brand/tokens.json in the repo. They are set as
 * Kadence's default palette and default theme options, so a fresh install picks
 * them up with nothing saved in the database, and the Customizer still works.
 *
 * @package clo-child
 */

defined( 'ABSPATH' ) || exit;

const CLO_NAVY   = '#13233F';
const CLO_YELLOW = '#FFD21F';
const CLO_CHALK  = '#F7F5EE';
const CLO_WHITE  = '#FFFFFF';

const CLO_FONT_BODY    = '"Barlow Condensed", "Arial Narrow", Arial, sans-serif';
const CLO_FONT_DISPLAY = 'Anton, Impact, "Arial Narrow Bold", sans-serif';

/**
 * Kadence palette slots mapped to the brand.
 *
 * Slots 5 to 7 are tints of navy and chalk for secondary text and borders. All
 * text colours pass 4.5:1 on both chalk and white.
 *
 * @return array<string,string>
 */
function clo_palette() {
	return array(
		'palette1'  => CLO_NAVY,   // Accent: links.
		'palette2'  => CLO_YELLOW, // Accent alt: primary buttons, badges.
		'palette3'  => CLO_NAVY,   // Headings.
		'palette4'  => CLO_NAVY,   // Body text.
		'palette5'  => '#4A566B',  // Secondary text.
		'palette6'  => '#5C6779',  // Subtle text.
		'palette7'  => '#E7E3D6',  // Borders and subtle fills.
		'palette8'  => CLO_CHALK,  // Page background.
		'palette9'  => CLO_WHITE,  // Cards and product tiles.
		'palette10' => CLO_YELLOW, // Complement.
	);
}

add_filter(
	'kadence_global_palette_defaults',
	function ( $json ) {
		$palettes = json_decode( $json, true );
		if ( ! is_array( $palettes ) ) {
			return $json;
		}
		$brand = clo_palette();
		foreach ( array( 'palette', 'second-palette', 'third-palette' ) as $set ) {
			if ( empty( $palettes[ $set ] ) || ! is_array( $palettes[ $set ] ) ) {
				continue;
			}
			foreach ( $palettes[ $set ] as $i => $swatch ) {
				if ( isset( $swatch['slug'], $brand[ $swatch['slug'] ] ) ) {
					$palettes[ $set ][ $i ]['color'] = $brand[ $swatch['slug'] ];
				}
			}
		}

		return wp_json_encode( $palettes );
	}
);

/**
 * A Kadence typography setting for a self-hosted (non-Google) font.
 *
 * @param array<string,mixed> $base    Kadence's default for this setting.
 * @param string              $family  CSS font-family value.
 * @param string              $weight  CSS font-weight.
 * @param array<string,mixed> $more    Other keys to override.
 * @return array<string,mixed>
 */
function clo_font( $base, $family, $weight, $more = array() ) {
	return array_replace_recursive(
		(array) $base,
		array(
			'family'  => $family,
			'google'  => false,
			'weight'  => $weight,
			'variant' => $weight,
		),
		$more
	);
}

add_filter(
	'kadence_theme_options_defaults',
	function ( $defaults ) {
		$get = function ( $key ) use ( $defaults ) {
			return isset( $defaults[ $key ] ) ? $defaults[ $key ] : array();
		};

		// Typography. Anton ships in one weight (400), so headings must not ask for bold.
		$defaults['base_font']    = clo_font(
			$get( 'base_font' ),
			CLO_FONT_BODY,
			'500',
			array(
				'size'       => array( 'desktop' => 19 ),
				'lineHeight' => array( 'desktop' => 1.5 ),
				'color'      => 'palette4',
			)
		);
		$defaults['heading_font'] = array( 'family' => CLO_FONT_DISPLAY );

		$display_sizes = array(
			'h1' => 46,
			'h2' => 36,
			'h3' => 28,
		);
		foreach ( $display_sizes as $tag => $size ) {
			$defaults[ $tag . '_font' ] = clo_font(
				$get( $tag . '_font' ),
				'inherit',
				'400',
				array(
					'size'       => array( 'desktop' => $size ),
					'lineHeight' => array( 'desktop' => 1.15 ),
					'color'      => 'palette3',
				)
			);
		}
		// Smaller headings read better in the body face.
		foreach ( array( 'h4', 'h5', 'h6' ) as $tag ) {
			$defaults[ $tag . '_font' ] = clo_font(
				$get( $tag . '_font' ),
				CLO_FONT_BODY,
				'600',
				array(
					'lineHeight' => array( 'desktop' => 1.25 ),
					'color'      => 'palette3',
				)
			);
		}

		// Links: navy, underlined. Hover must not fall back to yellow on a light background.
		$defaults['link_color'] = array(
			'highlight'      => 'palette1',
			'highlight-alt'  => 'palette5',
			'highlight-alt2' => 'palette9',
			'style'          => 'standard',
		);

		// Primary button: yellow with navy text. Hover: navy with white text.
		$defaults['buttons_color']      = array(
			'color' => 'palette3',
			'hover' => 'palette9',
		);
		$defaults['buttons_background'] = array(
			'color' => 'palette2',
			'hover' => 'palette1',
		);
		$defaults['buttons_typography'] = clo_font( $get( 'buttons_typography' ), CLO_FONT_BODY, '600' );

		// Secondary button: navy with white text. Hover: yellow with navy text.
		$defaults['buttons_secondary_color']      = array(
			'color' => 'palette9',
			'hover' => 'palette3',
		);
		$defaults['buttons_secondary_background'] = array(
			'color' => 'palette1',
			'hover' => 'palette2',
		);
		$defaults['buttons_secondary_typography'] = clo_font( $get( 'buttons_secondary_typography' ), CLO_FONT_BODY, '600' );

		// Outline button: navy outline and text. Hover fills navy.
		$defaults['buttons_outline_color']         = array(
			'color' => 'palette1',
			'hover' => 'palette9',
		);
		$defaults['buttons_outline_border_colors'] = array(
			'color' => 'palette1',
			'hover' => 'palette1',
		);
		$defaults['buttons_outline_background']    = array(
			'color' => '',
			'hover' => 'palette1',
		);
		$defaults['buttons_outline_typography']    = clo_font( $get( 'buttons_outline_typography' ), CLO_FONT_BODY, '600' );

		// Product tiles: add-to-basket button always visible, lined up along the bottom.
		$defaults['product_archive_style']        = 'action-visible';
		$defaults['product_archive_button_style'] = 'button';
		$defaults['product_archive_button_align'] = true;
		// Two tiles per row on phones, so more stock is visible without scrolling.
		$defaults['product_archive_mobile_columns'] = 'twocolumn';

		return $defaults;
	}
);
