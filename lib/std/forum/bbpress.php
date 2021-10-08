<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2021 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbStdForumBbpress' ) ) {

	class WpssoRrssbStdForumBbpress {

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

			if ( class_exists( 'bbpress' ) ) {

				$classname = __CLASS__ . 'Sharing';

				$this->sharing = new $classname( $this->p );
			}
		}
	}
}

if ( ! class_exists( 'WpssoRrssbStdForumBbpressSharing' ) ) {

	class WpssoRrssbStdForumBbpressSharing {

		private $p;	// Wpsso class object.

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
					'rrssb_buttons_show_on'       => 1,
					'rrssb_buttons_position_rows' => 2,
					'rrssb_styles_tabs'           => 1,
				) );

			} else {

				$location = empty( $this->p->options[ 'buttons_pos_bbp_single' ] ) ? 'top' : $this->p->options[ 'buttons_pos_bbp_single' ];

				switch ( $location ) {

					case 'top':

						$pos_bbp_single = array( 
							'bbp_template_before_single_forum',
							'bbp_template_before_single_reply',
							'bbp_template_before_single_topic',
						);

						break;

					case 'bottom':

						$pos_bbp_single = array( 
							'bbp_template_after_single_forum',
							'bbp_template_after_single_reply',
							'bbp_template_after_single_topic',
						);

						break;

					case 'both':

						$pos_bbp_single = array( 
							'bbp_template_before_single_forum',
							'bbp_template_before_single_reply',
							'bbp_template_before_single_topic',
							'bbp_template_after_single_forum',
							'bbp_template_after_single_reply',
							'bbp_template_after_single_topic',
						);

						break;

					default:

						if ( $this->p->debug->enabled ) {

							$this->p->debug->log( 'unrecognized value for buttons_pos_bbp_single option' );
						}

						$pos_bbp_single = array();

						break;
				}

				foreach ( $pos_bbp_single as $bbp_action ) {

					add_action( $bbp_action, array( $this, 'add_bbp_template_single' ), 90 );
				}
			}
		}

		public function filter_get_defaults( $opts_def ) {

			$opts_def[ 'buttons_pos_bbp_single' ] = 'top';	// Default position in bbPress Single.

			foreach ( $this->p->cf[ 'opt' ][ 'cm_prefix' ] as $id => $opt_pre ) {

				$opts_def[ $opt_pre . '_on_bbp_single' ] = 0;
			}

			return $opts_def;
		}

		public function filter_rrssb_buttons_show_on( $show_on = array() ) {

			$show_on[ 'bbp_single' ] = _x( 'bbPress Single', 'option value', 'wpsso-rrssb' );

			return $show_on;
		}

		public function filter_rrssb_buttons_position_rows( $table_rows, $form ) {

			$table_rows[ 'buttons_pos_bbp_single' ] = '' .
				$form->get_th_html( _x( 'Position in bbPress Single', 'option label', 'wpsso-rrssb' ),
					$css_class = '', $css_id = 'buttons_pos_bbp_single' ) . 
				'<td>' . $form->get_select( 'buttons_pos_bbp_single', $this->p->cf[ 'sharing' ][ 'position' ] ) . '</td>';

			return $table_rows;
		}

		public function filter_rrssb_styles( $styles ) {

			$styles[ 'rrssb-bbp_single' ] = _x( 'bbPress Single', 'option value', 'wpsso-rrssb' );

			return $styles;
		}

		public function filter_rrssb_styles_tabs( $styles ) {

			$styles[ 'rrssb-bbp_single' ] = _x( 'bbPress Single', 'option value', 'wpsso-rrssb' );

			return $styles;
		}

		public function add_bbp_template_single() {

			global $post;

			if ( ! empty( $post->ID ) ) {	// Just in case.

				if ( ! empty( $this->p->options[ 'buttons_add_to_' . $post->post_type ] ) ) {

					$rrssb =& WpssoRrssb::get_instance();

					echo $rrssb->social->get_buttons( $text = '', $type = 'bbp_single' );
				}
			}
		}
	}
}
