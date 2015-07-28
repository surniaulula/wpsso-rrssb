<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoRrssbSubmenuSharingTumblr' ) && class_exists( 'WpssoRrssbSubmenuSharingButtons' ) ) {

	class WpssoRrssbSubmenuSharingTumblr extends WpssoRrssbSubmenuSharingButtons {

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

			$rows[] = $this->p->util->get_th( 'Preferred Order', null, 'tumblr_order' ).
			'<td>'.$this->form->get_select( 'tumblr_order', 
				range( 1, count( $this->p->admin->submenu['sharing-buttons']->website ) ), 'short' ).  '</td>';

			$rows[] = $this->p->util->get_th( 'Show Button in' ).
			'<td>'.$this->show_on_checkboxes( 'tumblr' ).'</td>';

			$rows[] = '<tr class="hide_in_basic">'.
                        $this->p->util->get_th( 'Summary Text Length' ).
			'<td>'.$this->form->get_input( 'tumblr_cap_len', 'short' ).' characters or less</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( 'Append Hashtags to Summary' ).
			'<td>'.$this->form->get_select( 'tumblr_cap_hashtags',
				range( 0, $this->p->cf['form']['max_hashtags'] ), 'short', null, true ).' tag names</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( 'Sharing Button HTML', null, 'tumblr_html' ).
			'<td>'.$this->form->get_textarea( 'tumblr_html', 'average code' ).'</td>';

			return $rows;
		}
	}
}

if ( ! class_exists( 'WpssoRrssbSharingTumblr' ) ) {

	class WpssoRrssbSharingTumblr {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'tumblr_on_content' => 0,
					'tumblr_on_excerpt' => 0,
					'tumblr_on_sidebar' => 0,
					'tumblr_on_admin_edit' => 0,
					'tumblr_order' => 9,
					'tumblr_cap_len' => 300,
					'tumblr_cap_hashtags' => 0,
					'tumblr_html' => '<li class="rrssb-tumblr">
	<a href="http://tumblr.com/share/link?url=%%sharing_url%%&name=%%tumblr_title%%&description=%%tumblr_summary%%" class="popup">
		<span class="rrssb-icon">
			<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28">
				<path d="M18.02 21.842c-2.03.052-2.422-1.396-2.44-2.446v-7.294h4.73V7.874H15.6V1.592h-3.714s-.167.053-.182.186c-.218 1.935-1.144 5.33-4.988 6.688v3.637h2.927v7.677c0 2.8 1.7 6.7 7.3 6.6 1.863-.03 3.934-.795 4.392-1.453l-1.22-3.54c-.52.213-1.415.413-2.115.455z" />
			</svg>
		</span>
		<span class="rrssb-text">tumblr</span>
	</a>
</li><!-- .rrssb-tumblr -->',
				),
			),
		);

		protected $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 'get_defaults' => 1 ) );
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

			$add_hashtags = empty( $this->p->options['tumblr_cap_hashtags'] ) ?
				false : $this->p->options['tumblr_cap_hashtags'];

			if ( ! isset( $atts['add_page'] ) )
				$atts['add_page'] = true;

			if ( ! isset( $atts['source_id'] ) )
				$atts['source_id'] = $this->p->util->get_source_id( 'tumblr', $atts );

			return $this->p->util->replace_inline_vars( $this->p->options['tumblr_html'], $use_post, false, $atts, array(
				 	'tumblr_title' => rawurlencode( $this->p->webpage->get_caption( 'title', 0,
						$use_post, true, false, false, 'og_title', 'tumblr' ) ),
				 	'tumblr_caption' => rawurlencode( $this->p->webpage->get_caption( 'excerpt', $opts['tumblr_cap_len'],
						$use_post, true, $add_hashtags, false, 'og_desc', 'tumblr' ) ),
				 ) );
		}
	}
}

?>
