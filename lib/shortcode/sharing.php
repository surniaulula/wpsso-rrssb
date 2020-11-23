<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2020 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbShortcodeSharing' ) ) {

	class WpssoRrssbShortcodeSharing {

		private $p;	// Wpsso class object.

		private $shortcode_name = 'rrssb';	// Default shortcode name.

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			$this->shortcode_name = WPSSORRSSB_SHARING_SHORTCODE_NAME;

			if ( $this->p->avail[ 'p_ext' ][ 'rrssb' ] ) {

				$this->check_wpautop();

				$this->add_shortcode();

				$this->p->util->add_plugin_actions( $this, array( 
					'pre_apply_filters_text'   => 1,
				) );
			}
		}

		/**
		 * Make sure wpautop() does not have a higher priority than 10, otherwise it will format the shortcode output
		 * (shortcode filters are run at priority 11).
		 */
		public function check_wpautop() {

			$default_priority = 10;

			foreach ( array( 'get_the_excerpt', 'the_excerpt', 'the_content' ) as $filter_name ) {

				$filter_priority = has_filter( $filter_name, 'wpautop' );	// Can return a priority of 0.

				if ( false !== $filter_priority && $filter_priority > $default_priority ) {

					remove_filter( $filter_name, 'wpautop' );

					add_filter( $filter_name, 'wpautop' , $default_priority );

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( 'wpautop() priority changed from ' . $filter_priority . ' to ' . $default_priority );
					}
				}
			}
		}

		/**
		 * Remove our shortcode before applying a text filter.
		 */
		public function action_pre_apply_filters_text( $filter_name ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->log_args( array( 
					'filter_name' => $filter_name,
				) );
			}

			/**
			 * If a shortcode is removed, then re-add it when the text filter is finished executing.
			 */
			if ( $this->remove_shortcode() ) {

				$this->p->util->add_plugin_actions( $this, array( 
					'after_apply_filters_text' => 1,
				) );
			}
		}

		/**
		 * Re-add our shortcode after applying a text filter.
		 */
		public function action_after_apply_filters_text( $filter_name ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->log_args( array( 
					'filter_name' => $filter_name,
				) );
			}

			$this->add_shortcode();
		}

		public function add_shortcode() {

			if ( shortcode_exists( $this->shortcode_name ) ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'cannot add ' . $this->shortcode_name . ' shortcode - already exists' );
				}

				return false;
			}

        		add_shortcode( $this->shortcode_name, array( $this, 'do_shortcode' ) );

			if ( $this->p->debug->enabled ) {

				$this->p->debug->log( $this->shortcode_name . ' shortcode added' );
			}

			return true;
		}

		public function remove_shortcode() {

			if ( shortcode_exists( $this->shortcode_name ) ) {

				remove_shortcode( $this->shortcode_name );

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $this->shortcode_name . ' shortcode removed' );
				}

				return true;

			}

			if ( $this->p->debug->enabled ) {

				$this->p->debug->log( 'cannot remove ' . $this->shortcode_name . ' shortcode - does not exist' );
			}

			return false;
		}

		public function do_shortcode( $atts = array(), $content = null, $tag = '' ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			if ( SucomUtil::is_amp() ) {	// Returns null, true, or false.

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'exiting early: buttons not allowed in amp endpoint'  );
				}

				return $content;

			} elseif ( is_feed() ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'exiting early: buttons not allowed in rss feeds'  );
				}

				return $content;
			}

			$rrssb =& WpssoRrssb::get_instance();

			if ( ! is_array( $atts ) ) {	// Empty string if no shortcode attributes.

				$atts = array();
			}

			$atts = (array) apply_filters( 'wpsso_rrssb_sharing_shortcode_atts', $atts, $content );

			if ( empty( $atts[ 'buttons' ] ) ) {	// Nothing to do.

				return '<!-- ' . $this->shortcode_name . ' shortcode: no buttons attribute -->' . "\n\n";
			}

			$atts[ 'use_post' ]  = SucomUtil::sanitize_use_post( $atts, true );
			$atts[ 'css_class' ] = empty( $atts[ 'css_class' ] ) ? 'rrssb-shortcode' : $atts[ 'css_class' ];

			if ( $this->p->debug->enabled ) {

				$this->p->debug->log( 'required call to get_page_mod()' );
			}

			$mod         = $this->p->util->get_page_mod( $atts[ 'use_post' ] );
			$type        = 'sharing_shortcode_' . $this->shortcode_name;
			$atts[ 'url' ] = empty( $atts[ 'url' ] ) ? $this->p->util->get_sharing_url( $mod ) : $atts[ 'url' ];

			$cache_md5_pre  = 'wpsso_b_';
			$cache_exp_secs = $this->p->util->get_cache_exp_secs( $cache_md5_pre );	// Default is week in seconds.
			$cache_salt     = __METHOD__ . '(' . SucomUtil::get_mod_salt( $mod, $atts[ 'url' ] ) . ')';
			$cache_id       = $cache_md5_pre . md5( $cache_salt );
			$cache_index    = $rrssb->social->get_buttons_cache_index( $type, $atts );
			$cache_array    = array();

			if ( is_404() || is_search() ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'setting cache expiration to 0 seconds for 404 or search page' );
				}

				$cache_exp_secs = 0;
			}

			if ( $this->p->debug->enabled ) {

				$this->p->debug->log( 'sharing url = ' . $atts[ 'url' ] );
				$this->p->debug->log( 'cache expire = ' . $cache_exp_secs );
				$this->p->debug->log( 'cache salt = ' . $cache_salt );
				$this->p->debug->log( 'cache id = ' . $cache_id );
				$this->p->debug->log( 'cache index = ' . $cache_index );
			}

			if ( $cache_exp_secs > 0 ) {

				$cache_array = SucomUtil::get_transient_array( $cache_id );

				if ( isset( $cache_array[ $cache_index ] ) ) {	// Can be an empty string.

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( $type . ' cache index found in transient cache' );
					}

					return $cache_array[ $cache_index ];	// Stop here.

				} else {

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( $type . ' cache index not in transient cache' );
					}

					if ( ! is_array( $cache_array ) ) {

						$cache_array = array();
					}
				}

			} else {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $type . ' buttons transient cache is disabled' );
				}

				if ( SucomUtil::delete_transient_array( $cache_id ) ) {

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( 'deleted transient cache id ' . $cache_id );
					}
				}
			}

			$ids = array_map( 'trim', explode( ',', $atts[ 'buttons' ] ) );

			unset ( $atts[ 'buttons' ] );

			/**
			 * Returns html or an empty string.
			 */
			$cache_array[ $cache_index ] = $rrssb->social->get_html( $ids, $atts, $mod );

			if ( ! empty( $cache_array[ $cache_index ] ) ) {

				$cache_array[ $cache_index ] = '
<!-- wpsso ' . $type . ' begin -->
<div class="wpsso-rrssb wpsso-' . $atts[ 'css_class' ] . '">' . "\n" . 
$cache_array[ $cache_index ] . "\n" . 	// Buttons html is trimmed, so add newline.
'</div><!-- .wpsso-' . $atts[ 'css_class' ] . ' -->' . "\n" . 
'<!-- wpsso ' . $type . ' end -->' . "\n\n";
			}

			if ( $cache_exp_secs > 0 ) {

				/**
				 * Update the cached array and maintain the existing transient expiration time.
				 */
				$expires_in_secs = SucomUtil::update_transient_array( $cache_id, $cache_array, $cache_exp_secs );

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $type . ' buttons saved to transient cache (expires in ' . $expires_in_secs . ' secs)' );
				}
			}

			return $cache_array[ $cache_index ];
		}
	}
}
