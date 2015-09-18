<?php
/*
 * Plugin Name: WPSSO Ridiculously Responsive Social Sharing Buttons (WPSSO RRSSB)
 * Plugin URI: http://surniaulula.com/extend/plugins/wpsso-rrssb/
 * Author: Jean-Sebastien Morisset
 * Author URI: http://surniaulula.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Description: WPSSO extension to provide Ridiculously Responsive Social Sharing Buttons - with support for hashtags, short URLs, bbPress, and BuddyPress.
 * Requires At Least: 3.1
 * Tested Up To: 4.3.1
 * Version: 1.0.5
 * 
 * Copyright 2014-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoRrssb' ) ) {

	class WpssoRrssb {

		public $p;			// Wpsso
		public $reg;			// WpssoRrssbRegister

		protected static $instance = null;

		private $opt_version_suffix = 'rrssb3';
		private $wpsso_min_version = '3.9';
		private $wpsso_has_min_ver = true;

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

			if ( is_admin() )
				add_action( 'admin_init', array( &$this, 'wp_check_for_wpsso' ) );

			add_filter( 'wpsso_get_config', array( &$this, 'wpsso_get_config' ), 30, 1 );
			add_action( 'wpsso_init_options', array( &$this, 'wpsso_init_options' ), 10 );
			add_action( 'wpsso_init_objects', array( &$this, 'wpsso_init_objects' ), 10 );
			add_action( 'wpsso_init_plugin', array( &$this, 'wpsso_init_plugin' ), 10 );
		}

		public function wp_check_for_wpsso() {
			if ( ! class_exists( 'Wpsso' ) )
				add_action( 'all_admin_notices', array( &$this, 'wp_notice_missing_wpsso' ) );
		}

		public function wp_notice_missing_wpsso() {
			$ext_name = WpssoRrssbConfig::$cf['plugin']['wpssorrssb']['name'];
			$req_name = 'WordPress Social Sharing Optimization (WPSSO)';
			$req_uca = 'WPSSO';
			echo '<div class="error"><p>';
			echo sprintf( __( 'The %s extension requires the %s plugin &mdash; '.
				'Please install and activate the %s plugin.', WPSSORRSSB_TEXTDOM ),
					$ext_name, $req_name, $req_uca );
			echo '</p></div>';
		}

		public function wpsso_get_config( $cf ) {
			if ( version_compare( $cf['plugin']['wpsso']['version'], $this->wpsso_min_version, '<' ) ) {
				$this->wpsso_has_min_ver = false;
				return $cf;
			}
			$cf['opt']['version'] .= '-'.$this->opt_version_suffix.
				( is_dir( trailingslashit( dirname( __FILE__ ) ).'lib/pro/' ) ? 'pro' : 'gpl' );
			$cf = SucomUtil::array_merge_recursive_distinct( $cf, WpssoRrssbConfig::$cf );
			return $cf;
		}

		public function wpsso_init_options() {
			$this->p =& Wpsso::get_instance();
			if ( $this->wpsso_has_min_ver === false )
				return;
			$this->p->is_avail['rrssb'] = true;
			$this->p->is_avail['admin']['sharing'] = true;
		}

		public function wpsso_init_objects() {
			if ( $this->wpsso_has_min_ver === false )
				return;
			WpssoRrssbConfig::load_lib( false, 'sharing' );
			$this->p->rrssb = new WpssoRrssbSharing( $this->p, __FILE__ );
		}

		public function wpsso_init_plugin() {
			if ( $this->wpsso_has_min_ver === false )
				return $this->warning_wpsso_version( WpssoRrssbConfig::$cf['plugin']['wpssorrssb'] );
		}

		private function warning_wpsso_version( $info ) {
			$wpsso_version = $this->p->cf['plugin']['wpsso']['version'];
			if ( $this->p->debug->enabled )
				$this->p->debug->log( $info['name'].' requires WPSSO version '.$this->wpsso_min_version.
					' or newer ('.$wpsso_version.' installed)' );
			if ( is_admin() )
				$this->p->notice->err( 'The '.$info['name'].' version '.$info['version'].
					' extension requires WPSSO version '.$this->wpsso_min_version.
					' or newer (version '.$wpsso_version.' is currently installed).', true );
		}
	}

        global $wpssorrssb;
	$wpssoRrssb = WpssoRrssb::get_instance();
}

?>
