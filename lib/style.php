<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2018 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoRrssbStyle' ) ) {

	class WpssoRrssbStyle {

		private $p;

		public static $sharing_css_name = '';
		public static $sharing_css_file = '';
		public static $sharing_css_url  = '';

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			self::$sharing_css_name = 'rrssb-styles-id-' . get_current_blog_id() . '.min.css';
			self::$sharing_css_file = WPSSO_CACHEDIR . self::$sharing_css_name;
			self::$sharing_css_url  = WPSSO_CACHEURL . self::$sharing_css_name;

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		}

		public function enqueue_styles() {

			$plugin_version = $this->p->cf[ 'plugin' ][ 'wpssorrssb' ][ 'version' ];

			wp_register_style( 'rrssb', WPSSORRSSB_URLPATH . 'css/ext/rrssb.min.css', array(), $plugin_version );

			wp_enqueue_style( 'rrssb' );

			if ( ! empty( $this->p->options['buttons_use_social_style'] ) ) {

				if ( ! file_exists( self::$sharing_css_file ) ) {

					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( 'updating ' . self::$sharing_css_file );
					}

					WpssoRrssbSocial::update_sharing_css( $this->p->options );
				}

				if ( ! empty( $this->p->options['buttons_enqueue_social_style'] ) ) {

					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( 'wp_enqueue_style = ' . $this->p->lca . '_rrssb_sharing_css' );
					}

					wp_enqueue_style( $this->p->lca . '_rrssb_sharing_css', self::$sharing_css_url, false, $plugin_version );

				} else {

					if ( ! is_readable( self::$sharing_css_file ) ) {

						if ( $this->p->debug->enabled ) {
							$this->p->debug->log( self::$sharing_css_file . ' is not readable' );
						}

						if ( is_admin() ) {
							$this->p->notice->err( sprintf( __( 'The %s file is not readable.',
								'wpsso-rrssb' ), self::$sharing_css_file ) );
						}

					} elseif ( ( $fsize = @filesize( self::$sharing_css_file ) ) > 0 &&
						$fh = @fopen( self::$sharing_css_file, 'rb' ) ) {

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
