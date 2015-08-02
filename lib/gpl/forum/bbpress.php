<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoRrssbGplForumBbpress' ) ) {

	class WpssoRrssbGplForumBbpress {

		private $p;
		private $sharing;
		private $has_setup = false;
		private $post_id;
		private $post_type;
		private $topic_type = 'topic';
		private $forum_type = 'forum';
		private $reply_type = 'reply';

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( class_exists( 'bbpress' ) ) {	// is_bbpress() is not available here
				if ( array_key_exists( 'rrssb', $this->p->is_avail ) &&
					$this->p->is_avail['rrssb'] === true ) {
					$classname = __CLASS__.'Sharing';
					$this->sharing = new $classname( $this->p );
				}
			}
		}
	}
}

if ( ! class_exists( 'WpssoRrssbGplForumBbpressSharing' ) ) {

	class WpssoRrssbGplForumBbpressSharing {

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
					'sharing_position_rows' => 2,
				) );
			}
		}

		public function filter_get_defaults( $opts_def ) {
			foreach ( $this->p->cf['opt']['pre'] as $name => $prefix )
				$opts_def[$prefix.'_on_bbp_single'] = 0;
			$opts_def['buttons_pos_bbp_single'] = 'top';
			return $opts_def;
		}

		public function filter_sharing_show_on( $show_on = array(), $prefix ) {
			switch ( $prefix ) {
				case 'pin':
					break;
				default:
					$show_on['bbp_single'] = 'bbPress Single';
					$this->p->options[$prefix.'_on_bbp_single:is'] = 'disabled';
					break;
			}
			return $show_on;
		}

		public function filter_style_tabs( $tabs ) {
			$tabs['rrssb-bbp_single'] = 'bbPress Single';
			$this->p->options['buttons_css_rrssb-bbp_single:is'] = 'disabled';
			return $tabs;
		}

		public function filter_sharing_position_rows( $rows, $form ) {
			$pos = array( 'top' => 'Top', 'bottom' => 'Bottom', 'both' => 'Both Top and Bottom' );
			$rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg', array( 'lca' => 'wpssorrssb' ) ).'</td>';
			$rows['buttons_pos_bbp_single'] = $this->p->util->get_th( 'Position in bbPress Single', null, 'buttons_pos_bbp_single' ).
			'<td class="blank">'.$form->get_hidden( 'buttons_pos_bbp_single' ).$pos[$this->p->options['buttons_pos_bbp_single']].'</td>';
			return $rows;
		}
	}
}

?>
