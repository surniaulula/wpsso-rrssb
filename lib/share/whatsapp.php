<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2015-2024 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbSubmenuShareWhatsApp' ) ) {

	class WpssoRrssbSubmenuShareWhatsApp {

		private $p;	// Wpsso class object.
		private $s;	// Wpsso RRSSB submenu class object.

		public function __construct( &$plugin, &$submenu ) {

			$this->p =& $plugin;
			$this->s =& $submenu;

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array(
				'mb_rrssb_buttons_whatsapp_rows' => 4,
			) );
		}

		public function filter_mb_rrssb_buttons_whatsapp_rows( $table_rows, $form, $args ) {

			$utm_src_label = sprintf( _x( 'UTM Source for %s', 'option label', 'wpsso-rrssb' ), 'WhatsApp' );

			$table_rows[ 'wa_show_button'] = '' .
				$form->get_th_html( _x( 'Show Button in', 'option label', 'wpsso-rrssb' ) ) .
				'<td>' . $this->s->get_show_on_checkboxes( 'wa' ) . '</td>';

			$table_rows[ 'wa_button_order' ] = '' .
				$form->get_th_html( _x( 'Preferred Order', 'option label', 'wpsso-rrssb' ) ) .
				'<td>' . $form->get_select( 'wa_button_order', range( 1, count( $this->s->share ) ) ) . '</td>';

			$table_rows[ 'wa_utm_source' ] = $form->get_tr_hide( $in_view = 'basic', 'wa_utm_source' ) .
				$form->get_th_html( $utm_src_label ) .
				'<td>' . $form->get_input( 'wa_utm_source' ) . '</td>';

			$table_rows[ 'wa_rrssb_html' ] = $form->get_tr_hide( $in_view = 'basic', 'wa_rrssb_html' ) .
				'<td colspan="2">' . $form->get_textarea( 'wa_rrssb_html', 'button_html code' ) . '</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'WpssoRrssbShareWhatsApp' ) ) {

	class WpssoRrssbShareWhatsApp {

		private $p;

		private static $cf = array(
			'opt' => array(
				'defaults' => array(
					'wa_on_admin_edit' => 1,
					'wa_on_content'    => 1,
					'wa_on_excerpt'    => 0,
					'wa_on_sidebar'    => 0,
					'wa_button_order'  => 10,
					'wa_utm_source'    => 'whatsapp',
					'wa_rrssb_html'    => '<li class="rrssb-whatsapp">
	<a href="whatsapp://send?text=%%title%%%20%%sharing_short_url%%" data-action="share/whatsapp/share" class="wp-block-file__button">
		<span class="rrssb-icon">
			<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="28" height="28" viewBox="0 0 90 90" enable-background="new 0 0 90 90;" xml:space="preserve">
				<path d="M90,43.841c0,24.213-19.779,43.841-44.182,43.841c-7.747,0-15.025-1.98-21.357-5.455L0,90l7.975-23.522c-4.023-6.606-6.34-14.354-6.34-22.637C1.635,19.628,21.416,0,45.818,0C70.223,0,90,19.628,90,43.841z M45.818,6.982c-20.484,0-37.146,16.535-37.146,36.859c0,8.065,2.629,15.534,7.076,21.61L11.107,79.14l14.275-4.537c5.865,3.851,12.891,6.097,20.437,6.097c20.481,0,37.146-16.533,37.146-36.857S66.301,6.982,45.818,6.982z M68.129,53.938c-0.273-0.447-0.994-0.717-2.076-1.254c-1.084-0.537-6.41-3.138-7.4-3.495c-0.993-0.358-1.717-0.538-2.438,0.537c-0.721,1.076-2.797,3.495-3.43,4.212c-0.632,0.719-1.263,0.809-2.347,0.271c-1.082-0.537-4.571-1.673-8.708-5.333c-3.219-2.848-5.393-6.364-6.025-7.441c-0.631-1.075-0.066-1.656,0.475-2.191c0.488-0.482,1.084-1.255,1.625-1.882c0.543-0.628,0.723-1.075,1.082-1.793c0.363-0.717,0.182-1.344-0.09-1.883c-0.27-0.537-2.438-5.825-3.34-7.977c-0.902-2.15-1.803-1.792-2.436-1.792c-0.631,0-1.354-0.09-2.076-0.09c-0.722,0-1.896,0.269-2.889,1.344c-0.992,1.076-3.789,3.676-3.789,8.963c0,5.288,3.879,10.397,4.422,11.113c0.541,0.716,7.49,11.92,18.5,16.223 C58.2,65.771,58.2,64.336,60.186,64.156c1.984-0.179,6.406-2.599,7.312-5.107C68.398,56.537,68.398,54.386,68.129,53.938z"/>
			</svg>
		</span>
		<span class="rrssb-text"></span>
	</a>
</li><!-- .rrssb-whatsapp -->',
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

			return $this->p->util->inline->replace_variables( $this->p->options[ 'wa_rrssb_html' ], $mod, $atts );
		}
	}
}
