<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2016 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoRrssbSharing' ) ) {

	class WpssoRrssbSharing {

		protected $p;
		protected $website = array();
		protected $plugin_filepath;
		protected $buttons_for_type = array();		// cache for have_buttons_for_type()
		protected $post_buttons_disabled = array();	// cache for is_post_buttons_disabled()

		public static $sharing_css_name = '';
		public static $sharing_css_file = '';
		public static $sharing_css_url = '';

		public static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					/*
					 * Advanced Settings
					 */
					// Cache Settings Tab
					'plugin_sharing_buttons_cache_exp' => 604800,	// Sharing Buttons Cache Expiry (7 days)
					/*
					 * Sharing Buttons
					 */
					// Include Buttons
					'buttons_on_index' => 0,
					'buttons_on_front' => 0,
					'buttons_add_to_post' => 1,
					'buttons_add_to_page' => 1,
					'buttons_add_to_attachment' => 1,
					// Buttons Position Tab
					'buttons_pos_content' => 'bottom',
					'buttons_pos_excerpt' => 'bottom',
					/*
					 * Sharing Styles
					 */
					'buttons_use_social_style' => 1,
					'buttons_enqueue_social_style' => 1,
					'buttons_css_rrssb-sharing' => '',		// all buttons
					'buttons_css_rrssb-content' => '',		// post/page content
					'buttons_css_rrssb-excerpt' => '',		// post/page excerpt
					'buttons_css_rrssb-admin_edit' => '',
					'buttons_css_rrssb-sidebar' => '',
					'buttons_css_rrssb-shortcode' => '',
					'buttons_css_rrssb-widget' => '',
				),	// end of defaults
				'site_defaults' => array(
					'plugin_sharing_buttons_cache_exp' => 604800,	// Sharing Buttons Cache Expiry (7 days)
					'plugin_sharing_buttons_cache_exp:use' => 'default',
				),	// end of site defaults
			),
		);

		public function __construct( &$plugin, $plugin_filepath = WPSSORRSSB_FILEPATH ) {
			$this->p =& $plugin;

			if ( $this->p->debug->enabled )
				$this->p->debug->mark( 'rrssb sharing action / filter setup' );

			$this->plugin_filepath = $plugin_filepath;

			self::$sharing_css_name = 'rrssb-styles-id-'.get_current_blog_id().'.min.css';
			self::$sharing_css_file = WPSSO_CACHEDIR.self::$sharing_css_name;
			self::$sharing_css_url = WPSSO_CACHEURL.self::$sharing_css_name;

			$this->set_objects();

			add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_rrssb_ext' ) );
			add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_rrssb_ext' ) );
			add_action( 'wp_enqueue_scripts', array( &$this, 'wp_enqueue_styles' ) );
			add_action( 'wp_footer', array( &$this, 'show_footer' ), WPSSORRSSB_FOOTER_PRIORITY );

			if ( $this->have_buttons_for_type( 'content' ) )
				$this->add_buttons_filter( 'the_content' );

			if ( $this->have_buttons_for_type( 'excerpt' ) ) {
				$this->add_buttons_filter( 'get_the_excerpt' );
				$this->add_buttons_filter( 'the_excerpt' );
			}

			$this->p->util->add_plugin_filters( $this, array( 
				'get_defaults' => 1,
				'get_site_defaults' => 1,
				'get_md_defaults' => 1,
				'text_filter_has_changes_before' => 2,
				'text_filter_has_changes_after' => 2,
			) );

			if ( is_admin() ) {
				if ( $this->have_buttons_for_type( 'admin_edit' ) )
					add_action( 'add_meta_boxes', array( &$this, 'add_post_buttons_metabox' ) );

				$this->p->util->add_plugin_filters( $this, array( 
					'save_options' => 3,			// update the sharing css file
					'option_type' => 2,			// identify option type for sanitation
					'post_social_settings_tabs' => 2,	// $tabs, $mod
					'post_cache_transients' => 4,		// clear transients on post save
					'secondary_action_buttons' => 4,	// add a reload default styles button
					'messages_info' => 2,
					'messages_tooltip' => 2,
					'messages_tooltip_plugin' => 2,
				) );

				$this->p->util->add_plugin_filters( $this, array( 
					'status_gpl_features' => 3,		// include sharing, shortcode, and widget status
				), 10, 'wpssorrssb' );				// hook into the extension name instead

				$this->p->util->add_plugin_actions( $this, array(
					'load_setting_page_reload_default_sharing_rrssb_styles' => 4,
				) );
			}

			if ( $this->p->debug->enabled )
				$this->p->debug->mark( 'rrssb sharing action / filter setup' );
		}

		private function set_objects() {
			foreach ( $this->p->cf['plugin']['wpssorrssb']['lib']['website'] as $id => $name ) {
				$classname = WpssoRrssbConfig::load_lib( false, 'website/'.$id, 'wpssorrssbwebsite'.$id );
				if ( $classname !== false && class_exists( $classname ) ) {
					$this->website[$id] = new $classname( $this->p );
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $classname.' class loaded' );
				}
			}
		}

		public function filter_get_defaults( $def_opts ) {
			$def_opts = array_merge( $def_opts, self::$cf['opt']['defaults'] );
			$def_opts = $this->p->util->add_ptns_to_opts( $def_opts, 'buttons_add_to' );
			$plugin_dir = trailingslashit( realpath( dirname( $this->plugin_filepath ) ) );
			$url_path = parse_url( trailingslashit( plugins_url( '', $this->plugin_filepath ) ), PHP_URL_PATH );	// relative URL
			$tabs = apply_filters( $this->p->cf['lca'].'_rrssb_styles_tabs', $this->p->cf['sharing']['rrssb-styles'] );

			foreach ( $tabs as $id => $name ) {
				$buttons_css_file = $plugin_dir.'css/'.$id.'.css';

				// css files are only loaded once (when variable is empty) into defaults to minimize disk i/o
				if ( empty( $def_opts['buttons_css_'.$id] ) ) {
					if ( ! file_exists( $buttons_css_file ) )
						continue;
					elseif ( ! $fh = @fopen( $buttons_css_file, 'rb' ) )
						$this->p->notice->err( sprintf( __( 'Failed to open the %s file for reading.',
							'wpsso-rrssb' ), $buttons_css_file ) );
					else {
						$css_data = fread( $fh, filesize( $buttons_css_file ) );
						fclose( $fh );
						if ( $this->p->debug->enabled )
							$this->p->debug->log( 'read css from file '.$buttons_css_file );
						foreach ( array( 
							'plugin_url_path' => $url_path,
						) as $macro => $value )
							$css_data = preg_replace( '/%%'.$macro.'%%/', $value, $css_data );
						$def_opts['buttons_css_'.$id] = $css_data;
					}
				}
			}
			return $def_opts;
		}

		public function filter_get_site_defaults( $site_def_opts ) {
			return array_merge( $site_def_opts, self::$cf['opt']['site_defaults'] );
		}

		public function filter_get_md_defaults( $def_opts ) {
			return array_merge( $def_opts, array(
				'email_title' => '',		// Email Subject
				'email_desc' => '',		// Email Message
				'twitter_desc' => '',		// Tweet Text
				'pin_desc' => '',		// Pinterest Caption
				'linkedin_title' => '',		// LinkedIn Title
				'linkedin_desc' => '',		// LinkedIn Caption
				'reddit_title' => '',		// Reddit Title
				'reddit_desc' => '',		// Reddit Caption
				'tumblr_title' => '',		// Tumblr Title
				'tumblr_desc' => '',		// Tumblr Caption
				'buttons_disabled' => 0,	// Disable Sharing Buttons
			) );
		}

		public function filter_save_options( $opts, $options_name, $network ) {
			// update the combined and minimized social stylesheet
			if ( $network === false )
				$this->update_sharing_css( $opts );
			return $opts;
		}

		public function filter_option_type( $type, $key ) {

			if ( ! empty( $type ) )
				return $type;

			switch ( $key ) {
				// integer options that must be 1 or more (not zero)
				case ( preg_match( '/_order$/', $key ) ? true : false ):
					return 'pos_num';	// cast as integer
					break;
				// text strings that can be blank
				case ( preg_match( '/_(desc|title)$/', $key ) ? true : false ):
					return 'ok_blank';
					break;
			}
			return $type;
		}

		public function filter_post_social_settings_tabs( $tabs, $mod ) {
			return SucomUtil::get_after_key( $tabs, 'media', 'buttons',
				_x( 'Sharing Buttons', 'metabox tab', 'wpsso-rrssb' ) );
		}

		public function filter_post_cache_transients( $transients, $mod, $locale, $sharing_url ) {
			$locale_salt = SucomUtil::get_mod_salt( $mod, $locale, $sharing_url );
			$transients['WpssoRrssbSharing::get_buttons'][] = $locale_salt;
			$transients['WpssoRrssbShortcodeSharing::shortcode'][] = $locale_salt;
			$transients['WpssoRrssbWidgetSharing::widget'][] = $locale_salt;
			return $transients;
		}

		public function filter_status_gpl_features( $features, $lca, $info ) {
			if ( ! empty( $info['lib']['submenu']['rrssb-buttons'] ) )
				$features['(sharing) Sharing Buttons'] = array(
					'classname' => $lca.'Sharing',
				);
			if ( ! empty( $info['lib']['submenu']['rrssb-styles'] ) )
				$features['(sharing) Sharing Stylesheet'] = array(
					'status' => $this->p->options['buttons_use_social_style'] ? 'on' : 'off',
				);
			if ( ! empty( $info['lib']['shortcode']['sharing'] ) )
				$features['(sharing) Sharing Shortcode'] = array(
					'classname' => $lca.'ShortcodeSharing',
				);
			if ( ! empty( $info['lib']['widget']['sharing'] ) )
				$features['(sharing) Sharing Widget'] = array(
					'classname' => $lca.'WidgetSharing',
				);
			return $features;
		}

		public function filter_secondary_action_buttons( $actions, $menu_id, $menu_name, $menu_lib ) {
			if ( $menu_id === 'rrssb-styles' )
				$actions['reload_default_sharing_rrssb_styles'] = __( 'Reload Default Styles', 'submit button', 'wpsso-rrssb' );
			return $actions;
		}
		
		public function action_load_setting_page_reload_default_sharing_rrssb_styles( $pagehook, $menu_id, $menu_name, $menu_lib ) {
			$opts =& $this->p->options;
			$def_opts = $this->p->opt->get_defaults();
			$tabs = apply_filters( $this->p->cf['lca'].'_rrssb_styles_tabs', 
				$this->p->cf['sharing']['rrssb-styles'] );

			foreach ( $tabs as $id => $name )
				if ( isset( $opts['buttons_css_'.$id] ) &&
					isset( $def_opts['buttons_css_'.$id] ) )
						$opts['buttons_css_'.$id] = $def_opts['buttons_css_'.$id];

			$this->update_sharing_css( $opts );
			$this->p->opt->save_options( WPSSO_OPTIONS_NAME, $opts, false );
			$this->p->notice->upd( __( 'All sharing styles have been reloaded with their default settings and saved.', 'wpsso-rrssb' ) );
		}

		public function wp_enqueue_styles() {
			if ( ! empty( $this->p->options['buttons_use_social_style'] ) ) {
				if ( ! file_exists( self::$sharing_css_file ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'updating '.self::$sharing_css_file );
					$this->update_sharing_css( $this->p->options );
				}
				if ( ! empty( $this->p->options['buttons_enqueue_social_style'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'wp_enqueue_style = '.$this->p->cf['lca'].'_rrssb_sharing_css' );
					wp_register_style( $this->p->cf['lca'].'_rrssb_sharing_css', self::$sharing_css_url, 
						false, $this->p->cf['plugin'][$this->p->cf['lca']]['version'] );
					wp_enqueue_style( $this->p->cf['lca'].'_rrssb_sharing_css' );
				} else {
					if ( ! is_readable( self::$sharing_css_file ) ) {
						if ( $this->p->debug->enabled )
							$this->p->debug->log( self::$sharing_css_file.' is not readable' );
						if ( is_admin() )
							$this->p->notice->err( sprintf( __( 'The %s file is not readable.',
								'wpsso-rrssb' ), self::$sharing_css_file ) );
					} else {
						echo '<style type="text/css">';
						if ( ( $fsize = @filesize( self::$sharing_css_file ) ) > 0 &&
							$fh = @fopen( self::$sharing_css_file, 'rb' ) ) {
							echo fread( $fh, $fsize );
							fclose( $fh );
						}
						echo '</style>',"\n";
					}
				}
			} elseif ( $this->p->debug->enabled )
				$this->p->debug->log( 'buttons_use_social_style option is disabled' );
		}

		public function update_sharing_css( &$opts ) {

			if ( empty( $opts['buttons_use_social_style'] ) ) {
				$this->unlink_sharing_css();
				return;
			}

			$css_data = '';
			$tabs = apply_filters( $this->p->cf['lca'].'_rrssb_styles_tabs', 
				$this->p->cf['sharing']['rrssb-styles'] );

			foreach ( $tabs as $id => $name )
				if ( isset( $opts['buttons_css_'.$id] ) )
					$css_data .= $opts['buttons_css_'.$id];

			$classname = apply_filters( $this->p->cf['lca'].'_load_lib', 
				false, 'ext/compressor', 'SuextMinifyCssCompressor' );

			if ( $classname !== false && class_exists( $classname ) )
				$css_data = call_user_func( array( $classname, 'process' ), $css_data );
			else {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'failed to load minify class SuextMinifyCssCompressor' );
				if ( is_admin() )
					$this->p->notice->err( __( 'Failed to load the minify class SuextMinifyCssCompressor.',
						'wpsso-rrssb' ) );
			}

			if ( $fh = @fopen( self::$sharing_css_file, 'wb' ) ) {
				if ( ( $written = fwrite( $fh, $css_data ) ) === false ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'failed writing to '.self::$sharing_css_file );
					if ( is_admin() )
						$this->p->notice->err( sprintf( __( 'Failed writing to the % file.',
							'wpsso-rrssb' ), self::$sharing_css_file ) );
				} elseif ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'updated css file '.self::$sharing_css_file.' ('.$written.' bytes written)' );
					if ( is_admin() )
						$this->p->notice->upd( sprintf( __( 'Updated the <a href="%1$s">%2$s</a> stylesheet (%3$d bytes written).',
							'wpsso-rrssb' ), self::$sharing_css_url, self::$sharing_css_file, $written ), 
								true, 'updated_'.self::$sharing_css_file, true );
				}
				fclose( $fh );
			} else {
				if ( ! is_writable( WPSSO_CACHEDIR ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( WPSSO_CACHEDIR.' is not writable', true );
					if ( is_admin() )
						$this->p->notice->err( sprintf( __( 'The %s folder is not writable.',
							'wpsso-rrssb' ), WPSSO_CACHEDIR ) );
				}
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'failed opening '.self::$sharing_css_file.' for writing' );
				if ( is_admin() )
					$this->p->notice->err( sprintf( __( 'Failed to open file %s for writing.',
						'wpsso-rrssb' ), self::$sharing_css_file ) );
			}
		}

		public function unlink_sharing_css() {
			if ( file_exists( self::$sharing_css_file ) ) {
				if ( ! @unlink( self::$sharing_css_file ) ) {
					if ( is_admin() )
						$this->p->notice->err( __( 'Error removing the minimized stylesheet &mdash; does the web server have sufficient privileges?', 'wpsso-rrssb' ) );
				}
			}
		}

		public function add_post_buttons_metabox() {
			if ( ! is_admin() )
				return;

			// get the current object / post type
			if ( ( $post_obj = SucomUtil::get_post_object() ) === false ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'exiting early: invalid post object' );
				return;
			}

			if ( ! empty( $this->p->options[ 'buttons_add_to_'.$post_obj->post_type ] ) ) {
				// add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
				add_meta_box( '_'.$this->p->cf['lca'].'_rrssb_share', 
					_x( 'Sharing Buttons', 'metabox title', 'wpsso-rrssb' ),
						array( &$this, 'show_admin_sharing' ), $post_obj->post_type, 'side', 'high' );
			}
		}

		public function filter_text_filter_has_changes_before( $ret, $filter_name ) {
			return ( $this->remove_buttons_filter( $filter_name ) ? true : $ret );
		}

		public function filter_text_filter_has_changes_after( $ret, $filter_name ) {
			return ( $this->add_buttons_filter( $filter_name ) ? true : $ret );
		}

		public function show_footer() {
			if ( $this->have_buttons_for_type( 'sidebar' ) )
				$this->show_sidebar();
			elseif ( $this->p->debug->enabled )
				$this->p->debug->log( 'no buttons enabled for sidebar' );
			if ( $this->p->debug->enabled )
				$this->p->debug->show_html( null, 'Debug Log' );
		}

		public function show_sidebar() {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			$lca = $this->p->cf['lca'];
			echo $this->get_buttons( '', 'sidebar', false, '', array( 'container_each' => true ) );	// $use_post = false
			if ( $this->p->debug->enabled )
				$this->p->debug->show_html( null, 'Debug Log' );
		}

		public function show_admin_sharing( $post_obj ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			$lca = $this->p->cf['lca'];
			$css_data = '#side-sortables #_'.$lca.'_rrssb_share .inside table.sucom-setting { padding:0; }'.
				$this->p->options['buttons_css_rrssb-admin_edit'];

			$classname = apply_filters( $this->p->cf['lca'].'_load_lib', 
				false, 'ext/compressor', 'SuextMinifyCssCompressor' );

			if ( $classname !== false && class_exists( $classname ) )
				$css_data = call_user_func( array( $classname, 'process' ), $css_data );

			echo '<style type="text/css">'.$css_data.'</style>', "\n";
			echo '<table class="sucom-setting '.$this->p->cf['lca'].' side"><tr><td>';
			if ( get_post_status( $post_obj->ID ) === 'publish' || 
				$post_obj->post_type === 'attachment' ) {

				$content = '';
				echo $this->get_buttons( $content, 'admin_edit' );
				if ( $this->p->debug->enabled )
					$this->p->debug->show_html( null, 'Debug Log' );

			} else echo '<p class="centered">'.sprintf( __( '%s must be published<br/>before it can be shared.',
				'wpsso-rrssb' ), SucomUtil::titleize( $post_obj->post_type ) ).'</p>';
			echo '</td></tr></table>';
		}

		public function add_buttons_filter( $filter_name = 'the_content' ) {
			$added = false;
			if ( method_exists( $this, 'get_buttons_'.$filter_name ) ) {
				$added = add_filter( $filter_name, array( &$this, 'get_buttons_'.$filter_name ), WPSSORRSSB_SOCIAL_PRIORITY );
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'buttons filter '.$filter_name.
						' added ('.( $added  ? 'true' : 'false' ).')' );
			} elseif ( $this->p->debug->enabled )
				$this->p->debug->log( 'get_buttons_'.$filter_name.' method is missing' );
			return $added;
		}

		public function remove_buttons_filter( $filter_name = 'the_content' ) {
			$removed = false;
			if ( method_exists( $this, 'get_buttons_'.$filter_name ) ) {
				$removed = remove_filter( $filter_name, array( &$this, 'get_buttons_'.$filter_name ), WPSSORRSSB_SOCIAL_PRIORITY );
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'buttons filter '.$filter_name.
						' removed ('.( $removed  ? 'true' : 'false' ).')' );
			}
			return $removed;
		}

		public function get_buttons_the_excerpt( $text ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			$id = $this->p->cf['lca'].' excerpt-buttons';
			$text = preg_replace_callback( '/(<!-- '.$id.' begin -->.*<!-- '.$id.' end -->)(<\/p>)?/Usi', 
				array( __CLASS__, 'remove_paragraph_tags' ), $text );
			return $text;
		}

		public function get_buttons_get_the_excerpt( $text ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			return $this->get_buttons( $text, 'excerpt' );
		}

		public function get_buttons_the_content( $text ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			return $this->get_buttons( $text, 'content' );
		}

		// $mod = true | false | post_id | $mod array
		public function get_buttons( $text, $type = 'content', $mod = true, $location = '', $atts = array() ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$error_msg = false;
			if ( is_admin() ) {
				if ( strpos( $type, 'admin_' ) !== 0 )
					$error_msg = $type.' ignored in back-end';
			} elseif ( $this->p->is_avail['amp_endpoint'] && is_amp_endpoint() ) {
				$error_msg = 'buttons not allowed in amp endpoint';
			} elseif ( is_feed() ) {
				$error_msg = 'buttons not allowed in rss feeds';
			} elseif ( ! is_singular() ) {
				if ( empty( $this->p->options['buttons_on_index'] ) )
					$error_msg = 'buttons_on_index not enabled';
			} elseif ( is_front_page() ) {
				if ( empty( $this->p->options['buttons_on_front'] ) )
					$error_msg = 'buttons_on_front not enabled';
			} elseif ( is_singular() ) {
				if ( $this->is_post_buttons_disabled() )
					$error_msg = 'post buttons are disabled';
			}

			if ( ! $this->have_buttons_for_type( $type ) )
				$error_msg = 'no sharing buttons enabled';

			if ( $error_msg !== false ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( $type.' filter skipped: '.$error_msg );
				return $text."\n".'<!-- '.__METHOD__.' '.$type.' filter skipped: '.$error_msg.' -->'."\n".
					( $this->p->debug->enabled ? $this->p->debug->get_html() : '' );
			}

			$lca = $this->p->cf['lca'];
			if ( ! is_array( $mod ) )
				$mod = $this->p->util->get_page_mod( $mod );
			$sharing_url = $this->p->util->get_sharing_url( $mod );
			$buttons_index = $this->get_buttons_cache_index( $type );
			$buttons_array = array();
			$cache_exp = (int) apply_filters( $lca.'_cache_expire_sharing_buttons', 
				$this->p->options['plugin_sharing_buttons_cache_exp'] );

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log( 'sharing url = '.$sharing_url );
				$this->p->debug->log( 'buttons index = '.$buttons_index );
				$this->p->debug->log( 'cache expire = '.$cache_exp );
			}

			if ( $cache_exp > 0 ) {
				$cache_salt = __METHOD__.'('.SucomUtil::get_mod_salt( $mod, false, $sharing_url ).')';
				$cache_id = $lca.'_'.md5( $cache_salt );
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'transient cache salt = '.$cache_salt );
				$buttons_array = get_transient( $cache_id );
				if ( isset( $buttons_array[$buttons_index] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $type.' buttons array retrieved from transient '.$cache_id );
				}
			}

			if ( ! isset( $buttons_array[$buttons_index] ) ) {
				// sort enabled sharing buttons by their preferred order
				$sorted_ids = array();
				foreach ( $this->p->cf['opt']['cm_prefix'] as $id => $opt_pre )
					if ( ! empty( $this->p->options[$opt_pre.'_on_'.$type] ) )
						$sorted_ids[ zeroise( $this->p->options[$opt_pre.'_order'], 3 ).'-'.$id ] = $id;
				ksort( $sorted_ids );
				$atts['use_post'] = $mod['use_post'];
				$atts['css_id'] = $css_type_name = 'rrssb-'.$type;

				// returns html or an empty string
				$buttons_array[$buttons_index] = $this->get_html( $sorted_ids, $atts, $mod );

				if ( ! empty( $buttons_array[$buttons_index] ) ) {
					$buttons_array[$buttons_index] = '
<!-- '.$lca.' '.$css_type_name.' begin -->
<!-- generated on '.date( 'c' ).' -->
<div class="'.$lca.'-rrssb'.( $mod['use_post'] ? ' '.$lca.'-'.$css_type_name.'">' : '" id="'.$lca.'-'.$css_type_name.'">' ).
$buttons_array[$buttons_index]."\n".	// buttons html is trimmed, so add newline
'</div><!-- .'.$lca.'-rrssb '.( $mod['use_post'] ? '.' : '#' ).$lca.'-'.$css_type_name.' -->
<!-- '.$lca.' '.$css_type_name.' end -->'."\n\n";

					if ( $cache_exp > 0 ) {
						set_transient( $cache_id, $buttons_array, $cache_exp );
						if ( $this->p->debug->enabled )
							$this->p->debug->log( $type.' buttons html saved to transient '.
								$cache_id.' ('.$cache_exp.' seconds)' );
					}
				}
			}

			if ( empty( $location ) ) {
				$location = empty( $this->p->options['buttons_pos_'.$type] ) ? 
					'bottom' : $this->p->options['buttons_pos_'.$type];
			} 

			switch ( $location ) {
				case 'top': 
					$text = $buttons_array[$buttons_index].$text; 
					break;
				case 'bottom': 
					$text = $text.$buttons_array[$buttons_index]; 
					break;
				case 'both': 
					$text = $buttons_array[$buttons_index].$text.$buttons_array[$buttons_index]; 
					break;
			}

			return $text.( $this->p->debug->enabled ? $this->p->debug->get_html() : '' );
		}

		public function get_buttons_cache_index( $type, $atts = false, $ids = false ) {
			return 'type:'.( empty( $type ) ? 'none' : $type ).	// just in case
				'_https:'.( SucomUtil::is_https() ? 'true' : 'false' ).
				'_mobile:'.( SucomUtil::is_mobile() ? 'true' : 'false' ).
				( $atts !== false ? '_atts:'.http_build_query( $atts, '', '_' ) : '' ).
				( $ids !== false ? '_ids:'.http_build_query( $ids, '', '_' ) : '' );
		}

		// get_html() can be called by a widget, shortcode, function, filter hook, etc.
		public function get_html( array $ids, array $atts, $mod = false ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$lca = $this->p->cf['lca'];
			$atts['use_post'] = isset( $atts['use_post'] ) ? $atts['use_post'] : true;	// maintain backwards compat
			$atts['add_page'] = isset( $atts['add_page'] ) ? $atts['add_page'] : true;	// used by get_sharing_url()

			if ( ! is_array( $mod ) )
				$mod = $this->p->util->get_page_mod( $atts['use_post'] );

			$buttons_html = '';
			$buttons_begin = '<ul class="rrssb-buttons '.SucomUtil::get_locale( $mod ).' clearfix">'."\n";
			$buttons_end = '</ul><!-- .rrssb-buttons.'.SucomUtil::get_locale( $mod ).'.clearfix -->'."\n";

			$saved_atts = $atts;
			foreach ( $ids as $id ) {
				if ( isset( $this->website[$id] ) ) {
					if ( method_exists( $this->website[$id], 'get_html' ) ) {
						if ( $this->allow_for_platform( $id ) ) {

							$atts['src_id'] = SucomUtil::get_atts_src_id( $atts, $id );	// uses 'css_id' and 'use_post'
							$atts['url'] = empty( $atts['url'] ) ? 				// used by get_inline_vals()
								$this->p->util->get_sharing_url( $mod, 
									$atts['add_page'], $atts['src_id'] ) : 
								apply_filters( $lca.'_sharing_url', $atts['url'], 
									$mod, $atts['add_page'], $atts['src_id'] );
							$buttons_part = $this->website[$id]->get_html( $atts, $this->p->options, $mod )."\n";
							$atts = $saved_atts;	// restore the common $atts array

							if ( trim( $buttons_part ) !== '' ) {
								if ( empty( $atts['container_each'] ) )
									$buttons_html .= $buttons_part;
								else $buttons_html .= '<!-- container_each -->'.
									$buttons_begin.$buttons_part.$buttons_end;
							}

						} elseif ( $this->p->debug->enabled )
							$this->p->debug->log( $id.' not allowed for platform' );
					} elseif ( $this->p->debug->enabled )
						$this->p->debug->log( 'get_html method missing for '.$id );
				} elseif ( $this->p->debug->enabled )
					$this->p->debug->log( 'website object missing for '.$id );
			}

			$buttons_html = trim( $buttons_html );
			if ( ! empty( $buttons_html ) ) {
				if ( empty( $atts['container_each'] ) )
					$buttons_html = $buttons_begin.$buttons_html.$buttons_end;
			}
			return $buttons_html;
		}

		public function have_buttons_for_type( $type ) {
			if ( isset( $this->buttons_for_type[$type] ) )
				return $this->buttons_for_type[$type];
			foreach ( $this->p->cf['opt']['cm_prefix'] as $id => $opt_pre ) {
				if ( ! empty( $this->p->options[$opt_pre.'_on_'.$type] ) &&	// check if button is enabled
					$this->allow_for_platform( $id ) )			// check if allowed on platform
						return $this->buttons_for_type[$type] = true;
			}
			return $this->buttons_for_type[$type] = false;
		}

		public function allow_for_platform( $id ) {
			$opt_pre = isset( $this->p->cf['opt']['cm_prefix'][$id] ) ?
				$this->p->cf['opt']['cm_prefix'][$id] : $id;
			if ( isset( $this->p->options[$opt_pre.'_platform'] ) ) {
				switch( $this->p->options[$opt_pre.'_platform'] ) {
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
			$ret = false;

			if ( ( $post_obj = SucomUtil::get_post_object() ) === false ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'exiting early: invalid post object' );
				return $ret;
			} else $post_id = empty( $post_obj->ID ) ? 0 : $post_obj->ID;

			if ( empty( $post_id ) )
				return $ret;

			if ( isset( $this->post_buttons_disabled[$post_id] ) )
				return $this->post_buttons_disabled[$post_id];

			if ( $this->p->m['util']['post']->get_options( $post_id, 'buttons_disabled' ) ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'post '.$post_id.': sharing buttons disabled by meta data option' );
				$ret = true;
			} elseif ( ! empty( $post_obj->post_type ) && 
				empty( $this->p->options['buttons_add_to_'.$post_obj->post_type] ) ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'post '.$post_id.': sharing buttons not enabled for post type '.$post_obj->post_type );
				$ret = true;
			}

			return $this->post_buttons_disabled[$post_id] = apply_filters( $this->p->cf['lca'].'_post_buttons_disabled', $ret, $post_id );
		}

		public function remove_paragraph_tags( $match = array() ) {
			if ( empty( $match ) || ! is_array( $match ) ) return;
			$text = empty( $match[1] ) ? '' : $match[1];
			$suff = empty( $match[2] ) ? '' : $match[2];
			$ret = preg_replace( '/(<\/*[pP]>|\n)/', '', $text );
			return $suff.$ret; 
		}

		public function get_website_object_ids( $website_obj = array() ) {
			$ids = array();

			if ( empty( $website_obj ) )
				$website_keys = array_keys( $this->website );
			else $website_keys = array_keys( $website_obj );

			$website_ids = $this->p->cf['plugin']['wpssorrssb']['lib']['website'];

			foreach ( $website_keys as $id )
				$ids[$id] = isset( $website_ids[$id] ) ?
					$website_ids[$id] : ucfirst( $id );
			return $ids;
		}

		public function get_tweet_text( array $mod, $atts = array(), $opt_pre = 'twitter', $md_pre = 'twitter' ) {
			if ( ! isset( $atts['tweet'] ) ) {	// just in case
				$atts['use_post'] = isset( $atts['use_post'] ) ? $atts['use_post'] : true;
				$atts['add_page'] = isset( $atts['add_page'] ) ? $atts['add_page'] : true;	// used by get_sharing_url()
				$atts['add_hashtags'] = isset( $atts['add_hashtags'] ) ? $atts['add_hashtags'] : true;
				return $this->p->webpage->get_caption( ( empty( $this->p->options[$opt_pre.'_caption'] ) ?
					'title' : $this->p->options[$opt_pre.'_caption'] ), $this->get_tweet_max_len( $opt_pre ),
						$mod, true, $atts['add_hashtags'], false, $md_pre.'_desc' );
			} else return $atts['tweet'];
		}

		// $opt_pre can be twitter, buffer, etc.
		public function get_tweet_max_len( $opt_pre = 'twitter' ) {
			$short_len = 23;	// twitter counts 23 characters for any url

			if ( isset( $this->p->options['tc_site'] ) && ! empty( $this->p->options[$opt_pre.'_via'] ) ) {
				$tc_site = preg_replace( '/^@/', '', $this->p->options['tc_site'] );
				$site_len = empty( $tc_site ) ? 0 : strlen( $tc_site ) + 6;
			} else $site_len = 0;

			$max_len = $this->p->options[$opt_pre.'_cap_len'] - $site_len - $short_len;

			if ( $this->p->debug->enabled )
				$this->p->debug->log( 'max tweet length is '.$max_len.' chars ('.$this->p->options[$opt_pre.'_cap_len'].
					' less '.$site_len.' for site name and '.$short_len.' for url)' );

			return $max_len;
		}

		public function enqueue_rrssb_ext( $hook_name ) {
			$url_path = WPSSORRSSB_URLPATH;
			$plugin_version = $this->p->cf['plugin']['wpssorrssb']['version'];

			wp_register_script( 'rrssb', $url_path.'js/ext/rrssb.min.js', array( 'jquery' ), $plugin_version, true );	// in footer
			wp_enqueue_script( 'rrssb' );

			wp_register_style( 'rrssb', $url_path.'css/ext/rrssb.min.css', array(), $plugin_version );
			wp_enqueue_style( 'rrssb' );
		}

		public static function shorten_html_href( $html ) {
			return preg_replace_callback( '/(href=[\'"])([^\'"]+)([\'"])/', 
				array( __CLASS__, 'shorten_html_href_callback' ), $html );
		}

		protected static function shorten_html_href_callback( $matches ) {
			$wpsso = Wpsso::get_instance();
			if ( $wpsso->debug->enabled )
				$wpsso->debug->log( 'shortening href url '.$matches[2] );
			return $matches[1].apply_filters( $wpsso->cf['lca'].'_shorten_url',
				$matches[2], $wpsso->options['plugin_shortener'] ).$matches[3];
		}

		public function filter_messages_tooltip( $text, $idx ) {
			if ( strpos( $idx, 'tooltip-buttons_' ) !== 0 )
				return $text;
			switch ( $idx ) {
				case ( strpos( $idx, 'tooltip-buttons_pos_' ) === false ? false : true ):
					$text = sprintf( __( 'Social sharing buttons can be added to the top, bottom, or both. Each sharing button must also be enabled below (see the <em>%s</em> options).', 'wpsso-rrssb' ), _x( 'Show Button in', 'option label', 'wpsso-rrssb' ) );
					break;
				case 'tooltip-buttons_on_index':
					$text = __( 'Add the social sharing buttons to each entry of an index webpage (for example, <strong>non-static</strong> homepage, category, archive, etc.). Social sharing buttons are not included on index webpages by default.', 'wpsso-rrssb' );
					break;
				case 'tooltip-buttons_on_front':
					$text = __( 'If a static Post or Page has been selected for the homepage, you can add the social sharing buttons to that static homepage as well (default is unchecked).', 'wpsso-rrssb' );
					break;
				case 'tooltip-buttons_add_to':
					$text = __( 'Enabled social sharing buttons are added to the Post, Page, Media, and Product webpages by default. If your theme (or another plugin) supports additional custom post types, and you would like to include social sharing buttons on these webpages, check the appropriate option(s) here.', 'wpsso-rrssb' );
					break;
				case 'tooltip-buttons_use_social_style':
					$text = sprintf( __( 'Add the CSS of all <em>%1$s</em> to webpages (default is checked). The CSS will be <strong>minimized</strong>, and saved to a single stylesheet with a URL of <a href="%2$s">%3$s</a>. The minimized stylesheet can be enqueued or added directly to the webpage HTML.', 'wpsso-rrssb' ), _x( 'Sharing Styles', 'lib file description', 'wpsso-rrssb' ), WpssoRrssbSharing::$sharing_css_url, WpssoRrssbSharing::$sharing_css_url );
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
					$cache_exp = WpssoRrssbSharing::$cf['opt']['defaults']['plugin_sharing_buttons_cache_exp'];	// use original un-filtered value
					$cache_diff = $cache_exp ? human_time_diff( 0, $cache_exp ) : _x( 'disabled', 'option comment', 'wpsso-rrssb' );
					$text = __( 'The rendered HTML for social sharing buttons is saved to the WordPress transient cache to optimize performance.',
						'wpsso-rrssb' ).' '.sprintf( __( 'The suggested cache expiration value is %1$s seconds (%2$s).',
							'wpsso-rrssb' ), $cache_exp, $cache_diff );
					break;
			}
			return $text;
		}

		public function filter_messages_info( $text, $idx ) {
			if ( strpos( $idx, 'info-styles-rrssb-' ) !== 0 )
				return $text;
			$short = $this->p->cf['plugin']['wpsso']['short'];
			switch ( $idx ) {
				case 'info-styles-rrssb-sharing':
					$text = '<p>'.$short.' uses the \'wpsso-rrssb\' class to wrap all sharing buttons, and each button has it\'s own individual class name as well. This tab can be used to edit the CSS common to all sharing button locations.</p>';
					break;
				case 'info-styles-rrssb-content':
					$text = '<p>Social sharing buttons, enabled / added to the content text from the '.$this->p->util->get_admin_url( 'rrssb-buttons', 'Sharing Buttons' ).' settings page, are assigned the \'wpsso-rrssb-content\' class.</p>'.
					$this->get_info_css_example( 'content', true );
					break;
				case 'info-styles-rrssb-excerpt':
					$text = '<p>Social sharing buttons, enabled / added to the excerpt text from the '.$this->p->util->get_admin_url( 'rrssb-buttons', 'Sharing Buttons' ).' settings page, are assigned the \'wpsso-rrssb-excerpt\' class.</p>'.
					$this->get_info_css_example( 'excerpt', true );
					break;
				case 'info-styles-rrssb-sidebar':
					$text = '<p>Social sharing buttons, enabled / added to the CSS sidebar from the '.$this->p->util->get_admin_url( 'rrssb-buttons', 'Sharing Buttons' ).' settings page, are assigned the \'wpsso-rrssb-sidebar\' ID.</p> 
					<p>In order to achieve a vertical display, each un-ordered list (UL) contains a single list item (LI).</p>
					<p>Example:</p><pre>
div.wpsso-rrssb 
  #wpsso-rrssb-sidebar
    ul.rrssb-buttons
      li.rrssb-facebook {}</pre>';
					break;
				case 'info-styles-rrssb-shortcode':
					$text = '<p>Social sharing buttons added from a shortcode are assigned the \'wpsso-rrssb-shortcode\' class by default.</p>'.
					$this->get_info_css_example( 'admin_edit', true );
					break;
				case 'info-styles-rrssb-widget':
					$text = '<p>Social sharing buttons enabled in the '.$short.' widget are assigned the \'wpsso-rrssb-widget\' class (along with additional unique CSS ID names).</p> 
					<p>Example:</p><pre>
aside.widget 
  .wpsso-rrssb-widget 
    ul.rrssb-buttons
        li.rrssb-facebook {}</pre>';
					break;
				case 'info-styles-rrssb-admin_edit':
					$text = '<p>Social sharing buttons, enabled / added to the admin editing pages from the '.$this->p->util->get_admin_url( 'rrssb-buttons', 'Sharing Buttons' ).' settings page, are assigned the \'wpsso-rrssb-admin_edit\' class.</p>'.
					$this->get_info_css_example( 'admin_edit', true );
					break;
				case 'info-styles-rrssb-woo_short': 
					$text = '<p>Social sharing buttons, enabled / added to the WooCommerce Short Description text from the '.$this->p->util->get_admin_url( 'rrssb-buttons', 'Sharing Buttons' ).' settings page, are assigned the \'wpsso-rrssb-woo_short\' class.</p>'.
					$this->get_info_css_example( 'woo_short' );
      					break;
				case 'info-styles-rrssb-bbp_single': 
					$text = '<p>Social sharing buttons, enabled / added at the top of bbPress Single Templates from the '.$this->p->util->get_admin_url( 'rrssb-buttons', 'Sharing Buttons' ).' settings page, are assigned the \'wpsso-rrssb-bbp_single\' class.</p>'.
					$this->get_info_css_example( 'bbp_single' );
      					break;
				case 'info-styles-rrssb-bp_activity': 
					$text = '<p>Social sharing buttons, enabled / added to BuddyPress Activities from the '.$this->p->util->get_admin_url( 'rrssb-buttons', 'Sharing Buttons' ).' settings page, are assigned the \'wpsso-rrssb-bp_activity\' class.</p>'.
					$this->get_info_css_example( 'bp_activity' );
      					break;
			}
			return $text;
		}

		protected function get_info_css_example( $type ) {
			$text = '<p>Example:</p><pre>
div.wpsso-rrssb
  .wpsso-rrssb-'.$type.'
    ul.rrssb-buttons
      li.rrssb-facebook {}</pre>';
			return $text;
		}

	}
}

?>
