<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2019 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoRrssbStdSocialBuddyblog' ) ) {

	class WpssoRrssbStdSocialBuddyblog {

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

			if ( is_admin() || bp_is_buddyblog_component() ) {

				$classname = __CLASS__ . 'Sharing';

				$this->sharing = new $classname( $this->p );
			}
		}
	}
}

if ( ! class_exists( 'WpssoRrssbStdSocialBuddyblogSharing' ) ) {

	class WpssoRrssbStdSocialBuddyblogSharing {

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
					'rrssb_buttons_position_rows' => 2,
					'rrssb_buttons_show_on'       => 2,
					'rrssb_styles_tabs'           => 1,
				) );
			}
			
			if ( bp_is_buddyblog_component() ) {

				$location = empty( $this->p->options[ 'buttons_pos_bblog_post' ] ) ? 
					'bottom' : $this->p->options[ 'buttons_pos_bblog_post' ];
				
				switch ( $location ) {

					case 'top':

						add_action( 'buddyblog_before_blog_post', array( $this, 'show_post_buttons' ) );

						break;

					case 'bottom':

						add_action( 'buddyblog_after_blog_post', array( $this, 'show_post_buttons' ) );

						break;

					case 'both':

						add_action( 'buddyblog_before_blog_post', array( $this, 'show_post_buttons' ) );
						add_action( 'buddyblog_after_blog_post', array( $this, 'show_post_buttons' ) );

						break;
				}
			}
		}

		public function filter_get_defaults( $opts_def ) {

			foreach ( $this->p->cf[ 'opt' ][ 'cm_prefix' ] as $id => $opt_pre ) {
				$opts_def[ $opt_pre . '_on_bblog_post' ] = 0;
			}

			return $opts_def;
		}

		public function filter_rrssb_buttons_position_rows( $table_rows, $form ) {

			$table_rows[ 'buttons_pos_bblog_post' ] = $form->get_th_html( _x( 'Position in BuddyBlog Post',
				'option label', 'wpsso-rrssb' ), '', 'buttons_pos_bblog_post' ) . 
			'<td>' . $form->get_select( 'buttons_pos_bblog_post', $this->p->cf[ 'sharing' ][ 'position' ] ) . '</td>';

			return $table_rows;	
		}

		public function filter_rrssb_buttons_show_on( $show_on = array(), $opt_pre = '' ) {

			$show_on[ 'bblog_post' ] = 'BBlog Post';

			return $show_on;
		}

		public function filter_rrssb_styles( $styles ) {

			return $this->filter_rrssb_styles_tabs( $styles );
		}

		public function filter_rrssb_styles_tabs( $styles ) {

			$styles[ 'rrssb-bblog_post' ] = 'BBlog Post';

			return $styles;
		}

		public function show_post_buttons() {

			$rrssb =& WpssoRrssb::get_instance();

			echo $rrssb->social->get_buttons( '', 'bblog_post' );
		}
	}
}
