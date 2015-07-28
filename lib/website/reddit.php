<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoRrssbSubmenuSharingReddit' ) && class_exists( 'WpssoRrssbSubmenuSharingButtons' ) ) {

	class WpssoRrssbSubmenuSharingReddit extends WpssoRrssbSubmenuSharingButtons {

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

			$rows[] = $this->p->util->get_th( 'Preferred Order', null, 'reddit_order' ).
			'<td>'.$this->form->get_select( 'reddit_order', 
				range( 1, count( $this->p->admin->submenu['sharing-buttons']->website ) ), 'short' ).  '</td>';

			$rows[] = $this->p->util->get_th( 'Show Button in' ).
			'<td>'.$this->show_on_checkboxes( 'reddit' ).'</td>';

			$rows[] = '<tr class="hide_in_basic">'.
                        $this->p->util->get_th( 'Caption Text Length' ).
			'<td>'.$this->form->get_input( 'reddit_cap_len', 'short' ).' characters or less</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( 'Append Hashtags to Caption' ).
			'<td>'.$this->form->get_select( 'reddit_cap_hashtags',
				range( 0, $this->p->cf['form']['max_hashtags'] ), 'short', null, true ).' tag names</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( 'Sharing Button HTML', null, 'reddit_html' ).
			'<td>'.$this->form->get_textarea( 'reddit_html', 'average code' ).'</td>';

			return $rows;
		}
	}
}

if ( ! class_exists( 'WpssoRrssbSharingReddit' ) ) {

	class WpssoRrssbSharingReddit {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'reddit_on_content' => 0,
					'reddit_on_excerpt' => 0,
					'reddit_on_sidebar' => 0,
					'reddit_on_admin_edit' => 0,
					'reddit_order' => 7,
					'reddit_cap_len' => 300,
					'reddit_cap_hashtags' => 0,
					'reddit_html' => '<li class="rrssb-reddit">
	<a href="http://www.reddit.com/submit?url=%%sharing_url%%&title=%%reddit_title%%&text=%%reddit_summary%%">
		<span class="rrssb-icon">
			<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28">
				<path d="M11.794 15.316c0-1.03-.835-1.895-1.866-1.895-1.03 0-1.893.866-1.893 1.896s.863 1.9 1.9 1.9c1.023-.016 1.865-.916 1.865-1.9zM18.1 13.422c-1.03 0-1.895.864-1.895 1.895 0 1 .9 1.9 1.9 1.865 1.03 0 1.87-.836 1.87-1.865-.006-1.017-.875-1.917-1.875-1.895zM17.527 19.79c-.678.68-1.826 1.007-3.514 1.007h-.03c-1.686 0-2.834-.328-3.51-1.005-.264-.265-.693-.265-.958 0-.264.265-.264.7 0 1 .943.9 2.4 1.4 4.5 1.402.005 0 0 0 0 0 .005 0 0 0 0 0 2.066 0 3.527-.46 4.47-1.402.265-.264.265-.693.002-.958-.267-.334-.688-.334-.988-.043z" />
				<path d="M27.707 13.267c0-1.785-1.453-3.237-3.236-3.237-.792 0-1.517.287-2.08.76-2.04-1.294-4.647-2.068-7.44-2.218l1.484-4.69 4.062.955c.07 1.4 1.3 2.6 2.7 2.555 1.488 0 2.695-1.208 2.695-2.695C25.88 3.2 24.7 2 23.2 2c-1.06 0-1.98.616-2.42 1.508l-4.633-1.09c-.344-.082-.693.117-.803.454l-1.793 5.7C10.55 8.6 7.7 9.4 5.6 10.75c-.594-.45-1.3-.75-2.1-.72-1.785 0-3.237 1.45-3.237 3.2 0 1.1.6 2.1 1.4 2.69-.04.27-.06.55-.06.83 0 2.3 1.3 4.4 3.7 5.9 2.298 1.5 5.3 2.3 8.6 2.325 3.227 0 6.27-.825 8.57-2.325 2.387-1.56 3.7-3.66 3.7-5.917 0-.26-.016-.514-.05-.768.965-.465 1.577-1.565 1.577-2.698zm-4.52-9.912c.74 0 1.3.6 1.3 1.3 0 .738-.6 1.34-1.34 1.34s-1.343-.602-1.343-1.34c.04-.655.596-1.255 1.396-1.3zM1.646 13.3c0-1.038.845-1.882 1.883-1.882.31 0 .6.1.9.21-1.05.867-1.813 1.86-2.26 2.9-.338-.328-.57-.728-.57-1.26zm20.126 8.27c-2.082 1.357-4.863 2.105-7.83 2.105-2.968 0-5.748-.748-7.83-2.105-1.99-1.3-3.087-3-3.087-4.782 0-1.784 1.097-3.484 3.088-4.784 2.08-1.358 4.86-2.106 7.828-2.106 2.967 0 5.7.7 7.8 2.106 1.99 1.3 3.1 3 3.1 4.784C24.86 18.6 23.8 20.3 21.8 21.57zm4.014-6.97c-.432-1.084-1.19-2.095-2.244-2.977.273-.156.59-.245.928-.245 1.036 0 1.9.8 1.9 1.9-.016.522-.27 1.022-.57 1.327z" />
			</svg>
		</span>
		<span class="rrssb-text">reddit</span>
	</a>
</li><!-- .rrssb-reddit -->',
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
				'reddit_title' => '',
				'reddit_desc' => '',
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

			$add_hashtags = empty( $this->p->options['reddit_cap_hashtags'] ) ?
				false : $this->p->options['reddit_cap_hashtags'];

			if ( ! isset( $atts['add_page'] ) )
				$atts['add_page'] = true;

			if ( ! isset( $atts['source_id'] ) )
				$atts['source_id'] = $this->p->util->get_source_id( 'reddit', $atts );

			return $this->p->util->replace_inline_vars( $this->p->options['reddit_html'], $use_post, false, $atts, array(
				 	'reddit_title' => rawurlencode( $this->p->webpage->get_caption( 'title', 0,
						$use_post, true, false, false, 'reddit_title', 'reddit' ) ),
				 	'reddit_summary' => rawurlencode( $this->p->webpage->get_caption( 'excerpt', $opts['reddit_cap_len'],
						$use_post, true, $add_hashtags, false, 'reddit_desc', 'reddit' ) ),
				 ) );
		}
	}
}

?>
