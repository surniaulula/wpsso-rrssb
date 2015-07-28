<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoRrssbGplAdminSharing' ) ) {

	class WpssoRrssbGplAdminSharing {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 
				'post_tabs' => 1,		// post 'Sharing Buttons' tab
				'post_sharing_rows' => 3,	// post 'Sharing Buttons' options
			), 40 );
		}

		public function filter_post_tabs( $tabs ) {
			$new_tabs = array();
			foreach ( $tabs as $key => $val ) {
				$new_tabs[$key] = $val;
				if ( $key === 'media' )	// insert the social sharing tab after the media tab
					$new_tabs['sharing'] = 'Sharing Buttons';
			}
			return $new_tabs;
		}

		public function filter_post_sharing_rows( $rows, $form, $head_info ) {

			$lca = $this->p->cf['lca'];
			$post_status = get_post_status( $head_info['post_id'] );
			$size_info = $this->p->media->get_size_info( 'thumbnail' );

			$rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg', array( 'lca' => 'wpssorrssb' ) ).'</td>';

			/*
			 * Pinterest
			 */
			list( $pid, $img_url ) = $this->p->og->get_the_media_urls( $lca.'-pinterest-button',
				$head_info['post_id'], 'rp', array( 'pid', 'image' ) );

			$th = $this->p->util->get_th( 'Pinterest Image Caption', 'medium', 'post-pin_desc' );
			if ( ! empty( $pid ) ) {
				list(
					$img_url,
					$img_width,
					$img_height,
					$img_cropped
				) = $this->p->media->get_attachment_image_src( $pid, 'thumbnail', false ); 
			}
			if ( ! empty( $img_url ) ) {
				$rows['pin_desc'] = $th.'<td class="blank">'.
				$this->p->webpage->get_caption( 'excerpt', $this->p->options['pin_cap_len'],
					true, true, $this->p->options['pin_cap_hashtags'] ).'</td>'.
				'<td style="width:'.$size_info['width'].'px;"><img src="'.$img_url.'"
					style="max-width:'.$size_info['width'].'px;"></td>';
			} else $rows['pin_desc'] = $th.'<td class="blank"><em>Caption disabled - no suitable image found for the Pinterest button.</em></td>';

			/*
			 * Twitter
			 */
			$twitter_cap_len = $this->p->util->get_tweet_max_len( get_permalink( $head_info['post_id'] ) );
			$rows['twitter_desc'] = $this->p->util->get_th( 'Tweet Text', 'medium', 'post-twitter_desc' ). 
			'<td class="blank">'.$this->p->webpage->get_caption( 'title', $twitter_cap_len, 
				true, true, $this->p->options['twitter_cap_hashtags'] ).'</td>';

			$rows['buttons_disabled'] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( 'Disable Sharing Buttons', 'medium', 'post-buttons_disabled', $head_info ).
			'<td class="blank">&nbsp;</td>';

			return $rows;
		}

		protected function get_site_use( &$form, &$network, $opt ) {
			return $network === false ? '' : $this->p->util->get_th( 'Site Use', 'site_use' ).
				'<td class="site_use blank">'.$form->get_select( $opt.':use', 
					$this->p->cf['form']['site_option_use'], 'site_use', null, true, true ).'</td>';
		}
	}
}

?>
