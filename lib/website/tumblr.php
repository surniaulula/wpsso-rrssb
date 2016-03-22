<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoRrssbSubmenuSharingTumblr' ) && class_exists( 'WpssoRrssbSubmenuSharingButtons' ) ) {

	class WpssoRrssbSubmenuSharingTumblr extends WpssoRrssbSubmenuSharingButtons {

		public function __construct( &$plugin, $id, $name ) {
			$this->p =& $plugin;
			$this->website_id = $id;
			$this->website_name = $name;

			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
		}

		protected function get_table_rows( $metabox, $key ) {
			$table_rows = array();

			$table_rows[] = $this->form->get_th_html( _x( 'Preferred Order',
				'option label', 'wpsso-rrssb' ), null, 'tumblr_order' ).
			'<td>'.$this->form->get_select( 'tumblr_order', 
				range( 1, count( $this->p->admin->submenu['sharing-buttons']->website ) ), 'short' ).  '</td>';

			$table_rows[] = $this->form->get_th_html( _x( 'Show Button in',
				'option label', 'wpsso-rrssb' ) ).
			'<td>'.$this->show_on_checkboxes( 'tumblr' ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$this->form->get_th_html( _x( 'Allow for Platform',
				'option label', 'wpsso-rrssb' ) ).
			'<td>'.$this->form->get_select( 'tumblr_platform',
				$this->p->cf['sharing']['platform'] ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
                        $this->form->get_th_html( _x( 'Summary Text Length',
				'option label', 'wpsso-rrssb' ) ).
			'<td>'.$this->form->get_input( 'tumblr_cap_len', 'short' ).' '.
				_x( 'characters or less', 'option comment', 'wpsso-rrssb' ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$this->form->get_th_html( _x( 'Append Hashtags to Summary',
				'option label', 'wpsso-rrssb' ) ).
			'<td>'.$this->form->get_select( 'tumblr_cap_hashtags',
				range( 0, $this->p->cf['form']['max_hashtags'] ), 'short', null, true ).' '.
					_x( 'tag names', 'option comment', 'wpsso-rrssb' ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			'<td colspan="2">'.$this->form->get_textarea( 'tumblr_rrssb_html', 'average code' ).'</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'WpssoRrssbSharingTumblr' ) ) {

	class WpssoRrssbSharingTumblr {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'tumblr_order' => 9,
					'tumblr_on_content' => 0,
					'tumblr_on_excerpt' => 0,
					'tumblr_on_sidebar' => 0,
					'tumblr_on_admin_edit' => 0,
					'tumblr_platform' => 'any',
					'tumblr_cap_len' => 300,
					'tumblr_cap_hashtags' => 0,
					'tumblr_rrssb_html' => '<li class="rrssb-tumblr">
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
			$this->p->util->add_plugin_filters( $this, array( 
				'get_defaults' => 1,
				'get_md_defaults' => 1,
			) );
		}

		public function filter_get_md_defaults( $def_opts ) {
			return array_merge( $def_opts, array(
				'tumblr_title' => '',
				'tumblr_desc' => '',
			) );
		}

		public function filter_get_defaults( $def_opts ) {
			return array_merge( $def_opts, self::$cf['opt']['defaults'] );
		}

		// do not use an $atts reference to allow for local changes
		public function get_html( array $atts, array &$opts, array &$mod ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( empty( $opts ) ) 
				$opts =& $this->p->options;

			$atts['use_post'] = isset( $atts['use_post'] ) ? $atts['use_post'] : true;
			$atts['add_page'] = isset( $atts['add_page'] ) ? $atts['add_page'] : true;
			$atts['source_id'] = isset( $atts['source_id'] ) ?
				$atts['source_id'] : $this->p->util->get_source_id( 'tumblr', $atts );
			$atts['add_hashtags'] = empty( $this->p->options['tumblr_cap_hashtags'] ) ?
				false : $this->p->options['tumblr_cap_hashtags'];

			return $this->p->util->replace_inline_vars( '<!-- Tumblr Button -->'.
				$this->p->options['tumblr_rrssb_html'], $atts['use_post'], false, $atts, array(
				 	'tumblr_title' => rawurlencode( $this->p->webpage->get_caption( 'title', 0,
						$mod, true, false, false, 'tumblr_title', 'tumblr' ) ),
				 	'tumblr_summary' => rawurlencode( $this->p->webpage->get_caption( 'excerpt', $opts['tumblr_cap_len'],
						$mod, true, $atts['add_hashtags'], false, 'tumblr_desc', 'tumblr' ) ),
				 )
			 );
		}
	}
}

?>
