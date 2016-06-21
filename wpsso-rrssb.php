<?php
/*
 * Plugin Name: WPSSO Ridiculously Responsive Social Sharing Buttons (WPSSO RRSSB)
 * Plugin Slug: wpsso-rrssb
 * Text Domain: wpsso-rrssb
 * Domain Path: /languages
 * Plugin URI: http://surniaulula.com/extend/plugins/wpsso-rrssb/
 * Author: JS Morisset
 * Author URI: http://surniaulula.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Description: WPSSO extension to add Ridiculously Responsive (SVG) Social Sharing Buttons in your content, excerpts, CSS sidebar, widget, shortcode, etc.
 * Requires At Least: 3.1
 * Tested Up To: 4.5.3
 * Version: 1.3.14-1
 * 
 * Version Numbers: {major}.{minor}.{bugfix}-{stage}{level}
 *
 *	{major}		Major code changes and/or significant feature changes.
 *	{minor}		New features added and/or improvements included.
 *	{bugfix}	Bugfixes and/or very minor improvements.
 *	{stage}{level}	dev# (development), rc# (release candidate), # (production release)
 * 
 * Copyright 2014-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoRrssb' ) ) {

	class WpssoRrssb {

		public $p;			// Wpsso
		public $reg;			// WpssoRrssbRegister

		private static $instance = null;
		private static $wpsso_short = 'WPSSO';
		private static $wpsso_name = 'WordPress Social Sharing Optimization (WPSSO)';
		private static $wpsso_min_version = '3.32.4-1';
		private static $wpsso_has_min_ver = true;

		public static function &get_instance() {
			if ( self::$instance === null )
				self::$instance = new self;
			return self::$instance;
		}

		public function __construct() {

			if ( defined( 'WPSSORRSSB_SOCIAL_SHARING_DISABLE' ) &&
				WPSSORRSSB_SOCIAL_SHARING_DISABLE )
					return;

			require_once ( dirname( __FILE__ ).'/lib/config.php' );
			WpssoRrssbConfig::set_constants( __FILE__ );
			WpssoRrssbConfig::require_libs( __FILE__ );
			$this->reg = new WpssoRrssbRegister();		// activate, deactivate, uninstall hooks

			if ( is_admin() ) {
				load_plugin_textdomain( 'wpsso-rrssb', false, 'wpsso-rrssb/languages/' );
				add_action( 'admin_init', array( &$this, 'check_for_wpsso' ) );
			}

			add_filter( 'wpsso_get_config', array( &$this, 'wpsso_get_config' ), 30, 1 );
			add_action( 'wpsso_init_options', array( &$this, 'wpsso_init_options' ), 10 );
			add_action( 'wpsso_init_objects', array( &$this, 'wpsso_init_objects' ), 10 );
			add_action( 'wpsso_init_plugin', array( &$this, 'wpsso_init_plugin' ), 10 );
		}

		public function check_for_wpsso() {
			if ( ! class_exists( 'Wpsso' ) )
				add_action( 'all_admin_notices', array( &$this, 'wpsso_missing_notice' ) );
		}

		public static function wpsso_missing_notice( $deactivate = false ) {
			$info = WpssoRrssbConfig::$cf['plugin']['wpssorrssb'];

			if ( $deactivate === true ) {
				require_once( ABSPATH.'wp-admin/includes/plugin.php' );
				deactivate_plugins( $info['base'] );

				wp_die( '<p>'.sprintf( __( 'The %1$s extension requires the %2$s plugin &mdash; please install and activate the %3$s plugin before trying to re-activate the %4$s extension.', 'wpsso-rrssb' ), $info['name'], self::$wpsso_name, self::$wpsso_short, $info['short'] ).'</p>' );

			} else echo '<div class="error"><p>'.sprintf( __( 'The %1$s extension requires the %2$s plugin &mdash; please install and activate the %3$s plugin.', 'wpsso-rrssb' ), $info['name'], self::$wpsso_name, self::$wpsso_short ).'</p></div>';
		}

		public function wpsso_get_config( $cf ) {
			if ( version_compare( $cf['plugin']['wpsso']['version'], self::$wpsso_min_version, '<' ) ) {
				self::$wpsso_has_min_ver = false;
				return $cf;
			}
			return SucomUtil::array_merge_recursive_distinct( $cf, WpssoRrssbConfig::$cf );
		}

		public function wpsso_init_options() {
			if ( method_exists( 'Wpsso', 'get_instance' ) )
				$this->p =& Wpsso::get_instance();
			else $this->p =& $GLOBALS['wpsso'];

			if ( self::$wpsso_has_min_ver === false )
				return;

			$this->p->is_avail['rrssb'] = true;

			if ( is_admin() )
				$this->p->is_avail['admin']['sharing'] = true;
		}

		public function wpsso_init_objects() {
			if ( self::$wpsso_has_min_ver === false )
				return;

			$this->p->rrssb = new WpssoRrssbSharing( $this->p, __FILE__ );
		}

		public function wpsso_init_plugin() {
			if ( self::$wpsso_has_min_ver === false )
				return $this->warning_wpsso_version();
		}

		private function warning_wpsso_version() {
			$info = WpssoRrssbConfig::$cf['plugin']['wpssorrssb'];
			$wpsso_version = $this->p->cf['plugin']['wpsso']['version'];

			if ( $this->p->debug->enabled )
				$this->p->debug->log( $info['name'].' requires '.self::$wpsso_short.' version '.
					self::$wpsso_min_version.' or newer ('.$wpsso_version.' installed)' );

			if ( is_admin() )
				$this->p->notice->err( sprintf( __( 'The %1$s extension version %2$s requires the use of %3$s version %4$s or newer (version %5$s is currently installed).', 'wpsso-rrssb' ), $info['name'], $info['version'], self::$wpsso_short, self::$wpsso_min_version, $wpsso_version ), true );
		}
	}

        global $wpssorrssb;
	$wpssorrssb =& WpssoRrssb::get_instance();
}

?>
