<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2018 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoRrssbFilters' ) ) {

	class WpssoRrssbFilters {

		private $p;

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array( 
				'get_defaults'      => 1,
				'get_md_defaults'   => 1,
			) );

			if ( is_admin() ) {

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
				if ( empty( $def_opts[ 'buttons_css_' . $id ] ) ) {

					if ( ! file_exists( $buttons_css_file ) ) {

						continue;

					} elseif ( ! $fh = @fopen( $buttons_css_file, 'rb' ) ) {

						if ( $this->p->debug->enabled ) {
							$this->p->debug->log( 'failed to open the css file ' . $buttons_css_file . ' for reading' );
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

						$def_opts[ 'buttons_css_' . $id ] = $buttons_css_data;
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
				WpssoRrssbSocial::update_sharing_css( $opts );
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

			if ( $metabox_id === $this->p->cf['meta'][ 'id' ] ) {
				SucomUtil::add_after_key( $tabs, 'media', 'buttons',
					_x( 'Share Buttons', 'metabox tab', 'wpsso-rrssb' ) );
			}

			return $tabs;
		}

		public function filter_post_cache_transient_keys( $transient_keys, $mod, $sharing_url, $mod_salt ) {

			$cache_md5_pre = $this->p->lca . '_b_';

			$transient_keys[] = array(
				'id'   => $cache_md5_pre . md5( 'WpssoRrssbSocial::get_buttons(' . $mod_salt . ')' ),
				'pre'   => $cache_md5_pre,
				'salt' => 'WpssoRrssbSocial::get_buttons(' . $mod_salt . ')',
			);

			$transient_keys[] = array(
				'id'   => $cache_md5_pre . md5( 'WpssoRrssbShortcodeSharing::do_shortcode(' . $mod_salt . ')' ),
				'pre'  => $cache_md5_pre,
				'salt' => 'WpssoRrssbShortcodeSharing::do_shortcode(' . $mod_salt . ')',
			);

			$transient_keys[] = array(
				'id'   => $cache_md5_pre . md5( 'WpssoRrssbWidgetSharing::widget(' . $mod_salt . ')' ),
				'pre'  => $cache_md5_pre,
				'salt' => 'WpssoRrssbWidgetSharing::widget(' . $mod_salt . ')',
			);

			return $transient_keys;
		}

		public function filter_messages_info( $text, $msg_key ) {

			if ( strpos( $msg_key, 'info-styles-rrssb-' ) !== 0 ) {
				return $text;
			}

			$short = $this->p->cf[ 'plugin' ][ 'wpssorrssb' ][ 'short' ];

			switch ( $msg_key ) {

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

		private function get_info_css_example( $type ) {

			$text = '<p>Example CSS:</p>
<pre>
div.wpsso-rrssb
  .wpsso-rrssb-' . $type . '
    ul.rrssb-buttons
      li.rrssb-facebook {}
</pre>';

			return $text;
		}

		public function filter_messages_tooltip( $text, $msg_key ) {

			if ( strpos( $msg_key, 'tooltip-buttons_' ) !== 0 ) {
				return $text;
			}

			switch ( $msg_key ) {

				case ( strpos( $msg_key, 'tooltip-buttons_pos_' ) === false ? false : true ):

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

					$text = sprintf( __( 'Add the CSS of all <em>%1$s</em> to webpages (default is checked). The CSS will be <strong>minified</strong>, and saved to a single stylesheet with a URL of <a href="%2$s">%3$s</a>. The minified stylesheet can be enqueued or added directly to the webpage HTML.', 'wpsso-rrssb' ), _x( 'Responsive Styles', 'lib file description', 'wpsso-rrssb' ), WpssoRrssbSocial::$sharing_css_url, WpssoRrssbSocial::$sharing_css_url );

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

		public function filter_messages_tooltip_plugin( $text, $msg_key ) {

			switch ( $msg_key ) {

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

		public function filter_status_gpl_features( $features, $ext, $info ) {

			if ( ! empty( $info[ 'lib' ]['submenu']['rrssb-styles'] ) ) {
				$features['(sharing) Sharing Stylesheet'] = array(
					'status' => empty( $this->p->options['buttons_use_social_style'] ) ? 'off' : 'on',
				);
			}

			if ( ! empty( $info[ 'lib' ]['shortcode']['sharing'] ) ) {
				$features['(sharing) Sharing Shortcode'] = array(
					'classname' => $ext . 'ShortcodeSharing',
				);
			}

			if ( ! empty( $info[ 'lib' ]['widget']['sharing'] ) ) {
				$features['(sharing) Sharing Widget'] = array(
					'classname' => $ext . 'WidgetSharing',
				);
			}

			return $features;
		}
	}
}
