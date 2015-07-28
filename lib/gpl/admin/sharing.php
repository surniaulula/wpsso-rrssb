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
			$save_draft_msg = '<em>Save a draft version or publish the '.$head_info['ptn'].' to enable this field.</em>';

			$rows[] = '<td colspan="3" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg', array( 'lca' => 'wpssorrssb' ) ).'</td>';

			/*
			 * Email
			 */
			$rows['email_title'] = $this->p->util->get_th( 'Email Subject', 'medium', 'post-email_title' ). 
			'<td class="blank">'.$this->p->webpage->get_caption( 'title', 0, true, true, false ).'</td>';

			$rows['email_desc'] = $this->p->util->get_th( 'Email Message', 'medium', 'post-email_desc' ). 
			'<td class="blank">'.$this->p->webpage->get_caption( 'excerpt', $opts['email_cap_len'], 
				true, true, $this->p->options['email_cap_hashtags'] ).'</td>';

			/*
			 * Pinterest
			 */
			list( $pid, $img_url ) = $this->p->og->get_the_media_urls( $lca.'-pinterest-button',
				$head_info['post_id'], 'rp', array( 'pid', 'image' ) );

			$th = $this->p->util->get_th( 'Pinterest Caption', 'medium', 'post-pin_desc' );
			if ( ! empty( $pid ) ) {
				list(
					$img_url,
					$img_width,
					$img_height,
					$img_cropped
				) = $this->p->media->get_attachment_image_src( $pid, 'thumbnail', false ); 
			}
			$rows['pin_desc'] = $th.'<td class="blank">'.
			$this->p->webpage->get_caption( 'excerpt', $this->p->options['pin_cap_len'],
				true, true, $this->p->options['pin_cap_hashtags'] ).'</td>'.
			( empty( $img_url ) ? '' : '<td style="width:'.$size_info['width'].'px;"><img src="'.$img_url.'"
				style="max-width:'.$size_info['width'].'px;"></td>' );

			/*
			 * Twitter
			 */
			$twitter_cap_len = $this->p->util->get_tweet_max_len( get_permalink( $head_info['post_id'] ) );
			$rows['twitter_desc'] = $this->p->util->get_th( 'Tweet Text', 'medium', 'post-twitter_desc' ). 
			'<td class="blank">'.$this->p->webpage->get_caption( 'title', $twitter_cap_len, 
				true, true, $this->p->options['twitter_cap_hashtags'] ).'</td>';

			/*
			 * Generic Title / Caption Input
			 */
			foreach ( array(
				'LinkedIn' => 'linkedin',
				'Reddit' => 'reddit',
				'Tumblr' => 'tumblr',
			) as $name => $opt_prefix ) {
				$rows[$opt_prefix.'_title'] = $this->p->util->get_th( $name.' Title', 'medium', 'post-'.$opt_prefix.'_title' ). 
				'<td class="blank">'.$this->p->webpage->get_caption( 'title', 0, true, true, false ).'</td>';
	
				$rows[$opt_prefix.'_desc'] = $this->p->util->get_th( $name.' Caption', 'medium', 'post-'.$opt_prefix.'_desc' ). 
				'<td class="blank">'.$this->p->webpage->get_caption( 'excerpt', $opts[$opt_prefix.'_cap_len'], 
					true, true, $this->p->options[$opt_prefix.'_cap_hashtags'] ).'</td>';
			}

			/*
			 * Miscellaneous
			 */
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
