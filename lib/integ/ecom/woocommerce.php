<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbIntegEcomWooCommerce' ) ) {

	class WpssoRrssbIntegEcomWooCommerce {

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

			$classname = __CLASS__ . 'Sharing';

			$this->sharing = new $classname( $this->p );
		}
	}
}

if ( ! class_exists( 'WpssoRrssbIntegEcomWooCommerceSharing' ) ) {

	class WpssoRrssbIntegEcomWooCommerceSharing {

		private $p;	// Wpsso class object.
		private $is_variation = false;

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
					'rrssb_buttons_show_on'       => 1,
					'rrssb_buttons_position_rows' => 2,
					'rrssb_styles_tabs'           => 1,
				) );

			} else {

				add_filter( 'woocommerce_short_description', array( $this, 'get_buttons_wc_short_desc' ), 1000 );
				add_filter( 'woocommerce_available_variation', array( $this, 'get_variation_wc_short_desc' ), 10, 3 );

				add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'show_buttons_before_add_to_cart' ), -1000, 0 );
				add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'show_buttons_after_add_to_cart' ), 1000, 0 );
			}
		}

		public function filter_get_defaults( array $defs ) {

			$defs[ 'buttons_pos_wc_short_desc' ]  = 'bottom';	// Default position for product short description.
			$defs[ 'buttons_pos_wc_add_to_cart' ] = 'bottom';	// Default position (before/after) for Add to Cart.

			foreach ( $this->p->cf[ 'opt' ][ 'cm_prefix' ] as $cm_id => $opt_pre ) {

				$defs[ $opt_pre . '_on_wc_short_desc' ]  = 0;
				$defs[ $opt_pre . '_on_wc_add_to_cart' ] = 1;
			}

			return $defs;
		}

		public function filter_rrssb_buttons_show_on( $show_on = array() ) {

			$show_on[ 'wc_short_desc' ]  = _x( 'WC Short Desc', 'option value', 'wpsso-rrssb' );
			$show_on[ 'wc_add_to_cart' ] = _x( 'WC Add to Cart', 'option value', 'wpsso-rrssb' );

			return $show_on;
		}

		public function filter_rrssb_buttons_position_rows( $table_rows, $form ) {

			$table_rows[ 'buttons_pos_wc_short_desc' ] = '' .
				$form->get_th_html( _x( 'Position in WC Short Desc', 'option label', 'wpsso-rrssb' ),
					$css_class = '', $css_id = 'buttons_pos_wc_short_desc' ) .
				'<td>' . $form->get_select( 'buttons_pos_wc_short_desc', $this->p->cf[ 'sharing' ][ 'position' ] ) . '</td>';

			$table_rows[ 'buttons_pos_wc_add_to_cart' ] = '' .
				$form->get_th_html( _x( 'Position in WC Add to Cart', 'option label', 'wpsso-rrssb' ),
					$css_class = '', $css_id = 'buttons_pos_wc_add_to_cart' ) .
				'<td>' . $form->get_select( 'buttons_pos_wc_add_to_cart', $this->p->cf[ 'sharing' ][ 'position' ] ) . '</td>';

			return $table_rows;
		}

		public function filter_rrssb_styles( $styles ) {

			$styles[ 'rrssb-woocommerce' ]  = _x( 'WooCommerce', 'option value', 'wpsso-rrssb' );

			return $styles;
		}

		public function filter_rrssb_styles_tabs( $styles ) {

			$styles[ 'rrssb-woocommerce' ]  = _x( 'WooCommerce', 'option value', 'wpsso-rrssb' );

			return $styles;
		}

		public function get_buttons_wc_short_desc( $text ) {

			$rrssb =& WpssoRrssb::get_instance();

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			if ( $this->is_variation ) {

				return $text;

			} elseif ( ! empty( $GLOBALS[ 'wpsso_doing_filter_the_content' ] ) ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'exiting early: wpsso_doing_filter_the_content is true' );
				}

				return $text;
			}

			return $rrssb->social->get_buttons( $text, $type = 'wc_short_desc' );
		}

		public function get_variation_wc_short_desc( $data, $product, $variation ) {

			$this->is_variation = true;

			$data[ 'variation_description' ] = wc_format_content( $variation->get_description() );

			$this->is_variation = false;

			return $data;
		}

		public function show_buttons_before_add_to_cart() {

			$location = $this->p->options[ 'buttons_pos_wc_add_to_cart' ];

			switch ( $location ) {

				case 'both':
				case 'top':

					$rrssb =& WpssoRrssb::get_instance();

					echo $rrssb->social->get_buttons( $text = '', $type = 'wc_add_to_cart', $use_post = true, $location = 'top' );

					break;
			}
		}

		public function show_buttons_after_add_to_cart() {

			$location = $this->p->options[ 'buttons_pos_wc_add_to_cart' ];

			switch ( $location ) {

				case 'both':
				case 'bottom':

					$rrssb =& WpssoRrssb::get_instance();

					echo $rrssb->social->get_buttons( $text = '', $type = 'wc_add_to_cart', $use_post = true, $location = 'bottom' );

					break;
			}
		}
	}
}
