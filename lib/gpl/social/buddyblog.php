<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2019 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoRrssbGplSocialBuddyblog' ) ) {

	class WpssoRrssbGplSocialBuddyblog {

		private $p;
		private $sharing;

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			if ( is_admin() || bp_is_buddyblog_component() ) {

				if ( ! empty( $this->p->avail['p_ext']['rrssb'] ) ) {

					$classname = __CLASS__ . 'Sharing';

					if ( class_exists( $classname ) ) {
						$this->sharing = new $classname( $this->p );
					}
				}
			}
		}
	}
}

if ( ! class_exists( 'WpssoRrssbGplSocialBuddyblogSharing' ) ) {

	class WpssoRrssbGplSocialBuddyblogSharing {

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
		}

		public function filter_get_defaults( $opts_def ) {

			foreach ( $this->p->cf['opt']['cm_prefix'] as $id => $opt_pre ) {
				$opts_def[$opt_pre . '_on_bblog_post'] = 0;
			}

			return $opts_def;
		}

		public function filter_rrssb_buttons_position_rows( $table_rows, $form ) {

			$table_rows['buttons_pos_bblog_post'] = $form->get_th_html( _x( 'Position in BuddyBlog Post',
				'option label', 'wpsso-rrssb' ), '', 'buttons_pos_bblog_post' ) . 
			'<td class="blank">' . $form->get_no_select( 'buttons_pos_bblog_post', $this->p->cf['sharing']['position'] ) . '</td>';

			return $table_rows;	
		}

		public function filter_rrssb_buttons_show_on( $show_on = array(), $opt_pre = '' ) {

			$show_on['bblog_post'] = 'BBlog Post';

			$this->p->options[$opt_pre . '_on_bblog_post:is'] = 'disabled';

			return $show_on;
		}

		public function filter_rrssb_styles( $styles ) {

			return $this->filter_rrssb_styles_tabs( $styles );
		}

		public function filter_rrssb_styles_tabs( $styles ) {

			$styles['rrssb-bblog_post'] = 'BBlog Post';

			$this->p->options['buttons_css_rrssb-bblog_post:is'] = 'disabled';

			return $styles;
		}
	}
}
