<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2020 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbFilters' ) ) {

	class WpssoRrssbFilters {

		private $p;
		private $msgs;

		public function __construct( &$plugin ) {

			/**
			 * Just in case - prevent filters from being hooked and executed more than once.
			 */
			static $do_once = null;

			if ( true === $do_once ) {
				return;	// Stop here.
			}

			$do_once = true;

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array( 
				'option_type'            => 2,
				'save_options'           => 4,
				'get_defaults'           => 1,
				'get_md_defaults'        => 1,
				'rename_options_keys'    => 1,
			) );

			if ( is_admin() ) {

				if ( ! class_exists( 'WpssoRrssbFiltersMessages' ) ) {
					require_once WPSSORRSSB_PLUGINDIR . 'lib/filters-messages.php';
				}

				$this->msgs = new WpssoRrssbFiltersMessages( $plugin );

				$this->p->util->add_plugin_filters( $this, array( 
					'plugin_cache_rows'         => 3,
					'post_custom_meta_tabs'     => 3,
					'post_buttons_rows'         => 4,
					'post_cache_transient_keys' => 4,
				), $prio = 40 );	// Run after WPSSO Core's own Standard / Premium filters.

				$this->p->util->add_plugin_filters( $this, array( 
					'status_std_features' => 3,
				), $prio = 10, $ext = 'wpssorrssb' );	// Hook into our own filters.
			}
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

		public function filter_save_options( $opts, $options_name, $network, $doing_upgrade ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			if ( $network ) {
				return $opts;	// Nothing to do.
			}

			/**
			 * Update the combined and minified social stylesheet.
			 */
			WpssoRrssbSocial::update_sharing_css( $opts );

			return $opts;
		}

		public function filter_get_defaults( $def_opts ) {

			/**
			 * Add options using a key prefix array and post type names.
			 */
			$def_opts     = $this->p->util->add_ptns_to_opts( $def_opts, 'buttons_add_to', 1 );
			$rel_url_path = parse_url( WPSSORRSSB_URLPATH, PHP_URL_PATH );	// Returns a relative URL.
			$styles       = apply_filters( $this->p->lca . '_rrssb_styles', $this->p->cf[ 'sharing' ][ 'rrssb_styles' ] );

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

		public function filter_rename_options_keys( $options_keys ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$options_keys[ 'wpssorrssb' ] = array(
				14 => array(
					'email_cap_len'         => 'email_caption_max_len',
					'twitter_cap_len'       => 'twitter_caption_max_len',
					'pin_cap_len'           => 'pin_caption_max_len',
					'linkedin_cap_len'      => 'linkedin_caption_max_len',
					'reddit_cap_len'        => 'reddit_caption_max_len',
					'tumblr_cap_len'        => 'tumblr_caption_max_len',
					'email_cap_hashtags'    => 'email_caption_hashtags',
					'twitter_cap_hashtags'  => 'twitter_caption_hashtags',
					'pin_cap_hashtags'      => 'pin_caption_hashtags',
					'linkedin_cap_hashtags' => 'linkedin_caption_hashtags',
					'reddit_cap_hashtags'   => 'reddit_caption_hashtags',
					'tumblr_cap_hashtags'   => 'tumblr_caption_hashtags',
				),
				20 => array(
					'gp_order'      => '',
					'gp_platform'   => '',
					'gp_rrssb_html' => '',
				),
				23 => array(
					'plugin_wpssorrssb_tid' => '',
				),
			);

			$show_on = apply_filters( $this->p->lca . '_rrssb_buttons_show_on', $this->p->cf[ 'sharing' ][ 'show_on' ], 'gp' );

			foreach ( $show_on as $opt_suffix => $short_desc ) {
				$options_keys[ 'wpssorrssb' ][ 20 ][ 'gp_on_' . $opt_suffix ] = '';
			}

			return $options_keys;
		}

		public function filter_plugin_cache_rows( $table_rows, $form, $network = false ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			SucomUtil::add_after_key( $table_rows, 'plugin_types_cache_exp', array( 

				'plugin_sharing_buttons_cache_exp' => '' .
					$form->get_th_html( _x( 'Sharing Buttons HTML Cache Expiry',
						'option label', 'wpsso-rrssb' ), null, 'plugin_sharing_buttons_cache_exp' ) . 
					'<td nowrap>' . $form->get_input( 'plugin_sharing_buttons_cache_exp', 'medium' ) . ' ' . 
					_x( 'seconds (0 to disable)', 'option comment', 'wpsso-rrssb' ) . '</td>' . 
					WpssoAdmin::get_option_site_use( 'plugin_sharing_buttons_cache_exp', $form, $network ),
			) );

			return $table_rows;
		}

		public function filter_post_custom_meta_tabs( $tabs, $mod, $metabox_id ) {

			if ( $metabox_id === $this->p->cf[ 'meta' ][ 'id' ] ) {
				SucomUtil::add_after_key( $tabs, 'media', 'buttons', _x( 'Share Buttons', 'metabox tab', 'wpsso-rrssb' ) );
			}

			return $tabs;
		}

		public function filter_post_buttons_rows( $table_rows, $form, $head, $mod ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			if ( empty( $mod[ 'post_status' ] ) || $mod[ 'post_status' ] === 'auto-draft' ) {

				$table_rows[ 'save_draft' ] = '<td><blockquote class="status-info"><p class="centered">' . 
					sprintf( __( 'Save a draft version or publish the %s to display these options.',
						'wpsso-rrssb' ), SucomUtil::titleize( $mod[ 'post_type' ] ) ) . '</p></blockquote></td>';

				return $table_rows;	// Abort.
			}

			/**
			 * Default option values.
			 */
			$def_cap_title   = $this->p->page->get_caption( 'title', 0, $mod, true, false );

			/**
			 * Disable Buttons Checkbox
			 */
			$form_rows[ 'buttons_disabled' ] = array(
				'th_class' => 'medium',
				'label'    => _x( 'Disable Sharing Buttons', 'option label', 'wpsso-rrssb' ),
				'tooltip'  => 'post-buttons_disabled',
				'content'  => $form->get_checkbox( 'buttons_disabled' ),
			);

			/**
			 * Email
			 */
			$email_caption_max_len  = $this->p->options[ 'email_caption_max_len' ];
			$email_caption_hashtags = $this->p->options[ 'email_caption_hashtags' ];
			$email_caption_text     = $this->p->page->get_caption( 'excerpt', $email_caption_max_len, $mod, true, $email_caption_hashtags, true, 'none' );

			$form_rows[ 'subsection_email' ] = array(
				'td_class' => 'subsection',
				'col_span' => '3',
				'header'   => 'h4',
				'label'    => 'Email',
			);

			$form_rows[ 'email_title' ] = array(
				'th_class' => 'medium',
				'label'    => _x( 'Email Subject', 'option label', 'wpsso-rrssb' ),
				'tooltip'  => 'post-email_title',
				'content'  => $form->get_input( 'email_title', 'wide', '', 0, $def_cap_title ),
			);

			$form_rows[ 'email_desc' ] = array(
				'th_class' => 'medium',
				'label'    => _x( 'Email Message', 'option label', 'wpsso-rrssb' ),
				'tooltip'  => 'post-email_desc',
				'content'  => $form->get_textarea( 'email_desc', '', '', $email_caption_max_len, $email_caption_text ),
			);

			/**
			 * Twitter
			 */
			$twitter_caption_type     = empty( $this->p->options[ 'twitter_caption' ] ) ? 'title' : $this->p->options[ 'twitter_caption' ];
			$twitter_caption_max_len  = WpssoRrssbSocial::get_tweet_max_len();
			$twitter_caption_hashtags = $this->p->options[ 'twitter_caption_hashtags' ];
			$twitter_caption_text     = $this->p->page->get_caption( $twitter_caption_type, $twitter_caption_max_len, $mod, true, $twitter_caption_hashtags );

			$form_rows[ 'subsection_twitter' ] = array(
				'td_class' => 'subsection',
				'col_span' => '3',
				'header'   => 'h4',
				'label'    => 'Twitter',
			);

			$form_rows[ 'twitter_desc' ] = array(
				'th_class' => 'medium',
				'label'    => _x( 'Tweet Text', 'option label', 'wpsso-rrssb' ),
				'tooltip'  => 'post-twitter_desc',
				'content'  => $form->get_textarea( 'twitter_desc', '', '', $twitter_caption_max_len, $twitter_caption_text ),
			);

			/**
			 * Pinterest
			 */
			$pin_caption_max_len  = $this->p->options[ 'pin_caption_max_len' ];
			$pin_caption_hashtags = $this->p->options[ 'pin_caption_hashtags' ];
			$pin_caption_text     = $this->p->page->get_caption( 'excerpt', $pin_caption_max_len, $mod, true, $pin_caption_hashtags );
			$pin_media            = $this->p->og->get_media_info( $this->p->lca . '-pinterest-button', array( 'pid', 'img_url' ), $mod, 'schema' );

			/**
			 * Get the smaller thumbnail image as a preview image.
			 */
			if ( ! empty( $pin_media[ 'pid' ] ) ) {
				$pin_media[ 'img_url' ] = $this->p->media->get_attachment_image_url( $pin_media[ 'pid' ], 'thumbnail', false );
			}

			$form_rows[ 'subsection_pinterest' ] = array(
				'td_class' => 'subsection',
				'col_span' => '3',
				'header'   => 'h4',
				'label'    => 'Pinterest',
			);

			$form_rows[ 'pin_desc' ] = array(
				'th_class' => 'medium',
				'td_class' => 'top',
				'label'    => _x( 'Pinterest Caption', 'option label', 'wpsso-rrssb' ),
				'tooltip'  => 'post-pin_desc',
				'content'  => $form->get_textarea( 'pin_desc', '', '', $pin_caption_max_len, $pin_caption_text ) . 
					( empty( $pin_media[ 'img_url' ] ) ? '' : '</td><td class="top thumb_preview">' .
						'<img src="' . $pin_media[ 'img_url' ] . '">' ),
			);

			/**
			 * Other Title / Caption Input
			 */
			foreach ( array(
				'linkedin' => 'LinkedIn',
				'reddit' => 'Reddit',
				'tumblr' => 'Tumblr',
			) as $opt_pre => $name ) {

				$other_caption_max_len  = $this->p->options[ $opt_pre . '_caption_max_len' ];
				$other_caption_hashtags = $this->p->options[ $opt_pre . '_caption_hashtags' ];
				$other_caption_text     = $this->p->page->get_caption( 'excerpt', $other_caption_max_len, $mod, true, $other_caption_hashtags );

				$form_rows[ 'subsection_' . $opt_pre ] = array(
					'td_class' => 'subsection',
					'col_span' => '3',
					'header'   => 'h4',
					'label'    => $name,
				);

				$form_rows[ $opt_pre . '_title' ] = array(
					'th_class' => 'medium',
					'label'    => sprintf( _x( '%s Title', 'option label', 'wpsso-rrssb' ), $name ),
					'tooltip'  => 'post-' . $opt_pre . '_title',
					'content'  => $form->get_input( $opt_pre . '_title', 'wide', '', 0, $def_cap_title ),
				);

				$form_rows[ $opt_pre . '_desc' ] = array(
					'th_class' => 'medium',
					'label'    => sprintf( _x( '%s Caption', 'option label', 'wpsso-rrssb' ), $name ),
					'tooltip'  => 'post-' . $opt_pre . '_desc',
					'content'  => $form->get_textarea( $opt_pre . '_desc', '', '', $other_caption_max_len, $other_caption_text ),
				);
			}

			return $form->get_md_form_rows( $table_rows, $form_rows, $head, $mod );
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

		public function filter_status_std_features( $features, $ext, $info ) {

			if ( ! empty( $info[ 'lib' ][ 'submenu' ][ 'rrssb-styles' ] ) ) {
				$features[ '(sharing) Sharing Stylesheet' ] = array(
					'status' => empty( $this->p->options[ 'buttons_use_social_style' ] ) ? 'off' : 'on',
				);
			}

			if ( ! empty( $info[ 'lib' ][ 'shortcode' ][ 'sharing' ] ) ) {
				$features[ '(sharing) Sharing Shortcode' ] = array(
					'classname' => $ext . 'ShortcodeSharing',
				);
			}

			if ( ! empty( $info[ 'lib' ][ 'widget' ][ 'sharing' ] ) ) {
				$features[ '(sharing) Sharing Widget' ] = array(
					'classname' => $ext . 'WidgetSharing',
				);
			}

			return $features;
		}
	}
}
