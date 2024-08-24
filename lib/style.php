<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbStyle' ) ) {

	class WpssoRrssbStyle {

		private $p;	// Wpsso class object.
		private $a;	// WpssoRrssb class object.

		private $doing_dev = false;
		private $file_ext  = 'min.css';
		private $version   = '';

		/*
		 * Instantiated by WpssoRrssb->init_objects().
		 */
		public function __construct( &$plugin, &$addon ) {

			$this->p =& $plugin;
			$this->a =& $addon;

			$this->doing_dev = SucomUtilWP::doing_dev();
			$this->file_ext  = $this->doing_dev ? 'css' : 'min.css';
			$this->version   = WpssoRrssbConfig::get_version() . ( $this->doing_dev ? gmdate( '-ymd-His' ) : '' );

			if ( is_admin() ) {

				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );

			} else add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		}

		public function admin_enqueue_styles() {

			$settings_table_css = '
				table.sucom-settings td.rrssb_show_on {
					min-width:80px;
					padding-right:4px;
					white-space:nowrap;
				}
				table.sucom-settings td.rrssb_show_on input[type="checkbox"] {
					margin:0 6px 0 0;
					vertical-align:text-bottom;
				}
			';

			 wp_add_inline_style( 'sucom-settings-table', $settings_table_css );
		}

		public function enqueue_styles() {

			if ( SucomUtilWP::is_amp() ) return;

			wp_register_style( 'rrssb',
				WPSSORRSSB_URLPATH . 'css/ext/rrssb.' . $this->file_ext,
					array(), $this->version );

			wp_enqueue_style( 'rrssb' );

			if ( ! empty( $this->p->options[ 'buttons_use_social_style' ] ) ) {

				$sharing_css_path = WpssoRrssbSocial::get_sharing_css_path();
				$sharing_css_url  = WpssoRrssbSocial::get_sharing_css_url();

				if ( ! file_exists( $sharing_css_path ) ) {

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( 'updating ' . $sharing_css_path );
					}

					WpssoRrssbSocial::update_sharing_css( $this->p->options );
				}

				if ( ! empty( $this->p->options[ 'buttons_enqueue_social_style' ] ) ) {

					$sharing_css_mtime = filemtime( $sharing_css_path );

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( 'wp_enqueue_style = wpsso_rrssb_sharing_css' );
					}

					wp_enqueue_style( 'wpsso_rrssb_sharing_css', $sharing_css_url, false, $sharing_css_mtime );

				} else {

					if ( ! is_readable( $sharing_css_path ) ) {

						if ( $this->p->debug->enabled ) {

							$this->p->debug->log( $sharing_css_path . ' is not readable' );
						}

						if ( is_admin() ) {

							$this->p->notice->err( sprintf( __( 'The %s file is not readable.', 'wpsso-rrssb' ), $sharing_css_path ) );
						}

					} elseif ( ( $fsize = @filesize( $sharing_css_path ) ) > 0 && $fh = @fopen( $sharing_css_path, 'rb' ) ) {

						echo '<style type="text/css">';
						echo fread( $fh, $fsize );
						echo '</style>',"\n";

						fclose( $fh );
					}
				}

			} elseif ( $this->p->debug->enabled ) {

				$this->p->debug->log( 'buttons_use_social_style option is disabled' );
			}
		}
	}
}
