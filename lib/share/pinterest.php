<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2022 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbSubmenuSharePinterest' ) ) {

	class WpssoRrssbSubmenuSharePinterest {

		private $p;	// Wpsso class object.

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array(
				'rrssb_share_pinterest_rows'  => 4,
			) );
		}

		public function filter_rrssb_share_pinterest_rows( $table_rows, $form, $network, $submenu_obj ) {

			$utm_source_label = sprintf( _x( 'UTM Source for %s', 'option label', 'wpsso-rrssb' ), 'Pinterest' );

			$table_rows[] = '' .
				$form->get_th_html( _x( 'Show Button in', 'option label', 'wpsso-rrssb' ) ) .
				'<td>' . $submenu_obj->show_on_checkboxes( 'pin' ) . '</td>';

			$table_rows[] = '' .
				$form->get_th_html( _x( 'Preferred Order', 'option label', 'wpsso-rrssb' ) ) . 
				'<td>' . $form->get_select( 'pin_button_order', range( 1, count( $submenu_obj->share ) ) ) . '</td>';

			$table_rows[] = $form->get_tr_hide( 'basic', 'pin_utm_source' ) .
				$form->get_th_html( $utm_source_label ) . 
				'<td>' . $form->get_input( 'pin_utm_source' ) . '</td>';

			$table_rows[] = $form->get_tr_hide( 'basic', 'pin_caption_max_len' ) . 
				$form->get_th_html( _x( 'Caption Text Length', 'option label', 'wpsso-rrssb' ) ) . 
				'<td>' . $form->get_input( 'pin_caption_max_len', $css_class = 'chars' ) . ' ' . 
				_x( 'characters or less', 'option comment', 'wpsso-rrssb' ) . '</td>';

			$table_rows[] = $form->get_tr_hide( 'basic', 'pin_rrssb_html' ) . 
				'<td colspan="2">' . $form->get_textarea( 'pin_rrssb_html', 'button_html code' ) . '</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'WpssoRrssbSharePinterest' ) ) {

	class WpssoRrssbSharePinterest {

		private $p;

		private static $cf = array(
			'opt' => array(
				'defaults' => array(
					'pin_on_admin_edit'    => 1,
					'pin_on_content'       => 1,
					'pin_on_excerpt'       => 0,
					'pin_on_sidebar'       => 0,
					'pin_button_order'     => 4,
					'pin_utm_source'       => 'pinterest',
					'pin_caption_max_len'  => 300,
					'pin_rrssb_html'       => '<li class="rrssb-pinterest">
	<a href="http://pinterest.com/pin/create/button/?url=%%sharing_url%%&media=%%media_url%%&description=%%pinterest_caption%%" class="popup wp-block-file__button">
		<span class="rrssb-icon">
			<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28">
				<path d="M14.02 1.57c-7.06 0-12.784 5.723-12.784 12.785S6.96 27.14 14.02 27.14c7.062 0 12.786-5.725 12.786-12.785 0-7.06-5.724-12.785-12.785-12.785zm1.24 17.085c-1.16-.09-1.648-.666-2.558-1.22-.5 2.627-1.113 5.146-2.925 6.46-.56-3.972.822-6.952 1.462-10.117-1.094-1.84.13-5.545 2.437-4.632 2.837 1.123-2.458 6.842 1.1 7.557 3.71.744 5.226-6.44 2.924-8.775-3.324-3.374-9.677-.077-8.896 4.754.19 1.178 1.408 1.538.49 3.168-2.13-.472-2.764-2.15-2.683-4.388.132-3.662 3.292-6.227 6.46-6.582 4.008-.448 7.772 1.474 8.29 5.24.58 4.254-1.815 8.864-6.1 8.532v.003z" />
			</svg>
		</span>
		<span class="rrssb-text"></span>
	</a>
</li><!-- .rrssb-pinterest -->',
				),
			),
		);

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			/**
			 * Make sure the Pinterest Pin It image size is available.
			 */
			$this->p->options[ 'pin_add_img_html' ]          = 1;
			$this->p->options[ 'pin_add_img_html:disabled' ] = true;

			$this->p->util->add_plugin_filters( $this, array( 
				'get_defaults' => 1,
			) );
		}

		public function filter_get_defaults( $defs ) {

			return array_merge( $defs, self::$cf[ 'opt' ][ 'defaults' ] );
		}

		/**
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

			$size_name     = 'wpsso-pinterest';
			$media_request = array( 'img_url' );
			$media_info    = $this->p->og->get_media_info( $size_name, $media_request, $mod, $md_pre = array( 'pin', 'schema', 'og' ) );

			if ( empty( $media_info[ 'img_url' ] ) ) {

				return '<!-- pinterest button: no media url available -->';
			}

			$atts[ 'media_url' ] = $media_info[ 'img_url' ];

			$atts[ 'pinterest_caption' ] = $this->p->page->get_caption( $type = 'excerpt', $this->p->options[ 'pin_caption_max_len' ], $mod,
				$add_hashtags = false, $do_encode = false, $md_key = array ( 'pin_desc', 'pin_img_desc', 'og_desc' ) );

			return $this->p->util->inline->replace_variables( $this->p->options[ 'pin_rrssb_html' ], $mod, $atts );
		}
	}
}
