<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2015 - Jean-Sebastien Morisset - http://wpsso.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoRrssbConfig' ) ) {

	class WpssoRrssbConfig {

		public static $cf = array(
			'plugin' => array(
				'wpssorrssb' => array(
					'version' => '0.1',	// plugin version
					'short' => 'WPSSO RRSSB',
					'name' => 'WPSSO Ridiculously Responsive Social Sharing Buttons (WPSSO RRSSB)',
					'desc' => 'WPSSO extension to add Ridiculously Responsive (SVG) Social Sharing Buttons in your content, excerpts, CSS sidebar, widget, shortcode, etc.',
					'slug' => 'wpsso-rrssb',
					'base' => 'wpsso-rrssb/wpsso-rrssb.php',
					'update_auth' => '',
					'img' => array(
						'icon_small' => 'images/icon-128x128.png',
						'icon_medium' => 'images/icon-256x256.png',
					),
					'url' => array(
						// wordpress.org
						'download' => 'https://wordpress.org/plugins/wpsso-rrssb/',
						'review' => 'https://wordpress.org/support/view/plugin-reviews/wpsso-rrssb#postform',
						'readme' => 'https://plugins.svn.wordpress.org/wpsso-rrssb/trunk/readme.txt',
						'wp_support' => 'https://wordpress.org/support/plugin/wpsso-rrssb',
						// wpsso.com
						'update' => 'http://wpsso.com/extend/plugins/wpsso-rrssb/update/',
						'purchase' => 'http://wpsso.com/extend/plugins/wpsso-rrssb/',
						'changelog' => 'http://wpsso.com/extend/plugins/wpsso-rrssb/changelog/',
						'codex' => 'http://wpsso.com/codex/plugins/wpsso-rrssb/',
						'faq' => 'http://wpsso.com/codex/plugins/wpsso-rrssb/faq/',
						'notes' => '',
						'feed' => 'http://wpsso.com/category/application/wordpress/wp-plugins/wpsso-rrssb/feed/',
						'pro_support' => 'http://wpsso-rrssb.support.wpsso.com/',
					),
					'lib' => array(
						'submenu' => array (
							'wpssorrssb-separator-0' => 'RRSSB Extension',
							'sharing-buttons' => 'Sharing Buttons',
							'sharing-styles' => 'Sharing Styles',
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
						),
						'shortcode' => array(
							'sharing' => 'Sharing Shortcode',
						),
						'widget' => array(
							'sharing' => 'Sharing Widget',
						),
						'gpl' => array(
							'admin' => array(
								'sharing' => 'Sharing Admin',
							),
						),
						'pro' => array(
							'admin' => array(
								'sharing' => 'Sharing Admin',
							),
						),
					),
				),
			),
		);

		public static function set_constants( $plugin_filepath ) { 
			$lca = 'wpssorrssb';
			$slug = self::$cf['plugin'][$lca]['slug'];

			define( 'WPSSORRSSB_FILEPATH', $plugin_filepath );						
			define( 'WPSSORRSSB_PLUGINDIR', trailingslashit( plugin_dir_path( $plugin_filepath ) ) );
			define( 'WPSSORRSSB_PLUGINBASE', plugin_basename( $plugin_filepath ) );
			define( 'WPSSORRSSB_TEXTDOM', $slug );
			define( 'WPSSORRSSB_URLPATH', trailingslashit( plugins_url( '', $plugin_filepath ) ) );

			/*
			 * Allow some constants to be pre-defined in wp-config.php
			 */
			if ( ! defined( 'WPSSORRSSB_SHARING_SHORTCODE' ) )
				define( 'WPSSORRSSB_SHARING_SHORTCODE', 'rrssb' );

			/*
			 * WPSSO RRSSB hook priorities
			 */
			if ( ! defined( 'WPSSORRSSB_SOCIAL_PRIORITY' ) )
				define( 'WPSSORRSSB_SOCIAL_PRIORITY', 100 );

			if ( ! defined( 'WPSSORRSSB_FOOTER_PRIORITY' ) )
				define( 'WPSSORRSSB_FOOTER_PRIORITY', 100 );
		}

		public static function require_libs( $plugin_filepath ) {
			if ( ! is_admin() )
				require_once( WPSSORRSSB_PLUGINDIR.'lib/functions.php' );

			add_filter( 'wpssorrssb_load_lib', array( 'WpssoRrssbConfig', 'load_lib' ), 10, 3 );
		}

		// gpl / pro library loader
		public static function load_lib( $ret = false, $filespec = '', $classname = '' ) {
			if ( $ret === false && ! empty( $filespec ) ) {
				$filepath = WPSSORRSSB_PLUGINDIR.'lib/'.$filespec.'.php';
				if ( file_exists( $filepath ) ) {
					require_once( $filepath );
					if ( empty( $classname ) )
						return 'wpssorrssb'.str_replace( array( '/', '-' ), '', $filespec );
					else return $classname;
				}
			}
			return $ret;
		}
	}
}

?>
