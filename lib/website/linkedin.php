<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoRrssbSubmenuSharingLinkedin' ) && class_exists( 'WpssoRrssbSubmenuSharingButtons' ) ) {

	class WpssoRrssbSubmenuSharingLinkedin extends WpssoRrssbSubmenuSharingButtons {

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

			$rows[] = $this->p->util->get_th( 'Preferred Order', null, 'linkedin_order' ).
			'<td>'.$this->form->get_select( 'linkedin_order', 
				range( 1, count( $this->p->admin->submenu['sharing-buttons']->website ) ), 'short' ).  '</td>';

			$rows[] = $this->p->util->get_th( 'Show Button in' ).
			'<td>'.$this->show_on_checkboxes( 'linkedin' ).'</td>';

			$rows[] = '<tr class="hide_in_basic">'.
                        $this->p->util->get_th( 'Caption Text Length' ).
			'<td>'.$this->form->get_input( 'linkedin_cap_len', 'short' ).' characters or less</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( 'Append Hashtags to Caption' ).
			'<td>'.$this->form->get_select( 'linkedin_cap_hashtags',
				range( 0, $this->p->cf['form']['max_hashtags'] ), 'short', null, true ).' tag names</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( 'Sharing Button HTML', null, 'linkedin_html' ).
			'<td>'.$this->form->get_textarea( 'linkedin_html', 'average code' ).'</td>';

			return $rows;
		}
	}
}

if ( ! class_exists( 'WpssoRrssbSharingLinkedin' ) ) {

	class WpssoRrssbSharingLinkedin {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'linkedin_on_content' => 1,
					'linkedin_on_excerpt' => 0,
					'linkedin_on_sidebar' => 0,
					'linkedin_on_admin_edit' => 1,
					'linkedin_order' => 6,
					'linkedin_cap_len' => 300,
					'linkedin_cap_hashtags' => 0,
					'linkedin_html' => '<li class="rrssb-linkedin">
	<a href="http://www.linkedin.com/shareArticle?mini=true&url=%%sharing_url%%&title=%%linkedin_title%%&summary=%%linkedin_caption%%" class="popup">
		<span class="rrssb-icon">
			<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28">
				<path d="M25.424 15.887v8.447h-4.896v-7.882c0-1.98-.71-3.33-2.48-3.33-1.354 0-2.158.91-2.514 1.802-.13.315-.162.753-.162 1.194v8.216h-4.9s.067-13.35 0-14.73h4.9v2.087c-.01.017-.023.033-.033.05h.032v-.05c.65-1.002 1.812-2.435 4.414-2.435 3.222 0 5.638 2.106 5.638 6.632zM5.348 2.5c-1.676 0-2.772 1.093-2.772 2.54 0 1.42 1.066 2.538 2.717 2.546h.032c1.71 0 2.77-1.132 2.77-2.546C8.056 3.593 7.02 2.5 5.344 2.5h.005zm-2.48 21.834h4.896V9.604H2.867v14.73z" />
			</svg>
		</span>
		<span class="rrssb-text">linkedin</span>
	</a>
</li><!-- .rrssb-linkedin -->',
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
				'linkedin_title' => '',
				'linkedin_desc' => '',
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

			$add_hashtags = empty( $this->p->options['linkedin_cap_hashtags'] ) ?
				false : $this->p->options['linkedin_cap_hashtags'];

			if ( ! isset( $atts['add_page'] ) )
				$atts['add_page'] = true;

			if ( ! isset( $atts['source_id'] ) )
				$atts['source_id'] = $this->p->util->get_source_id( 'linkedin', $atts );

			return $this->p->util->replace_inline_vars( $this->p->options['linkedin_html'], $use_post, false, $atts, array(
				 	'linkedin_title' => rawurlencode( $this->p->webpage->get_caption( 'title', 0,
						$use_post, true, false, false, 'linkedin_title', 'linkedin' ) ),
				 	'linkedin_caption' => rawurlencode( $this->p->webpage->get_caption( 'excerpt', $opts['linkedin_cap_len'],
						$use_post, true, $add_hashtags, false, 'linkedin_desc', 'linkedin' ) ),
				 ) );
		}
	}
}

?>
