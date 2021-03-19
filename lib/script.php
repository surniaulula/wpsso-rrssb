<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2021 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbScript' ) ) {

	class WpssoRrssbScript {

		private $p;	// Wpsso class object.
		private $a;	// WpssoRrssb class object.

		private $doing_dev = false;
		private $file_ext  = 'min.js';
		private $version   = '';

		/**
		 * Instantiated by WpssoRrssb->init_objects().
		 */
		public function __construct( &$plugin ) {

			$this->p =& $plugin;
			$this->a =& $addon;

			$this->doing_dev = SucomUtil::get_const( 'WPSSO_DEV' );
			$this->file_ext  = $this->doing_dev ? 'js' : 'min.js';
			$this->version   = WpssoRrssbConfig::get_version();

			if ( is_admin() ) {

				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			} else {

				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			}
		}

		public function enqueue_scripts( $hook_name ) {

			if ( SucomUtil::is_amp() ) {	// Returns null, true, or false.

				return;
			}

			wp_register_script( 'rrssb',
				WPSSORRSSB_URLPATH . 'js/ext/rrssb.' . $this->file_ext,
					array( 'jquery' ), $this->version, true );

			wp_enqueue_script( 'rrssb' );
		}
	}
}
