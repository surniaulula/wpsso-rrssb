<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoRrssbSubmenuSharingPinterest' ) && class_exists( 'WpssoRrssbSubmenuSharingButtons' ) ) {

	class WpssoRrssbSubmenuSharingPinterest extends WpssoRrssbSubmenuSharingButtons {

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

			$rows[] = $this->p->util->get_th( 'Preferred Order', null, 'pin_order' ).
			'<td>'.$this->form->get_select( 'pin_order', 
				range( 1, count( $this->p->admin->submenu['sharing-buttons']->website ) ), 'short' ).  '</td>';

			$rows[] = $this->p->util->get_th( 'Show Button in' ).
			'<td>'.$this->show_on_checkboxes( 'pin' ).'</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( 'Share Single Image', null, null, 'Check this option to have the Pinterest button appear only on Posts and Pages with a custom Image ID (in the Social Settings metabox), a featured image, or an attached image, that is equal to or larger than the \'Image Dimensions\' you have chosen. <strong>By leaving this option unchecked, the Pinterest button will submit the current webpage URL without a specific image</strong>, allowing Pinterest to present any number of available images for pinning.' ).
			'<td>'.$this->form->get_checkbox( 'pin_use_img' ).'</td>';

			$rows[] = $this->p->util->get_th( 'Image Dimensions' ).
			'<td>'.$this->form->get_image_dimensions_input( 'pin_img' ).'</td>';

			$rows[] = '<tr class="hide_in_basic">'.
                        $this->p->util->get_th( 'Caption Text Length' ).
			'<td>'.$this->form->get_input( 'pin_cap_len', 'short' ).' characters or less</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( 'Append Hashtags to Summary' ).
			'<td>'.$this->form->get_select( 'pin_cap_hashtags',
				range( 0, $this->p->cf['form']['max_hashtags'] ), 'short', null, true ).' tag names</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( 'Sharing Button HTML', null, 'pin_html' ).
			'<td>'.$this->form->get_textarea( 'pin_html', 'average code' ).'</td>';

			return $rows;
		}
	}
}

if ( ! class_exists( 'WpssoRrssbSharingPinterest' ) ) {

	class WpssoRrssbSharingPinterest {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'pin_on_content' => 1,
					'pin_on_excerpt' => 0,
					'pin_on_sidebar' => 0,
					'pin_on_admin_edit' => 1,
					'pin_order' => 5,
					'pin_use_img' => 1,
					'pin_img_width' => 600,
					'pin_img_height' => 600,
					'pin_img_crop' => 0,
					'pin_img_crop_x' => 'center',
					'pin_img_crop_y' => 'center',
					'pin_cap_len' => 300,
					'pin_cap_hashtags' => 0,
					'pin_html' => '<li class="rrssb-pinterest">
	<a href="http://pinterest.com/pin/create/button/?url=%%sharing_url%%&amp;media=%%media_url%%&amp;description=%%pinterest_caption%%" class="popup">
		<span class="rrssb-icon">
			<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28">
				<path d="M14.02 1.57c-7.06 0-12.784 5.723-12.784 12.785S6.96 27.14 14.02 27.14c7.062 0 12.786-5.725 12.786-12.785 0-7.06-5.724-12.785-12.785-12.785zm1.24 17.085c-1.16-.09-1.648-.666-2.558-1.22-.5 2.627-1.113 5.146-2.925 6.46-.56-3.972.822-6.952 1.462-10.117-1.094-1.84.13-5.545 2.437-4.632 2.837 1.123-2.458 6.842 1.1 7.557 3.71.744 5.226-6.44 2.924-8.775-3.324-3.374-9.677-.077-8.896 4.754.19 1.178 1.408 1.538.49 3.168-2.13-.472-2.764-2.15-2.683-4.388.132-3.662 3.292-6.227 6.46-6.582 4.008-.448 7.772 1.474 8.29 5.24.58 4.254-1.815 8.864-6.1 8.532v.003z" />
			</svg>
		</span>
		<span class="rrssb-text">pinterest</span>
	</a>
</li><!-- .rrssb-pinterest -->',
				),
			),
		);

		protected $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 
				'plugin_image_sizes' => 1,
				'get_defaults' => 1,
			) );
		}

		public function filter_get_defaults( $opts_def ) {
			return array_merge( $opts_def, self::$cf['opt']['defaults'] );
		}

		public function filter_plugin_image_sizes( $sizes ) {
			$sizes['pin_img'] = array( 'name' => 'pinterest-button', 'label' => 'Pinterest Sharing Button' );
			return $sizes;
		}

		public function get_html( $atts = array(), &$opts = array() ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( empty( $opts ) ) 
				$opts =& $this->p->options;

			$use_post = isset( $atts['use_post'] ) ?
				$atts['use_post'] : true;

			$add_hashtags = empty( $this->p->options['pin_cap_hashtags'] ) ?
				false : $this->p->options['pin_cap_hashtags'];

			if ( ! isset( $atts['add_page'] ) )
				$atts['add_page'] = true;

			if ( ! isset( $atts['source_id'] ) )
				$atts['source_id'] = $this->p->util->get_source_id( 'pinterest', $atts );

			$post_id = 0;
			if ( is_singular() || $use_post !== false ) {
				if ( ( $obj = $this->p->util->get_post_object( $use_post ) ) === false ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'exiting early: invalid object type' );
					return false;
				}
				$post_id = empty( $obj->ID ) || empty( $obj->post_type ) ? 0 : $obj->ID;
			}

			if ( empty( $atts['size'] ) )
				$atts['size'] = $this->p->cf['lca'].'-pinterest-button';

			if ( ! empty( $atts['pid'] ) )
				list(
					$atts['photo'],
					$atts['width'],
					$atts['height'],
					$atts['cropped']
				) = $this->p->media->get_attachment_image_src( $atts['pid'], $atts['size'], false );

			if ( empty( $atts['photo'] ) ) {
				if ( ! empty( $this->p->options['pin_use_img'] ) && $post_id > 0 )
					list( $atts['photo'] ) = $this->p->og->get_the_media_urls( $atts['size'], $post_id, 'rp', array( 'image' ) );
				else $atts['photo'] = '';
			}

			if ( ! empty( $this->p->options['pin_use_img'] ) && empty( $atts['photo'] ) ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'exiting early: no photo defined for post_id '.$post_id );
				return false;
			}

			return $this->p->util->replace_inline_vars( $this->p->options['pin_html'], $use_post, false, $atts, array(
					'media_url' => rawurlencode( $atts['photo'] ),
				 	'pinterest_caption' => rawurlencode( $this->p->webpage->get_caption( 'excerpt', $opts['pin_cap_len'],
						$use_post, true, $add_hashtags, false, 'pin_desc', 'pinterest' ) ),
				 ) );
		}
	}
}

?>
