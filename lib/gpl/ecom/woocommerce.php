<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoRrssbGplEcomWoocommerce' ) ) {

	class WpssoRrssbGplEcomWoocommerce {

		private $p;
		private $sharing;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( ! empty( $this->p->is_avail['rrssb'] ) ) {
				$classname = __CLASS__.'Sharing';
				if ( class_exists( $classname ) )
					$this->sharing = new $classname( $this->p );
			}
		}
	}
}

if ( ! class_exists( 'WpssoRrssbGplEcomWoocommerceSharing' ) ) {

	class WpssoRrssbGplEcomWoocommerceSharing {

		private $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$this->p->util->add_plugin_filters( $this, array( 
				'get_defaults' => 1,
			) );

			if ( is_admin() ) {
				$this->p->util->add_plugin_filters( $this, array( 
					'sharing_show_on' => 2,
					'style_tabs' => 1,
					'sharing_buttons_position_rows' => 2,	// social sharing 'Buttons Position' options
				) );
			}
		}

		public function filter_get_defaults( $opts_def ) {
			foreach ( $this->p->cf['opt']['pre'] as $name => $prefix )
				$opts_def[$prefix.'_on_woo_short'] = 0;
			$opts_def['buttons_pos_woo_short'] = 'bottom';
			return $opts_def;
		}

		public function filter_sharing_show_on( $show_on = array(), $prefix ) {
			$show_on['woo_short'] = 'Woo Short';
			$this->p->options[$prefix.'_on_woo_short:is'] = 'disabled';
			return $show_on;
		}

		public function filter_style_tabs( $tabs ) {
			$tabs['rrssb-woo_short'] = 'Woo Short';
			$this->p->options['buttons_css_rrssb-woo_short:is'] = 'disabled';
			return $tabs;
		}

		public function filter_sharing_buttons_position_rows( $rows, $form ) {
			$pos = array( 'top' => 'Top', 'bottom' => 'Bottom', 'both' => 'Both Top and Bottom' );
			$rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg', array( 'lca' => 'wpssorrssb' ) ).'</td>';
			$rows['buttons_pos_woo_short'] = $this->p->util->get_th( 'Position in Woo Short Text', null, 'buttons_pos_woo_short' ).
			'<td class="blank">'.$form->get_hidden( 'buttons_pos_woo_short' ).$pos[$this->p->options['buttons_pos_woo_short']].'</td>';
			return $rows;
		}
	}
}

?>
