<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2018 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoRrssbConfig' ) ) {

	class WpssoRrssbConfig {

		public static $cf = array(
			'plugin' => array(
				'wpssorrssb' => array(			// Plugin acronym.
					'version' => '1.7.1-rc.1',		// Plugin version.
					'opt_version' => '13',		// Increment when changing default option values.
					'short' => 'WPSSO RRSSB',	// Short plugin name.
					'name' => 'WPSSO Ridiculously Responsive Social Sharing Buttons',
					'desc' => 'WPSSO Core add-on to provide Ridiculously Responsive (SVG) Social Sharing Buttons in your content, excerpts, CSS sidebar, widget, shortcode, post edit pages, etc.',
					'slug' => 'wpsso-rrssb',
					'base' => 'wpsso-rrssb/wpsso-rrssb.php',
					'update_auth' => 'tid',
					'text_domain' => 'wpsso-rrssb',
					'domain_path' => '/languages',
					'req' => array(
						'short' => 'WPSSO Core',
						'name' => 'WPSSO Core',
						'min_version' => '4.7.0-rc.1',
					),
					'img' => array(
						'icons' => array(
							'low' => 'images/icon-128x128.png',
							'high' => 'images/icon-256x256.png',
						),
					),
					'lib' => array(
						'submenu' => array(	// Note that submenu elements must have unique keys.
							'rrssb-buttons' => 'Responsive Buttons',
							'rrssb-styles' => 'Responsive Styles',
						),
						'shortcode' => array(
							'sharing' => 'Sharing Shortcode',
						),
						'widget' => array(
							'sharing' => 'Sharing Widget',
						),
						'website' => array(
							'email' => 'Email', 
							'facebook' => 'Facebook', 
							'gplus' => 'GooglePlus', 
							'twitter' => 'Twitter', 
							'pinterest' => 'Pinterest', 
							'linkedin' => 'LinkedIn', 
							'reddit' => 'Reddit', 
							'pocket' => 'Pocket', 
							'tumblr' => 'Tumblr', 
							'vk' => 'VK', 
							'whatsapp' => 'WhatsApp', 
						),
						'gpl' => array(
							'admin' => array(
								'sharing' => 'Extend Sharing Settings',
							),
							'ecom' => array(
								'woocommerce' => '(plugin) WooCommerce',
							),
							'forum' => array(
								'bbpress' => '(plugin) bbPress',
							),
							'social' => array(
								'buddypress' => '(plugin) BuddyPress',
							),
						),
						'pro' => array(
							'admin' => array(
								'sharing' => 'Extend Sharing Settings',
							),
							'ecom' => array(
								'woocommerce' => '(plugin) WooCommerce',
							),
							'forum' => array(
								'bbpress' => '(plugin) bbPress',
							),
							'social' => array(
								'buddypress' => '(plugin) BuddyPress',
							),
						),
					),
				),
			),
			'wp' => array(				// WordPress
				'transient' => array(
					'wpsso_b_' => array(
						'label' => 'Buttons HTML',
						'text_domain' => 'wpsso-rrssb',
						'opt_key' => 'plugin_sharing_buttons_cache_exp',
						'filter' => 'wpsso_cache_expire_sharing_buttons',
					),
				),
			),
			'sharing' => array(
				'show_on' => array( 
					'content' => 'Content',
					'excerpt' => 'Excerpt', 
					'sidebar' => 'CSS Sidebar', 
					'admin_edit' => 'Admin Edit',
				),
				'force_prot' => array( 
					'http' => 'HTTP',
					'https' => 'HTTPS',
				),
				'rrssb_styles' => array(
					'rrssb-sharing' => 'All Buttons',
					'rrssb-content' => 'Content',
					'rrssb-excerpt' => 'Excerpt',
					'rrssb-sidebar' => 'CSS Sidebar',
					'rrssb-admin_edit' => 'Admin Edit',
					'rrssb-shortcode' => 'Shortcode',
					'rrssb-widget' => 'Widget',
				),
				'position' => array(
					'top' => 'Top',
					'bottom' => 'Bottom',
					'both' => 'Top and Bottom',
				),
				'platform' => array(
					'desktop' => 'Desktop Only',
					'mobile' => 'Mobile Only',
					'any' => 'Any Platform',
				),
			),
		);

		public static function get_version( $add_slug = false ) {
			$ext = 'wpssorrssb';
			$info =& self::$cf['plugin'][$ext];
			return $add_slug ? $info['slug'].'-'.$info['version'] : $info['version'];
		}

		public static function set_constants( $plugin_filepath ) { 

			if ( defined( 'WPSSORRSSB_VERSION' ) ) {	// Define constants only once.
				return;
			}

			define( 'WPSSORRSSB_VERSION', self::$cf['plugin']['wpssorrssb']['version'] );						
			define( 'WPSSORRSSB_FILEPATH', $plugin_filepath );						
			define( 'WPSSORRSSB_PLUGINDIR', trailingslashit( realpath( dirname( $plugin_filepath ) ) ) );
			define( 'WPSSORRSSB_PLUGINSLUG', self::$cf['plugin']['wpssorrssb']['slug'] );	// wpsso-rrssb
			define( 'WPSSORRSSB_PLUGINBASE', self::$cf['plugin']['wpssorrssb']['base'] );	// wpsso-rrssb/wpsso-rrssb.php
			define( 'WPSSORRSSB_URLPATH', trailingslashit( plugins_url( '', $plugin_filepath ) ) );

			self::set_variable_constants();
		}

		public static function set_variable_constants( $var_const = null ) {

			if ( null === $var_const ) {
				$var_const = self::get_variable_constants();
			}

			foreach ( $var_const as $name => $value ) {
				if ( ! defined( $name ) ) {
					define( $name, $value );
				}
			}
		}

		public static function get_variable_constants() { 

			$var_const = array();

			$var_const['WPSSORRSSB_SHARING_SHORTCODE_NAME'] = 'rrssb';

			/**
			 * WPSSO RRSSB hook priorities
			 */
			$var_const['WPSSORRSSB_SOCIAL_PRIORITY'] = 100;
			$var_const['WPSSORRSSB_FOOTER_PRIORITY'] = 100;

			foreach ( $var_const as $name => $value ) {
				if ( defined( $name ) ) {
					$var_const[$name] = constant( $name );	// inherit existing values
				}
			}

			return $var_const;
		}

		public static function require_libs( $plugin_filepath ) {

			require_once WPSSORRSSB_PLUGINDIR.'lib/register.php';
			require_once WPSSORRSSB_PLUGINDIR.'lib/functions.php';
			require_once WPSSORRSSB_PLUGINDIR.'lib/sharing.php';

			add_filter( 'wpssorrssb_load_lib', array( 'WpssoRrssbConfig', 'load_lib' ), 10, 3 );
		}

		public static function load_lib( $ret = false, $filespec = '', $classname = '' ) {
			if ( false === $ret && ! empty( $filespec ) ) {
				$filepath = WPSSORRSSB_PLUGINDIR.'lib/'.$filespec.'.php';
				if ( file_exists( $filepath ) ) {
					require_once $filepath;
					if ( empty( $classname ) ) {
						return SucomUtil::sanitize_classname( 'wpssorrssb'.$filespec, false );	// $underscore = false
					} else {
						return $classname;
					}
				}
			}
			return $ret;
		}
	}
}

