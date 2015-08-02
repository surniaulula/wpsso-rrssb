<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
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
				$this->p->util->add_plugin_filters( $this, array( 
					'post_types' => 3,
				) );
				if ( ! empty( $this->p->is_avail['rrssb'] ) ) {
					$classname = __CLASS__.'Sharing';
					if ( class_exists( $classname ) )
						$this->sharing = new $classname( $this->p );
				}
			}
		}

		/* Purpose: Provide custom post types for wpssossb, without having to register them with WordPress */
		public function filter_post_types( $pt, $prefix, $output = 'objects' ) {
			if ( $prefix == 'buttons' ) {
				if ( $output == 'objects' ) {
					foreach ( array( 
						'activity' => 'Activity',
						'group' => 'Group',
						'members' => 'Members',
					) as $name => $desc ) {
						$pt['bp_'.$name] = new stdClass();
						$pt['bp_'.$name]->public = true;
						$pt['bp_'.$name]->name = 'bp_'.$name;
						$pt['bp_'.$name]->label = $desc;
						$pt['bp_'.$name]->description = 'BuddyPress '.$desc;
					}
				}
			}
			return $pt;
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
					'sharing_show_on' => 2,
					'style_tabs' => 1,
				) );
			}
		}

		public function filter_get_defaults( $opts_def ) {
			foreach ( $this->p->cf['opt']['pre'] as $name => $prefix )
				$opts_def[$prefix.'_on_bp_activity'] = 0;
			return $opts_def;
		}


		public function filter_sharing_show_on( $show_on = array(), $prefix ) {
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
		public function filter_style_tabs( $tabs ) {
			$tabs['rrssb-bp_activity'] = 'BP Activity';
			$this->p->options['buttons_css_rrssb-bp_activity:is'] = 'disabled';
			return $tabs;
		}
	}
}

?>
