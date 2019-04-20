<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2019 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoRrssbSubmenuSharePinterest' ) ) {

	class WpssoRrssbSubmenuSharePinterest {

		private $p;

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array(
				'image_dimensions_general_rows' => 2,
				'rrssb_share_pinterest_rows'  => 3,
			) );
		}

		/**
		 * Add an option to the WordPress > Settings > SSO Image Sizes settings page.
		 */
		public function filter_image_dimensions_general_rows( $table_rows, $form ) {

			$def_dimensions = $this->p->opt->get_defaults( 'pin_img_width' ).'x'.
				$this->p->opt->get_defaults( 'pin_img_height' ) . ' ' . 
				( $this->p->opt->get_defaults( 'pin_img_crop' ) == 0 ? 'uncropped' : 'cropped' );

			$table_rows[ 'pin_img_dimensions' ] = $form->get_th_html( _x( 'Pinterest <em>Sharing Button</em>', 'option label', 'wpsso-rrssb' ), null, 'pin_img_dimensions', 'The image dimensions that the Pinterest Pin It button will share (defaults is '.$def_dimensions.'). Images in the Facebook / Open Graph meta tags are usually cropped, where-as images on Pinterest often look better in their original aspect ratio (uncropped) and/or cropped using portrait photo dimensions.' ).
			'<td>'.$form->get_input_image_dimensions( 'pin_img' ).'</td>';	// $use_opts = false

			return $table_rows;
		}

		public function filter_rrssb_share_pinterest_rows( $table_rows, $form, $submenu ) {

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Show Button in', 'option label', 'wpsso-rrssb' ) ) .
			'<td>' . $submenu->show_on_checkboxes( 'pin' ) . '</td>';

			$table_rows[] = $form->get_th_html( _x( 'Preferred Order', 'option label', 'wpsso-rrssb' ) ).
			'<td>'.$form->get_select( 'pin_order', range( 1, count( $submenu->share ) ) ).'</td>';

			if ( $this->p->avail[ '*' ][ 'vary_ua' ] ) {

				$table_rows[] = $form->get_tr_hide( 'basic', 'pin_platform' ).
				$form->get_th_html( _x( 'Allow for Platform', 'option label', 'wpsso-rrssb' ) ).
				'<td>'.$form->get_select( 'pin_platform', $this->p->cf[ 'sharing' ][ 'platform' ] ).'</td>';
			}

			$table_rows[] = $form->get_th_html( _x( 'Image Dimensions', 'option label', 'wpsso-rrssb' ) ).
			'<td>'.$form->get_input_image_dimensions( 'pin_img' ).'</td>';	// $use_opts = false

			$table_rows[] = $form->get_tr_hide( 'basic', 'pin_caption_max_len' ).
                        $form->get_th_html( _x( 'Caption Text Length', 'option label', 'wpsso-rrssb' ) ).
			'<td>'.$form->get_input( 'pin_caption_max_len', 'short' ) . ' ' . 
				_x( 'characters or less', 'option comment', 'wpsso-rrssb' ).'</td>';

			$table_rows[] = $form->get_tr_hide( 'basic', 'pin_caption_hashtags' ).
			$form->get_th_html( _x( 'Append Hashtags to Caption', 'option label', 'wpsso-rrssb' ) ).
			'<td>'.$form->get_select( 'pin_caption_hashtags', range( 0, $this->p->cf[ 'form' ][ 'max_hashtags' ] ), 'short', '', true ) . ' ' . 
				_x( 'tag names', 'option comment', 'wpsso-rrssb' ).'</td>';

			$table_rows[] = $form->get_tr_hide( 'basic', 'pin_rrssb_html' ).
			'<td colspan="2">'.$form->get_textarea( 'pin_rrssb_html', 'button_html code' ).'</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'WpssoRrssbSharePinterest' ) ) {

	class WpssoRrssbSharePinterest {

		private $p;
		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'pin_order'            => 5,
					'pin_on_content'       => 1,
					'pin_on_excerpt'       => 0,
					'pin_on_sidebar'       => 0,
					'pin_on_admin_edit'    => 1,
					'pin_platform'         => 'any',
					'pin_img_width'        => 800,
					'pin_img_height'       => 1600,
					'pin_img_crop'         => 0,
					'pin_img_crop_x'       => 'center',
					'pin_img_crop_y'       => 'center',
					'pin_caption_max_len'  => 300,
					'pin_caption_hashtags' => 0,
					'pin_rrssb_html'       => '<li class="rrssb-pinterest">
	<a href="http://pinterest.com/pin/create/button/?url=%%sharing_url%%&media=%%media_url%%&description=%%pinterest_caption%%" class="popup">
		<span class="rrssb-icon">
			<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28">
				<path d="M14.02 1.57c-7.06 0-12.784 5.723-12.784 12.785S6.96 27.14 14.02 27.14c7.062 0 12.786-5.725 12.786-12.785 0-7.06-5.724-12.785-12.785-12.785zm1.24 17.085c-1.16-.09-1.648-.666-2.558-1.22-.5 2.627-1.113 5.146-2.925 6.46-.56-3.972.822-6.952 1.462-10.117-1.094-1.84.13-5.545 2.437-4.632 2.837 1.123-2.458 6.842 1.1 7.557 3.71.744 5.226-6.44 2.924-8.775-3.324-3.374-9.677-.077-8.896 4.754.19 1.178 1.408 1.538.49 3.168-2.13-.472-2.764-2.15-2.683-4.388.132-3.662 3.292-6.227 6.46-6.582 4.008-.448 7.772 1.474 8.29 5.24.58 4.254-1.815 8.864-6.1 8.532v.003z" />
			</svg>
		</span>
		<span class="rrssb-text"></span>
	</a>
</li><!-- .rrssb-pinterest -->',
				),
			),
		);

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array( 
				'get_defaults'       => 1,
				'plugin_image_sizes' => 1,
			) );
		}

		public function filter_get_defaults( $def_opts ) {
			return array_merge( $def_opts, self::$cf[ 'opt' ][ 'defaults' ] );
		}

		public function filter_plugin_image_sizes( $sizes ) {

			$sizes[ 'pin_img' ] = array(	// Options prefix.
				'name'  => 'pinterest-button',
				'label' => _x( 'Pinterest Sharing Button', 'image size label', 'wpsso-rrssb' ),
			);

			return $sizes;
		}

		public function get_html( array $atts, array $opts, array $mod ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$atts[ 'add_hashtags' ] = empty( $this->p->options[ 'pin_caption_hashtags' ] ) ? false : $this->p->options[ 'pin_caption_hashtags' ];

			if ( empty( $atts[ 'size' ] ) ) {
				$atts[ 'size' ] = $this->p->lca.'-pinterest-button';
			}

			if ( ! empty( $atts[ 'pid' ] ) ) {

				$force_regen = $this->p->util->is_force_regen( $mod, 'schema' );	// false by default

				list(
					$atts[ 'photo' ],
					$atts[ 'width' ],
					$atts[ 'height' ],
					$atts[ 'cropped' ],
					$atts[ 'pid' ],
					$atts[ 'alt' ]
				) = $this->p->media->get_attachment_image_src( $atts[ 'pid' ], $atts[ 'size' ], false, $force_regen );

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'returned image '.$atts[ 'photo' ].' ('.$atts[ 'width' ].'x'.$atts[ 'height' ].')' );
				}
			}

			if ( empty( $atts[ 'photo' ] ) ) {

				$media_info = $this->p->og->get_media_info( $atts[ 'size' ], array( 'img_url', 'prev_url' ), $mod, 'schema' );

				if ( ! empty( $media_info[ 'img_url' ] ) ) {
					$atts[ 'photo' ] = $media_info[ 'img_url' ];
				} elseif ( ! empty( $media_info[ 'prev_url' ] ) ) {
					$atts[ 'photo' ] = $media_info[ 'prev_url' ];
				}

				if ( empty( $atts[ 'photo' ] ) ) {
					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( 'exiting early: no photo available' );
					}
					return '<!-- Pinterest Button: no photo available -->';	// abort
				}
			}

			if ( ! $this->p->avail[ '*' ][ 'vary_ua' ] || SucomUtil::is_mobile() ) {
				$pinterest_button_html = $this->p->options[ 'pin_rrssb_html' ];
			} else {
				$pinterest_button_html = preg_replace( '/(\/create)\/(button\/)/', '$1/+/$2', $this->p->options[ 'pin_rrssb_html' ] );
			}

			$pinterest_caption = $this->p->page->get_caption( 'excerpt', $opts[ 'pin_caption_max_len' ], $mod, true,
				$atts[ 'add_hashtags' ], false, 'pin_desc' );

			return $this->p->util->replace_inline_vars( '<!-- Pinterest Button -->'.
				$pinterest_button_html, $mod, $atts, array(
					'media_url' => rawurlencode( $atts[ 'photo' ] ),
				 	'pinterest_caption' => rawurlencode( $pinterest_caption ),
				)
			);
		}
	}
}
