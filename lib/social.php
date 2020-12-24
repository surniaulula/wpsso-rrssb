<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2020 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbSocial' ) ) {

	class WpssoRrssbSocial {

		private $p;	// Wpsso class object.
		private $a;	// WpssoRrssb class object.

		private $share = array();	// Associative array of lib/share/ class objects.

		/**
		 * Instantiated by WpssoRrssb->init_objects().
		 */
		public function __construct( &$plugin ) {

			$this->p =& $plugin;
			$this->a =& $addon;

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark( 'rrssb sharing action / filter setup' );	// Begin timer.
			}

			$this->set_objects();

			add_action( 'wp_body_open', array( $this, 'wp_body_open' ) );	// Since WP v5.2.

			if ( $this->have_buttons_for_type( 'content' ) ) {

				$this->add_buttons_filter( 'the_content' );
			}

			if ( $this->have_buttons_for_type( 'excerpt' ) ) {

				$this->add_buttons_filter( 'get_the_excerpt' );
				$this->add_buttons_filter( 'the_excerpt' );
			}

			/**
			 * Maybe disable the sharing buttons transient cache for debugging purposes.
			 */
			if ( ! empty( $this->p->options[ 'plugin_cache_disable' ] ) || SucomUtil::get_const( 'WPSSO_CACHE_DISABLE' ) ) {

				$this->p->util->add_plugin_filters( $this, array(
					'cache_expire_sharing_buttons' => '__return_zero',
				) );
			}

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark( 'rrssb sharing action / filter setup' );	// End timer.
			}
		}

		private function set_objects() {

			foreach ( $this->p->cf[ 'plugin' ][ 'wpssorrssb' ][ 'lib' ][ 'share' ] as $id => $name ) {

				$classname = WpssoRrssbConfig::load_lib( false, 'share/' . $id, 'wpssorrssbshare' . $id );

				if ( false !== $classname && class_exists( $classname ) ) {

					$this->share[ $id ] = new $classname( $this->p );

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( $classname . ' class loaded' );
					}
				}
			}
		}

		public function get_share_objets() {

			return $this->share;
		}

		public static function get_sharing_css_name() {

			return 'rrssb-styles-id-' . get_current_blog_id() . '.min.css';
		}

		public static function get_sharing_css_path() {

			return WPSSO_CACHE_DIR . self::get_sharing_css_name();
		}

		public static function get_sharing_css_url() {

			return WPSSO_CACHE_URL . self::get_sharing_css_name();
		}

		public static function update_sharing_css( &$opts ) {

			$wpsso =& Wpsso::get_instance();

			if ( empty( $opts[ 'buttons_use_social_style' ] ) ) {

				self::unlink_sharing_css();

				return;
			}

			$styles = apply_filters( 'wpsso_rrssb_styles', $wpsso->cf[ 'sharing' ][ 'rrssb_styles' ] );

			$sharing_css_data = '';

			foreach ( $styles as $id => $name ) {

				if ( isset( $opts[ 'buttons_css_' . $id ] ) ) {

					$sharing_css_data .= $opts[ 'buttons_css_' . $id ];
				}
			}

			$sharing_css_data = SucomUtil::minify_css( $sharing_css_data, $ext = 'wpsso' );
			$sharing_css_path = self::get_sharing_css_path();

			if ( $fh = @fopen( $sharing_css_path, 'wb' ) ) {

				if ( ( $written = fwrite( $fh, $sharing_css_data ) ) === false ) {

					if ( $wpsso->debug->enabled ) {

						$wpsso->debug->log( 'failed writing the css file ' . $sharing_css_path );
					}

					if ( is_admin() ) {

						$wpsso->notice->err( sprintf( __( 'Failed writing the css file %s.', 'wpsso-rrssb' ), $sharing_css_path ) );
					}

				} elseif ( $wpsso->debug->enabled ) {

					$wpsso->debug->log( 'updated css file ' . $sharing_css_path . ' (' . $written . ' bytes written)' );
				}

				fclose( $fh );

			} else {

				if ( ! is_writable( WPSSO_CACHE_DIR ) ) {

					if ( $wpsso->debug->enabled ) {

						$wpsso->debug->log( 'cache folder ' . WPSSO_CACHE_DIR . ' is not writable' );
					}

					if ( is_admin() ) {

						$wpsso->notice->err( sprintf( __( 'Cache folder %s is not writable.', 'wpsso-rrssb' ), WPSSO_CACHE_DIR ) );
					}
				}

				if ( $wpsso->debug->enabled ) {

					$wpsso->debug->log( 'failed to open the css file ' . $sharing_css_path . ' for writing' );
				}

				if ( is_admin() ) {

					$wpsso->notice->err( sprintf( __( 'Failed to open the css file %s for writing.', 'wpsso-rrssb' ), $sharing_css_path ) );
				}
			}
		}

		public static function unlink_sharing_css() {

			$wpsso =& Wpsso::get_instance();

			$sharing_css_path = self::get_sharing_css_path();

			if ( file_exists( $sharing_css_path ) ) {

				if ( ! @unlink( $sharing_css_path ) ) {

					if ( is_admin() ) {

						$wpsso->notice->err( __( 'Error removing the minified stylesheet &mdash; does the web server have sufficient privileges?',
							'wpsso-rrssb' ) );
					}
				}
			}
		}

		public function wp_body_open() {

			if ( $this->have_buttons_for_type( 'sidebar' ) ) {

				$this->show_sidebar();

			} elseif ( $this->p->debug->enabled ) {

				$this->p->debug->log( 'no buttons enabled for sidebar' );
			}
		}

		public function show_sidebar() {

			echo "\n\n";

			echo $this->get_buttons( $text = '', $type = 'sidebar', $use_post = false, $location = 'bottom', $atts = array( 'container_each' => true ) );
		}

		public function add_buttons_filter( $filter_name = 'the_content' ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->log_args( array( 
					'filter_name' => $filter_name,
				) );
			}

			$added = false;

			if ( empty( $filter_name ) ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'filter_name argument is empty' );
				}

			} elseif ( method_exists( $this, 'get_buttons_for_' . $filter_name ) ) {

				$added = add_filter( $filter_name, array( $this, 'get_buttons_for_' . $filter_name ) );

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'buttons filter ' . $filter_name . ' added (' . ( $added  ? 'true' : 'false' ) . ')' );
				}

			} elseif ( $this->p->debug->enabled ) {

				$this->p->debug->log( 'get_buttons_for_' . $filter_name . ' method is missing' );
			}

			return $added;
		}

		public function remove_buttons_filter( $filter_name = 'the_content' ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->log_args( array( 
					'filter_name' => $filter_name,
				) );
			}

			$removed = false;

			if ( method_exists( $this, 'get_buttons_for_' . $filter_name ) ) {

				$removed = remove_filter( $filter_name, array( $this, 'get_buttons_for_' . $filter_name ) );

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'buttons filter ' . $filter_name . ' removed (' . ( $removed  ? 'true' : 'false' ) . ')' );
				}
			}

			return $removed;
		}

		public function get_buttons( $text, $type = 'content', $mod = true, $location = '', $atts = array() ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark( 'getting buttons for ' . $type );	// Begin timer.
			}

			$is_admin   = is_admin();
			$is_amp     = SucomUtil::is_amp();	// Returns null, true, or false.
			$doing_ajax = SucomUtilWP::doing_ajax();
			$error_msg  = '';

			if ( $doing_ajax ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'doing_ajax is true' );
				}

			} elseif ( $is_admin ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'is_admin is true' );
				}

				if ( strpos( $type, 'admin_' ) !== 0 ) {

					$error_msg = $type . ' ignored in back-end';
				}

			} elseif ( $is_amp ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'is_amp is true' );
				}

				$error_msg = 'buttons not allowed in amp endpoint';

			} elseif ( is_feed() ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'is_feed is true' );
				}

				$error_msg = 'buttons not allowed in rss feeds';

			} elseif ( ! is_singular() ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'is_singular is false' );
				}

				if ( empty( $this->p->options[ 'buttons_on_index' ] ) ) {

					$error_msg = 'buttons_on_index not enabled';
				}

			} elseif ( is_front_page() ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'is_front_page is true' );
				}

				if ( empty( $this->p->options[ 'buttons_on_front' ] ) ) {

					$error_msg = 'buttons_on_front not enabled';
				}

			} elseif ( is_singular() ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'is_singular is true' );
				}

				if ( $this->is_post_buttons_disabled() ) {

					$error_msg = 'post buttons are disabled';
				}

			} elseif ( ! apply_filters( 'wpsso_rrssb_add_buttons', true, $type, $mod, $location ) ) {

				$error_msg = 'wpsso_rrssb_add_buttons filter returned false';
			}

			if ( empty( $error_msg ) ) {

				if ( ! $this->have_buttons_for_type( $type ) ) {

					$error_msg = 'no sharing buttons enabled';
				}
			}

			if ( ! empty( $error_msg ) ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $type . ' filter skipped: ' . $error_msg );

					$this->p->debug->mark( 'getting buttons for ' . $type );	// End timer.
				}

				return $text . "\n" . '<!-- ' . __METHOD__ . ' ' . $type . ' filter skipped: ' . $error_msg . ' -->' . "\n";
			}

			/**
			 * The $mod array argument is preferred but not required.
			 *
			 * $mod = true | false | post_id | $mod array
			 */
			if ( ! is_array( $mod ) ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'optional call to WpssoPage->get_mod()' );
				}

				$mod = $this->p->page->get_mod( $mod );
			}

			if ( $this->p->debug->enabled ) {

				$this->p->debug->log( 'getting sharing url' );
			}

			$sharing_url = $this->p->util->get_sharing_url( $mod );

			$cache_md5_pre  = 'wpsso_b_';
			$cache_exp_secs = $this->p->util->get_cache_exp_secs( $cache_md5_pre );	// Default is week in seconds.
			$cache_salt     = __METHOD__ . '(' . SucomUtil::get_mod_salt( $mod, $sharing_url ) . ')';
			$cache_id       = $cache_md5_pre . md5( $cache_salt );
			$cache_index    = $this->get_buttons_cache_index( $type );
			$cache_array    = array();

			if ( $mod[ 'is_404' ] || $mod[ 'is_search' ] ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'setting cache expiration to 0 seconds for 404 or search page' );
				}

				$cache_exp_secs = 0;
			}

			if ( $this->p->debug->enabled ) {

				$this->p->debug->log( 'sharing url = ' . $sharing_url );
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

					/**
					 * Continue and add buttons relative to the content (top, bottom, or both).
					 */

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

			if ( empty( $location ) ) {

				$location = empty( $this->p->options[ 'buttons_pos_' . $type ] ) ? 'bottom' : $this->p->options[ 'buttons_pos_' . $type ];
			}

			if ( ! isset( $cache_array[ $cache_index ] ) ) {

				/**
				 * Sort enabled sharing buttons by their preferred order.
				 */
				$sorted_ids = array();

				foreach ( $this->p->cf[ 'opt' ][ 'cm_prefix' ] as $id => $opt_pre ) {

					if ( ! empty( $this->p->options[ $opt_pre . '_on_' . $type ] ) ) {

						$button_order = empty( $this->p->options[ $opt_pre . '_button_order' ] ) ? 0 : $this->p->options[ $opt_pre . '_button_order' ];

						$sorted_ids[ zeroise( $button_order, 3 ) . '-' . $id ] = $id;
					}
				}

				ksort( $sorted_ids );

				$atts[ 'use_post' ] = $mod[ 'use_post' ];

				/**
				 * Returns html or an empty string.
				 */
				$cache_array[ $cache_index ] = $this->get_html( $sorted_ids, $atts, $mod );

				if ( ! empty( $cache_array[ $cache_index ] ) ) {

					$css_type      = 'rrssb-' . $type;
					$css_id        = 'sidebar' === $type ? 'wpsso-' . $css_type . ' ' : '';
					$css_class     = 'wpsso-rrssb wpsso-' . $css_type;
					$css_class_max = 'wpsso-rrssb-limit wpsso-' . $css_type . '-limit';

					/**
					 * Do not count $sorted_ids since some buttons may not return anything (ie. the WhatsApp
					 * button for a desktop browser).
					 */
					$buttons_count = preg_match_all( '/<li/', $cache_array[ $cache_index ] );
					$css_style_max = 'max-width:' . ( 116 * $buttons_count ) . 'px;';

					if ( $mod[ 'name' ] ) {

						$css_id .= 'wpsso-' . $css_type . '-' . $mod[ 'name' ];

						if ( $mod[ 'id' ] ) {

							$css_id .= '-' . (int) $mod[ 'id' ];
						}
					}

					$cache_array[ $cache_index ] = '' .
						'<!-- wpsso ' . $css_type . ' begin -->' .	// Used by $this->get_buttons_for_the_excerpt().
						'<div class="' . $css_class . '" id="' . trim( $css_id ) . '">' . 
						'<div class="' . $css_class_max . '" style="' . $css_style_max . '">' . 
						$cache_array[ $cache_index ] .
						'</div>' . "\n" .
						'</div><!-- .wpsso-rrssb -->' .
						'<!-- wpsso ' . $css_type . ' end -->' .	// Used by $this->get_buttons_for_the_excerpt().
						'<!-- generated on ' . date( 'c' ) . ' -->';

					$cache_array[ $cache_index ] = apply_filters( 'wpsso_rrssb_buttons_html',
						$cache_array[ $cache_index ], $type, $mod, $location, $atts );
				}

				if ( $cache_exp_secs > 0 ) {

					/**
					 * Update the cached array and maintain the existing transient expiration time.
					 */
					$expires_in_secs = SucomUtil::update_transient_array( $cache_id, $cache_array, $cache_exp_secs );

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( $type . ' buttons html saved to transient cache (expires in ' . $expires_in_secs . ' secs)' );
					}
				}
			}

			switch ( $location ) {

				case 'top': 

					$text = $cache_array[ $cache_index ] . $text;

					break;

				case 'bottom': 

					$text = $text . $cache_array[ $cache_index ];

					break;

				case 'both': 

					$text = $cache_array[ $cache_index ] . $text . $cache_array[ $cache_index ];

					break;
			}

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark( 'getting buttons for ' . $type );	// End timer.
			}

			return $text;
		}

		public function get_buttons_cache_index( $type, $atts = false, $share_ids = false ) {

			$cache_index = 'locale:' . SucomUtil::get_locale( 'current' );

			$cache_index .= '_type:' . ( empty( $type ) ? 'none' : $type );

			$cache_index .= '_https:' . ( SucomUtil::is_https() ? 'true' : 'false' );

			$cache_index .= false !== $atts ? '_atts:' . http_build_query( $atts, '', '_' ) : '';

			$cache_index .= false !== $share_ids ? '_share_ids:' . http_build_query( $share_ids, '', ',' ) : '';

			$cache_index = apply_filters( 'wpsso_rrssb_buttons_cache_index', $cache_index );

			return $cache_index;
		}

		public function get_buttons_for_the_content( $text ) {

			return $this->get_buttons( $text, 'content' );
		}

		public function get_buttons_for_get_the_excerpt( $text ) {

			return $this->get_buttons( $text, 'excerpt' );
		}

		public function get_buttons_for_the_excerpt( $text ) {

			$css_type = 'rrssb-excerpt';

			$text = preg_replace_callback( '/<!-- wpsso ' . $css_type . ' begin -->(.*)<!-- wpsso ' . $css_type . ' end -->/Usi',
				array( $this, 'remove_wp_breaks' ), $text );

			return $text;
		}

		private function remove_wp_breaks( array $match ) {

			return preg_replace( '/(<(\/?p|br ?\/?)>|\n)/i', '', $match[ 1 ] );
		}

		/**
		 * Called by $this->get_buttons().
		 *
		 * get_html() can also be called by a widget, shortcode, function, filter hook, etc.
		 */
		public function get_html( array $share_ids, array $atts, $mod = false ) {

			$atts[ 'use_post' ] = isset( $atts[ 'use_post' ] ) ? $atts[ 'use_post' ] : true;	// Maintain backwards compat.
			$atts[ 'add_page' ] = isset( $atts[ 'add_page' ] ) ? $atts[ 'add_page' ] : true;	// Used by get_sharing_url().

			/**
			 * The $mod array argument is preferred but not required.
			 *
			 * $mod = true | false | post_id | $mod array
			 */
			if ( ! is_array( $mod ) ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'optional call to WpssoPage->get_mod()' );
				}

				$mod = $this->p->page->get_mod( $atts[ 'use_post' ] );
			}

			$buttons_html  = '';
			$buttons_begin = '<ul class="rrssb-buttons ' . SucomUtil::get_locale( $mod ) . ' clearfix">' . "\n";
			$buttons_end   = '</ul><!-- .rrssb-buttons.' . SucomUtil::get_locale( $mod ) . '.clearfix -->' . "\n";
			$saved_atts    = $atts;

			foreach ( $share_ids as $id ) {

				if ( isset( $this->share[ $id ] ) ) {

					if ( method_exists( $this->share[ $id ], 'get_html' ) ) {

						if ( empty( $atts[ 'url' ] ) ) {

							$atts[ 'url' ] = $this->p->util->get_sharing_url( $mod, $atts[ 'add_page' ] );

						} else {

							$atts[ 'url' ] = apply_filters( 'wpsso_sharing_url', $atts[ 'url' ], $mod, $atts[ 'add_page' ] );
						}

						/**
						 * Filter to add custom tracking arguments.
						 */
						$atts[ 'url' ] = apply_filters( 'wpsso_rrssb_buttons_shared_url', $atts[ 'url' ], $mod, $id );

						$force_prot = apply_filters( 'wpsso_rrssb_buttons_force_prot', $this->p->options[ 'buttons_force_prot' ],
							$mod, $id, $atts[ 'url' ] );

						if ( ! empty( $force_prot ) && $force_prot !== 'none' ) {

							$atts[ 'url' ] = preg_replace( '/^.*:\/\//', $force_prot . '://', $atts[ 'url' ] );
						}

						/**
						 * Do not terminate with a newline to avoid WordPress adding breaks and paragraphs.
						 */
						$buttons_part = $this->share[ $id ]->get_html( $atts, $this->p->options, $mod );

						$atts = $saved_atts;	// Restore the common $atts array.

						if ( false !== strpos( $buttons_part, '<li' ) ) {

							if ( empty( $atts[ 'container_each' ] ) ) {

								$buttons_html .= $buttons_part;

							} else {

								$buttons_html .= '<!-- adding buttons as individual containers -->' . "\n" . 
									$buttons_begin . $buttons_part . $buttons_end;
							}
						}

					} elseif ( $this->p->debug->enabled ) {

						$this->p->debug->log( 'get_html method missing for ' . $id );
					}

				} elseif ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'share object missing for ' . $id );
				}
			}

			$buttons_html = trim( $buttons_html );

			if ( ! empty( $buttons_html ) ) {

				if ( empty( $atts[ 'container_each' ] ) ) {

					$buttons_html = $buttons_begin . $buttons_html . $buttons_end;
				}
			}

			return $buttons_html;
		}

		public function have_buttons_for_type( $type ) {

			static $local_cache = array();

			if ( isset( $local_cache[ $type ] ) ) {

				return $local_cache[ $type ];
			}

			foreach ( $this->p->cf[ 'opt' ][ 'cm_prefix' ] as $id => $opt_pre ) {

				if ( ! empty( $this->p->options[ $opt_pre . '_on_' . $type ] ) ) {	// Check if button is enabled.

					return $local_cache[ $type ] = true;	// Stop here.
				}
			}

			return $local_cache[ $type ] = false;
		}

		public function is_post_buttons_disabled() {

			$ret = false;

			static $local_cache = array();

			if ( ( $post_obj = SucomUtil::get_post_object() ) === false ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'exiting early: invalid post object' );
				}

				return $ret;

			}

			$post_id = empty( $post_obj->ID ) ? 0 : $post_obj->ID;

			if ( empty( $post_id ) ) {

				return $ret;
			}

			if ( isset( $local_cache[ $post_id ] ) ) {

				return $local_cache[ $post_id ];
			}

			if ( $this->p->post->get_options( $post_id, 'buttons_disabled' ) ) {	// Returns null if an index key is not found.

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'post ' . $post_id . ': sharing buttons disabled by meta data option' );
				}

				$ret = true;

			} elseif ( ! empty( $post_obj->post_type ) && empty( $this->p->options[ 'buttons_add_to_' . $post_obj->post_type ] ) ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'post ' . $post_id . ': sharing buttons not enabled for post type ' . $post_obj->post_type );
				}

				$ret = true;
			}

			return $local_cache[ $post_id ] = apply_filters( 'wpsso_post_buttons_disabled', $ret, $post_id );
		}

		public function get_share_ids( $share = array() ) {

			$share_ids = array();

			if ( empty( $share ) ) {

				$keys = array_keys( $this->share );

			} else {

				$keys = array_keys( $share );
			}

			$share_lib = $this->p->cf[ 'plugin' ][ 'wpssorrssb' ][ 'lib' ][ 'share' ];

			foreach ( $keys as $id ) {

				$share_ids[ $id ] = isset( $share_lib[ $id ] ) ? $share_lib[ $id ] : ucfirst( $id );
			}

			return $share_ids;
		}

		public static function get_tweet_text( array $mod, $atts = array(), $opt_pre = 'twitter', $md_pre = 'twitter' ) {

			$wpsso =& Wpsso::get_instance();

			if ( ! isset( $atts[ 'tweet' ] ) ) {	// Just in case.

				$atts[ 'use_post' ]     = isset( $atts[ 'use_post' ] ) ? $atts[ 'use_post' ] : true;
				$atts[ 'add_page' ]     = isset( $atts[ 'add_page' ] ) ? $atts[ 'add_page' ] : true;	// Used by get_sharing_url().
				$atts[ 'add_hashtags' ] = isset( $atts[ 'add_hashtags' ] ) ? $atts[ 'add_hashtags' ] : true;

				$caption_type = empty( $wpsso->options[ $opt_pre . '_caption' ] ) ? 'title' : $wpsso->options[ $opt_pre . '_caption' ];

				$caption_max_len = self::get_tweet_max_len( $opt_pre );

				$atts[ 'tweet' ] = $wpsso->page->get_caption( $caption_type, $caption_max_len, $mod,
					$read_cache = true, $atts[ 'add_hashtags' ], $do_encode = false, $md_key = $md_pre . '_desc' );
			}

			return $atts[ 'tweet' ];
		}

		/**
		 * $opt_pre can be twitter, buffer, etc.
		 */
		public static function get_tweet_max_len( $opt_pre = 'twitter', $num_urls = 1 ) {

			$wpsso =& Wpsso::get_instance();

			$short_len = 23 * $num_urls;	// Twitter counts 23 characters for any url.

			if ( isset( $wpsso->options[ 'tc_site' ] ) && ! empty( $wpsso->options[ $opt_pre . '_via' ] ) ) {

				$tc_site  = preg_replace( '/^@/', '', $wpsso->options[ 'tc_site' ] );
				$site_len = empty( $tc_site ) ? 0 : strlen( $tc_site ) + 6;

			} else {
				$site_len = 0;
			}

			$caption_max_len = $wpsso->options[ $opt_pre . '_caption_max_len' ] - $site_len - $short_len;

			if ( $wpsso->debug->enabled ) {

				$wpsso->debug->log( 'max tweet length is ' . $caption_max_len . ' chars ' .
					'(' . $wpsso->options[ $opt_pre . '_caption_max_len' ] . ' less ' . $site_len .
						' for site name and ' . $short_len . ' for url)' );
			}

			return $caption_max_len;
		}
	}
}
