<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
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
			'sharing' => array(
				'show_on' => array( 
					'content' => 'Content',
					'excerpt' => 'Excerpt', 
					'sidebar' => 'CSS Sidebar', 
					'admin_edit' => 'Admin Edit',
				),
				'style' => array(
					'rrssb-sharing' => 'All Buttons',
					'rrssb-content' => 'Content',
					'rrssb-excerpt' => 'Excerpt',
					'rrssb-sidebar' => 'CSS Sidebar',
					'rrssb-admin_edit' => 'Admin Edit',
					'rrssb-shortcode' => 'Shortcode',
					'rrssb-widget' => 'Widget',
				),
			),
		);

		public function __construct( &$plugin, $plugin_filepath = WPSSORRSSB_FILEPATH ) {
			$this->p =& $plugin;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark( 'action / filter setup' );
			$this->plugin_filepath = $plugin_filepath;

			self::$sharing_css_name = 'sharing-styles-id-'.get_current_blog_id().'.min.css';
			self::$sharing_css_file = realpath( WPSSO_CACHEDIR.self::$sharing_css_name );	// fwrite() needs the real path
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
				'pre_filter_remove' => 2,	// remove the buttons filter from content, excerpt, etc.
				'post_filter_add' => 2,		// re-add the buttons filter to content, excerpt, etc.
			) );

			if ( is_admin() ) {
				if ( $this->have_buttons_for_type( 'admin_edit' ) )
					add_action( 'add_meta_boxes', array( &$this, 'add_post_buttons_metabox' ) );

				$this->p->util->add_plugin_filters( $this, array( 
					'save_options' => 3,		// update the sharing css file
					'option_type' => 4,		// identify option type for sanitation
					'post_cache_transients' => 4,	// clear transients on post save
				) );
			}
			if ( $this->p->debug->enabled )
				$this->p->debug->mark( 'action / filter setup' );
		}

		public function filter_get_defaults( $opts_def ) {
			$opts_def = array_merge( $opts_def, self::$cf['opt']['defaults'] );
			$opts_def = $this->p->util->push_add_to_options( $opts_def, array( 'buttons' => 'frontend' ) );
			$plugin_dir = trailingslashit( plugin_dir_path( $this->plugin_filepath ) );
			$url_path = parse_url( trailingslashit( plugins_url( '', $this->plugin_filepath ) ), PHP_URL_PATH );	// relative URL
			$style_tabs = apply_filters( $this->p->cf['lca'].'_style_tabs', self::$cf['sharing']['style'] );

			foreach ( $style_tabs as $id => $name ) {
				$css_file = realpath( $plugin_dir.'css/'.$id.'.css' );

				// css files are only loaded once (when variable is empty) into defaults to minimize disk i/o
				if ( empty( $opts_def['buttons_css_'.$id] ) ) {
					if ( ! file_exists( $css_file ) )
						continue;
					elseif ( ! $fh = @fopen( $css_file, 'rb' ) )
						$this->p->notice->err( 'Failed to open '.$css_file.' for reading.' );
					else {
						$css_data = fread( $fh, filesize( $css_file ) );
						fclose( $fh );
						if ( $this->p->debug->enabled )
							$this->p->debug->log( 'read css from file '.$css_file );
						foreach ( array( 
							'plugin_url_path' => $url_path,
						) as $macro => $value )
							$css_data = preg_replace( '/%%'.$macro.'%%/', $value, $css_data );
						$opts_def['buttons_css_'.$id] = $css_data;
					}
				}
			}
			return $opts_def;
		}

		public function filter_save_options( $opts, $options_name, $network ) {
			// update the combined and minimized social stylesheet
			if ( $network === false )
				$this->update_sharing_css( $opts );
			return $opts;
		}

		public function filter_option_type( $type, $key, $network, $mod ) {
			if ( ! empty( $type ) )
				return $type;

			// remove localization for more generic match
			if ( strpos( $key, '#' ) !== false )
				$key = preg_replace( '/#.*$/', '', $key );

			switch ( $key ) {
				// integer options that must be 1 or more (not zero)
				case ( preg_match( '/_order$/', $key ) ? true : false ):
					return 'pos_num';
					break;
				// text strings that can be blank
				case 'twitter_desc':
					return 'ok_blank';
					break;
			}
			return $type;
		}

		public function filter_post_cache_transients( $transients, $post_id, $lang = 'en_US', $sharing_url ) {
			if ( ! empty( self::$cf['sharing']['show_on'] ) &&
				is_array( self::$cf['sharing']['show_on'] ) ) {

				$transients['WpssoRrssbSharing::get_buttons'] = array();
				foreach( self::$cf['sharing']['show_on'] as $type_id => $type_name )
					$transients['WpssoRrssbSharing::get_buttons'][$type_id] = 'lang:'.$lang.'_type:'.$type_id.'_post:'.$post_id;
			}
			return $transients;
		}

		public function update_sharing_css( &$opts ) {
			if ( ! empty( $opts['buttons_use_social_css'] ) ) {
				$css_data = '';
				$style_tabs = apply_filters( $this->p->cf['lca'].'_style_tabs', self::$cf['sharing']['style'] );

				foreach ( $style_tabs as $id => $name )
					if ( isset( $opts['buttons_css_'.$id] ) )
						$css_data .= $opts['buttons_css_'.$id];

				$classname = apply_filters( $this->p->cf['lca'].'_load_lib', 
					false, 'ext/compressor', 'SuextMinifyCssCompressor' );

				if ( $classname !== false && class_exists( $classname ) )
					$css_data = call_user_func( array( $classname, 'process' ), $css_data );
				else {
					if ( is_admin() )
						$this->p->notice->err( 'Failed to load minify class SuextMinifyCssCompressor.', true );
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'failed to load minify class SuextMinifyCssCompressor' );
				}

				if ( $fh = @fopen( self::$sharing_css_file, 'wb' ) ) {
					if ( ( $written = fwrite( $fh, $css_data ) ) === false ) {
						if ( is_admin() )
							$this->p->notice->err( 'Failed writing to file '.self::$sharing_css_file.'.', true );
						if ( $this->p->debug->enabled )
							$this->p->debug->log( 'failed writing to '.self::$sharing_css_file );
					} elseif ( $this->p->debug->enabled )
						$this->p->debug->log( 'updated css file '.self::$sharing_css_file.' ('.$written.' bytes written)' );
					fclose( $fh );
				} else {
					if ( ! is_writable( WPSSO_CACHEDIR ) ) {
						if ( is_admin() )
							$this->p->notice->err( WPSSO_CACHEDIR.' is not writable.', true );
						if ( $this->p->debug->enabled )
							$this->p->debug->log( WPSSO_CACHEDIR.' is not writable', true );
					}
					if ( is_admin() )
						$this->p->notice->err( 'Failed to open file '.self::$sharing_css_file.' for writing.', true );
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'failed opening '.self::$sharing_css_file.' for writing' );
				}
			} else $this->unlink_sharing_css();
		}

		public function unlink_sharing_css() {
			if ( file_exists( self::$sharing_css_file ) ) {
				if ( ! @unlink( self::$sharing_css_file ) ) {
					if ( is_admin() )
						$this->p->notice->err( 'Error removing minimized stylesheet file'.
							' &mdash; does the web server have sufficient privileges?', true );
				}
			}
		}

		public function add_post_buttons_metabox() {
			if ( ! is_admin() )
				return;

			// get the current object / post type
			if ( ( $obj = $this->p->util->get_post_object() ) === false ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'exiting early: invalid object type' );
				return;
			}
			$post_type = get_post_type_object( $obj->post_type );

			if ( ! empty( $this->p->options[ 'buttons_add_to_'.$post_type->name ] ) ) {
				// add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
				add_meta_box( '_'.$this->p->cf['lca'].'_share', 'Sharing Buttons', 
					array( &$this, 'show_admin_sharing' ), $post_type->name, 'side', 'high' );
			}
		}

		public function show_admin_sharing( $post ) {
			$post_type = get_post_type_object( $post->post_type );	// since 3.0
			$post_type_name = ucfirst( $post_type->name );
			$css_data = $this->p->options['buttons_css_rrssb-admin_edit'];
			$classname = apply_filters( $this->p->cf['lca'].'_load_lib', false, 'ext/compressor', 'SuextMinifyCssCompressor' );
			if ( $classname !== false && class_exists( $classname ) )
				$css_data = call_user_func( array( $classname, 'process' ), $css_data );

			echo '<style type="text/css">'.$css_data.'</style>', "\n";
			echo '<table class="sucom-setting '.$this->p->cf['lca'].' side"><tr><td>';
			if ( get_post_status( $post->ID ) === 'publish' || 
				get_post_type( $post->ID ) === 'attachment' ) {
				$content = '';
				echo $this->get_buttons( $content, 'admin_edit' );
				if ( $this->p->debug->enabled )
					$this->p->debug->show_html( null, 'Debug Log' );
			} else echo '<p class="centered">The '.$post_type_name.' must be published<br/>before it can be shared.</p>';
			echo '</td></tr></table>';
		}

		public function filter_pre_filter_remove( $ret, $filter ) {
			return ( $this->remove_buttons_filter( $filter ) ? true : $ret );
		}

		public function filter_post_filter_add( $ret, $filter ) {
			return ( $this->add_buttons_filter( $filter ) ? true : $ret );
		}

		public function show_footer() {
			if ( $this->have_buttons_for_type( 'sidebar' ) )
				$this->show_sidebar_buttons();
			elseif ( $this->p->debug->enabled )
				$this->p->debug->log( 'no buttons enabled for sidebar' );
			if ( $this->p->debug->enabled )
				$this->p->debug->show_html( null, 'Debug Log' );
		}

		public function show_sidebar_buttons() {
			$lca = $this->p->cf['lca'];
			$text = '';	// variable must be passed by reference
			echo $this->get_buttons( $text, 'sidebar', false, '', array( 'container_each' => true ) );
			if ( $this->p->debug->enabled )
				$this->p->debug->show_html( null, 'Debug Log' );
		}

		private function set_objects() {
			foreach ( $this->p->cf['plugin']['wpssorrssb']['lib']['website'] as $id => $name ) {
				$classname = WpssoRrssbConfig::load_lib( false, 'website/'.$id, 'wpssorrssbsharing'.$id );
				if ( $classname !== false && class_exists( $classname ) ) {
					$this->website[$id] = new $classname( $this->p );
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $classname.' class loaded' );
				}
			}
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
						if ( is_admin() )
							$this->p->notice->err( self::$sharing_css_file.' is not readable.', true );
						if ( $this->p->debug->enabled )
							$this->p->debug->log( self::$sharing_css_file.' is not readable' );
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
				$this->p->debug->log( 'social css option is disabled' );
		}

		public function have_buttons_for_type( $type ) {
			if ( isset( $this->buttons_for_type[$type] ) )
				return $this->buttons_for_type[$type];
			foreach ( $this->p->cf['opt']['pre'] as $id => $pre )
				if ( ! empty( $this->p->options[$pre.'_on_'.$type] ) )	// check if button is enabled
					return $this->buttons_for_type[$type] = true;
			return $this->buttons_for_type[$type] = false;
		}

		public function add_buttons_filter( $type = 'the_content' ) {
			$ret = false;
			if ( method_exists( $this, 'get_buttons_'.$type ) ) {
				$ret = add_filter( $type, array( &$this, 'get_buttons_'.$type ), WPSSORRSSB_SOCIAL_PRIORITY );
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'buttons filter '.$type.' added ('.( $ret  ? 'true' : 'false' ).')' );
			} elseif ( $this->p->debug->enabled )
				$this->p->debug->log( 'get_buttons_'.$type.' method is missing' );
			return $ret;
		}

		public function remove_buttons_filter( $type = 'the_content' ) {
			$ret = false;
			if ( method_exists( $this, 'get_buttons_'.$type ) ) {
				$ret = remove_filter( $type, array( &$this, 'get_buttons_'.$type ), WPSSORRSSB_SOCIAL_PRIORITY );
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'buttons filter '.$type.' removed ('.( $ret  ? 'true' : 'false' ).')' );
			}
			return $ret;
		}

		public function get_buttons_the_excerpt( $text ) {
			$id = $this->p->cf['lca'].' excerpt-buttons';
			$text = preg_replace_callback( '/(<!-- '.$id.' begin -->.*<!-- '.$id.' end -->)(<\/p>)?/Usi', 
				array( __CLASS__, 'remove_paragraph_tags' ), $text );
			return $text;
		}

		public function get_buttons_get_the_excerpt( $text ) {
			return $this->get_buttons( $text, 'excerpt' );
		}

		public function get_buttons_the_content( $text ) {
			return $this->get_buttons( $text, 'content' );
		}

		public function get_buttons( &$text, $type = 'content', $use_post = true, $location = '', $atts = array() ) {

			// should we skip the sharing buttons for this content type or webpage?
			if ( is_admin() ) {
				if ( strpos( $type, 'admin_' ) !== 0 ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $type.' filter skipped: '.$type.' ignored with is_admin()'  );
					return $text;
				}
			} elseif ( is_feed() ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( $type.' filter skipped: no buttons allowed in rss feeds'  );
				return $text;
			} else {
				if ( ! is_singular() && empty( $this->p->options['buttons_on_index'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $type.' filter skipped: index page without buttons_on_index enabled' );
					return $text;
				} elseif ( is_front_page() && empty( $this->p->options['buttons_on_front'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $type.' filter skipped: front page without buttons_on_front enabled' );
					return $text;
				}
				if ( $this->is_post_buttons_disabled() ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $type.' filter skipped: sharing buttons disabled' );
					return $text;
				}
			}

			if ( ! $this->have_buttons_for_type( $type ) ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( $type.' filter exiting early: no sharing buttons enabled' );
				return $text;
			}

			$lca = $this->p->cf['lca'];
			$obj = $this->p->util->get_post_object( $use_post );
			$post_id = empty( $obj->ID ) || empty( $obj->post_type ) || 
				( ! is_singular() && $use_post === false ) ? 0 : $obj->ID;
			$source_id = $this->p->util->get_source_id( $type );
			$html = false;

			// fetch from the cache, if possible
			if ( $this->p->is_avail['cache']['transient'] ) {
				$cache_salt = __METHOD__.'('.apply_filters( $lca.'_buttons_cache_salt', 
					'lang:'.SucomUtil::get_locale().'_type:'.$type.'_post:'.$post_id.
						( empty( $post_id ) ? '_url:'.$this->p->util->get_sharing_url( $use_post,
							true, $source_id ) : '' ), $type, $use_post ).')';
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
				$css_type = $atts['css_id'] = 'rrssb-'.$type;
				$buttons_html = $this->get_html( $sorted_ids, $atts );

				if ( ! empty( $buttons_html ) ) {
					$html = '
<!-- '.$lca.' '.$css_type.' begin -->
<div class="'.$lca.'-rrssb'.
	( $use_post ? ' '.$lca.'-'.$css_type.'">' : '" id="'.$lca.'-'.$css_type.'">' ).'
'.$buttons_html.'</div><!-- .'.$lca.'-rrssb '.
	( $use_post ? '.' : '#' ).$lca.'-'.$css_type.' -->
<!-- '.$lca.' '.$css_type.' end -->'."\n\n";

					if ( $this->p->is_avail['cache']['transient'] ) {
						set_transient( $cache_id, $html, $this->p->cache->object_expire );
						if ( $this->p->debug->enabled )
							$this->p->debug->log( $cache_type.': '.$type.' html saved to transient '.
							$cache_id.' ('.$this->p->cache->object_expire.' seconds)' );
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
		public function get_html( &$ids = array(), &$atts = array() ) {
			$html_ret = '';
			$html_begin = "<ul class=\"rrssb-buttons clearfix\">\n";
			$html_end = "</ul><!-- .rrssb-buttons -->\n";
			foreach ( $ids as $id ) {
				$id = preg_replace( '/[^a-z]/', '', $id );	// sanitize the website object name, just in case
				if ( isset( $this->website[$id] ) ) {
					if ( method_exists( $this->website[$id], 'get_html' ) ) {
						$html_part = $this->website[$id]->get_html( $atts, $this->p->options )."\n";
						if ( trim( $html_part ) !== '' ) {
							if ( empty( $atts['container_each'] ) )
								$html_ret .= $html_part;
							else $html_ret .= $html_begin.$html_part.$html_end;
						}
					} elseif ( $this->p->debug->enabled )
						$this->p->debug->log( 'get_html method missing for '.$id );
				} elseif ( $this->p->debug->enabled )
					$this->p->debug->log( 'website object missing for '.$id );
			}
			if ( trim( $html_ret ) !== '' ) {
				if ( empty( $atts['container_each'] ) )
					$html_ret = $html_begin.$html_ret.$html_end;
			}
			return $html_ret;
		}

		public function is_post_buttons_disabled() {
			global $post;
			$ret = false;
			
			if ( isset( $this->post_buttons_disabled[$post->ID] ) )
				return $this->post_buttons_disabled[$post->ID];

			if ( ! empty( $post ) ) {
				$post_type = $post->post_type;
				if ( $this->p->mods['util']['post']->get_options( $post->ID, 'buttons_disabled' ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'post '.$post->ID.': sharing buttons disabled by custom meta option' );
					$ret = true;
				} elseif ( ! empty( $post_type ) && empty( $this->p->options['buttons_add_to_'.$post_type] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'post '.$post->ID.': sharing buttons not enabled for post type '.$post_type );
					$ret = true;
				}
			}

			return $this->post_buttons_disabled[$post->ID] = apply_filters( $this->p->cf['lca'].'_post_buttons_disabled', $ret, $post->ID );
		}

		public function get_defined_website_names() {
			$ids = array();
			foreach ( array_keys( $this->website ) as $id )
				$ids[$id] = $this->p->cf['*']['lib']['website'][$id];
			return $ids;
		}
	}
}

?>
