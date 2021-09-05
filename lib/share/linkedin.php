<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2021 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbSubmenuShareLinkedin' ) ) {

	class WpssoRrssbSubmenuShareLinkedin {

		private $p;	// Wpsso class object.

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array(
				'rrssb_share_linkedin_rows' => 3,
			) );
		}

		public function filter_rrssb_share_linkedin_rows( $table_rows, $form, $submenu ) {

			$table_rows[] = '' .
				$form->get_th_html( _x( 'Show Button in', 'option label', 'wpsso-rrssb' ) ) .
				'<td>' . $submenu->show_on_checkboxes( 'linkedin' ) . '</td>';

			$table_rows[] = '' .
				$form->get_th_html( _x( 'Preferred Order', 'option label', 'wpsso-rrssb' ) ) . 
				'<td>' . $form->get_select( 'linkedin_button_order', range( 1, count( $submenu->share ) ) ) . '</td>';

			$table_rows[] = $form->get_tr_hide( 'basic', 'linkedin_utm_source' ) .
				$form->get_th_html( _x( 'UTM Source', 'option label', 'wpsso-rrssb' ) ) . 
				'<td>' . $form->get_input( 'linkedin_utm_source' ) . '</td>';

			$table_rows[] = $form->get_tr_hide( 'basic', 'linkedin_caption_max_len' ) . 
				$form->get_th_html( _x( 'Caption Text Length', 'option label', 'wpsso-rrssb' ) ) . 
				'<td>' . $form->get_input( 'linkedin_caption_max_len', $css_class = 'chars' ) . ' ' . 
				_x( 'characters or less', 'option comment', 'wpsso-rrssb' ) . '</td>';

			$table_rows[] = $form->get_tr_hide( 'basic', 'linkedin_rrssb_html' ) . 
				'<td colspan="2">' . $form->get_textarea( 'linkedin_rrssb_html', 'button_html code' ) . '</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'WpssoRrssbShareLinkedin' ) ) {

	class WpssoRrssbShareLinkedin {

		private $p;

		private static $cf = array(
			'opt' => array(
				'defaults' => array(
					'linkedin_on_admin_edit'    => 1,
					'linkedin_on_content'       => 1,
					'linkedin_on_excerpt'       => 0,
					'linkedin_on_sidebar'       => 0,
					'linkedin_on_woo_short'     => 1,
					'linkedin_button_order'     => 5,
					'linkedin_utm_source'       => 'linkedin',
					'linkedin_caption_max_len'  => 300,
					'linkedin_rrssb_html'       => '<li class="rrssb-linkedin">
	<a href="http://www.linkedin.com/shareArticle?mini=true&url=%%sharing_url%%&title=%%linkedin_title%%&summary=%%linkedin_caption%%" class="popup">
		<span class="rrssb-icon">
			<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28">
				<path d="M25.424 15.887v8.447h-4.896v-7.882c0-1.98-.71-3.33-2.48-3.33-1.354 0-2.158.91-2.514 1.802-.13.315-.162.753-.162 1.194v8.216h-4.9s.067-13.35 0-14.73h4.9v2.087c-.01.017-.023.033-.033.05h.032v-.05c.65-1.002 1.812-2.435 4.414-2.435 3.222 0 5.638 2.106 5.638 6.632zM5.348 2.5c-1.676 0-2.772 1.093-2.772 2.54 0 1.42 1.066 2.538 2.717 2.546h.032c1.71 0 2.77-1.132 2.77-2.546C8.056 3.593 7.02 2.5 5.344 2.5h.005zm-2.48 21.834h4.896V9.604H2.867v14.73z" />
			</svg>
		</span>
		<span class="rrssb-text"></span>
	</a>
</li><!-- .rrssb-linkedin -->',
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
		 *
		 * Note that for backwards compatibility, the 'sharing_short_url' value also replaces the '%%short_url%%' variable.
		 */
		public function get_html( $mod, $atts ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			$extras = array(
		 		'linkedin_title' => $this->p->page->get_caption( $type = 'title', $max_len = 0, $mod,
					$read_cache = true, $add_hashtags = false, $do_encode = false, $md_key = 'linkedin_title' ),
				'linkedin_caption' => $this->p->page->get_caption( $type = 'excerpt', $this->p->options[ 'linkedin_caption_max_len' ], $mod,
					$read_cache = true, $add_hashtags = false, $do_encode = false, $md_key = 'linkedin_desc' ),
			);

			return $this->p->util->replace_inline_variables( $this->p->options[ 'linkedin_rrssb_html' ], $mod, $atts, $extras );
		}
	}
}
