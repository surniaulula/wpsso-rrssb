<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2019 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoRrssbStdEcomWoocommerce' ) ) {

	class WpssoRrssbStdEcomWoocommerce {

		private $p;
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

			$classname = __CLASS__ . 'Sharing';

			$this->sharing = new $classname( $this->p );
		}
	}
}

if ( ! class_exists( 'WpssoRrssbStdEcomWoocommerceSharing' ) ) {

	class WpssoRrssbStdEcomWoocommerceSharing {

		private $p;

		public function __construct( &$plugin ) {

			$this->p =& $plugin;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$this->p->util->add_plugin_filters( $this, array( 
				'get_defaults' => 1,
				'rrssb_styles' => 1,
			) );

			if ( is_admin() ) {

				$this->p->util->add_plugin_filters( $this, array( 
					'rrssb_buttons_show_on'       => 2,
					'rrssb_styles_tabs'           => 1,
					'rrssb_buttons_position_rows' => 2,
				) );

			} else {

				add_filter( 'woocommerce_short_description', array( $this, 'get_buttons_woo_short' ), WPSSORRSSB_SOCIAL_PRIORITY );
			}
		}

		public function filter_get_defaults( $opts_def ) {

			foreach ( $this->p->cf['opt']['cm_prefix'] as $cm_id => $opt_pre ) {
				$opts_def[$opt_pre . '_on_woo_short'] = 0;
			}

			$opts_def['buttons_pos_woo_short'] = 'bottom';

			return $opts_def;
		}

		public function filter_rrssb_buttons_show_on( $show_on = array(), $opt_pre ) {

			$show_on['woo_short'] = 'Woo Short';

			return $show_on;
		}

		public function filter_rrssb_styles( $styles ) {

			return $this->filter_rrssb_styles_tabs( $styles );
		}

		public function filter_rrssb_styles_tabs( $styles ) {

			$styles['rrssb-woo_short'] = 'Woo Short';

			return $styles;
		}

		public function filter_rrssb_buttons_position_rows( $table_rows, $form ) {

			$table_rows['buttons_pos_woo_short'] = $form->get_th_html( _x( 'Position in Woo Short Text',
				'option label', 'wpsso-rrssb' ), null, 'buttons_pos_woo_short' ) . 
			'<td>' . $form->get_select( 'buttons_pos_woo_short', $this->p->cf['sharing']['position'] ) . '</td>';

			return $table_rows;
		}

		public function get_buttons_woo_short( $text ) {

			$rrssb =& WpssoRrssb::get_instance();

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			if ( ! empty( $GLOBALS[ $this->p->lca . '_doing_filter_the_content' ] ) ) {

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'exiting early: ' . $this->p->lca . '_doing_filter_the_content is true' );
				}

				return $text;
			}

			return $rrssb->social->get_buttons( $text, 'woo_short' );
		}
	}
}
