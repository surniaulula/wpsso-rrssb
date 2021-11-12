<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2021 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbFiltersOptions' ) ) {

	class WpssoRrssbFiltersOptions {

		private $p;	// Wpsso class object.
		private $a;	// WpssoRrssb class object.

		/**
		 * Instantiated by WpssoRrssbFilters->__construct().
		 */
		public function __construct( &$plugin, &$addon ) {

			$this->p =& $plugin;
			$this->a =& $addon;

			$this->p->util->add_plugin_filters( $this, array( 
				'option_type'          => 2,
				'save_setting_options' => 3,
				'get_defaults'         => 1,
				'get_md_defaults'      => 1,
			) );
		}

		/**
		 * Return the sanitation type for a given option key.
		 */
		public function filter_option_type( $type, $base_key ) {

			if ( ! empty( $type ) ) {	// Return early if we already have a type.

				return $type;
			}

			switch ( $base_key ) {

				/**
				 * Options that cannot be blank.
				 */
				case 'buttons_force_prot':
				case ( preg_match( '/^buttons_pos_/', $base_key ) ? true : false ):

					return 'not_blank';

				/**
				 * Integer options that must be 1 or more (not zero).
				 */
				case ( preg_match( '/_button_order$/', $base_key ) ? true : false ):

					return 'pos_int';

				/**
				 * Text strings that can be blank (line breaks are removed).
				 */
				case ( preg_match( '/_(desc|title)$/', $base_key ) ? true : false ):

					return 'one_line';

				/**
				 * Text strings that can be blank.
				 */
				case 'buttons_cta':
				case ( preg_match( '/^buttons_css_/', $base_key ) ? true : false ):	// Use the tool page to reload the default CSS.

					return 'ok_blank';
			}

			return $type;
		}

		/**
		 * $network is true if saving multisite network settings.
		 */
		public function filter_save_setting_options( array $opts, $network, $upgrading ) {

			if ( $network ) {

				return $opts;	// Nothing to do.
			}

			/**
			 * Reload the defaults styles if older than WPSSO RRSSB v4.0.0 (options version 30).
			 */
			if ( ! empty( $opts[ 'plugin_wpssorrssb_opt_version' ] ) && $opts[ 'plugin_wpssorrssb_opt_version' ] < 32 ) {

				$defs = $this->p->opt->get_defaults();

				$styles = apply_filters( 'wpsso_rrssb_styles', $this->p->cf[ 'sharing' ][ 'rrssb_styles' ] );

				foreach ( $styles as $id => $name ) {

					if ( isset( $this->p->options[ 'buttons_css_' . $id ] ) && isset( $defs[ 'buttons_css_' . $id ] ) ) {

						$this->p->options[ 'buttons_css_' . $id ] = $defs[ 'buttons_css_' . $id ];
					}
				}

				$this->p->notice->upd( __( 'The default responsive styles CSS has been reloaded and saved.', 'wpsso-rrssb' ) );
			}

			/**
			 * Update the combined and minified social stylesheet.
			 */
			WpssoRrssbSocial::update_sharing_css( $opts );

			return $opts;
		}

		public function filter_get_defaults( $defs ) {

			$this->p->util->add_post_type_names( $defs, array(
				'buttons_add_to' => 1,
			) );

			$rel_url_path = parse_url( WPSSORRSSB_URLPATH, PHP_URL_PATH );	// Returns a relative URL.

			$styles = apply_filters( 'wpsso_rrssb_styles', $this->p->cf[ 'sharing' ][ 'rrssb_styles' ] );

			foreach ( $styles as $id => $name ) {

				$buttons_css_file = WPSSORRSSB_PLUGINDIR . 'css/' . $id . '.css';

				/**
				 * CSS files are only loaded once (when variable is empty) into defaults to minimize disk I/O.
				 */
				if ( empty( $defs[ 'buttons_css_' . $id ] ) ) {

					if ( ! file_exists( $buttons_css_file ) ) {

						continue;

					} elseif ( ! $fh = @fopen( $buttons_css_file, 'rb' ) ) {

						if ( $this->p->debug->enabled ) {

							$this->p->debug->log( 'failed to open the css file ' . $buttons_css_file . ' for reading' );
						}

						if ( is_admin() ) {

							$this->p->notice->err( sprintf( __( 'Failed to open the css file %s for reading.',
								'wpsso-rrssb' ), $buttons_css_file ) );
						}

					} else {

						if ( $this->p->debug->enabled ) {

							$this->p->debug->log( 'reading css file ' . $buttons_css_file );
						}

						$buttons_css_data = fread( $fh, filesize( $buttons_css_file ) );

						fclose( $fh );

						foreach ( array( 'plugin_url_path' => $rel_url_path ) as $macro => $value ) {

							$buttons_css_data = preg_replace( '/%%' . $macro . '%%/', $value, $buttons_css_data );
						}

						$defs[ 'buttons_css_' . $id ] = $buttons_css_data;
					}
				}
			}

			return $defs;
		}

		public function filter_get_md_defaults( $md_defs ) {

			return array_merge( $md_defs, array(
				'buttons_disabled'     => 0,	// Disable Share Buttons.
				'buttons_utm_campaign' => '',	// UTM Campaign.
				'email_title'          => '',	// Email Subject.
				'email_desc'           => '',	// Email Message.
				'twitter_desc'         => '',	// Tweet Text.
				'pin_desc'             => '',	// Pinterest Caption.
				'linkedin_title'       => '',	// LinkedIn Title.
				'linkedin_desc'        => '',	// LinkedIn Caption.
				'reddit_title'         => '',	// Reddit Title.
				'reddit_desc'          => '',	// Reddit Caption.
				'tumblr_title'         => '',	// Tumblr Title.
				'tumblr_desc'          => '',	// Tumblr Caption.
			) );
		}
	}
}
