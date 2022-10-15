<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2022 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbFiltersUpgrade' ) ) {

	class WpssoRrssbFiltersUpgrade {

		private $p;	// Wpsso class object.
		private $a;	// WpssoRrssb class object.

		/**
		 * Instantiated by WpssoRrssbFilters->__construct().
		 */
		public function __construct( &$plugin, &$addon ) {

			$this->p =& $plugin;
			$this->a =& $addon;

			$this->p->util->add_plugin_filters( $this, array(
				'rename_options_keys' => 1,
				'upgraded_options'    => 2,
			) );
		}

		public function filter_rename_options_keys( $options_keys ) {

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
				32 => array(
					'email_order'    => 'email_button_order',
					'fb_order'       => 'fb_button_order',
					'linkedin_order' => 'linkedin_button_order',
					'pin_order'      => 'pin_button_order',
					'pocket_order'   => 'pocket_button_order',
					'reddit_order'   => 'reddit_button_order',
					'tumblr_order'   => 'tumblr_button_order',
					'twitter_order'  => 'twitter_button_order',
					'vk_order'       => 'vk_button_order',
					'wa_order'       => 'wa_button_order',
				),
				33 => array(
					'email_platform'    => '',	// Deprecated on 2020/10/02.
					'fb_platform'       => '',	// Deprecated on 2020/10/02.
					'linkedin_platform' => '',	// Deprecated on 2020/10/02.
					'pin_platform'      => '',	// Deprecated on 2020/10/02.
					'pocket_platform'   => '',	// Deprecated on 2020/10/02.
					'reddit_platform'   => '',	// Deprecated on 2020/10/02.
					'tumblr_platform'   => '',	// Deprecated on 2020/10/02.
					'twitter_platform'  => '',	// Deprecated on 2020/10/02.
					'vk_platform'       => '',	// Deprecated on 2020/10/02.
					'wa_platform'       => '',	// Deprecated on 2020/10/02.
				),
				34 => array(
					'buttons_on_index' => 'buttons_on_archive',
				),
				39 => array(
					'email_caption_hashtags'    => '',
					'linkedin_caption_hashtags' => '',
					'pin_caption_hashtags'      => '',
					'reddit_caption_hashtags'   => '',
					'tumblr_caption_hashtags'   => '',
					'buttons_css_rrssb-sharing' => 'buttons_css_rrssb-common',
				),
				45 => array(
					'buttons_pos_woo_short'       => 'buttons_pos_wc_short_desc',
					'buttons_pos_woo_short_desc'  => 'buttons_pos_wc_short_desc',
					'buttons_css_rrssb-woo_short' => '',		// Remove and re-load new key value from default.
					'buttons_css_rrssb-wc_short_desc' => '',	// Remove and re-load new key value from default.
				),
			);

			/**
			 * Remove all G+ buttons.
			 */
			$show_on = apply_filters( 'wpsso_rrssb_buttons_show_on', $this->p->cf[ 'sharing' ][ 'show_on' ] );

			foreach ( $show_on as $opt_suffix => $short_desc ) {

				$options_keys[ 'wpssorrssb' ][ 20 ][ 'gp_on_' . $opt_suffix ] = '';
			}

			/**
			 * Rename show button from 'woo_short' to 'wc_short_desc'.
			 */
			foreach ( $this->p->cf[ 'opt' ][ 'cm_prefix' ] as $cm_id => $opt_pre ) {

				$options_keys[ 'wpssorrssb' ][ 44 ][ $opt_pre . '_on_woo_short' ]      = $opt_pre . '_on_wc_short_desc';
				$options_keys[ 'wpssorrssb' ][ 44 ][ $opt_pre . '_on_woo_short_desc' ] = $opt_pre . '_on_wc_short_desc';
			}

			return $options_keys;
		}

		public function filter_upgraded_options( $opts, $defs ) {

			/**
			 * Get the current options version number for checks to follow.
			 */
			$prev_version = $this->p->opt->get_version( $opts, 'wpssorrssb' );	// Returns 'opt_version'.

			/**
			 * Reload the defaults styles if older than WPSSO RRSSB v4.0.0 (options version 31).
			 */
			if ( $prev_version > 0 && $prev_version <= 31 ) {

				$styles = apply_filters( 'wpsso_rrssb_styles', $this->p->cf[ 'sharing' ][ 'rrssb_styles' ] );

				foreach ( $styles as $id => $name ) {

					if ( isset( $opts[ 'buttons_css_' . $id ] ) && isset( $defs[ 'buttons_css_' . $id ] ) ) {

						$opts[ 'buttons_css_' . $id ] = $defs[ 'buttons_css_' . $id ];
					}
				}

				$this->p->notice->upd( __( 'The default responsive styles CSS has been reloaded and saved.', 'wpsso-rrssb' ) );

				/**
				 * Update the combined and minified social stylesheet.
				 */
				WpssoRrssbSocial::update_sharing_css( $opts );
			}

			return $opts;
		}
	}
}
