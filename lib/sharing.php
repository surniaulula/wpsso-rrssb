<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2018 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoRrssbSharing' ) ) {

	class WpssoRrssbSharing {

		protected $p;
		protected $website = array();
		protected $buttons_for_type = array();		// cache for have_buttons_for_type()
		protected $post_buttons_disabled = array();	// cache for is_post_buttons_disabled()

		public static $sharing_css_name = '';
		public static $sharing_css_file = '';
		public static $sharing_css_url = '';

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark( 'rrssb sharing action / filter setup' );
			}

			self::$sharing_css_name = 'rrssb-styles-id-' . get_current_blog_id() . '.min.css';
			self::$sharing_css_file = WPSSO_CACHEDIR . self::$sharing_css_name;
			self::$sharing_css_url  = WPSSO_CACHEURL . self::$sharing_css_name;

			$this->set_objects();

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_rrssb_ext' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_rrssb_ext' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_styles' ) );
			add_action( 'wp_footer', array( $this, 'show_footer' ), WPSSORRSSB_FOOTER_PRIORITY );

			if ( $this->have_buttons_for_type( 'content' ) ) {
				$this->add_buttons_filter( 'the_content' );
			}

			if ( $this->have_buttons_for_type( 'excerpt' ) ) {
				$this->add_buttons_filter( 'get_the_excerpt' );
				$this->add_buttons_filter( 'the_excerpt' );
			}

			$this->p->util->add_plugin_filters( $this, array( 
				'get_defaults'      => 1,
				'get_md_defaults'   => 1,
			) );

			$this->p->util->add_plugin_actions( $this, array( 
				'pre_apply_filters_text'   => 1,
				'after_apply_filters_text' => 1,
			) );

			if ( is_admin() ) {

				if ( $this->have_buttons_for_type( 'admin_edit' ) ) {
					add_action( 'add_meta_boxes', array( $this, 'add_post_buttons_metabox' ) );
				}

				$this->p->util->add_plugin_actions( $this, array(
					'load_setting_page_reload_default_sharing_rrssb_buttons_html' => 4,
					'load_setting_page_reload_default_sharing_rrssb_styles'       => 4,
				) );

				$this->p->util->add_plugin_filters( $this, array( 
					'save_options'              => 3,
					'option_type'               => 2,
					'post_custom_meta_tabs'     => 3,
					'post_cache_transient_keys' => 4,
					'messages_info'             => 2,
					'messages_tooltip'          => 2,
					'messages_tooltip_plugin'   => 2,
				) );

				$this->p->util->add_plugin_filters( $this, array( 
					'status_gpl_features' => 3,
				), 10, 'wpssorrssb' );
			}

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark( 'rrssb sharing action / filter setup' );
			}
		}

		private function set_objects() {

			foreach ( $this->p->cf['plugin']['wpssorrssb']['lib']['website'] as $id => $name ) {

				$classname = WpssoRrssbConfig::load_lib( false, 'website/' . $id, 'wpssorrssbwebsite' . $id );

				if ( $classname !== false && class_exists( $classname ) ) {

					$this->website[$id] = new $classname( $this->p );

					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( $classname . ' class loaded' );
					}
				}
			}
		}

		public function filter_get_defaults( $def_opts ) {

			/**
			 * Add options using a key prefix array and post type names.
			 */
			$def_opts     = $this->p->util->add_ptns_to_opts( $def_opts, 'buttons_add_to', 1 );
			$rel_url_path = parse_url( WPSSORRSSB_URLPATH, PHP_URL_PATH );	// Returns a relative URL.
			$styles       = apply_filters( $this->p->lca . '_rrssb_styles', $this->p->cf['sharing']['rrssb_styles'] );

			foreach ( $styles as $id => $name ) {

				$buttons_css_file = WPSSORRSSB_PLUGINDIR . 'css/' . $id . '.css';

				/**
				 * CSS files are only loaded once (when variable is empty) into defaults to minimize disk I/O.
				 */
				if ( empty( $def_opts['buttons_css_' . $id] ) ) {

					if ( ! file_exists( $buttons_css_file ) ) {

						continue;

					} elseif ( ! $fh = @fopen( $buttons_css_file, 'rb' ) ) {

						if ( $this->p->debug->enabled ) {
							$this->p->debug->log( 'failed to open the css file ' . self::$buttons_css_file . ' for reading' );
						}

						if ( is_admin() ) {
							$this->p->notice->err( sprintf( __( 'Failed to open the css file %s for reading.',
								'wpsso-rrssb' ), $buttons_css_file ) );
						}

					} else {

						$buttons_css_data = fread( $fh, filesize( $buttons_css_file ) );

						fclose( $fh );

						if ( $this->p->debug->enabled ) {
							$this->p->debug->log( 'read css file ' . $buttons_css_file );
						}

						foreach ( array( 'plugin_url_path' => $rel_url_path ) as $macro => $value ) {
							$buttons_css_data = preg_replace( '/%%' . $macro . '%%/', $value, $buttons_css_data );
						}

						$def_opts['buttons_css_' . $id] = $buttons_css_data;
					}
				}
			}

			return $def_opts;
		}

		public function filter_get_md_defaults( $md_defs ) {

			return array_merge( $md_defs, array(
				'email_title'      => '',	// Email Subject
				'email_desc'       => '',	// Email Message
				'twitter_desc'     => '',	// Tweet Text
				'pin_desc'         => '',	// Pinterest Caption
				'linkedin_title'   => '',	// LinkedIn Title
				'linkedin_desc'    => '',	// LinkedIn Caption
				'reddit_title'     => '',	// Reddit Title
				'reddit_desc'      => '',	// Reddit Caption
				'tumblr_title'     => '',	// Tumblr Title
				'tumblr_desc'      => '',	// Tumblr Caption
				'buttons_disabled' => 0,	// Disable Sharing Buttons
			) );
		}

		public function filter_save_options( $opts, $options_name, $network ) {

			/**
			 * Update the combined and minified social stylesheet.
			 */
			if ( false === $network ) {
				$this->update_sharing_css( $opts );
			}

			return $opts;
		}

		public function filter_option_type( $type, $base_key ) {

			if ( ! empty( $type ) ) {
				return $type;
			}

			switch ( $base_key ) {

				/**
				 * Integer options that must be 1 or more (not zero).
				 */
				case ( preg_match( '/_order$/', $base_key ) ? true : false ):

					return 'pos_int';

					break;

				/**
				 * Text strings that can be blank.
				 */
				case 'buttons_force_prot':
				case ( preg_match( '/_(desc|title)$/', $base_key ) ? true : false ):

					return 'ok_blank';

					break;
			}

			return $type;
		}

		public function filter_post_custom_meta_tabs( $tabs, $mod, $metabox_id ) {

			if ( $metabox_id === $this->p->cf['meta']['id'] ) {
				SucomUtil::add_after_key( $tabs, 'media', 'buttons',
					_x( 'Share Buttons', 'metabox tab', 'wpsso-rrssb' ) );
			}

			return $tabs;
		}

		public function filter_post_cache_transient_keys( $transient_keys, $mod, $sharing_url, $mod_salt ) {

			$cache_md5_pre = $this->p->lca . '_b_';
			$classname_pre = 'WpssoRrssb';

			$transient_keys[] = array(
				'id'   => $cache_md5_pre . md5( $classname_pre . 'Sharing::get_buttons(' . $mod_salt . ')' ),
				'pre'   => $cache_md5_pre,
				'salt' => $classname_pre . 'Sharing::get_buttons(' . $mod_salt . ')',
			);

			$transient_keys[] = array(
				'id'   => $cache_md5_pre . md5( $classname_pre . 'ShortcodeSharing::do_shortcode(' . $mod_salt . ')' ),
				'pre'  => $cache_md5_pre,
				'salt' => $classname_pre . 'ShortcodeSharing::do_shortcode(' . $mod_salt . ')',
			);

			$transient_keys[] = array(
				'id'   => $cache_md5_pre . md5( $classname_pre . 'WidgetSharing::widget(' . $mod_salt . ')' ),
				'pre'  => $cache_md5_pre,
				'salt' => $classname_pre . 'WidgetSharing::widget(' . $mod_salt . ')',
			);

			return $transient_keys;
		}

		public function filter_status_gpl_features( $features, $ext, $info ) {

			if ( ! empty( $info['lib']['submenu']['rrssb-buttons'] ) ) {
				$features['(sharing) Sharing Buttons'] = array(
					'classname' => $ext . 'Sharing',
				);
			}

			if ( ! empty( $info['lib']['submenu']['rrssb-styles'] ) ) {
				$features['(sharing) Sharing Stylesheet'] = array(
					'status' => empty( $this->p->options['buttons_use_social_style'] ) ? 'off' : 'on',
				);
			}

			if ( ! empty( $info['lib']['shortcode']['sharing'] ) ) {
				$features['(sharing) Sharing Shortcode'] = array(
					'classname' => $ext . 'ShortcodeSharing',
				);
			}

			if ( ! empty( $info['lib']['widget']['sharing'] ) ) {
				$features['(sharing) Sharing Widget'] = array(
					'classname' => $ext . 'WidgetSharing',
				);
			}

			return $features;
		}

		public function action_load_setting_page_reload_default_sharing_rrssb_buttons_html( $pagehook, $menu_id, $menu_name, $menu_lib ) {

			$opts     =& $this->p->options;
			$def_opts = $this->p->opt->get_defaults();

			foreach ( $this->p->cf['opt']['cm_prefix'] as $id => $opt_pre ) {
				if ( isset( $this->p->options[$opt_pre . '_rrssb_html'] ) && isset( $def_opts[$opt_pre . '_rrssb_html'] ) ) {
					$this->p->options[$opt_pre . '_rrssb_html'] = $def_opts[$opt_pre . '_rrssb_html'];
				}
			}

			$this->p->opt->save_options( WPSSO_OPTIONS_NAME, $this->p->options, $network = false );

			$this->p->notice->upd( __( 'The default HTML for all sharing buttons has been reloaded and saved.', 'wpsso-rrssb' ) );
		}

		public function action_load_setting_page_reload_default_sharing_rrssb_styles( $pagehook, $menu_id, $menu_name, $menu_lib ) {

			$def_opts = $this->p->opt->get_defaults();
			$styles   = apply_filters( $this->p->lca . '_rrssb_styles', $this->p->cf['sharing']['rrssb_styles'] );

			foreach ( $styles as $id => $name ) {
				if ( isset( $this->p->options['buttons_css_' . $id] ) && isset( $def_opts['buttons_css_' . $id] ) ) {
					$this->p->options['buttons_css_' . $id] = $def_opts['buttons_css_' . $id];
				}
			}

			$this->update_sharing_css( $this->p->options );

			$this->p->opt->save_options( WPSSO_OPTIONS_NAME, $this->p->options, $network = false );

			$this->p->notice->upd( __( 'The default sharing styles have been reloaded and saved.', 'wpsso-rrssb' ) );
		}

		public function wp_enqueue_styles() {

			if ( ! empty( $this->p->options['buttons_use_social_style'] ) ) {

				if ( ! file_exists( self::$sharing_css_file ) ) {

					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( 'updating ' . self::$sharing_css_file );
					}

					$this->update_sharing_css( $this->p->options );
				}

				if ( ! empty( $this->p->options['buttons_enqueue_social_style'] ) ) {

					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( 'wp_enqueue_style = ' . $this->p->lca . '_rrssb_sharing_css' );
					}

					wp_enqueue_style( $this->p->lca . '_rrssb_sharing_css', self::$sharing_css_url, 
						false, $this->p->cf['plugin'][$this->p->lca]['version'] );

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

		public function update_sharing_css( &$opts ) {

			if ( empty( $opts['buttons_use_social_style'] ) ) {

				$this->unlink_sharing_css();

				return;
			}

			$styles = apply_filters( $this->p->lca . '_rrssb_styles', $this->p->cf['sharing']['rrssb_styles'] );

			$sharing_css_data = '';

			foreach ( $styles as $id => $name ) {
				if ( isset( $opts['buttons_css_' . $id] ) ) {
					$sharing_css_data .= $opts['buttons_css_' . $id];
				}
			}

			$sharing_css_data = SucomUtil::minify_css( $sharing_css_data, $this->p->lca );

			if ( $fh = @fopen( self::$sharing_css_file, 'wb' ) ) {

				if ( ( $written = fwrite( $fh, $sharing_css_data ) ) === false ) {

					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( 'failed writing the css file ' . self::$sharing_css_file );
					}

					if ( is_admin() ) {
						$this->p->notice->err( sprintf( __( 'Failed writing the css file %s.',
							'wpsso-rrssb' ), self::$sharing_css_file ) );
					}

				} elseif ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'updated css file ' . self::$sharing_css_file . ' (' . $written . ' bytes written)' );

					if ( is_admin() ) {
						$this->p->notice->upd( sprintf( __( 'Updated the <a href="%1$s">%2$s</a> stylesheet (%3$d bytes written).',
							'wpsso-rrssb' ), self::$sharing_css_url, self::$sharing_css_file, $written ), 
								true, 'updated_' . self::$sharing_css_file, true );	// allow dismiss
					}
				}

				fclose( $fh );

			} else {

				if ( ! is_writable( WPSSO_CACHEDIR ) ) {

					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( 'cache folder ' . WPSSO_CACHEDIR . ' is not writable' );
					}

					if ( is_admin() ) {
						$this->p->notice->err( sprintf( __( 'Cache folder %s is not writable.',
							'wpsso-rrssb' ), WPSSO_CACHEDIR ) );
					}
				}

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'failed to open the css file ' . self::$sharing_css_file . ' for writing' );
				}

				if ( is_admin() ) {
					$this->p->notice->err( sprintf( __( 'Failed to open the css file %s for writing.',
						'wpsso-rrssb' ), self::$sharing_css_file ) );
				}
			}
		}

		public function unlink_sharing_css() {

			if ( file_exists( self::$sharing_css_file ) ) {

				if ( ! @unlink( self::$sharing_css_file ) ) {

					if ( is_admin() ) {
						$this->p->notice->err( __( 'Error removing the minified stylesheet &mdash; does the web server have sufficient privileges?', 'wpsso-rrssb' ) );
					}
				}
			}
		}

		public function add_post_buttons_metabox() {

			if ( ! is_admin() ) {
				return;
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

			if ( ! empty( $this->p->options['buttons_add_to_' . $post_obj->post_type] ) ) {
				add_meta_box( '_' . $this->p->lca . '_rrssb_share', 
					_x( 'Share Buttons', 'metabox title', 'wpsso-rrssb' ),
						array( $this, 'show_admin_sharing' ), $post_obj->post_type, 'side', 'high' );
			}
		}

		public function action_pre_apply_filters_text( $filter_name ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log_args( array( 
					'filter_name' => $filter_name,
				) );
			}

			$this->remove_buttons_filter( $filter_name );
		}

		public function action_after_apply_filters_text( $filter_name ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log_args( array( 
					'filter_name' => $filter_name,
				) );
			}

			$this->add_buttons_filter( $filter_name );
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

			echo $this->get_buttons( '', 'sidebar', false, '', array( 'container_each' => true ) );	// $use_post is false.
		}

		public function show_admin_sharing( $post_obj ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$sharing_css_data = $this->p->options['buttons_css_rrssb-admin_edit'];
			$sharing_css_data = SucomUtil::minify_css( $sharing_css_data, $this->p->lca );

			echo '<style type="text/css">' . $sharing_css_data . '</style>', "\n";
			echo '<table class="sucom-settings ' . $this->p->lca . ' post-side-metabox"><tr><td>';

			if ( get_post_status( $post_obj->ID ) === 'publish' || $post_obj->post_type === 'attachment' ) {

				echo $this->get_buttons( '', 'admin_edit' );

			} else {
				echo '<p class="centered">' . __( 'This content must be published<br/>before it can be shared.',
					'wpsso-rrssb' ) . '</p>';
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

			} elseif ( method_exists( $this, 'get_buttons_' . $filter_name ) ) {

				$added = add_filter( $filter_name, array( $this, 'get_buttons_' . $filter_name ), WPSSORRSSB_SOCIAL_PRIORITY );

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'buttons filter ' . $filter_name . ' added (' . ( $added  ? 'true' : 'false' ) . ')' );
				}

			} elseif ( $this->p->debug->enabled ) {
				$this->p->debug->log( 'get_buttons_' . $filter_name . ' method is missing' );
			}

			return $added;
		}

		public function remove_buttons_filter( $filter_name = 'the_content' ) {

			$removed = false;

			if ( method_exists( $this, 'get_buttons_' . $filter_name ) ) {

				$removed = remove_filter( $filter_name, array( $this, 'get_buttons_' . $filter_name ), WPSSORRSSB_SOCIAL_PRIORITY );

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'buttons filter ' . $filter_name . ' removed (' . ( $removed  ? 'true' : 'false' ) . ')' );
				}
			}

			return $removed;
		}

		public function get_buttons_the_excerpt( $text ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$css_type_name = 'rrssb-excerpt';

			$text = preg_replace_callback( '/(<!-- ' . $this->p->lca . ' ' . $css_type_name . ' begin -->' . 
				'.*<!-- ' . $this->p->lca . ' ' . $css_type_name . ' end -->)(<\/p>)?/Usi', 
					array( __CLASS__, 'remove_paragraph_tags' ), $text );

			return $text;
		}

		public function get_buttons_get_the_excerpt( $text ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			return $this->get_buttons( $text, 'excerpt' );
		}

		public function get_buttons_the_content( $text ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			return $this->get_buttons( $text, 'content' );
		}

		/**
		 * $mod = true | false | post_id | $mod array
		 */
		public function get_buttons( $text, $type = 'content', $mod = true, $location = '', $atts = array() ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark( 'getting buttons for ' . $type );	// start timer
			}

			$error_message = '';
			$append_error  = true;
			$doing_dev     = SucomUtil::get_const( 'WPSSO_DEV' );
			$doing_ajax    = SucomUtil::get_const( 'DOING_AJAX' );

			if ( $doing_ajax ) {

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'doing_ajax is true' );
				}

			} elseif ( is_admin() ) {

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

				if ( empty( $this->p->options['buttons_on_index'] ) ) {
					$error_message = 'buttons_on_index not enabled';
				}

			} elseif ( is_front_page() ) {

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'is_front_page is true' );
				}

				if ( empty( $this->p->options['buttons_on_front'] ) ) {
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
					$this->p->debug->mark( 'getting buttons for ' . $type );	// end timer
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

				$cache_array = get_transient( $cache_id );

				if ( isset( $cache_array[$cache_index] ) ) {	// can be an empty string

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
				$location = empty( $this->p->options['buttons_pos_' . $type] ) ? 
					'bottom' : $this->p->options['buttons_pos_' . $type];
			} 

			if ( ! isset( $cache_array[$cache_index] ) ) {

				// sort enabled sharing buttons by their preferred order
				$sorted_ids = array();

				foreach ( $this->p->cf['opt']['cm_prefix'] as $id => $opt_pre ) {
					if ( ! empty( $this->p->options[$opt_pre . '_on_' . $type] ) ) {
						$sorted_ids[ zeroise( $this->p->options[$opt_pre . '_order'], 3 ) . '-' . $id ] = $id;
					}
				}

				ksort( $sorted_ids );

				$atts['use_post'] = $mod['use_post'];
				$atts['css_id']   = $css_type_name = 'rrssb-' . $type;

				/**
				 * Returns html or an empty string.
				 */
				$cache_array[$cache_index] = $this->get_html( $sorted_ids, $atts, $mod );

				if ( ! empty( $cache_array[$cache_index] ) ) {

					$cache_array[$cache_index] = '
<!-- ' . $this->p->lca . ' ' . $css_type_name . ' begin -->
<!-- generated on ' . date( 'c' ) . ' -->
<div class="' . $this->p->lca . '-rrssb' .
	( $mod['use_post'] ? ' ' . $this->p->lca . '-' . $css_type_name . '"' : '" id="' . $this->p->lca . '-' . $css_type_name . '"' ) . '>' . "\n" . 
$cache_array[$cache_index] . 
'</div><!-- .' . $this->p->lca . '-rrssb ' . ( $mod['use_post'] ? '.' : '#' ) . $this->p->lca . '-' . $css_type_name . ' -->
<!-- ' . $this->p->lca . ' ' . $css_type_name . ' end -->' . "\n\n";

					$cache_array[$cache_index] = apply_filters( $this->p->lca . '_rrssb_buttons_html',
						$cache_array[$cache_index], $type, $mod, $location, $atts );
				}

				if ( $cache_exp_secs > 0 ) {

					// update the cached array and maintain the existing transient expiration time
					$expires_in_secs = SucomUtil::update_transient_array( $cache_id, $cache_array, $cache_exp_secs );

					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( $type . ' buttons html saved to transient cache (expires in ' . $expires_in_secs . ' secs)' );
					}
				}
			}

			switch ( $location ) {

				case 'top': 

					$text = $cache_array[$cache_index] . $text; 

					break;

				case 'bottom': 

					$text = $text . $cache_array[$cache_index]; 

					break;

				case 'both': 

					$text = $cache_array[$cache_index] . $text . $cache_array[$cache_index]; 

					break;
			}

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark( 'getting buttons for ' . $type );	// end timer
			}

			return $text;
		}

		public function get_buttons_cache_exp() {

			static $cache_exp_secs = null;	// filter the cache expiration value only once

			if ( ! isset( $cache_exp_secs ) ) {
				$cache_md5_pre    = $this->p->lca . '_b_';
				$cache_exp_filter = $this->p->cf['wp']['transient'][$cache_md5_pre]['filter'];
				$cache_opt_key    = $this->p->cf['wp']['transient'][$cache_md5_pre]['opt_key'];
				$cache_exp_secs   = isset( $this->p->options[$cache_opt_key] ) ? $this->p->options[$cache_opt_key] : WEEK_IN_SECONDS;
				$cache_exp_secs   = (int) apply_filters( $cache_exp_filter, $cache_exp_secs );
			}

			return $cache_exp_secs;
		}

		public function get_buttons_cache_index( $type, $atts = false, $ids = false ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$cache_index = 'locale:' . SucomUtil::get_locale( 'current' );

			$cache_index .= '_type:' . ( empty( $type ) ? 'none' : $type );

			$cache_index .= '_https:' . ( SucomUtil::is_https() ? 'true' : 'false' );

			$cache_index .= $this->p->avail['*']['vary_ua'] ? '_mobile:' . ( SucomUtil::is_mobile() ? 'true' : 'false' ) : '';

			$cache_index .= $atts !== false ? '_atts:' . http_build_query( $atts, '', '_' ) : '';

			$cache_index .= $ids !== false ? '_ids:' . http_build_query( $ids, '', '_' ) : '';

			$cache_index = SucomUtil::get_query_salt( $cache_index );	// Add $wp_query args.

			$cache_index = apply_filters( $this->p->lca . '_rrssb_buttons_cache_index', $cache_index );

			return $cache_index;
		}

		/**
		 * get_html() can be called by a widget, shortcode, function, filter hook, etc.
		 */
		public function get_html( array $ids, array $atts, $mod = false ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$atts['use_post'] = isset( $atts['use_post'] ) ? $atts['use_post'] : true;	// maintain backwards compat
			$atts['add_page'] = isset( $atts['add_page'] ) ? $atts['add_page'] : true;	// used by get_sharing_url()

			/**
			 * The $mod array argument is preferred but not required.
			 * $mod = true | false | post_id | $mod array
			 */
			if ( ! is_array( $mod ) ) {
				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'optional call to get_page_mod()' );
				}
				$mod = $this->p->util->get_page_mod( $atts['use_post'] );
			}

			$buttons_html = '';
			$buttons_begin = '<ul class="rrssb-buttons ' . SucomUtil::get_locale( $mod ) . ' clearfix">' . "\n";
			$buttons_end = '</ul><!-- .rrssb-buttons.' . SucomUtil::get_locale( $mod ) . '.clearfix -->' . "\n";

			$saved_atts = $atts;

			foreach ( $ids as $id ) {

				if ( isset( $this->website[$id] ) ) {

					if ( method_exists( $this->website[$id], 'get_html' ) ) {

						if ( $this->allow_for_platform( $id ) ) {

							$atts['src_id'] = SucomUtil::get_atts_src_id( $atts, $id );	// uses 'css_id' and 'use_post'

							if ( empty( $atts['url'] ) ) {
								$atts['url'] = $this->p->util->get_sharing_url( $mod,
									$atts['add_page'], $atts['src_id'] );
							} else {
								$atts['url'] = apply_filters( $this->p->lca . '_sharing_url',
									$atts['url'], $mod, $atts['add_page'], $atts['src_id'] );
							}

							// filter to add custom tracking arguments
							$atts['url'] = apply_filters( $this->p->lca . '_rrssb_buttons_shared_url',
								$atts['url'], $mod, $id );

							$force_prot = apply_filters( $this->p->lca . '_rrssb_buttons_force_prot',
								$this->p->options['buttons_force_prot'], $mod, $id, $atts['url'] );

							if ( ! empty( $force_prot ) && $force_prot !== 'none' ) {
								$atts['url'] = preg_replace( '/^.*:\/\//', $force_prot . '://', $atts['url'] );
							}

							$buttons_part = $this->website[$id]->get_html( $atts, $this->p->options, $mod ) . "\n";

							$atts = $saved_atts;	// restore the common $atts array

							if ( trim( $buttons_part ) !== '' ) {
								if ( empty( $atts['container_each'] ) ) {
									$buttons_html .= $buttons_part;
								} else {
									$buttons_html .= '<!-- adding buttons as individual containers -->' . "\n" . 
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
					$this->p->debug->log( 'website object missing for ' . $id );
				}
			}

			$buttons_html = trim( $buttons_html );

			if ( ! empty( $buttons_html ) ) {
				if ( empty( $atts['container_each'] ) )
					$buttons_html = $buttons_begin . $buttons_html . $buttons_end;
			}

			return $buttons_html;
		}

		public function have_buttons_for_type( $type ) {

			if ( isset( $this->buttons_for_type[$type] ) ) {
				return $this->buttons_for_type[$type];
			}

			foreach ( $this->p->cf['opt']['cm_prefix'] as $id => $opt_pre ) {
				if ( ! empty( $this->p->options[$opt_pre . '_on_' . $type] ) &&	// check if button is enabled
					$this->allow_for_platform( $id ) ) {			// check if allowed on platform

					return $this->buttons_for_type[$type] = true;
				}
			}

			return $this->buttons_for_type[$type] = false;
		}

		public function allow_for_platform( $id ) {

			// always allow if the content does not vary by user agent
			if ( ! $this->p->avail['*']['vary_ua'] ) {
				return true;
			}

			$opt_pre = isset( $this->p->cf['opt']['cm_prefix'][$id] ) ?
				$this->p->cf['opt']['cm_prefix'][$id] : $id;

			if ( isset( $this->p->options[$opt_pre . '_platform'] ) ) {
				switch( $this->p->options[$opt_pre . '_platform'] ) {
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

			// get_options() returns null if an index key is not found
			if ( $this->p->m['util']['post']->get_options( $post_id, 'buttons_disabled' ) ) {

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'post ' . $post_id . ': sharing buttons disabled by meta data option' );
				}

				$ret = true;

			} elseif ( ! empty( $post_obj->post_type ) && empty( $this->p->options['buttons_add_to_' . $post_obj->post_type] ) ) {

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
			$ret = preg_replace( '/(<\/*[pP]>|\n)/', '', $text );

			return $suff . $ret; 
		}

		public function get_website_object_ids( $website = array() ) {

			$website_ids = array();

			if ( empty( $website ) ) {
				$keys = array_keys( $this->website );
			} else {
				$keys = array_keys( $website );
			}

			$website_lib = $this->p->cf['plugin']['wpssorrssb']['lib']['website'];

			foreach ( $keys as $id ) {
				$website_ids[$id] = isset( $website_lib[$id] ) ? $website_lib[$id] : ucfirst( $id );
			}

			return $website_ids;
		}

		public function get_tweet_text( array $mod, $atts = array(), $opt_pre = 'twitter', $md_pre = 'twitter' ) {

			if ( ! isset( $atts['tweet'] ) ) {	// just in case

				$atts['use_post']     = isset( $atts['use_post'] ) ? $atts['use_post'] : true;
				$atts['add_page']     = isset( $atts['add_page'] ) ? $atts['add_page'] : true;	// used by get_sharing_url()
				$atts['add_hashtags'] = isset( $atts['add_hashtags'] ) ? $atts['add_hashtags'] : true;

				$cap_type   = empty( $this->p->options[$opt_pre . '_caption'] ) ? 'title' : $this->p->options[$opt_pre . '_caption'];
				$max_len    = $this->get_tweet_max_len( $opt_pre );
				$read_cache = true;
				$do_encode  = false;
				$md_idx     = $md_pre . '_desc';

				return $this->p->page->get_caption( $cap_type, $max_len, $mod, $read_cache, $atts['add_hashtags'], $do_encode, $md_idx );

			} else {
				return $atts['tweet'];
			}
		}

		/**
		 * $opt_pre can be twitter, buffer, etc.
		 */
		public function get_tweet_max_len( $opt_pre = 'twitter' ) {

			$short_len = 23;	// twitter counts 23 characters for any url

			if ( isset( $this->p->options['tc_site'] ) && ! empty( $this->p->options[$opt_pre . '_via'] ) ) {
				$tc_site = preg_replace( '/^@/', '', $this->p->options['tc_site'] );
				$site_len = empty( $tc_site ) ? 0 : strlen( $tc_site ) + 6;
			} else {
				$site_len = 0;
			}

			$max_len = $this->p->options[$opt_pre . '_cap_len'] - $site_len - $short_len;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log( 'max tweet length is ' . $max_len . ' chars (' . $this->p->options[$opt_pre . '_cap_len'] . 
					' less ' . $site_len . ' for site name and ' . $short_len . ' for url)' );
			}

			return $max_len;
		}

		public function enqueue_rrssb_ext( $hook_name ) {

			$plugin_version = $this->p->cf['plugin']['wpssorrssb']['version'];

			wp_register_script( 'rrssb', WPSSORRSSB_URLPATH . 'js/ext/rrssb.min.js', array( 'jquery' ), $plugin_version, true );	// in footer
			wp_enqueue_script( 'rrssb' );

			wp_register_style( 'rrssb', WPSSORRSSB_URLPATH . 'css/ext/rrssb.min.css', array(), $plugin_version );
			wp_enqueue_style( 'rrssb' );
		}

		public function filter_messages_tooltip( $text, $idx ) {

			if ( strpos( $idx, 'tooltip-buttons_' ) !== 0 ) {
				return $text;
			}

			switch ( $idx ) {

				case ( strpos( $idx, 'tooltip-buttons_pos_' ) === false ? false : true ):

					$text = sprintf( __( 'Social sharing buttons can be added to the top, bottom, or both. Each sharing button must also be enabled below (see the <em>%s</em> options).', 'wpsso-rrssb' ), _x( 'Show Button in', 'option label', 'wpsso-rrssb' ) );

					break;

				case 'tooltip-buttons_on_index':

					$text = __( 'Add the social sharing buttons to each entry of an index webpage (blog front page, category, archive, etc.). Social sharing buttons are not included on index webpages by default.', 'wpsso-rrssb' );

					break;

				case 'tooltip-buttons_on_front':

					$text = __( 'If a static Post or Page has been selected for the front page, you can add the social sharing buttons to that static front page as well (default is unchecked).', 'wpsso-rrssb' );

					break;

				case 'tooltip-buttons_add_to':

					$text = __( 'Enabled social sharing buttons are added to the Post, Page, Media, and Product webpages by default. If your theme (or another plugin) supports additional custom post types, and you would like to include social sharing buttons on these webpages, check the appropriate option(s) here.', 'wpsso-rrssb' );

					break;

				case 'tooltip-buttons_force_prot':

					$text = __( 'Modify URLs shared by the sharing buttons to use a specific protocol.', 'wpsso-rrssb' );

					break;

				case 'tooltip-buttons_use_social_style':

					$text = sprintf( __( 'Add the CSS of all <em>%1$s</em> to webpages (default is checked). The CSS will be <strong>minified</strong>, and saved to a single stylesheet with a URL of <a href="%2$s">%3$s</a>. The minified stylesheet can be enqueued or added directly to the webpage HTML.', 'wpsso-rrssb' ), _x( 'Responsive Styles', 'lib file description', 'wpsso-rrssb' ), WpssoRrssbSharing::$sharing_css_url, WpssoRrssbSharing::$sharing_css_url );

					break;

				case 'tooltip-buttons_enqueue_social_style':

					$text = __( 'Have WordPress enqueue the social stylesheet instead of adding the CSS to in the webpage HTML (default is unchecked). Enqueueing the stylesheet may be desirable if you use a plugin to concatenate all enqueued styles into a single stylesheet URL.', 'wpsso-rrssb' );

					break;

				case 'tooltip-buttons_add_via':

					$text = sprintf( __( 'Append the %1$s to the tweet (see <a href="%2$s">the Twitter options tab</a> in the %3$s settings page). The %1$s will be displayed and recommended after the webpage is shared.', 'wpsso-rrssb' ), _x( 'Twitter Business @username', 'option label', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'general#sucom-tabset_pub-tab_twitter' ), _x( 'General', 'lib file description', 'wpsso-rrssb' ) );

					break;

				case 'tooltip-buttons_rec_author':

					$text = sprintf( __( 'Recommend following the author\'s Twitter @username after sharing a webpage. If the %1$s option (above) is also checked, the %2$s is suggested first.', 'wpsso-rrssb' ), _x( 'Add via Business @username', 'option label', 'wpsso-rrssb' ), _x( 'Twitter Business @username', 'option label', 'wpsso-rrssb' ) );

					break;
			}

			return $text;
		}

		public function filter_messages_tooltip_plugin( $text, $idx ) {

			switch ( $idx ) {

				case 'tooltip-plugin_sharing_buttons_cache_exp':

					$cache_exp_secs  = WpssoRrssbConfig::$cf['opt']['defaults']['plugin_sharing_buttons_cache_exp'];
					$cache_exp_human = $cache_exp_secs ? human_time_diff( 0, $cache_exp_secs ) : _x( 'disabled', 'option comment', 'wpsso-rrssb' );

					$text = __( 'The rendered HTML for social sharing buttons is saved to the WordPress transient cache to optimize performance.',
						'wpsso-rrssb' ) . ' ' . sprintf( __( 'The suggested cache expiration value is %1$s seconds (%2$s).',
							'wpsso-rrssb' ), $cache_exp_secs, $cache_exp_human );

					break;
			}

			return $text;
		}

		public function filter_messages_info( $text, $idx ) {

			if ( strpos( $idx, 'info-styles-rrssb-' ) !== 0 ) {
				return $text;
			}

			$short = $this->p->cf['plugin']['wpssorrssb']['short'];

			switch ( $idx ) {

				case 'info-styles-rrssb-sharing':

					$text = '<p>';
					
					$text .= sprintf( __( 'The %1$s add-on uses the "%2$s" class to wrap all sharing buttons, and each button has its own individual class name as well.', 'wpsso-rrssb' ), $short, 'wpsso-rrssb' );

					$text .= '</p><p>';

					$text .= __( 'This tab can be used to edit the CSS common to all sharing button locations.', 'wpsso-rrssb' );

					$text .= '</p>';

					break;

				case 'info-styles-rrssb-content':

					$text = '<p>';
					
					$text .= sprintf( __( 'Social sharing buttons enabled in the %1$s settings page for the WordPress content are assigned the "%2$s" class.', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'rrssb-buttons', 'Responsive Buttons' ), 'wpsso-rrssb-content' ).

					$text .= '</p>';
					
					$text .= $this->get_info_css_example( 'content', true );

					break;

				case 'info-styles-rrssb-excerpt':

					$text = '<p>';
					
					$text .= sprintf( __( 'Social sharing buttons enabled in the %1$s settings page for the WordPress excerpt are assigned the "%2$s" class.', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'rrssb-buttons', 'Responsive Buttons' ), 'wpsso-rrssb-excerpt' );
					
					$text .= '</p>';

					$text .= $this->get_info_css_example( 'excerpt', true );

					break;

				case 'info-styles-rrssb-sidebar':

					$text = '<p>';
					
					$text .= sprintf( __( 'Social sharing buttons enabled in the %1$s settings page for the CSS sidebar are assigned the "%2$s" ID.', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'rrssb-buttons', 'Responsive Buttons' ), 'wpsso-rrssb-sidebar' );
					
					$text .= '</p><p>';

					$text .= 'In order to achieve a vertical display, each unordered list (UL) contains a single list item (LI).';

					$text .= '</p>';

					$text .= '<p>Example CSS:</p>
<pre>
div.wpsso-rrssb 
  #wpsso-rrssb-sidebar
    ul.rrssb-buttons
      li.rrssb-facebook {}
</pre>';
					break;

				case 'info-styles-rrssb-shortcode':

					$text = '<p>';
					
					$text .= sprintf( __( 'Social sharing buttons added from a shortcode are assigned the "%1$s" class by default.', 'wpsso-rrssb' ), 'wpsso-rrssb-shortcode' );
					
					$text .= '</p>';

					$text .= $this->get_info_css_example( 'shortcode', true );

					break;

				case 'info-styles-rrssb-widget':

					$text = '<p>';
					
					$text .= sprintf( __( 'Social sharing buttons enabled in the %1$s widget are assigned the "%2$s" class (along with additional unique CSS ID names).', 'wpsso-rrssb' ), $short, 'wpsso-rrssb-widget' );
					
					$text .= '</p>';

					$text .= '<p>Example CSS:</p>
<pre>
aside.widget 
  .wpsso-rrssb-widget 
    ul.rrssb-buttons
        li.rrssb-facebook {}
</pre>';

					break;

				case 'info-styles-rrssb-admin_edit':

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons enabled in the %1$s settings page for admin editing pages are assigned the "%2$s" class.', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'rrssb-buttons', 'Responsive Buttons' ), 'wpsso-rrssb-admin_edit' );

					$text .= '</p>';

					$text .= $this->get_info_css_example( 'admin_edit', true );

					break;

				case 'info-styles-rrssb-woo_short': 

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons enabled in the %1$s settings page for WooCommerce short descriptions are assigned the "%2$s" class.', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'rrssb-buttons', 'Responsive Buttons' ), 'wpsso-rrssb-woo_short' );

					$text .= '</p>';

					$text .= $this->get_info_css_example( 'woo_short' );

      					break;

				case 'info-styles-rrssb-bbp_single': 

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons enabled in the %1$s settings page for bbPress single templates are assigned the "%2$s" class.', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'rrssb-buttons', 'Responsive Buttons' ), 'wpsso-rrssb-bbp_single' );

					$text .= '</p>';

					$text .= $this->get_info_css_example( 'bbp_single' );

      					break;

				case 'info-styles-rrssb-bblog_post': 

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons enabled in the %1$s settings page for BuddyBlog posts are assigned the "%2$s" class.', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'rrssb-buttons', 'Responsive Buttons' ), 'wpsso-rrssb-bblog_post' );

					$text .= '</p>';

					$text .= $this->get_info_css_example( 'bblog_post' );

      					break;

				case 'info-styles-rrssb-bp_activity': 

					$text = '<p>';

					$text .= sprintf( __( 'Social sharing buttons enabled in the %1$s settings page for BuddyPress activities are assigned the "%2$s" class.', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'rrssb-buttons', 'Responsive Buttons' ), 'wpsso-rrssb-bp_activity' );

					$text .= '</p>';

					$text .= $this->get_info_css_example( 'bp_activity' );

      					break;
			}
			return $text;
		}

		protected function get_info_css_example( $type ) {

			$text = '<p>Example CSS:</p>
<pre>
div.wpsso-rrssb
  .wpsso-rrssb-'.$type.'
    ul.rrssb-buttons
      li.rrssb-facebook {}
</pre>';

			return $text;
		}

	}
}
