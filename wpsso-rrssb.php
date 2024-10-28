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
 * Requires PHP: 7.4.33
 * Requires At Least: 5.9
 * Tested Up To: 6.7.0
 * WC Tested Up To: 9.3.3
 * Version: 11.7.0
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

		public $social;		// WpssoRrssbSocial class object.

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
		 * Called by Wpsso->set_objects() which runs at init priority 10.
		 */
		public function init_objects_preloader() {

			$this->p =& Wpsso::get_instance();

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			if ( $this->get_missing_requirements() ) {	// Returns false or an array of missing requirements.

				return;	// Stop here.
			}

			new WpssoRrssbActions( $this->p, $this );
			new WpssoRrssbFilters( $this->p, $this );
			new WpssoRrssbScript( $this->p, $this );
			new WpssoRrssbStyle( $this->p, $this );

			/*
			 * See WpssoRrssbActions->action_pre_apply_filters_text().
			 * See WpssoRrssbActions->action_after_apply_filters_text().
			 * See WpssoRrssbActions->action_load_settings_page_reload_default_rrssb_buttons().
			 * See WpssoRrssbFiltersEdit->filter_mb_sso_inside_footer().
			 * See WpssoRrssbShortcodeSharing->do_shortcode().
			 * See WpssoRrssbWidgetSharing->widget().
			 * See WpssoRrssbWidgetSharing->update().
			 * See WpssoRrssbWidgetSharing->form().
			 */
			$this->social = new WpssoRrssbSocial( $this->p, $this );
		}
	}

	WpssoRrssb::get_instance();
}
