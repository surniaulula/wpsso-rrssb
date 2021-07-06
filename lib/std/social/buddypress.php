<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2021 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbStdSocialBuddypress' ) ) {

	class WpssoRrssbStdSocialBuddypress {

		private $p;	// Wpsso class object.

		private $sharing;

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			if ( empty( $this->p->avail[ 'p_ext' ][ 'rrssb' ] ) ) {	// False if required version(s) not available.

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'exiting early: this extension / add-on is not available' );
				}

				return;
			}

			/**
			 * The latest BuddyPress templates use ajax calls, so is_admin(), bp_current_component(), and DOING_AJAX
			 * will all be true for those ajax calls.
			 */
			$component = bp_current_component();

			if ( is_admin() || $component ) {

				$classname = __CLASS__.'Sharing';

				$this->sharing = new $classname( $this->p );
			}
		}
	}
}

if ( ! class_exists( 'WpssoRrssbStdSocialBuddypressSharing' ) ) {

	class WpssoRrssbStdSocialBuddypressSharing {

		private $p;	// Wpsso class object.

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array( 
				'get_defaults' => 1,
				'rrssb_styles' => 1,
			) );

			/**
			 * The latest BuddyPress templates use ajax calls, so is_admin(), bp_current_component(), and DOING_AJAX
			 * will all be true for those ajax calls.
			 */
			$component = bp_current_component();

			if ( is_admin() ) {

				$this->p->util->add_plugin_filters( $this, array( 
					'rrssb_buttons_show_on' => 2,
					'rrssb_styles_tabs'     => 1,
				) );
			}

			if ( $component ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'bp_current_component() is true' );
				}

				/**
				 * Remove sharing filters on WordPress content and excerpt.
				 */
				add_action( 'wpsso_init_plugin', array( $this, 'remove_wp_buttons' ), 100, 0 );

				/**
				 * Add sharing buttons to each activity entry.
				 */
				add_action( 'bp_activity_entry_meta', array( $this, 'show_activity_buttons' ), 100, 0 );
			}
		}

		public function filter_get_defaults( $opts_def ) {

			foreach ( $this->p->cf[ 'opt' ][ 'cm_prefix' ] as $id => $opt_pre ) {

				$opts_def[ $opt_pre . '_on_bp_activity' ] = 0;
			}

			return $opts_def;
		}

		public function filter_rrssb_buttons_show_on( $show_on = array(), $opt_pre = '' ) {

			$show_on[ 'bp_activity' ] = 'BP Activity';

			return $show_on;
		}

		public function filter_rrssb_styles( $styles ) {

			$styles[ 'rrssb-bp_activity' ] = 'BP Activity';

			return $styles;
		}

		public function filter_rrssb_styles_tabs( $styles ) {

			$styles[ 'rrssb-bp_activity' ] = 'BP Activity';

			return $styles;
		}

		public function show_activity_buttons() {

			static $do_once = array();

			$activity_id = bp_get_activity_id();

			if ( empty( $do_once[ $activity_id ] ) ) {	// Only run once per activity ID.

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'adding sharing buttons for activity id ' . $activity_id );
				}

				$do_once[ $activity_id ] = true;

				$rrssb =& WpssoRrssb::get_instance();

				echo $rrssb->social->get_buttons( $text = '', 'bp_activity' );

			} elseif ( $this->p->debug->enabled ) {

				$this->p->debug->log( 'buttons skipped: already added to activity id ' . $activity_id );
			}
		}

		public function remove_wp_buttons() {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			$rrssb =& WpssoRrssb::get_instance();

			foreach ( array( 'get_the_excerpt', 'the_excerpt', 'the_content' ) as $filter_name ) {

				$rrssb->social->remove_buttons_filter( $filter_name );
			}
		}
	}
}
