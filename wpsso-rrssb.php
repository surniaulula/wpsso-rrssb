<?php
/*
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
 * Description: Ridiculously Responsive (SVG) Social Sharing Buttons for your content, excerpts, CSS sidebar, widget, shortcode, templates, and editor.
 * Requires Plugins: wpsso
 * Requires PHP: 7.2.34
 * Requires At Least: 5.5
 * Tested Up To: 6.4.2
 * WC Tested Up To: 8.4.0
 * Version: 11.0.0
 *
 * Version Numbering: {major}.{minor}.{bugfix}[-{stage}.{level}]
 *
 *      {major}         Major structural code changes and/or incompatible API changes (ie. breaking changes).
 *      {minor}         New functionality was added or improved in a backwards-compatible manner.
 *      {bugfix}        Backwards-compatible bug fixes or small improvements.
 *      {stage}.{level} Pre-production release: dev < a (alpha) < b (beta) < rc (release candidate).
 *
 * Copyright 2015-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoAbstractAddOn' ) ) {

	require_once dirname( __FILE__ ) . '/lib/abstract/add-on.php';
}

if ( ! class_exists( 'WpssoRrssb' ) ) {

	class WpssoRrssb extends WpssoAbstractAddOn {

		public $actions;	// WpssoRrssbActions class object.
		public $filters;	// WpssoRrssbFilters class object.
		public $script;		// WpssoRrssbScript class object.
		public $social;		// WpssoRrssbSocial class object.
		public $style;		// WpssoRrssbStyle class object.

		protected $p;	// Wpsso class object.

		private static $instance = null;	// WpssoRrssb class object.

		public function __construct() {

			parent::__construct( __FILE__, __CLASS__ );
		}

		public static function &get_instance() {

			if ( null === self::$instance ) {

				self::$instance = new self;
			}

			return self::$instance;
		}

		public function init_textdomain() {

			load_plugin_textdomain( 'wpsso-rrssb', false, 'wpsso-rrssb/languages/' );
		}

		/*
		 * Called by Wpsso->set_objects which runs at init priority 10.
		 */
		public function init_objects() {

			$this->p =& Wpsso::get_instance();

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			if ( $this->get_missing_requirements() ) {	// Returns false or an array of missing requirements.

				return;	// Stop here.
			}

			$this->actions = new WpssoRrssbActions( $this->p, $this );
			$this->filters = new WpssoRrssbFilters( $this->p, $this );
			$this->script  = new WpssoRrssbScript( $this->p, $this );
			$this->social  = new WpssoRrssbSocial( $this->p, $this );
			$this->style   = new WpssoRrssbStyle( $this->p, $this );
		}
	}

        global $wpssorrssb;

	$wpssorrssb =& WpssoRrssb::get_instance();
}
