<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2018 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoRrssbSocial' ) ) {

	class WpssoRrssbSocial {

		private $p;
		private $share = array();
		private $post_buttons_disabled = array();	// cache for is_post_buttons_disabled()

		public static $sharing_css_name = '';
		public static $sharing_css_file = '';
		public static $sharing_css_url  = '';

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark( 'rrssb sharing action / filter setup' );
			}

			self::$sharing_css_name = 'rrssb-styles-id-' . get_current_blog_id() . '.min.css';
			self::$sharing_css_file = WPSSO_CACHEDIR . self::$sharing_css_name;
			self::$sharing_css_url  = WPSSO_CACHEURL . self::$sharing_css_name;

			$this->set_objects();

			add_action( 'wp_footer', array( $this, 'show_footer' ), WPSSORRSSB_FOOTER_PRIORITY );

			if ( $this->have_buttons_for_type( 'content' ) ) {
				$this->add_buttons_filter( 'the_content' );
			}

			if ( $this->have_buttons_for_type( 'excerpt' ) ) {
				$this->add_buttons_filter( 'get_the_excerpt' );
				$this->add_buttons_filter( 'the_excerpt' );
			}

			if ( is_admin() ) {
				if ( $this->have_buttons_for_type( 'admin_edit' ) ) {
					add_action( 'add_meta_boxes', array( $this, 'add_metabox_admin_edit' ) );
				}

			}

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark( 'rrssb sharing action / filter setup' );
			}
		}

		private function set_objects() {

			foreach ( $this->p->cf[ 'plugin' ][ 'wpssorrssb' ][ 'lib' ][ 'share' ] as $id => $name ) {

				$classname = WpssoRrssbConfig::load_lib( false, 'share/' . $id, 'wpssorrssbshare' . $id );

				if ( false !== $classname && class_exists( $classname ) ) {

					$this->share[$id] = new $classname( $this->p );

					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( $classname . ' class loaded' );
					}
				}
			}
		}

		public static function update_sharing_css( &$opts ) {

			$wpsso =& Wpsso::get_instance();

			if ( $wpsso->debug->enabled ) {
				$wpsso->debug->mark();
			}

			if ( empty( $opts[ 'buttons_use_social_style' ] ) ) {

				self::unlink_sharing_css();

				return;
			}

			$styles = apply_filters( $wpsso->lca . '_rrssb_styles', $wpsso->cf[ 'sharing' ][ 'rrssb_styles' ] );

			$sharing_css_data = '';

			foreach ( $styles as $id => $name ) {
				if ( isset( $opts[ 'buttons_css_' . $id ] ) ) {
					$sharing_css_data .= $opts[ 'buttons_css_' . $id ];
				}
			}

			$sharing_css_data = SucomUtil::minify_css( $sharing_css_data, $wpsso->lca );

			if ( $fh = @fopen( self::$sharing_css_file, 'wb' ) ) {

				if ( ( $written = fwrite( $fh, $sharing_css_data ) ) === false ) {

					if ( $wpsso->debug->enabled ) {
						$wpsso->debug->log( 'failed writing the css file ' . self::$sharing_css_file );
					}

					if ( is_admin() ) {
						$wpsso->notice->err( sprintf( __( 'Failed writing the css file %s.',
							'wpsso-rrssb' ), self::$sharing_css_file ) );
					}

				} elseif ( $wpsso->debug->enabled ) {

					$wpsso->debug->log( 'updated css file ' . self::$sharing_css_file . ' (' . $written . ' bytes written)' );

					if ( is_admin() ) {
						$wpsso->notice->upd( sprintf( __( 'Updated the <a href="%1$s">%2$s</a> stylesheet (%3$d bytes written).',
							'wpsso-rrssb' ), self::$sharing_css_url, self::$sharing_css_file, $written ), 
								true, 'updated_' . self::$sharing_css_file, true );	// allow dismiss
					}
				}

				fclose( $fh );

			} else {

				if ( ! is_writable( WPSSO_CACHEDIR ) ) {

					if ( $wpsso->debug->enabled ) {
						$wpsso->debug->log( 'cache folder ' . WPSSO_CACHEDIR . ' is not writable' );
					}

					if ( is_admin() ) {
						$wpsso->notice->err( sprintf( __( 'Cache folder %s is not writable.',
							'wpsso-rrssb' ), WPSSO_CACHEDIR ) );
					}
				}

				if ( $wpsso->debug->enabled ) {
					$wpsso->debug->log( 'failed to open the css file ' . self::$sharing_css_file . ' for writing' );
				}

				if ( is_admin() ) {
					$wpsso->notice->err( sprintf( __( 'Failed to open the css file %s for writing.',
						'wpsso-rrssb' ), self::$sharing_css_file ) );
				}
			}
		}

		public static function unlink_sharing_css() {

			$wpsso =& Wpsso::get_instance();

			if ( $wpsso->debug->enabled ) {
				$wpsso->debug->mark();
			}

			if ( file_exists( self::$sharing_css_file ) ) {

				if ( ! @unlink( self::$sharing_css_file ) ) {

					if ( is_admin() ) {
						$wpsso->notice->err( __( 'Error removing the minified stylesheet &mdash; does the web server have sufficient privileges?', 'wpsso-rrssb' ) );
					}
				}
			}
		}

		public function add_metabox_admin_edit() {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			/**
			 * Get the current object / post type.
			 */
			if ( ( $post_obj = SucomUtil::get_post_object() ) === false ) {

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'exiting early: invalid post object' );
				}

				return;
			}

			if ( ! empty( $this->p->options[ 'buttons_add_to_' . $post_obj->post_type ] ) ) {

				$metabox_id      = 'admin_edit';
				$metabox_title   = _x( 'Share Buttons', 'metabox title', 'wpsso-rrssb' );
				$metabox_screen  = $post_obj->post_type;
				$metabox_context = 'side';
				$metabox_prio    = 'high';
				$callback_args   = array(	// Second argument passed to the callback function / method.
					'__block_editor_compatible_meta_box' => true,
				);

				add_meta_box( '_' . $this->p->lca . '_rrssb_' . $metabox_id, $metabox_title,
					array( $this, 'show_metabox_admin_edit' ), $metabox_screen,
						$metabox_context, $metabox_prio, $callback_args );
			}
		}

		public function show_footer() {

			if ( $this->have_buttons_for_type( 'sidebar' ) ) {
				$this->show_sidebar();
			} elseif ( $this->p->debug->enabled ) {
				$this->p->debug->log( 'no buttons enabled for sidebar' );
			}
		}

		public function show_sidebar() {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			echo $this->get_buttons( '', 'sidebar', $use_post = false, '', array( 'container_each' => true ) );
		}

		public function show_metabox_admin_edit( $post_obj ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			echo '<table class="sucom-settings ' . $this->p->lca . ' post-side-metabox"><tr><td>';

			if ( get_post_status( $post_obj->ID ) === 'publish' || $post_obj->post_type === 'attachment' ) {

				$mod = $this->p->util->get_page_mod( $post_obj->ID );

				echo $this->get_buttons( $text = '', 'admin_edit', $mod );

			} else {
				echo '<p class="centered">' . __( 'This content must be published<br/>before it can be shared.', 'wpsso-rrssb' ) . '</p>';
			}

			echo '</td></tr></table>';
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

				$added = add_filter( $filter_name, array( $this, 'get_buttons_for_' . $filter_name ), WPSSORRSSB_SOCIAL_PRIORITY );

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'buttons filter ' . $filter_name . ' added (' . ( $added  ? 'true' : 'false' ) . ')' );
				}

			} elseif ( $this->p->debug->enabled ) {
				$this->p->debug->log( 'get_buttons_for_' . $filter_name . ' method is missing' );
			}

			return $added;
		}

		public function remove_buttons_filter( $filter_name = 'the_content' ) {

			$removed = false;

			if ( method_exists( $this, 'get_buttons_for_' . $filter_name ) ) {

				$removed = remove_filter( $filter_name, array( $this, 'get_buttons_for_' . $filter_name ), WPSSORRSSB_SOCIAL_PRIORITY );

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'buttons filter ' . $filter_name . ' removed (' . ( $removed  ? 'true' : 'false' ) . ')' );
				}
			}

			return $removed;
		}

		/**
		 * $mod = true | false | post_id | $mod array
		 */
		public function get_buttons( $text, $type = 'content', $mod = true, $location = '', $atts = array() ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark( 'getting buttons for ' . $type );	// Start timer.
			}

			$error_message = '';
			$append_error  = true;
			$is_admin      = is_admin();	// Call the function only once.
			$doing_ajax    = SucomUtil::get_const( 'DOING_AJAX' );

			if ( $doing_ajax ) {

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'doing_ajax is true' );
				}

			} elseif ( $is_admin ) {

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'is_admin is true' );
				}

				if ( strpos( $type, 'admin_' ) !== 0 ) {
					$error_message = $type . ' ignored in back-end';
				}

			} elseif ( SucomUtil::is_amp() ) {

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'is_amp is true' );
				}

				$error_message = 'buttons not allowed in amp endpoint';

			} elseif ( is_feed() ) {

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'is_feed is true' );
				}

				$error_message = 'buttons not allowed in rss feeds';

			} elseif ( ! is_singular() ) {

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'is_singular is false' );
				}

				if ( empty( $this->p->options[ 'buttons_on_index' ] ) ) {
					$error_message = 'buttons_on_index not enabled';
				}

			} elseif ( is_front_page() ) {

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'is_front_page is true' );
				}

				if ( empty( $this->p->options[ 'buttons_on_front' ] ) ) {
					$error_message = 'buttons_on_front not enabled';
				}

			} elseif ( is_singular() ) {

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'is_singular is true' );
				}

				if ( $this->is_post_buttons_disabled() ) {
					$error_message = 'post buttons are disabled';
				}
			}

			if ( empty( $error_message ) && ! $this->have_buttons_for_type( $type ) ) {
				$error_message = 'no sharing buttons enabled';
			}

			if ( ! empty( $error_message ) ) {

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( $type . ' filter skipped: ' . $error_message );
					$this->p->debug->mark( 'getting buttons for ' . $type );	// End timer.
				}

				if ( $append_error ) {
					return $text . "\n" . '<!-- ' . __METHOD__ . ' ' . $type . ' filter skipped: ' . $error_message . ' -->' . "\n";
				} else {
					return $text;
				}
			}

			/**
			 * The $mod array argument is preferred but not required.
			 * $mod = true | false | post_id | $mod array
			 */
			if ( ! is_array( $mod ) ) {

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'optional call to get_page_mod()' );
				}

				$mod = $this->p->util->get_page_mod( $mod );
			}

			$sharing_url = $this->p->util->get_sharing_url( $mod );

			$cache_md5_pre  = $this->p->lca . '_b_';
			$cache_exp_secs = $this->get_buttons_cache_exp();
			$cache_salt     = __METHOD__ . '(' . SucomUtil::get_mod_salt( $mod, $sharing_url ) . ')';
			$cache_id       = $cache_md5_pre . md5( $cache_salt );
			$cache_index    = $this->get_buttons_cache_index( $type );	// Returns salt with locale, mobile, wp_query, etc.
			$cache_array    = array();

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log( 'sharing url = ' . $sharing_url );
				$this->p->debug->log( 'cache expire = ' . $cache_exp_secs );
				$this->p->debug->log( 'cache salt = ' . $cache_salt );
				$this->p->debug->log( 'cache id = ' . $cache_id );
				$this->p->debug->log( 'cache index = ' . $cache_index );
			}

			if ( $cache_exp_secs > 0 ) {

				$cache_array = SucomUtil::get_transient_array( $cache_id );

				if ( isset( $cache_array[ $cache_index ] ) ) {	// can be an empty string

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

			} elseif ( $this->p->debug->enabled ) {
				$this->p->debug->log( $type . ' buttons array transient cache is disabled' );
			}

			if ( empty( $location ) ) {
				$location = empty( $this->p->options[ 'buttons_pos_' . $type] ) ? 
					'bottom' : $this->p->options[ 'buttons_pos_' . $type];
			} 

			if ( ! isset( $cache_array[ $cache_index ] ) ) {

				/**
				 * Sort enabled sharing buttons by their preferred order.
				 */
				$sorted_ids = array();

				foreach ( $this->p->cf[ 'opt' ][ 'cm_prefix' ] as $id => $opt_pre ) {
					if ( ! empty( $this->p->options[$opt_pre . '_on_' . $type] ) ) {
						$sorted_ids[ zeroise( $this->p->options[ $opt_pre . '_order' ], 3 ) . '-' . $id ] = $id;
					}
				}

				ksort( $sorted_ids );

				$atts[ 'use_post' ] = $mod[ 'use_post' ];
				$atts[ 'css_id' ]   = $css_type_name = 'rrssb-' . $type;

				/**
				 * Returns html or an empty string.
				 */
				$cache_array[ $cache_index ] = $this->get_html( $sorted_ids, $atts, $mod );

				if ( ! empty( $cache_array[ $cache_index ] ) ) {

					$cache_array[ $cache_index ] = '
<div class="' . $this->p->lca . '-rrssb' .
	( $mod[ 'use_post' ] ? ' ' . $this->p->lca . '-' . $css_type_name . '"' : '" id="' . $this->p->lca . '-' . $css_type_name . '"' ) . '>' . "\n" . 
$cache_array[ $cache_index ] . 
'</div><!-- .' . $this->p->lca . '-rrssb ' . ( $mod[ 'use_post' ] ? '.' : '#' ) . $this->p->lca . '-' . $css_type_name . ' -->' .
'<!-- generated on ' . date( 'c' ) . ' -->' . "\n\n";

					$cache_array[ $cache_index ] = apply_filters( $this->p->lca . '_rrssb_buttons_html',
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

			if ( $doing_ajax ) {
				$text .= '
					<script type="text/javascript">
						if ( ! window.rrssb_done ) {
							window.rrssb_done = true; 
							window.rrssbResize();
							window.rrssbInit();
						}
					</script>' . "\n";
			}

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark( 'getting buttons for ' . $type );	// End timer.
			}

			return $text;
		}

		public function get_buttons_cache_exp() {

			static $cache_exp_secs = null;	// filter the cache expiration value only once

			if ( ! isset( $cache_exp_secs ) ) {
				$cache_md5_pre    = $this->p->lca . '_b_';
				$cache_exp_filter = $this->p->cf[ 'wp' ][ 'transient' ][$cache_md5_pre][ 'filter' ];
				$cache_opt_key    = $this->p->cf[ 'wp' ][ 'transient' ][$cache_md5_pre][ 'opt_key' ];
				$cache_exp_secs   = isset( $this->p->options[$cache_opt_key] ) ? $this->p->options[$cache_opt_key] : WEEK_IN_SECONDS;
				$cache_exp_secs   = (int) apply_filters( $cache_exp_filter, $cache_exp_secs );
			}

			return $cache_exp_secs;
		}

		public function get_buttons_cache_index( $type, $atts = false, $share_ids = false ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$cache_index = 'locale:' . SucomUtil::get_locale( 'current' );

			$cache_index .= '_type:' . ( empty( $type ) ? 'none' : $type );

			$cache_index .= '_https:' . ( SucomUtil::is_https() ? 'true' : 'false' );

			$cache_index .= $this->p->avail[ '*' ][ 'vary_ua' ] ? '_mobile:' . ( SucomUtil::is_mobile() ? 'true' : 'false' ) : '';

			$cache_index .= false !== $atts ? '_atts:' . http_build_query( $atts, '', '_' ) : '';

			$cache_index .= false !== $share_ids ? '_share_ids:' . http_build_query( $share_ids, '', ',' ) : '';

			$cache_index = apply_filters( $this->p->lca . '_rrssb_buttons_cache_index', $cache_index );

			return $cache_index;
		}

		public function get_buttons_for_the_content( $text ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			return $this->get_buttons( $text, 'content' );
		}

		public function get_buttons_for_the_excerpt( $text ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$css_type_name = 'rrssb-excerpt';

			$text = preg_replace_callback( '/(<!-- ' . $this->p->lca . ' ' . $css_type_name . ' begin -->' . 
				'.*<!-- ' . $this->p->lca . ' ' . $css_type_name . ' end -->)(<\/p>)?/Usi', 
					array( __CLASS__, 'remove_paragraph_tags' ), $text );

			return $text;
		}

		public function get_buttons_for_get_the_excerpt( $text ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			return $this->get_buttons( $text, 'excerpt' );
		}

		/**
		 * get_html() can be called by a widget, shortcode, function, filter hook, etc.
		 */
		public function get_html( array $share_ids, array $atts, $mod = false ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$atts[ 'use_post' ] = isset( $atts[ 'use_post' ] ) ? $atts[ 'use_post' ] : true;	// Maintain backwards compat.
			$atts[ 'add_page' ] = isset( $atts[ 'add_page' ] ) ? $atts[ 'add_page' ] : true;	// Used by get_sharing_url().

			/**
			 * The $mod array argument is preferred but not required.
			 * $mod = true | false | post_id | $mod array
			 */
			if ( ! is_array( $mod ) ) {

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'optional call to get_page_mod()' );
				}

				$mod = $this->p->util->get_page_mod( $atts[ 'use_post' ] );
			}

			$buttons_html  = '';
			$buttons_begin = '<ul class="rrssb-buttons ' . SucomUtil::get_locale( $mod ) . ' clearfix">' . "\n";
			$buttons_end   = '</ul><!-- .rrssb-buttons.' . SucomUtil::get_locale( $mod ) . '.clearfix -->' . "\n";

			$saved_atts = $atts;

			foreach ( $share_ids as $id ) {

				if ( isset( $this->share[$id] ) ) {

					if ( method_exists( $this->share[$id], 'get_html' ) ) {

						if ( $this->allow_for_platform( $id ) ) {

							$atts[ 'src_id' ] = SucomUtil::get_atts_src_id( $atts, $id );	// Uses 'css_id' and 'use_post'.

							if ( empty( $atts[ 'url' ] ) ) {
								$atts[ 'url' ] = $this->p->util->get_sharing_url( $mod,
									$atts[ 'add_page' ], $atts[ 'src_id' ] );
							} else {
								$atts[ 'url' ] = apply_filters( $this->p->lca . '_sharing_url',
									$atts[ 'url' ], $mod, $atts[ 'add_page' ], $atts[ 'src_id' ] );
							}

							/**
							 * Filter to add custom tracking arguments.
							 */
							$atts[ 'url' ] = apply_filters( $this->p->lca . '_rrssb_buttons_shared_url',
								$atts[ 'url' ], $mod, $id );

							$force_prot = apply_filters( $this->p->lca . '_rrssb_buttons_force_prot',
								$this->p->options[ 'buttons_force_prot' ], $mod, $id, $atts[ 'url' ] );

							if ( ! empty( $force_prot ) && $force_prot !== 'none' ) {
								$atts[ 'url' ] = preg_replace( '/^.*:\/\//', $force_prot . '://', $atts[ 'url' ] );
							}

							$buttons_part = $this->share[$id]->get_html( $atts, $this->p->options, $mod ) . "\n";

							$atts = $saved_atts;	// Restore the common $atts array.

							if ( false !== strpos( $buttons_part, '<li' ) ) {
								if ( empty( $atts[ 'container_each' ] ) ) {
									$buttons_html .= $buttons_part;
								} else {
									$buttons_html .= '<!-- adding button as individual containers -->' . "\n" . 
										$buttons_begin . $buttons_part . $buttons_end;
								}
							}

						} elseif ( $this->p->debug->enabled ) {
							$this->p->debug->log( $id . ' not allowed for platform' );
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

					if ( $this->allow_for_platform( $id ) ) {	// Check if allowed on platform.

						return $local_cache[ $type ] = true;	// Stop here.
					}
				}
			}

			return $local_cache[ $type ] = false;
		}

		public function allow_for_platform( $id ) {

			/**
			 * Always allow if the content does not vary by user agent.
			 */
			if ( empty( $this->p->avail[ '*' ][ 'vary_ua' ] ) ) {
				return true;
			}

			$opt_pre = isset( $this->p->cf[ 'opt' ][ 'cm_prefix' ][ $id ] ) ?
				$this->p->cf[ 'opt' ][ 'cm_prefix' ][ $id ] : $id;

			if ( isset( $this->p->options[ $opt_pre . '_platform' ] ) ) {

				switch( $this->p->options[ $opt_pre . '_platform' ] ) {

					case 'any':

						return true;

					case 'desktop':

						return SucomUtil::is_desktop();

					case 'mobile':

						return SucomUtil::is_mobile();

					default:

						return true;
				}
			}

			return true;
		}

		public function is_post_buttons_disabled() {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$ret = false;

			if ( ( $post_obj = SucomUtil::get_post_object() ) === false ) {
				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'exiting early: invalid post object' );
				}
				return $ret;
			} else {
				$post_id = empty( $post_obj->ID ) ? 0 : $post_obj->ID;
			}

			if ( empty( $post_id ) ) {
				return $ret;
			}

			if ( isset( $this->post_buttons_disabled[$post_id] ) ) {
				return $this->post_buttons_disabled[$post_id];
			}

			if ( $this->p->m[ 'util' ][ 'post' ]->get_options( $post_id, 'buttons_disabled' ) ) {	// Returns null if an index key is not found.

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

			return $this->post_buttons_disabled[$post_id] = apply_filters( $this->p->lca . '_post_buttons_disabled', $ret, $post_id );
		}

		public function remove_paragraph_tags( $match = array() ) {

			if ( empty( $match ) || ! is_array( $match ) ) {
				return;
			}

			$text = empty( $match[1] ) ? '' : $match[1];
			$suff = empty( $match[2] ) ? '' : $match[2];
			$ret  = preg_replace( '/(<\/*[pP]>|\n)/', '', $text );

			return $suff . $ret; 
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

			if ( $wpsso->debug->enabled ) {
				$wpsso->debug->mark();
			}

			if ( ! isset( $atts[ 'tweet' ] ) ) {	// Just in case.

				$atts[ 'use_post' ]     = isset( $atts[ 'use_post' ] ) ? $atts[ 'use_post' ] : true;
				$atts[ 'add_page' ]     = isset( $atts[ 'add_page' ] ) ? $atts[ 'add_page' ] : true;	// Used by get_sharing_url().
				$atts[ 'add_hashtags' ] = isset( $atts[ 'add_hashtags' ] ) ? $atts[ 'add_hashtags' ] : true;

				$caption_type    = empty( $wpsso->options[ $opt_pre . '_caption' ] ) ? 'title' : $wpsso->options[ $opt_pre . '_caption' ];
				$caption_max_len = self::get_tweet_max_len( $opt_pre );

				$atts[ 'tweet' ] = $wpsso->page->get_caption( $caption_type, $caption_max_len, $mod, $read_cache = true,
					$atts[ 'add_hashtags' ], $do_encode = false, $md_key = $md_pre . '_desc' );
			}

			return $atts[ 'tweet' ];
		}

		/**
		 * $opt_pre can be twitter, buffer, etc.
		 */
		public static function get_tweet_max_len( $opt_pre = 'twitter', $num_urls = 1 ) {

			$wpsso =& Wpsso::get_instance();

			if ( $wpsso->debug->enabled ) {
				$wpsso->debug->mark();
			}

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
