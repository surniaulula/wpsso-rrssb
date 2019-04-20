<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2019 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoRrssbSubmenuShareEmail' ) ) {

	class WpssoRrssbSubmenuShareEmail {

		private $p;

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array(
				'rrssb_share_email_rows' => 3,
			) );
		}

		public function filter_rrssb_share_email_rows( $table_rows, $form, $submenu ) {

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Show Button in', 'option label', 'wpsso-rrssb' ) ) .
			'<td>' . $submenu->show_on_checkboxes( 'email' ) . '</td>';

			$table_rows[] = $form->get_th_html( _x( 'Preferred Order', 'option label', 'wpsso-rrssb' ) ).
			'<td>'.$form->get_select( 'email_order', range( 1, count( $submenu->share ) ) ).'</td>';

			if ( $this->p->avail[ '*' ]['vary_ua'] ) {

				$table_rows[] = $form->get_tr_hide( 'basic', 'email_platform' ).
				$form->get_th_html( _x( 'Allow for Platform', 'option label', 'wpsso-rrssb' ) ).
				'<td>'.$form->get_select( 'email_platform', $this->p->cf['sharing']['platform'] ).'</td>';
			}

			$table_rows[] = $form->get_tr_hide( 'basic', 'email_caption_max_len' ).
                        $form->get_th_html( _x( 'Email Message Length', 'option label', 'wpsso-rrssb' ) ).
			'<td>'.$form->get_input( 'email_caption_max_len', 'short' ) . ' ' . 
				_x( 'characters or less', 'option comment', 'wpsso-rrssb' ).'</td>';

			$table_rows[] = $form->get_tr_hide( 'basic', 'email_caption_hashtags' ).
			$form->get_th_html( _x( 'Append Hashtags to Message', 'option label', 'wpsso-rrssb' ) ).
			'<td>'.$form->get_select( 'email_caption_hashtags', range( 0, $this->p->cf['form']['max_hashtags'] ), 'short', '', true ) . ' ' . 
				_x( 'tag names', 'option comment', 'wpsso-rrssb' ).'</td>';

			$table_rows[] = $form->get_tr_hide( 'basic', 'email_rrssb_html' ).
			'<td colspan="2">'.$form->get_textarea( 'email_rrssb_html', 'button_html code' ).'</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'WpssoRrssbShareEmail' ) ) {

	class WpssoRrssbShareEmail {

		private $p;
		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'email_order'            => 1,
					'email_on_content'       => 1,
					'email_on_excerpt'       => 0,
					'email_on_sidebar'       => 0,
					'email_on_admin_edit'    => 0,
					'email_platform'         => 'any',
					'email_caption_max_len'  => 500,
					'email_caption_hashtags' => 0,
					'email_rrssb_html'       => '<li class="rrssb-email">
	<a href="mailto:?subject=Share:%20%%email_title%%&body=%%email_excerpt%%%0D%0A%0D%0AShared%20from%20%%sharing_url%%%0D%0A">
		<span class="rrssb-icon">
			<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28">
				<path d="M20.11 26.147c-2.335 1.05-4.36 1.4-7.124 1.4C6.524 27.548.84 22.916.84 15.284.84 7.343 6.602.45 15.4.45c6.854 0 11.8 4.7 11.8 11.252 0 5.684-3.193 9.265-7.398 9.3-1.83 0-3.153-.934-3.347-2.997h-.077c-1.208 1.986-2.96 2.997-5.023 2.997-2.532 0-4.36-1.868-4.36-5.062 0-4.75 3.503-9.07 9.11-9.07 1.713 0 3.7.4 4.6.972l-1.17 7.203c-.387 2.298-.115 3.3 1 3.4 1.674 0 3.774-2.102 3.774-6.58 0-5.06-3.27-8.994-9.304-8.994C9.05 2.87 3.83 7.545 3.83 14.97c0 6.5 4.2 10.2 10 10.202 1.987 0 4.09-.43 5.647-1.245l.634 2.22zM16.647 10.1c-.31-.078-.7-.155-1.207-.155-2.572 0-4.596 2.53-4.596 5.53 0 1.5.7 2.4 1.9 2.4 1.44 0 2.96-1.83 3.31-4.088l.592-3.72z" />
			</svg>
		</span>
		<span class="rrssb-text"></span>
	</a>
</li><!-- .rrssb-email -->',
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

			$atts[ 'add_hashtags' ] = empty( $this->p->options['email_caption_hashtags'] ) ? false : $this->p->options['email_caption_hashtags'];

			$email_title   = $this->p->page->get_caption( 'title', 0, $mod, true, false, false, 'email_title' );
			$email_excerpt = $this->p->page->get_caption( 'both', $opts['email_caption_max_len'], $mod, true,
				$atts[ 'add_hashtags' ], false, 'email_desc' );

			return $this->p->util->replace_inline_vars( '<!-- Email Button -->'.
				$this->p->options['email_rrssb_html'], $mod, $atts, array(
				 	'email_title'   => rawurlencode( $email_title ),
			 		'email_excerpt' => rawurlencode( $email_excerpt ),
				 )
			 );
		}
	}
}

