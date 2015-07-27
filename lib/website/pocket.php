<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoRrssbSubmenuSharingPocket' ) && class_exists( 'WpssoRrssbSubmenuSharingButtons' ) ) {

	class WpssoRrssbSubmenuSharingPocket extends WpssoRrssbSubmenuSharingButtons {

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

			$rows[] = $this->p->util->get_th( 'Preferred Order', null, 'pocket_order' ).
			'<td>'.$this->form->get_select( 'pocket_order', 
				range( 1, count( $this->p->admin->submenu['sharing-buttons']->website ) ), 'short' ).  '</td>';

			$rows[] = $this->p->util->get_th( 'Show Button in' ).
			'<td>'.$this->show_on_checkboxes( 'pocket' ).'</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( 'Sharing Button HTML', null, 'pocket_html' ).
			'<td>'.$this->form->get_textarea( 'pocket_html', 'average code' ).'</td>';

			return $rows;
		}
	}
}

if ( ! class_exists( 'WpssoRrssbSharingPocket' ) ) {

	class WpssoRrssbSharingPocket {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'pocket_on_content' => 0,
					'pocket_on_excerpt' => 0,
					'pocket_on_sidebar' => 0,
					'pocket_on_admin_edit' => 0,
					'pocket_order' => 8,
					'pocket_html' => '<li class="rrssb-pocket">
	<a href="https://getpocket.com/save?url=%%sharing_url%%">
		<span class="rrssb-icon">
			<svg width="32" height="28" viewBox="0 0 32 28" xmlns="http://www.w3.org/2000/svg">
				<path d="M28.782.002c2.03.002 3.193 1.12 3.182 3.106-.022 3.57.17 7.16-.158 10.7-1.09 11.773-14.588 18.092-24.6 11.573C2.72 22.458.197 18.313.057 12.937c-.09-3.36-.05-6.72-.026-10.08C.04 1.113 1.212.016 3.02.008 7.347-.006 11.678.004 16.006.002c4.258 0 8.518-.004 12.776 0zM8.65 7.856c-1.262.135-1.99.57-2.357 1.476-.392.965-.115 1.81.606 2.496 2.453 2.334 4.91 4.664 7.398 6.966 1.086 1.003 2.237.99 3.314-.013 2.407-2.23 4.795-4.482 7.17-6.747 1.203-1.148 1.32-2.468.365-3.426-1.01-1.014-2.302-.933-3.558.245-1.596 1.497-3.222 2.965-4.75 4.526-.706.715-1.12.627-1.783-.034-1.597-1.596-3.25-3.138-4.93-4.644-.47-.42-1.123-.647-1.478-.844z" />
			</svg>
		</span>
		<span class="rrssb-text">pocket</span>
	</a>
</li><!-- .rrssb-pocket -->',
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

			if ( ! isset( $atts['add_page'] ) )
				$atts['add_page'] = true;

			if ( ! isset( $atts['source_id'] ) )
				$atts['source_id'] = $this->p->util->get_source_id( 'pocket', $atts );

			return $this->p->util->replace_inline_vars( $this->p->options['pocket_html'], $use_post, false, $atts );
		}
	}
}

?>
