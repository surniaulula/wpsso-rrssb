<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2019 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbScript' ) ) {

	class WpssoRrssbScript {

		private $p;

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		public function enqueue_scripts( $hook_name ) {

			$is_amp         = SucomUtil::is_amp();
			$plugin_version = $this->p->cf[ 'plugin' ][ 'wpssorrssb' ][ 'version' ];

			if ( $is_amp ) {	// No buttons for AMP pages.
				return;
			}

			wp_register_script( 'rrssb', WPSSORRSSB_URLPATH . 'js/ext/rrssb.min.js', array( 'jquery' ), $plugin_version, true );

			wp_enqueue_script( 'rrssb' );
		}
	}
}
