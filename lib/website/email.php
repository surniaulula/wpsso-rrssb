<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoRrssbSubmenuSharingEmail' ) && class_exists( 'WpssoRrssbSubmenuSharingButtons' ) ) {

	class WpssoRrssbSubmenuSharingEmail extends WpssoRrssbSubmenuSharingButtons {

		public $id = '';
		public $name = '';
		public $form = '';

		public function __construct( &$plugin, $id, $name ) {
			$this->p =& $plugin;
			$this->id = $id;
			$this->name = $name;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
		}

		protected function get_rows( $metabox, $key ) {
			$rows = array();

			$rows[] = $this->p->util->get_th( 'Preferred Order', null, 'email_order' ).
			'<td>'.$this->form->get_select( 'email_order', 
				range( 1, count( $this->p->admin->submenu['sharing-buttons']->website ) ), 'short' ).  '</td>';

			$rows[] = $this->p->util->get_th( 'Show Button in' ).
			'<td>'.$this->show_on_checkboxes( 'email' ).'</td>';

			$rows[] = '<tr class="hide_in_basic">'.
                        $this->p->util->get_th( 'Email Message Length' ).
			'<td>'.$this->form->get_input( 'email_cap_len', 'short' ).' characters or less</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( 'Append Hashtags to Message' ).
			'<td>'.$this->form->get_select( 'email_cap_hashtags',
				range( 0, $this->p->cf['form']['max_hashtags'] ), 'short', null, true ).' tag names</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( 'Sharing Button HTML', null, 'email_html' ).
			'<td>'.$this->form->get_textarea( 'email_html', 'average code' ).'</td>';

			return $rows;
		}
	}
}

if ( ! class_exists( 'WpssoRrssbSharingEmail' ) ) {

	class WpssoRrssbSharingEmail {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'email_on_content' => 1,
					'email_on_excerpt' => 0,
					'email_on_sidebar' => 0,
					'email_on_admin_edit' => 0,
					'email_order' => 1,
					'email_cap_len' => 500,
					'email_cap_hashtags' => 0,
					'email_html' => '<li class="rrssb-email">
	<a href="mailto:?subject=Share:%20%%email_title%%&body=%%email_excerpt%%%0D%0A%0D%0AShared%20from%20%%sharing_url%%%0D%0A">
		<span class="rrssb-icon">
			<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28">
				<path d="M20.11 26.147c-2.335 1.05-4.36 1.4-7.124 1.4C6.524 27.548.84 22.916.84 15.284.84 7.343 6.602.45 15.4.45c6.854 0 11.8 4.7 11.8 11.252 0 5.684-3.193 9.265-7.398 9.3-1.83 0-3.153-.934-3.347-2.997h-.077c-1.208 1.986-2.96 2.997-5.023 2.997-2.532 0-4.36-1.868-4.36-5.062 0-4.75 3.503-9.07 9.11-9.07 1.713 0 3.7.4 4.6.972l-1.17 7.203c-.387 2.298-.115 3.3 1 3.4 1.674 0 3.774-2.102 3.774-6.58 0-5.06-3.27-8.994-9.304-8.994C9.05 2.87 3.83 7.545 3.83 14.97c0 6.5 4.2 10.2 10 10.202 1.987 0 4.09-.43 5.647-1.245l.634 2.22zM16.647 10.1c-.31-.078-.7-.155-1.207-.155-2.572 0-4.596 2.53-4.596 5.53 0 1.5.7 2.4 1.9 2.4 1.44 0 2.96-1.83 3.31-4.088l.592-3.72z" />
			</svg>
		</span>
		<span class="rrssb-text">email</span>
	</a>
</li><!-- .rrssb-email -->',
				),
			),
		);

		protected $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array(
				'get_defaults' => 1,
				'get_meta_defaults' => 2,
			) );
		}

		public function filter_get_meta_defaults( $opts_def, $mod ) {
			$meta_def = array(
				'email_title' => '',
				'email_desc' => '',
			);
			return array_merge( $opts_def, $meta_def );
		}

		public function filter_get_defaults( $opts_def ) {
			return array_merge( $opts_def, self::$cf['opt']['defaults'] );
		}

		public function get_html( $atts = array(), &$opts = array() ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( empty( $opts ) ) 
				$opts =& $this->p->options;

			$use_post = isset( $atts['use_post'] ) ?
				$atts['use_post'] : true;

			$add_hashtags = empty( $this->p->options['email_cap_hashtags'] ) ?
				false : $this->p->options['email_cap_hashtags'];

			if ( ! isset( $atts['add_page'] ) )
				$atts['add_page'] = true;

			if ( ! isset( $atts['source_id'] ) )
				$atts['source_id'] = $this->p->util->get_source_id( 'email', $atts );

			return $this->p->util->replace_inline_vars( $this->p->options['email_html'], $use_post, false, $atts, array(
				 	'email_title' => rawurlencode( $this->p->webpage->get_title( 0, '',
						$use_post, true, false, false, 'og_title', 'email' ) ),
				 	'email_excerpt' => rawurlencode( $this->p->webpage->get_caption( 'excerpt', $opts['email_cap_len'],
						$use_post, true, $add_hashtags, false, 'og_desc', 'email' ) ),
				 ) );
		}
	}
}

?>
