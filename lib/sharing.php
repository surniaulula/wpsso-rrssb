<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
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
					'buttons_on_index' => 0,
					'buttons_on_front' => 0,
					'buttons_add_to_post' => 1,
					'buttons_add_to_page' => 1,
					'buttons_add_to_attachment' => 1,
					'buttons_pos_content' => 'bottom',
					'buttons_pos_excerpt' => 'bottom',
					'buttons_use_social_css' => 1,
					'buttons_enqueue_social_css' => 1,
					'buttons_css_rrssb-sharing' => '',		// all buttons
					'buttons_css_rrssb-content' => '',		// post/page content
					'buttons_css_rrssb-excerpt' => '',		// post/page excerpt
					'buttons_css_rrssb-admin_edit' => '',
					'buttons_css_rrssb-sidebar' => '',
					'buttons_css_rrssb-shortcode' => '',
					'buttons_css_rrssb-widget' => '',
				),
			),
		);

		public function __construct( &$plugin, $plugin_filepath = WPSSORRSSB_FILEPATH ) {
			$this->p =& $plugin;

			if ( $this->p->debug->enabled )
				$this->p->debug->mark( 'action / filter setup' );

			$this->plugin_filepath = $plugin_filepath;

			self::$sharing_css_name = 'rrssb-sharing-styles-id-'.get_current_blog_id().'.min.css';
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
				'get_defaults' => 1,		// add sharing options and css file contents to defaults
				'text_filter_has_added' => 2,	// re-add the buttons filter to content, excerpt, etc.
				'text_filter_has_removed' => 2,	// remove the buttons filter from content, excerpt, etc.
			) );

			if ( is_admin() ) {
				if ( $this->have_buttons_for_type( 'admin_edit' ) )
					add_action( 'add_meta_boxes', array( &$this, 'add_post_buttons_metabox' ) );

				$this->p->util->add_plugin_filters( $this, array( 
					'save_options' => 3,			// update the sharing css file
					'option_type' => 2,			// identify option type for sanitation
					'post_cache_transients' => 4,		// clear transients on post save
					'messages_tooltip_side' => 2,		// tooltip messages for side boxes
					'secondary_action_buttons' => 4,	// add a reload default styles button
				) );

				$this->p->util->add_plugin_filters( $this, array( 
					'status_gpl_features' => 3,		// include sharing, shortcode, and widget status
				), 10, 'wpssorrssb' );				// hook into the extension name instead

				$this->p->util->add_plugin_actions( $this, array(
					'load_setting_page_reload_default_sharing_rrssb_styles' => 4,
				) );
			}

			if ( $this->p->debug->enabled )
				$this->p->debug->mark( 'action / filter setup' );
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
			$tabs = apply_filters( $this->p->cf['lca'].'_rrssb_styles_tabs', 
				$this->p->cf['sharing']['rrssb-style'] );

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

		public function filter_save_options( $opts, $options_name, $network ) {
			// update the combined and minimized social stylesheet
			if ( $network === false )
				$this->update_sharing_css( $opts );
			return $opts;
		}

		public function filter_option_type( $type, $key ) {
			if ( ! empty( $type ) )
				return $type;

			// remove localization for more generic match
			if ( strpos( $key, '#' ) !== false )
				$key = preg_replace( '/#.*$/', '', $key );

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

		public function filter_post_cache_transients( $transients, $post_id, $lang = 'en_US', $sharing_url ) {
			$show_on = apply_filters( $this->p->cf['lca'].'_rrssb_buttons_show_on', 
				$this->p->cf['sharing']['show_on'], null );

			foreach( $show_on as $type_id => $type_name ) {
				$transients['WpssoRrssbSharing::get_buttons'][] = 'lang:'.$lang.'_type:'.$type_id.'_id:'.$post_id.'_name:post';
				$transients['WpssoRrssbSharing::get_buttons'][] = 'lang:'.$lang.'_type:'.$type_id.'_id:'.$post_id.'_name:post_prot:https';
			}

			return $transients;
		}

		public function filter_status_gpl_features( $features, $lca, $info ) {
			if ( ! empty( $info['lib']['submenu']['sharing-buttons'] ) )
				$features['Sharing Buttons'] = array( 
					'classname' => $lca.'Sharing',
				);
			if ( ! empty( $info['lib']['submenu']['sharing-styles'] ) )
				$features['Sharing Stylesheet'] = array(
					'status' => $this->p->options['buttons_use_social_css'] ? 'on' : 'off',
				);
			if ( ! empty( $info['lib']['shortcode']['sharing'] ) )
				$features['Sharing Shortcode'] = array(
					'classname' => $lca.'ShortcodeSharing',
				);
			if ( ! empty( $info['lib']['widget']['sharing'] ) )
				$features['Sharing Widget'] = array(
					'classname' => $lca.'WidgetSharing',
				);
			return $features;
		}

		public function filter_messages_tooltip_side( $text, $idx ) {
			switch ( $idx ) {
				case 'tooltip-side-sharing-buttons':
					$text = sprintf( __( 'Social sharing features include the <a href="%1$s">%2$s</a> and <a href="%3$s">%4$s</a> settings pages, the <em>%5$s</em> tab in the <em>%6$s</em> metabox, along with the social sharing shortcode and widget.', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'sharing-buttons' ), _x( 'Sharing Buttons', 'lib file description', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'sharing-styles' ), _x( 'Sharing Styles', 'lib file description', 'wpsso-rrssb' ), _x( 'Sharing Buttons', 'metabox tab', 'wpsso-rrssb' ), _x( 'Social Settings', 'metabox title', 'wpsso-rrssb' ) );
					break;
				case 'tooltip-side-sharing-stylesheet':
					$text = sprintf( __( 'A stylesheet for the social sharing buttons can be included in all webpages. You can enable or disable use of the stylesheet from the <a href="%1$s">%2$s</a> settings page.', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'sharing-styles' ), _x( 'Sharing Styles', 'lib file description', 'wpsso-rrssb' ) );
					break;
				case 'tooltip-side-sharing-shortcode':
					$text = sprintf( __( 'Support for shortcode(s) can be enabled and disabled on the <a href="%1$s">%2$s</a> settings page. Shortcodes are disabled by default to optimize performance and content processing.', 'wpsso-rrssb' ), $this->p->util->get_admin_url( 'advanced' ), _x( 'Advanced', 'lib file description', 'wpsso-rrssb' ) );
					break;
				case 'tooltip-side-sharing-widget':
					$text = sprintf( __( 'The sharing widget feature adds a <em>%s</em> widget to the WordPress Widgets settings page. The sharing widget shares the URL for the current webpage (and not individual items within an index / archive webpage, for example).', 'wpsso-rrssb' ),_x( 'WPSSO RRSSB', 'lib file description', 'wpsso-rrssb' ) );
					break;
			}
			return $text;
		}

		public function filter_secondary_action_buttons( $actions, $menu_id, $menu_name, $menu_lib ) {
			if ( $menu_id === 'sharing-styles' )
				$actions['reload_default_sharing_rrssb_styles'] = __( 'Reload Default Styles', 'submit button', 'wpsso-rrssb' );
			return $actions;
		}
		
		public function action_load_setting_page_reload_default_sharing_rrssb_styles( $pagehook, $menu_id, $menu_name, $menu_lib ) {
			$opts =& $this->p->options;
			$def_opts = $this->p->opt->get_defaults();
			$tabs = apply_filters( $this->p->cf['lca'].'_rrssb_styles_tabs', 
				$this->p->cf['sharing']['rrssb-style'] );

			foreach ( $tabs as $id => $name )
				if ( isset( $opts['buttons_css_'.$id] ) &&
					isset( $def_opts['buttons_css_'.$id] ) )
						$opts['buttons_css_'.$id] = $def_opts['buttons_css_'.$id];

			$this->update_sharing_css( $opts );
			$this->p->opt->save_options( WPSSO_OPTIONS_NAME, $opts, false );
			$this->p->notice->inf( __( 'All sharing styles have been reloaded with their default settings and saved.', 'wpsso-ssb' ) );
		}

		public function wp_enqueue_styles() {
			if ( ! empty( $this->p->options['buttons_use_social_css'] ) ) {
				if ( ! file_exists( self::$sharing_css_file ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'updating '.self::$sharing_css_file );
					$this->update_sharing_css( $this->p->options );
				}
				if ( ! empty( $this->p->options['buttons_enqueue_social_css'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'wp_enqueue_style = '.$this->p->cf['lca'].'_sharing_buttons' );
					wp_register_style( $this->p->cf['lca'].'_sharing_buttons', self::$sharing_css_url, 
						false, $this->p->cf['plugin'][$this->p->cf['lca']]['version'] );
					wp_enqueue_style( $this->p->cf['lca'].'_sharing_buttons' );
				} else {
					if ( ! is_readable( self::$sharing_css_file ) ) {
						if ( $this->p->debug->enabled )
							$this->p->debug->log( self::$sharing_css_file.' is not readable' );
						if ( is_admin() )
							$this->p->notice->err( sprintf( __( 'The %s file is not readable.',
								'wpsso-rrssb' ), self::$sharing_css_file ), true );
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
				$this->p->debug->log( 'buttons_use_social_css option is disabled' );
		}

		public function update_sharing_css( &$opts ) {

			if ( empty( $opts['buttons_use_social_css'] ) ) {
				$this->unlink_sharing_css();
				return;
			}

			$css_data = '';
			$tabs = apply_filters( $this->p->cf['lca'].'_rrssb_styles_tabs', 
				$this->p->cf['sharing']['rrssb-style'] );

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
						'wpsso-rrssb' ), true );
			}

			if ( $fh = @fopen( self::$sharing_css_file, 'wb' ) ) {
				if ( ( $written = fwrite( $fh, $css_data ) ) === false ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'failed writing to '.self::$sharing_css_file );
					if ( is_admin() )
						$this->p->notice->err( sprintf( __( 'Failed writing to the % file.',
							'wpsso-rrssb' ), self::$sharing_css_file ), true );
				} elseif ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'updated css file '.self::$sharing_css_file.' ('.$written.' bytes written)' );
					if ( is_admin() )
						$this->p->notice->inf( sprintf( __( 'Updated the <a href="%1$s">%2$s</a> stylesheet (%3$d bytes written).',
							'wpsso-rrssb' ), self::$sharing_css_url, self::$sharing_css_file, $written ), true );
				}
				fclose( $fh );
			} else {
				if ( ! is_writable( WPSSO_CACHEDIR ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( WPSSO_CACHEDIR.' is not writable', true );
					if ( is_admin() )
						$this->p->notice->err( sprintf( __( 'The %s folder is not writable.',
							'wpsso-rrssb' ), WPSSO_CACHEDIR ), true );
				}
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'failed opening '.self::$sharing_css_file.' for writing' );
				if ( is_admin() )
					$this->p->notice->err( sprintf( __( 'Failed to open file %s for writing.',
						'wpsso-rrssb' ), self::$sharing_css_file ), true );
			}
		}

		public function unlink_sharing_css() {
			if ( file_exists( self::$sharing_css_file ) ) {
				if ( ! @unlink( self::$sharing_css_file ) ) {
					if ( is_admin() )
						$this->p->notice->err( __( 'Error removing the minimized stylesheet &mdash; does the web server have sufficient privileges?', 'wpsso-rrssb' ), true );
				}
			}
		}

		public function add_post_buttons_metabox() {
			if ( ! is_admin() )
				return;

			// get the current object / post type
			if ( ( $post_obj = $this->p->util->get_post_object() ) === false ) {
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

		public function filter_text_filter_has_added( $ret, $filter_name ) {
			return ( $this->add_buttons_filter( $filter_name ) ? true : $ret );
		}

		public function filter_text_filter_has_removed( $ret, $filter_name ) {
			return ( $this->remove_buttons_filter( $filter_name ) ? true : $ret );
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
			$text = '';	// variable must be passed by reference
			echo $this->get_buttons( $text, 'sidebar', false, 	// $use_post = false
				'', array( 'container_each' => true ) );
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
				'wpsso-rrssb' ), ucfirst( $post_obj->post_type ) ).'</p>';
			echo '</td></tr></table>';
		}

		public function add_buttons_filter( $type = 'the_content' ) {
			$ret = false;
			if ( method_exists( $this, 'get_buttons_'.$type ) ) {
				$ret = add_filter( $type, array( &$this, 'get_buttons_'.$type ), WPSSORRSSB_SOCIAL_PRIORITY );
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'buttons filter '.$type.
						' added ('.( $ret  ? 'true' : 'false' ).')' );
			} elseif ( $this->p->debug->enabled )
				$this->p->debug->log( 'get_buttons_'.$type.' method is missing' );
			return $ret;
		}

		public function remove_buttons_filter( $type = 'the_content' ) {
			$ret = false;
			if ( method_exists( $this, 'get_buttons_'.$type ) ) {
				$ret = remove_filter( $type, array( &$this, 'get_buttons_'.$type ), WPSSORRSSB_SOCIAL_PRIORITY );
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'buttons filter '.$type.
						' removed ('.( $ret  ? 'true' : 'false' ).')' );
			}
			return $ret;
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

		public function get_buttons( &$text, $type = 'content', $use_post = true, $location = '', $atts = array() ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( is_admin() ) {
				if ( strpos( $type, 'admin_' ) !== 0 ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $type.' filter skipped: '.$type.' ignored in back-end'  );
					return $text;
				}
			} elseif ( $this->p->is_avail['amp_endpoint'] && is_amp_endpoint() ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( $type.' filter skipped: buttons not allowed in amp endpoint'  );
				return $text;
			} elseif ( is_feed() ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( $type.' filter skipped: buttons not allowed in rss feeds'  );
				return $text;
			} elseif ( ! is_singular() ) {
				if ( empty( $this->p->options['buttons_on_index'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $type.' filter skipped: index page without buttons_on_index enabled' );
					return $text;
				}
			} elseif ( is_front_page() ) {
				if ( empty( $this->p->options['buttons_on_front'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $type.' filter skipped: front page without buttons_on_front enabled' );
					return $text;
				}
			} elseif ( is_singular() ) {
				if ( $this->is_post_buttons_disabled() ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $type.' filter skipped: is singular and post buttons disabled' );
					return $text;
				}
			}

			if ( ! $this->have_buttons_for_type( $type ) ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( $type.' filter skipped: no sharing buttons enabled' );
				return $text;
			}

			$lca = $this->p->cf['lca'];
			$mod = $this->p->util->get_page_mod( $use_post );	// get post/user/term id, module name, and module object reference
			$src_id = $this->p->util->get_source_id( $type );
			$html = false;

			// fetch from the cache, if possible
			if ( $this->p->is_avail['cache']['transient'] ) {

				$sharing_url = $this->p->util->get_sharing_url( $use_post, true, $src_id );
				$cache_salt = __METHOD__.'('.apply_filters( $lca.'_buttons_cache_salt', 
					'lang:'.SucomUtil::get_locale().
					'_type:'.$type.'_id:'.$mod['id'].
					'_name:'.$mod['name'].
					( SucomUtil::is_https() ? '_prot:https' : '' ).
					( empty( $mod['id'] ) ? '_url:'.$sharing_url : '' ),
						$type, $use_post ).')';
				$cache_id = $lca.'_'.md5( $cache_salt );
				$cache_type = 'object cache';

				if ( $this->p->debug->enabled )
					$this->p->debug->log( $cache_type.': transient salt '.$cache_salt );

				$html = get_transient( $cache_id );
			}

			if ( $html !== false ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( $cache_type.': '.$type.' html retrieved from transient '.$cache_id );
			} else {
				// sort enabled sharing buttons by their preferred order
				$sorted_ids = array();
				foreach ( $this->p->cf['opt']['pre'] as $id => $pre )
					if ( ! empty( $this->p->options[$pre.'_on_'.$type] ) )
						$sorted_ids[ zeroise( $this->p->options[$pre.'_order'], 3 ).'-'.$id ] = $id;
				ksort( $sorted_ids );

				$atts['use_post'] = $use_post;
				$atts['css_id'] = $css_type = 'rrssb-'.$type;

				$buttons_html = $this->get_html( $sorted_ids, $atts, $mod );

				if ( trim( $buttons_html ) ) {
					$html = '
<!-- '.$lca.' '.$css_type.' begin -->
<div class="'.$lca.'-rrssb'.
	( $use_post ? ' '.$lca.'-'.$css_type.'">' : '" id="'.$lca.'-'.$css_type.'">' ).
$buttons_html."\n".
'</div><!-- .'.$lca.'-rrssb '.
	( $use_post ? '.' : '#' ).$lca.'-'.$css_type.' -->
<!-- '.$lca.' '.$css_type.' end -->'."\n\n";

					if ( $this->p->is_avail['cache']['transient'] ) {
						set_transient( $cache_id, $html, $this->p->options['plugin_object_cache_exp'] );
						if ( $this->p->debug->enabled )
							$this->p->debug->log( $cache_type.': '.$type.' html saved to transient '.
								$cache_id.' ('.$this->p->options['plugin_object_cache_exp'].' seconds)' );
					}
				}
			}

			if ( empty( $location ) ) {
				$location = empty( $this->p->options['buttons_pos_'.$type] ) ? 
					'bottom' : $this->p->options['buttons_pos_'.$type];
			} 

			switch ( $location ) {
				case 'top': 
					$text = $html.$text; 
					break;
				case 'bottom': 
					$text = $text.$html; 
					break;
				case 'both': 
					$text = $html.$text.$html; 
					break;
			}

			return $text.( $this->p->debug->enabled ? $this->p->debug->get_html() : '' );
		}

		// get_html() can be called by a widget, shortcode, function, filter hook, etc.
		public function get_html( array &$ids, array &$atts, &$mod = false ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$lca = $this->p->cf['lca'];
			$use_post = isset( $atts['use_post'] ) ?
				$atts['use_post'] : true;
			if ( ! is_array( $mod ) )
				$mod = $this->p->util->get_page_mod( $use_post );	// get post/user/term id, module name, and module object reference

			$html_ret = '';
			$html_begin = "<ul class=\"rrssb-buttons clearfix\">\n";
			$html_end = "</ul><!-- .rrssb-buttons -->\n";

			foreach ( $ids as $id ) {
				if ( isset( $this->website[$id] ) ) {
					if ( method_exists( $this->website[$id], 'get_html' ) ) {
						if ( $this->allow_for_platform( $id ) ) {
							$html_part = $this->website[$id]->get_html( $atts, $this->p->options, $mod )."\n";
							if ( trim( $html_part ) !== '' ) {
								if ( empty( $atts['container_each'] ) )
									$html_ret .= $html_part;
								else $html_ret .= '<!-- container_each -->'.
									$html_begin.$html_part.$html_end;
							}
						} elseif ( $this->p->debug->enabled )
							$this->p->debug->log( $id.' not allowed for platform' );
					} elseif ( $this->p->debug->enabled )
						$this->p->debug->log( 'get_html method missing for '.$id );
				} elseif ( $this->p->debug->enabled )
					$this->p->debug->log( 'website object missing for '.$id );
			}
			$html_ret = trim( $html_ret );
			if ( ! empty( $html_ret ) ) {
				if ( empty( $atts['container_each'] ) )
					$html_ret = $html_begin.$html_ret.$html_end;
			}
			return $html_ret;
		}

		public function have_buttons_for_type( $type ) {
			if ( isset( $this->buttons_for_type[$type] ) )
				return $this->buttons_for_type[$type];
			foreach ( $this->p->cf['opt']['pre'] as $id => $pre ) {
				if ( ! empty( $this->p->options[$pre.'_on_'.$type] ) &&		// check if button is enabled
					$this->allow_for_platform( $id ) )			// check if allowed on platform
						return $this->buttons_for_type[$type] = true;
			}
			return $this->buttons_for_type[$type] = false;
		}

		public function allow_for_platform( $id ) {
			$pre = isset( $this->p->cf['opt']['pre'][$id] ) ?
				$this->p->cf['opt']['pre'][$id] : $id;
			if ( isset( $this->p->options[$pre.'_platform'] ) ) {
				switch( $this->p->options[$pre.'_platform'] ) {
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

			if ( ( $post_obj = $this->p->util->get_post_object() ) === false ) {
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

		public function enqueue_rrssb_ext( $hook ) {
			$url_path = WPSSORRSSB_URLPATH;
			$plugin_version = $this->p->cf['plugin']['wpssorrssb']['version'];

			wp_register_script( 'rrssb',
				$url_path.'js/ext/rrssb.min.js', 
					array( 'jquery' ), $plugin_version, true );	// in footer
			wp_enqueue_script( 'rrssb' );

			wp_register_style( 'rrssb',
				$url_path.'css/ext/rrssb.min.css', 
					array(), $plugin_version );
			wp_enqueue_style( 'rrssb' );
		}
	}
}

?>
