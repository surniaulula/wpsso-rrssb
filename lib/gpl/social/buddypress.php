<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoRrssbGplSocialBuddypress' ) ) {

	class WpssoRrssbGplSocialBuddypress {

		private $p;
		private $sharing;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( is_admin() || bp_current_component() ) {
				if ( ! empty( $this->p->is_avail['rrssb'] ) ) {
					$classname = __CLASS__.'Sharing';
					if ( class_exists( $classname ) )
						$this->sharing = new $classname( $this->p );
				}
			}
		}
	}
}

if ( ! class_exists( 'WpssoRrssbGplSocialBuddypressSharing' ) ) {

	class WpssoRrssbGplSocialBuddypressSharing {

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
					'rrssb_buttons_show_on' => 2,
					'rrssb_styles_tabs' => 1,
				) );
			}
		}

		public function filter_get_defaults( $opts_def ) {
			foreach ( $this->p->cf['opt']['pre'] as $name => $prefix )
				$opts_def[$prefix.'_on_bp_activity'] = 0;
			return $opts_def;
		}

		public function filter_rrssb_buttons_show_on( $show_on = array(), $prefix = '' ) {
			switch ( $prefix ) {
				case 'pin':
					break;
				default:
					$show_on['bp_activity'] = 'BP Activity';
					$this->p->options[$prefix.'_on_bp_activity:is'] = 'disabled';
					break;
			}
			return $show_on;
		}

		public function filter_rrssb_styles_tabs( $tabs ) {
			$tabs['rrssb-bp_activity'] = 'BP Activity';
			$this->p->options['buttons_css_rrssb-bp_activity:is'] = 'disabled';
			return $tabs;
		}
	}
}

?>
