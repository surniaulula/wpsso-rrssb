<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2019 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoRrssbSubmenuShareFacebook' ) ) {

	class WpssoRrssbSubmenuShareFacebook {

		private $p;

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array(
				'rrssb_share_facebook_rows' => 3,
			) );
		}

		public function filter_rrssb_share_facebook_rows( $table_rows, $form, $submenu ) {

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Show Button in', 'option label', 'wpsso-rrssb' ) ) .
			'<td>' . $submenu->show_on_checkboxes( 'fb' ) . '</td>';

			$table_rows[] = $form->get_th_html( _x( 'Preferred Order', 'option label', 'wpsso-rrssb' ) ).
			'<td>'.$form->get_select( 'fb_order', range( 1, count( $submenu->share ) ) ).'</td>';

			if ( $this->p->avail[ '*' ]['vary_ua'] ) {

				$table_rows[] = $form->get_tr_hide( 'basic', 'fb_platform' ).
				$form->get_th_html( _x( 'Allow for Platform', 'option label', 'wpsso-rrssb' ) ).
				'<td>'.$form->get_select( 'fb_platform', $this->p->cf['sharing']['platform'] ).'</td>';
			}

			$table_rows[] = $form->get_tr_hide( 'basic', 'fb_rrssb_html' ).
			'<td colspan="2">'.$form->get_textarea( 'fb_rrssb_html', 'button_html code' ).'</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'WpssoRrssbShareFacebook' ) ) {

	class WpssoRrssbShareFacebook {

		private $p;
		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'fb_order'         => 2,
					'fb_on_content'    => 1,
					'fb_on_excerpt'    => 0,
					'fb_on_sidebar'    => 0,
					'fb_on_admin_edit' => 1,
					'fb_platform'      => 'any',
					'fb_rrssb_html'    => '<li class="rrssb-facebook">
	<a href="https://www.facebook.com/sharer/sharer.php?u=%%sharing_url%%" class="popup">
		<span class="rrssb-icon">
			<svg xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid" width="29" height="29" viewBox="0 0 29 29">
				<path d="M26.4 0H2.6C1.714 0 0 1.715 0 2.6v23.8c0 .884 1.715 2.6 2.6 2.6h12.393V17.988h-3.996v-3.98h3.997v-3.062c0-3.746 2.835-5.97 6.177-5.97 1.6 0 2.444.173 2.845.226v3.792H21.18c-1.817 0-2.156.9-2.156 2.168v2.847h5.045l-.66 3.978h-4.386V29H26.4c.884 0 2.6-1.716 2.6-2.6V2.6c0-.885-1.716-2.6-2.6-2.6z" class="cls-2" fill-rule="evenodd"/>
			</svg>
		</span>
		<span class="rrssb-text"></span>
	</a>
</li><!-- .rrssb-facebook -->',
				),
			),
		);

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array(
				'get_defaults' => 1,
			) );
		}

		public function filter_get_defaults( $def_opts ) {
			return array_merge( $def_opts, self::$cf['opt']['defaults'] );
		}

		public function get_html( array $atts, array $opts, array $mod ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			return $this->p->util->replace_inline_vars( '<!-- Facebook Button -->'.
				$this->p->options['fb_rrssb_html'], $mod, $atts );
		}
	}
}
