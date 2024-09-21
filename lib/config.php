<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbConfig' ) ) {

	class WpssoRrssbConfig {

		public static $cf = array(
			'plugin' => array(
				'wpssorrssb' => array(			// Plugin acronym.
					'version'     => '11.7.0',	// Plugin version.
					'opt_version' => '48',		// Increment when changing default option values.
					'short'       => 'WPSSO RRSSB',	// Short plugin name.
					'name'        => 'WPSSO Ridiculously Responsive Social Sharing Buttons',
					'desc'        => 'Ridiculously Responsive (SVG) Social Sharing Buttons for your content, excerpts, CSS sidebar, widget, shortcode, templates, and editor.',
					'slug'        => 'wpsso-rrssb',
					'base'        => 'wpsso-rrssb/wpsso-rrssb.php',
					'update_auth' => '',		// No premium version.
					'text_domain' => 'wpsso-rrssb',
					'domain_path' => '/languages',

					/*
					 * Required plugin and its version.
					 */
					'req' => array(
						'wpsso' => array(
							'name'          => 'WPSSO Core',
							'home'          => 'https://wordpress.org/plugins/wpsso/',
							'plugin_class'  => 'Wpsso',
							'version_const' => 'WPSSO_VERSION',
							'min_version'   => '18.10.0',
						),
					),

					/*
					 * URLs or relative paths to plugin banners and icons.
					 */
					'assets' => array(

						/*
						 * Icon image array keys are '1x' and '2x'.
						 */
						'icons' => array(
							'1x' => 'images/icon-128x128.png',
							'2x' => 'images/icon-256x256.png',
						),
					),

					/*
					 * Library files loaded and instantiated by WPSSO.
					 */
					'lib' => array(
						'integ' => array(
							'ecom' => array(
								'woocommerce' => '(plugin) WooCommerce',
							),
						),
						'share' => array(
							'email'     => 'Email',
							'facebook'  => 'Facebook',
							'pinterest' => 'Pinterest',
							'linkedin'  => 'LinkedIn',
							'reddit'    => 'Reddit',
							'pocket'    => 'Pocket',
							'vk'        => 'VK',
							'whatsapp'  => 'WhatsApp',
							'twitter'   => 'X (Twitter)',
						),
						'shortcode' => array(
							'sharing' => 'Sharing Shortcode',
						),
						'submenu' => array(
							'rrssb-buttons' => 'Responsive Buttons',
							'rrssb-styles'  => 'Responsive Styles',
						),
						'widget' => array(
							'sharing' => 'Sharing Widget',
						),
					),

					/*
					 * Declare compatibility with WooCommerce HPOS.
					 *
					 * See https://github.com/woocommerce/woocommerce/wiki/High-Performance-Order-Storage-Upgrade-Recipe-Book.
					 */
					'wc_compat' => array(
						'custom_order_tables',
					),
				),
			),

			/*
			 * Additional add-on setting options.
			 */
			'opt' => array(
				'defaults' => array(

					/*
					 * Responsive Buttons
					 */
					'buttons_on_archive'        => 0,		// Include on Archive Webpages.
					'buttons_on_front'          => 0,		// Include on Static Homepage.
					'buttons_add_to_attachment' => 1,		// Include on Post Types (Media).
					'buttons_add_to_page'       => 1,		// Include on Post Types (Pages).
					'buttons_add_to_post'       => 1,		// Include on Post Types (Posts).
					'buttons_pos_content'       => 'bottom',	// Buttons Position in Content.
					'buttons_pos_excerpt'       => 'bottom',	// Buttons Position in Excerpt.
					'buttons_force_prot'        => 'none',		// Force Protocol for Shared URLs.
					'buttons_utm_medium'        => 'social',	// UTM Medium for All Buttons.

					/*
					 * Responsive Styles
					 */
					'buttons_use_social_style'     => 1,
					'buttons_enqueue_social_style' => 1,
					'buttons_css_rrssb-common'     => '',	// All tab.
					'buttons_css_rrssb-content'    => '',	// Content tab.
					'buttons_css_rrssb-excerpt'    => '',	// Excerpt tab.
					'buttons_css_rrssb-sidebar'    => '',	// Sidebar tab.
					'buttons_css_rrssb-admin_edit' => '',	// Admin tab.
					'buttons_css_rrssb-shortcode'  => '',	// Shortcode tab.
					'buttons_css_rrssb-widget'     => '',	// Widget tab.
				),
			),
			'sharing' => array(
				'show_on' => array(
					'content'    => 'Content',
					'excerpt'    => 'Excerpt',
					'sidebar'    => 'Sidebar',
					'admin_edit' => 'Admin',
				),
				'force_prot' => array(
					'http'  => 'HTTP',
					'https' => 'HTTPS',
				),
				'rrssb_styles' => array(
					'rrssb-common'     => 'All',
					'rrssb-content'    => 'Content',
					'rrssb-excerpt'    => 'Excerpt',
					'rrssb-sidebar'    => 'Sidebar',
					'rrssb-admin_edit' => 'Admin',
					'rrssb-shortcode'  => 'Shortcode',
					'rrssb-widget'     => 'Widget',
				),
				'position' => array(
					'top'    => 'Top',
					'bottom' => 'Bottom',
					'both'   => 'Top and Bottom',
				),
			),
		);

		public static function get_version( $add_slug = false ) {

			$info =& self::$cf[ 'plugin' ][ 'wpssorrssb' ];

			return $add_slug ? $info[ 'slug' ] . '-' . $info[ 'version' ] : $info[ 'version' ];
		}

		public static function set_constants( $plugin_file ) {

			if ( defined( 'WPSSORRSSB_VERSION' ) ) {	// Define constants only once.

				return;
			}

			$info =& self::$cf[ 'plugin' ][ 'wpssorrssb' ];

			/*
			 * Define fixed constants.
			 */
			define( 'WPSSORRSSB_FILEPATH', $plugin_file );
			define( 'WPSSORRSSB_PLUGINBASE', $info[ 'base' ] );	// Example: wpsso-rrssb/wpsso-rrssb.php.
			define( 'WPSSORRSSB_PLUGINDIR', trailingslashit( realpath( dirname( $plugin_file ) ) ) );
			define( 'WPSSORRSSB_PLUGINSLUG', $info[ 'slug' ] );	// Example: wpsso-rrssb.
			define( 'WPSSORRSSB_URLPATH', trailingslashit( plugins_url( '', $plugin_file ) ) );
			define( 'WPSSORRSSB_VERSION', $info[ 'version' ] );

			/*
			 * Define variable constants.
			 */
			self::set_variable_constants();
		}

		public static function set_variable_constants( $var_const = null ) {

			if ( ! is_array( $var_const ) ) {

				$var_const = self::get_variable_constants();
			}

			/*
			 * Define the variable constants, if not already defined.
			 */
			foreach ( $var_const as $name => $value ) {

				if ( ! defined( $name ) ) {

					define( $name, $value );
				}
			}
		}

		public static function get_variable_constants() {

			$var_const = array();

			$var_const[ 'WPSSORRSSB_SHARING_SHORTCODE_NAME' ] = 'rrssb';
			$var_const[ 'WPSSORRSSB_MAX_WIDTH_MULTIPLIER' ]   = 118;

			/*
			 * Maybe override the default constant value with a pre-defined constant value.
			 */
			foreach ( $var_const as $name => $value ) {

				if ( defined( $name ) ) {

					$var_const[$name] = constant( $name );
				}
			}

			return $var_const;
		}

		public static function require_libs( $plugin_file ) {

			require_once WPSSORRSSB_PLUGINDIR . 'lib/actions.php';
			require_once WPSSORRSSB_PLUGINDIR . 'lib/filters.php';
			require_once WPSSORRSSB_PLUGINDIR . 'lib/functions.php';
			require_once WPSSORRSSB_PLUGINDIR . 'lib/register.php';
			require_once WPSSORRSSB_PLUGINDIR . 'lib/script.php';
			require_once WPSSORRSSB_PLUGINDIR . 'lib/social.php';
			require_once WPSSORRSSB_PLUGINDIR . 'lib/style.php';

			add_filter( 'wpssorrssb_load_lib', array( __CLASS__, 'load_lib' ), 10, 3 );
		}

		public static function load_lib( $success = false, $filespec = '', $classname = '' ) {

			if ( false !== $success ) {

				return $success;
			}

			if ( ! empty( $classname ) ) {

				if ( class_exists( $classname ) ) {

					return $classname;
				}
			}

			if ( ! empty( $filespec ) ) {

				$file_path = WPSSORRSSB_PLUGINDIR . 'lib/' . $filespec . '.php';

				if ( file_exists( $file_path ) ) {

					require_once $file_path;

					if ( empty( $classname ) ) {

						return SucomUtil::sanitize_classname( 'wpssorrssb' . $filespec, $allow_underscore = false );
					}

					return $classname;
				}
			}

			return $success;
		}
	}
}
