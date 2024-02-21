<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbSocial' ) ) {

	class WpssoRrssbSocial {

		private $p;	// Wpsso class object.
		private $a;	// WpssoRrssb class object.

		private $share = array();	// Associative array of lib/share/ class objects.

		/*
		 * Instantiated by WpssoRrssb->init_objects().
		 */
		public function __construct( &$plugin, &$addon ) {

			$this->p =& $plugin;
			$this->a =& $addon;

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark( 'rrssb sharing action / filter setup' );	// Begin timer.
			}

			$this->set_objects();

			add_action( 'wp_body_open', array( $this, 'wp_body_open' ) );

			if ( $this->have_buttons_for_type( 'content' ) ) {

				$this->add_buttons_filter( 'the_content' );
			}

			if ( $this->have_buttons_for_type( 'excerpt' ) ) {

				$this->add_buttons_filter( 'get_the_excerpt' );

				$this->add_buttons_filter( 'the_excerpt' );
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

			$sharing_css_data = SucomUtil::minify_css( $sharing_css_data, $filter_prefix = 'wpsso' );

			$sharing_css_path = self::get_sharing_css_path();

			if ( $fh = @fopen( $sharing_css_path, 'wb' ) ) {

				if ( ( $written = fwrite( $fh, $sharing_css_data ) ) === false ) {

					if ( is_admin() ) {

						$wpsso->notice->err( sprintf( __( 'Failed writing the css file %s.', 'wpsso-rrssb' ), $sharing_css_path ) );
					}
				}

				fclose( $fh );

			} else {

				if ( ! is_writable( WPSSO_CACHE_DIR ) ) {

					if ( is_admin() ) {

						$wpsso->notice->err( sprintf( __( 'Cache folder %s is not writable.', 'wpsso-rrssb' ), WPSSO_CACHE_DIR ) );
					}
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

						$wpsso->notice->err( __( 'Error removing the minified stylesheet - does the web server have sufficient privileges?',
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

			$atts = array( 'container_each' => true );

			echo "\n\n" . $this->get_buttons( $text = '', $type = 'sidebar', $use_post = false, $location = 'bottom', $atts );
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

		/*
		 * The WpssoRrssbSocial->show_sidebar() method passes $atts = array( 'container_each' => true ).
		 */
		public function get_buttons( $text = '', $type = 'content', $mod = true, $location = '', $atts = array() ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark( 'getting buttons for ' . $type );	// Begin timer.
			}

			$is_admin         = is_admin();
			$is_amp           = SucomUtilWP::is_amp();
			$buttons_disabled = false;

			if ( $is_amp ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'is_amp is true' );
				}

				$buttons_disabled = true;

				$text .= '<!-- wpsso rrssb get buttons: buttons not allowed in amp endpoint -->' . "\n";

			} elseif ( is_feed() ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'is_feed is true' );
				}

				$buttons_disabled = true;

				$text .= '<!-- wpsso rrssb get buttons: buttons not allowed in rss feed -->' . "\n";

			} elseif ( $is_admin ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'is_admin is true' );
				}

				if ( strpos( $type, 'admin_' ) !== 0 ) {

					$buttons_disabled = true;

					$text .= '<!-- wpsso rrssb get buttons: ' . $type . ' buttons ignore in back-end -->' . "\n";
				}

			} elseif ( ! is_singular() ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'is_singular is false' );
				}

				if ( empty( $this->p->options[ 'buttons_on_archive' ] ) ) {

					$buttons_disabled = true;

					$text .= '<!-- wpsso rrssb get buttons: buttons on archive option not enabled -->' . "\n";
				}

			} elseif ( is_front_page() ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'is_front_page is true' );
				}

				if ( empty( $this->p->options[ 'buttons_on_front' ] ) ) {

					$buttons_disabled = true;

					$text .= '<!-- wpsso rrssb get buttons: buttons on front option not enabled -->' . "\n";
				}

			} elseif ( is_singular() ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'is_singular is true' );
				}

				if ( $this->is_post_buttons_disabled() ) {

					$buttons_disabled = true;

					$text .= '<!-- wpsso rrssb get buttons: post buttons disabled -->' . "\n";
				}
			}

			if ( ! $buttons_disabled ) {

				if ( ! $this->have_buttons_for_type( $type ) ) {

					$buttons_disabled = true;

					$text .= '<!-- wpsso rrssb get buttons: no buttons for type ' . $type . ' -->' . "\n";
				}
			}

			$buttons_disabled = apply_filters( 'wpsso_rrssb_buttons_disabled', $buttons_disabled, $type, $mod, $location );

			if ( $buttons_disabled ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'exiting early: add buttons is false' );

					$this->p->debug->mark( 'getting buttons for ' . $type );	// End timer.
				}

				return $text;
			}

			/*
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

			if ( empty( $location ) ) {

				$location = empty( $this->p->options[ 'buttons_pos_' . $type ] ) ? 'bottom' : $this->p->options[ 'buttons_pos_' . $type ];

				if ( 'both' === $location && '' === trim( $text ) ) {	// Avoid adding both top and bottom on empty content.

					$location = 'bottom';
				}
			}

			/*
			 * Sort enabled sharing buttons by their preferred order.
			 */
			$sorted_ids = array();

			foreach ( $this->p->cf[ 'opt' ][ 'cm_prefix' ] as $id => $opt_pre ) {

				if ( ! empty( $this->p->options[ $opt_pre . '_on_' . $type ] ) ) {

					$button_order = empty( $this->p->options[ $opt_pre . '_button_order' ] ) ? 0 : $this->p->options[ $opt_pre . '_button_order' ];

					$sorted_ids[ zeroise( $button_order, 3 ) . '-' . $id ] = $id;
				}
			}

			ksort( $sorted_ids );	// Sort associative array by key.

			$atts[ 'use_post' ]    = $mod[ 'use_post' ];
			$atts[ 'utm_content' ] = 'wpsso-rrssb-' . sanitize_title_with_dashes( $type . '-' . $location );

			$sorted_ids = apply_filters( 'wpsso_rrssb_buttons_ids', $sorted_ids, $type, $mod, $location, $atts );

			$buttons_html = $this->get_html( $sorted_ids, $mod, $atts );

			if ( ! empty( $buttons_html ) ) {

				$buttons_count = preg_match_all( '/<li/', $buttons_html );	// Returns number of matches or false on error.

				$css_type      = 'rrssb-' . $type;
				$css_class     = 'wpsso-rrssb wpsso-' . $css_type . ' wpsso-rrssb-pos-' . $location;
				$css_class_max = 'wpsso-rrssb-limit wpsso-' . $css_type . '-limit';
				$css_style_max = 'max-width:' . ( WPSSORRSSB_MAX_WIDTH_MULTIPLIER * $buttons_count ) . 'px; margin:0 auto;';

				if ( 'sidebar' === $type ) {

					$css_id = 'wpsso-' . $css_type;

				} elseif ( $mod[ 'name' ] ) {

					$css_id = 'wpsso-' . $css_type . '-' . $mod[ 'name' ];

					if ( $mod[ 'id' ] ) {

						$css_id .= '-' . (int) $mod[ 'id' ];
					}
				}

				$mod_locale  = SucomUtilWP::get_locale( $mod );
				$buttons_cta = $this->p->opt->get_text( 'buttons_cta', $use_opts = true, $mod );
				$buttons_cta = apply_filters( 'wpsso_rrssb_buttons_cta', $buttons_cta, $type, $mod, $location, $atts );
				$buttons_cta = '<div class="rrssb-buttons-cta ' . $mod_locale . '">' . $buttons_cta . '</div>';

				$buttons_html = '<!-- wpsso ' . $css_type . ' begin -->' .	// Used by $this->get_buttons_for_the_excerpt().
					'<div class="' . $css_class . '" id="' . $css_id . '">' .
					'<div class="' . $css_class_max . '" style="' . $css_style_max . '">' .
					$buttons_cta .
					$buttons_html .
					'</div><!-- .wpsso-rrssb-limit -->' .
					'</div><!-- .wpsso-rrssb -->' .
					'<!-- wpsso ' . $css_type . ' end -->';			// Used by $this->get_buttons_for_the_excerpt().
			}

			$buttons_html = apply_filters( 'wpsso_rrssb_buttons_html', $buttons_html, $type, $mod, $location, $atts );

			if ( ! empty( $buttons_html ) ) {

				switch ( $location ) {

					case 'top':

						$text = $buttons_html . $text;

						break;

					case 'bottom':

						$text = $text . $buttons_html;

						break;

					case 'both':

						$text = $buttons_html . $text . $buttons_html;

						break;
				}
			}

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark( 'getting buttons for ' . $type );	// End timer.
			}

			return $text;
		}

		public function get_buttons_for_the_content( $text ) {

			return $this->get_buttons( $text, $type = 'content' );
		}

		public function get_buttons_for_get_the_excerpt( $text ) {

			return $this->get_buttons( $text, $type = 'excerpt' );
		}

		public function get_buttons_for_the_excerpt( $text ) {

			$css_type = 'rrssb-excerpt';

			$text = preg_replace_callback( '/<!-- wpsso ' . $css_type . ' begin -->(.*)<!-- wpsso ' . $css_type . ' end -->/Usi',
				array( $this, 'remove_wp_breaks_callback' ), $text );

			return $text;
		}

		/*
		 * Called by WpssoRrssbSocial->get_buttons().
		 * Called by WpssoRrssbShortcodeSharing->do_shortcode().
		 * Called by WpssoRrssbWidgetSharing->widget().
		 * Called by wpssorrssb_get_sharing_buttons().
		 *
		 * Note that the $atts array may include additional user input from the RRSSB shortcode attributes.
		 */
		public function get_html( $ids, $mod, $atts ) {

			/*
			 * Basic attributes for all buttons.
			 */
			$atts[ 'use_post' ]   = isset( $atts[ 'use_post' ] ) ? $atts[ 'use_post' ] : true;
			$atts[ 'add_page' ]   = isset( $atts[ 'add_page' ] ) ? $atts[ 'add_page' ] : true;

			/*
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

			$mod_locale    = SucomUtilWP::get_locale( $mod );
			$buttons_begin = '<ul class="rrssb-buttons ' . $mod_locale . ' clearfix">';
			$buttons_html  = '';
			$buttons_end   = '</ul><!-- .rrssb-buttons.' . $mod_locale . '.clearfix -->';
			$saved_atts    = $atts;

			foreach ( $ids as $id ) {

				if ( isset( $this->share[ $id ] ) ) {

					if ( method_exists( $this->share[ $id ], 'get_html' ) ) {	// Just in case.

						/*
						 * Get the social sharing button UTM source name (ie. 'facebook').
						 */
						if ( ! empty( $this->p->cf[ 'opt' ][ 'cm_prefix' ][ $id ] ) ) {	// Skip if empty.

							$opt_pre = $this->p->cf[ 'opt' ][ 'cm_prefix' ][ $id ];

							if ( isset( $this->p->options[ $opt_pre . '_utm_source' ] ) ) {	// Empty is ok.

								$atts[ 'utm_source' ] = $this->p->options[ $opt_pre . '_utm_source' ];
							}
						}

						/*
						 * Backwards compatible filter to add custom tracking arguments.
						 *
						 * Note that an RRSSB shortcode attribute may already provide the sharing URL.
						 */
						if ( empty( $atts[ 'sharing_url' ] ) ) {

							$atts[ 'sharing_url' ] = $this->p->util->get_sharing_url( $mod, $atts[ 'add_page' ], $atts );
							$atts[ 'sharing_url' ] = apply_filters( 'wpsso_rrssb_buttons_shared_url', $atts[ 'sharing_url' ], $mod, $id );
							$atts[ 'sharing_url' ] = $this->maybe_force_prot( $atts[ 'sharing_url' ], $mod, $id );
						}

						/*
						 * Maybe shorten the sharing URL.
						 *
						 * We already have a complete and filtered sharing URL, so we can shorten the URL
						 * here instead of using the WpssoUtil->get_sharing_short_url() method.
						 */
						$atts[ 'sharing_short_url' ] = $this->p->util->shorten_url( $atts[ 'sharing_url' ], $mod );

						/*
						 * Signal to encode values as URL query arguments.
						 */
						$atts[ 'rawurlencode' ] = true;

						/*
						 * Do not terminate with a newline to avoid WordPress adding breaks and paragraphs.
						 */
						$buttons_part = $this->share[ $id ]->get_html( $mod, $atts );

						/*
						 * Restore the common attributes array.
						 */
						$atts = $saved_atts;

						/*
						 * Add the button HTML and maybe align the buttons vertically.
						 */
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

		/*
		 * Maybe force a URL protocol to http or https.
		 */
		public function maybe_force_prot( $url, $mod, $id ) {

			/*
			 * Use false instead of 'none' for the 'wpsso_rrssb_buttons_force_prot' filter.
			 */
			$prot = empty( $this->p->options[ 'buttons_force_prot' ] ) ||
				'none' === $this->p->options[ 'buttons_force_prot' ] ?
					false : $this->p->options[ 'buttons_force_prot' ];

			$prot = apply_filters( 'wpsso_rrssb_buttons_force_prot', $prot, $mod, $id, $url );

			if ( $prot && is_string( $prot ) && 'none' !== $prot ) {

				$url = preg_replace( '/^\w*:?\/\//', $prot . '://', $url );
			}

			return $url;
		}

		public function have_buttons_for_type( $type ) {

			static $local_cache = array();

			if ( isset( $local_cache[ $type ] ) ) {

				return $local_cache[ $type ];
			}

			foreach ( $this->p->cf[ 'opt' ][ 'cm_prefix' ] as $id => $opt_pre ) {

				if ( ! empty( $this->p->options[ $opt_pre . '_on_' . $type ] ) ) {

					return $local_cache[ $type ] = true;
				}
			}

			return $local_cache[ $type ] = false;
		}

		public function is_post_buttons_disabled() {

			$disabled = false;

			static $local_cache = array();

			if ( false === ( $post_obj = SucomUtilWP::get_post_object() ) ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'exiting early: invalid post object' );
				}

				return $disabled;
			}

			$post_id = empty( $post_obj->ID ) ? 0 : $post_obj->ID;

			if ( empty( $post_id ) ) {

				return $disabled;
			}

			if ( isset( $local_cache[ $post_id ] ) ) {

				return $local_cache[ $post_id ];
			}

			if ( $this->p->post->get_options( $post_id, 'buttons_disabled' ) ) {	// Returns null if an index key is not found.

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'post ' . $post_id . ': sharing buttons disabled by meta data option' );
				}

				$disabled = true;

			} elseif ( ! empty( $post_obj->post_type ) && empty( $this->p->options[ 'buttons_add_to_' . $post_obj->post_type ] ) ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'post ' . $post_id . ': sharing buttons not enabled for post type ' . $post_obj->post_type );
				}

				$disabled = true;
			}

			return $local_cache[ $post_id ] = $disabled;
		}

		public function get_share_ids( $share = array() ) {

			$ids = array();

			if ( empty( $share ) ) {

				$keys = array_keys( $this->share );

			} else {

				$keys = array_keys( $share );
			}

			$share_lib = $this->p->cf[ 'plugin' ][ 'wpssorrssb' ][ 'lib' ][ 'share' ];

			foreach ( $keys as $id ) {

				$ids[ $id ] = isset( $share_lib[ $id ] ) ? $share_lib[ $id ] : ucfirst( $id );
			}

			return $ids;
		}

		public static function get_tweet_text( array $mod, $opt_pre = 'twitter', $md_pre = 'twitter' ) {

			$wpsso =& Wpsso::get_instance();

			$type  = empty( $wpsso->options[ $opt_pre . '_caption' ] ) ? 'title' : $wpsso->options[ $opt_pre . '_caption' ];

			$max_len = self::get_tweet_max_len( $opt_pre );

			$num_hashtags  = isset( $wpsso->options[ $opt_pre . '_caption_hashtags' ] ) ? $wpsso->options[ $opt_pre . '_caption_hashtags' ] : true;

			return $wpsso->page->get_caption( $mod, $md_pre . '_desc', $type, $max_len, $num_hashtags, $do_encode = false );
		}

		public static function get_tweet_max_len( $opt_pre = 'twitter', $num_urls = 1 ) {

			$wpsso =& Wpsso::get_instance();

			$site_len = 0;

			$short_len = 23 * $num_urls;	// X (Twitter) counts 23 characters for any url.

			if ( isset( $wpsso->options[ 'tc_site' ] ) && ! empty( $wpsso->options[ $opt_pre . '_via' ] ) ) {

				$tc_site = preg_replace( '/^@/', '', $wpsso->options[ 'tc_site' ] );

				$site_len = empty( $tc_site ) ? 0 : strlen( $tc_site ) + 6;
			}

			$max_len = isset( $wpsso->options[ $opt_pre . '_caption_max_len' ] ) ? $wpsso->options[ $opt_pre . '_caption_max_len' ] : 280;

			return $max_len - $site_len - $short_len;
		}

		private function remove_wp_breaks_callback( array $match ) {

			return preg_replace( '/(<(\/?p|br ?\/?)>|\n)/i', '', $match[ 1 ] );
		}
	}
}
