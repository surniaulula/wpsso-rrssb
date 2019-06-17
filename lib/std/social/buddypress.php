<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2019 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoRrssbStdSocialBuddypress' ) ) {

	class WpssoRrssbStdSocialBuddypress {

		private $p;
		private $sharing;

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			/**
			 * Note that the latest BuddyPress templates use AJAX calls, so is_admin(),
			 * bp_current_component(), and DOING_AJAX will all be true in those cases.
			 */
			if ( is_admin() || bp_current_component() ) {

				if ( ! empty( $this->p->avail['p_ext']['rrssb'] ) ) {

					$classname = __CLASS__.'Sharing';

					if ( class_exists( $classname ) ) {
						$this->sharing = new $classname( $this->p );
					}
				}
			}
		}
	}
}

if ( ! class_exists( 'WpssoRrssbStdSocialBuddypressSharing' ) ) {

	class WpssoRrssbStdSocialBuddypressSharing {

		private $p;

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array( 
				'get_defaults' => 1,
				'rrssb_styles' => 1,
			) );

			if ( is_admin() ) {

				$this->p->util->add_plugin_filters( $this, array( 
					'rrssb_buttons_show_on' => 2,
					'rrssb_styles_tabs'     => 1,
				) );
			}

			if ( bp_current_component() ) {

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'bp_current_component() = ' . bp_current_component() );
				}

				/**
				 * Remove sharing filters on WordPress content and excerpt.
				 */
				add_action( $this->p->lca . '_init_plugin', array( $this, 'remove_wp_buttons' ), 100 );
			}
		}

		public function filter_get_defaults( $opts_def ) {

			foreach ( $this->p->cf['opt']['cm_prefix'] as $id => $opt_pre ) {
				$opts_def[$opt_pre.'_on_bp_activity'] = 0;
			}

			return $opts_def;
		}

		public function filter_rrssb_buttons_show_on( $show_on = array(), $opt_pre = '' ) {

			$show_on['bp_activity'] = 'BP Activity';

			$this->p->options[$opt_pre.'_on_bp_activity:is'] = 'disabled';

			return $show_on;
		}

		public function filter_rrssb_styles( $styles ) {

			return $this->filter_rrssb_styles_tabs( $styles );
		}

		public function filter_rrssb_styles_tabs( $styles ) {

			$styles['rrssb-bp_activity'] = 'BP Activity';

			$this->p->options['buttons_css_rrssb-bp_activity:is'] = 'disabled';

			return $styles;
		}

		public function remove_wp_buttons() {

			$rrssb =& WpssoRrssb::get_instance();

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			foreach ( array( 'get_the_excerpt', 'the_excerpt', 'the_content' ) as $filter_name ) {
				$rrssb->social->remove_buttons_filter( $filter_name );
			}
		}
	}
}
