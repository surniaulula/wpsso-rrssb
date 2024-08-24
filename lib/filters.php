<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbFilters' ) ) {

	class WpssoRrssbFilters {

		private $p;	// Wpsso class object.
		private $a;	// WpssoRrssb class object.
		private $edit;	// WpssoRrssbFiltersEdit class object.
		private $msgs;	// WpssoRrssbFiltersMessages class object.
		private $opts;	// WpssoRrssbFiltersOptions class object.
		private $upg;	// WpssoRrssbFiltersUpgrade class object.

		/*
		 * Instantiated by WpssoRrssb->init_objects().
		 */
		public function __construct( &$plugin, &$addon ) {

			static $do_once = null;

			if ( $do_once ) return;	// Stop here.

			$do_once = true;

			$this->p =& $plugin;
			$this->a =& $addon;

			require_once WPSSORRSSB_PLUGINDIR . 'lib/filters-options.php';

			$this->opts = new WpssoRrssbFiltersOptions( $plugin, $addon );

			require_once WPSSORRSSB_PLUGINDIR . 'lib/filters-upgrade.php';

			$this->upg = new WpssoRrssbFiltersUpgrade( $plugin, $addon );

			$this->p->util->add_plugin_filters( $this, array(
				'sharing_utm_args' => 3,
			), $prio = 1000 );

			if ( is_admin() ) {

				require_once WPSSORRSSB_PLUGINDIR . 'lib/filters-edit.php';

				$this->edit = new WpssoRrssbFiltersEdit( $plugin, $addon );

				require_once WPSSORRSSB_PLUGINDIR . 'lib/filters-messages.php';

				$this->msgs = new WpssoRrssbFiltersMessages( $plugin, $addon );
			}
		}

		public function filter_sharing_utm_args( $utm, $mod ) {

			/*
			 * Example:
			 *
			 * 	utm_medium   = 'social'
			 * 	utm_source   = 'facebook'
			 * 	utm_campaign = 'book-launch'
			 * 	utm_content  = 'wpsso-rrssb-content-bottom'
			 */
			if ( ! empty( $mod[ 'obj' ] ) && $mod[ 'id' ] ) {

				$utm[ 'utm_campaign' ] = $mod[ 'obj' ]->get_options( $mod[ 'id' ], 'buttons_utm_campaign' );
			}

			if ( ! empty( $this->p->options[ 'buttons_utm_medium' ] ) ) {

				$utm[ 'utm_medium' ] = $this->p->options[ 'buttons_utm_medium' ];
			}

			return $utm;
		}
	}
}
