<?php
/**
 * Plugin Name: WPSSO Ridiculously Responsive Social Sharing Buttons
 * Plugin Slug: wpsso-rrssb
 * Text Domain: wpsso-rrssb
 * Domain Path: /languages
 * Plugin URI: https://wpsso.com/extend/plugins/wpsso-rrssb/
 * Assets URI: https://surniaulula.github.io/wpsso-rrssb/assets/
 * Author: JS Morisset
 * Author URI: https://wpsso.com/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Description: Ridiculously Responsive (SVG) Social Sharing Buttons for your Content, Excerpts, CSS Sidebar, Widget, Shortcode, Templates, and Editor.
 * Requires PHP: 5.6
 * Requires At Least: 5.2
 * Tested Up To: 5.5.1
 * WC Tested Up To: 4.5.2
 * Version: 5.1.0-dev.1
 * 
 * Version Numbering: {major}.{minor}.{bugfix}[-{stage}.{level}]
 *
 *      {major}         Major structural code changes / re-writes or incompatible API changes.
 *      {minor}         New functionality was added or improved in a backwards-compatible manner.
 *      {bugfix}        Backwards-compatible bug fixes or small improvements.
 *      {stage}.{level} Pre-production release: dev < a (alpha) < b (beta) < rc (release candidate).
 * 
 * Copyright 2014-2020 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'SucomAddOn' ) ) {

	require_once dirname( __FILE__ ) . '/lib/abstracts/com/add-on.php';	// SucomAddOn class.
}

if ( ! class_exists( 'WpssoRrssb' ) ) {

	class WpssoRrssb extends SucomAddOn {

		/**
		 * Library class object variables.
		 */
		public $actions;	// WpssoRrssbActions class.
		public $filters;	// WpssoRrssbFilters class.
		public $reg;		// WpssoRrssbRegister class.
		public $script;		// WpssoRrssbScript class.
		public $social;		// WpssoRrssbSocial class.
		public $style;		// WpssoRrssbStyle class.

		/**
		 * Reference Variables (config, options, modules, etc.).
		 */
		protected $p;
		protected $ext   = 'wpssorrssb';
		protected $p_ext = 'rrssb';
		protected $cf    = array();

		private static $instance = null;

		public function __construct() {

			require_once dirname( __FILE__ ) . '/lib/config.php';

			WpssoRrssbConfig::set_constants( __FILE__ );

			WpssoRrssbConfig::require_libs( __FILE__ );	// Includes the register.php class library.

			$this->cf =& WpssoRrssbConfig::$cf;

			$this->reg = new WpssoRrssbRegister();		// Activate, deactivate, uninstall hooks.

			/**
			 * WPSSO filter hooks.
			 */
			add_filter( 'wpsso_get_config', array( $this, 'get_config' ), 10, 1 );
			add_filter( 'wpsso_get_avail', array( $this, 'get_avail' ), 10, 1 );

			/**
			 * WPSSO action hooks.
			 */
			add_action( 'wpsso_init_textdomain', array( $this, 'init_textdomain' ), 10, 1 );
			add_action( 'wpsso_init_objects', array( $this, 'init_objects' ), 10, 0 );
			add_action( 'wpsso_init_plugin', array( $this, 'init_missing_requirements' ), 10, 2 );

			/**
			 * WordPress action hooks.
			 */
			add_action( 'all_admin_notices', array( $this, 'show_missing_requirements' ) );
		}

		public static function &get_instance() {

			if ( null === self::$instance ) {

				self::$instance = new self;
			}

			return self::$instance;
		}

		public function init_textdomain( $debug_enabled = false ) {

			static $local_cache = null;

			if ( null === $local_cache || $debug_enabled ) {

				$local_cache = 'wpsso-rrssb';

				load_plugin_textdomain( 'wpsso-rrssb', false, 'wpsso-rrssb/languages/' );
			}

			return $local_cache;
		}

		public function init_objects() {

			$this->p =& Wpsso::get_instance();

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			if ( $this->get_missing_requirements() ) {	// Returns false or an array of missing requirements.

				return;	// Stop here.
			}

			$this->actions = new WpssoRrssbActions( $this->p );
			$this->filters = new WpssoRrssbFilters( $this->p );
			$this->script  = new WpssoRrssbScript( $this->p );
			$this->social  = new WpssoRrssbSocial( $this->p );
			$this->style   = new WpssoRrssbStyle( $this->p );
		}
	}

        global $wpssorrssb;

	$wpssorrssb =& WpssoRrssb::get_instance();
}
