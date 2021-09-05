<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2021 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbSubmenuShareTumblr' ) ) {

	class WpssoRrssbSubmenuShareTumblr {

		private $p;	// Wpsso class object.

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array(
				'rrssb_share_tumblr_rows' => 3,
			) );
		}

		public function filter_rrssb_share_tumblr_rows( $table_rows, $form, $submenu ) {

			$table_rows[] = '' .
				$form->get_th_html( _x( 'Show Button in', 'option label', 'wpsso-rrssb' ) ) .
				'<td>' . $submenu->show_on_checkboxes( 'tumblr' ) . '</td>';

			$table_rows[] = '' .
				$form->get_th_html( _x( 'Preferred Order', 'option label', 'wpsso-rrssb' ) ) . 
				'<td>' . $form->get_select( 'tumblr_button_order', range( 1, count( $submenu->share ) ) ) . '</td>';

			$table_rows[] = $form->get_tr_hide( 'basic', 'tumblr_utm_source' ) .
				$form->get_th_html( _x( 'UTM Source', 'option label', 'wpsso-rrssb' ) ) . 
				'<td>' . $form->get_input( 'tumblr_utm_source' ) . '</td>';

			$table_rows[] = $form->get_tr_hide( 'basic', 'tumblr_caption_max_len' ) . 
				$form->get_th_html( _x( 'Summary Text Length', 'option label', 'wpsso-rrssb' ) ) . 
				'<td>' . $form->get_input( 'tumblr_caption_max_len', $css_class = 'chars' ) . ' ' . 
				_x( 'characters or less', 'option comment', 'wpsso-rrssb' ) . '</td>';

			$table_rows[] = $form->get_tr_hide( 'basic', 'tumblr_rrssb_html' ) . 
				'<td colspan="2">' . $form->get_textarea( 'tumblr_rrssb_html', 'button_html code' ) . '</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'WpssoRrssbShareTumblr' ) ) {

	class WpssoRrssbShareTumblr {

		private $p;

		private static $cf = array(
			'opt' => array(
				'defaults' => array(
					'tumblr_on_admin_edit'    => 1,
					'tumblr_on_content'       => 1,
					'tumblr_on_excerpt'       => 0,
					'tumblr_on_sidebar'       => 0,
					'tumblr_on_woo_short'     => 1,
					'tumblr_button_order'     => 8,
					'tumblr_utm_source'       => 'tumblr',
					'tumblr_caption_max_len'  => 300,
					'tumblr_rrssb_html'       => '<li class="rrssb-tumblr">
	<a href="http://tumblr.com/share/link?url=%%sharing_url%%&name=%%tumblr_title%%&description=%%tumblr_summary%%" class="popup">
		<span class="rrssb-icon">
			<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28">
				<path d="M18.02 21.842c-2.03.052-2.422-1.396-2.44-2.446v-7.294h4.73V7.874H15.6V1.592h-3.714s-.167.053-.182.186c-.218 1.935-1.144 5.33-4.988 6.688v3.637h2.927v7.677c0 2.8 1.7 6.7 7.3 6.6 1.863-.03 3.934-.795 4.392-1.453l-1.22-3.54c-.52.213-1.415.413-2.115.455z" />
			</svg>
		</span>
		<span class="rrssb-text"></span>
	</a>
</li><!-- .rrssb-tumblr -->',
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

			return array_merge( $def_opts, self::$cf[ 'opt' ][ 'defaults' ] );
		}

		/**
		 * Pre-defined attributes:
		 *
		 *	'use_post'
		 *	'add_page'
		 *	'sharing_url'
		 *	'sharing_short_url'
		 *	'rawurlencode' (true)
		 */
		public function get_html( $mod, $atts ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			$extras = array(
				'tumblr_title' => $this->p->page->get_caption( $type = 'title', $max_len = 0, $mod,
					$read_cache = true, $add_hashtags = false, $do_encode = false, $md_key = 'tumblr_title' ),
				'tumblr_summary' => $this->p->page->get_caption( $type = 'excerpt', $this->p->options[ 'tumblr_caption_max_len' ], $mod,
					$read_cache = true, $add_hashtags = false, $do_encode = false, $md_key = 'tumblr_desc' ),
			);

			return $this->p->util->replace_inline_variables( $this->p->options[ 'tumblr_rrssb_html' ], $mod, $atts, $extras );
		}
	}
}
