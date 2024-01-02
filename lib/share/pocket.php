<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbSubmenuSharePocket' ) ) {

	class WpssoRrssbSubmenuSharePocket {

		private $p;	// Wpsso class object.
		private $s;	// Wpsso RRSSB submenu class object.

		public function __construct( &$plugin, &$submenu ) {

			$this->p =& $plugin;
			$this->s =& $submenu;

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array(
				'mb_rrssb_buttons_pocket_rows' => 4,
			) );
		}

		public function filter_mb_rrssb_buttons_pocket_rows( $table_rows, $form, $args ) {

			$utm_src_label = sprintf( _x( 'UTM Source for %s', 'option label', 'wpsso-rrssb' ), 'Pocket' );

			$table_rows[ 'pocket_show_button'] = '' .
				$form->get_th_html( _x( 'Show Button in', 'option label', 'wpsso-rrssb' ) ) .
				'<td>' . $this->s->get_show_on_checkboxes( 'pocket' ) . '</td>';

			$table_rows[ 'pocket_button_order' ] = '' .
				$form->get_th_html( _x( 'Preferred Order', 'option label', 'wpsso-rrssb' ) ) .
				'<td>' . $form->get_select( 'pocket_button_order', range( 1, count( $this->s->share ) ) ) . '</td>';

			$table_rows[ 'pocket_utm_source' ] = $form->get_tr_hide( $in_view = 'basic', 'pocket_utm_source' ) .
				$form->get_th_html( $utm_src_label ) .
				'<td>' . $form->get_input( 'pocket_utm_source' ) . '</td>';

			$table_rows[ 'pocket_rrssb_html' ] = $form->get_tr_hide( $in_view = 'basic', 'pocket_rrssb_html' ) .
				'<td colspan="2">' . $form->get_textarea( 'pocket_rrssb_html', 'button_html code' ) . '</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'WpssoRrssbSharePocket' ) ) {

	class WpssoRrssbSharePocket {

		private $p;

		private static $cf = array(
			'opt' => array(
				'defaults' => array(
					'pocket_on_admin_edit' => 1,
					'pocket_on_content'    => 1,
					'pocket_on_excerpt'    => 0,
					'pocket_on_sidebar'    => 0,
					'pocket_button_order'  => 7,
					'pocket_utm_source'    => 'pocket',
					'pocket_rrssb_html'    => '<li class="rrssb-pocket">
	<a href="https://getpocket.com/save?url=%%sharing_url%%" class="popup wp-block-file__button">
		<span class="rrssb-icon">
			<svg width="32" height="28" viewBox="0 0 32 28" xmlns="http://www.w3.org/2000/svg">
				<path d="M28.782.002c2.03.002 3.193 1.12 3.182 3.106-.022 3.57.17 7.16-.158 10.7-1.09 11.773-14.588 18.092-24.6 11.573C2.72 22.458.197 18.313.057 12.937c-.09-3.36-.05-6.72-.026-10.08C.04 1.113 1.212.016 3.02.008 7.347-.006 11.678.004 16.006.002c4.258 0 8.518-.004 12.776 0zM8.65 7.856c-1.262.135-1.99.57-2.357 1.476-.392.965-.115 1.81.606 2.496 2.453 2.334 4.91 4.664 7.398 6.966 1.086 1.003 2.237.99 3.314-.013 2.407-2.23 4.795-4.482 7.17-6.747 1.203-1.148 1.32-2.468.365-3.426-1.01-1.014-2.302-.933-3.558.245-1.596 1.497-3.222 2.965-4.75 4.526-.706.715-1.12.627-1.783-.034-1.597-1.596-3.25-3.138-4.93-4.644-.47-.42-1.123-.647-1.478-.844z" />
			</svg>
		</span>
		<span class="rrssb-text"></span>
	</a>
</li><!-- .rrssb-pocket -->',
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

		public function filter_get_defaults( array $defs ) {

			return array_merge( $defs, self::$cf[ 'opt' ][ 'defaults' ] );
		}

		/*
		 * Pre-defined attributes:
		 *
		 *	'use_post'
		 *	'add_page'
		 *	'sharing_url'
		 *	'sharing_short_url'
		 *	'rawurlencode' (true)
		 *
		 * Note that the $atts array may include additional user input from the RRSSB shortcode attributes.
		 */
		public function get_html( $mod, $atts ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			return $this->p->util->inline->replace_variables( $this->p->options[ 'pocket_rrssb_html' ], $mod, $atts );
		}
	}
}
