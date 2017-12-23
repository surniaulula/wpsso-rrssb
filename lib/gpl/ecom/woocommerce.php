<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2017 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoRrssbGplEcomWoocommerce' ) ) {

	class WpssoRrssbGplEcomWoocommerce {

		private $p;
		private $sharing;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			if ( ! empty( $this->p->avail['p_ext']['rrssb'] ) ) {
				$classname = __CLASS__.'Sharing';
				if ( class_exists( $classname ) ) {
					$this->sharing = new $classname( $this->p );
				}
			}
		}
	}
}

if ( ! class_exists( 'WpssoRrssbGplEcomWoocommerceSharing' ) ) {

	class WpssoRrssbGplEcomWoocommerceSharing {

		private $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array( 
				'get_defaults' => 1,
			) );

			if ( is_admin() && empty( $this->p->options['plugin_hide_pro'] ) ) {
				$this->p->util->add_plugin_filters( $this, array( 
					'rrssb_buttons_show_on' => 2,
					'rrssb_styles_tabs' => 1,
					'sharing_buttons_position_rows' => 2,
				) );
			}
		}

		public function filter_get_defaults( $opts_def ) {
			foreach ( $this->p->cf['opt']['cm_prefix'] as $id => $opt_pre ) {
				$opts_def[$opt_pre.'_on_woo_short'] = 0;
			}
			$opts_def['buttons_pos_woo_short'] = 'bottom';

			return $opts_def;
		}

		public function filter_rrssb_buttons_show_on( $show_on = array(), $opt_pre ) {
			$show_on['woo_short'] = 'Woo Short';
			$this->p->options[$opt_pre.'_on_woo_short:is'] = 'disabled';
			return $show_on;
		}

		public function filter_rrssb_styles_tabs( $tabs ) {
			$tabs['rrssb-woo_short'] = 'Woo Short';
			$this->p->options['buttons_css_rrssb-woo_short:is'] = 'disabled';
			return $tabs;
		}

		public function filter_sharing_buttons_position_rows( $table_rows, $form ) {
			$table_rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg', 
					array( 'lca' => 'wpssorrssb' ) ).'</td>';
			$table_rows['buttons_pos_woo_short'] = $form->get_th_html( _x( 'Position in Woo Short Text',
				'option label', 'wpsso-rrssb' ), null, 'buttons_pos_woo_short' ).
			'<td class="blank">'.$this->p->cf['sharing']['position'][$this->p->options['buttons_pos_woo_short']].'</td>';
			return $table_rows;
		}
	}
}

